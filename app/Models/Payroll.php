<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Payroll extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'employee_id',
        'period',
        'basic_salary',
        'allowance',
        'overtime',
        'bonus',
        'deduction',
        'tax_amount',
        'bpjs_amount',
        'total_salary',
        'status',
        'payment_date',
        'notes',
        'company_id'
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'allowance' => 'decimal:2',
        'overtime' => 'decimal:2',
        'bonus' => 'decimal:2',
        'deduction' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'bpjs_amount' => 'decimal:2',
        'total_salary' => 'decimal:2',
        'payment_date' => 'date',
    ];

    /**
     * Get the company that owns the payroll.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the employee that owns the payroll.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Scope a query to only include draft payrolls.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope a query to only include approved payrolls.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include paid payrolls.
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope a query to only include payrolls from current company.
     */
    public function scopeCurrentCompany($query)
    {
        if (auth()->check() && auth()->user()->company_id) {
            return $query->where('company_id', auth()->user()->company_id);
        }
        return $query;
    }

    /**
     * Get the payroll's formatted basic salary.
     */
    public function getFormattedBasicSalaryAttribute()
    {
        return 'Rp ' . number_format($this->basic_salary, 0, ',', '.');
    }

    /**
     * Get the payroll's formatted total salary.
     */
    public function getFormattedTotalSalaryAttribute()
    {
        return 'Rp ' . number_format($this->total_salary, 0, ',', '.');
    }

    /**
     * Get the payroll's formatted payment date.
     */
    public function getFormattedPaymentDateAttribute()
    {
        return $this->payment_date ? $this->payment_date->format('d/m/Y') : '-';
    }

    /**
     * Get the payroll's status badge.
     */
    public function getStatusBadgeAttribute()
    {
        $statusClass = [
            'draft' => 'badge badge-secondary',
            'approved' => 'badge badge-warning',
            'paid' => 'badge badge-success'
        ];
        
        $statusText = [
            'draft' => 'Draft',
            'approved' => 'Disetujui',
            'paid' => 'Dibayar'
        ];
        
        return '<span class="' . $statusClass[$this->status] . '">' . $statusText[$this->status] . '</span>';
    }

    /**
     * Calculate total salary.
     */
    public function calculateTotalSalary()
    {
        $total = $this->basic_salary + $this->allowance + $this->overtime + $this->bonus;
        $total = $total - $this->deduction - $this->tax_amount - $this->bpjs_amount;
        
        return max(0, $total);
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payroll) {
            if (!$payroll->total_salary) {
                $payroll->total_salary = $payroll->calculateTotalSalary();
            }
        });

        static::updating(function ($payroll) {
            if ($payroll->isDirty(['basic_salary', 'allowance', 'overtime', 'bonus', 'deduction', 'tax_amount', 'bpjs_amount'])) {
                $payroll->total_salary = $payroll->calculateTotalSalary();
            }
        });
    }
} 