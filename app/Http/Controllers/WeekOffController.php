<?php

namespace App\Http\Controllers;
use App\Models\ManageWeekOff;
use App\Models\Leave as LocalLeave;
use App\Models\Employee;
use App\Models\Utility;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;

class WeekOffController extends Controller
{
    // public function index()
    // {

    //     if (\Auth::user()->can('Manage Weekoff')) {
    //         if (\Auth::user()->type == 'employee') {
    //             // $user     = \Auth::user();
    //             // dd($user->id);
    //             // $employee = Employee::where('user_id', '=', $user->id)->first();
    //             // dd($employee->id);
    //             $weekoffs   = ManageWeekOff::where('employee_id', '=', \Auth::user()->id)->orderBy('updated_at', 'desc')->get();
    //         } else {
    //             // dd("elseee");
    //             $weekoffs = ManageWeekOff::with(['employees'])->orderBy('updated_at', 'desc')->get();
    //         }

    //         return view('weekoff.index', compact('weekoffs'));
    //     } else {
    //         return redirect()->back()->with('error', __('Permission denied.'));
    //     }
    // }

    public function index(Request $request)
    {
        // Check for permission
        if (\Auth::user()->can('Manage Weekoff')) {
            // Initialize the query
            $query = ManageWeekOff::query();

            // Check if user is an employee
            if (\Auth::user()->type == 'employee') {
                $query->where('employee_id', '=', \Auth::user()->id);
            } else {
                $query->with('employees');
            }

            // Apply filters if present
            $weekOffDate = $request->input('week_off_date');
            if ($weekOffDate) {
                $query->whereDate('week_off_date', $weekOffDate);
            }

            if ($request->filled('start_date') && $request->filled('end_date')) {
                $startDate = $request->input('start_date');
                $endDate = $request->input('end_date');
                $query->whereBetween('created_at', [$startDate, $endDate]);
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
            $weekoffs = $query->orderBy('updated_at', 'desc')->get();

            // Fetch the list of users for filter dropdown (excluding super admin and company)
            $objUser = \Auth::user();
            $usersList = User::where('created_by', $objUser->creatorId())
                ->whereNotIn('type', ['super admin', 'company'])
                ->get()
                ->pluck('name', 'id');
            $usersList->prepend('All', '');

            return view('weekoff.index', compact('weekoffs', 'usersList'));
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
    
    //     // Get the requested week off date
    //     $weekOffDate = new \DateTime($request->week_off_date);
    
    //     // Determine the start and end of the week for the given date
    //     $startOfWeek = clone $weekOffDate;
    //     $startOfWeek->modify('last sunday')->modify('+1 day');
    //     $endOfWeek = clone $startOfWeek;
    //     $endOfWeek->modify('next sunday');
    
    //     // Check if the user (employee) already has a week off on any day in the same week
    //     $existingWeekOff = ManageWeekOff::where('employee_id', \Auth::user()->id)
    //         ->where('week_off_date', '>=', $startOfWeek->format('Y-m-d'))
    //         ->where('week_off_date', '<', $endOfWeek->format('Y-m-d'))
    //         ->exists();
    
    //     if ($existingWeekOff) {
    //         // If there's already a week off for the same user within the requested week, return an error
    //         return redirect()->back()->with('error', 'You already have a week off in the current week.');
    //     }
    
    //     // Check if the user (employee) already has a week off on the requested day
    //     $existingDayOff = ManageWeekOff::where('employee_id', \Auth::user()->id)
    //         ->where('week_off_date', $weekOffDate->format('Y-m-d'))
    //         ->exists();
    
    //     if ($existingDayOff) {
    //         // If there's already a week off for the same user on the requested day, return an error
    //         return redirect()->back()->with('error', 'You already have a week off on this day.');
    //     }
    
    //     // Create a new ManageWeekOff record for the user
    //     $dayName = $weekOffDate->format('l'); // Get the day name (e.g., Monday, Tuesday)
    //     $weekOff = new ManageWeekOff();
    //     $weekOff->employee_id = \Auth::user()->id;
    //     $weekOff->week_off_date = $weekOffDate->format('Y-m-d');
    //     $weekOff->day_name = $dayName;
    //     $weekOff->remark = $request->remark;
    //     $weekOff->status = "Pending";
    //     $weekOff->created_by = \Auth::user()->id;
    //     $weekOff->save();

    //     $user     = \Auth::user();
    //     $employee = Employee::where('user_id', '=', \Auth::user()->id)->first();

    //     $localLeave = new LocalLeave();
    //     $localLeave->employee_id = $employee->id;
    //     $localLeave->leave_type_id = 4;
    //     $localLeave->weekoff_id = $weekOff->id;
    //     $localLeave->applied_on = date("Y-m-d");
    //     $localLeave->start_date = $weekOffDate->format('Y-m-d');
    //     $localLeave->end_date = $weekOffDate->format('Y-m-d');
    //     $localLeave->total_leave_days = 1;
    //     $localLeave->leave_reason = $request->remark;
    //     $localLeave->remark = $request->remark;
    //     $localLeave->status = "Pending";
    //     $localLeave->created_by = \Auth::user()->creatorId(); 
    //     $localLeave->save();
    
    //     // Redirect with success message or perform any other necessary actions
    //     return redirect()->back()->with('success', 'Week off request successfully added.');
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
        
    //     // Get the requested week off date
    //     $weekOffDate = new \DateTime($request->week_off_date);
    //     $year = $weekOffDate->format('Y');
    //     $month = $weekOffDate->format('m');
    
    //     // Determine the start and end of the week for the given date
    //     $startOfWeek = clone $weekOffDate;
    //     $startOfWeek->modify('sunday this week');
    //     $startOfWeek->modify('+1 day'); // To get Monday of the week
    
    //     $endOfWeek = clone $startOfWeek;
    //     $endOfWeek->modify('next sunday');
        
    //     // Calculate the number of week-offs taken in the month
    //     $weekOffCount = ManageWeekOff::where('employee_id', \Auth::user()->id)
    //         ->whereYear('week_off_date', $year)
    //         ->whereMonth('week_off_date', $month)
    //         ->count();
        
    //     if ($weekOffCount >= 4) {
    //         return redirect()->back()->with('error', 'You have already taken 4 week-offs this month.');
    //     }
        
    //     // Check if the employee already has a week off in the same week
    //     $existingWeekOff = ManageWeekOff::where('employee_id', \Auth::user()->id)
    //         ->whereBetween('week_off_date', [$startOfWeek->format('Y-m-d'), $endOfWeek->format('Y-m-d')])
    //         ->where('status', '<>', 'Reject')
    //         ->exists();
        
    //     if ($existingWeekOff) {
    //         return redirect()->back()->with('error', 'You already have a week off in the current week.');
    //     }
        
    //     // Determine the current week number of the month
    //     $currentWeek = (int) $weekOffDate->format('W') - (int) (new \DateTime($weekOffDate->format('Y-m-01')))->format('W') + 1;
    
    //     // Set week off limits based on the current week of the month
    //     $weekOffDaysAllowed = 0;
    //     if ($currentWeek == 1) {
    //         $weekOffDaysAllowed = 1;
    //     } elseif ($currentWeek == 2) {
    //         $weekOffDaysAllowed = 2;
    //     } elseif ($currentWeek == 3) {
    //         $weekOffDaysAllowed = 3;
    //     } elseif ($currentWeek >= 4) {
    //         $weekOffDaysAllowed = 4;
    //     }
    
    //     // Check if the number of week-offs already taken in the week exceeds the allowed days
    //     $takenDays = ManageWeekOff::where('employee_id', \Auth::user()->id)
    //         ->whereBetween('week_off_date', [$startOfWeek->format('Y-m-d'), $endOfWeek->format('Y-m-d')])
    //         ->where('status', '<>', 'Reject')
    //         ->count();
        
    //     if ($takenDays >= $weekOffDaysAllowed) {
    //         return redirect()->back()->with('error', "You cannot take more than $weekOffDaysAllowed week-off days in the current week.");
    //     }
        
    //     // Check if the user already has a week off on the requested day
    //     $existingDayOff = ManageWeekOff::where('employee_id', \Auth::user()->id)
    //         ->where('week_off_date', $weekOffDate->format('Y-m-d'))
    //         ->where('status', '<>', 'Reject')
    //         ->exists();
        
    //     if ($existingDayOff) {
    //         return redirect()->back()->with('error', 'You already have a week off on this day.');
    //     }
        
    //     // Create a new ManageWeekOff record for the user
    //     $dayName = $weekOffDate->format('l'); // Get the day name (e.g., Monday, Tuesday)
    //     $weekOff = new ManageWeekOff();
    //     $weekOff->employee_id = \Auth::user()->id;
    //     $weekOff->week_off_date = $weekOffDate->format('Y-m-d');
    //     $weekOff->day_name = $dayName;
    //     $weekOff->remark = $request->remark;
    //     $weekOff->status = "Pending";
    //     $weekOff->created_by = \Auth::user()->id;
    //     $weekOff->save();
        
    //     $user = \Auth::user();
    //     $employee = Employee::where('user_id', '=', \Auth::user()->id)->first();
    
    //     $localLeave = new LocalLeave();
    //     $localLeave->employee_id = $employee->id;
    //     $localLeave->leave_type_id = 4;
    //     $localLeave->weekoff_id = $weekOff->id;
    //     $localLeave->applied_on = date("Y-m-d");
    //     $localLeave->start_date = $weekOffDate->format('Y-m-d');
    //     $localLeave->end_date = $weekOffDate->format('Y-m-d');
    //     $localLeave->total_leave_days = 1;
    //     $localLeave->leave_reason = $request->remark;
    //     $localLeave->remark = $request->remark;
    //     $localLeave->status = "Pending";
    //     $localLeave->created_by = \Auth::user()->creatorId(); 
    //     $localLeave->save();
        
    //     // Redirect with success message or perform any other necessary actions
    //     return redirect()->back()->with('success', 'Week off request successfully added.');
    // }

    ##########################################################################################
    #     All Function And condition and situation work fine if if employee apply week off    #
    #     and then admin or hr response anything from rejected or approved then employee      #
    #     apply any type of situation then works fine and if emp apply week off and admin     #
    #     and hr not response anything then employee apply any situation week off then work   #
    #     worng something like so employee apply then hr or admin response then employee take #
    #     action for next apply.                                                              #
    ##############################################################################


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
    
    //     // Get the week off date
    //     $weekOffDate = new \DateTime($request->week_off_date);
    //     $currentDate = new \DateTime(); // Get the current date
    //     $weekOffMonth = $weekOffDate->format('Y-m');

    //     // Check if the week off date is in the past
    //     if ($weekOffDate < $currentDate) {
    //         // Return an error if the week off date is before today
    //         return redirect()->back()->with('error', 'You cannot apply for a week off in the past.');
    //     }
    
    //     // Determine the start and end of the week for the given date
    //     $startOfWeek = (clone $weekOffDate)->modify('this week')->modify('monday');
    //     $endOfWeek = (clone $startOfWeek)->modify('sunday');
        
    //     $existingWeekOff = ManageWeekOff::where('employee_id', \Auth::user()->id)
    //         // ->where('week_off_date', $startOfWeek->format('Y-m-d'))
    //         ->whereBetween('week_off_date', [$startOfWeek->format('Y-m-d'), $endOfWeek->format('Y-m-d')])
    //         // ->where(function ($query) {
    //         //     $query->where('status', '!=', 'Reject')
    //         //         ->orWhereNull('status');
    //         // })
    //         ->where(function ($query) {
    //             $query->whereNotIn('status', ['Reject', 'Approved'])
    //                   ->orWhereNull('status');
    //         })
    //         ->first();
        
    //     $exitsRejectWeekOff = ManageWeekOff::where('employee_id', \Auth::user()->id)
    //     ->where('week_off_date', $weekOffDate)
    //     // ->whereBetween('week_off_date', [$startOfWeek->format('Y-m-d'), $endOfWeek->format('Y-m-d')])
    //     ->whereIn('status',['Reject','Approved'])
    //     ->first();
        
    //     // dd($existingWeekOff);


    //     // if ($existingWeekOff) {   
    //     //     return redirect()->back()->with('error', 'You already have a week off on this day in the current week.');
    //     // }

    //     // dd($exitsRejectWeekOff);

         
    //         if($exitsRejectWeekOff){
    //             return redirect()->back()->with('error', 'You have already applied for this date in the current week, try for another date in same week.');
    //         }

    //     $weekOffsThisMonth = ManageWeekOff::where('employee_id', \Auth::user()->id)
    //         ->where('week_off_date', 'like', "$weekOffMonth%")
    //         // ->where(function ($query) {
    //         //     $query->where('status', '!=', 'Reject')
    //         //         ->orWhereNull('status');
    //         // })
    //         ->where(function ($query) {
    //             $query->whereNotIn('status', ['Reject', 'Approved'])
    //                   ->orWhereNull('status');
    //         })
    //         ->count();

    //         // dd($weekOffsThisMonth);
    
    //     if ($weekOffsThisMonth >= 4) {
    //         // Return an error if the user has already taken the maximum number of week offs in the month
    //         return redirect()->back()->with('error', 'You have already taken the maximum number of week offs for this month.');
    //     }
    
    //     // Determine the number of days the employee can take off based on previous week offs
    //     $weekOffsInPreviousWeeks = ManageWeekOff::where('employee_id', \Auth::user()->id)
    //         ->where('week_off_date', 'like', "$weekOffMonth%")
    //         // ->where(function ($query) {
    //         //     $query->where('status', '!=', 'Reject')
    //         //         ->orWhereNull('status');
    //         // })
    //         ->where(function ($query) {
    //             $query->whereNotIn('status', ['Reject', 'Approved'])
    //                   ->orWhereNull('status');
    //         })
    //         ->whereBetween('week_off_date', [
    //             (new \DateTime($weekOffMonth . '-01'))->format('Y-m-d'),
    //             (new \DateTime($weekOffMonth . '-01'))->modify('last day of this month')->format('Y-m-d')
    //         ])
    //         ->count();

    //     // dd($weekOffsInPreviousWeeks);
    
    //     $allowedDays = 1; // Default allowed days if no week offs were taken in previous weeks
    
    //     if ($weekOffsInPreviousWeeks == 0) {
    //         $allowedDays = 1; // First week: 1 day
    //     } elseif ($weekOffsInPreviousWeeks == 1) {
    //         $allowedDays = 2; // Second week: 2 days
    //     } elseif ($weekOffsInPreviousWeeks == 2) {
    //         $allowedDays = 3; // Third week: 3 days
    //     } elseif ($weekOffsInPreviousWeeks == 3) {
    //         $allowedDays = 4; // Fourth week: 4 days
    //     }
    
    //     // Check if the request meets the allowed number of days
    //     $weekOffsInCurrentWeek = ManageWeekOff::where('employee_id', \Auth::user()->id)
    //         ->whereBetween('week_off_date', [$startOfWeek->format('Y-m-d'), $endOfWeek->format('Y-m-d')])
    //         // ->where(function ($query) {
    //         //     $query->where('status', '!=', 'Reject')
    //         //         ->orWhereNull('status');
    //         // })
    //         ->where(function ($query) {
    //             $query->whereNotIn('status', ['Reject', 'Approved'])
    //                   ->orWhereNull('status');
    //         })
    //         ->count();
        
    //         // dd( $weekOffsInCurrentWeek, $allowedDays);
    
    //     if ($weekOffsInCurrentWeek >= $allowedDays) {
    //         // Return an error if the user exceeds the allowed number of week offs in the current week
    //         return redirect()->back()->with('error', "You can only take $allowedDays days off in the current week.");
    //     }
    
    //     // Create a new ManageWeekOff record for the user
    //     $weekOff = new ManageWeekOff();
    //     $weekOff->employee_id = \Auth::user()->id;
    //     $weekOff->week_off_date = $weekOffDate->format('Y-m-d');
    //     $weekOff->day_name = $weekOffDate->format('l');
    //     $weekOff->remark = $request->remark;
    //     $weekOff->status = "Pending";
    //     $weekOff->created_by = \Auth::user()->id;
    //     $weekOff->save();


    //     $user = \Auth::user();
    //     $employee = Employee::where('user_id', '=', \Auth::user()->id)->first();

    //     $localLeave = new LocalLeave();
    //     $localLeave->employee_id = $employee->id;
    //     $localLeave->leave_type_id = 4;
    //     $localLeave->weekoff_id = $weekOff->id;
    //     $localLeave->applied_on = date("Y-m-d");
    //     $localLeave->start_date = $weekOffDate->format('Y-m-d');
    //     $localLeave->end_date = $weekOffDate->format('Y-m-d');
    //     $localLeave->total_leave_days = 1;
    //     $localLeave->leave_reason = $request->remark;
    //     $localLeave->remark = $request->remark;
    //     $localLeave->status = "Pending";
    //     $localLeave->created_by = \Auth::user()->creatorId(); 
    //     $localLeave->save();
    
    //     // Redirect with success message
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
    
        // Get the week off date
        $weekOffDate = new \DateTime($request->week_off_date);
        $currentDate = new \DateTime(); // Get the current date
        $weekOffMonth = $weekOffDate->format('Y-m');

        // Check if the week off date is in the past
        if ($weekOffDate < $currentDate) {
            // Return an error if the week off date is before today
            return redirect()->back()->with('error', 'You cannot apply for a week off in the past.');
        }
    
        // Determine the start and end of the week for the given date
        $startOfWeek = (clone $weekOffDate)->modify('this week')->modify('monday');
        $endOfWeek = (clone $startOfWeek)->modify('sunday');
        
        $existingWeekOff = ManageWeekOff::where('employee_id', \Auth::user()->id)
            // ->where('week_off_date', $startOfWeek->format('Y-m-d'))
            ->whereBetween('week_off_date', [$startOfWeek->format('Y-m-d'), $endOfWeek->format('Y-m-d')])
            // ->where(function ($query) {
            //     $query->where('status', '!=', 'Reject')
            //         ->orWhereNull('status');
            // })
            ->where(function ($query) {
                $query->whereNotIn('status', ['Reject', 'Approved'])
                      ->orWhereNull('status');
            })
            ->first();
        
        $exitsRejectWeekOff = ManageWeekOff::where('employee_id', \Auth::user()->id)
        ->where('week_off_date', $weekOffDate)
        // ->whereBetween('week_off_date', [$startOfWeek->format('Y-m-d'), $endOfWeek->format('Y-m-d')])
        ->whereIn('status',['Reject','Approved'])
        ->first();
        
        // dd($existingWeekOff);


        // if ($existingWeekOff) {   
        //     return redirect()->back()->with('error', 'You already have a week off on this day in the current week.');
        // }

        // dd($exitsRejectWeekOff);

         
        if($exitsRejectWeekOff){
            return redirect()->back()->with('error', 'You have already applied for this date in the current week, try for another date in same week.');
        }

        $weekOffsThisMonth = ManageWeekOff::where('employee_id', \Auth::user()->id)
            ->where('week_off_date', 'like', "$weekOffMonth%")
            // ->where(function ($query) {
            //     $query->where('status', '!=', 'Reject')
            //         ->orWhereNull('status');
            // })
            ->where(function ($query) {
                $query->whereNotIn('status', ['Reject', 'Approved'])
                      ->orWhereNull('status');
            })
            ->count();

            // dd($weekOffsThisMonth);
    
        if ($weekOffsThisMonth >= 4) {
            // Return an error if the user has already taken the maximum number of week offs in the month
            return redirect()->back()->with('error', 'You have already taken the maximum number of week offs for this month.');
        }
    
        // Determine the number of days the employee can take off based on previous week offs
        $weekOffsInPreviousWeeks = ManageWeekOff::where('employee_id', \Auth::user()->id)
            ->where('week_off_date', 'like', "$weekOffMonth%")
            // ->where(function ($query) {
            //     $query->where('status', '!=', 'Reject')
            //         ->orWhereNull('status');
            // })
            ->where(function ($query) {
                $query->whereNotIn('status', ['Reject', 'Approved'])
                      ->orWhereNull('status');
            })
            ->whereBetween('week_off_date', [
                (new \DateTime($weekOffMonth . '-01'))->format('Y-m-d'),
                (new \DateTime($weekOffMonth . '-01'))->modify('last day of this month')->format('Y-m-d')
            ])
            ->count();

        // dd($weekOffsInPreviousWeeks);
    
        $allowedDays = 1; // Default allowed days if no week offs were taken in previous weeks
    
        if ($weekOffsInPreviousWeeks == 0) {
            // dd("1");
            $allowedDays = 1; // First week: 1 day
        } elseif ($weekOffsInPreviousWeeks == 1) {
            // dd("2");
            $allowedDays = 2; // Second week: 2 days
            if ($existingWeekOff && $allowedDays < 2) {   
                return redirect()->back()->with('error', 'You already have a week off on this day in the current week.');
            }
        } elseif ($weekOffsInPreviousWeeks == 2) {
            // dd("3");
            $allowedDays = 3; // Third week: 3 days
            if ($existingWeekOff && $allowedDays < 3) {   
                return redirect()->back()->with('error', 'You already have a week off on this day in the current week.');
            }
        } elseif ($weekOffsInPreviousWeeks == 3) {
            // dd("4");
            $allowedDays = 4; // Fourth week: 4 days

            if ($existingWeekOff && $allowedDays < 4) {  
                return redirect()->back()->with('error', 'You already have a week off on this day in the current week.');
            }

        }
    
        // Check if the request meets the allowed number of days
        $weekOffsInCurrentWeek = ManageWeekOff::where('employee_id', \Auth::user()->id)
            ->whereBetween('week_off_date', [$startOfWeek->format('Y-m-d'), $endOfWeek->format('Y-m-d')])
            // ->where(function ($query) {
            //     $query->where('status', '!=', 'Reject')
            //         ->orWhereNull('status');
            // })
            ->where(function ($query) {
                $query->whereNotIn('status', ['Reject', 'Approved'])
                      ->orWhereNull('status');
            })
            ->count();
        
            // dd( $weekOffsInCurrentWeek, $allowedDays);
    
        if ($weekOffsInCurrentWeek >= $allowedDays) {
            // Return an error if the user exceeds the allowed number of week offs in the current week
            return redirect()->back()->with('error', "You can only take $allowedDays days off in the current week.");
        }
    
        // Create a new ManageWeekOff record for the user
        $weekOff = new ManageWeekOff();
        $weekOff->employee_id = \Auth::user()->id;
        $weekOff->week_off_date = $weekOffDate->format('Y-m-d');
        $weekOff->day_name = $weekOffDate->format('l');
        $weekOff->remark = $request->remark;
        $weekOff->status = "Pending";
        $weekOff->created_by = \Auth::user()->id;
        $weekOff->save();


        $user = \Auth::user();
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
    
        // Redirect with success message
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
        // $employee  = Employee::find($weekoff->employee_id);
        // dd($employee);
        // $leavetype = LeaveType::find($leave->leave_type_id);

        return view('weekoff.action', compact('weekoff'));
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
