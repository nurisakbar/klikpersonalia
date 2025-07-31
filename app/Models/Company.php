<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Carbon\Carbon;

class Company extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'city',
        'province',
        'postal_code',
        'country',
        'website',
        'tax_number',
        'business_number',
        'logo',
        'status',
        'subscription_plan',
        'subscription_start',
        'subscription_end',
        'max_employees',
        'is_trial',
        'trial_ends_at',
    ];

    protected $casts = [
        'subscription_start' => 'date',
        'subscription_end' => 'date',
        'trial_ends_at' => 'date',
        'is_trial' => 'boolean',
        'max_employees' => 'integer',
    ];

    /**
     * Get the users for the company.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the employees for the company.
     */
    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    /**
     * Get the payrolls for the company.
     */
    public function payrolls()
    {
        return $this->hasMany(Payroll::class);
    }

    /**
     * Get the attendances for the company.
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Get the leaves for the company.
     */
    public function leaves()
    {
        return $this->hasMany(Leave::class);
    }

    /**
     * Get the overtimes for the company.
     */
    public function overtimes()
    {
        return $this->hasMany(Overtime::class);
    }

    /**
     * Get the company owner.
     */
    public function owner()
    {
        return $this->users()->where('is_company_owner', true)->first();
    }

    /**
     * Scope a query to only include active companies.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include trial companies.
     */
    public function scopeTrial($query)
    {
        return $query->where('is_trial', true);
    }

    /**
     * Scope a query to only include paid companies.
     */
    public function scopePaid($query)
    {
        return $query->where('is_trial', false);
    }

    /**
     * Get the company's full address.
     */
    public function getFullAddressAttribute()
    {
        return "{$this->address}, {$this->city}, {$this->province} {$this->postal_code}, {$this->country}";
    }

    /**
     * Get the company's status badge.
     */
    public function getStatusBadgeAttribute()
    {
        $statusClass = [
            'active' => 'badge badge-success',
            'inactive' => 'badge badge-warning',
            'suspended' => 'badge badge-danger'
        ];
        
        $statusText = [
            'active' => 'Aktif',
            'inactive' => 'Tidak Aktif',
            'suspended' => 'Ditangguhkan'
        ];
        
        return '<span class="' . $statusClass[$this->status] . '">' . $statusText[$this->status] . '</span>';
    }

    /**
     * Get the company's subscription badge.
     */
    public function getSubscriptionBadgeAttribute()
    {
        $planClass = [
            'free' => 'badge badge-secondary',
            'basic' => 'badge badge-info',
            'premium' => 'badge badge-warning',
            'enterprise' => 'badge badge-success'
        ];
        
        $planText = [
            'free' => 'Gratis',
            'basic' => 'Basic',
            'premium' => 'Premium',
            'enterprise' => 'Enterprise'
        ];
        
        return '<span class="' . $planClass[$this->subscription_plan] . '">' . $planText[$this->subscription_plan] . '</span>';
    }

    /**
     * Check if company is on trial.
     */
    public function isOnTrial()
    {
        return $this->is_trial && $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    /**
     * Check if company trial has expired.
     */
    public function trialExpired()
    {
        return $this->is_trial && $this->trial_ends_at && $this->trial_ends_at->isPast();
    }

    /**
     * Check if company subscription is active.
     */
    public function subscriptionActive()
    {
        if ($this->isOnTrial()) {
            return true;
        }

        return $this->subscription_end && $this->subscription_end->isFuture();
    }

    /**
     * Check if company can add more employees.
     */
    public function canAddEmployee()
    {
        $currentEmployees = $this->employees()->count();
        return $currentEmployees < $this->max_employees;
    }

    /**
     * Get remaining employee slots.
     */
    public function getRemainingEmployeeSlotsAttribute()
    {
        $currentEmployees = $this->employees()->count();
        return max(0, $this->max_employees - $currentEmployees);
    }

    /**
     * Get company statistics.
     */
    public function getStatisticsAttribute()
    {
        return [
            'total_employees' => $this->employees()->count(),
            'active_employees' => $this->employees()->where('status', 'active')->count(),
            'total_payrolls' => $this->payrolls()->count(),
            'total_attendances' => $this->attendances()->count(),
            'total_leaves' => $this->leaves()->count(),
            'total_overtimes' => $this->overtimes()->count(),
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($company) {
            if (!$company->trial_ends_at) {
                $company->trial_ends_at = Carbon::now()->addDays(30); // 30 days trial
            }
        });
    }
} 