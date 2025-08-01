<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class EmployeeBenefit extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'company_id',
        'employee_id',
        'benefit_id',
        'enrollment_date',
        'termination_date',
        'monthly_cost',
        'employer_contribution',
        'employee_contribution',
        'coverage_amount',
        'policy_number',
        'status',
        'notes'
    ];

    protected $casts = [
        'enrollment_date' => 'date',
        'termination_date' => 'date',
        'monthly_cost' => 'decimal:2',
        'employer_contribution' => 'decimal:2',
        'employee_contribution' => 'decimal:2',
        'coverage_amount' => 'decimal:2'
    ];

    // Status constants
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_PENDING = 'pending';
    const STATUS_TERMINATED = 'terminated';
    const STATUS_SUSPENDED = 'suspended';

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function benefit()
    {
        return $this->belongsTo(Benefit::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeByEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeByBenefit($query, $benefitId)
    {
        return $query->where('benefit_id', $benefitId);
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            self::STATUS_ACTIVE => 'success',
            self::STATUS_INACTIVE => 'secondary',
            self::STATUS_PENDING => 'warning',
            self::STATUS_TERMINATED => 'danger',
            self::STATUS_SUSPENDED => 'info'
        ];

        return $badges[$this->status] ?? 'secondary';
    }

    public function getStatusLabelAttribute()
    {
        $labels = [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
            self::STATUS_PENDING => 'Pending',
            self::STATUS_TERMINATED => 'Terminated',
            self::STATUS_SUSPENDED => 'Suspended'
        ];

        return $labels[$this->status] ?? 'Unknown';
    }

    public function isActive()
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isTerminated()
    {
        return $this->status === self::STATUS_TERMINATED;
    }

    public function calculateMonthlyCost($employeeSalary = null)
    {
        if (!$this->benefit) {
            return 0;
        }

        $baseCost = $this->benefit->calculateCost($employeeSalary);
        
        // If custom monthly cost is set, use it
        if ($this->monthly_cost) {
            return $this->monthly_cost;
        }

        return $baseCost;
    }

    public function calculateContributions($employeeSalary = null)
    {
        $totalCost = $this->calculateMonthlyCost($employeeSalary);
        
        // Default to 100% employer contribution if not specified
        if (!$this->employer_contribution && !$this->employee_contribution) {
            $this->employer_contribution = $totalCost;
            $this->employee_contribution = 0;
        }

        return [
            'total' => $totalCost,
            'employer' => $this->employer_contribution ?? 0,
            'employee' => $this->employee_contribution ?? 0
        ];
    }

    public function getEnrollmentDurationAttribute()
    {
        if (!$this->enrollment_date) {
            return 0;
        }

        $endDate = $this->termination_date ?? now();
        return $this->enrollment_date->diffInDays($endDate);
    }

    public function getEnrollmentDurationFormattedAttribute()
    {
        $days = $this->enrollment_duration;
        
        if ($days < 30) {
            return $days . ' days';
        } elseif ($days < 365) {
            $months = floor($days / 30);
            return $months . ' month' . ($months > 1 ? 's' : '');
        } else {
            $years = floor($days / 365);
            $months = floor(($days % 365) / 30);
            $result = $years . ' year' . ($years > 1 ? 's' : '');
            if ($months > 0) {
                $result .= ' ' . $months . ' month' . ($months > 1 ? 's' : '');
            }
            return $result;
        }
    }
} 