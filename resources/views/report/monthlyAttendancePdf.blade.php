<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monthly Attendance Report</title>
    <style>
        body {
            margin: 0 !important;
            padding: 0 !important;
            font-family: Arial, sans-serif;
            font-size: 0.6rem;
        }

        .report-container {
            width: 100%;
            border-collapse: collapse;
        }

        .report-container th, .report-container td {
            border: 1px solid #000;
            padding: 2px;
            text-align: center;
        }

        .no-border {
            border: none !important;
        }

        .report-header {
            text-align: center;
            margin-bottom: 10px;
        }

        .report-header h2 {
            margin: 2px 0;
        }

        .small-text {
            font-size: 8px;
        }

        .text-left {
            text-align: left;
        }

        .bg-even {
        background-color: #ffffff !important;
        }
        .bg-odd {
            background-color: #ffffff !important;
        }

        .table-rodd:nth-child(odd){
            background-color: #f2f0eb !important;
        }

        .table-rodd:nth-child(even){
            background-color: white !important;
        }
        
    </style>
</head>
<body>

<div class="report-footer small-text">
    <p style="font-size:12px !important;"><b>Print Date <?php echo date('m/d/Y H:i:s'); ?></b></p>
    <img style="float: right !important; margin-top:-70px !important;" src="https://hrm.junglesafariindia.in/storage/uploads/logo/dark_logo.png?1725617205" alt="Company Logo" width="150" height="100" />
</div>


<div class="report-header">
    <p style="color:#f21177 !important; margin-left:140px !important;"><b>Report from : 01-{{ $month }}-{{$year}} To : 31-{{ $month }}-{{$year}}</b></p>
    <h2 style="color:#6FD943 !important; margin-left:5px !important;"><b>Monthly Attendance Report</b></h2>
    <p style="color:blue !important;"><b>Company Name : Daily Tour & Travel Pvt Ltd</b></p>
    <p style="color:brown !important;"><b>Branch : Jungle Safari India A2 Second Floor, Pandav Nagar Delhi 110092</b></p>
</div>

@php

$attendanceRecords = DB::table('attendance_employees')
    ->whereYear('date', $year)
    ->whereMonth('date', $month)
    ->get()
    ->groupBy('employee_id');
@endphp

@foreach($employees as $employee)
@php
    //$employee->id=12;

    $department = \App\Models\Department::where('id', $employee->department_id)->value('name');
    $designation = \App\Models\Designation::where('id', $employee->designation_id)->value('name');

    $otherSalary = $employee->salary ?? 0.0;

    $daysData = attendanceDaysDeductionsForPdf($month, $year, $employee->id, $otherSalary, $employee->shift_code, $employee->enable_weekoff, $employee->company_doj, $employee->enable_ot);

    $lateHours = DB::table('attendance_employees')
        ->where('employee_id', $employee->id)
        ->whereYear('date', $year)
        ->whereMonth('date', $month)
        ->whereRaw('TIME_TO_SEC(late) > 0') 
        ->select(DB::raw('SUM(TIME_TO_SEC(late)) as total_late_seconds'))
        ->pluck('total_late_seconds')
        ->first() ?? 0;

    
    $overtime = DB::table('attendance_employees')
            ->where('employee_id', $employee->id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->select(DB::raw('SUM(TIME_TO_SEC(overtime)) as total_overtime_seconds'))
            ->pluck('total_overtime_seconds')
            ->first() ?? 0; // Default to 0 if no records are found

    
    $hours = floor($lateHours / 3600);
    $minutes = floor(($lateHours % 3600) / 60);

    
    $othours = floor($overtime / 3600);
    $otminutes = floor(($overtime % 3600) / 60);

    
    $formattedLate = sprintf('%02d:%02d', $hours, $minutes);
    $formattedot = sprintf('%02d:%02d', $othours, $otminutes);  

    $attendanceByDate = $attendanceRecords->get($employee->id, collect())->keyBy('date');

    $shift = \App\Models\ManageShift::where('shift_code', $employee->shift_code)->value('shift_hours');
    $shift_start_time = \App\Models\ManageShift::where('shift_code', $employee->shift_code)->value('start_time');
    $shift_end_time = \App\Models\ManageShift::where('shift_code', $employee->shift_code)->value('end_time');

    if (strpos($shift, ':') !== false) {
        $shiftTimeParts = explode(':', $shift);
        $shiftHours = (int)$shiftTimeParts[0];
        $shiftMinutes = (int)$shiftTimeParts[1];
        $shiftSeconds = (int)$shiftTimeParts[2];

        $shiftHoursTotal = $shiftHours + ($shiftMinutes / 60) + ($shiftSeconds / 3600);
    } else {
        $shiftHoursTotal = (float)$shift; // Assume it's a numeric value
    }

    $currentMonth = $month; 
    $currentYear = $year;  
    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $currentMonth, $currentYear);

    $daysInMonthWrk = $daysData['present'];
    $totalHours = $shiftHoursTotal * $daysInMonthWrk;

    if($employee->enable_ot=="Enabled"){

        $subtractHours = $hours;
        $subtractMinutes = $minutes;
        $totalMinutes = ($totalHours * 60);
        $subtractMinutesTotal = ($subtractHours * 60) + $subtractMinutes;
        $remainingMinutes = $totalMinutes - $subtractMinutesTotal;
        $remainingHours = intdiv($remainingMinutes, 60);
        $remainingMinutes = $remainingMinutes % 60;
        $lateHoursResult = sprintf('%d:%02d', $remainingHours, $remainingMinutes);

        $totalHours = $lateHoursResult; 
        list($hoursGet, $minutesGet) = explode(':', $totalHours);
        $hoursGetting = (int)$hoursGet;
        $minutesGetting = (int)$minutesGet;

        $get1 = $othours * 60 + $otminutes;
        $get2 = ($hoursGetting * 60) + $minutesGetting;
        $get3 = $get1 + $get2;

        $semiHours = floor($get3 / 60);
        $semiMinutes = $get3 % 60;
        $semiFormattedMinutes = str_pad($semiMinutes, 2, '0', STR_PAD_LEFT);
        $totalHours = "{$semiHours}:{$semiFormattedMinutes}";

    }else{

        $subtractHours = $hours;
        $subtractMinutes = $minutes;
        $totalMinutes = ($totalHours * 60);
        $subtractMinutesTotal = ($subtractHours * 60) + $subtractMinutes;
        $remainingMinutes = $totalMinutes - $subtractMinutesTotal;
        $remainingHours = intdiv($remainingMinutes, 60);
        $remainingMinutes = $remainingMinutes % 60;
        $lateHoursResult = sprintf('%d:%02d', $remainingHours, $remainingMinutes);

        $totalHours = $lateHoursResult;
    }

    $backgroundClass = ($employee->id % 2 == 0) ? 'bg-even' : 'bg-odd';

@endphp

<table class="report-container <?php echo $backgroundClass; ?>">
    <thead>
        <tr class="table-rodd">
            <th colspan="4" class="text-left">Dept.: {{ $department }}</th>
            <th colspan="10" class="text-left">Design: {{ $designation }}</th>
            <th colspan="4">Attendance Month OF: {{ $month }} - {{$year}}</th>
        </tr>
        
        <tr style="border-bottom:1px solid #000;" class="table-rodd">
            <th class="no-border"><b>EmpCode</b></th>
            <th colspan="8" class="no-border"><b>Name</b></th>
            <th class="no-border" style="color:green !important;"><b>Present</b></th>
            <th class="no-border" style="color:brown !important;"><b>HL</b></th>
            <th class="no-border" style="color:blue !important;"><b>WO</b></th>
            <th class="no-border" style="color:red !important;"><b>Absent</b></th>
            <th class="no-border" style="color:red !important;"><b>Leave</b></th>
            <th class="no-border" style="color:green !important;"><b>PaidDays</b></th>
            <th class="no-border" style="color:red !important;"><b>LateHrs</b></th>
            <th class="no-border" style="color:green !important;"><b>WorkHrs</b></th>
            <th class="no-border" style="color:#e31bc5 !important;"><b>OvTim</b></th>
        </tr>
    </thead>
    <tbody>
        <tr class="table-rodd">
            <td class="no-border"><b>#EMP00000{{$employee->id}}</b></td>
            <td colspan="8" class="no-border"><b>{{$employee->name}}</b></td>
            <td class="no-border" style="color:green !important;"><b>{{ $daysData['present'] }}</b></td>
            <td class="no-border" style="color:brown !important;"><b>{{ $daysData['holidays'] }}</b></td>
            <td class="no-border" style="color:blue !important;"><b>{{ $daysData['weekoffs'] }}</b></td>
            <td class="no-border" style="color:red !important;"><b>{{ $daysData['absent'] }}</b></td>
            <td class="no-border" style="color:red !important;"><b>{{ $daysData['leave'] }}</b></td>
            <td class="no-border" style="color:green !important;"><b>{{ $daysData['present'] }}</b></td>
            <td class="no-border" style="color:red !important;"><b>{{ $formattedLate }}</b></td>
            <td class="no-border" style="color:green !important;"><b>{{ $totalHours }}</b></td>
            <td class="no-border" style="color:#e31bc5 !important;"><b>{{ $employee->enable_ot=="Enabled" ? $formattedot : '00:00'}}</b></td>
        </tr>
    </tbody>
</table>

<table class="report-container <?php echo $backgroundClass; ?>" style="margin-bottom:20px !important;">
    <thead>
        <tr style="background-color:white !important;">
            <th><b>Date</b></th>
            @for($day = 1; $day <= $daysInMonth; $day++)
                <th>{{ str_pad($day, 2, '0', STR_PAD_LEFT) }}</th>
            @endfor
        </tr>
    </thead>
    <tbody>
        <tr class="table-rodd">
            <td style="color:green !important;"><b>Arrived Time</b></td>
            @for($day = 1; $day <= $daysInMonth; $day++)
                @php
                    $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
                    $clockIn = $attendanceByDate->get($date)->clock_in ?? '00:00';
                    $timeInObj = new DateTime($clockIn);
                    $formattedTimeIn = $timeInObj->format('H:i');
                @endphp
                <td>{{ $formattedTimeIn }}</td>
            @endfor
        </tr>
        <tr class="table-rodd">
            <td style="color:red !important;"><b>Dept. Time</b></td>
            @for($day = 1; $day <= $daysInMonth; $day++)
                @php
                    $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
                    $clockOut = $attendanceByDate->get($date)->clock_out ?? '00:00';
                    $timeObj = new DateTime($clockOut);
                    $formattedTime = $timeObj->format('H:i');
                @endphp
                <td>{{ $formattedTime }}</td>
            @endfor
        </tr>
        <tr class="table-rodd">
            <td style="color:blue !important;"><b>Working Hrs</b></td>
            @for($day = 1; $day <= $daysInMonth; $day++)
            @php
                $date = sprintf('%04d-%02d-%02d', $year, $month, $day);

                // $date="2024-08-31";
                
                $overtime = $attendanceByDate->get($date)->overtime ?? '00:00';
                $timeOtObj = new DateTime($overtime);
                $formattedOts = $timeOtObj->format('H:i');

                $late = $attendanceByDate->get($date)->late ?? '00:00';
                $lateObj = new DateTime($late);
                $checkingLate = isNegativeTime($late)=="true" ?  '00:00' : $lateObj->format('H:i');

                $earlygoing = $attendanceByDate->get($date)->early_leaving ?? '00:00';
                $earlygoingObj = new DateTime($earlygoing);
                $checkingEarlygoing = $earlygoingObj->format('H:i');
            @endphp

            <td>{{ getDayTotalHours($date, $formattedOts, $formattedTimeIn, $shift_start_time, $shift, $employee->enable_ot, $employee->id, $checkingLate, $checkingEarlygoing ) }}</td>
            @endfor
        </tr>
        <tr class="table-rodd">
            <td style="color:#f211d4 !important;"><b>O.Times Hrs.</b></td>
            @for($day = 1; $day <= $daysInMonth; $day++)
                @php
                    $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
                    $overtime = $attendanceByDate->get($date)->overtime ?? '00:00';
                    $timeOtObj = new DateTime($overtime);
                    $formattedOts = $timeOtObj->format('H:i');
                @endphp
                <td>{{ $employee->enable_ot=="Enabled" ? $formattedOts : '00:00' }}</td>
            @endfor
        </tr>
        <tr class="table-rodd">
            <td style="color:brown !important;"><b>Status</b></td>
            @for($day = 1; $day <= $daysInMonth; $day++)
                @php
                    $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
                    $status = 'A'; // Default to Absent

                    //  $date = "2024-08-27";

                    $holidays = DB::table('holidays')
                                ->whereDate('start_date', '<=', $date)
                                ->whereDate('end_date', '>=', $date)
                                ->get();

                if ($holidays->isNotEmpty()) {
                    $status = 'HL'; // Holiday
                } else {


                    $leaveRecords = DB::table('leaves')
                                    ->where('employee_id', $employee->id)
                                    ->where(function($query) use ($date) {
                                        $query->where('start_date', '<=', $date)
                                            ->where('end_date', '>=', $date);
                                    })
                                    ->get();


                    if($leaveRecords){
                    foreach ($leaveRecords as $leave) {

                        $leaveType = DB::table('leave_types')->where('id', $leave->leave_type_id)->first();
                       
                        if ($leaveType) {
                            
                            if ($leaveType->title === 'Short Leave') {

                                $late = $attendanceByDate->get($date)->late ?? '00:00';
                                $lateObj = new DateTime($late);
                                $checkingLate = isNegativeTime($late)=="true" ?  '00:00' : $lateObj->format('H:i');
                            
                                $lateAllow = new DateTime($permittedLateArrival);
                                $arrivalTime = new DateTime($checkingLate);

                                if($arrivalTime > $lateAllow){
                                    $status = 'PSL-LT';
                                }else{
                                    $status = 'PSL';
                                }

                            } elseif ($leaveType->title === 'Half Day') {
                                
                                if ($leave->start_date == $date || $leave->end_date == $date) {

                                    $late = $attendanceByDate->get($date)->late ?? '00:00';
                                    $lateObj = new DateTime($late);
                                    $checkingLate = isNegativeTime($late)=="true" ?  '00:00' : $lateObj->format('H:i');
                                
                                    $lateAllow = new DateTime($permittedLateArrival);
                                    $arrivalTime = new DateTime($checkingLate);

                                    if($arrivalTime > $lateAllow){
                                        $status = 'P/2-LT';
                                    }else{
                                        $status = 'P/2';
                                    }
                                   
                                 }
                            } elseif ($leaveType->title === 'Full Day Leave') {
                                $status = 'A'; 
                            } elseif ($leaveType->title === 'Weekoff') {
                                $status = 'WO';
                            }
                        }
                    }
                }
             

                    $timeSheet = DB::table('time_sheets2')->first();
                    $permittedLateArrival = $timeSheet ? $timeSheet->permitted_late_arrival : '00:00:00';
                    
                    if ($attendanceByDate->has($date)) {
                        if( $status=="P/2" || $status=="PSL"){
                           $status=$status;
                       }else{

                        $late = $attendanceByDate->get($date)->late ?? '00:00';
                        $lateObj = new DateTime($late);
                        $checkingLate = isNegativeTime($late)=="true" ?  '00:00' : $lateObj->format('H:i');
                        
                        $lateAllow = new DateTime($permittedLateArrival);
                        $arrivalTime = new DateTime($checkingLate);

                        $end_time1 = $attendanceByDate->get($date)->clock_out ?? '00:00';
                        $endTimeObj1 = new DateTime($end_time1);
                        $checkEndTime1 = $endTimeObj1->format('H:i') ?? '00:00';
                       
                        $shiftEndTime1 = new DateTime($shift_end_time);
                        $goingTime1 = new DateTime($checkEndTime1);
                        $goingTimeDefine1 = new DateTime("12:00");

                            if ($arrivalTime > $lateAllow) {
                                $status = "P-L";
                            } 
                            elseif ($goingTime1 > $goingTimeDefine1 && $goingTime1 < $shiftEndTime1){
                                $status = 'P-EG';
                            }
                            else{
                                $status = 'P';
                            }
                        }
                    }

                }

                $statuses = [
                    "P" => "green",
                    "A" => "red",
                    "HL" => "brown",
                    "PSL-LT" => "#0af253",
                    "PSL" => "#f20a7a",
                    "P/2-LT" => "#f2860a",
                    "P/2" => "#f5672a",
                    "WO" => "#920cf2",
                    "P-L" => "#ff7233",
                    "P-EG" => "#fb18d5"
                ];
            
                $color = $statuses[$status] ?? 'black';

                @endphp
                <td style="color: {{ $color }} !important;"><b>{{ $status }}</b></td>
            @endfor
        </tr>
        <tr class="table-rodd">
            <td style="color:#f21177 !important;"><b>Shift</b></td>
            @for($day = 1; $day <= $daysInMonth; $day++)
                <td>{{$employee->shift_code}}</td>
            @endfor
        </tr>
    </tbody>
</table>

@endforeach

</body>
</html>
