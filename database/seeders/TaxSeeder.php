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
            // Create tax calculations for multiple periods
            $periods = [
                'Agustus 2025', 'September 2025', 'Oktober 2025', 'November 2025', 'Desember 2025',
                'Januari 2025', 'Februari 2025', 'Maret 2025', 'April 2025', 'Mei 2025', 'Juni 2025', 'Juli 2025'
            ];
            
            foreach ($periods as $period) {
                // Format tax_period to Y-m format for consistency
                $monthNames = [
                    'Januari' => '01', 'Februari' => '02', 'Maret' => '03', 'April' => '04',
                    'Mei' => '05', 'Juni' => '06', 'Juli' => '07', 'Agustus' => '08',
                    'September' => '09', 'Oktober' => '10', 'November' => '11', 'Desember' => '12'
                ];
                $periodParts = explode(' ', $period);
                $taxPeriod = $periodParts[1] . '-' . $monthNames[$periodParts[0]];
                
                // Check if tax already exists
                $existingTax = Tax::where('employee_id', $employee->id)
                    ->where('tax_period', $taxPeriod)
                    ->first();
                
                if (!$existingTax) {
                    // Get payroll for this period if exists
                    $payroll = Payroll::where('employee_id', $employee->id)
                        ->where('period', $period)
                        ->first();
                    
                    // Always use high taxable income to ensure tax is calculated (above PTKP threshold)
                    // This ensures we have realistic tax data for testing
                    $baseIncome = 150000000; // Base 150 juta
                    $taxableIncome = $baseIncome * (1.0 + (rand(0, 100) / 100)); // 100% to 200% variation
                    
                    // Calculate tax using the Tax model
                    $taxCalculation = Tax::calculatePPh21($employee, $taxableIncome);
                    
                    // Determine status based on period and random chance for variety
                    $status = 'calculated'; // Default
                    
                    // Different status distribution based on period
                    $month = $periodParts[0];
                    $year = $periodParts[1];
                    
                    // For current year, more varied status
                    if ($year == '2025') {
                        $statusChance = rand(1, 100);
                        if ($statusChance <= 40) {
                            $status = 'calculated'; // 40% - Dihitung
                        } elseif ($statusChance <= 65) {
                            $status = 'pending'; // 25% - Menunggu
                        } elseif ($statusChance <= 85) {
                            $status = 'paid'; // 20% - Dibayar
                        } else {
                            $status = 'verified'; // 15% - Terverifikasi
                        }
                    } else {
                        // For past years, mostly paid/verified
                        $statusChance = rand(1, 100);
                        if ($statusChance <= 20) {
                            $status = 'calculated';
                        } elseif ($statusChance <= 30) {
                            $status = 'pending';
                        } elseif ($statusChance <= 80) {
                            $status = 'paid';
                        } else {
                            $status = 'verified';
                        }
                    }
                    
                    Tax::create([
                        'company_id' => $employee->company_id,
                        'employee_id' => $employee->id,
                        'payroll_id' => $payroll->id ?? null,
                        'tax_period' => $taxPeriod,
                        'taxable_income' => $taxableIncome,
                        'ptkp_status' => $employee->ptkp_status ?? 'TK/0',
                        'ptkp_amount' => $taxCalculation['ptkp_amount'],
                        'taxable_base' => $taxCalculation['taxable_base'],
                        'tax_amount' => $taxCalculation['tax_amount'],
                        'tax_bracket' => $taxCalculation['tax_bracket'],
                        'tax_rate' => $taxCalculation['tax_rate'],
                        'status' => $status,
                        'notes' => 'Sample tax calculation for testing',
                    ]);
                }
            }
        }
    }
} 