<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class EmployeeSalaryComponent extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'company_id',
        'employee_id',
        'salary_component_id',
        'amount',
        'calculation_type',
        'percentage_value',
        'reference_type',
        'is_active',
        'effective_date',
        'expiry_date',
        'notes'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'percentage_value' => 'decimal:2',
        'is_active' => 'boolean',
        'effective_date' => 'date',
        'expiry_date' => 'date'
    ];

    /**
     * Get the company that owns the employee salary component.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the employee that owns the salary component.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the salary component.
     */
    public function salaryComponent()
    {
        return $this->belongsTo(SalaryComponent::class);
    }

    /**
     * Scope a query to only include active components.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include components effective for a given date.
     */
    public function scopeEffectiveForDate($query, $date = null)
    {
        $date = $date ?? now()->toDateString();
        
        return $query->where(function ($q) use ($date) {
            $q->whereNull('effective_date')
              ->orWhere('effective_date', '<=', $date);
        })->where(function ($q) use ($date) {
            $q->whereNull('expiry_date')
              ->orWhere('expiry_date', '>=', $date);
        });
    }

    /**
     * Scope a query to only include earning components.
     */
    public function scopeEarnings($query)
    {
        return $query->whereHas('salaryComponent', function ($q) {
            $q->where('type', 'earning');
        });
    }

    /**
     * Scope a query to only include deduction components.
     */
    public function scopeDeductions($query)
    {
        return $query->whereHas('salaryComponent', function ($q) {
            $q->where('type', 'deduction');
        });
    }

    /**
     * Calculate the actual amount for this component based on calculation type.
     */
    public function calculateAmount($referenceSalary = null)
    {
        if ($this->calculation_type === 'fixed') {
            return $this->amount;
        }

        if ($this->calculation_type === 'percentage' && $this->percentage_value && $referenceSalary) {
            return ($this->percentage_value / 100) * $referenceSalary;
        }

        return 0;
    }

    /**
     * Get the formatted amount.
     */
    public function getFormattedAmountAttribute()
    {
        if ($this->calculation_type === 'fixed') {
            return 'Rp ' . number_format($this->amount, 0, ',', '.');
        }

        if ($this->calculation_type === 'percentage') {
            return $this->percentage_value . '%';
        }

        return 'Rp 0';
    }

    /**
     * Get the calculation type text.
     */
    public function getCalculationTypeTextAttribute()
    {
        return $this->calculation_type === 'fixed' ? 'Nilai Tetap' : 'Persentase';
    }

    /**
     * Get the reference type text.
     */
    public function getReferenceTypeTextAttribute()
    {
        if (!$this->reference_type) return '-';
        
        $types = [
            'basic_salary' => 'Gaji Pokok',
            'gross_salary' => 'Gaji Kotor',
            'net_salary' => 'Gaji Bersih'
        ];

        return $types[$this->reference_type] ?? $this->reference_type;
    }

    /**
     * Check if component is currently effective.
     */
    public function isCurrentlyEffective()
    {
        $today = now()->toDateString();
        
        if ($this->effective_date && $this->effective_date > $today) {
            return false;
        }
        
        if ($this->expiry_date && $this->expiry_date < $today) {
            return false;
        }
        
        return true;
    }
}
