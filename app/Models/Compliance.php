<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class Compliance extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'company_id',
        'compliance_type',
        'title',
        'description',
        'regulation_reference',
        'effective_date',
        'due_date',
        'status',
        'priority',
        'assigned_to',
        'completion_date',
        'compliance_score',
        'risk_level',
        'documentation_required',
        'notes',
        'last_audit_date',
        'next_audit_date'
    ];

    protected $casts = [
        'effective_date' => 'date',
        'due_date' => 'date',
        'completion_date' => 'date',
        'last_audit_date' => 'date',
        'next_audit_date' => 'date',
        'compliance_score' => 'decimal:2',
        'documentation_required' => 'array'
    ];

    // Compliance types
    const TYPE_TAX_COMPLIANCE = 'tax_compliance';
    const TYPE_LABOR_LAW = 'labor_law';
    const TYPE_BPJS_COMPLIANCE = 'bpjs_compliance';
    const TYPE_DATA_PROTECTION = 'data_protection';
    const TYPE_FINANCIAL_REPORTING = 'financial_reporting';
    const TYPE_EMPLOYMENT_CONTRACTS = 'employment_contracts';
    const TYPE_WORKPLACE_SAFETY = 'workplace_safety';
    const TYPE_ANTI_DISCRIMINATION = 'anti_discrimination';
    const TYPE_OTHER = 'other';

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_OVERDUE = 'overdue';
    const STATUS_EXEMPT = 'exempt';
    const STATUS_UNDER_REVIEW = 'under_review';

    // Priority constants
    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_CRITICAL = 'critical';

    // Risk level constants
    const RISK_LOW = 'low';
    const RISK_MEDIUM = 'medium';
    const RISK_HIGH = 'high';
    const RISK_CRITICAL = 'critical';

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function auditLogs()
    {
        return $this->hasMany(ComplianceAuditLog::class);
    }

    public function documents()
    {
        return $this->hasMany(ComplianceDocument::class);
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('status', [self::STATUS_COMPLETED, self::STATUS_EXEMPT]);
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
                    ->whereNotIn('status', [self::STATUS_COMPLETED, self::STATUS_EXEMPT]);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('compliance_type', $type);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeByRiskLevel($query, $riskLevel)
    {
        return $query->where('risk_level', $riskLevel);
    }

    public function getTypeLabelAttribute()
    {
        $labels = [
            self::TYPE_TAX_COMPLIANCE => 'Tax Compliance',
            self::TYPE_LABOR_LAW => 'Labor Law',
            self::TYPE_BPJS_COMPLIANCE => 'BPJS Compliance',
            self::TYPE_DATA_PROTECTION => 'Data Protection',
            self::TYPE_FINANCIAL_REPORTING => 'Financial Reporting',
            self::TYPE_EMPLOYMENT_CONTRACTS => 'Employment Contracts',
            self::TYPE_WORKPLACE_SAFETY => 'Workplace Safety',
            self::TYPE_ANTI_DISCRIMINATION => 'Anti-Discrimination',
            self::TYPE_OTHER => 'Other'
        ];

        return $labels[$this->compliance_type] ?? 'Unknown';
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            self::STATUS_PENDING => 'warning',
            self::STATUS_IN_PROGRESS => 'info',
            self::STATUS_COMPLETED => 'success',
            self::STATUS_OVERDUE => 'danger',
            self::STATUS_EXEMPT => 'secondary',
            self::STATUS_UNDER_REVIEW => 'primary'
        ];

        return $badges[$this->status] ?? 'secondary';
    }

    public function getStatusLabelAttribute()
    {
        $labels = [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_OVERDUE => 'Overdue',
            self::STATUS_EXEMPT => 'Exempt',
            self::STATUS_UNDER_REVIEW => 'Under Review'
        ];

        return $labels[$this->status] ?? 'Unknown';
    }

    public function getPriorityBadgeAttribute()
    {
        $badges = [
            self::PRIORITY_LOW => 'success',
            self::PRIORITY_MEDIUM => 'warning',
            self::PRIORITY_HIGH => 'danger',
            self::PRIORITY_CRITICAL => 'dark'
        ];

        return $badges[$this->priority] ?? 'secondary';
    }

    public function getPriorityLabelAttribute()
    {
        $labels = [
            self::PRIORITY_LOW => 'Low',
            self::PRIORITY_MEDIUM => 'Medium',
            self::PRIORITY_HIGH => 'High',
            self::PRIORITY_CRITICAL => 'Critical'
        ];

        return $labels[$this->priority] ?? 'Unknown';
    }

    public function getRiskLevelBadgeAttribute()
    {
        $badges = [
            self::RISK_LOW => 'success',
            self::RISK_MEDIUM => 'warning',
            self::RISK_HIGH => 'danger',
            self::RISK_CRITICAL => 'dark'
        ];

        return $badges[$this->risk_level] ?? 'secondary';
    }

    public function getRiskLevelLabelAttribute()
    {
        $labels = [
            self::RISK_LOW => 'Low Risk',
            self::RISK_MEDIUM => 'Medium Risk',
            self::RISK_HIGH => 'High Risk',
            self::RISK_CRITICAL => 'Critical Risk'
        ];

        return $labels[$this->risk_level] ?? 'Unknown';
    }

    public function isOverdue()
    {
        return $this->due_date && $this->due_date->isPast() && 
               !in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_EXEMPT]);
    }

    public function isDueSoon()
    {
        return $this->due_date && $this->due_date->diffInDays(now()) <= 30 && 
               !in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_EXEMPT]);
    }

    public function getDaysUntilDueAttribute()
    {
        if (!$this->due_date) {
            return null;
        }

        return $this->due_date->diffInDays(now(), false);
    }

    public function getCompletionPercentageAttribute()
    {
        if ($this->status === self::STATUS_COMPLETED) {
            return 100;
        }

        if ($this->status === self::STATUS_PENDING) {
            return 0;
        }

        if ($this->status === self::STATUS_IN_PROGRESS) {
            return 50;
        }

        return 0;
    }

    public function calculateComplianceScore()
    {
        // Calculate compliance score based on various factors
        $score = 0;
        $totalFactors = 0;

        // Status factor
        $statusScores = [
            self::STATUS_COMPLETED => 100,
            self::STATUS_IN_PROGRESS => 60,
            self::STATUS_PENDING => 20,
            self::STATUS_OVERDUE => 0,
            self::STATUS_EXEMPT => 100,
            self::STATUS_UNDER_REVIEW => 40
        ];

        $score += $statusScores[$this->status] ?? 0;
        $totalFactors++;

        // Due date factor
        if ($this->due_date) {
            if ($this->isOverdue()) {
                $score += 0;
            } elseif ($this->isDueSoon()) {
                $score += 70;
            } else {
                $score += 90;
            }
            $totalFactors++;
        }

        // Documentation factor
        if ($this->documents()->count() > 0) {
            $score += 80;
        } else {
            $score += 20;
        }
        $totalFactors++;

        // Audit factor
        if ($this->last_audit_date && $this->last_audit_date->diffInDays(now()) < 365) {
            $score += 90;
        } else {
            $score += 30;
        }
        $totalFactors++;

        return $totalFactors > 0 ? round($score / $totalFactors, 2) : 0;
    }

    public function updateComplianceScore()
    {
        $this->update(['compliance_score' => $this->calculateComplianceScore()]);
    }
} 