<?php

namespace App\Http\Controllers;
use App\Models\ManageWeekOff;
use App\Models\Leave as LocalLeave;
use App\Models\Employee;
use App\Models\Utility;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;

class WeekOffController extends Controller
{
    public function index()
    {

        if (\Auth::user()->can('Manage Weekoff')) {
            if (\Auth::user()->type == 'employee') {
                // $user     = \Auth::user();
                // dd($user->id);
                // $employee = Employee::where('user_id', '=', $user->id)->first();
                // dd($employee->id);
                $weekoffs   = ManageWeekOff::where('employee_id', '=', \Auth::user()->id)->get();
            } else {
                // dd("elseee");
                $weekoffs = ManageWeekOff::with(['employees'])->get();
            }

            return view('weekoff.index', compact('weekoffs'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    // public function store(Request $request)
    // { 
    //     if (\Auth::user()->can('Create Weekoff')) {
    //         $validator = \Validator::make(
    //             $request->all(),
    //             [
    //                 'week_off_date' => 'required',
    //                 'remark' => 'required',
    //             ]
    //         );
    //         if ($validator->fails()) {
    //             $messages = $validator->getMessageBag();

    //             return redirect()->back()->with('error', $messages->first());
    //         }


    //         // $employee = Employee::where('employee_id', '=', \Auth::user()->creatorId())->first();
    //         // $leave_type = LeaveType::find($request->leave_type_id);

    //         $startDate = new \DateTime($request->week_off_date);
    //         // dd($startDate);
    //         // $endDate = new \DateTime($request->end_date);
    //         // $endDate->add(new \DateInterval('P1D'));
    //         // $total_leave_days = !empty($startDate->diff($endDate)) ? $startDate->diff($endDate)->days : 0;
    //         // $date = Utility::AnnualLeaveCycle();
    //         dd(\Auth::user());

    //         if (\Auth::user()->type == 'employee') {
    //          $weeks =   ManageWeekOff::where('id', '=',\Auth::user()->id);
    //             // Leave day
    //             // $leaves_used   = LocalLeave::where('employee_id', '=', $request->employee_id)->where('leave_type_id', $leave_type->id)->where('status', 'Approved')->whereBetween('created_at', [$date['start_date'], $date['end_date']])->sum('total_leave_days');

    //             // $leaves_pending  = LocalLeave::where('employee_id', '=', $request->employee_id)->where('leave_type_id', $leave_type->id)->where('status', 'Pending')->whereBetween('created_at', [$date['start_date'], $date['end_date']])->sum('total_leave_days');
    //         } else {
    //             // Leave day
    //             // $leaves_used   = LocalLeave::where('employee_id', '=', $request->employee_id)->where('leave_type_id', $leave_type->id)->where('status', 'Approved')->whereBetween('created_at', [$date['start_date'], $date['end_date']])->sum('total_leave_days');

    //             // $leaves_pending  = LocalLeave::where('employee_id', '=', $request->employee_id)->where('leave_type_id', $leave_type->id)->where('status', 'Pending')->whereBetween('created_at', [$date['start_date'], $date['end_date']])->sum('total_leave_days');
    //         }

    //         $total_leave_days = !empty($startDate->diff($endDate)) ? $startDate->diff($endDate)->days : 0;

    //         $return = $leave_type->days - $leaves_used;
    //         if ($total_leave_days > $return) {
    //             return redirect()->back()->with('error', __('You are not eligible for leave.'));
    //         }

    //         if (!empty($leaves_pending) && $leaves_pending + $total_leave_days > $return) {
    //             return redirect()->back()->with('error', __('Multiple leave entry is pending.'));
    //         }

    //         if ($leave_type->days >= $total_leave_days) {
    //             $leave    = new LocalLeave();
    //             if (\Auth::user()->type == "employee") {
    //                 $leave->employee_id = $request->employee_id;
    //             } else {
    //                 $leave->employee_id = $request->employee_id;
    //             }
    //             $leave->leave_type_id    = $request->leave_type_id;
    //             $leave->applied_on       = date('Y-m-d');
    //             $leave->start_date       = $request->start_date;
    //             $leave->end_date         = $request->end_date;
    //             $leave->total_leave_days = $total_leave_days;
    //             $leave->leave_reason     = $request->leave_reason;
    //             $leave->remark           = $request->remark;
    //             $leave->status           = 'Pending';
    //             $leave->created_by       = \Auth::user()->creatorId();

    //             $leave->save();

    //             // Google celander
    //             if ($request->get('synchronize_type')  == 'google_calender') {

    //                 $type = 'leave';
    //                 $request1 = new GoogleEvent();
    //                 $request1->title = !empty(\Auth::user()->getLeaveType($leave->leave_type_id)) ? \Auth::user()->getLeaveType($leave->leave_type_id)->title : '';
    //                 $request1->start_date = $request->start_date;
    //                 $request1->end_date = $request->end_date;

    //                 Utility::addCalendarData($request1, $type);
    //             }

    //             return redirect()->route('leave.index')->with('success', __('Leave  successfully created.'));
    //         } else {
    //             return redirect()->back()->with('error', __('Leave type ' . $leave_type->name . ' is provide maximum ' . $leave_type->days . "  days please make sure your selected days is under " . $leave_type->days . ' days.'));
    //         }
    //     } else {
    //         return redirect()->back()->with('error', __('Permission denied.'));
    //     }
    // }

    // public function store(Request $request)
    // { 
    //     // Validate the incoming request data
    //     $validator = \Validator::make($request->all(), [
    //         'week_off_date' => 'required|date',
    //         'remark' => 'required|string',
    //     ]);

    //     if ($validator->fails()) {
    //         // If validation fails, redirect back with error messages
    //         return redirect()->back()->with('error', $validator->errors()->first());
    //     }

    //     // Get the start date of the requested week off
    //     $weekOffDate = new \DateTime($request->week_off_date);

    //     // Determine the start and end of the week for the given date
    //     $startOfWeek = clone $weekOffDate;
    //     $startOfWeek->modify('last sunday')->modify('+1 day');
    //     $endOfWeek = clone $startOfWeek;
    //     $endOfWeek->modify('next sunday');

    //     // Check if the user (employee) already has a week off on the requested day in the same week
    //     $existingWeekOff = ManageWeekOff::where('employee_id', \Auth::user()->id)
    //         ->where('week_off_date', '>=', $startOfWeek->format('Y-m-d'))
    //         ->where('week_off_date', '<', $endOfWeek->format('Y-m-d'))
    //         ->first();

    //     if ($existingWeekOff) {
    //         // If there's already a week off for the same user on the requested day within the same week, return an error
    //         return redirect()->back()->with('error', 'You already have a week off on this day in the current week.');
    //     }

    //     $dayName = date('l', strtotime($weekOffDate->format('Y-m-d')));
    //     // dd( $dayName);
    //     // Create a new ManageWeekOff record for the user
    //     $weekOff = new ManageWeekOff();
    //     $weekOff->employee_id = \Auth::user()->creatorId();
    //     $weekOff->week_off_date = $weekOffDate->format('Y-m-d');
    //     $weekOff->day_name = $dayName;
    //     $weekOff->remark = $request->remark;
    //     $weekOff->status = "Pending";
    //     $weekOff->created_by = \Auth::user()->id;
    //     $weekOff->save();

    //     // Redirect with success message or perform any other necessary actions
    //     return redirect()->back()->with('success', 'Week off request successfully added.');
    // }

    public function store(Request $request)
    {
        // Validate the incoming request data
        $validator = \Validator::make($request->all(), [
            'week_off_date' => 'required|date',
            'remark' => 'required|string',
        ]);
    
        if ($validator->fails()) {
            // If validation fails, redirect back with error messages
            return redirect()->back()->with('error', $validator->errors()->first());
        }
    
        // Get the requested week off date
        $weekOffDate = new \DateTime($request->week_off_date);
    
        // Determine the start and end of the week for the given date
        $startOfWeek = clone $weekOffDate;
        $startOfWeek->modify('last sunday')->modify('+1 day');
        $endOfWeek = clone $startOfWeek;
        $endOfWeek->modify('next sunday');
    
        // Check if the user (employee) already has a week off on any day in the same week
        $existingWeekOff = ManageWeekOff::where('employee_id', \Auth::user()->id)
            ->where('week_off_date', '>=', $startOfWeek->format('Y-m-d'))
            ->where('week_off_date', '<', $endOfWeek->format('Y-m-d'))
            ->exists();
    
        if ($existingWeekOff) {
            // If there's already a week off for the same user within the requested week, return an error
            return redirect()->back()->with('error', 'You already have a week off in the current week.');
        }
    
        // Check if the user (employee) already has a week off on the requested day
        $existingDayOff = ManageWeekOff::where('employee_id', \Auth::user()->id)
            ->where('week_off_date', $weekOffDate->format('Y-m-d'))
            ->exists();
    
        if ($existingDayOff) {
            // If there's already a week off for the same user on the requested day, return an error
            return redirect()->back()->with('error', 'You already have a week off on this day.');
        }
    
        // Create a new ManageWeekOff record for the user
        $dayName = $weekOffDate->format('l'); // Get the day name (e.g., Monday, Tuesday)
        $weekOff = new ManageWeekOff();
        $weekOff->employee_id = \Auth::user()->id;
        $weekOff->week_off_date = $weekOffDate->format('Y-m-d');
        $weekOff->day_name = $dayName;
        $weekOff->remark = $request->remark;
        $weekOff->status = "Pending";
        $weekOff->created_by = \Auth::user()->id;
        $weekOff->save();

        $user     = \Auth::user();
        $employee = Employee::where('user_id', '=', \Auth::user()->id)->first();

        $localLeave = new LocalLeave();
        $localLeave->employee_id = $employee->id;
        $localLeave->leave_type_id = 4;
        $localLeave->weekoff_id = $weekOff->id;
        $localLeave->applied_on = date("Y-m-d");
        $localLeave->start_date = $weekOffDate->format('Y-m-d');
        $localLeave->end_date = $weekOffDate->format('Y-m-d');
        $localLeave->total_leave_days = 1;
        $localLeave->leave_reason = $request->remark;
        $localLeave->remark = $request->remark;
        $localLeave->status = "Pending";
        $localLeave->created_by = \Auth::user()->creatorId(); 
        $localLeave->save();
    
        // Redirect with success message or perform any other necessary actions
        return redirect()->back()->with('success', 'Week off request successfully added.');
    }
    

    public function update(Request $request, $weekoffId)
    { 
        // Validate the incoming request data
        $validator = \Validator::make($request->all(), [
            'employee_id' => 'required',
            'week_off_date' => 'required|date',
            'remark' => 'required|string',
        ]);

        if ($validator->fails()) {
            // If validation fails, redirect back with error messages
            return redirect()->back()->with('error', $validator->errors()->first());
        }
        $weekOff = ManageWeekOff::find($weekoffId);
// dd($request->all());
        // Get the start date of the requested week off
        $weekOffDate = new \DateTime($request->week_off_date);

        // Determine the start and end of the week for the given date
        $startOfWeek = clone $weekOffDate;
        $startOfWeek->modify('last sunday')->modify('+1 day');
        $endOfWeek = clone $startOfWeek;
        $endOfWeek->modify('next sunday');

        // Check if the user (employee) already has a week off on the requested day in the same week
        // $existingWeekOff = ManageWeekOff::where('employee_id', $request->employee_id)
        //     ->where('week_off_date', '>=', $startOfWeek->format('Y-m-d'))
        //     ->where('week_off_date', '<', $endOfWeek->format('Y-m-d'))
        //     ->first();

        // if ($existingWeekOff) {
        //     // If there's already a week off for the same user on the requested day within the same week, return an error
        //     return redirect()->back()->with('error', 'You already have a week off on this day in the current week.');
        // }

        $dayName = date('l', strtotime($weekOffDate->format('Y-m-d')));
        // dd( $dayName);
        // Create a new ManageWeekOff record for the user
        $weekOff->employee_id = $request->employee_id;
        $weekOff->week_off_date = $weekOffDate->format('Y-m-d');
        $weekOff->day_name = $dayName;
        $weekOff->remark = $request->remark;
        $weekOff->status = "Pending";
        $weekOff->created_by = $request->employee_id;
        $weekOff->save();


        if($weekOff->id){
            $localLeave = LocalLeave::where('employee_id',$request->employee_id)->where('weekoff_id',$weekOff->id)->first();
            
            $localLeave->employee_id = $request->employee_id;
            $localLeave->leave_type_id = 4;
            // $localLeave->applied_on = date("Y-m-d");
            $localLeave->start_date = $weekOffDate->format('Y-m-d');
            $localLeave->end_date = $weekOffDate->format('Y-m-d');
            $localLeave->total_leave_days = 1;
            $localLeave->leave_reason = $request->remark;
            $localLeave->remark = $request->remark;
            $localLeave->status = "Pending";
            $localLeave->created_by = $request->employee_id;
            $localLeave->save();
        }

        // Redirect with success message or perform any other necessary actions
        return redirect()->back()->with('success', 'Week off request successfully updated.');
    }

    public function action($id)
    {   
        $weekoff     = ManageWeekOff::find($id);
        $employee  = Employee::find($weekoff->employee_id);
        // dd($employee);
        // $leavetype = LeaveType::find($leave->leave_type_id);

        return view('weekoff.action', compact('employee', 'weekoff'));
    }

    public function changeaction(Request $request)
    {
        $weekoff = ManageWeekOff::find($request->weekoff_id);
        $weekoff->status = $request->status;
        // dd($request->weekoff_id);
        if($request->weekoff_id){
            $localLeave = LocalLeave::where('weekoff_id',$request->weekoff_id)->first();
            // dd($request->status);

            if ($weekoff->status == 'Approved') {
                // dd("1");
                $localLeave->updated_at = Carbon::now();
                $localLeave->status           = 'Approved';
            }elseif ($weekoff->status == 'Reject'){
                // dd("2");
                $localLeave->updated_at = Carbon::now();
                $localLeave->status           = 'Reject';
            }else{
                // dd("3");
                $localLeave->updated_at = Carbon::now();
                $localLeave->status           = 'Pending';
            }

            $localLeave->save();

        }
            if ($weekoff->status == 'Approved') {
                $weekoff->updated_at = Carbon::now();
                $weekoff->status           = 'Approved';
            }elseif ($weekoff->status == 'Reject'){
                $weekoff->updated_at = Carbon::now();
                $weekoff->status           = 'Reject';
            }else{
                $weekoff->updated_at = Carbon::now();
                $weekoff->status           = 'Pending';
            }
    
            $weekoff->save();
        


        // twilio  
        // $setting = Utility::settings(\Auth::user()->creatorId());
        // $emp = Employee::find($leave->employee_id);
        // if (isset($setting['twilio_leave_approve_notification']) && $setting['twilio_leave_approve_notification'] == 1) {
        //     //    $msg = __("Your leave has been").' '.$leave->status.'.';

        //     $uArr = [
        //         'leave_status' => $leave->status,
        //     ];

        //     Utility::send_twilio_msg($emp->phone, 'leave_approve_reject', $uArr);
        // }

        // $setings = Utility::settings();
        // if ($setings['leave_status'] == 1) {
        //     $employee     = Employee::where('id', $leave->employee_id)->where('created_by', '=', \Auth::user()->creatorId())->first();
        //     $uArr = [
        //         'leave_status_name' => $employee->name,
        //         'leave_status' => $request->status,
        //         'leave_reason' => $leave->leave_reason,
        //         'leave_start_date' => $leave->start_date,
        //         'leave_end_date' => $leave->end_date,
        //         'total_leave_days' => $leave->total_leave_days,


        //     ];
        //     $resp = Utility::sendEmailTemplate('leave_status', [$employee->email], $uArr);
        //     return redirect()->route('weekoff.index')->with('success', __('Leave status successfully updated.') . ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
        // }

        return redirect()->route('weekoff.index')->with('success', __('Leave status successfully updated.'));
    }

    public function edit(ManageWeekOff $weekoff)
    {
        if (\Auth::user()->can('Edit Weekoff')) {
            // dd($weekoff->created_by,\Auth::user()->creatorId());
            // if ($weekoff->created_by == \Auth::user()->creatorId()) {
                if (Auth::user()->type == 'employee') {
                    $employees = Employee::where('employee_id', '=', \Auth::user()->creatorId())->first();;
                } else {
                    $employees = Employee::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                }

                // $employees  = Employee::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                // $leavetypes = LeaveType::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('title', 'id');
                // $leavetypes      = LeaveType::where('created_by', '=', \Auth::user()->creatorId())->get();

                return view('weekoff.edit', compact('weekoff', 'employees'));
            // } else {
            //     return response()->json(['error' => __('Permission denied.')], 401);
            // }
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }


    public function destroy(ManageWeekOff $weekoff)
    {
        if (\Auth::user()->can('Delete Weekoff')) {
            // if ($weekoff->created_by == \Auth::user()->creatorId()) {
            // dd($weekoff->id);
            if($weekoff->id){
                // dd("if");
                $localLeave = LocalLeave::where('weekoff_id', $weekoff->id)->first();

                // Check if the record exists
                if ($localLeave) {
                    $localLeave->delete();
                }

                $weekoff->delete();
                    

                
            }else{   
                $weekoff->delete();
            }

            return redirect()->route('weekoff.index')->with('success', __('Weekoff successfully deleted.'));

        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


}
