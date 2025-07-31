<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Overtime;
use App\Models\Employee;
use App\Models\User;
use App\Models\Company;
use Carbon\Carbon;

class OvertimeSeeder extends Seeder
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

        $overtimeTypes = ['regular', 'holiday', 'weekend', 'emergency'];
        $statuses = ['pending', 'approved', 'rejected'];

        foreach ($employees as $employee) {
            // Create 2-4 overtime requests per employee
            $numOvertimes = rand(2, 4);
            
            for ($i = 0; $i < $numOvertimes; $i++) {
                $overtimeType = $overtimeTypes[array_rand($overtimeTypes)];
                $status = $statuses[array_rand($statuses)];
                
                // Generate random date (past and future)
                $date = Carbon::now()->addDays(rand(-30, 30));
                
                // Generate random times
                $startHour = rand(17, 20); // After 5 PM
                $endHour = $startHour + rand(1, 4); // 1-4 hours overtime
                $startTime = sprintf('%02d:00', $startHour);
                $endTime = sprintf('%02d:00', $endHour);
                
                // Calculate total hours
                $totalHours = $endHour - $startHour;

                $overtime = Overtime::create([
                    'employee_id' => $employee->id,
                    'company_id' => $company->id,
                    'overtime_type' => $overtimeType,
                    'date' => $date,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'total_hours' => $totalHours,
                    'reason' => $this->getRandomReason($overtimeType),
                    'status' => $status,
                    'approved_by' => $status !== 'pending' ? $adminUser->id : null,
                    'approved_at' => $status !== 'pending' ? Carbon::now()->subDays(rand(1, 30)) : null,
                    'approval_notes' => $status !== 'pending' ? $this->getRandomApprovalNotes($status) : null,
                    'attachment' => rand(0, 1) ? 'sample-overtime-attachment.pdf' : null,
                ]);
            }
        }

        $this->command->info('Overtime requests seeded successfully!');
    }

    private function getRandomReason($overtimeType)
    {
        $reasons = [
            'regular' => [
                'Project deadline approaching',
                'Client meeting preparation',
                'System maintenance work',
                'Report completion',
                'Team coordination tasks'
            ],
            'holiday' => [
                'Critical system update',
                'Emergency client support',
                'Server maintenance',
                'Security patch deployment',
                'Data backup and recovery'
            ],
            'weekend' => [
                'Weekend project work',
                'System migration',
                'Database optimization',
                'Code deployment',
                'Testing and quality assurance'
            ],
            'emergency' => [
                'System crash recovery',
                'Security incident response',
                'Critical bug fix',
                'Data breach investigation',
                'Infrastructure emergency'
            ]
        ];

        $typeReasons = $reasons[$overtimeType] ?? $reasons['regular'];
        return $typeReasons[array_rand($typeReasons)];
    }

    private function getRandomApprovalNotes($status)
    {
        if ($status === 'approved') {
            $notes = [
                'Approved. Good work on meeting the deadline.',
                'Approved. Please ensure proper handover.',
                'Approved. Keep up the good work.',
                'Approved. Remember to log your hours properly.',
                'Approved. Thanks for the extra effort.'
            ];
        } else {
            $notes = [
                'Rejected due to insufficient justification.',
                'Rejected. Please provide more details.',
                'Rejected. Not enough advance notice.',
                'Rejected. Work can be completed during regular hours.',
                'Rejected. Budget constraints this month.'
            ];
        }

        return $notes[array_rand($notes)];
    }
} 