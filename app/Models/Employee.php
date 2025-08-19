<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class Employee extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'employee_id',
        'name',
        'email',
        'phone',
        'address',
        'join_date',
        'department',
        'position',
        'ptkp_status',
        'tax_notes',
        'bpjs_kesehatan_number',
        'bpjs_ketenagakerjaan_number',
        'bpjs_kesehatan_active',
        'bpjs_ketenagakerjaan_active',
        'bpjs_effective_date',
        'bpjs_notes',
        'basic_salary',
        'status',
        'emergency_contact',
        'bank_name',
        'bank_account',
        'company_id',
        'user_id',
    ];

    protected $casts = [
        'join_date' => 'date',
        'basic_salary' => 'decimal:2',
        'bpjs_kesehatan_active' => 'boolean',
        'bpjs_ketenagakerjaan_active' => 'boolean',
        'bpjs_effective_date' => 'date',
    ];

    /**
     * Get the company that owns the employee.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the user associated with the employee.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the department that owns the employee.
     */
    public function departmentRelation()
    {
        return $this->belongsTo(Department::class, 'department', 'name');
    }

    /**
     * Get the position that owns the employee.
     */
    public function positionRelation()
    {
        return $this->belongsTo(Position::class, 'position', 'name');
    }

    /**
     * Get the payrolls for the employee.
     */
    public function payrolls()
    {
        return $this->hasMany(Payroll::class);
    }

    /**
     * Get the attendances for the employee.
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Get the leaves for the employee.
     */
    public function leaves()
    {
        return $this->hasMany(Leave::class);
    }

    /**
     * Get the taxes for the employee.
     */
    public function taxes()
    {
        return $this->hasMany(Tax::class);
    }

    /**
     * Get the BPJS records for the employee.
     */
    public function bpjs()
    {
        return $this->hasMany(Bpjs::class);
    }

    /**
     * Scope a query to only include active employees.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include employees from current company.
     */
    public function scopeCurrentCompany($query)
    {
        if (auth()->check() && auth()->user()->company_id) {
            return $query->where('company_id', auth()->user()->company_id);
        }
        return $query;
    }

    /**
     * Get the employee's full name.
     */
    public function getFullNameAttribute()
    {
        return $this->name;
    }

    /**
     * Get the employee's formatted salary.
     */
    public function getFormattedSalaryAttribute()
    {
        return 'Rp ' . number_format($this->basic_salary, 0, ',', '.');
    }

    /**
     * Get the employee's formatted join date.
     */
    public function getFormattedJoinDateAttribute()
    {
        return $this->join_date->format('d/m/Y');
    }

    /**
     * Get the employee's status badge.
     */
    public function getStatusBadgeAttribute()
    {
        $statusClass = [
            'active' => 'badge badge-success',
            'inactive' => 'badge badge-warning',
            'terminated' => 'badge badge-danger'
        ];
        
        $statusText = [
            'active' => 'Aktif',
            'inactive' => 'Tidak Aktif',
            'terminated' => 'Berhenti'
        ];
        
        return '<span class="' . $statusClass[$this->status] . '">' . $statusText[$this->status] . '</span>';
    }
} 