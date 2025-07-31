<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'company_id',
        'role',
        'is_company_owner',
        'phone',
        'position',
        'department',
        'status',
        'last_login_at',
        'last_login_ip',
        'last_login_device',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
            'is_company_owner' => 'boolean',
        ];
    }

    /**
     * Get the company that owns the user.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the employees for the user.
     */
    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    /**
     * Check if user is company owner.
     */
    public function isCompanyOwner()
    {
        return $this->is_company_owner;
    }

    /**
     * Check if user is admin.
     */
    public function isAdmin()
    {
        return in_array($this->role, ['super_admin', 'admin']);
    }

    /**
     * Check if user is HR.
     */
    public function isHR()
    {
        return in_array($this->role, ['super_admin', 'admin', 'hr']);
    }

    /**
     * Check if user is manager.
     */
    public function isManager()
    {
        return in_array($this->role, ['super_admin', 'admin', 'hr', 'manager']);
    }

    /**
     * Get user's role badge.
     */
    public function getRoleBadgeAttribute()
    {
        $roleClass = [
            'super_admin' => 'badge badge-danger',
            'admin' => 'badge badge-warning',
            'hr' => 'badge badge-info',
            'manager' => 'badge badge-primary',
            'employee' => 'badge badge-secondary'
        ];
        
        $roleText = [
            'super_admin' => 'Super Admin',
            'admin' => 'Admin',
            'hr' => 'HR',
            'manager' => 'Manager',
            'employee' => 'Karyawan'
        ];
        
        return '<span class="' . $roleClass[$this->role] . '">' . $roleText[$this->role] . '</span>';
    }

    /**
     * Get user's status badge.
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
}
