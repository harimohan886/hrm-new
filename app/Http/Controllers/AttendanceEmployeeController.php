<?php

namespace App\Http\Controllers;

use App\Imports\AttendanceImport;
use App\Models\AttendanceEmployee;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\ManageShift;
use DateTime;
use App\Models\IpRestrict;
use App\Models\User;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceEmployeeController extends Controller
{
    public function index(Request $request)
    {
        if(\Auth::user()->can('Manage Attendance'))
        {   
            // $attendanceEmployee = AttendanceEmployee::get();
            // dd($attendanceEmployee);
            $branch = Branch::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $branch->prepend('Select Branch', '');

            $department = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $department->prepend('All', '');

            if(\Auth::user()->type == 'employee')
            {

                $emp = !empty(\Auth::user()->employee) ? \Auth::user()->employee->id : 0;

                $attendanceEmployee = AttendanceEmployee::where('employee_id', $emp);

                $date = $request->input('date');
                if ($date) {
                    $attendanceEmployee->whereDate('date', $date);
                }

                if ($request->filled('employee')) {
                    $employeeId = $request->input('employee');
                    $attendanceEmployee->where('employee_id', $employeeId);
                }

                
                if ($request->filled('start_date') && $request->filled('end_date')) {
                    $startDate = $request->input('start_date');
                    $endDate = $request->input('end_date');
                    $attendanceEmployee->whereBetween('date', [$startDate, $endDate]);
                }

                $attendanceEmployee = $attendanceEmployee->orderBy('updated_at', 'desc')->get();

            }
            else
            {   
                $employee = Employee::select('id')->where('created_by', \Auth::user()->creatorId());
                // dd($employee );
               
                $employee = $employee->get()->pluck('id');

                $attendanceEmployee = AttendanceEmployee::whereIn('employee_id', $employee);

                $date = $request->input('date');
                if ($date) {
                    $attendanceEmployee->whereDate('date', $date);
                }

                if ($request->filled('employee')) {
                    $employeeId = $request->input('employee');
                    // dd($employeeId);
                    //$emp =  Employee::where('user_id',$employeeId)->first();
                    // dd($emp->id);
                    $attendanceEmployee->where('employee_id',  $employeeId);
                }

                if ($request->filled('start_date') && $request->filled('end_date')) {
                    $startDate = $request->input('start_date');
                    $endDate = $request->input('end_date');
                    $attendanceEmployee->whereBetween('date', [$startDate, $endDate]);
                }


                $attendanceEmployee = $attendanceEmployee->orderBy('updated_at', 'desc')->get();
                // dd($attendanceEmployee);
            }

            $created_by = Auth::user()->creatorId();
            $employee_option = User::where('created_by', $created_by)->whereNotIn('type', ['company','hr'])->pluck('name', 'id');

            $objUser = \Auth::user();
            $usersList = Employee::where('created_by', $objUser->creatorId())
                // ->whereNotIn('type', ['super admin', 'company'])
                ->get()
                ->pluck('name', 'id');
            $usersList->prepend('All', '');

            // dd($attendanceEmployee);

            return view('attendance.index', compact('attendanceEmployee', 'branch', 'department','employee_option','usersList'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

//     public function index(Request $request)
// {
//     if (\Auth::user()->can('Manage Attendance'))
//     {
//         // Get the branch and department options
//         $branch = Branch::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
//         $branch->prepend('Select Branch', '');

//         $department = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
//         $department->prepend('All', '');

//         // Fetch attendance employee records without applying any filters
//         if (\Auth::user()->type == 'employee')
//         {
//             $emp = !empty(\Auth::user()->employee) ? \Auth::user()->employee->id : 0;

//             // Get all attendance records for the employee
//             $attendanceEmployee = AttendanceEmployee::where('employee_id', $emp)
//                 ->orderBy('updated_at', 'desc')
//                 ->get();
//         }
//         else
//         {
//             // Get all employees for the current user
//             $employeeIds = Employee::select('id')
//                 ->where('created_by', \Auth::user()->creatorId())
//                 ->get()
//                 ->pluck('id');

//             // Get all attendance records for the selected employees
//             $attendanceEmployee = AttendanceEmployee::whereIn('employee_id', $employeeIds)
//                 ->orderBy('updated_at', 'desc')
//                 ->get();
//         }

//         // Fetch employee options for the view
//         $created_by = \Auth::user()->creatorId();
//         $employee_option = User::where('created_by', $created_by)
//             ->whereNotIn('type', ['company', 'hr'])
//             ->pluck('name', 'id');

//         $usersList = Employee::where('created_by', \Auth::user()->creatorId())
//             ->get()
//             ->pluck('name', 'id');
//         $usersList->prepend('All', '');

//         // Return the view with the fetched data
//         return view('attendance.index', compact('attendanceEmployee', 'branch', 'department', 'employee_option', 'usersList'));
//     }
//     else
//     {
//         return redirect()->back()->with('error', __('Permission denied.'));
//     }
// }


    public function create()
    {
        if(\Auth::user()->can('Create Attendance'))
        {
            $employees = User::where('created_by', '=', Auth::user()->creatorId())->where('type', '=', "employee")->get()->pluck('name', 'id');

            return view('attendance.create', compact('employees'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }


    }

    public function store(Request $request)
    {
        if(\Auth::user()->can('Create Attendance'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'employee_id' => 'required',
                                   'date' => 'required',
                                   'clock_in' => 'required',
                                   'clock_out' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $startTime  = Utility::getValTimings('company_start_time',$request->employee_id);
            $endTime    = Utility::getValTimings('company_end_time',$request->employee_id);
            $attendance = AttendanceEmployee::where('employee_id', '=', $request->employee_id)->where('date', '=', $request->date)->where('clock_out', '=', '00:00:00')->get()->toArray();
            if($attendance)
            {
                return redirect()->route('attendanceemployee.index')->with('error', __('Employee Attendance Already Created.'));
            }
            else
            {
                $date = date("Y-m-d");

                $totalLateSeconds = strtotime($request->clock_in) - strtotime($date . $startTime);

                $hours = floor($totalLateSeconds / 3600);
                $mins  = floor($totalLateSeconds / 60 % 60);
                $secs  = floor($totalLateSeconds % 60);
                $late  = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

                //early Leaving
                $totalEarlyLeavingSeconds = strtotime($date . $endTime) - strtotime($request->clock_out);
                $hours                    = floor($totalEarlyLeavingSeconds / 3600);
                $mins                     = floor($totalEarlyLeavingSeconds / 60 % 60);
                $secs                     = floor($totalEarlyLeavingSeconds % 60);
                $earlyLeaving             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);


                if(strtotime($request->clock_out) > strtotime($date . $endTime))
                {
                    //Overtime
                    $totalOvertimeSeconds = strtotime($request->clock_out) - strtotime($date . $endTime);
                    $hours                = floor($totalOvertimeSeconds / 3600);
                    $mins                 = floor($totalOvertimeSeconds / 60 % 60);
                    $secs                 = floor($totalOvertimeSeconds % 60);
                    $overtime             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
                }
                else
                {
                    $overtime = '00:00:00';
                }

                $employeeAttendance                = new AttendanceEmployee();
                $employeeAttendance->employee_id   = $request->employee_id;
                $employeeAttendance->date          = $request->date;
                $employeeAttendance->status        = 'Present';
                $employeeAttendance->clock_in      = $request->clock_in . ':00';
                $employeeAttendance->clock_out     = $request->clock_out . ':00';
                $employeeAttendance->late          = $late;
                $employeeAttendance->early_leaving = $earlyLeaving;
                $employeeAttendance->overtime      = $overtime;
                $employeeAttendance->total_rest    = '00:00:00';
                $employeeAttendance->created_by    = \Auth::user()->creatorId();
                $employeeAttendance->save();

                return redirect()->route('attendanceemployee.index')->with('success', __('Employee attendance successfully created.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    public function show(Request $request)
    {
        // return redirect()->back();
        return redirect()->route('attendanceemployee.index');
    }

    public function edit($id)
    {
        if(\Auth::user()->can('Edit Attendance'))
        {
            $attendanceEmployee = AttendanceEmployee::where('id', $id)->first();
            $employees          = Employee::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');

            return view('attendance.edit', compact('attendanceEmployee', 'employees'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    // public function update(Request $request, $id)
    // {
    //     if (\Auth::user()->type == 'company' || \Auth::user()->type == 'hr') {
    //         $employeeId      = AttendanceEmployee::where('employee_id', $request->employee_id)->first();
    //         $check = AttendanceEmployee::where('employee_id', '=', $request->employee_id)->where('date', $request->date)->first();
            
    //         $startTime = Utility::getValTimings('company_start_time',$request->employee_id);
    //         $endTime   = Utility::getValTimings('company_end_time',$request->employee_id);
            
    //         $clockIn = $request->clock_in;
    //         $clockOut = $request->clock_out;
            
    //         if ($clockIn) {
    //             $status = "present";
    //         } else {
    //             $status = "leave";
    //         }
            
    //         $totalLateSeconds = strtotime($clockIn) - strtotime($startTime);

    //         $hours = floor($totalLateSeconds / 3600);
    //         $mins  = floor($totalLateSeconds / 60 % 60);
    //         $secs  = floor($totalLateSeconds % 60);
    //         $late  = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

    //         $totalEarlyLeavingSeconds = strtotime($endTime) - strtotime($clockOut);
    //         $hours                    = floor($totalEarlyLeavingSeconds / 3600);
    //         $mins                     = floor($totalEarlyLeavingSeconds / 60 % 60);
    //         $secs                     = floor($totalEarlyLeavingSeconds % 60);
    //         $earlyLeaving             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

    //         if (strtotime($clockOut) > strtotime($endTime)) {
    //             //Overtime
    //             $totalOvertimeSeconds = strtotime($clockOut) - strtotime($endTime);
    //             $hours                = floor($totalOvertimeSeconds / 3600);
    //             $mins                 = floor($totalOvertimeSeconds / 60 % 60);
    //             $secs                 = floor($totalOvertimeSeconds % 60);
    //             $overtime             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
    //         } else {
    //             $overtime = '00:00:00';
    //         }
    //         if ($check->date == date('Y-m-d')) {
    //             $check->update([
    //                 'late' => $late,
    //                 'early_leaving' => ($earlyLeaving > 0) ? $earlyLeaving : '00:00:00',
    //                 'overtime' => $overtime,
    //                 'clock_in' => $clockIn,
    //                 'clock_out' => $clockOut
    //             ]);

    //             return redirect()->route('attendanceemployee.index')->with('success', __('Employee attendance successfully updated.'));
    //         } else {
    //             return redirect()->route('attendanceemployee.index')->with('error', __('You can only update current day attendance'));
    //         }
    //     }

    //     $employeeId      = !empty(\Auth::user()->employee) ? \Auth::user()->employee->id : 0;
    //     $todayAttendance = AttendanceEmployee::where('employee_id', '=', $employeeId)->where('date', date('Y-m-d'))->first();
    //     if(!empty($todayAttendance) && $todayAttendance->clock_out == '00:00:00')
    //     {
    //         $startTime = Utility::getValTimings('company_start_time',$employeeId);
    //         $endTime   = Utility::getValTimings('company_end_time',$employeeId);
    //         if(Auth::user()->type == 'employee')
    //         {

    //             $date = date("Y-m-d");
    //             $time = date("H:i:s");

    //             //early Leaving
    //             $totalEarlyLeavingSeconds = strtotime($date . $endTime) - time();
    //             $hours                    = floor($totalEarlyLeavingSeconds / 3600);
    //             $mins                     = floor($totalEarlyLeavingSeconds / 60 % 60);
    //             $secs                     = floor($totalEarlyLeavingSeconds % 60);
    //             $earlyLeaving             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

    //             if(time() > strtotime($date . $endTime))
    //             {
    //                 //Overtime
    //                 $totalOvertimeSeconds = time() - strtotime($date . $endTime);
    //                 $hours                = floor($totalOvertimeSeconds / 3600);
    //                 $mins                 = floor($totalOvertimeSeconds / 60 % 60);
    //                 $secs                 = floor($totalOvertimeSeconds % 60);
    //                 $overtime             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
    //             }
    //             else
    //             {
    //                 $overtime = '00:00:00';
    //             }

    //             $attendanceEmployee                = AttendanceEmployee::find($id);
    //             $attendanceEmployee->clock_out     = $time;
    //             $attendanceEmployee->early_leaving = $earlyLeaving;
    //             $attendanceEmployee->overtime      = $overtime;
    //             $attendanceEmployee->save();

    //             return redirect()->route('home')->with('success', __('Employee successfully clock Out.'));
    //         }
    //         else
    //         {
    //             $date = date("Y-m-d");
    //             //late
    //             $totalLateSeconds = strtotime($request->clock_in) - strtotime($date . $startTime);

    //             $hours = floor($totalLateSeconds / 3600);
    //             $mins  = floor($totalLateSeconds / 60 % 60);
    //             $secs  = floor($totalLateSeconds % 60);
    //             $late  = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

    //             //early Leaving
    //             $totalEarlyLeavingSeconds = strtotime($date . $endTime) - strtotime($request->clock_out);
    //             $hours                    = floor($totalEarlyLeavingSeconds / 3600);
    //             $mins                     = floor($totalEarlyLeavingSeconds / 60 % 60);
    //             $secs                     = floor($totalEarlyLeavingSeconds % 60);
    //             $earlyLeaving             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);


    //             if(strtotime($request->clock_out) > strtotime($date . $endTime))
    //             {
    //                 //Overtime
    //                 $totalOvertimeSeconds = strtotime($request->clock_out) - strtotime($date . $endTime);
    //                 $hours                = floor($totalOvertimeSeconds / 3600);
    //                 $mins                 = floor($totalOvertimeSeconds / 60 % 60);
    //                 $secs                 = floor($totalOvertimeSeconds % 60);
    //                 $overtime             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
    //             }
    //             else
    //             {
    //                 $overtime = '00:00:00';
    //             }

    //             $attendanceEmployee                = AttendanceEmployee::find($id);
    //             $attendanceEmployee->employee_id   = $request->employee_id;
    //             $attendanceEmployee->date          = $request->date;
    //             $attendanceEmployee->clock_in      = $request->clock_in;
    //             $attendanceEmployee->clock_out     = $request->clock_out;
    //             $attendanceEmployee->late          = $late;
    //             $attendanceEmployee->early_leaving = $earlyLeaving;
    //             $attendanceEmployee->overtime      = $overtime;
    //             $attendanceEmployee->total_rest    = '00:00:00';

    //             $attendanceEmployee->save();

    //             return redirect()->route('attendanceemployee.index')->with('success', __('Employee attendance successfully updated.'));
    //         }
    //     }
    //     else
    //     {
    //         return redirect()->back()->with('error', __('Employee are not allow multiple time clock in & clock for every day.'));
    //     }
    // }

    public function update(Request $request, $id)
    {
        if (\Auth::user()->type == 'company' || \Auth::user()->type == 'hr') {
            $employeeId      = AttendanceEmployee::where('employee_id', $request->employee_id)->first();
            $check = AttendanceEmployee::where('id', '=', $id)->where('employee_id', '=', $request->employee_id)->where('date', $request->date)->first();

            $startTime = Utility::getValTimings('company_start_time',$request->employee_id);
            $endTime   = Utility::getValTimings('company_end_time',$request->employee_id);

            $clockIn = $request->clock_in;
            $clockOut = $request->clock_out;

            if ($clockIn) {
                $status = "present";
            } else {
                $status = "leave";
            }

            $totalLateSeconds = strtotime($clockIn) - strtotime($startTime);

            $hours = floor($totalLateSeconds / 3600);
            $mins  = floor($totalLateSeconds / 60 % 60);
            $secs  = floor($totalLateSeconds % 60);
            $late  = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

            $totalEarlyLeavingSeconds = strtotime($endTime) - strtotime($clockOut);
            $hours                    = floor($totalEarlyLeavingSeconds / 3600);
            $mins                     = floor($totalEarlyLeavingSeconds / 60 % 60);
            $secs                     = floor($totalEarlyLeavingSeconds % 60);
            $earlyLeaving             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

            if (strtotime($clockOut) > strtotime($endTime)) {
                //Overtime
                $totalOvertimeSeconds = strtotime($clockOut) - strtotime($endTime);
                $hours                = floor($totalOvertimeSeconds / 3600);
                $mins                 = floor($totalOvertimeSeconds / 60 % 60);
                $secs                 = floor($totalOvertimeSeconds % 60);
                $overtime             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
            } else {
                $overtime = '00:00:00';
            }
            // if ($check->date == date('Y-m-d')) {
                $check->update([
                    'late' => $late,
                    'early_leaving' => ($earlyLeaving > 0) ? $earlyLeaving : '00:00:00',
                    'overtime' => $overtime,
                    'clock_in' => $clockIn,
                    'clock_out' => $clockOut
                ]);

                return redirect()->route('attendanceemployee.index')->with('success', __('Employee attendance successfully updated.'));
            // } else {
            //     return redirect()->route('attendanceemployee.index')->with('error', __('You can only update current day attendance.'));
            // }
        }

        $employeeId      = !empty(\Auth::user()->employee) ? \Auth::user()->employee->id : 0;
        $todayAttendance = AttendanceEmployee::where('employee_id', '=', $employeeId)->where('date', date('Y-m-d'))->first();

        $startTime = Utility::getValTimings('company_start_time',$employeeId);
        $endTime   = Utility::getValTimings('company_end_time',$employeeId);
        if (Auth::user()->type == 'employee') {

            $date = date("Y-m-d");
            $time = date("H:i:s");

            //early Leaving
            $totalEarlyLeavingSeconds = strtotime($date . $endTime) - time();
            $hours                    = floor($totalEarlyLeavingSeconds / 3600);
            $mins                     = floor($totalEarlyLeavingSeconds / 60 % 60);
            $secs                     = floor($totalEarlyLeavingSeconds % 60);
            $earlyLeaving             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

            if (time() > strtotime($date . $endTime)) {
                //Overtime
                $totalOvertimeSeconds = time() - strtotime($date . $endTime);
                $hours                = floor($totalOvertimeSeconds / 3600);
                $mins                 = floor($totalOvertimeSeconds / 60 % 60);
                $secs                 = floor($totalOvertimeSeconds % 60);
                $overtime             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
            } else {
                $overtime = '00:00:00';
            }

            $attendanceEmployee['clock_out']     = $time;
            $attendanceEmployee['early_leaving'] = $earlyLeaving;
            $attendanceEmployee['overtime']      = $overtime;

            if (!empty($request->date)) {
                $attendanceEmployee['date']       =  $request->date;
            }
            AttendanceEmployee::where('id', $id)->update($attendanceEmployee);

            return redirect()->route('dashboard')->with('success', __('Employee successfully clock Out.'));
        } else {
            $date = date("Y-m-d");
            $clockout_time = date("H:i:s");
            //late
            $totalLateSeconds = strtotime($clockout_time) - strtotime($date . $startTime);

            $hours            = abs(floor($totalLateSeconds / 3600));
            $mins             = abs(floor($totalLateSeconds / 60 % 60));
            $secs             = abs(floor($totalLateSeconds % 60));

            $late  = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

            //early Leaving
            $totalEarlyLeavingSeconds = strtotime($date . $endTime) - strtotime($clockout_time);
            $hours                    = floor($totalEarlyLeavingSeconds / 3600);
            $mins                     = floor($totalEarlyLeavingSeconds / 60 % 60);
            $secs                     = floor($totalEarlyLeavingSeconds % 60);
            $earlyLeaving             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);


            if (strtotime($clockout_time) > strtotime($date . $endTime)) {
                //Overtime
                $totalOvertimeSeconds = strtotime($clockout_time) - strtotime($date . $endTime);
                $hours                = floor($totalOvertimeSeconds / 3600);
                $mins                 = floor($totalOvertimeSeconds / 60 % 60);
                $secs                 = floor($totalOvertimeSeconds % 60);
                $overtime             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
            } else {
                $overtime = '00:00:00';
            }

            $attendanceEmployee                = AttendanceEmployee::find($id);
            $attendanceEmployee->clock_out     = $clockout_time;
            $attendanceEmployee->late          = $late;
            $attendanceEmployee->early_leaving = $earlyLeaving;
            $attendanceEmployee->overtime      = $overtime;
            $attendanceEmployee->total_rest    = '00:00:00';

            $attendanceEmployee->save();

            return redirect()->back()->with('success', __('Employee attendance successfully updated.'));
        }
    }

    public function destroy($id)
    {
        if(\Auth::user()->can('Delete Attendance'))
        {
            $attendance = AttendanceEmployee::where('id', $id)->first();

            $attendance->delete();

            return redirect()->route('attendanceemployee.index')->with('success', __('Attendance successfully deleted.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    // public function attendance(Request $request)
    // {
    //     $settings = Utility::settings();

    //     if($settings['ip_restrict'] == 'on')
    //     {
    //         $userIp = request()->ip();
    //         $ip     = IpRestrict::where('created_by', \Auth::user()->creatorId())->whereIn('ip', [$userIp])->first();
    //         if(!empty($ip))
    //         {
    //             return redirect()->back()->with('error', __('this ip is not allowed to clock in & clock out.'));
    //         }
    //     }

    //     $employeeId      = !empty(\Auth::user()->employee) ? \Auth::user()->employee->id : 0;
    //     $todayAttendance = AttendanceEmployee::where('employee_id', '=', $employeeId)->where('date', date('Y-m-d'))->first();
    //     if(empty($todayAttendance))
    //     {

    //         $startTime = Utility::getValTimings('company_start_time',$employeeId);
    //         $endTime   = Utility::getValTimings('company_end_time',$employeeId);

    //         $attendance = AttendanceEmployee::orderBy('id', 'desc')->where('employee_id', '=', $employeeId)->where('clock_out', '=', '00:00:00')->first();

    //         if($attendance != null)
    //         {
    //             $attendance            = AttendanceEmployee::find($attendance->id);
    //             $attendance->clock_out = $endTime;
    //             $attendance->save();
    //         }

    //         $date = date("Y-m-d");
    //         $time = date("H:i:s");

    //         //late
    //         $totalLateSeconds = time() - strtotime($date . $startTime);
    //         $hours            = floor($totalLateSeconds / 3600);
    //         $mins             = floor($totalLateSeconds / 60 % 60);
    //         $secs             = floor($totalLateSeconds % 60);
    //         $late             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

    //         $checkDb = AttendanceEmployee::where('employee_id', '=', \Auth::user()->id)->get()->toArray();


    //         if(empty($checkDb))
    //         {
    //             $employeeAttendance                = new AttendanceEmployee();
    //             $employeeAttendance->employee_id   = $employeeId;
    //             $employeeAttendance->date          = $date;
    //             $employeeAttendance->status        = 'Present';
    //             $employeeAttendance->clock_in      = $time;
    //             $employeeAttendance->clock_out     = '00:00:00';
    //             $employeeAttendance->late          = $late;
    //             $employeeAttendance->early_leaving = '00:00:00';
    //             $employeeAttendance->overtime      = '00:00:00';
    //             $employeeAttendance->total_rest    = '00:00:00';
    //             $employeeAttendance->created_by    = \Auth::user()->id;

    //             $employeeAttendance->save();

    //             return redirect()->route('home')->with('success', __('Employee Successfully Clock In.'));
    //         }
    //         foreach($checkDb as $check)
    //         {


    //             $employeeAttendance                = new AttendanceEmployee();
    //             $employeeAttendance->employee_id   = $employeeId;
    //             $employeeAttendance->date          = $date;
    //             $employeeAttendance->status        = 'Present';
    //             $employeeAttendance->clock_in      = $time;
    //             $employeeAttendance->clock_out     = '00:00:00';
    //             $employeeAttendance->late          = $late;
    //             $employeeAttendance->early_leaving = '00:00:00';
    //             $employeeAttendance->overtime      = '00:00:00';
    //             $employeeAttendance->total_rest    = '00:00:00';
    //             $employeeAttendance->created_by    = \Auth::user()->id;

    //             $employeeAttendance->save();

    //             return redirect()->route('home')->with('success', __('Employee Successfully Clock In.'));

    //         }
    //     }
    //     else
    //     {
    //         return redirect()->back()->with('error', __('Employee are not allow multiple time clock in & clock for every day.'));
    //     }
    // }

    public function attendance(Request $request)
    {
        $settings = Utility::settings();

        if (!empty($settings['ip_restrict']) && $settings['ip_restrict'] == 'on') {
            $userIp = request()->ip();
            $ip     = IpRestrict::where('created_by', Auth::user()->creatorId())->whereIn('ip', [$userIp])->first();
            if (empty($ip)) {
                return redirect()->back()->with('error', __('This IP is not allowed to clock in & clock out.'));
            }
        }

        $employeeId = !empty(\Auth::user()->employee) ? \Auth::user()->employee->id : 0;

        $startTime = Utility::getValTimings('company_start_time',$employeeId);
        $endTime = Utility::getValTimings('company_end_time',$employeeId);

        // Find the last clocked out entry for the employee
        $lastClockOutEntry = AttendanceEmployee::orderBy('id', 'desc')
            ->where('employee_id', '=', $employeeId)
            ->where('clock_out', '!=', '00:00:00')
            ->where('date', '=', date('Y-m-d'))
            ->first();

        $date = date("Y-m-d");
        $time = date("H:i:s");

        if ($lastClockOutEntry != null) {
            // Calculate late based on the difference between the last clock-out time and the current clock-in time
            $lastClockOutTime = $lastClockOutEntry->clock_out;
            $actualClockInTime = $date . ' ' . $time;

            $totalLateSeconds = strtotime($actualClockInTime) - strtotime($date . ' ' . $lastClockOutTime);

            // Ensure late time is non-negative
            $totalLateSeconds = max($totalLateSeconds, 0);

            $hours = abs(floor($totalLateSeconds / 3600));
            $mins = abs(floor($totalLateSeconds / 60 % 60));
            $secs = abs(floor($totalLateSeconds % 60));
            $late = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
        } else {
            // If there is no previous clock-out entry, assume no lateness
            $expectedStartTime = $date . ' ' . $startTime;
            $actualClockInTime = $date . ' ' . $time;

            $totalLateSeconds = strtotime($actualClockInTime) - strtotime($expectedStartTime);

            // Ensure late time is non-negative
            $totalLateSeconds = max($totalLateSeconds, 0);

            $hours = abs(floor($totalLateSeconds / 3600));
            $mins = abs(floor($totalLateSeconds / 60 % 60));
            $secs = abs(floor($totalLateSeconds % 60));
            $late = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
        }

        $checkDb = AttendanceEmployee::where('employee_id', '=', \Auth::user()->id)->get()->toArray();

        if (empty($checkDb)) {
            $employeeAttendance                = new AttendanceEmployee();
            $employeeAttendance->employee_id   = $employeeId;
            $employeeAttendance->date          = $date;
            $employeeAttendance->status        = 'Present';
            $employeeAttendance->clock_in      = $time;
            $employeeAttendance->clock_out     = '00:00:00';
            $employeeAttendance->late          = $late;
            $employeeAttendance->early_leaving = '00:00:00';
            $employeeAttendance->overtime      = '00:00:00';
            $employeeAttendance->total_rest    = '00:00:00';
            $employeeAttendance->created_by    = \Auth::user()->id;

            $employeeAttendance->save();

            return redirect()->back()->with('success', __('Employee Successfully Clock In.'));
        }
        foreach ($checkDb as $check) {

            $employeeAttendance                = new AttendanceEmployee();
            $employeeAttendance->employee_id   = $employeeId;
            $employeeAttendance->date          = $date;
            $employeeAttendance->status        = 'Present';
            $employeeAttendance->clock_in      = $time;
            $employeeAttendance->clock_out     = '00:00:00';
            $employeeAttendance->late          = $late;
            $employeeAttendance->early_leaving = '00:00:00';
            $employeeAttendance->overtime      = '00:00:00';
            $employeeAttendance->total_rest    = '00:00:00';
            $employeeAttendance->created_by    = \Auth::user()->id;

            $employeeAttendance->save();

            return redirect()->back()->with('success', __('Employee Successfully Clock In.'));
        }
    }

    public function bulkAttendance(Request $request)
    {
        if(\Auth::user()->can('Create Attendance'))
        {

            $branch = Branch::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $branch->prepend('Select Branch', '');

            $department = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $department->prepend('Select Department', '');

            $employees = [];
            if(!empty($request->branch) && !empty($request->department))
            {
                $employees = Employee::where('created_by', \Auth::user()->creatorId())->where('branch_id', $request->branch)->where('department_id', $request->department)->get();


            }


            return view('attendance.bulk', compact('employees', 'branch', 'department'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function bulkAttendanceData(Request $request)
    {

        if(\Auth::user()->can('Create Attendance'))
        {
            if(!empty($request->branch) && !empty($request->department))
            {
                $startTime = Utility::getValTimings('company_start_time',$request->employee_id);
                $endTime   = Utility::getValTimings('company_end_time',$request->employee_id);
                $date      = $request->date;

                $employees = $request->employee_id;
                $atte      = [];
                foreach($employees as $employee)
                {
                    $present = 'present-' . $employee;
                    $in      = 'in-' . $employee;
                    $out     = 'out-' . $employee;
                    $atte[]  = $present;
                    if($request->$present == 'on')
                    {

                        $in  = date("H:i:s", strtotime($request->$in));
                        $out = date("H:i:s", strtotime($request->$out));

                        $totalLateSeconds = strtotime($in) - strtotime($startTime);

                        $hours = floor($totalLateSeconds / 3600);
                        $mins  = floor($totalLateSeconds / 60 % 60);
                        $secs  = floor($totalLateSeconds % 60);
                        $late  = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

                        //early Leaving
                        $totalEarlyLeavingSeconds = strtotime($endTime) - strtotime($out);
                        $hours                    = floor($totalEarlyLeavingSeconds / 3600);
                        $mins                     = floor($totalEarlyLeavingSeconds / 60 % 60);
                        $secs                     = floor($totalEarlyLeavingSeconds % 60);
                        $earlyLeaving             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);


                        if(strtotime($out) > strtotime($endTime))
                        {
                            //Overtime
                            $totalOvertimeSeconds = strtotime($out) - strtotime($endTime);
                            $hours                = floor($totalOvertimeSeconds / 3600);
                            $mins                 = floor($totalOvertimeSeconds / 60 % 60);
                            $secs                 = floor($totalOvertimeSeconds % 60);
                            $overtime             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
                        }
                        else
                        {
                            $overtime = '00:00:00';
                        }


                        $attendance = AttendanceEmployee::where('employee_id', '=', $employee)->where('date', '=', $request->date)->first();

                        if(!empty($attendance))
                        {
                            $employeeAttendance = $attendance;
                        }
                        else
                        {
                            $employeeAttendance              = new AttendanceEmployee();
                            $employeeAttendance->employee_id = $employee;
                            $employeeAttendance->created_by  = \Auth::user()->creatorId();
                        }


                        $employeeAttendance->date          = $request->date;
                        $employeeAttendance->status        = 'Present';
                        $employeeAttendance->clock_in      = $in;
                        $employeeAttendance->clock_out     = $out;
                        $employeeAttendance->late          = $late;
                        $employeeAttendance->early_leaving = ($earlyLeaving > 0) ? $earlyLeaving : '00:00:00';
                        $employeeAttendance->overtime      = $overtime;
                        $employeeAttendance->total_rest    = '00:00:00';
                        $employeeAttendance->save();

                    }
                    else
                    {
                        $attendance = AttendanceEmployee::where('employee_id', '=', $employee)->where('date', '=', $request->date)->first();

                        if(!empty($attendance))
                        {
                            $employeeAttendance = $attendance;
                        }
                        else
                        {
                            $employeeAttendance              = new AttendanceEmployee();
                            $employeeAttendance->employee_id = $employee;
                            $employeeAttendance->created_by  = \Auth::user()->creatorId();
                        }

                        $employeeAttendance->status        = 'Leave';
                        $employeeAttendance->date          = $request->date;
                        $employeeAttendance->clock_in      = '00:00:00';
                        $employeeAttendance->clock_out     = '00:00:00';
                        $employeeAttendance->late          = '00:00:00';
                        $employeeAttendance->early_leaving = '00:00:00';
                        $employeeAttendance->overtime      = '00:00:00';
                        $employeeAttendance->total_rest    = '00:00:00';
                        $employeeAttendance->save();
                    }
                }

                return redirect()->back()->with('success', __('Employee attendance successfully created.'));
            }
            else
            {
                return redirect()->back()->with('error', __('Branch & department field required.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function importFile()
    {
        return view('attendance.import');
    }

    public function import(Request $request)
    {
        $rules = [
            'file' => 'required|mimes:csv,txt,xlsx',
        ];
        $validator = \Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        $attendance = (new AttendanceImport())->toArray(request()->file('file'))[0];

        $email_data = [];
        foreach ($attendance as $key => $employee) {
            if ($key != 0) {
                echo "<pre>";
                if ($employee != null && Employee::where('email', $employee[0])->where('created_by', \Auth::user()->creatorId())->exists()) {
                    $email = $employee[0];
                } else {
                    $email_data[] = $employee[0];
                }
            }
        }
        $totalattendance = count($attendance) - 1;
        $errorArray    = [];

        

        if (!empty($attendanceData)) {
            $errorArray[] = $attendanceData;
        } else {
            foreach ($attendance as $key => $value) {
                if ($key != 0) {
                    $employeeData = Employee::where('email', $value[0])->where('created_by', \Auth::user()->creatorId())->first();
                    // $employeeId = 0;

                    
                    if (!empty($employeeData)) {

                        $startTime = Utility::getValTimings('company_start_time',$employeeData->id);
                        $endTime   = Utility::getValTimings('company_end_time',$employeeData->id);

                        $employeeId = $employeeData->id;


                        $clockIn = $value[2];
                        $clockOut = $value[3];

                        if ($clockIn) {
                            $status = "present";
                        } else {
                            $status = "leave";
                        }

                        $totalLateSeconds = strtotime($clockIn) - strtotime($startTime);

                        $hours = floor($totalLateSeconds / 3600);
                        $mins  = floor($totalLateSeconds / 60 % 60);
                        $secs  = floor($totalLateSeconds % 60);
                        $late  = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

                        $totalEarlyLeavingSeconds = strtotime($endTime) - strtotime($clockOut);
                        $hours                    = floor($totalEarlyLeavingSeconds / 3600);
                        $mins                     = floor($totalEarlyLeavingSeconds / 60 % 60);
                        $secs                     = floor($totalEarlyLeavingSeconds % 60);
                        $earlyLeaving             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

                        if (strtotime($clockOut) > strtotime($endTime)) {
                            //Overtime
                            $totalOvertimeSeconds = strtotime($clockOut) - strtotime($endTime);
                            $hours                = floor($totalOvertimeSeconds / 3600);
                            $mins                 = floor($totalOvertimeSeconds / 60 % 60);
                            $secs                 = floor($totalOvertimeSeconds % 60);
                            $overtime             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
                        } else {
                            $overtime = '00:00:00';
                        }

                        $check = AttendanceEmployee::where('employee_id', $employeeId)->where('date', $value[1])->first();
                        if ($check) {
                            $check->update([
                                'late' => $late,
                                'early_leaving' => ($earlyLeaving > 0) ? $earlyLeaving : '00:00:00',
                                'overtime' => $overtime,
                                'clock_in' => $value[2],
                                'clock_out' => $value[3]
                            ]);
                        } else {
                            $time_sheet = AttendanceEmployee::create([
                                'employee_id' => $employeeId,
                                'date' => $value[1],
                                'status' => $status,
                                'late' => $late,
                                'early_leaving' => ($earlyLeaving > 0) ? $earlyLeaving : '00:00:00',
                                'overtime' => $overtime,
                                'clock_in' => $value[2],
                                'clock_out' => $value[3],
                                'created_by' => \Auth::user()->id,
                            ]);
                        }
                    }
                } else {
                    $email_data = implode(' And ', $email_data);
                }
            }
            if (!empty($email_data)) {
                return redirect()->back()->with('status', 'this record is not import. ' . '</br>' . $email_data);
            } else {
                if (empty($errorArray)) {
                    $data['status'] = 'success';
                    $data['msg']    = __('Record successfully imported');
                } else {

                    $data['status'] = 'error';
                    $data['msg']    = count($errorArray) . ' ' . __('Record imported fail out of' . ' ' . $totalattendance . ' ' . 'record');


                    foreach ($errorArray as $errorData) {
                        $errorRecord[] = implode(',', $errorData->toArray());
                    }

                    \Session::put('errorArray', $errorRecord);
                }

                return redirect()->back()->with($data['status'], $data['msg']);
            }
        }
    }

    // public function manualEmployeeAttendance(Request $request)
    // {   
    //     dd($request->all());
    //     // Validate the incoming request data as needed
    //     $validatedData = $request->validate([
    //         'user_id' => 'required|array',
    //         'from_date' => 'required|date',
    //         'to_date' => 'required|date',
    //         'clock_in' => 'required',
    //         'clock_out' => 'required',
    //     ]);

    //     // Retrieve user_ids selected
    //     $userIds = $request->input('user_id');
    //     dd($userIds);

    //     @foreach($userIds){

    

    //     $emp = Employee::select('id','shift_mode')->where('user_id', $user_id)->first();

    //     $shift = ManageShift::where('shift_mode',$emp->shift_mode)->first();

    //     $late = 
    //     $earlyGoing = 
    //     $overtime = 

    //     $entry = new AttendanceEmployee();
    //     $entry->employee_id = 
    //     $entry->date = 
    //     $entry->status = "Present";
    //     $entry->clock_in = 
    //     $entry->clock_out = 
    //     $entry->late = 
    //     $entry->early_going = 
    //     $entry->overtime = 
    //     $entry->total_rest = "00:00:00";
    //     $entry->save();

    // }

    // }

//     public function manualEmployeeAttendance(Request $request)
// {
//     // Validate the incoming request data
//     $validatedData = $request->validate([
//         'user_id' => 'required|array',
//         'from_date' => 'required|date',
//         'to_date' => 'required|date',
//         'clock_in.*' => 'required',
//         'clock_out.*' => 'required',
//     ]);

//     $userIds = $request->input('user_id');
//     $fromDate = $request->input('from_date');
//     $toDate = $request->input('to_date');
//     $clockIn = $request->input('clock_in');
//     $clockOut = $request->input('clock_out');

//     // dd($clockIn,$clockOut);

//     foreach ($userIds as $userId) {
//         // Fetch employee details
//         $employee = Employee::where('user_id', $userId)->first();
//         // dd($employee->id);

//         // Fetch shift details
//         $shift = ManageShift::where('shift_code', $employee->shift_code)->first();
//         dd($shift);


//         // Loop through each date from from_date to to_date
//         $currentDate = $fromDate;
//         while ($currentDate <= $toDate) {

//             $late =  diff($clockIn,$shift->start_time);
//             $earlyGoing = diff($clockOut,$shift->endtime_time);
//             $overtime = also calcualte overtime 

//             // Perform calculations for late, early going, overtime based on $clockIn, $clockOut, $shift->start_time, $shift->end_time

//             // Create AttendanceEmployee entry
//             $entry = new AttendanceEmployee();
//             $entry->employee_id = $employee->id;
//             $entry->date = $currentDate;
//             $entry->status = "Present";
//             $entry->clock_in = $clockIn;
//             $entry->clock_out = $clockOut;
//             $entry->late = $late; // Calculate actual late time
//             $entry->early_leaving = $earlyGoing; // Calculate actual early leaving time
//             $entry->overtime = $overtime; // Calculate actual overtime
//             $entry->total_rest = "00:00:00";
//             $entry->save();

//             $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
//         }
//     }

//     // Optionally, redirect or return a response after processing
//     return redirect()->back()->with('success', 'Attendance entries saved successfully');
// }


public function manualEmployeeAttendance(Request $request)
{
    // Validate the incoming request data
    $validatedData = $request->validate([
        'user_id' => 'required|array',
        'from_date' => 'required|date',
        'to_date' => 'required|date',
        'clock_in.*' => 'required',
        'clock_out.*' => 'required',
    ]);

    $userIds = $request->input('user_id');
    $fromDate = $request->input('from_date');
    $toDate = $request->input('to_date');
    $clockIn = $request->input('clock_in');
    $clockOut = $request->input('clock_out');

    foreach ($userIds as $userId) {
        // Fetch employee details
        $employee = Employee::where('user_id', $userId)->first();


        // Fetch shift details
        $shift = ManageShift::where('shift_code', $employee->shift_code)->first();
        // dd($shift);

        // Loop through each date from from_date to to_date
        $currentDate = $fromDate;
        while ($currentDate <= $toDate) {
            $clockInTime = $clockIn;
            $clockOutTime = $clockOut;
            // dd($clockOutTime);

            // Perform calculations for late, early going, overtime based on $clockInTime, $clockOutTime, $shift->start_time, $shift->end_time

            // Calculate late time
            $late = $this->calculateLate($clockInTime, $shift->start_time);
            // dd($late);
            // Calculate early going time
            $earlyGoing = $this->calculateEarlyGoing($clockOutTime, $shift->end_time);
            // dd($earlyGoing);
            // Calculate overtime
            $overtime = $this->calculateOvertime($clockInTime, $clockOutTime, $shift->start_time, $shift->end_time);
            // dd($overtime);
            // Create AttendanceEmployee entry
            $entry = new AttendanceEmployee();
            $entry->employee_id = $employee->id;
            $entry->date = $currentDate;
            $entry->status = "Present";
            $entry->clock_in = $clockInTime;
            $entry->clock_out = $clockOutTime;
            $entry->late = $late;
            $entry->early_leaving = $earlyGoing;
            $entry->overtime = $overtime;
            $entry->total_rest = "00:00:00";
            $entry->manually_data = 1;
            $entry->save();

            $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
        }
    }

    // Optionally, redirect or return a response after processing
    return redirect()->back()->with('success', 'Attendance entries saved successfully');
}

private function calculateLate($clockInTime, $shiftStartTime)
{
    // Assuming both $clockInTime and $shiftStartTime are in 'H:i:s' format
    $clockIn = new DateTime($clockInTime);
    $start = new DateTime($shiftStartTime);

    if ($clockIn > $start) {
        $diff = $clockIn->diff($start);
        return $diff->format('%H:%I:%S');
    }

    return "00:00:00";
}

private function calculateEarlyGoing($clockOutTime, $shiftEndTime)
{
    // Assuming both $clockOutTime and $shiftEndTime are in 'H:i:s' format
    $clockOut = new DateTime($clockOutTime);
    $end = new DateTime($shiftEndTime);

    if ($clockOut < $end) {
        $diff = $end->diff($clockOut);
        return $diff->format('%H:%I:%S');
    }

    return "00:00:00";
}

// private function calculateOvertime($clockInTime, $clockOutTime, $shiftStartTime, $shiftEndTime)
// {
//     // Assuming all times are in 'H:i:s' format
//     $clockIn = new DateTime($clockInTime);
//     $clockOut = new DateTime($clockOutTime);
//     $start = new DateTime($shiftStartTime);
//     $end = new DateTime($shiftEndTime);

//     $workHours = $start->diff($end)->format('%H:%I:%S');
//     $actualWorkHours = $clockIn->diff($clockOut)->format('%H:%I:%S');

//     if ($actualWorkHours > $workHours) {
//         $diff = $actualWorkHours->diff($workHours);
//         return $diff->format('%H:%I:%S');
//     }

//     return "00:00:00";
// }

// private function calculateOvertime($clockInTime, $clockOutTime, $shiftStartTime, $shiftEndTime)
// {
//     // Create DateTime objects
//     $clockIn = new DateTime($clockInTime);
//     $clockOut = new DateTime($clockOutTime);
//     $start = new DateTime($shiftStartTime);
//     $end = new DateTime($shiftEndTime);

//     // Adjust end time if it's before start time (indicating shift spans across midnight)
//     if ($end < $start) {
//         $end->modify('+1 day');
//     }

//     // Calculate work hours in seconds
//     $workHoursSeconds = $start->diff($end)->h * 3600 + $start->diff($end)->i * 60;

//     // Calculate actual work hours in seconds
//     $actualWorkHoursSeconds = $clockIn->diff($clockOut)->h * 3600 + $clockIn->diff($clockOut)->i * 60;

//     // Check if actual work hours exceed regular work hours
//     if ($actualWorkHoursSeconds > $workHoursSeconds) {
//         // Calculate overtime in seconds
//         $overtimeSeconds = $actualWorkHoursSeconds - $workHoursSeconds;

//         // Convert overtime seconds to hours, minutes, seconds
//         $overtimeHours = floor($overtimeSeconds / 3600);
//         $overtimeSeconds %= 3600;
//         $overtimeMinutes = floor($overtimeSeconds / 60);

//         // Format overtime as HH:MM:SS
//         $overtimeFormatted = sprintf('%02d:%02d:%02d', $overtimeHours, $overtimeMinutes, $overtimeSeconds);

//         return $overtimeFormatted;
//     }

//     return "00:00:00";  // No overtime
// }


private function calculateOvertime($clockInTime, $clockOutTime, $shiftStartTime, $shiftEndTime)
{
    // Create DateTime objects
    $clockIn = new DateTime($clockInTime);
    $clockOut = new DateTime($clockOutTime);
    $start = new DateTime($shiftStartTime);
    $end = new DateTime($shiftEndTime);

    // Adjust end time if it's before start time (indicating shift spans across midnight)
    if ($end < $start) {
        $end->modify('+1 day');
    }

    // Calculate work hours in seconds
    $workHoursSeconds = $start->diff($end)->h * 3600 + $start->diff($end)->i * 60;

    // Calculate actual work hours in seconds
    $actualWorkHoursSeconds = $clockIn->diff($clockOut)->h * 3600 + $clockIn->diff($clockOut)->i * 60;

    // Check if actual work hours exceed regular work hours
    if ($actualWorkHoursSeconds > $workHoursSeconds) {
        // Calculate overtime in seconds
        $overtimeSeconds = $actualWorkHoursSeconds - $workHoursSeconds;

        // Calculate hours, minutes, seconds from overtime seconds
        $overtimeHours = floor($overtimeSeconds / 3600);
        $overtimeSeconds %= 3600;
        $overtimeMinutes = floor($overtimeSeconds / 60);
        $overtimeSeconds %= 60;

        // Format overtime as HH:MM:SS
        $overtimeFormatted = sprintf('%02d:%02d:%02d', $overtimeHours, $overtimeMinutes, $overtimeSeconds);

        return $overtimeFormatted;
    }

    return "00:00:00";  // No overtime
}



}
