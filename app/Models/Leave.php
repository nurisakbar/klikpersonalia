<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;
use Carbon\Carbon;

class Leave extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'employee_id',
        'leave_type',
        'start_date',
        'end_date',
        'total_days',
        'reason',
        'status',
        'approved_by',
        'approved_at',
        'approval_notes',
        'attachment',
        'company_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'total_days' => 'integer',
        'approved_at' => 'datetime',
    ];

    /**
     * Get the company that owns the leave.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the employee that owns the leave.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the user who approved the leave.
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope a query to only include pending leaves.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include approved leaves.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include rejected leaves.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope a query to only include this month's leaves.
     */
    public function scopeThisMonth($query)
    {
        return $query->whereBetween('start_date', [
            Carbon::now()->startOfMonth(),
            Carbon::now()->endOfMonth()
        ]);
    }

    /**
     * Scope a query to only include leaves from current company.
     */
    public function scopeCurrentCompany($query)
    {
        if (auth()->check() && auth()->user()->company_id) {
            return $query->where('company_id', auth()->user()->company_id);
        }
        return $query;
    }

    /**
     * Get the leave's formatted start date.
     */
    public function getFormattedStartDateAttribute()
    {
        return $this->start_date->format('d/m/Y');
    }

    /**
     * Get the leave's formatted end date.
     */
    public function getFormattedEndDateAttribute()
    {
        return $this->end_date->format('d/m/Y');
    }

    /**
     * Get the leave's formatted approved date.
     */
    public function getFormattedApprovedDateAttribute()
    {
        return $this->approved_at ? $this->approved_at->format('d/m/Y H:i') : '-';
    }

    /**
     * Get the leave's status badge.
     */
    public function getStatusBadgeAttribute()
    {
        $statusClass = [
            'pending' => 'badge badge-warning',
            'approved' => 'badge badge-success',
            'rejected' => 'badge badge-danger'
        ];
        
        $statusText = [
            'pending' => 'Menunggu',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak'
        ];
        
        return '<span class="' . $statusClass[$this->status] . '">' . $statusText[$this->status] . '</span>';
    }

    /**
     * Get the leave's type badge.
     */
    public function getTypeBadgeAttribute()
    {
        $typeClass = [
            'annual' => 'badge badge-info',
            'sick' => 'badge badge-danger',
            'maternity' => 'badge badge-primary',
            'paternity' => 'badge badge-secondary',
            'other' => 'badge badge-warning'
        ];
        
        $typeText = [
            'annual' => 'Cuti Tahunan',
            'sick' => 'Cuti Sakit',
            'maternity' => 'Cuti Melahirkan',
            'paternity' => 'Cuti Melahirkan (Suami)',
            'other' => 'Cuti Lainnya'
        ];
        
        return '<span class="' . $typeClass[$this->leave_type] . '">' . $typeText[$this->leave_type] . '</span>';
    }

    /**
     * Calculate total days between start and end date.
     */
    public function calculateTotalDays()
    {
        $startDate = Carbon::parse($this->start_date);
        $endDate = Carbon::parse($this->end_date);
        
        // Exclude weekends
        $totalDays = 0;
        $currentDate = $startDate->copy();
        
        while ($currentDate->lte($endDate)) {
            if (!$currentDate->isWeekend()) {
                $totalDays++;
            }
            $currentDate->addDay();
        }
        
        return $totalDays;
    }

    /**
     * Approve the leave request.
     */
    public function approve($approvedBy, $notes = null)
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $approvedBy,
            'approved_at' => now(),
            'approval_notes' => $notes
        ]);
    }

    /**
     * Reject the leave request.
     */
    public function reject($approvedBy, $notes = null)
    {
        $this->update([
            'status' => 'rejected',
            'approved_by' => $approvedBy,
            'approved_at' => now(),
            'approval_notes' => $notes
        ]);
    }

    /**
     * Check if leave is pending.
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if leave is approved.
     */
    public function isApproved()
    {
        return $this->status === 'approved';
    }

    /**
     * Check if leave is rejected.
     */
    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    /**
     * Get leave summary for employee.
     */
    public static function getSummary($employeeId, $year = null)
    {
        $query = self::where('employee_id', $employeeId);
        
        if ($year) {
            $query->whereYear('start_date', $year);
        }
        
        return [
            'total_leaves' => $query->count(),
            'pending_leaves' => $query->where('status', 'pending')->count(),
            'approved_leaves' => $query->where('status', 'approved')->count(),
            'rejected_leaves' => $query->where('status', 'rejected')->count(),
            'total_days' => $query->where('status', 'approved')->sum('total_days'),
            'annual_leaves' => $query->where('leave_type', 'annual')->where('status', 'approved')->sum('total_days'),
            'sick_leaves' => $query->where('leave_type', 'sick')->where('status', 'approved')->sum('total_days'),
        ];
    }
} 