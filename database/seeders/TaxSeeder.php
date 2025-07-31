<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tax;
use App\Models\Employee;
use App\Models\Payroll;
use Carbon\Carbon;

class TaxSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employees = Employee::all();
        
        foreach ($employees as $employee) {
            // Create tax calculations for the last 3 months
            for ($i = 1; $i <= 3; $i++) {
                $date = Carbon::now()->subMonths($i);
                $period = $date->format('Y-m');
                
                // Get payroll for this period
                $payroll = Payroll::where('employee_id', $employee->id)
                    ->where('month', $date->month)
                    ->where('year', $date->year)
                    ->first();
                
                if ($payroll) {
                    // Calculate taxable income
                    $taxableIncome = $payroll->basic_salary + 
                                   $payroll->allowances + 
                                   $payroll->overtime_pay + 
                                   $payroll->attendance_bonus;
                    
                    // Calculate tax using the Tax model
                    $taxCalculation = Tax::calculatePPh21($employee, $taxableIncome);
                    
                    Tax::create([
                        'company_id' => $employee->company_id,
                        'employee_id' => $employee->id,
                        'payroll_id' => $payroll->id,
                        'tax_period' => $period,
                        'taxable_income' => $taxableIncome,
                        'ptkp_status' => $employee->ptkp_status ?? 'TK/0',
                        'ptkp_amount' => $taxCalculation['ptkp_amount'],
                        'taxable_base' => $taxCalculation['taxable_base'],
                        'tax_amount' => $taxCalculation['tax_amount'],
                        'tax_bracket' => $taxCalculation['tax_bracket'],
                        'tax_rate' => $taxCalculation['tax_rate'],
                        'status' => 'calculated',
                        'notes' => 'Sample tax calculation for testing',
                    ]);
                }
            }
        }
    }
} 