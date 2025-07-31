<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Carbon\Carbon;

class Overtime extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'employee_id',
        'date',
        'start_time',
        'end_time',
        'hours',
        'type',
        'reason',
        'status',
        'approved_by',
        'approved_at',
        'approval_notes',
        'rate_multiplier',
        'total_amount',
        'attachment',
        'company_id',
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'hours' => 'decimal:2',
        'rate_multiplier' => 'decimal:2',
        'total_amount' => 'decimal:2',
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
        return $this->date->format('d/m/Y');
    }

    /**
     * Get the overtime's formatted start time.
     */
    public function getFormattedStartTimeAttribute()
    {
        return $this->start_time ? Carbon::parse($this->start_time)->format('H:i') : '-';
    }

    /**
     * Get the overtime's formatted end time.
     */
    public function getFormattedEndTimeAttribute()
    {
        return $this->end_time ? Carbon::parse($this->end_time)->format('H:i') : '-';
    }

    /**
     * Get the overtime's formatted approved date.
     */
    public function getFormattedApprovedDateAttribute()
    {
        return $this->approved_at ? $this->approved_at->format('d/m/Y H:i') : '-';
    }

    /**
     * Get the overtime's status badge.
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
     * Get the overtime's type badge.
     */
    public function getTypeBadgeAttribute()
    {
        $typeClass = [
            'weekday' => 'badge badge-info',
            'weekend' => 'badge badge-warning',
            'holiday' => 'badge badge-danger'
        ];
        
        $typeText = [
            'weekday' => 'Hari Kerja',
            'weekend' => 'Akhir Pekan',
            'holiday' => 'Hari Libur'
        ];
        
        return '<span class="' . $typeClass[$this->type] . '">' . $typeText[$this->type] . '</span>';
    }

    /**
     * Get the overtime's formatted total amount.
     */
    public function getFormattedTotalAmountAttribute()
    {
        return 'Rp ' . number_format($this->total_amount, 0, ',', '.');
    }

    /**
     * Calculate overtime hours.
     */
    public function calculateHours()
    {
        if ($this->start_time && $this->end_time) {
            $startTime = Carbon::parse($this->start_time);
            $endTime = Carbon::parse($this->end_time);
            $hours = $startTime->diffInHours($endTime, true);
            
            $this->update(['hours' => $hours]);
        }
    }

    /**
     * Calculate total amount based on employee salary and rate multiplier.
     */
    public function calculateTotalAmount()
    {
        if ($this->employee && $this->hours) {
            $hourlyRate = $this->employee->basic_salary / 173; // 173 working hours per month
            $totalAmount = $hourlyRate * $this->hours * $this->rate_multiplier;
            
            $this->update(['total_amount' => $totalAmount]);
        }
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

    /**
     * Get overtime summary for employee.
     */
    public static function getSummary($employeeId, $month = null, $year = null)
    {
        $query = self::where('employee_id', $employeeId);
        
        if ($month && $year) {
            $query->whereYear('date', $year)->whereMonth('date', $month);
        }
        
        return [
            'total_overtimes' => $query->count(),
            'pending_overtimes' => $query->where('status', 'pending')->count(),
            'approved_overtimes' => $query->where('status', 'approved')->count(),
            'rejected_overtimes' => $query->where('status', 'rejected')->count(),
            'total_hours' => $query->where('status', 'approved')->sum('hours'),
            'total_amount' => $query->where('status', 'approved')->sum('total_amount'),
            'weekday_hours' => $query->where('type', 'weekday')->where('status', 'approved')->sum('hours'),
            'weekend_hours' => $query->where('type', 'weekend')->where('status', 'approved')->sum('hours'),
            'holiday_hours' => $query->where('type', 'holiday')->where('status', 'approved')->sum('hours'),
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($overtime) {
            if (!$overtime->rate_multiplier) {
                $overtime->rate_multiplier = self::getDefaultRateMultiplier($overtime->type);
            }
        });

        static::saving(function ($overtime) {
            $overtime->calculateHours();
            $overtime->calculateTotalAmount();
        });
    }

    /**
     * Get default rate multiplier based on type.
     */
    private static function getDefaultRateMultiplier($type)
    {
        $rates = [
            'weekday' => 1.5,
            'weekend' => 2.0,
            'holiday' => 3.0
        ];
        
        return $rates[$type] ?? 1.5;
    }
} 