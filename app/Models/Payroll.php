<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class Payroll extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'employee_id',
        'company_id',
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
        'generated_by',
        'generated_at',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'paid_by',
        'paid_at',
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
        'generated_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'paid_at' => 'datetime',
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
     * Get the user who generated the payroll.
     */
    public function generatedBy()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    /**
     * Get the user who approved the payroll.
     */
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the user who rejected the payroll.
     */
    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    /**
     * Get the user who updated the payroll.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the taxes for the payroll.
     */
    public function taxes()
    {
        return $this->hasMany(Tax::class);
    }

    /**
     * Get the BPJS records for the payroll.
     */
    public function bpjs()
    {
        return $this->hasMany(Bpjs::class);
    }

    /**
     * Scope a query to only include pending payrolls.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include approved payrolls.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include rejected payrolls.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
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
     * Get the payroll's formatted period.
     */
    public function getFormattedPeriodAttribute()
    {
        if (!$this->period) {
            return '-';
        }

        $parts = explode('-', $this->period);
        if (count($parts) !== 2) {
            return $this->period;
        }

        $year = $parts[0];
        $month = (int) $parts[1];

        $monthNames = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
        ];

        return $monthNames[$month] . ' ' . $year;
    }

    /**
     * Get the payroll's status badge.
     */
    public function getStatusBadgeAttribute()
    {
        $statusClass = [
            'draft' => 'badge badge-secondary',
            'pending' => 'badge badge-warning',
            'approved' => 'badge badge-success',
            'paid' => 'badge badge-info',
            'rejected' => 'badge badge-danger'
        ];
        
        $statusText = [
            'draft' => 'Draft',
            'pending' => 'Pending',
            'approved' => 'Approved',
            'paid' => 'Paid',
            'rejected' => 'Rejected'
        ];
        
        return '<span class="' . ($statusClass[$this->status] ?? 'badge badge-secondary') . '">' . ($statusText[$this->status] ?? 'Unknown') . '</span>';
    }

    /**
     * Check if payroll is pending.
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if payroll is approved.
     */
    public function isApproved()
    {
        return $this->status === 'approved';
    }

    /**
     * Check if payroll is paid.
     */
    public function isPaid()
    {
        return $this->status === 'paid';
    }

    /**
     * Check if payroll is rejected.
     */
    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    /**
     * Check if payroll is draft.
     */
    public function isDraft()
    {
        return $this->status === 'draft';
    }

    /**
     * Approve the payroll.
     */
    public function approve($approvedBy)
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $approvedBy,
            'approved_at' => now(),
        ]);
    }

    /**
     * Reject the payroll.
     */
    public function reject($rejectedBy, $reason = null)
    {
        $this->update([
            'status' => 'rejected',
            'rejected_by' => $rejectedBy,
            'rejected_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }
} 