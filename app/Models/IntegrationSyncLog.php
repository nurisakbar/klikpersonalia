<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class IntegrationSyncLog extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'company_id',
        'external_integration_id',
        'sync_type',
        'status',
        'started_at',
        'completed_at',
        'records_processed',
        'records_success',
        'records_failed',
        'error_message',
        'response_data',
        'sync_duration',
        'triggered_by'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'response_data' => 'array',
        'sync_duration' => 'integer'
    ];

    // Sync types
    const SYNC_EMPLOYEE = 'employee';
    const SYNC_PAYROLL = 'payroll';
    const SYNC_ATTENDANCE = 'attendance';
    const SYNC_TAX = 'tax';
    const SYNC_BPJS = 'bpjs';
    const SYNC_LEAVE = 'leave';
    const SYNC_OVERTIME = 'overtime';

    // Status constants
    const STATUS_RUNNING = 'running';
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILED = 'failed';
    const STATUS_PARTIAL = 'partial';

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function externalIntegration()
    {
        return $this->belongsTo(ExternalIntegration::class);
    }

    public function triggeredByUser()
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('sync_type', $type);
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('started_at', '>=', now()->subDays($days));
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            self::STATUS_RUNNING => 'warning',
            self::STATUS_SUCCESS => 'success',
            self::STATUS_FAILED => 'danger',
            self::STATUS_PARTIAL => 'info'
        ];

        return $badges[$this->status] ?? 'secondary';
    }

    public function getSyncTypeLabelAttribute()
    {
        $labels = [
            self::SYNC_EMPLOYEE => 'Employee Data',
            self::SYNC_PAYROLL => 'Payroll Data',
            self::SYNC_ATTENDANCE => 'Attendance Data',
            self::SYNC_TAX => 'Tax Data',
            self::SYNC_BPJS => 'BPJS Data',
            self::SYNC_LEAVE => 'Leave Data',
            self::SYNC_OVERTIME => 'Overtime Data'
        ];

        return $labels[$this->sync_type] ?? 'Unknown';
    }

    public function getDurationFormattedAttribute()
    {
        if (!$this->sync_duration) {
            return 'N/A';
        }

        $minutes = floor($this->sync_duration / 60);
        $seconds = $this->sync_duration % 60;

        if ($minutes > 0) {
            return "{$minutes}m {$seconds}s";
        }

        return "{$seconds}s";
    }

    public function getSuccessRateAttribute()
    {
        if ($this->records_processed == 0) {
            return 0;
        }

        return round(($this->records_success / $this->records_processed) * 100, 2);
    }

    public function isRunning()
    {
        return $this->status === self::STATUS_RUNNING;
    }

    public function isCompleted()
    {
        return in_array($this->status, [self::STATUS_SUCCESS, self::STATUS_FAILED, self::STATUS_PARTIAL]);
    }

    public function markAsCompleted($status, $successCount = 0, $failedCount = 0, $errorMessage = null)
    {
        $this->update([
            'status' => $status,
            'completed_at' => now(),
            'records_success' => $successCount,
            'records_failed' => $failedCount,
            'error_message' => $errorMessage,
            'sync_duration' => $this->started_at->diffInSeconds(now())
        ]);
    }
} 