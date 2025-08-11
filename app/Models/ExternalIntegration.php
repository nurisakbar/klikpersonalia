<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class ExternalIntegration extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'company_id',
        'integration_type',
        'name',
        'api_endpoint',
        'api_key',
        'api_secret',
        'username',
        'password',
        'is_active',
        'last_sync_at',
        'sync_frequency',
        'config_data',
        'status',
        'error_message',
        'notes'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_sync_at' => 'datetime',
        'config_data' => 'array',
        'sync_frequency' => 'integer'
    ];

    // Integration types
    const TYPE_HRIS = 'hris';
    const TYPE_ACCOUNTING = 'accounting';
    const TYPE_GOVERNMENT = 'government';
    const TYPE_BPJS = 'bpjs';
    const TYPE_TAX_OFFICE = 'tax_office';

    // Status constants
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_ERROR = 'error';
    const STATUS_SYNCING = 'syncing';

    // Sync frequency constants (in minutes)
    const FREQ_DAILY = 1440;
    const FREQ_HOURLY = 60;
    const FREQ_WEEKLY = 10080;
    const FREQ_MONTHLY = 43200;

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function syncLogs()
    {
        return $this->hasMany(IntegrationSyncLog::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('integration_type', $type);
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            self::STATUS_ACTIVE => 'success',
            self::STATUS_INACTIVE => 'secondary',
            self::STATUS_ERROR => 'danger',
            self::STATUS_SYNCING => 'warning'
        ];

        return $badges[$this->status] ?? 'secondary';
    }

    public function getTypeLabelAttribute()
    {
        $labels = [
            self::TYPE_HRIS => 'HRIS System',
            self::TYPE_ACCOUNTING => 'Accounting System',
            self::TYPE_GOVERNMENT => 'Government Portal',
            self::TYPE_BPJS => 'BPJS Online',
            self::TYPE_TAX_OFFICE => 'Tax Office'
        ];

        return $labels[$this->integration_type] ?? 'Unknown';
    }

    public function getFrequencyLabelAttribute()
    {
        $labels = [
            self::FREQ_DAILY => 'Daily',
            self::FREQ_HOURLY => 'Hourly',
            self::FREQ_WEEKLY => 'Weekly',
            self::FREQ_MONTHLY => 'Monthly'
        ];

        return $labels[$this->sync_frequency] ?? 'Custom';
    }

    public function isSyncDue()
    {
        if (!$this->last_sync_at) {
            return true;
        }

        return $this->last_sync_at->addMinutes($this->sync_frequency)->isPast();
    }

    public function maskApiKey()
    {
        if (!$this->api_key) {
            return null;
        }

        $length = strlen($this->api_key);
        if ($length <= 8) {
            return str_repeat('*', $length);
        }

        return substr($this->api_key, 0, 4) . str_repeat('*', $length - 8) . substr($this->api_key, -4);
    }

    public function maskApiSecret()
    {
        if (!$this->api_secret) {
            return null;
        }

        $length = strlen($this->api_secret);
        if ($length <= 8) {
            return str_repeat('*', $length);
        }

        return substr($this->api_secret, 0, 4) . str_repeat('*', $length - 8) . substr($this->api_secret, -4);
    }
} 