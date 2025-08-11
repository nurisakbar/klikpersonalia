<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'employee_id',
        'date',
        'check_in',
        'check_out',
        'total_hours',
        'overtime_hours',
        'status',
        'notes',
        'check_in_location',
        'check_out_location',
        'check_in_ip',
        'check_out_ip',
        'check_in_device',
        'check_out_device',
        'company_id',
    ];

    protected $casts = [
        'date' => 'date',
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'total_hours' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
    ];

    /**
     * Get the company that owns the attendance.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the employee that owns the attendance.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Scope a query to only include today's attendance.
     */
    public function scopeToday($query)
    {
        return $query->where('date', Carbon::today());
    }

    /**
     * Scope a query to only include this month's attendance.
     */
    public function scopeThisMonth($query)
    {
        return $query->whereBetween('date', [
            Carbon::now()->startOfMonth(),
            Carbon::now()->endOfMonth()
        ]);
    }

    /**
     * Scope a query to only include present attendance.
     */
    public function scopePresent($query)
    {
        return $query->where('status', 'present');
    }

    /**
     * Scope a query to only include absent attendance.
     */
    public function scopeAbsent($query)
    {
        return $query->where('status', 'absent');
    }

    /**
     * Scope a query to only include late attendance.
     */
    public function scopeLate($query)
    {
        return $query->where('status', 'late');
    }

    /**
     * Scope a query to only include attendance from current company.
     */
    public function scopeCurrentCompany($query)
    {
        if (auth()->check() && auth()->user()->company_id) {
            return $query->where('company_id', auth()->user()->company_id);
        }
        return $query;
    }

    /**
     * Get the attendance's formatted date.
     */
    public function getFormattedDateAttribute()
    {
        return $this->date->format('d/m/Y');
    }

    /**
     * Get the attendance's formatted check in time.
     */
    public function getFormattedCheckInAttribute()
    {
        return $this->check_in ? Carbon::parse($this->check_in)->format('H:i') : '-';
    }

    /**
     * Get the attendance's formatted check out time.
     */
    public function getFormattedCheckOutAttribute()
    {
        return $this->check_out ? Carbon::parse($this->check_out)->format('H:i') : '-';
    }

    /**
     * Get the attendance's status badge.
     */
    public function getStatusBadgeAttribute()
    {
        $statusClass = [
            'present' => 'badge badge-success',
            'absent' => 'badge badge-danger',
            'late' => 'badge badge-warning',
            'half_day' => 'badge badge-info',
            'leave' => 'badge badge-secondary',
            'holiday' => 'badge badge-primary'
        ];
        
        $statusText = [
            'present' => 'Hadir',
            'absent' => 'Tidak Hadir',
            'late' => 'Terlambat',
            'half_day' => 'Setengah Hari',
            'leave' => 'Cuti',
            'holiday' => 'Libur'
        ];
        
        return '<span class="' . $statusClass[$this->status] . '">' . $statusText[$this->status] . '</span>';
    }

    /**
     * Calculate total working hours.
     */
    public function calculateTotalHours()
    {
        if ($this->check_in && $this->check_out) {
            $checkIn = Carbon::parse($this->check_in);
            $checkOut = Carbon::parse($this->check_out);
            $totalHours = $checkIn->diffInHours($checkOut, true);
            
            // Calculate overtime (more than 8 hours)
            $regularHours = 8;
            $overtimeHours = max(0, $totalHours - $regularHours);
            
            $this->update([
                'total_hours' => $totalHours,
                'overtime_hours' => $overtimeHours
            ]);
        }
    }

    /**
     * Check if employee is late.
     */
    public function isLate()
    {
        if ($this->check_in) {
            $checkInTime = Carbon::parse($this->check_in);
            $startTime = Carbon::parse('08:00'); // Company start time
            
            return $checkInTime->gt($startTime);
        }
        
        return false;
    }

    /**
     * Get attendance summary for employee.
     */
    public static function getSummary($employeeId, $month = null, $year = null)
    {
        $query = self::where('employee_id', $employeeId);
        
        if ($month && $year) {
            $query->whereYear('date', $year)->whereMonth('date', $month);
        }
        
        return [
            'total_days' => $query->count(),
            'present_days' => $query->where('status', 'present')->count(),
            'absent_days' => $query->where('status', 'absent')->count(),
            'late_days' => $query->where('status', 'late')->count(),
            'leave_days' => $query->where('status', 'leave')->count(),
            'total_hours' => $query->sum('total_hours'),
            'overtime_hours' => $query->sum('overtime_hours'),
        ];
    }
} 