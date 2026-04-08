<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Spatie\Permission\Models\Role;
use App\Models\User;

class DummyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create additional departments
        $departments = [
            ['branch_id' => 1, 'name' => 'Marketing', 'created_by' => 1],
            ['branch_id' => 1, 'name' => 'Sales', 'created_by' => 1],
            ['branch_id' => 1, 'name' => 'Operations', 'created_by' => 1],
            ['branch_id' => 2, 'name' => 'Customer Support', 'created_by' => 1],
            ['branch_id' => 2, 'name' => 'Business Development', 'created_by' => 1],
            ['branch_id' => 3, 'name' => 'Research & Development', 'created_by' => 1],
        ];

        foreach ($departments as $dept) {
            DB::table('departments')->insertOrIgnore([
                'branch_id' => $dept['branch_id'],
                'name' => $dept['name'],
                'created_by' => $dept['created_by'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Create additional designations
        $designations = [
            ['branch_id' => 1, 'department_id' => 4, 'name' => 'Marketing Manager', 'created_by' => 1],
            ['branch_id' => 1, 'department_id' => 4, 'name' => 'Marketing Executive', 'created_by' => 1],
            ['branch_id' => 1, 'department_id' => 4, 'name' => 'Content Writer', 'created_by' => 1],
            ['branch_id' => 1, 'department_id' => 5, 'name' => 'Sales Manager', 'created_by' => 1],
            ['branch_id' => 1, 'department_id' => 5, 'name' => 'Sales Executive', 'created_by' => 1],
            ['branch_id' => 1, 'department_id' => 6, 'name' => 'Operations Manager', 'created_by' => 1],
            ['branch_id' => 1, 'department_id' => 6, 'name' => 'Operations Executive', 'created_by' => 1],
            ['branch_id' => 2, 'department_id' => 7, 'name' => 'Support Manager', 'created_by' => 1],
            ['branch_id' => 2, 'department_id' => 7, 'name' => 'Support Agent', 'created_by' => 1],
            ['branch_id' => 2, 'department_id' => 8, 'name' => 'Business Analyst', 'created_by' => 1],
            ['branch_id' => 3, 'department_id' => 9, 'name' => 'R&D Manager', 'created_by' => 1],
            ['branch_id' => 3, 'department_id' => 9, 'name' => 'Research Scientist', 'created_by' => 1],
            ['branch_id' => 1, 'department_id' => 2, 'name' => 'Software Engineer', 'created_by' => 1],
            ['branch_id' => 1, 'department_id' => 2, 'name' => 'Senior Software Engineer', 'created_by' => 1],
            ['branch_id' => 1, 'department_id' => 2, 'name' => 'DevOps Engineer', 'created_by' => 1],
            ['branch_id' => 1, 'department_id' => 3, 'name' => 'Finance Manager', 'created_by' => 1],
            ['branch_id' => 1, 'department_id' => 3, 'name' => 'Accountant', 'created_by' => 1],
        ];

        foreach ($designations as $designation) {
            DB::table('designations')->insertOrIgnore([
                'branch_id' => $designation['branch_id'],
                'department_id' => $designation['department_id'],
                'name' => $designation['name'],
                'created_by' => $designation['created_by'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Create 50 dummy users and employees
        $userRoles = ['employee', 'hr', 'company'];
        $genders = ['Male', 'Female'];
        $salaryTypes = [1, 2]; // Assuming 1 = monthly, 2 = hourly
        
        $firstNames = [
            'John', 'Jane', 'Michael', 'Sarah', 'David', 'Lisa', 'Robert', 'Maria',
            'James', 'Jennifer', 'William', 'Patricia', 'Richard', 'Linda', 'Joseph',
            'Barbara', 'Thomas', 'Susan', 'Charles', 'Nancy', 'Christopher', 'Betty',
            'Daniel', 'Helen', 'Matthew', 'Sandra', 'Anthony', 'Donna', 'Mark',
            'Carol', 'Donald', 'Ruth', 'Steven', 'Sharon', 'Paul', 'Michelle',
            'Andrew', 'Laura', 'Joshua', 'Emily', 'Kenneth', 'Kimberly', 'Kevin',
            'Deborah', 'Brian', 'Dorothy', 'George', 'Amy', 'Edward', 'Angela'
        ];
        
        $lastNames = [
            'Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller',
            'Davis', 'Rodriguez', 'Martinez', 'Hernandez', 'Lopez', 'Gonzalez',
            'Wilson', 'Anderson', 'Thomas', 'Taylor', 'Moore', 'Jackson', 'Martin',
            'Lee', 'Perez', 'Thompson', 'White', 'Harris', 'Sanchez', 'Clark',
            'Ramirez', 'Lewis', 'Robinson', 'Walker', 'Young', 'Allen', 'King',
            'Wright', 'Scott', 'Torres', 'Nguyen', 'Hill', 'Flores', 'Green',
            'Adams', 'Nelson', 'Baker', 'Hall', 'Rivera', 'Campbell', 'Mitchell',
            'Carter', 'Roberts'
        ];

        for ($i = 1; $i <= 50; $i++) {
            $firstName = $firstNames[array_rand($firstNames)];
            $lastName = $lastNames[array_rand($lastNames)];
            $fullName = $firstName . ' ' . $lastName;
            $email = strtolower($firstName . '.' . $lastName . $i . '@barcosys.com');
            $phone = '+1' . rand(1000000000, 9999999999);
            $gender = $genders[array_rand($genders)];
            $dob = Carbon::now()->subYears(rand(22, 60))->subMonths(rand(1, 12))->format('Y-m-d');
            $salary = rand(30000, 120000);
            $branchId = rand(1, 3);
            $departmentId = rand(1, 9);
            $designationId = rand(1, 20);
            
            // Assign role based on index
            if ($i <= 5) {
                $role = 'hr';
            } elseif ($i <= 10) {
                $role = 'company';
            } else {
                $role = 'employee';
            }

            // Create user
            $userId = DB::table('users')->insertGetId([
                'name' => $fullName,
                'email' => $email,
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
                'type' => $role,
                'avatar' => 'avatar.png',
                'lang' => 'english',
                'last_login' => now()->subDays(rand(0, 30)),
                'is_active' => 1,
                'active_status' => 1,
                'is_login_enable' => 1,
                'dark_mode' => 0,
                'messenger_color' => '#2180f3',
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create employee
            DB::table('employees')->insert([
                'user_id' => $userId,
                'name' => $fullName,
                'dob' => $dob,
                'gender' => $gender,
                'phone' => $phone,
                'address' => rand(100, 9999) . ' ' . $lastNames[array_rand($lastNames)] . ' Street, City, State',
                'email' => $email,
                'password' => Hash::make('password123'),
                'employee_id' => 'EMP' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'branch_id' => $branchId,
                'department_id' => $departmentId,
                'designation_id' => $designationId,
                'company_doj' => Carbon::now()->subYears(rand(0, 5))->subMonths(rand(1, 12))->format('Y-m-d'),
                'account_holder_name' => $fullName,
                'account_number' => rand(1000000000, 9999999999),
                'bank_name' => ['Chase Bank', 'Bank of America', 'Wells Fargo', 'Citibank'][array_rand(['Chase Bank', 'Bank of America', 'Wells Fargo', 'Citibank'])],
                'bank_identifier_code' => 'SWIFT' . rand(1000, 9999),
                'branch_location' => 'Main Branch',
                'tax_payer_id' => rand(100000000, 999999999),
                'salary_type' => $salaryTypes[array_rand($salaryTypes)],
                'salary' => $salary,
                'is_active' => 1,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Assign role to user
            $user = User::find($userId);
            if ($user) {
                $user->assignRole($role);
            }
        }

        // Create attendance data for last 2 months
        $employees = DB::table('employees')->get();
        $startDate = Carbon::now()->subMonths(2)->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();
        
        $statuses = ['Present', 'Absent', 'Half Day', 'Holiday'];
        $workingDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

        while ($startDate <= $endDate) {
            // Skip weekends for most employees
            if (in_array($startDate->format('l'), $workingDays)) {
                foreach ($employees as $employee) {
                    // 90% attendance rate
                    if (rand(1, 100) <= 90) {
                        $status = $statuses[0]; // Present
                        $clockIn = Carbon::parse($startDate->format('Y-m-d') . ' ' . rand(8, 9) . ':' . rand(0, 59) . ':00');
                        $clockOut = $clockIn->copy()->addHours(8)->addMinutes(rand(0, 60));
                        $late = $clockIn->hour > 9 ? $clockIn->copy()->subHours(9)->format('H:i:s') : '00:00:00';
                        $earlyLeaving = $clockOut->hour < 17 ? Carbon::parse('17:00:00')->diff($clockOut)->format('%H:%i:%s') : '00:00:00';
                        $overtime = $clockOut->hour > 17 ? $clockOut->copy()->subHours(17)->format('H:i:s') : '00:00:00';
                    } else {
                        // 10% chance of absence or half day
                        $status = rand(1, 100) <= 70 ? $statuses[1] : $statuses[2]; // Absent or Half Day
                        if ($status === 'Absent') {
                            $clockIn = '00:00:00';
                            $clockOut = '00:00:00';
                        } else { // Half Day
                            $clockIn = Carbon::parse($startDate->format('Y-m-d') . ' ' . rand(8, 9) . ':' . rand(0, 59) . ':00');
                            $clockOut = $clockIn->copy()->addHours(4);
                        }
                        $late = '00:00:00';
                        $earlyLeaving = '00:00:00';
                        $overtime = '00:00:00';
                    }

                    DB::table('attendance_employees')->insert([
                        'employee_id' => $employee->id,
                        'date' => $startDate->format('Y-m-d'),
                        'status' => $status,
                        'clock_in' => $clockIn instanceof Carbon ? $clockIn->format('H:i:s') : $clockIn,
                        'clock_out' => $clockOut instanceof Carbon ? $clockOut->format('H:i:s') : $clockOut,
                        'late' => $late,
                        'early_leaving' => $earlyLeaving,
                        'overtime' => $overtime,
                        'total_rest' => '01:00:00', // 1 hour lunch break
                        'created_by' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
            $startDate->addDay();
        }

        // Create some leave types if they don't exist
        $leaveTypes = [
            'Annual Leave',
            'Sick Leave',
            'Personal Leave',
            'Maternity Leave',
            'Paternity Leave',
            'Emergency Leave'
        ];

        foreach ($leaveTypes as $leaveType) {
            DB::table('leave_types')->insertOrIgnore([
                'title' => $leaveType,
                'days' => rand(10, 30),
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Create some holiday records
        $holidays = [
            ['start_date' => '2026-01-01', 'end_date' => '2026-01-01', 'occasion' => 'New Year Day'],
            ['start_date' => '2026-07-04', 'end_date' => '2026-07-04', 'occasion' => 'Independence Day'],
            ['start_date' => '2026-12-25', 'end_date' => '2026-12-25', 'occasion' => 'Christmas Day'],
            ['start_date' => '2026-11-26', 'end_date' => '2026-11-26', 'occasion' => 'Thanksgiving'],
            ['start_date' => '2026-09-07', 'end_date' => '2026-09-07', 'occasion' => 'Labor Day'],
        ];

        foreach ($holidays as $holiday) {
            DB::table('holidays')->insertOrIgnore([
                'start_date' => $holiday['start_date'],
                'end_date' => $holiday['end_date'],
                'occasion' => $holiday['occasion'],
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        echo "Dummy data has been created successfully!\n";
        echo "- 50 users with different roles (5 HR, 5 Company, 40 Employees)\n";
        echo "- Attendance data for the last 2 months\n";
        echo "- Additional departments, designations, leave types, and holidays\n";
        echo "- Default password for all users: password123\n";
    }
}