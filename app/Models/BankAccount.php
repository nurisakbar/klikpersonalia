<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class BankAccount extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'company_id',
        'employee_id',
        'bank_name',
        'account_number',
        'account_holder_name',
        'branch_code',
        'swift_code',
        'account_type',
        'is_active',
        'is_primary',
        'notes'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_primary' => 'boolean',
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

    public function salaryTransfers()
    {
        return $this->hasMany(SalaryTransfer::class);
    }

    // Constants
    const ACCOUNT_TYPES = [
        'savings' => 'Savings Account',
        'current' => 'Current Account',
        'salary' => 'Salary Account',
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    // Methods
    public function getFormattedAccountNumberAttribute()
    {
        return $this->maskAccountNumber($this->account_number);
    }

    public function maskAccountNumber($accountNumber)
    {
        if (strlen($accountNumber) <= 4) {
            return $accountNumber;
        }
        
        return substr($accountNumber, 0, 4) . str_repeat('*', strlen($accountNumber) - 8) . substr($accountNumber, -4);
    }

    public function getFullBankInfoAttribute()
    {
        return "{$this->bank_name} - {$this->account_holder_name} ({$this->getFormattedAccountNumberAttribute()})";
    }
} 