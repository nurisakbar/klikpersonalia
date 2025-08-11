<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Leave;
use App\Models\Employee;
use App\Models\User;
use App\Models\Company;
use Carbon\Carbon;

class LeaveSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employees = Employee::all();
        $company = Company::first();
        
        if (!$company) {
            $this->command->error('No company found. Please run CompanySeeder first.');
            return;
        }

        if ($employees->isEmpty()) {
            $this->command->error('No employees found. Please run EmployeeSeeder first.');
            return;
        }

        // Get admin user for approval
        $adminUser = User::where('role', 'admin')->first();

        $leaveTypes = ['annual', 'sick', 'maternity', 'paternity', 'other'];
        $statuses = ['pending', 'approved', 'rejected'];

        foreach ($employees as $employee) {
            // Create 2-4 leave requests per employee
            $numLeaves = rand(2, 4);
            
            for ($i = 0; $i < $numLeaves; $i++) {
                $leaveType = $leaveTypes[array_rand($leaveTypes)];
                $status = $statuses[array_rand($statuses)];
                
                // Generate random dates (past and future)
                $startDate = Carbon::now()->addDays(rand(-30, 60));
                $endDate = $startDate->copy()->addDays(rand(1, 7));
                
                // Calculate total days (excluding weekends)
                $totalDays = 0;
                $currentDate = $startDate->copy();
                while ($currentDate->lte($endDate)) {
                    if (!$currentDate->isWeekend()) {
                        $totalDays++;
                    }
                    $currentDate->addDay();
                }

                $leave = Leave::create([
                    'employee_id' => $employee->id,
                    'company_id' => $company->id,
                    'leave_type' => $leaveType,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'total_days' => $totalDays,
                    'reason' => $this->getRandomReason($leaveType),
                    'status' => $status,
                    'approved_by' => $status !== 'pending' ? $adminUser->id : null,
                    'approved_at' => $status !== 'pending' ? Carbon::now()->subDays(rand(1, 30)) : null,
                    'approval_notes' => $status !== 'pending' ? $this->getRandomApprovalNotes($status) : null,
                    'attachment' => rand(0, 1) ? 'sample-attachment.pdf' : null,
                ]);
            }
        }

        $this->command->info('Leave requests seeded successfully!');
    }

    private function getRandomReason($leaveType)
    {
        $reasons = [
            'annual' => [
                'Family vacation to Bali',
                'Personal time off for rest',
                'Attending family event',
                'Holiday with friends',
                'Personal development time'
            ],
            'sick' => [
                'Fever and flu symptoms',
                'Dental appointment',
                'Medical check-up',
                'Recovery from minor surgery',
                'Not feeling well today'
            ],
            'maternity' => [
                'Maternity leave for childbirth',
                'Pre-natal care appointments',
                'Post-natal recovery period',
                'Taking care of newborn baby'
            ],
            'paternity' => [
                'Supporting wife during childbirth',
                'Taking care of newborn baby',
                'Family bonding time'
            ],
            'other' => [
                'Personal emergency',
                'Family emergency',
                'Religious holiday',
                'Wedding ceremony',
                'Funeral attendance'
            ]
        ];

        $typeReasons = $reasons[$leaveType] ?? $reasons['other'];
        return $typeReasons[array_rand($typeReasons)];
    }

    private function getRandomApprovalNotes($status)
    {
        if ($status === 'approved') {
            $notes = [
                'Approved. Enjoy your time off!',
                'Approved. Please ensure handover is completed.',
                'Approved. Have a great vacation!',
                'Approved. Take care and get well soon.',
                'Approved. See you when you return.'
            ];
        } else {
            $notes = [
                'Rejected due to insufficient leave balance.',
                'Rejected. Please provide medical certificate.',
                'Rejected. Too many employees on leave during this period.',
                'Rejected. Please submit request earlier next time.',
                'Rejected. Critical project deadline during this period.'
            ];
        }

        return $notes[array_rand($notes)];
    }
} 