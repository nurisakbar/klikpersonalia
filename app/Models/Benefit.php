<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class Benefit extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'company_id', 'name', 'description', 'type', 'amount', 'frequency',
        'is_taxable', 'is_active', 'effective_date', 'expiry_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_taxable' => 'boolean',
        'is_active' => 'boolean',
        'effective_date' => 'date',
        'expiry_date' => 'date',
    ];

    // Relationships
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'employee_benefits')
                    ->withPivot(['amount', 'start_date', 'end_date', 'status', 'notes'])
                    ->withTimestamps();
    }

    public function employeeBenefits()
    {
        return $this->hasMany(EmployeeBenefit::class);
    }

    // Scopes
    public function scopeCurrentCompany($query)
    {
        return $query->where('company_id', auth()->user()->company_id);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now());
    }

    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expiry_date')
              ->orWhere('expiry_date', '>=', now());
        });
    }

    // Accessors
    public function getTypeBadgeAttribute()
    {
        $badges = [
            'health' => 'badge-success',
            'insurance' => 'badge-info',
            'allowance' => 'badge-warning',
            'bonus' => 'badge-primary',
            'other' => 'badge-secondary',
        ];

        return $badges[$this->type] ?? 'badge-secondary';
    }

    public function getFrequencyBadgeAttribute()
    {
        $badges = [
            'monthly' => 'badge-info',
            'quarterly' => 'badge-warning',
            'yearly' => 'badge-success',
            'one_time' => 'badge-secondary',
        ];

        return $badges[$this->frequency] ?? 'badge-secondary';
    }

    public function getStatusBadgeAttribute()
    {
        if (!$this->is_active) {
            return 'badge-danger';
        }

        if ($this->expiry_date && $this->expiry_date < now()) {
            return 'badge-warning';
        }

        return 'badge-success';
    }

    public function getFormattedAmountAttribute()
    {
        return $this->amount ? 'Rp ' . number_format($this->amount, 0, ',', '.') : 'N/A';
    }

    public function getFormattedEffectiveDateAttribute()
    {
        return $this->effective_date ? $this->effective_date->format('d M Y') : 'N/A';
    }

    public function getFormattedExpiryDateAttribute()
    {
        return $this->expiry_date ? $this->expiry_date->format('d M Y') : 'N/A';
    }

    // Methods
    public function isExpired()
    {
        return $this->expiry_date && $this->expiry_date < now();
    }

    public function isEffective()
    {
        return !$this->effective_date || $this->effective_date <= now();
    }

    public function getActiveEmployeeCount()
    {
        return $this->employeeBenefits()
                    ->where('status', 'active')
                    ->count();
    }

    public function getTotalCost()
    {
        return $this->employeeBenefits()
                    ->where('status', 'active')
                    ->sum('amount');
    }
} 