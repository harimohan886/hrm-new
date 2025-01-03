<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ManageWeekOff;
use App\Models\ManageWfh;
use App\Models\Leave as LocalLeave;
use App\Models\Employee;
use App\Models\Utility;
use Carbon\Carbon;
use App\Models\User;
use App\Models\AttendanceEmployee;
use App\Models\ManageShift;
use Illuminate\Support\Facades\Auth;

class wfhController extends Controller
{
    public function index(Request $request)
    {   
        // dd("checker");
        // Check for permission
        // if (\Auth::user()->can('Manage Weekoff')) {
            // Initialize the query
            $query = ManageWfh::query();

            // Check if user is an employee
            if (\Auth::user()->type == 'employee') {
                $query->where('employee_id', '=', \Auth::user()->id);
            } else {
                $query->with('employees');
            }

            // Apply filters if present
            $created_at = $request->input('created_at');
            if ($created_at) {
                $query->whereDate('created_at', $created_at);
            }

            if ($request->filled('start_date') && $request->filled('end_date')) {
                $startDate = $request->input('start_date');
                $endDate = $request->input('end_date');
                $query->whereBetween('start_date', [$startDate, $endDate]);
                $query->whereBetween('end_date', [$startDate, $endDate]);
            }

            if ($request->filled('status')) {
                $status = $request->input('status');
                $query->where('status', $status);
            }

            if ($request->filled('employee')) {
                $employeeId = $request->input('employee');
                $emp =  Employee::where('user_id',$employeeId)->first();
                // dd($emp->id);
                $query->where('employee_id', $emp->id);
            }

            // Debug query (for development only, remove or comment out in production)
            // $sql = $query->toSql(); // Get the raw SQL query
            // $bindings = $query->getBindings(); // Get the bindings
            // dd($sql, $bindings); // Print raw query and bindings

            // Execute the query
            $wfhs = $query->orderBy('updated_at', 'desc')->get();

            // Fetch the list of users for filter dropdown (excluding super admin and company)
            $objUser = \Auth::user();
            $usersList = User::where('created_by', $objUser->creatorId())
                ->whereNotIn('type', ['super admin', 'company'])
                ->get()
                ->pluck('name', 'id');
            $usersList->prepend('All', '');

            return view('wfh.index', compact('wfhs', 'usersList'));
        // } else {
        //     return redirect()->back()->with('error', __('Permission denied.'));
        // }
    }

    public function store(Request $request)
    {   
        // dd($request->all());

        $validator = \Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'remark' => 'required|string',
        ]);
    
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first());
        }
    
        $start_date = new \DateTime($request->start_date);
        $end_date = new \DateTime($request->end_date);
        $currentDate = new \DateTime(); 

        $currentDate->setTime(0, 0);

        // Check if the start date is in the past or today
        if ($start_date <= $currentDate) {
            return redirect()->back()->with('error', 'You cannot apply for a WFH in the past or today.');
        }
    
        // Check if the end date is before the start date
        if ($end_date < $start_date) {
            return redirect()->back()->with('error', 'End date cannot be before the start date.');
        }

        $wfh = new ManageWfh();
        $wfh->employee_id = \Auth::user()->id;
        $wfh->start_date = $start_date->format('Y-m-d');
        $wfh->end_date = $end_date->format('Y-m-d');
        $wfh->remark = $request->remark;
        $wfh->status = "Pending";
        $wfh->created_by = \Auth::user()->id;
        $wfh->save();
    
        // Redirect with success message
        return redirect()->back()->with('success', 'Wfh request successfully added.');
    }

    public function action($id)
    {   
        $wfh     = ManageWfh::find($id);
        // $employee  = Employee::find($weekoff->employee_id);
        // dd($employee);
        // $leavetype = LeaveType::find($leave->leave_type_id);

        return view('wfh.action', compact('wfh'));
    }

    // public function changeaction(Request $request)
    // {   
    //     // dd($request->all());
    //     $wfh = ManageWfh::find($request->wfh_id);
    //     $wfh->status = $request->status;
    //     // dd($request->weekoff_id);

    //         if ($wfh->status == 'Approved') {
    //             $wfh->updated_at = Carbon::now();
    //             $wfh->status           = 'Approved';
    //         }elseif ($wfh->status == 'Reject'){
    //             $wfh->updated_at = Carbon::now();
    //             $wfh->status           = 'Reject';
    //         }else{
    //             $wfh->updated_at = Carbon::now();
    //             $wfh->status           = 'Pending';
    //         }
    
    //         $wfh->save();

    //         // dd($wfh->status);
    //         $currentDate = $wfh->start_date;
    //         if($wfh->status=="Approved"){

    //             while ($currentDate <= $wfh->end_date) {

    //                 $employee = Employee::where('user_id', $wfh->employee_id)->first();
    //                 $shift = ManageShift::where('shift_code', $employee->shift_code)->first();

    //                 // Create AttendanceEmployee entry
    //                 $entry = new AttendanceEmployee();
    //                 $entry->employee_id = $wfh->employee_id;
    //                 $entry->date = $currentDate;
    //                 $entry->status = "Present";
    //                 $entry->clock_in = $shift->start_time;
    //                 $entry->clock_out = $shift->end_time;
    //                 $entry->late = "00:00:00";
    //                 $entry->early_leaving = "00:00:00";
    //                 $entry->overtime = "00:00:00";
    //                 $entry->total_rest = "00:00:00";
    //                 $entry->manually_data = 1;
    //                 $entry->save();
        
    //                 $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
    //             }

    //         }

    //     return redirect()->route('wfh.index')->with('success', __('Wfh status successfully updated.'));
    // }

    public function changeaction(Request $request)
{
    // Validate the request data
    $request->validate([
        'wfh_id' => 'required',
        'status' => 'required|in:Approved,Reject,Pending',
    ]);

    // Find the WFH record
    $wfh = ManageWfh::find($request->wfh_id);
    
    // Update the status and timestamp
    $wfh->status = $request->status;
    $wfh->updated_at = Carbon::now();

    // Save the updated WFH record
    $wfh->save();

    // Process if the status is 'Approved'
    if ($wfh->status === 'Approved') {
        $startDate = Carbon::parse($wfh->start_date);
        $endDate = Carbon::parse($wfh->end_date);
        
        while ($startDate->lte($endDate)) {
            $employee = Employee::where('user_id', $wfh->employee_id)->first();
            if ($employee) {
                $shift = ManageShift::where('shift_code', $employee->shift_code)->first();
                
                if ($shift) {
                    // Create AttendanceEmployee entry
                    AttendanceEmployee::create([
                        'employee_id' => $wfh->employee_id,
                        'date' => $startDate->format('Y-m-d'),
                        'status' => 'Present',
                        'clock_in' => $shift->start_time,
                        'clock_out' => $shift->end_time,
                        'late' => '00:00:00',
                        'early_leaving' => '00:00:00',
                        'overtime' => '00:00:00',
                        'total_rest' => '00:00:00',
                        'manually_data' => 1,
                    ]);
                }
            }
            
            // Move to the next day
            $startDate->addDay();
        }
    }

    // Redirect with success message
    return redirect()->route('wfh.index')->with('success', __('WFH status successfully updated.'));
}

public function destroy($id)
{
    // Find the WFH record by ID
    $wfh = ManageWfh::find($id);
    // dd($wfh->status);
    // Check if the ManageWfh record exists
    if ($wfh) {
        // Delete the WFH record
        $wfh->delete();

        if($wfh->status === "Approved"){
            // dd("gs");
            $startDate = Carbon::parse($wfh->start_date);
            $endDate = Carbon::parse($wfh->end_date);
            
            while ($startDate->lte($endDate)) {

                AttendanceEmployee::where('employee_id', $wfh->employee_id)->where('date', $startDate->format('Y-m-d'))->where('manually_data', 1)->delete();

                $startDate->addDay();
            }


        }
        
        // Redirect with success message
        return redirect()->route('wfh.index')->with('success', __('WFH successfully deleted.'));
    }

    // If the record does not exist, redirect with an error message
    return redirect()->route('wfh.index')->with('error', __('Record not found.'));
}



}
