<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class Benefit extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'company_id',
        'name',
        'description',
        'benefit_type',
        'cost_type',
        'cost_amount',
        'cost_percentage',
        'provider',
        'policy_number',
        'start_date',
        'end_date',
        'is_active',
        'eligibility_criteria',
        'coverage_details',
        'notes'
    ];

    protected $casts = [
        'cost_amount' => 'decimal:2',
        'cost_percentage' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'eligibility_criteria' => 'array',
        'coverage_details' => 'array'
    ];

    // Benefit types
    const TYPE_HEALTH_INSURANCE = 'health_insurance';
    const TYPE_LIFE_INSURANCE = 'life_insurance';
    const TYPE_DISABILITY_INSURANCE = 'disability_insurance';
    const TYPE_RETIREMENT_PLAN = 'retirement_plan';
    const TYPE_EDUCATION_ASSISTANCE = 'education_assistance';
    const TYPE_MEAL_ALLOWANCE = 'meal_allowance';
    const TYPE_TRANSPORT_ALLOWANCE = 'transport_allowance';
    const TYPE_HOUSING_ALLOWANCE = 'housing_allowance';
    const TYPE_OTHER = 'other';

    // Cost types
    const COST_TYPE_FIXED = 'fixed';
    const COST_TYPE_PERCENTAGE = 'percentage';
    const COST_TYPE_MIXED = 'mixed';

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function employeeBenefits()
    {
        return $this->hasMany(EmployeeBenefit::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('benefit_type', $type);
    }

    public function getTypeLabelAttribute()
    {
        $labels = [
            self::TYPE_HEALTH_INSURANCE => 'Health Insurance',
            self::TYPE_LIFE_INSURANCE => 'Life Insurance',
            self::TYPE_DISABILITY_INSURANCE => 'Disability Insurance',
            self::TYPE_RETIREMENT_PLAN => 'Retirement Plan',
            self::TYPE_EDUCATION_ASSISTANCE => 'Education Assistance',
            self::TYPE_MEAL_ALLOWANCE => 'Meal Allowance',
            self::TYPE_TRANSPORT_ALLOWANCE => 'Transport Allowance',
            self::TYPE_HOUSING_ALLOWANCE => 'Housing Allowance',
            self::TYPE_OTHER => 'Other'
        ];

        return $labels[$this->benefit_type] ?? 'Unknown';
    }

    public function getCostTypeLabelAttribute()
    {
        $labels = [
            self::COST_TYPE_FIXED => 'Fixed Amount',
            self::COST_TYPE_PERCENTAGE => 'Percentage',
            self::COST_TYPE_MIXED => 'Mixed'
        ];

        return $labels[$this->cost_type] ?? 'Unknown';
    }

    public function getStatusBadgeAttribute()
    {
        if (!$this->is_active) {
            return 'secondary';
        }

        if ($this->end_date && $this->end_date->isPast()) {
            return 'danger';
        }

        return 'success';
    }

    public function getStatusLabelAttribute()
    {
        if (!$this->is_active) {
            return 'Inactive';
        }

        if ($this->end_date && $this->end_date->isPast()) {
            return 'Expired';
        }

        return 'Active';
    }

    public function calculateCost($employeeSalary = null)
    {
        $cost = 0;

        switch ($this->cost_type) {
            case self::COST_TYPE_FIXED:
                $cost = $this->cost_amount ?? 0;
                break;
            case self::COST_TYPE_PERCENTAGE:
                if ($employeeSalary) {
                    $cost = ($employeeSalary * ($this->cost_percentage ?? 0)) / 100;
                }
                break;
            case self::COST_TYPE_MIXED:
                $cost = $this->cost_amount ?? 0;
                if ($employeeSalary) {
                    $cost += ($employeeSalary * ($this->cost_percentage ?? 0)) / 100;
                }
                break;
        }

        return round($cost, 2);
    }

    public function isEligible($employee)
    {
        if (!$this->eligibility_criteria) {
            return true;
        }

        foreach ($this->eligibility_criteria as $criteria) {
            $field = $criteria['field'] ?? '';
            $operator = $criteria['operator'] ?? '';
            $value = $criteria['value'] ?? '';

            switch ($field) {
                case 'position':
                    if (!$this->evaluateCondition($employee->position, $operator, $value)) {
                        return false;
                    }
                    break;
                case 'department':
                    if (!$this->evaluateCondition($employee->department, $operator, $value)) {
                        return false;
                    }
                    break;
                case 'salary':
                    if (!$this->evaluateCondition($employee->salary, $operator, $value)) {
                        return false;
                    }
                    break;
                case 'hire_date':
                    $hireDate = $employee->hire_date;
                    if (!$this->evaluateCondition($hireDate, $operator, $value)) {
                        return false;
                    }
                    break;
            }
        }

        return true;
    }

    private function evaluateCondition($actualValue, $operator, $expectedValue)
    {
        switch ($operator) {
            case 'equals':
                return $actualValue == $expectedValue;
            case 'not_equals':
                return $actualValue != $expectedValue;
            case 'greater_than':
                return $actualValue > $expectedValue;
            case 'less_than':
                return $actualValue < $expectedValue;
            case 'greater_than_or_equal':
                return $actualValue >= $expectedValue;
            case 'less_than_or_equal':
                return $actualValue <= $expectedValue;
            case 'contains':
                return strpos($actualValue, $expectedValue) !== false;
            case 'not_contains':
                return strpos($actualValue, $expectedValue) === false;
            default:
                return true;
        }
    }

    public function getTotalEnrolledEmployeesAttribute()
    {
        return $this->employeeBenefits()->count();
    }

    public function getTotalMonthlyCostAttribute()
    {
        return $this->employeeBenefits()->sum('monthly_cost');
    }
} 