<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Leave;
use App\Models\Employee;
use Carbon\Carbon;

class CreatePendingLeavesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companyId = 'de5c6d3d-97e7-46a8-9eae-d20c792e5b98';
        
        // Get some employees
        $employees = Employee::where('company_id', $companyId)->take(5)->get();
        
        if ($employees->isEmpty()) {
            echo "No employees found for company!\n";
            return;
        }
        
        // Create pending leave requests
        $leaveTypes = ['annual', 'sick', 'maternity', 'paternity', 'other'];
        $reasons = [
            'Annual vacation with family',
            'Medical check-up',
            'Maternity leave for childbirth',
            'Taking care of newborn baby',
            'Personal emergency',
            'Family event attendance',
            'Medical treatment',
            'Wedding ceremony',
            'Funeral attendance',
            'Religious ceremony'
        ];
        
        foreach ($employees as $index => $employee) {
            $startDate = Carbon::now()->addDays(rand(5, 30));
            $endDate = $startDate->copy()->addDays(rand(1, 7));
            $totalDays = $startDate->diffInDays($endDate) + 1;
            
            Leave::create([
                'company_id' => $companyId,
                'employee_id' => $employee->id,
                'leave_type' => $leaveTypes[array_rand($leaveTypes)],
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'total_days' => $totalDays,
                'reason' => $reasons[array_rand($reasons)],
                'status' => 'pending',
                'approved_by' => null,
                'approved_at' => null,
                'approval_notes' => null,
                'attachment' => null,
            ]);
        }
        
        echo "Created " . $employees->count() . " pending leave requests!\n";
    }
}
