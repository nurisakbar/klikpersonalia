<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Payroll;
use App\Models\Employee;
use App\Models\Company;

class PayrollSeeder extends Seeder
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
        
        $periods = ['Januari 2024', 'Februari 2024', 'Maret 2024'];

        foreach ($employees as $employee) {
            foreach ($periods as $period) {
                $basicSalary = $employee->basic_salary;
                $allowance = $basicSalary * 0.2; // 20% dari gaji pokok
                $overtime = rand(100000, 500000);
                $bonus = rand(200000, 1000000);
                $deduction = rand(50000, 200000);
                $taxAmount = $basicSalary * 0.05; // 5% pajak
                $bpjsAmount = $basicSalary * 0.04; // 4% BPJS

                $totalSalary = $basicSalary + $allowance + $overtime + $bonus - $deduction - $taxAmount - $bpjsAmount;

                Payroll::create([
                    'company_id' => $employee->company_id,
                    'employee_id' => $employee->id,
                    'company_id' => $company->id,
                    'period' => $period,
                    'basic_salary' => $basicSalary,
                    'allowance' => $allowance,
                    'overtime' => $overtime,
                    'bonus' => $bonus,
                    'deduction' => $deduction,
                    'tax_amount' => $taxAmount,
                    'bpjs_amount' => $bpjsAmount,
                    'total_salary' => $totalSalary,
                    'status' => $this->getRandomStatus(),
                    'payment_date' => $this->getRandomPaymentDate($period),
                    'notes' => 'Pembayaran gaji bulanan ' . $period . ' untuk ' . $employee->name
                ]);
            }
        }

        $this->command->info('Payroll data seeded successfully!');
    }

    /**
     * Get random status for payroll.
     */
    private function getRandomStatus()
    {
        $statuses = ['draft', 'approved', 'paid'];
        return $statuses[array_rand($statuses)];
    }

    /**
     * Get random payment date based on period.
     */
    private function getRandomPaymentDate($period)
    {
        $monthMap = [
            'Januari 2024' => '2024-01-31',
            'Februari 2024' => '2024-02-29',
            'Maret 2024' => '2024-03-31',
            'April 2024' => '2024-04-30',
            'Mei 2024' => '2024-05-31',
            'Juni 2024' => '2024-06-30',
            'Juli 2024' => '2024-07-31',
            'Agustus 2024' => '2024-08-31',
            'September 2024' => '2024-09-30',
            'Oktober 2024' => '2024-10-31',
            'November 2024' => '2024-11-30',
            'Desember 2024' => '2024-12-31',
        ];

        return $monthMap[$period] ?? null;
    }
}
