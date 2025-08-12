<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Company;
use App\Models\Employee;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first company
        $company = Company::first();
        
        if (!$company) {
            $this->command->error('No company found. Please run CompanySeeder first.');
            return;
        }

        // Create admin user
        $adminUser = User::create([
            'name' => 'Administrator',
            'email' => 'admin@klikmedis.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'company_id' => $company->id,
            'role' => 'admin',
            'is_company_owner' => true,
            'phone' => '081234567890',
            'position' => 'System Administrator',
            'department' => 'IT',
            'status' => 'active',
        ]);

        // Create demo user
        $demoUser = User::create([
            'name' => 'Demo User',
            'email' => 'demo@klikmedis.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'company_id' => $company->id,
            'role' => 'employee',
            'is_company_owner' => false,
            'phone' => '081234567891',
            'position' => 'Software Developer',
            'department' => 'IT',
            'status' => 'active',
        ]);

        // Create employee for demo user
        Employee::create([
            'employee_id' => 'EMP001',
            'name' => 'Demo User',
            'email' => 'demo@klikmedis.com',
            'phone' => '081234567891',
            'address' => 'Jl. Demo No. 1, Jakarta',
            'join_date' => '2024-01-01',
            'department' => 'IT',
            'position' => 'Software Developer',
            'basic_salary' => 8000000,
            'status' => 'active',
            'emergency_contact' => '081234567892',
            'bank_name' => 'BCA',
            'bank_account' => '1234567890',
            'company_id' => $company->id,
            'user_id' => $demoUser->id,
        ]);

        $this->command->info('Users created successfully!');
        $this->command->info('Admin: admin@klikmedis.com / password');
        $this->command->info('Demo: demo@klikmedis.com / password');
    }
}
