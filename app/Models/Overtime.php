<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;
use Carbon\Carbon;

class Overtime extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'employee_id',
        'overtime_type',
        'date',
        'start_time',
        'end_time',
        'total_hours',
        'reason',
        'status',
        'approved_by',
        'approved_at',
        'approval_notes',
        'attachment',
        'company_id',
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'string',
        'end_time' => 'string',
        'total_hours' => 'integer',
        'approved_at' => 'datetime',
    ];



    /**
     * Get the company that owns the overtime.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the employee that owns the overtime.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the user who approved the overtime.
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope a query to only include pending overtimes.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include approved overtimes.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include rejected overtimes.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope a query to only include cancelled overtimes.
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Scope a query to only include this month's overtimes.
     */
    public function scopeThisMonth($query)
    {
        return $query->whereBetween('date', [
            Carbon::now()->startOfMonth(),
            Carbon::now()->endOfMonth()
        ]);
    }

    /**
     * Scope a query to only include overtimes from current company.
     */
    public function scopeCurrentCompany($query)
    {
        if (auth()->check() && auth()->user()->company_id) {
            return $query->where('company_id', auth()->user()->company_id);
        }
        return $query;
    }

    /**
     * Get the overtime's formatted date.
     */
    public function getFormattedDateAttribute()
    {
        if (!$this->date) {
            return '-';
        }
        
        // Ensure date is a Carbon instance
        if (is_string($this->date)) {
            return \Carbon\Carbon::parse($this->date)->format('d/m/Y');
        }
        
        return $this->date->format('d/m/Y');
    }

    /**
     * Get the overtime's formatted start time.
     */
    public function getFormattedStartTimeAttribute()
    {
        if (!$this->start_time) {
            return '';
        }
        
        // Pastikan format waktu selalu H:i
        try {
            $time = \Carbon\Carbon::parse($this->start_time);
            return $time->format('H:i');
        } catch (\Exception $e) {
            return $this->start_time;
        }
    }

    /**
     * Get the overtime's formatted end time.
     */
    public function getFormattedEndTimeAttribute()
    {
        if (!$this->end_time) {
            return '';
        }
        
        // Pastikan format waktu selalu H:i
        try {
            $time = \Carbon\Carbon::parse($this->end_time);
            return $time->format('H:i');
        } catch (\Exception $e) {
            return $this->end_time;
        }
    }

    /**
     * Mutator untuk memastikan format waktu yang konsisten
     */
    public function setStartTimeAttribute($value)
    {
        if ($value) {
            try {
                $time = \Carbon\Carbon::parse($value);
                $this->attributes['start_time'] = $time->format('H:i:s');
            } catch (\Exception $e) {
                $this->attributes['start_time'] = $value;
            }
        } else {
            $this->attributes['start_time'] = null;
        }
    }

    public function setEndTimeAttribute($value)
    {
        if ($value) {
            try {
                $time = \Carbon\Carbon::parse($value);
                $this->attributes['end_time'] = $time->format('H:i:s');
            } catch (\Exception $e) {
                $this->attributes['end_time'] = $value;
            }
        } else {
            $this->attributes['end_time'] = null;
        }
    }

    /**
     * Get the overtime's formatted approved date.
     */
    public function getFormattedApprovedDateAttribute()
    {
        return $this->approved_at ? $this->approved_at->format('d/m/Y H:i') : '-';
    }

    /**
     * Get the overtime's attachment path.
     */
    public function getAttachmentPathAttribute()
    {
        return $this->attachment;
    }

    /**
     * Get the overtime's status badge.
     */
    public function getStatusBadgeAttribute()
    {
        $statusClass = [
            'pending' => 'badge badge-warning',
            'approved' => 'badge badge-success',
            'rejected' => 'badge badge-danger',
            'cancelled' => 'badge badge-secondary'
        ];
        
        $statusText = [
            'pending' => 'Menunggu',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'cancelled' => 'Dibatalkan'
        ];
        
        return '<span class="' . $statusClass[$this->status] . '">' . $statusText[$this->status] . '</span>';
    }

    /**
     * Get the overtime's type badge.
     */
    public function getTypeBadgeAttribute()
    {
        $typeClass = [
            'regular' => 'badge badge-info',
            'holiday' => 'badge badge-danger',
            'weekend' => 'badge badge-warning',
            'emergency' => 'badge badge-dark'
        ];
        
        $typeText = [
            'regular' => 'Regular Overtime',
            'holiday' => 'Holiday Overtime',
            'weekend' => 'Weekend Overtime',
            'emergency' => 'Emergency Overtime'
        ];
        
        return '<span class="' . $typeClass[$this->overtime_type] . '">' . $typeText[$this->overtime_type] . '</span>';
    }

    /**
     * Approve the overtime request.
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
     * Reject the overtime request.
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
     * Check if overtime is pending.
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if overtime is approved.
     */
    public function isApproved()
    {
        return $this->status === 'approved';
    }

    /**
     * Check if overtime is rejected.
     */
    public function isRejected()
    {
        return $this->status === 'rejected';
    }
} 