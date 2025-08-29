<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class SalaryComponent extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'company_id',
        'name',
        'description',
        'default_value',
        'type',
        'is_active',
        'is_taxable',
        'is_bpjs_calculated',
        'sort_order'
    ];

    protected $casts = [
        'default_value' => 'decimal:2',
        'is_active' => 'boolean',
        'is_taxable' => 'boolean',
        'is_bpjs_calculated' => 'boolean',
        'sort_order' => 'integer'
    ];

    // Type constants
    const TYPE_EARNING = 'earning';
    const TYPE_DEDUCTION = 'deduction';

    /**
     * Get the company that owns the salary component.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Scope a query to only include active components.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include earning components.
     */
    public function scopeEarnings($query)
    {
        return $query->where('type', self::TYPE_EARNING);
    }

    /**
     * Scope a query to only include deduction components.
     */
    public function scopeDeductions($query)
    {
        return $query->where('type', self::TYPE_DEDUCTION);
    }

    /**
     * Scope a query to only include taxable components.
     */
    public function scopeTaxable($query)
    {
        return $query->where('is_taxable', true);
    }

    /**
     * Scope a query to only include BPJS calculated components.
     */
    public function scopeBpjsCalculated($query)
    {
        return $query->where('is_bpjs_calculated', true);
    }

    /**
     * Get the formatted default value.
     */
    public function getFormattedDefaultValueAttribute()
    {
        return number_format($this->default_value, 2);
    }

    /**
     * Get the status text.
     */
    public function getStatusTextAttribute()
    {
        return $this->is_active ? 'Aktif' : 'Tidak Aktif';
    }

    /**
     * Scope to filter by current company.
     */
    public function scopeCurrentCompany($query)
    {
        $companyId = auth()->user()->company_id ?? session('company_id');
        return $query->where('company_id', $companyId);
    }

    /**
     * Get the type text.
     */
    public function getTypeTextAttribute()
    {
        return $this->type === self::TYPE_EARNING ? 'Pendapatan' : 'Potongan';
    }

    /**
     * Get the type badge.
     */
    public function getTypeBadgeAttribute()
    {
        $badgeClass = $this->type === self::TYPE_EARNING ? 'badge badge-success' : 'badge badge-danger';
        return '<span class="' . $badgeClass . '">' . $this->type_text . '</span>';
    }

    /**
     * Get the type icon.
     */
    public function getTypeIconAttribute()
    {
        return $this->type === self::TYPE_EARNING ? 'fas fa-plus-circle' : 'fas fa-minus-circle';
    }

    /**
     * Check if component is being used in payrolls.
     */
    public function isUsedInPayrolls(): bool
    {
        // Check if component is assigned to any employees
        return $this->employeeComponents()->exists();
    }

    /**
     * Get the employee components that use this salary component.
     */
    public function employeeComponents()
    {
        return $this->hasMany(EmployeeSalaryComponent::class);
    }

    /**
     * Get the count of employees using this component.
     */
    public function getEmployeeCountAttribute()
    {
        return $this->employeeComponents()->count();
    }

    /**
     * Check if component is assigned to a specific employee.
     */
    public function isAssignedToEmployee($employeeId)
    {
        return $this->employeeComponents()
            ->where('employee_id', $employeeId)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Check if this is an earning component.
     */
    public function isEarning(): bool
    {
        return $this->type === self::TYPE_EARNING;
    }

    /**
     * Check if this is a deduction component.
     */
    public function isDeduction(): bool
    {
        return $this->type === self::TYPE_DEDUCTION;
    }

    /**
     * Get all type options for forms.
     */
    public static function getTypeOptions(): array
    {
        return [
            self::TYPE_EARNING => 'Pendapatan',
            self::TYPE_DEDUCTION => 'Potongan'
        ];
    }
}
