<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class ComplianceAuditLog extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'company_id',
        'compliance_id',
        'audit_type',
        'audit_date',
        'auditor_id',
        'findings',
        'recommendations',
        'compliance_score',
        'risk_assessment',
        'corrective_actions',
        'follow_up_date',
        'status',
        'notes'
    ];

    protected $casts = [
        'audit_date' => 'date',
        'follow_up_date' => 'date',
        'compliance_score' => 'decimal:2',
        'findings' => 'array',
        'recommendations' => 'array',
        'risk_assessment' => 'array',
        'corrective_actions' => 'array'
    ];

    // Audit types
    const TYPE_INTERNAL = 'internal';
    const TYPE_EXTERNAL = 'external';
    const TYPE_REGULATORY = 'regulatory';
    const TYPE_SELF_ASSESSMENT = 'self_assessment';

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_OVERDUE = 'overdue';

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function compliance()
    {
        return $this->belongsTo(Compliance::class);
    }

    public function auditor()
    {
        return $this->belongsTo(User::class, 'auditor_id');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('audit_type', $type);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('audit_date', '>=', now()->subDays($days));
    }

    public function getTypeLabelAttribute()
    {
        $labels = [
            self::TYPE_INTERNAL => 'Internal Audit',
            self::TYPE_EXTERNAL => 'External Audit',
            self::TYPE_REGULATORY => 'Regulatory Audit',
            self::TYPE_SELF_ASSESSMENT => 'Self Assessment'
        ];

        return $labels[$this->audit_type] ?? 'Unknown';
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            self::STATUS_PENDING => 'warning',
            self::STATUS_IN_PROGRESS => 'info',
            self::STATUS_COMPLETED => 'success',
            self::STATUS_FAILED => 'danger',
            self::STATUS_OVERDUE => 'dark'
        ];

        return $badges[$this->status] ?? 'secondary';
    }

    public function getStatusLabelAttribute()
    {
        $labels = [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_FAILED => 'Failed',
            self::STATUS_OVERDUE => 'Overdue'
        ];

        return $labels[$this->status] ?? 'Unknown';
    }

    public function getComplianceScoreColorAttribute()
    {
        if ($this->compliance_score >= 90) {
            return 'success';
        } elseif ($this->compliance_score >= 70) {
            return 'warning';
        } else {
            return 'danger';
        }
    }

    public function getRiskLevelAttribute()
    {
        if ($this->compliance_score >= 90) {
            return 'Low Risk';
        } elseif ($this->compliance_score >= 70) {
            return 'Medium Risk';
        } else {
            return 'High Risk';
        }
    }

    public function getRiskLevelColorAttribute()
    {
        if ($this->compliance_score >= 90) {
            return 'success';
        } elseif ($this->compliance_score >= 70) {
            return 'warning';
        } else {
            return 'danger';
        }
    }

    public function isOverdue()
    {
        return $this->follow_up_date && $this->follow_up_date->isPast() && 
               $this->status !== self::STATUS_COMPLETED;
    }

    public function getDaysUntilFollowUpAttribute()
    {
        if (!$this->follow_up_date) {
            return null;
        }

        return $this->follow_up_date->diffInDays(now(), false);
    }

    public function getFindingsCountAttribute()
    {
        return is_array($this->findings) ? count($this->findings) : 0;
    }

    public function getRecommendationsCountAttribute()
    {
        return is_array($this->recommendations) ? count($this->recommendations) : 0;
    }

    public function getCorrectiveActionsCountAttribute()
    {
        return is_array($this->corrective_actions) ? count($this->corrective_actions) : 0;
    }

    public function getCompletionPercentageAttribute()
    {
        $totalActions = $this->corrective_actions_count;
        if ($totalActions === 0) {
            return 100;
        }

        $completedActions = 0;
        if (is_array($this->corrective_actions)) {
            foreach ($this->corrective_actions as $action) {
                if (isset($action['completed']) && $action['completed']) {
                    $completedActions++;
                }
            }
        }

        return $totalActions > 0 ? round(($completedActions / $totalActions) * 100, 2) : 0;
    }
} 