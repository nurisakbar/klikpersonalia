<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class DataArchive extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'company_id',
        'archive_type',
        'table_name',
        'record_id',
        'original_data',
        'archived_data',
        'archive_date',
        'retention_period',
        'expiry_date',
        'archive_reason',
        'archived_by',
        'status',
        'file_path',
        'file_size',
        'checksum'
    ];

    protected $casts = [
        'original_data' => 'array',
        'archived_data' => 'array',
        'archive_date' => 'datetime',
        'expiry_date' => 'datetime',
        'file_size' => 'integer'
    ];

    // Archive types
    const TYPE_EMPLOYEE = 'employee';
    const TYPE_PAYROLL = 'payroll';
    const TYPE_ATTENDANCE = 'attendance';
    const TYPE_LEAVE = 'leave';
    const TYPE_OVERTIME = 'overtime';
    const TYPE_TAX = 'tax';
    const TYPE_BPJS = 'bpjs';
    const TYPE_BENEFIT = 'benefit';
    const TYPE_PERFORMANCE = 'performance';
    const TYPE_COMPLIANCE = 'compliance';
    const TYPE_AUDIT = 'audit';

    // Status constants
    const STATUS_ACTIVE = 'active';
    const STATUS_EXPIRED = 'expired';
    const STATUS_DELETED = 'deleted';
    const STATUS_RESTORED = 'restored';

    // Retention periods (in days)
    const RETENTION_1_YEAR = 365;
    const RETENTION_3_YEARS = 1095;
    const RETENTION_5_YEARS = 1825;
    const RETENTION_7_YEARS = 2555;
    const RETENTION_10_YEARS = 3650;
    const RETENTION_PERMANENT = -1;

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function archivedBy()
    {
        return $this->belongsTo(User::class, 'archived_by');
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeExpired($query)
    {
        return $query->where('status', self::STATUS_EXPIRED);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('archive_type', $type);
    }

    public function scopeByTable($query, $tableName)
    {
        return $query->where('table_name', $tableName);
    }

    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->where('expiry_date', '<=', now()->addDays($days))
                    ->where('status', self::STATUS_ACTIVE);
    }

    public function getTypeLabelAttribute()
    {
        $labels = [
            self::TYPE_EMPLOYEE => 'Employee Data',
            self::TYPE_PAYROLL => 'Payroll Data',
            self::TYPE_ATTENDANCE => 'Attendance Data',
            self::TYPE_LEAVE => 'Leave Data',
            self::TYPE_OVERTIME => 'Overtime Data',
            self::TYPE_TAX => 'Tax Data',
            self::TYPE_BPJS => 'BPJS Data',
            self::TYPE_BENEFIT => 'Benefit Data',
            self::TYPE_PERFORMANCE => 'Performance Data',
            self::TYPE_COMPLIANCE => 'Compliance Data',
            self::TYPE_AUDIT => 'Audit Data'
        ];

        return $labels[$this->archive_type] ?? 'Unknown';
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            self::STATUS_ACTIVE => 'success',
            self::STATUS_EXPIRED => 'warning',
            self::STATUS_DELETED => 'danger',
            self::STATUS_RESTORED => 'info'
        ];

        return $badges[$this->status] ?? 'secondary';
    }

    public function getStatusLabelAttribute()
    {
        $labels = [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_EXPIRED => 'Expired',
            self::STATUS_DELETED => 'Deleted',
            self::STATUS_RESTORED => 'Restored'
        ];

        return $labels[$this->status] ?? 'Unknown';
    }

    public function getRetentionPeriodLabelAttribute()
    {
        $labels = [
            self::RETENTION_1_YEAR => '1 Year',
            self::RETENTION_3_YEARS => '3 Years',
            self::RETENTION_5_YEARS => '5 Years',
            self::RETENTION_7_YEARS => '7 Years',
            self::RETENTION_10_YEARS => '10 Years',
            self::RETENTION_PERMANENT => 'Permanent'
        ];

        return $labels[$this->retention_period] ?? 'Unknown';
    }

    public function isExpired()
    {
        if ($this->retention_period === self::RETENTION_PERMANENT) {
            return false;
        }

        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function isExpiringSoon($days = 30)
    {
        if ($this->retention_period === self::RETENTION_PERMANENT) {
            return false;
        }

        return $this->expiry_date && $this->expiry_date->diffInDays(now()) <= $days;
    }

    public function getDaysUntilExpiryAttribute()
    {
        if ($this->retention_period === self::RETENTION_PERMANENT) {
            return null;
        }

        if (!$this->expiry_date) {
            return null;
        }

        return $this->expiry_date->diffInDays(now(), false);
    }

    public function getFileSizeFormattedAttribute()
    {
        if (!$this->file_size) {
            return 'N/A';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->file_size;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2) . ' ' . $units[$unit];
    }

    public function calculateExpiryDate()
    {
        if ($this->retention_period === self::RETENTION_PERMANENT) {
            return null;
        }

        return $this->archive_date->addDays($this->retention_period);
    }

    public function markAsExpired()
    {
        $this->update(['status' => self::STATUS_EXPIRED]);
    }

    public function markAsDeleted()
    {
        $this->update(['status' => self::STATUS_DELETED]);
    }

    public function markAsRestored()
    {
        $this->update(['status' => self::STATUS_RESTORED]);
    }

    public function getArchiveReasonLabelAttribute()
    {
        $reasons = [
            'retention_policy' => 'Retention Policy',
            'data_cleanup' => 'Data Cleanup',
            'compliance' => 'Compliance Requirement',
            'storage_optimization' => 'Storage Optimization',
            'manual_archive' => 'Manual Archive',
            'system_cleanup' => 'System Cleanup'
        ];

        return $reasons[$this->archive_reason] ?? 'Unknown';
    }
} 