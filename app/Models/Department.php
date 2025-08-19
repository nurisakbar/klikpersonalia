<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class Department extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'name',
        'description',
        'status',
        'company_id'
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    // Relationships
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class, 'department', 'name');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeByCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    // Accessors
    public function getStatusBadgeAttribute()
    {
        return $this->status 
            ? '<span class="badge badge-success">Active</span>'
            : '<span class="badge badge-danger">Inactive</span>';
    }

    public function getEmployeeCountAttribute()
    {
        return $this->employees()->count();
    }
}
