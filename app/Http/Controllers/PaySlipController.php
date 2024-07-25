<?php

namespace App\Http\Controllers;

use App\Exports\PayslipExport;
use App\Models\Allowance;
use App\Models\Commission;
use App\Models\Employee;
use App\Models\Loan;
use App\Mail\InvoiceSend;
use App\Mail\PayslipSend;
use App\Models\AccountList;
use App\Models\Expense;
use App\Models\OtherPayment;
use App\Models\Overtime;
use App\Models\ManageShift;
use App\Models\PaySlip;
use App\Models\Department;
use App\Models\LeaveType;
use App\Models\Resignation;
use App\Models\SaturationDeduction;
use App\Models\Termination;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\Leave as LocalLeave;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use DateTime;
use Illuminate\Support\Facades\Storage;

class PaySlipController extends Controller
{

    public function index()
    {   
        if (\Auth::user()->can('Manage Pay Slip') || \Auth::user()->type == 'employee') {
            $employees = Employee::where(
                [
                    'created_by' => \Auth::user()->creatorId(),
                ]
            )->first();

            $month = [
                '01' => 'JAN',
                '02' => 'FEB',
                '03' => 'MAR',
                '04' => 'APR',
                '05' => 'MAY',
                '06' => 'JUN',
                '07' => 'JUL',
                '08' => 'AUG',
                '09' => 'SEP',
                '10' => 'OCT',
                '11' => 'NOV',
                '12' => 'DEC',
            ];

            $year = [
                // '2020' => '2020',
                '2021' => '2021',
                '2022' => '2022',
                '2023' => '2023',
                '2024' => '2024',
                '2025' => '2025',
                '2026' => '2026',
                '2027' => '2027',
                '2028' => '2028',
                '2029' => '2029',
                '2030' => '2030',
            ];
            // dd($employees);
            return view('payslip.index', compact('employees', 'month', 'year'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        //
    }

    // public function store(Request $request)
    // {
    //     $validator = \Validator::make(
    //         $request->all(),
    //         [
    //             'month' => 'required',
    //             'year' => 'required',

    //         ]
    //     );

    //     if ($validator->fails()) {
    //         $messages = $validator->getMessageBag();

    //         return redirect()->back()->with('error', $messages->first());
    //     }

    //     $month = $request->month;
    //     $year  = $request->year;

        
    //     $formate_month_year = $year . '-' . $month;
    //     $validatePaysilp    = PaySlip::where('salary_month', '=', $formate_month_year)->where('created_by', \Auth::user()->creatorId())->pluck('employee_id');
    //     $payslip_employee   = Employee::where('created_by', \Auth::user()->creatorId())->where('company_doj', '<=', date($year . '-' . $month . '-t'))->count();
        
    //     if ($payslip_employee > count($validatePaysilp)) {
    //         $employees = Employee::where('created_by', \Auth::user()->creatorId())->where('company_doj', '<=', date($year . '-' . $month . '-t'))->whereNotIn('employee_id', $validatePaysilp)->get();
            
    //         $employeesSalary = Employee::where('created_by', \Auth::user()->creatorId())->where('salary', '<=', 0)->first();
            
    //         if (!empty($employeesSalary)) {
    //             return redirect()->route('payslip.index')->with('error', __('Please set employee salary.'));
    //         }
    //         foreach ($employees as $employee) {
                
    //             $check = Payslip::where('employee_id', $employee->id)->where('salary_month', $formate_month_year)->first();
    //             $terminationDate = Termination::where('employee_id', $employee->id)
    //             ->whereDate('termination_date', '<=', Carbon::create($year, $month)->endOfMonth())
    //             ->exists();
                
    //             $resignationDate = Resignation::where('employee_id', $employee->id)
    //             ->whereDate('resignation_date', '<=', Carbon::create($year, $month)->endOfMonth())
    //             ->exists();
                
    //             if ($terminationDate || $resignationDate) {
    //                 continue;
    //             }
                
    //             if (!$check && $check == null) {
    //                 $payslipEmployee                       = new PaySlip();
    //                 $payslipEmployee->employee_id          = $employee->id;
    //                 $payslipEmployee->net_payble           = $employee->get_net_salary();
    //                 $payslipEmployee->salary_month         = $formate_month_year;
    //                 $payslipEmployee->status               = 0;
    //                 $payslipEmployee->basic_salary         = !empty($employee->salary) ? $employee->salary : 0;
    //                 $payslipEmployee->allowance            = Employee::allowance($employee->id);
    //                 $payslipEmployee->commission           = Employee::commission($employee->id);
    //                 $payslipEmployee->loan                 = Employee::loan($employee->id);
    //                 $payslipEmployee->saturation_deduction = Employee::saturation_deduction($employee->id);
    //                 $payslipEmployee->other_payment        = Employee::other_payment($employee->id);
    //                 $payslipEmployee->overtime             = Employee::overtime($employee->id);
    //                 $payslipEmployee->created_by           = \Auth::user()->creatorId();
                    
    //                 $payslipEmployee->save();
    //                 // }
                    
    //                 // slack 
    //                 $setting = Utility::settings(\Auth::user()->creatorId());
    //                 // $month = date('M Y', strtotime($payslipEmployee->salary_month . ' ' . $payslipEmployee->time));
    //                 if (isset($setting['monthly_payslip_notification']) && $setting['monthly_payslip_notification'] == 1) {
    //                     // $msg = ("payslip generated of") . ' ' . $month . '.';

    //                     $uArr = [
    //                         'year' => $formate_month_year,
    //                     ];
    //                     Utility::send_slack_msg('new_monthly_payslip', $uArr);
    //                 }

    //                 // telegram 
    //                 $setting = Utility::settings(\Auth::user()->creatorId());
    //                 // $month = date('M Y', strtotime($payslipEmployee->salary_month . ' ' . $payslipEmployee->time));
    //                 if (isset($setting['telegram_monthly_payslip_notification']) && $setting['telegram_monthly_payslip_notification'] == 1) {
    //                     // $msg = ("payslip generated of") . ' ' . $month . '.';

    //                     $uArr = [
    //                         'year' => $formate_month_year,
    //                     ];

    //                     Utility::send_telegram_msg('new_monthly_payslip', $uArr);
    //                 }


    //                 // twilio
    //                 $setting  = Utility::settings(\Auth::user()->creatorId());
    //                 $emp = Employee::where('id', $payslipEmployee->employee_id = \Auth::user()->id)->first();
    //                 if (isset($setting['twilio_monthly_payslip_notification']) && $setting['twilio_monthly_payslip_notification'] == 1) {
    //                     $employeess = Employee::where($request->employee_id)->get();
    //                     foreach ($employeess as $key => $employee) {
    //                         // $msg = ("payslip generated of") . ' ' . $month . '.';

    //                         $uArr = [
    //                             'year' => $formate_month_year,
    //                         ];
    //                         Utility::send_twilio_msg($emp->phone, 'new_monthly_payslip', $uArr);
    //                     }
    //                 }

    //                 //webhook
    //                 $module = 'New Monthly Payslip';
    //                 $webhook =  Utility::webhookSetting($module);
    //                 if ($webhook) {
    //                     $parameter = json_encode($payslipEmployee);
    //                     // 1 parameter is  URL , 2 parameter is data , 3 parameter is method
    //                     $status = Utility::WebhookCall($webhook['url'], $parameter, $webhook['method']);
    //                     if ($status == true) {
    //                         return redirect()->back()->with('success', __('Payslip successfully created.'));
    //                     } else {
    //                         return redirect()->back()->with('error', __('Webhook call failed.'));
    //                     }
    //                 }
    //             }
    //         }
    //         return redirect()->route('payslip.index')->with('success', __('Payslip successfully created.'));
    //     } else {
    //         return redirect()->route('payslip.index')->with('error', __('Payslip Already created.'));
    //     }
    // }

    public function store(Request $request)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'month' => 'required',
                'year' => 'required',

            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        $month = $request->month;
        $year  = $request->year;

        
        $formate_month_year = $year . '-' . $month;
        $validatePaysilp    = PaySlip::where('salary_month', '=', $formate_month_year)->where('created_by', \Auth::user()->creatorId())->pluck('employee_id');
        // dd($validatePaysilp);
        $payslip_employee   = Employee::where('created_by', \Auth::user()->creatorId())->where('company_doj', '<=', date($year . '-' . $month . '-t'))->count();
        // dd($payslip_employee);
        if ($payslip_employee > count($validatePaysilp)) {
            $employees = Employee::where('created_by', \Auth::user()->creatorId())->where('company_doj', '<=', date($year . '-' . $month . '-t'))->whereNotIn('employee_id', $validatePaysilp)->get();
            
            $employeesSalary = Employee::where('created_by', \Auth::user()->creatorId())->where('salary', '<=', 0)->first();
            // dd($employeesSalary);
            if (!empty($employeesSalary)) {
                return redirect()->route('payslip.index')->with('error', __('Please set employee salary.'));
            }
            foreach ($employees as $employee) {
                
                $check = Payslip::where('employee_id', $employee->id)->where('salary_month', $formate_month_year)->first();
                $terminationDate = Termination::where('employee_id', $employee->id)
                ->whereDate('termination_date', '<=', Carbon::create($year, $month)->endOfMonth())
                ->exists();
                
                $resignationDate = Resignation::where('employee_id', $employee->id)
                ->whereDate('resignation_date', '<=', Carbon::create($year, $month)->endOfMonth())
                ->exists();
                
                if ($terminationDate || $resignationDate) {
                    continue;
                }
                // dd($employee->id);

                //Calculation Salary Here Start Here
                    // $getNetPayble = $this->calculateNetPayable($month, $year, $employee->id, $employee->get_net_salary());
                    $getNetPayble = $this->calculateNetPayable($month, $year, $employee->id, $employee->salary, $employee->enable_weekoff);
                    // dd($getNetPayble);

                    // $getAttendanceSalary = $this->attendanceNatPayable($month, $year, $employee->id, $employee->get_net_salary());
                    $getAttendanceSalary = $this->attendanceNatPayable($month, $year, $employee->id, $employee->salary, $employee->shift_code, $employee->enable_weekoff, $employee->company_doj);
                    // dd($getAttendanceSalary);

                    $leaveManageSal = $employee->salary - $getNetPayble;
                    $attendanceSal = $employee->salary - $getAttendanceSalary['netPayable'];

                    $getDeductSal =  $leaveManageSal + $attendanceSal;

                    $getNatEmpSal   = $employee->get_net_salary() - $employee->salary;
                    // dd($leaveManageSal,$attendanceSal,$getNatEmpSal);

                    $addAllPayment = $employee->salary + $getNatEmpSal;
                    $getFinalizePrice = $addAllPayment - $getDeductSal;

                    // Overtime Salary Start
                    if($employee->enable_ot == "Enabled"){
                        // dd($getFinalizePrice,"if",$getAttendanceSalary['getFinalOTSal']);
                        $salaryFinalized = $getFinalizePrice + $getAttendanceSalary['getFinalOTSal'];
                    }else{
                        // dd("else");
                        $salaryFinalized = $getFinalizePrice;
                    }

                    // dd($salaryFinalized);
                    
                    // Overtime Salary End

                //Calculation Salary Here End Here

                #######################################
                $this->nettPayableBanksheet($month, $year,$salaryFinalized,$employee->name,$employee->phone,$employee->account_number,$employee->bank_identifier_code);
                #######################################

                
                if (!$check && $check == null) {
                    $payslipEmployee                       = new PaySlip();
                    $payslipEmployee->employee_id          = $employee->id;
                    // $payslipEmployee->net_payble           = $employee->get_net_salary();
                    $payslipEmployee->net_payble           = $salaryFinalized;
                    $payslipEmployee->salary_month         = $formate_month_year;
                    $payslipEmployee->status               = 0;
                    $payslipEmployee->basic_salary         = !empty($employee->salary) ? $employee->salary : 0;
                    $payslipEmployee->allowance            = Employee::allowance($employee->id);
                    $payslipEmployee->commission           = Employee::commission($employee->id);
                    $payslipEmployee->loan                 = Employee::loan($employee->id);
                    $payslipEmployee->saturation_deduction = Employee::saturation_deduction($employee->id);
                    $payslipEmployee->other_payment        = Employee::other_payment($employee->id);
                    $payslipEmployee->overtime             = Employee::overtime($employee->id);
                    $payslipEmployee->created_by           = \Auth::user()->creatorId();
                    
                    $payslipEmployee->save();
                    // }
                    
                    // slack 
                    $setting = Utility::settings(\Auth::user()->creatorId());
                    // $month = date('M Y', strtotime($payslipEmployee->salary_month . ' ' . $payslipEmployee->time));
                    if (isset($setting['monthly_payslip_notification']) && $setting['monthly_payslip_notification'] == 1) {
                        // $msg = ("payslip generated of") . ' ' . $month . '.';

                        $uArr = [
                            'year' => $formate_month_year,
                        ];
                        Utility::send_slack_msg('new_monthly_payslip', $uArr);
                    }

                    // telegram 
                    $setting = Utility::settings(\Auth::user()->creatorId());
                    // $month = date('M Y', strtotime($payslipEmployee->salary_month . ' ' . $payslipEmployee->time));
                    if (isset($setting['telegram_monthly_payslip_notification']) && $setting['telegram_monthly_payslip_notification'] == 1) {
                        // $msg = ("payslip generated of") . ' ' . $month . '.';

                        $uArr = [
                            'year' => $formate_month_year,
                        ];

                        Utility::send_telegram_msg('new_monthly_payslip', $uArr);
                    }


                    // twilio
                    $setting  = Utility::settings(\Auth::user()->creatorId());
                    $emp = Employee::where('id', $payslipEmployee->employee_id = \Auth::user()->id)->first();
                    if (isset($setting['twilio_monthly_payslip_notification']) && $setting['twilio_monthly_payslip_notification'] == 1) {
                        $employeess = Employee::where($request->employee_id)->get();
                        foreach ($employeess as $key => $employee) {
                            // $msg = ("payslip generated of") . ' ' . $month . '.';

                            $uArr = [
                                'year' => $formate_month_year,
                            ];
                            Utility::send_twilio_msg($emp->phone, 'new_monthly_payslip', $uArr);
                        }
                    }

                    //webhook
                    $module = 'New Monthly Payslip';
                    $webhook =  Utility::webhookSetting($module);
                    if ($webhook) {
                        $parameter = json_encode($payslipEmployee);
                        // 1 parameter is  URL , 2 parameter is data , 3 parameter is method
                        $status = Utility::WebhookCall($webhook['url'], $parameter, $webhook['method']);
                        if ($status == true) {
                            return redirect()->back()->with('success', __('Payslip successfully created.'));
                        } else {
                            return redirect()->back()->with('error', __('Webhook call failed.'));
                        }
                    }
                }
            }
            return redirect()->route('payslip.index')->with('success', __('Payslip successfully created.'));
        } else {
            return redirect()->route('payslip.index')->with('error', __('Payslip Already created.'));
        }
    }

    public function calculateNetPayable($month, $year, $empId, $otherSalary, $weekoff){
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
            return $netPayable;
    }



    // public function attendanceNatPayable($month, $year, $empId, $otherSalary) {
    //     // Fetch attendance data for the given month and year
    //     $attendanceRecords = DB::table('attendance_employees')
    //         ->where('employee_id', $empId)
    //         ->whereMonth('date', $month)
    //         ->whereYear('date', $year)
    //         ->get();
    
    //     // Fetch the applicable time sheet policy (assuming there's only one policy)
    //     $timeSheetPolicy = DB::table('time_sheets2')->first();
    
    //     // Initialize variables for calculations
    //     $totalDeduction = 0;
    //     $dailySalary = $otherSalary / 30; // Assuming salary is divided by 30 days
    
    //     // Arrays to track processed IDs for late and early leaving
    //     $processedLateIds = [];
    //     $processedEarlyLeavingIds = [];
    
    //     // Iterate through attendance records and calculate deductions
    //     foreach ($attendanceRecords as $attendance) {
    //         // Initialize DateTime objects for late and early leaving
    //         $late = $attendance->late;
    //         $earlyLeaving = $attendance->early_leaving;
    //         $attendanceId = $attendance->id;
    
    //         // Calculate late deductions
    //         $deductionPercentage = 0; // Initialize deduction percentage for late
    //         if ($late > $timeSheetPolicy->late_4) {
    //             $deductionPercentage = $timeSheetPolicy->deduct_percentage_4;
    //         } elseif ($late > $timeSheetPolicy->late_3) {
    //             $deductionPercentage = $timeSheetPolicy->deduct_percentage_3;
    //         } elseif ($late > $timeSheetPolicy->late_2) {
    //             $deductionPercentage = $timeSheetPolicy->deduct_percentage_2;
    //         } elseif ($late > $timeSheetPolicy->late_1) {
    //             $deductionPercentage = $timeSheetPolicy->deduct_percentage_1;
    //         }
    
    //         $lateDeduction = ($deductionPercentage / 100) * $dailySalary;
    
    //         // Check if early leaving deduction has already been applied for this attendance ID
    //         if ($earlyLeaving > "00:00:00" && !in_array($attendanceId, $processedEarlyLeavingIds)) {
    //             // Calculate early leaving deductions
    //             if ($earlyLeaving < $timeSheetPolicy->early_going_1) {
    //                 $deductionPercentage = $timeSheetPolicy->deduct_percentage_early_going_1;
    //             } elseif ($earlyLeaving < $timeSheetPolicy->early_going_2) {
    //                 $deductionPercentage = $timeSheetPolicy->deduct_percentage_early_going_2;
    //             } elseif ($earlyLeaving < $timeSheetPolicy->early_going_3) {
    //                 $deductionPercentage = $timeSheetPolicy->deduct_percentage_early_going_3;
    //             } elseif ($earlyLeaving < $timeSheetPolicy->early_going_4) {
    //                 $deductionPercentage = $timeSheetPolicy->deduct_percentage_early_going_4;
    //             }
    
    //             // Calculate deduction only if not processed earlier for this attendance ID
    //             if (!in_array($attendanceId, $processedEarlyLeavingIds)) {
    //                 $earlyLeavingDeduction = ($deductionPercentage / 100) * $dailySalary;
    //                 $processedEarlyLeavingIds[] = $attendanceId; // Mark as processed by ID
    //                 $totalDeduction += $earlyLeavingDeduction; // Add to total deduction
    //             }
    //         }
    
    //         // Add late deduction to total deduction
    //         $totalDeduction += $lateDeduction;
    //     }
    //     dd($totalDeduction);
    //     // Calculate net payable salary
    //     $netPayable = $otherSalary - $totalDeduction;
    
    //     return $netPayable;
    // } 
    
    // public function attendanceNatPayable($month, $year, $empId, $otherSalary) {
    //     // Fetch attendance data for the given month and year
    //     $attendanceRecords = DB::table('attendance_employees')
    //         ->where('employee_id', $empId)
    //         ->whereMonth('date', $month)
    //         ->whereYear('date', $year)
    //         ->get();
    
    //     // Fetch the applicable time sheet policy (assuming there's only one policy)
    //     $timeSheetPolicy = DB::table('time_sheets2')->first();
    
    //     // Initialize variables for calculations
    //     $totalDeduction = 0;
    //     $dailySalary = $otherSalary / 30; // Assuming salary is divided by 30 days
    
    //     // Arrays to track processed IDs for late and early leaving
    //     $processedLateIds = [];
    //     $processedEarlyLeavingIds = [];
    
    //     // Iterate through attendance records and calculate deductions
    //     foreach ($attendanceRecords as $attendance) {
    //         // Initialize DateTime objects for late and early leaving
    //         $late = $attendance->late;
    //         $earlyLeaving = $attendance->early_leaving;
    //         $attendanceId = $attendance->id;
    
    //         // Calculate late deductions if not processed for this attendance ID
    //         if (!in_array($attendanceId, $processedLateIds)) {
    //             $deductionPercentage = 0; // Initialize deduction percentage for late
    //             if ($late > $timeSheetPolicy->late_4) {
    //                 $deductionPercentage = $timeSheetPolicy->deduct_percentage_4;
    //             } elseif ($late > $timeSheetPolicy->late_3) {
    //                 $deductionPercentage = $timeSheetPolicy->deduct_percentage_3;
    //             } elseif ($late > $timeSheetPolicy->late_2) {
    //                 $deductionPercentage = $timeSheetPolicy->deduct_percentage_2;
    //             } elseif ($late > $timeSheetPolicy->late_1) {
    //                 $deductionPercentage = $timeSheetPolicy->deduct_percentage_1;
    //             }
    
    //             $lateDeduction = ($deductionPercentage / 100) * $dailySalary;
    //             $totalDeduction += $lateDeduction;
    
    //             // Mark this attendance ID as processed for late deduction
    //             $processedLateIds[] = $attendanceId;
    //         }
    
    //         // Calculate early leaving deductions if not processed for this attendance ID
    //         if ($earlyLeaving > "00:00:00" && !in_array($attendanceId, $processedEarlyLeavingIds)) {
    //             if ($earlyLeaving < $timeSheetPolicy->early_going_1) {
    //                 $deductionPercentage = $timeSheetPolicy->deduct_percentage_early_going_1;
    //             } elseif ($earlyLeaving < $timeSheetPolicy->early_going_2) {
    //                 $deductionPercentage = $timeSheetPolicy->deduct_percentage_early_going_2;
    //             } elseif ($earlyLeaving < $timeSheetPolicy->early_going_3) {
    //                 $deductionPercentage = $timeSheetPolicy->deduct_percentage_early_going_3;
    //             } elseif ($earlyLeaving < $timeSheetPolicy->early_going_4) {
    //                 $deductionPercentage = $timeSheetPolicy->deduct_percentage_early_going_4;
    //             }
    
    //             $earlyLeavingDeduction = ($deductionPercentage / 100) * $dailySalary;
    //             $totalDeduction += $earlyLeavingDeduction;
    
    //             // Mark this attendance ID as processed for early leaving deduction
    //             $processedEarlyLeavingIds[] = $attendanceId;
    //         }
    //     }
    //     dd($totalDeduction);
    //     // Calculate net payable salary
    //     $netPayable = $otherSalary - $totalDeduction;
    
    //     return $netPayable;
    // }

    // public function attendanceNatPayable($month, $year, $empId, $otherSalary) {
    //         // Fetch attendance data for the given month and year
    //         $attendanceRecords = DB::table('attendance_employees')
    //             ->where('employee_id', $empId)
    //             ->whereMonth('date', $month)
    //             ->whereYear('date', $year)
    //             ->get();
        
    //         // Fetch the applicable time sheet policy (assuming there's only one policy)
    //         $timeSheetPolicy = DB::table('time_sheets2')->first();
        
    //         // Initialize variables for calculations
    //         $totalDeduction = 0;
    //         $dailySalary = $otherSalary / 30; // Assuming salary is divided by 30 days
        
    //         // Arrays to track processed IDs for late and early leaving
    //         $processedLateIds = [];
    //         $processedEarlyLeavingIds = [];

    //          // Arrays to track processed dates for late and early leaving
    //          $processedDates = [];
        
    //         // Iterate through attendance records and calculate deductions
    //         foreach ($attendanceRecords as $attendance) {
    //             // Check if $attendance->date is already a DateTime object
    //         if (is_string($attendance->date)) {
    //             // Convert string to DateTime object
    //             $attendanceDate = new DateTime($attendance->date);
    //         } elseif ($attendance->date instanceof DateTime) {
    //             // If already a DateTime object, use it directly
    //             $attendanceDate = $attendance->date;
    //         } else {
    //             // Handle other cases or throw an error if necessary
    //             continue; // Skip this record if date format is not as expected
    //         }
    
    //         // Get the formatted date as a string for tracking
    //         $attendanceDateString = $attendanceDate->format('Y-m-d');
    
    //         // Check if this date has already been processed
    //         if (!in_array($attendanceDateString, $processedDates)) {

    //             // Initialize DateTime objects for late and early leaving
    //             $late = $attendance->late;
    //             $earlyLeaving = $attendance->early_leaving;
    //             $attendanceId = $attendance->id;
        
    //             // Calculate late deductions if not processed for this attendance ID
    //             if (!in_array($attendanceId, $processedLateIds)) {
    //                 $deductionPercentage = 0; // Initialize deduction percentage for late
    //                 if ($late > $timeSheetPolicy->late_4) {
    //                     $deductionPercentage = $timeSheetPolicy->deduct_percentage_4;
    //                 } elseif ($late > $timeSheetPolicy->late_3) {
    //                     $deductionPercentage = $timeSheetPolicy->deduct_percentage_3;
    //                 } elseif ($late > $timeSheetPolicy->late_2) {
    //                     $deductionPercentage = $timeSheetPolicy->deduct_percentage_2;
    //                 } elseif ($late > $timeSheetPolicy->late_1) {
    //                     $deductionPercentage = $timeSheetPolicy->deduct_percentage_1;
    //                 }
        
    //                 $lateDeduction = ($deductionPercentage / 100) * $dailySalary;
    //                 $totalDeduction += $lateDeduction;
        
    //                 // Mark this attendance ID as processed for late deduction
    //                 $processedLateIds[] = $attendanceId;
    //             }
        
    //             // Calculate early leaving deductions if not processed for this attendance ID
    //             if ($earlyLeaving > "00:00:00" && !in_array($attendanceId, $processedEarlyLeavingIds)) {
    //                 if ($earlyLeaving < $timeSheetPolicy->early_going_1) {
    //                     $deductionPercentage = $timeSheetPolicy->deduct_percentage_early_going_1;
    //                 } elseif ($earlyLeaving < $timeSheetPolicy->early_going_2) {
    //                     $deductionPercentage = $timeSheetPolicy->deduct_percentage_early_going_2;
    //                 } elseif ($earlyLeaving < $timeSheetPolicy->early_going_3) {
    //                     $deductionPercentage = $timeSheetPolicy->deduct_percentage_early_going_3;
    //                 } elseif ($earlyLeaving < $timeSheetPolicy->early_going_4) {
    //                     $deductionPercentage = $timeSheetPolicy->deduct_percentage_early_going_4;
    //                 }
        
    //                 $earlyLeavingDeduction = ($deductionPercentage / 100) * $dailySalary;
    //                 $totalDeduction += $earlyLeavingDeduction;
        
    //                 // Mark this attendance ID as processed for early leaving deduction
    //                 $processedEarlyLeavingIds[] = $attendanceId;
    //                 // Mark this date as processed
    //             $processedDates[] = $attendanceDateString;
    //             }
    //         }}
    //         dd($totalDeduction);
    //         // Calculate net payable salary
    //         $netPayable = $otherSalary - $totalDeduction;
        
    //         return $netPayable;
    //     }

    // public function attendanceNatPayable($month, $year, $empId, $otherSalary) {
    //     // Fetch attendance data for the given month and year
    //     $attendanceRecords = DB::table('attendance_employees')
    //         ->where('employee_id', $empId)
    //         ->whereMonth('date', $month)
    //         ->whereYear('date', $year)
    //         ->get();
    
    //     // Fetch the applicable time sheet policy (assuming there's only one policy)
    //     $timeSheetPolicy = DB::table('time_sheets2')->first();
    
    //     // Initialize variables for calculations
    //     $totalDeduction = 0;
    //     $dailySalary = $otherSalary / 30; // Assuming salary is divided by 30 days
    
    //     // Arrays to track processed IDs for late and early leaving
    //     $processedLateIds = [];
    //     $processedEarlyLeavingIds = [];
    
    //     // Arrays to track processed dates for late and early leaving
    //     $processedDates = [];
    
    //     // Iterate through attendance records and calculate deductions
    //     foreach ($attendanceRecords as $attendance) {
    //         // Initialize DateTime object for the current attendance date
    //         $attendanceDate = new DateTime($attendance->date);
    //         $attendanceDateString = $attendanceDate->format('Y-m-d');
    
    //         // Check if this date has already been processed
    //         if (!in_array($attendanceDateString, $processedDates)) {
    //             // Initialize deduction variables
    //             $lateDeduction = 0;
    //             $earlyLeavingDeduction = 0;
    //             $attendanceId = $attendance->id;
    
    //             // Calculate late deduction if not processed for this attendance ID
    //             if (!in_array($attendanceId, $processedLateIds)) {
    //                 $deductionPercentage = 0; // Initialize deduction percentage for late
    //                 if ($attendance->late > $timeSheetPolicy->late_4) {
    //                     $deductionPercentage = $timeSheetPolicy->deduct_percentage_4;
    //                 } elseif ($attendance->late > $timeSheetPolicy->late_3) {
    //                     $deductionPercentage = $timeSheetPolicy->deduct_percentage_3;
    //                 } elseif ($attendance->late > $timeSheetPolicy->late_2) {
    //                     $deductionPercentage = $timeSheetPolicy->deduct_percentage_2;
    //                 } elseif ($attendance->late > $timeSheetPolicy->late_1) {
    //                     $deductionPercentage = $timeSheetPolicy->deduct_percentage_1;
    //                 }
    
    //                 $lateDeduction = ($deductionPercentage / 100) * $dailySalary;
    //                 $totalDeduction += $lateDeduction;
    
    //                 // Mark this attendance ID as processed for late deduction
    //                 $processedLateIds[] = $attendanceId;
    //             }
    
    //             // Calculate early leaving deduction if not processed for this attendance ID
    //             if ($attendance->early_leaving > "00:00:00" && !in_array($attendanceId, $processedEarlyLeavingIds)) {
    //                 if ($attendance->early_leaving < $timeSheetPolicy->early_going_1) {
    //                     $deductionPercentage = $timeSheetPolicy->deduct_percentage_early_going_1;
    //                 } elseif ($attendance->early_leaving < $timeSheetPolicy->early_going_2) {
    //                     $deductionPercentage = $timeSheetPolicy->deduct_percentage_early_going_2;
    //                 } elseif ($attendance->early_leaving < $timeSheetPolicy->early_going_3) {
    //                     $deductionPercentage = $timeSheetPolicy->deduct_percentage_early_going_3;
    //                 } elseif ($attendance->early_leaving < $timeSheetPolicy->early_going_4) {
    //                     $deductionPercentage = $timeSheetPolicy->deduct_percentage_early_going_4;
    //                 }
    
    //                 $earlyLeavingDeduction = ($deductionPercentage / 100) * $dailySalary;
    //                 $totalDeduction += $earlyLeavingDeduction;
    
    //                 // Mark this attendance ID as processed for early leaving deduction
    //                 $processedEarlyLeavingIds[] = $attendanceId;
    //             }
    
    //             // Mark this date as processed
    //             $processedDates[] = $attendanceDateString;
    //         }
    //     }
    //     dd($totalDeduction);
    //     // Calculate net payable salary
    //     $netPayable = $otherSalary - $totalDeduction;
    
    //     return $netPayable;
    // }









    
    public function attendanceNatPayable($month, $year, $empId, $otherSalary, $shiftCode, $enableWeekoff, $doj) {
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
        
             $dateDeductions =  $this->attendanceDaysDeductions($month, $year, $empId, $otherSalary, $shiftCode, $enableWeekoff, $doj);
            // dd($dateDeductions['finalExcept_dates_count']);

            $daysDeductions = $dateDeductions['finalExcept_dates_count'] * $dailySalary;
            // dd($daysDeductions);
        foreach ($attendanceRecords as $attendance) {
            // Initialize DateTime object for the current attendance date
            $attendanceDate = new DateTime($attendance->date);
            $attendanceDateString = $attendanceDate->format('Y-m-d');


            //Function FOr check which date deduct salary which dates not deduct 
            // $dateDeductions =  $this->attendanceDaysDeductions($month, $year, $empId, $otherSalary, $shiftCode, $enableWeekoff, $doj);
            // // dd($dateDeductions['finalExcept_dates_count']);

            // $daysDeductions = $dateDeductions['finalExcept_dates_count'] * $dailySalary;
            // dd($daysDeductions);

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
            "getFinalOTSal" => $getFinalOTSal
        );
    }   
    
    
    ##### 2024-07-11 start (if occur any issue in below funtion then use above commentd function ) ###

//     public function attendanceNatPayable($month, $year, $empId, $otherSalary, $shiftCode, $enableWeekoff, $doj) {
//         // Fetch attendance data for the given month and year
//         $attendanceRecords = DB::table('attendance_employees')
//             ->where('employee_id', $empId)
//             ->whereMonth('date', $month)
//             ->whereYear('date', $year)
//             ->get();

//             $dailySalary = $otherSalary / 30; 

//         //Function FOr check which date deduct salary which dates not deduct 
//         $dateDeductions =  $this->attendanceDaysDeductions($month, $year, $empId, $otherSalary, $shiftCode, $enableWeekoff, $doj);
//         // dd($dateDeductions);

//         $daysDeductions = $dateDeductions['finalExcept_dates_count'] * $dailySalary;

//             // dd($attendanceRecords);
// if (!$attendanceRecords->isEmpty()) {
//         dd("if");
//         // Fetch the applicable time sheet policy (assuming there's only one policy)
//         $timeSheetPolicy = DB::table('time_sheets2')->first();
    
//         // Initialize variables for calculations
//         $totalDeduction = 0;
//         $totalOvertime = 0;
//         // $dailySalary = $otherSalary / 30; // Assuming salary is divided by 30 days

//         // Initialize $getFinalOTSal with a default value
//         $getFinalOTSal = 0;

//         $daysDeductions = 0;
    
//         // Arrays to track processed IDs for late and early leaving
//         $processedLateIds = [];
//         $processedEarlyLeavingIds = [];
    
//         // Arrays to track processed dates for late and early leaving
//         $processedDates = [];
    
//         // Iterate through attendance records and calculate deductions
  
       
//         foreach ($attendanceRecords as $attendance) {
//             dd("if foreach");
//             // Initialize DateTime object for the current attendance date
//             $attendanceDate = new DateTime($attendance->date);
//             $attendanceDateString = $attendanceDate->format('Y-m-d');


//             // //Function FOr check which date deduct salary which dates not deduct 
//             // $dateDeductions =  $this->attendanceDaysDeductions($month, $year, $empId, $otherSalary, $shiftCode, $enableWeekoff, $doj);
//             // dd($dateDeductions);

//             // $daysDeductions = $dateDeductions['finalExcept_dates_count'] * $dailySalary;
//             // dd($daysDeductions);

//             // Check if this date has already been processed
//             if (!in_array($attendanceDateString, $processedDates)) {
//                 // dd($attendanceDateString);
//                 // Calculate sum of late and early leaving times for this date
//                 $latEntries = DB::table('attendance_employees')
//                             ->where('employee_id', $empId)
//                             ->whereDate('date', $attendanceDateString)
//                             ->get();

//                 // Initialize variables to calculate total late time in seconds
//                 $totalLateSeconds = 0;
//                 $totalEarlySeconds = 0;
//                 $totalOvertimeSeconds = 0;

//                 // Calculate total late time in seconds for all entries
//                 foreach ($latEntries as $entry) {
//                     // Parse hh:mm:ss to calculate total seconds
//                     list($hours, $minutes, $seconds) = explode(':', $entry->late);
//                     $totalLateSeconds += ($hours * 3600) + ($minutes * 60) + $seconds;
//                 }
//                 foreach ($latEntries as $early) {
//                     // Parse hh:mm:ss to calculate total seconds
//                     list($hours, $minutes, $seconds) = explode(':', $early->early_leaving);
//                     $totalEarlySeconds += ($hours * 3600) + ($minutes * 60) + $seconds;
//                 }
//                 foreach ($latEntries as $overtime) {
//                     // Parse hh:mm:ss to calculate total seconds
//                     list($hours, $minutes, $seconds) = explode(':', $overtime->overtime);
//                     $totalOvertimeSeconds += ($hours * 3600) + ($minutes * 60) + $seconds;
//                 }

//                 // Format total late time into h:mm:ss
//                 $totalLateFormatted = gmdate('H:i:s', $totalLateSeconds);
//                 $totalEarlyFormatted = gmdate('H:i:s', $totalEarlySeconds);
//                 $totalOvertimeFormatted = gmdate('H:i:s', $totalOvertimeSeconds);
//                 // dd($totalOvertimeFormatted,$shiftCode);
//                 list($hours, $minutes, $seconds) = explode(':', $totalOvertimeFormatted);
//                 $totalOvertimeMinutes = ($hours * 60) + $minutes;
//                 // dd($totalOvertimeMinutes);

//                 $getShiftTime = ManageShift::where('shift_code',$shiftCode)->pluck('shift_hours')->first();
//                 list($hours, $minutes, $seconds) = explode(':', $getShiftTime);
//                 $totalMinutes = ($hours * 60) + $minutes;
//                 // dd($totalMinutes);

//                 $minutesSal = $dailySalary / $totalMinutes;
//                 $getFinalOTSal = $totalOvertimeMinutes * $minutesSal;
//                 // dd($getFinalOTSal);

//                 // dd($totalLateFormatted);
    
//                 // Initialize deduction variables
//                 $lateDeduction = 0;
//                 $earlyLeavingDeduction = 0;
//                 $attendanceId = $attendance->id;
    
//                 // Calculate late deduction if not processed for this attendance date
//                 if ($totalLateFormatted > "00:00:00" && !in_array($attendanceDateString, $processedLateIds)) {
//                     $deductionPercentage = 0; // Initialize deduction percentage for late
//                     if ($totalLateFormatted > $timeSheetPolicy->late_4) {
//                         $deductionPercentage = $timeSheetPolicy->deduct_percentage_4;
//                     } elseif ($totalLateFormatted > $timeSheetPolicy->late_3) {
//                         $deductionPercentage = $timeSheetPolicy->deduct_percentage_3;
//                     } elseif ($totalLateFormatted > $timeSheetPolicy->late_2) {
//                         $deductionPercentage = $timeSheetPolicy->deduct_percentage_2;
//                     } elseif ($totalLateFormatted > $timeSheetPolicy->late_1) {
//                         $deductionPercentage = $timeSheetPolicy->deduct_percentage_1;
//                     }
    
//                     $lateDeduction = ($deductionPercentage / 100) * $dailySalary;
//                     $totalDeduction += $lateDeduction;
    
//                     // Mark this attendance date as processed for late deduction
//                     $processedLateIds[] = $attendanceDateString;
//                 }
    
//                 // Calculate early leaving deduction if not processed for this attendance date
//                 if ($totalEarlyFormatted > "00:00:00" && !in_array($attendanceDateString, $processedEarlyLeavingIds)) {
//                     $deductionPercentage = 0; // Initialize deduction percentage for early leaving
//                     if ($totalEarlyFormatted < $timeSheetPolicy->early_going_1) {
//                         $deductionPercentage = $timeSheetPolicy->deduct_percentage_early_going_1;
//                     } elseif ($totalEarlyFormatted < $timeSheetPolicy->early_going_2) {
//                         $deductionPercentage = $timeSheetPolicy->deduct_percentage_early_going_2;
//                     } elseif ($totalEarlyFormatted < $timeSheetPolicy->early_going_3) {
//                         $deductionPercentage = $timeSheetPolicy->deduct_percentage_early_going_3;
//                     } elseif ($totalEarlyFormatted < $timeSheetPolicy->early_going_4) {
//                         $deductionPercentage = $timeSheetPolicy->deduct_percentage_early_going_4;
//                     }
    
//                     $earlyLeavingDeduction = ($deductionPercentage / 100) * $dailySalary;
//                     $totalDeduction += $earlyLeavingDeduction;
    
//                     // Mark this attendance date as processed for early leaving deduction
//                     $processedEarlyLeavingIds[] = $attendanceDateString;
//                 }
    
//                 // Mark this date as processed
//                 $processedDates[] = $attendanceDateString;
//             }
//         }
    
//         // dd($totalDeduction);
//         // Calculate net payable salary
//         $netPayableSalary = $otherSalary - $totalDeduction;
//         // dd($netPayableSalary);
//         // dd($daysDeductions);
//         $getDed =  $otherSalary - $netPayableSalary;
//         $netPayable = $otherSalary - $daysDeductions+$getDed;
//         dd($netPayable);
    
//         // return $netPayable;
//         return $data = array(
//             "netPayable" => $netPayable,
//             "getFinalOTSal" => $getFinalOTSal
//         );

//     }else{
//         // dd("else");

//         // $netPayableSalary = $otherSalary - $totalDeduction;
//         // // dd($netPayableSalary);
//         // // dd($daysDeductions);
//         // $getDed =  $otherSalary - $netPayableSalary;
//         $netPayable = $otherSalary - $daysDeductions;
//         dd($netPayable);
    
//         // return $netPayable;
//         return $data = array(
//             "netPayable" => $netPayable,
//             "getFinalOTSal" => 0
//         );


//     }

//     }

    ##### 2024-07-11 end #####

    // Banksheet Generate

    public function nettPayableBanksheet($month, $year,$salary,$name,$phone,$account_number,$bank_identifier_code) {

        // dd($month, $year,$salary,$name,$phone,$account_number,$bank_identifier_code);
        // $banksheet = BankSheet::first();

        $dateString = date("Y-m-d", strtotime("{$month}-01-01"));
        $monthName = date("F", strtotime($dateString));

        $monthWithoutZero = ltrim($month, '0');
        $lastTwoDigits = substr($year, -2);

        // Generate auto-incremented number
        $autoIncrement = $this->generateAutoIncrement();

         // Construct secret code
        $secretCode = '100' . $month . $lastTwoDigits . $autoIncrement;

        $insertData = [
            'amount' => $salary,  
            'date_name' => $monthWithoutZero.'/'.date("d").'/'.$year,  
            'account_number' => $account_number,  
            'salary_against' => 'Salary against '.$monthName.' '.$year,  
            'company_account_no' => '456567898789',  
            'secret_code' => $secretCode,  
            'bank_code' => $bank_identifier_code,  
            'digit' => '10', 
            'payment_towards' => 'Payment towards Salary', 
            'contact_number' => $phone, 
            'created_at' => now(),
            'updated_at' => now(),
        ];
    
        DB::table('payments_banksheet')->insert($insertData);
    }   
    
    // Auto-increment function
    private function generateAutoIncrement() {
        static $count = 0;
        $count++;
        return $count;
    }


    // public function attendanceDaysDeductions($month, $year, $empId, $otherSalary, $shiftCode, $enableWeekoff, $doj) {

    //     $checkLeaves = DB::table('leaves')
    //                     ->where('status',"Approved")
    //                     ->where('employee_id', $empId)
    //                     ->whereMonth('start_date', $month)
    //                     ->whereYear('start_date', $year)
    //                     ->where('leave_type_id',2)
    //                     ->get();


    //     $date_parts = explode("-", $doj);
    //     $day_digit = $date_parts[2];          
    //     // dd($day_digit);

    //     if($enableWeekoff == "Enabled"){

    //          $currentDate = now();
    //          // $lastDayOfMonth = Carbon::createFromDate($year, $month)->endOfMonth()->day;
    //          $lastDayOfMonth = 30;
 
    //          $datesInMonth = [];
  
    //          for ($day = $day_digit; $day <= $lastDayOfMonth; $day++) {
    //              $dateString = sprintf('%d-%02d-%02d', $year, $month, $day);
    //              $datesInMonth[] = $dateString;
 
    //              if ($year == $currentDate->year && $month == $currentDate->month && $day == $currentDate->day) {
    //                  break;
    //              }
    //          }

    //      $attendanceRecords = DB::table('attendance_employees')
    //          ->where('employee_id', $empId)
    //          ->whereMonth('date', $month)
    //          ->whereYear('date', $year)
    //          ->pluck('date')
    //          ->toArray();
 
    //      $employee = Employee::where('id', '=', $empId)->first();
    //      // dd($attendanceRecords);
 
    //     //  $manageWeekOffRecords = DB::table('manage_weekoff')
    //     //      ->where('employee_id', $employee->user_id)
    //     //      ->whereMonth('week_off_date', $month)
    //     //      ->whereYear('week_off_date', $year)
    //     //      ->pluck('week_off_date')
    //     //      ->toArray();
             

    //         // Remove Saturdays and Sundays from $datesInMonth
    //         foreach ($datesInMonth as $key => $date) {
    //             $dayOfWeek = Carbon::parse($date)->dayOfWeek;
    //             if ($dayOfWeek == Carbon::SATURDAY || $dayOfWeek == Carbon::SUNDAY) {
    //                 unset($datesInMonth[$key]);
    //             }
    //         }

    //         //  dd($datesInMonth, $attendanceRecords);
     
    //      $missingDates = array_diff($datesInMonth, $attendanceRecords);
    //         // dd($missingDates, count($missingDates));
    //       // Remove dates that are also leaves (excluding leave_type_id 2 or Full Holi Day)
    //       foreach ($checkLeaves as $leave) {
    //         $startDate = $leave->start_date;
    //         $endDate = $leave->end_date;

    //         // Iterate through the missing dates and remove those that fall within the leave period
    //         foreach ($missingDates as $key => $date) {
    //             if ($date >= $startDate && $date <= $endDate) {
    //                 unset($missingDates[$key]);
    //             }
    //         }
    //     }

    //     // dd($missingDates, count($missingDates));
    //     //  $finalExceptDates = array_diff($missingDates, $manageWeekOffRecords);

    //      return $data = array(
    //         //  "missingDates" => $missingDates,
    //         //  "finalExceptDates" => $finalExceptDates,
    //         //  "missing_dates_count" => count($missingDates),
    //          "finalExcept_dates_count" => count($missingDates)
    //      );

    //     }else{
    //          /////////////////////////////////////////////////////////////////////////////////

    //          $currentDate = now();

    //          // Determine the last day of the specified month and year
    //          // $lastDayOfMonth = Carbon::createFromDate($year, $month)->endOfMonth()->day;
    //          $lastDayOfMonth = 30;
 
    //          // Initialize an array to store the dates
    //          $datesInMonth = [];
 
 
    //          // Iterate through each day of the month
    //          for ($day = $day_digit; $day <= $lastDayOfMonth; $day++) {
    //              $dateString = sprintf('%d-%02d-%02d', $year, $month, $day);
    //              $datesInMonth[] = $dateString;
 
    //              // Stop adding dates if we reach the current date
    //              if ($year == $currentDate->year && $month == $currentDate->month && $day == $currentDate->day) {
    //                  break;
    //              }
    //          }
 
    //          // Count the total number of dates
    //          // dd($datesInMonth);
    //          // $totalDatesCount = count($datesInMonth);
    //          // dd($totalDatesCount);
 
    //      /////////////////////////////////////////////////////////////////////////////////
 
    //      // dd($datesInMonth);
     
    //      // Fetch attendance records for the specified month and employee
    //      $attendanceRecords = DB::table('attendance_employees')
    //          ->where('employee_id', $empId)
    //          ->whereMonth('date', $month)
    //          ->whereYear('date', $year)
    //          ->pluck('date')
    //          ->toArray();
 
    //      $employee = Employee::where('id', '=', $empId)->first();
    //      // dd($attendanceRecords);
 
    //      $manageWeekOffRecords = DB::table('manage_weekoff')
    //          ->where('employee_id', $employee->user_id)
    //          ->whereMonth('week_off_date', $month)
    //          ->whereYear('week_off_date', $year)
    //          ->pluck('week_off_date')
    //          ->toArray();
 
    //      // dd($manageWeekOffRecords);
     
    //      // Identify dates that are missing in attendance records
    //      $missingDates = array_diff($datesInMonth, $attendanceRecords);
    //         // dd($missingDates, count($missingDates));
    //     // Remove dates that are also leaves (excluding leave_type_id 2 or Full Holi Day)
    //       foreach ($checkLeaves as $leave) {
    //         $startDate = $leave->start_date;
    //         $endDate = $leave->end_date;

    //         // Iterate through the missing dates and remove those that fall within the leave period
    //         foreach ($missingDates as $key => $date) {
    //             if ($date >= $startDate && $date <= $endDate) {
    //                 unset($missingDates[$key]);
    //             }
    //         }
    //     }

    //     // dd($missingDates, count($missingDates));

    //      $finalExceptDates = array_diff($missingDates, $manageWeekOffRecords);
    //     //  dd($missingDates,$finalExceptDates);

    //      return $data = array(
    //          "missingDates" => $missingDates,
    //          "finalExceptDates" => $finalExceptDates,
    //          "missing_dates_count" => count($missingDates),
    //          "finalExcept_dates_count" => count($finalExceptDates)
    //      );

    //     }
        
    // }


    public function attendanceDaysDeductions($month, $year, $empId, $otherSalary, $shiftCode, $enableWeekoff, $doj) {

        $checkLeaves = DB::table('leaves')
                        ->where('status',"Approved")
                        ->where('employee_id', $empId)
                        ->whereMonth('start_date', $month)
                        ->whereYear('start_date', $year)
                        ->where('leave_type_id',2)
                        ->get();

        $date_parts = explode("-", $doj);
        $day_digit = $date_parts[2];          
        // dd($day_digit);

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
  
            //  for ($day = $day_digit; $day <= $lastDayOfMonth; $day++) {
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
        // dd($totalDays);
        //  $finalExceptDates = array_diff($missingDates, $manageWeekOffRecords);
        // dd("enabled");
         return $data = array(
            //  "missingDates" => $missingDates,
            //  "finalExceptDates" => $finalExceptDates,
            //  "missing_dates_count" => count($missingDates),
             "finalExcept_dates_count" => count($missingDates) - $totalDays
         );

        }else{
            // dd('disable');
             /////////////////////////////////////////////////////////////////////////////////

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
            //  for ($day = $day_digit; $day <= $lastDayOfMonth; $day++) {
                for ($day = 1; $day <= $lastDayOfMonth; $day++) {
                 $dateString = sprintf('%d-%02d-%02d', $year, $month, $day);
                 $datesInMonth[] = $dateString;
 
                 // Stop adding dates if we reach the current date
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

        // dd($totalDays);

         return $data = array(
             "missingDates" => $missingDates,
             "finalExceptDates" => $finalExceptDates,
             "missing_dates_count" => count($missingDates),
             "finalExcept_dates_count" => count($finalExceptDates) - $totalDays
         );

        }
        
    }
    
    

    public function destroy($id)
    {
        $payslip = PaySlip::find($id);
        $payslip->delete();

        // Delete all Banksheet Data 
        DB::table('payments_banksheet')->delete();

        return true;
    }

    public function destroyAll(Request $request)
    {  
        $filter_year = $request->input('filter_year');
        $filter_month = $request->input('filter_month');
    
       
        $filter_condition = "$filter_year-$filter_month";
    
        DB::table('pay_slips')->where('created_at', 'LIKE', "$filter_condition%")->delete();
    
        DB::table('payments_banksheet')->where('created_at', 'LIKE', "$filter_condition%")->delete();
    
        return redirect()->back()->with('success', 'Records deleted successfully.');
    }    

    public function showemployee($paySlip)
    {

        $payslip = PaySlip::find($paySlip);

        return view('payslip.show', compact('payslip'));
    }
    public function search_json(Request $request)
    {

        $formate_month_year = $request->datePicker;
        $validatePaysilp    = PaySlip::where('salary_month', '=', $formate_month_year)->where('created_by', \Auth::user()->creatorId())->get()->toarray();

        $data = [];
        if (empty($validatePaysilp)) {
            $data = [];
            return;
        } else {
            $paylip_employee = PaySlip::select(
                [
                    'employees.id',
                    'employees.employee_id',
                    'employees.name',
                    'payslip_types.name as payroll_type',
                    'pay_slips.basic_salary',
                    'pay_slips.net_payble',
                    'pay_slips.id as pay_slip_id',
                    'pay_slips.status',
                    'employees.user_id',
                ]
            )->leftjoin(
                'employees',
                function ($join) use ($formate_month_year) {
                    $join->on('employees.id', '=', 'pay_slips.employee_id');
                    $join->on('pay_slips.salary_month', '=', \DB::raw("'" . $formate_month_year . "'"));
                    $join->leftjoin('payslip_types', 'payslip_types.id', '=', 'employees.salary_type');
                }
            )->where('employees.created_by', \Auth::user()->creatorId())->get();


            foreach ($paylip_employee as $employee) {

                if (Auth::user()->type == 'employee') {
                    if (Auth::user()->id == $employee->user_id) {
                        $tmp   = [];
                        $tmp[] = $employee->id;
                        $tmp[] = $employee->name;
                        $tmp[] = $employee->payroll_type;
                        $tmp[] = $employee->pay_slip_id;
                        $tmp[] = !empty($employee->basic_salary) ? \Auth::user()->priceFormat($employee->basic_salary) : '-';
                        $tmp[] = !empty($employee->net_payble) ? \Auth::user()->priceFormat($employee->net_payble) : '-';
                        if ($employee->status == 1) {
                            $tmp[] = 'paid';
                        } else {
                            $tmp[] = 'unpaid';
                        }
                        $tmp[]  = !empty($employee->pay_slip_id) ? $employee->pay_slip_id : 0;
                        $tmp['url']  = route('employee.show', Crypt::encrypt($employee->id));
                        $data[] = $tmp;
                    }
                } else {

                    $tmp   = [];
                    $tmp[] = $employee->id;
                    $tmp[] = \Auth::user()->employeeIdFormat($employee->employee_id);
                    $tmp[] = $employee->name;
                    $tmp[] = $employee->payroll_type;
                    $tmp[] = !empty($employee->basic_salary) ? \Auth::user()->priceFormat($employee->basic_salary) : '-';
                    $tmp[] = !empty($employee->net_payble) ? \Auth::user()->priceFormat($employee->net_payble) : '-';
                    if ($employee->status == 1) {
                        $tmp[] = 'Paid';
                    } else {
                        $tmp[] = 'UnPaid';
                    }
                    $tmp[]  = !empty($employee->pay_slip_id) ? $employee->pay_slip_id : 0;
                    $tmp['url']  = route('employee.show', Crypt::encrypt($employee->id));
                    $data[] = $tmp;
                }
            }
            return $data;
        }
    }

    public function paysalary($id, $date)
    {
        $employeePayslip = PaySlip::where('employee_id', '=', $id)->where('created_by', \Auth::user()->creatorId())->where('salary_month', '=', $date)->first();
        $get_employee = Employee::where('id', $id)->where('created_by', \Auth::user()->creatorId())->first();
        $get_account = AccountList::where('id', $get_employee->account_type)->where('created_by', \Auth::user()->creatorId())->first();
        $initial_balance = !empty($get_account->initial_balance) ? $get_account->initial_balance : 0;
        $net_salary = !empty($employeePayslip->net_payble) ? $employeePayslip->net_payble : 0;
        if (!empty($employeePayslip)) {
            $employeePayslip->status = 1;
            $employeePayslip->save();

            $total_balance = $initial_balance - $net_salary;
            $get_account->initial_balance = $total_balance;
            $get_account->save();

            $set_expense = new Expense();
            $set_expense->account_id = $get_account->id;
            $set_expense->amount = $employeePayslip->net_payble;
            $set_expense->date = date('Y-m-d');
            $set_expense->expense_category_id = '';
            $set_expense->payee_id = $get_employee->id;
            $set_expense->payment_type_id = '';
            $set_expense->referal_id = '';
            $set_expense->description = '';
            $set_expense->created_by = $get_employee->created_by;
            $set_expense->save();

            return redirect()->route('payslip.index')->with('success', __('Payslip Payment successfully.'));
        } else {
            return redirect()->route('payslip.index')->with('error', __('Payslip Payment failed.'));
        }
    }

    public function bulk_pay_create($date)
    {
        $Employees       = PaySlip::where('salary_month', $date)->where('created_by', \Auth::user()->creatorId())->get();
        $unpaidEmployees = PaySlip::where('salary_month', $date)->where('created_by', \Auth::user()->creatorId())->where('status', '=', 0)->get();

        return view('payslip.bulkcreate', compact('Employees', 'unpaidEmployees', 'date'));
    }

    public function bulkpayment(Request $request, $date)
    {
        $unpaidEmployees = PaySlip::where('salary_month', $date)->where('created_by', \Auth::user()->creatorId())->where('status', '=', 0)->get();

        foreach ($unpaidEmployees as $employee) {
            $employee->status = 1;
            $employee->save();
        }

        return redirect()->route('payslip.index')->with('success', __('Payslip Bulk Payment successfully.'));
    }

    public function employeepayslip()
    {
        $employees = Employee::where(
            [
                'user_id' => \Auth::user()->id,
            ]
        )->first();

        $payslip = PaySlip::where('employee_id', '=', $employees->id)->get();

        return view('payslip.employeepayslip', compact('payslip'));
    }

    public function pdf($id, $month)
    {

        $payslip  = PaySlip::where('employee_id', $id)->where('salary_month', $month)->where('created_by', \Auth::user()->creatorId())->first();
        $employee = Employee::find($payslip->employee_id);

        $payslipDetail = Utility::employeePayslipDetail($id, $month);

        return view('payslip.pdf', compact('payslip', 'employee', 'payslipDetail'));
    }

    public function send($id, $month)
    {
        $payslip  = PaySlip::where('employee_id', $id)->where('salary_month', $month)->where('created_by', \Auth::user()->creatorId())->first();
        $employee = Employee::find($payslip->employee_id);
        $payslip->name  = $employee->name;
        $payslip->email = $employee->email;

        $payslipId    = Crypt::encrypt($payslip->id);
        $payslip->url = route('payslip.payslipPdf', $payslipId);

        $setings = Utility::settings();
        if ($setings['new_payroll'] == 1) {
            $uArr = [
                'payslip_email' => $payslip->email,
                'name'  => $payslip->name,
                'url' => $payslip->url,
                'salary_month' => $payslip->salary_month,
            ];

            $resp = Utility::sendEmailTemplate('new_payroll', [$payslip->email], $uArr);
            return redirect()->back()->with('success', __('Payslip successfully sent.')  . ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
        }

        return redirect()->back()->with('success', __('Payslip successfully sent.'));
    }

    public function payslipPdf($id)
    {
        $payslipId = Crypt::decrypt($id);

        $payslip  = PaySlip::where('id', $payslipId)->where('created_by', \Auth::user()->creatorId())->first();
        $month = $payslip->salary_month;
        $employee = Employee::find($payslip->employee_id);

        $payslipDetail = Utility::employeePayslipDetail($payslip->employee_id, $month);

        return view('payslip.payslipPdf', compact('payslip', 'employee', 'payslipDetail'));
    }

    public function editEmployee($paySlip)
    {
        $payslip = PaySlip::find($paySlip);

        return view('payslip.salaryEdit', compact('payslip'));
    }

    public function updateEmployee(Request $request, $id)
    {


        if (isset($request->allowance) && !empty($request->allowance)) {
            $allowances   = $request->allowance;
            $allowanceIds = $request->allowance_id;
            foreach ($allowances as $k => $allownace) {
                $allowanceData         = Allowance::find($allowanceIds[$k]);
                $allowanceData->amount = $allownace;
                $allowanceData->save();
            }
        }


        if (isset($request->commission) && !empty($request->commission)) {
            $commissions   = $request->commission;
            $commissionIds = $request->commission_id;
            foreach ($commissions as $k => $commission) {
                $commissionData         = Commission::find($commissionIds[$k]);
                $commissionData->amount = $commission;
                $commissionData->save();
            }
        }

        if (isset($request->loan) && !empty($request->loan)) {
            $loans   = $request->loan;
            $loanIds = $request->loan_id;
            foreach ($loans as $k => $loan) {
                $loanData         = Loan::find($loanIds[$k]);
                $loanData->amount = $loan;
                $loanData->save();
            }
        }


        if (isset($request->saturation_deductions) && !empty($request->saturation_deductions)) {
            $saturation_deductionss   = $request->saturation_deductions;
            $saturation_deductionsIds = $request->saturation_deductions_id;
            foreach ($saturation_deductionss as $k => $saturation_deductions) {

                $saturation_deductionsData         = SaturationDeduction::find($saturation_deductionsIds[$k]);
                $saturation_deductionsData->amount = $saturation_deductions;
                $saturation_deductionsData->save();
            }
        }


        if (isset($request->other_payment) && !empty($request->other_payment)) {
            $other_payments   = $request->other_payment;
            $other_paymentIds = $request->other_payment_id;
            foreach ($other_payments as $k => $other_payment) {
                $other_paymentData         = OtherPayment::find($other_paymentIds[$k]);
                $other_paymentData->amount = $other_payment;
                $other_paymentData->save();
            }
        }


        if (isset($request->rate) && !empty($request->rate)) {
            $rates   = $request->rate;
            $rateIds = $request->rate_id;
            $hourses = $request->hours;

            foreach ($rates as $k => $rate) {
                $overtime        = Overtime::find($rateIds[$k]);
                $overtime->rate  = $rate;
                $overtime->hours = $hourses[$k];
                $overtime->save();
            }
        }


        $payslipEmployee                       = PaySlip::find($request->payslip_id);
        $payslipEmployee->allowance            = Employee::allowance($payslipEmployee->employee_id);
        $payslipEmployee->commission           = Employee::commission($payslipEmployee->employee_id);
        $payslipEmployee->loan                 = Employee::loan($payslipEmployee->employee_id);
        $payslipEmployee->saturation_deduction = Employee::saturation_deduction($payslipEmployee->employee_id);
        $payslipEmployee->other_payment        = Employee::other_payment($payslipEmployee->employee_id);
        $payslipEmployee->overtime             = Employee::overtime($payslipEmployee->employee_id);
        $payslipEmployee->net_payble           = Employee::find($payslipEmployee->employee_id)->get_net_salary();
        $payslipEmployee->save();

        return redirect()->route('payslip.index')->with('success', __('Employee payroll successfully updated.'));
    }

    public function PayslipExport(Request $request)
    {
        $name = 'payslip_' . date('Y-m-d i:h:s');
        $data = \Excel::download(new PayslipExport($request), $name . '.xlsx');
        ob_end_clean();

        return $data;
    }

    // public function attendancePayslipExport(Request $request)
    // {
    //     // dd($request->all());
    
    //     ini_set('memory_limit', '512M');

    //     $filter_month                = $request->filter_month;
    //     $filter_year                 = $request->filter_year;


        
    //     $query = PaySlip::query();

    //     if ($filter_month) {
    //         $query->where('salary_month', 'LIKE', "$filter_year-$filter_month%");
    //     }
        

    //     $paySlips = $query->get();

    //     // dd($paySlips);   

    //     $csvFileName = 'payslip-'.$filter_month.'-' .$filter_year.'.csv';
    
    //     $headers = array(
    //         "Content-type"        => "text/csv",
    //         "Content-Disposition" => "attachment; filename=$csvFileName",
    //         "Pragma"              => "no-cache",
    //         "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
    //         "Expires"             => "0",
    //     );
    
    //     $handle = fopen('php://output', 'w');
    
    //     // Add CSV header
    //     fputcsv($handle, array('EMP ID', 'EMP Name', 'Desination', 'Date Of Joining', 'Attendance', 'Week Offs', 'Salary', 'Basic', 'HRA', 'Conveyance','Special Allowance', 'Deduction (EPF/ESIC)', 'Income Tax', 'Total', 'Attendace Deduction', 'Leave Deduction', 'Deduction Amount', 'Overtime','Net Salary', 'Incentive' , 'Leave/HD', 'Week Off Carry Forward/not use', 'Final Salary To Be Credit'));
    
    //     // Add CSV data
    //     foreach ($paySlips as $paySlip) {
    
    //         $employee = Employee::where('id', '=', $paySlip->employee_id)->first();
    //         $depertment = Department::where('branch_id', $employee->branch_id)->where('id', $employee->department_id)->first();
    //         // dd($depertment->name);

    //         $daysData = attendanceNatPayable_helper($filter_month, $filter_year, $paySlip->employee_id, $employee->salary, $employee->shift_code, $employee->enable_weekoff);

    //         $leaveData = calculateNetPayable_helper($filter_month, $filter_year, $paySlip->employee_id, $employee->salary, $employee->enable_weekoff);
    //         // dd($daysData['daysComeAttendance']);
    //          $basicSal = $employee->salary/2;
    //          $hra = $basicSal/2;
    //          $conveyance = $employee->salary/4;
    //          $total = $basicSal+$hra+$conveyance;

    //          $attendanceDeduction = $employee->salary - $daysData['netPayable'];
    //          $leavesDeduction =  $employee->salary - $leaveData['netPayable'];
    //          $deductionAmount = $attendanceDeduction + $leavesDeduction;




    //          ##################################################

    //          $leaveManageSal = $employee->salary - $daysData['netPayable'];
    //          $attendanceSal = $employee->salary - $leaveData['netPayable'];

    //          $getDeductSal =  $leaveManageSal + $attendanceSal;

    //          $getNatEmpSal   = $employee->get_net_salary() - $employee->salary;
    //          // dd($leaveManageSal,$attendanceSal,$getNatEmpSal);

    //          $addAllPayment = $employee->salary + $getNatEmpSal;
    //          $getFinalizePrice = $addAllPayment - $getDeductSal;

    //          // Overtime Salary Start
    //          if($employee->enable_ot == "Enabled"){
    //              // dd($getFinalizePrice,"if",$getAttendanceSalary['getFinalOTSal']);
    //              $salaryFinalized = $getFinalizePrice + $daysData['getFinalOTSal'];
    //          }else{
    //              // dd("else");
    //              $salaryFinalized = $getFinalizePrice;
    //          }

    //          $overtime = $employee->enable_ot == "Enabled" ? $daysData['getFinalOTSal'] : null;

    //          ###################################################

    //         fputcsv($handle, array(
    //             'EMP'.$employee->id,
    //             $employee->name,
    //             $depertment->name,
    //             $employee->company_doj,
    //             $daysData['daysComeAttendance'],
    //             $daysData['weekoffs'],
    //             $employee->salary,
    //             $basicSal,
    //             $hra,
    //             $conveyance,
    //             null,
    //             null,
    //             null,
    //             $total,
    //             $attendanceDeduction,
    //             $leavesDeduction,
    //             $deductionAmount,
    //             $overtime,
    //             $total - $deductionAmount,
    //             null,
    //             $leaveData['leaves'],
    //             null,
    //             $salaryFinalized
    //         ));
    //     }
    
    //     fclose($handle);
    
    //     $headers['Connection'] = 'close';
    
    //     return response()->stream(
    //         function () use ($handle) {
    //             if (is_resource($handle)) {
    //                 fclose($handle);
    //             }
    //         },
    //         200,
    //         $headers
    //     );
    

    // }

    public function attendancePayslipExport(Request $request)
{
    ini_set('memory_limit', '512M');

    $filter_month = $request->filter_month;
    $filter_year = $request->filter_year;

    $query = PaySlip::query();

    if ($filter_month) {
        $query->where('salary_month', 'LIKE', "$filter_year-$filter_month%");
    }

    $paySlips = $query->get();

    $csvFileName = 'payslip-'.$filter_month.'-' .$filter_year.'-'.date('Y-m-d H:i:s').'.csv';
    $filePath = storage_path('app/'.$csvFileName);

    $handle = fopen($filePath, 'w');

    // Add CSV header
    fputcsv($handle, array('EMP ID', 'EMP Name', 'Designation', 'Date Of Joining', 'Attendance', 'Week Offs', 'Salary', 'Basic', 'HRA', 'Conveyance','Special Allowance', 'Deduction (EPF/ESIC)', 'Income Tax', 'Total', 'Attendance Deduction', 'Leave Deduction', 'Deduction Amount', 'Overtime','Net Salary', 'Incentive' , 'Leave/HD', 'Week Off Carry Forward/not use', 'Final Salary To Be Credit'));

    // Add CSV data
    foreach ($paySlips as $paySlip) {
        $employee = Employee::where('id', '=', $paySlip->employee_id)->first();
        $department = Department::where('branch_id', $employee->branch_id)->where('id', $employee->department_id)->first();

        $daysData = attendanceNatPayable_helper($filter_month, $filter_year, $paySlip->employee_id, $employee->salary, $employee->shift_code, $employee->enable_weekoff,$employee->company_doj);
        $leaveData = calculateNetPayable_helper($filter_month, $filter_year, $paySlip->employee_id, $employee->salary, $employee->enable_weekoff);

        $basicSal = $employee->salary / 2;
        $hra = $basicSal / 2;
        $conveyance = $employee->salary / 4;
        $total = $basicSal + $hra + $conveyance;

        $attendanceDeduction = $employee->salary - $daysData['netPayable'];
        $leavesDeduction = $employee->salary - $leaveData['netPayable'];
        $deductionAmount = $attendanceDeduction + $leavesDeduction;

        $leaveManageSal = $employee->salary - $daysData['netPayable'];
        $attendanceSal = $employee->salary - $leaveData['netPayable'];
        $getDeductSal = $leaveManageSal + $attendanceSal;
        $getNatEmpSal = $employee->get_net_salary() - $employee->salary;
        $addAllPayment = $employee->salary + $getNatEmpSal;
        $getFinalizePrice = $addAllPayment - $getDeductSal;
        $salaryFinalized = $employee->enable_ot == "Enabled" ? $getFinalizePrice + $daysData['getFinalOTSal'] : $getFinalizePrice;
        $overtime = $employee->enable_ot == "Enabled" ? $daysData['getFinalOTSal'] : null;

        fputcsv($handle, array(
            'EMP' . $employee->id,
            $employee->name,
            $department->name,
            $employee->company_doj,
            $daysData['daysComeAttendance'],
            $daysData['weekoffs'],
            $employee->salary,
            $basicSal,
            $hra,
            $conveyance,
            null,
            null,
            null,
            $total,
            $attendanceDeduction,
            $leavesDeduction,
            $deductionAmount,
            $overtime,
            $total - $deductionAmount,
            null,
            $leaveData['leaves'],
            null,
            $salaryFinalized
        ));
    }

    fclose($handle);

    // Send CSV to Telegram
    $this->sendTelegramCsv($filePath, $csvFileName);

    // Send CSV to Slack
    // $this->sendSlackCsv($filePath, $csvFileName);

    return response()->download($filePath, $csvFileName, [
        "Content-Type" => "text/csv",
        "Pragma" => "no-cache",
        "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
        "Expires" => "0",
    ]);
}

    public function sendTelegramCsv($filePath, $fileName)
    {
        $settings = Utility::settings(\Auth::user()->creatorId());

        if (!isset($settings['telegram_accestoken']) || !isset($settings['telegram_chatid'])) {
            throw new \Exception('Telegram bot token or chat ID not set.');
        }

        $telegrambot = $settings['telegram_accestoken'];
        $telegramchatid = $settings['telegram_chatid'];

        $url = 'https://api.telegram.org/bot' . $telegrambot . '/sendDocument';

        $post_fields = [
            'chat_id' => $telegramchatid,
            'document' => new \CURLFile($filePath, 'text/csv', $fileName)
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type:multipart/form-data"]);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
        $result = curl_exec($ch);
        curl_close($ch);

        if ($result === false) {
            throw new \Exception('Error sending message to Telegram.');
        }
    }

//     public function sendSlackCsv($filePath, $fileName)
// {
//     $settings = Utility::settings(\Auth::user()->creatorId());

//     if (!isset($settings['slack_token']) || !isset($settings['slack_channel'])) {
//         throw new \Exception('Slack token or channel ID not set.');
//     }

//     $slackToken = $settings['slack_token'];
//     $slackChannel = $settings['slack_channel'];

//     $url = 'https://slack.com/api/files.upload';

//     $post_fields = [
//         'channels' => $slackChannel,
//         'title' => $fileName,
//         'file' => new \CURLFile($filePath, 'text/csv', $fileName)
//     ];

//     $headers = [
//         "Authorization: Bearer $slackToken",
//         "Content-Type: multipart/form-data"
//     ];

//     $ch = curl_init();
//     curl_setopt($ch, CURLOPT_URL, $url);
//     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//     curl_setopt($ch, CURLOPT_POST, 1);
//     curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
//     curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//     $result = curl_exec($ch);
//     curl_close($ch);

//     if ($result === false) {
//         throw new \Exception('Error sending file to Slack.');
//     }

//     $response = json_decode($result, true);
//     if (!$response['ok']) {
//         throw new \Exception('Error: ' . $response['error']);
//     }
// }


//     public function BanksheetExport(Request $request)
// {
//     // Ensure sufficient memory allocation
//     ini_set('memory_limit', '512M');

//     // Retrieve all data from the 'payments_banksheet' table
//     $bankSheetData = DB::table('payments_banksheet')->get();

//     // Prepare CSV file name
//     $csvFileName = 'payments_banksheet-export.csv';

//     // Define CSV headers (optional, but omitted here)

//     // Open a file pointer connected to php://output
//     $handle = fopen('php://output', 'w');

//     // Add CSV data rows
//     foreach ($bankSheetData as $row) {
//         fputcsv($handle, [
//             // $row->id,
//             $row->amount,
//             $row->date_name,
//             $row->account_number,
//             null,
//             $row->salary_against,
//             $row->company_account_no,
//             $row->secret_code,
//             $row->bank_code,
//             $row->digit,
//             $row->payment_towards,
//             $row->contact_number,
//             // $row->created_at,
//             // $row->updated_at
//         ]);
//     }

//     // Close the file pointer
//     fclose($handle);

//     // Ensure the connection is closed
//     $headers = [
//         "Content-type"        => "text/csv",
//         "Content-Disposition" => "attachment; filename=$csvFileName",
//         "Pragma"              => "no-cache",
//         "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
//         "Expires"             => "0",
//         'Connection'          => 'close',
//     ];

//     // Return the response as a streamed CSV file
//     return response()->stream(
//         function () use ($handle) {
//             if (is_resource($handle)) {
//                 fclose($handle);
//             }
//         },
//         200,
//         $headers
//     );
// }

public function BanksheetExport(Request $request)
{
    // Ensure sufficient memory allocation
    ini_set('memory_limit', '512M');

    // Retrieve all data from the 'payments_banksheet' table
    $bankSheetData = DB::table('payments_banksheet')->get();

    // Prepare CSV file name
    $csvFileName = 'payments_banksheet-export'.'-'.date('Y-m-d H:i:s').'.csv';
    $filePath = storage_path('app/'.$csvFileName);

    // Open a file pointer connected to a file path
    $handle = fopen($filePath, 'w');

    // Add CSV header (optional)
    fputcsv($handle, [
        'Amount',
        'Date Name',
        'Account Number',
        'Salary Against',
        'Company Account No',
        'Secret Code',
        'Bank Code',
        'Digit',
        'Payment Towards',
        'Contact Number'
    ]);

    // Add CSV data rows
    foreach ($bankSheetData as $row) {
        fputcsv($handle, [
            $row->amount,
            $row->date_name,
            $row->account_number,
            $row->salary_against,
            $row->company_account_no,
            $row->secret_code,
            $row->bank_code,
            $row->digit,
            $row->payment_towards,
            $row->contact_number
        ]);
    }

    // Close the file pointer
    fclose($handle);

    // Send CSV to Telegram
    $this->sendTelegramCsv($filePath, $csvFileName);

    // Send CSV to Slack
    // $this->sendSlackCsv($filePath, $csvFileName);

    // Return the response as a downloaded CSV file
    return response()->download($filePath, $csvFileName, [
        "Content-Type" => "text/csv",
        "Pragma" => "no-cache",
        "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
        "Expires" => "0",
    ]);
}


}
