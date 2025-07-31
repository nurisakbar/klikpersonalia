<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class EmployeeBenefit extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'employee_id', 'benefit_id', 'company_id', 'amount', 'start_date',
        'end_date', 'status', 'notes', 'assigned_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    // Relationships
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function benefit()
    {
        return $this->belongsTo(Benefit::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    // Scopes
    public function scopeCurrentCompany($query)
    {
        return $query->where('company_id', auth()->user()->company_id);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeByBenefit($query, $benefitId)
    {
        return $query->where('benefit_id', $benefitId);
    }

    public function scopeExpired($query)
    {
        return $query->where('end_date', '<', now());
    }

    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('end_date')
              ->orWhere('end_date', '>=', now());
        });
    }

    // Accessors
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'active' => 'badge-success',
            'inactive' => 'badge-secondary',
            'pending' => 'badge-warning',
            'expired' => 'badge-danger',
        ];

        return $badges[$this->status] ?? 'badge-secondary';
    }

    public function getFormattedAmountAttribute()
    {
        return $this->amount ? 'Rp ' . number_format($this->amount, 0, ',', '.') : 'N/A';
    }

    public function getFormattedStartDateAttribute()
    {
        return $this->start_date ? $this->start_date->format('d M Y') : 'N/A';
    }

    public function getFormattedEndDateAttribute()
    {
        return $this->end_date ? $this->end_date->format('d M Y') : 'N/A';
    }

    // Methods
    public function isExpired()
    {
        return $this->end_date && $this->end_date < now();
    }

    public function isActive()
    {
        return $this->status === 'active' && !$this->isExpired();
    }

    public function getRemainingDays()
    {
        if (!$this->end_date) {
            return null;
        }

        return max(0, now()->diffInDays($this->end_date, false));
    }

    public function getDaysElapsed()
    {
        return now()->diffInDays($this->start_date);
    }

    public function getTotalDays()
    {
        if (!$this->end_date) {
            return null;
        }

        return $this->start_date->diffInDays($this->end_date);
    }
} 