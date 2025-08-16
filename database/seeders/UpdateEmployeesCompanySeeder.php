<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;

class UpdateEmployeesCompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Update all employees to use the same company_id
        Employee::query()->update([
            'company_id' => 'de5c6d3d-97e7-46a8-9eae-d20c792e5b98'
        ]);
        
        echo "All employees company_id updated successfully!\n";
    }
}
