<?php

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Employee; 
use App\Models\ManageShift;
use App\Models\Leave as LocalLeave;
use App\Models\LeaveType;


if(! function_exists('attendanceNatPayable_helper')){

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function attendanceNatPayable_helper($month, $year, $empId, $otherSalary, $shiftCode, $enableWeekoff, $doj) {
    // Fetch attendance data for the given month and year
    $attendanceRecords = DB::table('attendance_employees')
        ->where('employee_id', $empId)
        ->whereMonth('date', $month)
        ->whereYear('date', $year)
        ->get();

        // dd($attendanceRecords);

    // Fetch the applicable time sheet policy (assuming there's only one policy)
    $timeSheetPolicy = DB::table('time_sheets2')->first();

    // Initialize variables for calculations
    $totalDeduction = 0;
    $totalOvertime = 0;
    $dailySalary = $otherSalary / 30; // Assuming salary is divided by 30 days

    // Initialize $getFinalOTSal with a default value
    $getFinalOTSal = 0;

    $daysDeductions = 0;

    // Arrays to track processed IDs for late and early leaving
    $processedLateIds = [];
    $processedEarlyLeavingIds = [];

    // Arrays to track processed dates for late and early leaving
    $processedDates = [];

    // Iterate through attendance records and calculate deductions
if($attendanceRecords){

            $dateDeductions =  attendanceDaysDeductions($month, $year, $empId, $otherSalary, $shiftCode, $enableWeekoff, $doj);
            // dd($dateDeductions['finalExcept_dates_count']);

            $daysDeductions = $dateDeductions['finalExcept_dates_count'] * $dailySalary;

    foreach ($attendanceRecords as $attendance) {
        // Initialize DateTime object for the current attendance date
        // $attendanceDate = new DateTime($attendance->date);
        $attendanceDate = Carbon::parse($attendance->date);
        $attendanceDateString = $attendanceDate->format('Y-m-d');

        // Check if this date has already been processed
        if (!in_array($attendanceDateString, $processedDates)) {
            // dd($attendanceDateString);
            // Calculate sum of late and early leaving times for this date
            $latEntries = DB::table('attendance_employees')
                        ->where('employee_id', $empId)
                        ->whereDate('date', $attendanceDateString)
                        ->get();

            // Initialize variables to calculate total late time in seconds
            $totalLateSeconds = 0;
            $totalEarlySeconds = 0;
            $totalOvertimeSeconds = 0;

            // Calculate total late time in seconds for all entries
            foreach ($latEntries as $entry) {
                // Parse hh:mm:ss to calculate total seconds
                list($hours, $minutes, $seconds) = explode(':', $entry->late);
                $totalLateSeconds += ($hours * 3600) + ($minutes * 60) + $seconds;
            }
            foreach ($latEntries as $early) {
                // Parse hh:mm:ss to calculate total seconds
                list($hours, $minutes, $seconds) = explode(':', $early->early_leaving);
                $totalEarlySeconds += ($hours * 3600) + ($minutes * 60) + $seconds;
            }
            foreach ($latEntries as $overtime) {
                // Parse hh:mm:ss to calculate total seconds
                list($hours, $minutes, $seconds) = explode(':', $overtime->overtime);
                $totalOvertimeSeconds += ($hours * 3600) + ($minutes * 60) + $seconds;
            }

            // Format total late time into h:mm:ss
            $totalLateFormatted = gmdate('H:i:s', $totalLateSeconds);
            $totalEarlyFormatted = gmdate('H:i:s', $totalEarlySeconds);
            $totalOvertimeFormatted = gmdate('H:i:s', $totalOvertimeSeconds);
            // dd($totalOvertimeFormatted,$shiftCode);
            list($hours, $minutes, $seconds) = explode(':', $totalOvertimeFormatted);
            $totalOvertimeMinutes = ($hours * 60) + $minutes;
            // dd($totalOvertimeMinutes);

            $getShiftTime = ManageShift::where('shift_code',$shiftCode)->pluck('shift_hours')->first();
            list($hours, $minutes, $seconds) = explode(':', $getShiftTime);
            $totalMinutes = ($hours * 60) + $minutes;
            // dd($totalMinutes);

            $minutesSal = $dailySalary / $totalMinutes;
            $getFinalOTSal = $totalOvertimeMinutes * $minutesSal;
            // dd($getFinalOTSal);

            // dd($totalLateFormatted);

            // Initialize deduction variables
            $lateDeduction = 0;
            $earlyLeavingDeduction = 0;
            $attendanceId = $attendance->id;

            // Calculate late deduction if not processed for this attendance date
            if ($totalLateFormatted > "00:00:00" && !in_array($attendanceDateString, $processedLateIds)) {
                $deductionPercentage = 0; // Initialize deduction percentage for late
                if ($totalLateFormatted > $timeSheetPolicy->late_4) {
                    $deductionPercentage = $timeSheetPolicy->deduct_percentage_4;
                } elseif ($totalLateFormatted > $timeSheetPolicy->late_3) {
                    $deductionPercentage = $timeSheetPolicy->deduct_percentage_3;
                } elseif ($totalLateFormatted > $timeSheetPolicy->late_2) {
                    $deductionPercentage = $timeSheetPolicy->deduct_percentage_2;
                } elseif ($totalLateFormatted > $timeSheetPolicy->late_1) {
                    $deductionPercentage = $timeSheetPolicy->deduct_percentage_1;
                }

                $lateDeduction = ($deductionPercentage / 100) * $dailySalary;
                $totalDeduction += $lateDeduction;

                // Mark this attendance date as processed for late deduction
                $processedLateIds[] = $attendanceDateString;
            }

            // Calculate early leaving deduction if not processed for this attendance date
            if ($totalEarlyFormatted > "00:00:00" && !in_array($attendanceDateString, $processedEarlyLeavingIds)) {
                $deductionPercentage = 0; // Initialize deduction percentage for early leaving
                if ($totalEarlyFormatted < $timeSheetPolicy->early_going_1) {
                    $deductionPercentage = $timeSheetPolicy->deduct_percentage_early_going_1;
                } elseif ($totalEarlyFormatted < $timeSheetPolicy->early_going_2) {
                    $deductionPercentage = $timeSheetPolicy->deduct_percentage_early_going_2;
                } elseif ($totalEarlyFormatted < $timeSheetPolicy->early_going_3) {
                    $deductionPercentage = $timeSheetPolicy->deduct_percentage_early_going_3;
                } elseif ($totalEarlyFormatted < $timeSheetPolicy->early_going_4) {
                    $deductionPercentage = $timeSheetPolicy->deduct_percentage_early_going_4;
                }

                $earlyLeavingDeduction = ($deductionPercentage / 100) * $dailySalary;
                $totalDeduction += $earlyLeavingDeduction;

                // Mark this attendance date as processed for early leaving deduction
                $processedEarlyLeavingIds[] = $attendanceDateString;
            }

            // Mark this date as processed
            $processedDates[] = $attendanceDateString;
        }
    }
}
    // dd($totalDeduction);
    // Calculate net payable salary
    $netPayableSalary = $otherSalary - $totalDeduction;
    // dd($netPayableSalary);
    // dd($daysDeductions);
    $getDed =  $otherSalary - $netPayableSalary;
    $netPayable = $otherSalary - $daysDeductions+$getDed;
    // dd($netPayable);

    // return $netPayable;
    return $data = array(
        "netPayable" => $netPayable,
        "getFinalOTSal" => $getFinalOTSal,
        "daysComeAttendance" => $dateDeductions['daysComeAttendance'],
        'weekoffs' => $dateDeductions['weekoffs']
    );
}  


#########################################################################################################################################

function attendanceDaysDeductions($month, $year, $empId, $otherSalary, $shiftCode, $enableWeekoff, $doj) {

    $checkLeaves = DB::table('leaves')
                    ->where('status',"Approved")
                    ->where('employee_id', $empId)
                    ->whereMonth('start_date', $month)
                    ->whereYear('start_date', $year)
                    ->where('leave_type_id',2)
                    ->get();
    
    $date_parts = explode("-", $doj);
    $day_digit = $date_parts[2]; 

    // dd($checkLeaves);

    if($enableWeekoff == "Enabled"){

        ################################################################################################################
                    $countHolidays = DB::table('holidays')
                    ->where(function ($query) use ($month, $year) {
                        // Holidays starting in June and ending in June or later
                        $query->whereRaw('MONTH(start_date) = ?', [$month])
                            ->whereRaw('YEAR(start_date) = ?', [$year])
                            ->orWhere(function ($query) use ($month, $year) {
                                // Holidays starting before June and ending in June
                                $query->whereRaw('MONTH(start_date) < ?', [$month])
                                        ->whereRaw('MONTH(end_date) >= ?', [$month])
                                        ->whereRaw('YEAR(end_date) = ?', [$year]);
                            });
                    })
                    ->get(['start_date', 'end_date', 'total_days'])
                    ->toArray();

                    $totalDays = 0;

                foreach ($countHolidays as $holiday) {
                    $startDate = new DateTime($holiday->start_date);
                    $endDate = new DateTime($holiday->end_date);

                    // Calculate days in June for each holiday
                    if ($startDate->format('Y-m') == "$year-$month") {
                        // Holiday starts in June
                        if ($endDate->format('Y-m') == "$year-$month") {
                            // Holiday ends in June
                            $totalDays += calculateWorkingDays($startDate, $endDate);
                        } else {
                            // Holiday spans across June and July, count days till end of June
                            $endOfJune = new DateTime("$year-$month-30");
                            $totalDays += calculateWorkingDays($startDate, $endOfJune);
                        }
                    } else {
                        // Holiday starts before June and ends in June
                        $totalDays += calculateWorkingDays($startDate, $endDate);
                    }
                }

                // dd($totalDays);
            #################################################################################################################

         $currentDate = now();
         // $lastDayOfMonth = Carbon::createFromDate($year, $month)->endOfMonth()->day;
         $lastDayOfMonth = 30;

         $datesInMonth = [];

        //  for ($day = 1; $day <= $lastDayOfMonth; $day++) {
        //      $dateString = sprintf('%d-%02d-%02d', $year, $month, $day);
        //      $datesInMonth[] = $dateString;

        //      if ($year == $currentDate->year && $month == $currentDate->month && $day == $currentDate->day) {
        //          break;
        //      }
        //  }

        // for ($day = $day_digit; $day <= $lastDayOfMonth; $day++) {
        for ($day = 1; $day <= $lastDayOfMonth; $day++) {
            $dateString = sprintf('%d-%02d-%02d', $year, $month, $day);
            $datesInMonth[] = $dateString;

            if ($year == $currentDate->year && $month == $currentDate->month && $day == $currentDate->day) {
                break;
            }
        }

     $attendanceRecords = DB::table('attendance_employees')
         ->where('employee_id', $empId)
         ->whereMonth('date', $month)
         ->whereYear('date', $year)
         ->pluck('date')
         ->toArray();

     $employee = Employee::where('id', '=', $empId)->first();
     // dd($attendanceRecords);

    //  $manageWeekOffRecords = DB::table('manage_weekoff')
    //      ->where('employee_id', $employee->user_id)
    //      ->whereMonth('week_off_date', $month)
    //      ->whereYear('week_off_date', $year)
    //      ->pluck('week_off_date')
    //      ->toArray();
         

        // Remove Saturdays and Sundays from $datesInMonth
        foreach ($datesInMonth as $key => $date) {
            $dayOfWeek = Carbon::parse($date)->dayOfWeek;
            if ($dayOfWeek == Carbon::SATURDAY || $dayOfWeek == Carbon::SUNDAY) {
                unset($datesInMonth[$key]);
            }
        }

        //  dd($datesInMonth, $attendanceRecords);
 
     $missingDates = array_diff($datesInMonth, $attendanceRecords);
        // dd($missingDates, count($missingDates));
      // Remove dates that are also leaves (excluding leave_type_id 2 or Full Holi Day)
      foreach ($checkLeaves as $leave) {
        $startDate = $leave->start_date;
        $endDate = $leave->end_date;

        // Iterate through the missing dates and remove those that fall within the leave period
        foreach ($missingDates as $key => $date) {
            if ($date >= $startDate && $date <= $endDate) {
                unset($missingDates[$key]);
            }
        }
    }

    // dd($missingDates, count($missingDates));
    //  $finalExceptDates = array_diff($missingDates, $manageWeekOffRecords);

    // $daysComeAttendance = 30 - count($missingDates);
    // $daysComeAttendance = 30 - count($attendanceRecords);

     return $data = array(
        //  "missingDates" => $missingDates,
        //  "finalExceptDates" => $finalExceptDates,
        //  "missing_dates_count" => count($missingDates),
         "finalExcept_dates_count" => count($missingDates) - $totalDays,
         "daysComeAttendance" => count($attendanceRecords),
         'weekoffs' => 8
     );

    }else{
         
         #######################################################################################################################

         $countHolidays = DB::table('holidays')
         ->where(function ($query) use ($month, $year) {
             // Holidays starting in June and ending in June or later
             $query->whereRaw('MONTH(start_date) = ?', [$month])
                 ->whereRaw('YEAR(start_date) = ?', [$year])
                 ->orWhere(function ($query) use ($month, $year) {
                     // Holidays starting before June and ending in June
                     $query->whereRaw('MONTH(start_date) < ?', [$month])
                             ->whereRaw('MONTH(end_date) >= ?', [$month])
                             ->whereRaw('YEAR(end_date) = ?', [$year]);
                 });
         })
         ->get(['start_date', 'end_date', 'total_days'])
         ->toArray();
 
         $totalDays = 0;
         
         foreach ($countHolidays as $holiday) {
             $startDate = new DateTime($holiday->start_date);
             $endDate = new DateTime($holiday->end_date);
         
             // Calculate days in June for each holiday
             if ($startDate->format('Y-m') == "$year-$month") {
                 // Holiday starts in June
                 if ($endDate->format('Y-m') == "$year-$month") {
                     // Holiday ends in June
                     $totalDays += $holiday->total_days;
                 } else {
                     // Holiday spans across June and July, count days till end of June
                     $lastDayOfMonth = (int) date('t', strtotime("$year-$month-01"));
                     $totalDays += $lastDayOfMonth - $startDate->format('j') + 1;
                 }
             } else {
                 // Holiday starts before June and ends in June
                 $totalDays += $endDate->format('j');
             }
         }

        
         
        //  dd($totalDays);

         ######################################################################################################################

         $currentDate = now();

         // Determine the last day of the specified month and year
         // $lastDayOfMonth = Carbon::createFromDate($year, $month)->endOfMonth()->day;
         $lastDayOfMonth = 30;

         // Initialize an array to store the dates
         $datesInMonth = [];


         // Iterate through each day of the month
        //  for ($day = 1; $day <= $lastDayOfMonth; $day++) {
        //      $dateString = sprintf('%d-%02d-%02d', $year, $month, $day);
        //      $datesInMonth[] = $dateString;

        //      // Stop adding dates if we reach the current date
        //      if ($year == $currentDate->year && $month == $currentDate->month && $day == $currentDate->day) {
        //          break;
        //      }
        //  }

        // for ($day = $day_digit; $day <= $lastDayOfMonth; $day++) {
        for ($day = 1; $day <= $lastDayOfMonth; $day++) {
            $dateString = sprintf('%d-%02d-%02d', $year, $month, $day);
            $datesInMonth[] = $dateString;

            if ($year == $currentDate->year && $month == $currentDate->month && $day == $currentDate->day) {
                break;
            }
        }

         // Count the total number of dates
         // dd($datesInMonth);
         // $totalDatesCount = count($datesInMonth);
         // dd($totalDatesCount);

     /////////////////////////////////////////////////////////////////////////////////

     // dd($datesInMonth);
 
     // Fetch attendance records for the specified month and employee
     $attendanceRecords = DB::table('attendance_employees')
         ->where('employee_id', $empId)
         ->whereMonth('date', $month)
         ->whereYear('date', $year)
         ->pluck('date')
         ->toArray();

     $employee = Employee::where('id', '=', $empId)->first();
     // dd($attendanceRecords);

     $manageWeekOffRecords = DB::table('manage_weekoff')
         ->where('employee_id', $employee->user_id)
         ->whereMonth('week_off_date', $month)
         ->whereYear('week_off_date', $year)
         ->pluck('week_off_date')
         ->toArray();

     // dd($manageWeekOffRecords);
 
     // Identify dates that are missing in attendance records
     $missingDates = array_diff($datesInMonth, $attendanceRecords);
        // dd($missingDates, count($missingDates));
    // Remove dates that are also leaves (excluding leave_type_id 2 or Full Holi Day)
      foreach ($checkLeaves as $leave) {
        $startDate = $leave->start_date;
        $endDate = $leave->end_date;

        // Iterate through the missing dates and remove those that fall within the leave period
        foreach ($missingDates as $key => $date) {
            if ($date >= $startDate && $date <= $endDate) {
                unset($missingDates[$key]);
            }
        }
    }

    // dd($missingDates, count($missingDates));

     $finalExceptDates = array_diff($missingDates, $manageWeekOffRecords);
    //  dd($missingDates,$finalExceptDates);

    // $daysComeAttendance = 30 - count($finalExceptDates);

     return $data = array(
         "missingDates" => $missingDates,
         "finalExceptDates" => $finalExceptDates,
         "missing_dates_count" => count($missingDates),
         "finalExcept_dates_count" => count($finalExceptDates) - $totalDays,
         'daysComeAttendance' => count($attendanceRecords),
         'weekoffs' => count($manageWeekOffRecords)
     );

    }
    
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


}


if(! function_exists('calculateNetPayable_helper')){
function calculateNetPayable_helper($month, $year, $empId, $otherSalary, $weekoff){
    // dd($empId);

    $netSalary = !empty($otherSalary) ? $otherSalary : 0;

    $leaves = LocalLeave::where('employee_id', $empId)
            ->where('status', 'Approved')
            ->whereYear('start_date', $year)
            ->whereMonth('start_date', $month)
            ->get();

        // dd($leaves);

        $totalDeduction = 0;

        foreach ($leaves as $leave) {
            $leaveType = LeaveType::find($leave->leave_type_id);

            $leaveDays = Carbon::parse($leave->start_date)->diffInDays(Carbon::parse($leave->end_date)) + 1;
            // dd($leaveDays);

            switch ($leaveType->title) {
                case 'Short Leave':
                    // Deduct 20% of one day's salary
                    // $dailySalary = $netSalary / Carbon::createFromDate($year, $month)->daysInMonth;
                    $dailySalary = $netSalary / 30;
                    $deduction = 0.2 * $dailySalary * $leaveDays;
                    break;
                case 'Half Day':
                    // Deduct 50% of one day's salary
                    // $dailySalary = $netSalary / Carbon::createFromDate($year, $month)->daysInMonth;
                    $dailySalary = $netSalary / 30;
                    $deduction = 0.5 * $dailySalary * $leaveDays;
                    break;
                case 'Full Day Leave':
                    // Deduct full day's salary
                    // $dailySalary = $netSalary / Carbon::createFromDate($year, $month)->daysInMonth;
                    $dailySalary = $netSalary / 30;
                    $deduction = $dailySalary * $leaveDays;
                    break;
                case 'Weekoff':
                        $deduction = 0;
                        break;  
                default:
                    $deduction = 0;
                    break;
            }

            $totalDeduction += $deduction;
        }
        // dd($totalDeduction);

        $netPayable = $netSalary - $totalDeduction;
        // return $netPayable;
        return $data = array(
            "netPayable" => $netPayable,
            "leaves" => count($leaves)
        );
}

}

if(! function_exists('calculateWorkingDays')){
    
function calculateWorkingDays($startDate, $endDate) {
    $totalDays = 0;
    $currentDate = clone $startDate;

    while ($currentDate <= $endDate) {
        // Check if current day is not Saturday or Sunday
        if ($currentDate->format('N') < 6) {
            $totalDays++;
        }
        $currentDate->modify('+1 day');
    }

    return $totalDays;
}
}

