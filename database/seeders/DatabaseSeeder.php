<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Call other seeders in proper order
        $this->call([
            CompanySeeder::class,
            UserSeeder::class,
            DepartmentSeeder::class,
            PositionSeeder::class,
            EmployeeSeeder::class,
            PayrollSeeder::class,
            AttendanceSeeder::class,
            LeaveSeeder::class,
            OvertimeSeeder::class,
            TaxSeeder::class,
            BpjsSeeder::class,
            SalaryComponentSeeder::class,
        ]);
    }
}
