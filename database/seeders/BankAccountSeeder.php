<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\BankAccount;
use App\Models\Employee;
use App\Models\User;

class BankAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();
        if (!$user) {
            $this->command->error('No user found. Please run UserSeeder first.');
            return;
        }

        $employees = Employee::where('company_id', $user->company_id)->get();
        if ($employees->isEmpty()) {
            $this->command->error('No employees found. Please run EmployeeSeeder first.');
            return;
        }

        $banks = [
            'Bank Central Asia (BCA)',
            'Bank Rakyat Indonesia (BRI)',
            'Bank Mandiri',
            'Bank Negara Indonesia (BNI)',
            'Bank Danamon',
            'Bank CIMB Niaga',
            'Bank Permata',
            'Bank Panin',
            'Bank Mega',
            'Bank BTPN'
        ];

        $accountTypes = ['savings', 'current', 'salary'];

        foreach ($employees as $index => $employee) {
            // Create 1-3 bank accounts per employee
            $numAccounts = rand(1, 3);
            
            for ($i = 0; $i < $numAccounts; $i++) {
                $bankName = $banks[array_rand($banks)];
                $accountType = $accountTypes[array_rand($accountTypes)];
                $isPrimary = ($i === 0); // First account is primary
                
                BankAccount::create([
                    'company_id' => $user->company_id,
                    'employee_id' => $employee->id,
                    'bank_name' => $bankName,
                    'account_number' => str_pad(rand(1000000000, 9999999999), 10, '0', STR_PAD_LEFT),
                    'account_holder_name' => $employee->name,
                    'branch_code' => 'BR' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
                    'swift_code' => 'SWIFT' . strtoupper(substr($bankName, 0, 4)) . 'ID',
                    'account_type' => $accountType,
                    'is_active' => true,
                    'is_primary' => $isPrimary,
                    'notes' => $isPrimary ? 'Rekening utama untuk transfer gaji' : 'Rekening tambahan',
                ]);
            }
        }

        $this->command->info('Bank accounts seeded successfully!');
    }
}
