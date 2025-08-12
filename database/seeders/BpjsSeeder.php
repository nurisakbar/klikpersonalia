<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Bpjs;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\Company;

class BpjsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = Company::all();

        foreach ($companies as $company) {
            $employees = Employee::where('company_id', $company->id)->get();
            $payrolls = Payroll::where('company_id', $company->id)->get();

            foreach ($employees as $employee) {
                // Find payroll for this employee
                $payroll = $payrolls->where('employee_id', $employee->id)->first();
                
                if (!$payroll) {
                    continue;
                }

                $baseSalary = $employee->basic_salary;

                // Generate BPJS Kesehatan records for the last 3 months
                for ($i = 0; $i < 3; $i++) {
                    $period = now()->subMonths($i)->format('Y-m');
                    
                    // Check if BPJS Kesehatan record already exists
                    $existingKesehatan = Bpjs::where('company_id', $company->id)
                        ->where('employee_id', $employee->id)
                        ->where('bpjs_period', $period)
                        ->where('bpjs_type', 'kesehatan')
                        ->first();

                    if (!$existingKesehatan && $employee->bpjs_kesehatan_active) {
                        $kesehatanCalculation = Bpjs::calculateKesehatan($employee, $baseSalary, $period);
                        
                        Bpjs::create([
                            'company_id' => $company->id,
                            'employee_id' => $employee->id,
                            'payroll_id' => $payroll->id,
                            'bpjs_period' => $period,
                            'bpjs_type' => 'kesehatan',
                            'employee_contribution' => $kesehatanCalculation['employee_contribution'],
                            'company_contribution' => $kesehatanCalculation['company_contribution'],
                            'total_contribution' => $kesehatanCalculation['total_contribution'],
                            'base_salary' => $kesehatanCalculation['base_salary'],
                            'contribution_rate_employee' => $kesehatanCalculation['contribution_rate_employee'],
                            'contribution_rate_company' => $kesehatanCalculation['contribution_rate_company'],
                            'status' => $i === 0 ? 'paid' : ($i === 1 ? 'calculated' : 'pending'),
                            'payment_date' => $i === 0 ? now()->subDays(rand(1, 30)) : null,
                            'notes' => $i === 0 ? 'Payment completed' : null,
                        ]);
                    }

                    // Check if BPJS Ketenagakerjaan record already exists
                    $existingKetenagakerjaan = Bpjs::where('company_id', $company->id)
                        ->where('employee_id', $employee->id)
                        ->where('bpjs_period', $period)
                        ->where('bpjs_type', 'ketenagakerjaan')
                        ->first();

                    if (!$existingKetenagakerjaan && $employee->bpjs_ketenagakerjaan_active) {
                        $ketenagakerjaanCalculation = Bpjs::calculateKetenagakerjaan($employee, $baseSalary, $period);
                        
                        Bpjs::create([
                            'company_id' => $company->id,
                            'employee_id' => $employee->id,
                            'payroll_id' => $payroll->id,
                            'bpjs_period' => $period,
                            'bpjs_type' => 'ketenagakerjaan',
                            'employee_contribution' => $ketenagakerjaanCalculation['employee_contribution'],
                            'company_contribution' => $ketenagakerjaanCalculation['company_contribution'],
                            'total_contribution' => $ketenagakerjaanCalculation['total_contribution'],
                            'base_salary' => $ketenagakerjaanCalculation['base_salary'],
                            'contribution_rate_employee' => $ketenagakerjaanCalculation['contribution_rate_employee'],
                            'contribution_rate_company' => $ketenagakerjaanCalculation['contribution_rate_company'],
                            'status' => $i === 0 ? 'paid' : ($i === 1 ? 'calculated' : 'pending'),
                            'payment_date' => $i === 0 ? now()->subDays(rand(1, 30)) : null,
                            'notes' => $i === 0 ? 'Payment completed' : null,
                        ]);
                    }
                }
            }
        }
    }
} 