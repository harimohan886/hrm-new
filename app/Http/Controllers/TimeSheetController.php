<?php

namespace App\Http\Controllers;

use App\Exports\TimesheetExport;
use App\Imports\EmployeeImport;
use App\Imports\TimesheetImport;
use App\Models\Employee;
use App\Models\TimeSheet;
use App\Models\TimeSheet2;
use App\Models\ManageShift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class TimeSheetController extends Controller
{
    // public function index(Request $request)
    // {
    //     if(\Auth::user()->can('Manage TimeSheet'))
    //     {
    //         $employeesList = [];
    //         if(\Auth::user()->type == 'employee')
    //         {
    //             $timeSheets = TimeSheet::where('employee_id', \Auth::user()->id)->get();

    //             $employeesList = Employee::where('created_by', \Auth::user()->creatorId())->first();

    //             $timesheets = TimeSheet::where('created_by', \Auth::user()->creatorId());
    //             if(!empty($request->start_date) && !empty($request->end_date))
    //             {
    //                 $timesheets->where('date', '>=', $request->start_date);
    //                 $timesheets->where('date', '<=', $request->end_date);
    //             }

    //             if(!empty($employeesList->user_id))
    //             {
    //                 $timesheets->where('employee_id', \Auth::user()->id);
    //             }
    //             $timeSheets = $timesheets->get();

    //         }
    //         else
    //         {
    //             $employeesList = Employee::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'user_id');
    //             $employeesList->prepend('All', '');

    //             $timesheets = TimeSheet::where('created_by', \Auth::user()->creatorId());

    //             if(!empty($request->start_date) && !empty($request->end_date))
    //             {
    //                 $timesheets->where('date', '>=', $request->start_date);
    //                 $timesheets->where('date', '<=', $request->end_date);
    //             }

    //             if(!empty($request->employee))
    //             {
    //                 $timesheets->where('employee_id', $request->employee);
    //             }
    //             $timeSheets = $timesheets->get();
    //         }

    //         return view('timeSheet.index', compact('timeSheets', 'employeesList'));
    //     }
    //     else
    //     {
    //         return redirect()->back()->with('error', 'Permission denied.');
    //     }
    // }

    public function index(Request $request)
    {
        if(\Auth::user()->can('Manage TimeSheet'))
        {
           
            $timeSheet = Timesheet2::first(); 
            $shifts = ManageShift::all(); 

            return view('timeSheet.index2', compact('timeSheet','shifts'));
        }
        else
        {
            return redirect()->back()->with('error', 'Permission denied.');
        }
    }

    public function create()
    {

        if(\Auth::user()->can('Create TimeSheet'))
        {
            $employees = Employee::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'user_id');

            return view('timeSheet.create', compact('employees'));
        }
        else
        {
            return redirect()->back()->with('error', 'Permission denied.');
        }
    }

    // public function store(Request $request)
    // {
    //     if(\Auth::user()->can('Create TimeSheet'))
    //     {
    //         $timeSheet = new Timesheet();
    //         if(\Auth::user()->type == 'employee')
    //         {
    //             $timeSheet->employee_id = \Auth::user()->id;
    //         }
    //         else
    //         {
    //             $timeSheet->employee_id = $request->employee_id;
    //         }

    //         $timeSheetCheck = TimeSheet::where('date', $request->date)->where('employee_id', $timeSheet->employee_id)->first();

    //         if(!empty($timeSheetCheck))
    //         {
    //             return redirect()->back()->with('error', __('Timesheet already created in this day.'));
    //         }

    //         $timeSheet->date       = $request->date;
    //         $timeSheet->hours      = $request->hours;
    //         $timeSheet->remark     = $request->remark;
    //         $timeSheet->created_by = \Auth::user()->creatorId();
    //         $timeSheet->save();

    //         return redirect()->route('timesheet.index')->with('success', __('Timesheet successfully created.'));
    //     }
    //     else
    //     {
    //         return redirect()->back()->with('error', 'Permission denied.');
    //     }

    // }

    public function store(Request $request)
{
    if (\Auth::user()->can('Create TimeSheet')) {
        // Retrieve all input data
        $inputData = $request->all();

        // Find existing record based on policy_name (assuming policy_name is unique)
        $timeSheet2 = Timesheet2::where('policy_name', $inputData['policy_name'])->first();

        // If record exists, update; otherwise, create new
        if ($timeSheet2) {
            // Update existing record
            $timeSheet2->update($inputData);
            $message = 'Timesheet successfully updated.';
        } else {
            // Create new record
            $timeSheet2 = new Timesheet2($inputData);
            $timeSheet2->save();
            $message = 'Timesheet successfully created.';
        }

        return redirect()->route('timesheet.index')->with('success', __($message));
    } else {
        return redirect()->back()->with('error', 'Permission denied.');
    }
}

public function shiftManage(Request $request)
{
    // Validate incoming request data (if needed)
    
    // Ensure user has permission to create timesheets
    if (\Auth::user()->can('Create TimeSheet')) {
        // Retrieve all input data
        $inputData = $request->all();

        try {
            // Create or update the record
            $manageShift = ManageShift::updateOrCreate(
                ['shift_code' => $inputData['shift_code']], // unique identifier
                $inputData  // data to be updated or inserted
            );

            $message = $manageShift->wasRecentlyCreated ? 'Shift successfully created.' : 'Shift successfully updated.';
            
            return redirect()->route('timesheet.index')->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to process shift.');
        }
    } else {
        return redirect()->back()->with('error', 'Permission denied.');
    }
}

public function deleteShift($id)
{
    try {
        $shift = ManageShift::findOrFail($id);
        $shift->delete();

        return redirect()->route('timesheet.index')->with('success', 'Shift deleted successfully.');
    } catch (\Exception $e) {
        return redirect()->route('timesheet.index')->with('error', 'Failed to delete shift.');
    }
}

public function updateShift(Request $request, $id)
{
    try {
        $shift = ManageShift::findOrFail($id);
        
        // Update shift data
        $shift->shift_code = $request->input('shift_code');
        $shift->shift_name = $request->input('shift_name');
        $shift->start_time = $request->input('start_time');
        $shift->end_time = $request->input('end_time');
        $shift->shift_hours = $request->input('shift_hours');
        $shift->save();

        return redirect()->route('timesheet.index')->with('success', 'Shift updated successfully.');
    } catch (\Exception $e) {
        return redirect()->route('timesheet.index')->with('error', 'Failed to update shift.');
    }
}

    public function show(TimeSheet $timeSheet)
    {
        //
    }

    public function edit(TimeSheet $timeSheet, $id)
    {

        if(\Auth::user()->can('Edit TimeSheet'))
        {
            $employees = Employee::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'user_id');
            $timeSheet = Timesheet::find($id);

            return view('timeSheet.edit', compact('timeSheet', 'employees'));
        }
        else
        {
            return redirect()->back()->with('error', 'Permission denied.');
        }
    }

    public function update(Request $request, $id)
    {
        if(\Auth::user()->can('Edit TimeSheet'))
        {

            $timeSheet = Timesheet::find($id);
            if(\Auth::user()->type == 'employee')
            {
                $timeSheet->employee_id = \Auth::user()->id;
            }
            else
            {
                $timeSheet->employee_id = $request->employee_id;
            }

            $timeSheetCheck = TimeSheet::where('date', $request->date)->where('employee_id', $timeSheet->employee_id)->first();

            if(!empty($timeSheetCheck) && $timeSheetCheck->id != $id)
            {
                return redirect()->back()->with('error', __('Timesheet already created in this day.'));
            }

            $timeSheet->date   = $request->date;
            $timeSheet->hours  = $request->hours;
            $timeSheet->remark = $request->remark;
            $timeSheet->save();

            return redirect()->route('timesheet.index')->with('success', __('TimeSheet successfully updated.'));
        }
        else
        {
            return redirect()->back()->with('error', 'Permission denied.');
        }
    }

    public function destroy($id)
    {
        if(\Auth::user()->can('Delete TimeSheet'))
        {
            $timeSheet = Timesheet::find($id);
            $timeSheet->delete();

            return redirect()->route('timesheet.index')->with('success', __('TimeSheet successfully deleted.'));
        }
        else
        {
            return redirect()->back()->with('error', 'Permission denied.');
        }
    }

    public function export(Request $request)
    {
        $name = 'Timesheet_' . date('Y-m-d i:h:s');
        $data = Excel::download(new TimesheetExport(), $name . '.xlsx');

        return $data;
    }
    public function importFile(Request $request)
    {
        return view('timeSheet.import');
    }
    public function import(Request $request)
    {
        $rules = [
            'file' => 'required|mimes:csv,txt',
        ];
        $validator = \Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            
            return redirect()->back()->with('error', $messages->first());
        }
        $timesheet = (new TimesheetImport())->toArray(request()->file('file'))[0];
        
        $totalTimesheet = count($timesheet) - 1;
        $errorArray    = [];
        for ($i = 1; $i <= $totalTimesheet; $i++) {
            $timesheets = $timesheet[$i];
            $timesheetData=TimeSheet::where('employee_id',$timesheets[0])->where('date',$timesheets[1])->first();
            
            if(!empty($timesheetData))
            {   
                $errorArray[]=$timesheetData;
            }
            else
            {
                $time_sheet=new TimeSheet();

                $time_sheet->employee_id=$timesheets[0];
                $time_sheet->date=$timesheets[1];
                $time_sheet->hours=$timesheets[2];
                $time_sheet->remark=$timesheets[3];
                $time_sheet->created_by=Auth::user()->id;
                $time_sheet->save();
            }
        }
       
        
        if (empty($errorArray)) {
            $data['status'] = 'success';
            $data['msg']    = __('Record successfully imported');
        } else {
           
            $data['status'] = 'error';
            $data['msg']    = count($errorArray) . ' ' . __('Record imported fail out of' . ' ' . $totalTimesheet . ' ' . 'record');

            foreach ($errorArray as $errorData) {
                $errorRecord[] = implode(',', $errorData->toArray());
            }
            
            \Session::put('errorArray', $errorRecord);
        }

        return redirect()->back()->with($data['status'], $data['msg']);
    }
}
