<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class Bpjs extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'company_id',
        'employee_id',
        'payroll_id',
        'bpjs_period',
        'bpjs_type', // 'kesehatan' or 'ketenagakerjaan'
        'employee_contribution',
        'company_contribution',
        'total_contribution',
        'base_salary',
        'contribution_rate_employee',
        'contribution_rate_company',
        'status', // 'pending', 'calculated', 'paid', 'verified'
        'payment_date',
        'notes',
    ];

    protected $casts = [
        'employee_contribution' => 'decimal:2',
        'company_contribution' => 'decimal:2',
        'total_contribution' => 'decimal:2',
        'base_salary' => 'decimal:2',
        'contribution_rate_employee' => 'decimal:4',
        'contribution_rate_company' => 'decimal:4',
        'payment_date' => 'date',
    ];

    // Relationships
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }

    // Constants for BPJS types
    const TYPE_KESEHATAN = 'kesehatan';
    const TYPE_KETENAGAKERJAAN = 'ketenagakerjaan';

    // Constants for status
    const STATUS_PENDING = 'pending';
    const STATUS_CALCULATED = 'calculated';
    const STATUS_PAID = 'paid';
    const STATUS_VERIFIED = 'verified';

    // BPJS Kesehatan rates (2024)
    const KESEHATAN_EMPLOYEE_RATE = 0.01; // 1% dari gaji pokok
    const KESEHATAN_COMPANY_RATE = 0.04; // 4% dari gaji pokok

    // BPJS Ketenagakerjaan rates (2024)
    const JHT_EMPLOYEE_RATE = 0.02; // 2% dari gaji pokok
    const JHT_COMPANY_RATE = 0.037; // 3.7% dari gaji pokok
    const JKK_COMPANY_RATE = 0.0024; // 0.24% dari gaji pokok (variabel berdasarkan risiko)
    const JKM_COMPANY_RATE = 0.003; // 0.3% dari gaji pokok
    const JP_EMPLOYEE_RATE = 0.01; // 1% dari gaji pokok
    const JP_COMPANY_RATE = 0.02; // 2% dari gaji pokok

    // Maximum base salary for BPJS calculation (2024)
    const MAX_BASE_SALARY = 12000000; // Rp 12.000.000

    /**
     * Calculate BPJS Kesehatan contribution
     */
    public static function calculateKesehatan($employee, $baseSalary, $period = null)
    {
        // Cap base salary at maximum
        $cappedSalary = min($baseSalary, self::MAX_BASE_SALARY);
        
        $employeeContribution = $cappedSalary * self::KESEHATAN_EMPLOYEE_RATE;
        $companyContribution = $cappedSalary * self::KESEHATAN_COMPANY_RATE;
        $totalContribution = $employeeContribution + $companyContribution;

        return [
            'employee_contribution' => round($employeeContribution, 2),
            'company_contribution' => round($companyContribution, 2),
            'total_contribution' => round($totalContribution, 2),
            'base_salary' => $cappedSalary,
            'contribution_rate_employee' => self::KESEHATAN_EMPLOYEE_RATE,
            'contribution_rate_company' => self::KESEHATAN_COMPANY_RATE,
        ];
    }

    /**
     * Calculate BPJS Ketenagakerjaan contribution
     */
    public static function calculateKetenagakerjaan($employee, $baseSalary, $period = null)
    {
        // Cap base salary at maximum
        $cappedSalary = min($baseSalary, self::MAX_BASE_SALARY);
        
        // JHT
        $jhtEmployee = $cappedSalary * self::JHT_EMPLOYEE_RATE;
        $jhtCompany = $cappedSalary * self::JHT_COMPANY_RATE;
        
        // JKK (variabel berdasarkan risiko perusahaan, default 0.24%)
        $jkkCompany = $cappedSalary * self::JKK_COMPANY_RATE;
        
        // JKM
        $jkmCompany = $cappedSalary * self::JKM_COMPANY_RATE;
        
        // JP
        $jpEmployee = $cappedSalary * self::JP_EMPLOYEE_RATE;
        $jpCompany = $cappedSalary * self::JP_COMPANY_RATE;

        $employeeContribution = $jhtEmployee + $jpEmployee;
        $companyContribution = $jhtCompany + $jkkCompany + $jkmCompany + $jpCompany;
        $totalContribution = $employeeContribution + $companyContribution;

        return [
            'employee_contribution' => round($employeeContribution, 2),
            'company_contribution' => round($companyContribution, 2),
            'total_contribution' => round($totalContribution, 2),
            'base_salary' => $cappedSalary,
            'contribution_rate_employee' => self::JHT_EMPLOYEE_RATE + self::JP_EMPLOYEE_RATE,
            'contribution_rate_company' => self::JHT_COMPANY_RATE + self::JKK_COMPANY_RATE + self::JKM_COMPANY_RATE + self::JP_COMPANY_RATE,
            'breakdown' => [
                'jht_employee' => round($jhtEmployee, 2),
                'jht_company' => round($jhtCompany, 2),
                'jkk_company' => round($jkkCompany, 2),
                'jkm_company' => round($jkmCompany, 2),
                'jp_employee' => round($jpEmployee, 2),
                'jp_company' => round($jpCompany, 2),
            ]
        ];
    }

    /**
     * Get BPJS type options
     */
    public static function getTypeOptions()
    {
        return [
            self::TYPE_KESEHATAN => 'BPJS Kesehatan',
            self::TYPE_KETENAGAKERJAAN => 'BPJS Ketenagakerjaan',
        ];
    }

    /**
     * Get status options
     */
    public static function getStatusOptions()
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_CALCULATED => 'Calculated',
            self::STATUS_PAID => 'Paid',
            self::STATUS_VERIFIED => 'Verified',
        ];
    }

    /**
     * Scope for filtering by company
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope for filtering by period
     */
    public function scopeForPeriod($query, $period)
    {
        return $query->where('bpjs_period', $period);
    }

    /**
     * Scope for filtering by type
     */
    public function scopeForType($query, $type)
    {
        return $query->where('bpjs_type', $type);
    }
} 