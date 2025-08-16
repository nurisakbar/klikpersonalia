<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Leave;
use App\Models\Employee;
use App\Models\User;
use App\Models\Company;
use Carbon\Carbon;

class FixLeaveApprovalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        echo "Starting Leave Approval Fix...\n";
        
        // 1. Create company if not exists
        $company = Company::firstOrCreate(
            ['id' => 'de5c6d3d-97e7-46a8-9eae-d20c792e5b98'],
            [
                'name' => 'KlikMedis Demo Company',
                'address' => 'Jl. Demo No. 1, Jakarta',
                'city' => 'Jakarta',
                'province' => 'DKI Jakarta',
                'country' => 'Indonesia',
                'postal_code' => '12345',
                'phone' => '021-12345678',
                'email' => 'demo@klikmedis.com',
                'tax_number' => '123456789',
                'created_at' => now(),
                'updated_at' => now()
            ]
        );
        echo "Company: " . $company->name . "\n";
        
        // 2. Update admin user
        $admin = User::where('name', 'Administrator')->first();
        if ($admin) {
            $admin->update(['company_id' => $company->id]);
            echo "Admin company_id updated\n";
        }
        
        // 3. Update all employees
        $employeeCount = Employee::count();
        Employee::query()->update(['company_id' => $company->id]);
        echo "Updated {$employeeCount} employees\n";
        
        // 4. Delete all existing leaves and create fresh ones
        Leave::truncate();
        echo "Deleted all existing leaves\n";
        
        // 5. Get employees
        $employees = Employee::where('company_id', $company->id)->take(10)->get();
        echo "Found " . $employees->count() . " employees\n";
        
        if ($employees->isEmpty()) {
            echo "ERROR: No employees found!\n";
            return;
        }
        
        // 6. Create pending leave requests
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
        
        $pendingCount = 0;
        foreach ($employees as $index => $employee) {
            // Create 2 pending leaves per employee
            for ($i = 0; $i < 2; $i++) {
                $startDate = Carbon::now()->addDays(rand(5, 30));
                $endDate = $startDate->copy()->addDays(rand(1, 7));
                $totalDays = $startDate->diffInDays($endDate) + 1;
                
                Leave::create([
                    'company_id' => $company->id,
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
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                $pendingCount++;
            }
        }
        
        echo "Created {$pendingCount} pending leave requests!\n";
        
        // 7. Verify
        $totalLeaves = Leave::count();
        $pendingLeaves = Leave::where('status', 'pending')->count();
        $pendingWithCompany = Leave::where('status', 'pending')->where('company_id', $company->id)->count();
        
        echo "=== VERIFICATION ===\n";
        echo "Total leaves: {$totalLeaves}\n";
        echo "Pending leaves: {$pendingLeaves}\n";
        echo "Pending with company: {$pendingWithCompany}\n";
        echo "Admin company_id: " . ($admin ? $admin->company_id : 'NULL') . "\n";
        echo "=== END VERIFICATION ===\n";
    }
}
