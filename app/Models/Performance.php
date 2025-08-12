<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class Performance extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'company_id',
        'employee_id',
        'performance_type',
        'period_start',
        'period_end',
        'kpi_data',
        'appraisal_data',
        'goals_data',
        'overall_score',
        'rating',
        'status',
        'reviewed_by',
        'reviewed_at',
        'notes',
        'next_review_date'
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'kpi_data' => 'array',
        'appraisal_data' => 'array',
        'goals_data' => 'array',
        'overall_score' => 'decimal:2',
        'reviewed_at' => 'datetime',
        'next_review_date' => 'date'
    ];

    // Performance types
    const TYPE_KPI = 'kpi';
    const TYPE_APPRAISAL = 'appraisal';
    const TYPE_GOAL = 'goal';
    const TYPE_ANNUAL = 'annual';

    // Status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    // Rating constants
    const RATING_EXCELLENT = 'excellent';
    const RATING_GOOD = 'good';
    const RATING_AVERAGE = 'average';
    const RATING_BELOW_AVERAGE = 'below_average';
    const RATING_POOR = 'poor';

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('performance_type', $type);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('period_start', [$startDate, $endDate]);
    }

    public function scopeByEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            self::STATUS_DRAFT => 'secondary',
            self::STATUS_PENDING => 'warning',
            self::STATUS_IN_PROGRESS => 'info',
            self::STATUS_COMPLETED => 'success',
            self::STATUS_APPROVED => 'success',
            self::STATUS_REJECTED => 'danger'
        ];

        return $badges[$this->status] ?? 'secondary';
    }

    public function getRatingBadgeAttribute()
    {
        $badges = [
            self::RATING_EXCELLENT => 'success',
            self::RATING_GOOD => 'info',
            self::RATING_AVERAGE => 'warning',
            self::RATING_BELOW_AVERAGE => 'warning',
            self::RATING_POOR => 'danger'
        ];

        return $badges[$this->rating] ?? 'secondary';
    }

    public function getTypeLabelAttribute()
    {
        $labels = [
            self::TYPE_KPI => 'KPI Review',
            self::TYPE_APPRAISAL => 'Performance Appraisal',
            self::TYPE_GOAL => 'Goal Review',
            self::TYPE_ANNUAL => 'Annual Review'
        ];

        return $labels[$this->performance_type] ?? 'Unknown';
    }

    public function getRatingLabelAttribute()
    {
        $labels = [
            self::RATING_EXCELLENT => 'Excellent',
            self::RATING_GOOD => 'Good',
            self::RATING_AVERAGE => 'Average',
            self::RATING_BELOW_AVERAGE => 'Below Average',
            self::RATING_POOR => 'Poor'
        ];

        return $labels[$this->rating] ?? 'Not Rated';
    }

    public function getScorePercentageAttribute()
    {
        if (!$this->overall_score) {
            return 0;
        }

        return round(($this->overall_score / 100) * 100, 1);
    }

    public function isCompleted()
    {
        return in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_APPROVED]);
    }

    public function isPending()
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_IN_PROGRESS]);
    }

    public function canBeReviewed()
    {
        return $this->status === self::STATUS_PENDING || $this->status === self::STATUS_IN_PROGRESS;
    }

    public function calculateOverallScore()
    {
        if (!$this->kpi_data) {
            return 0;
        }

        $totalScore = 0;
        $totalWeight = 0;

        foreach ($this->kpi_data as $kpi) {
            $score = $kpi['score'] ?? 0;
            $weight = $kpi['weight'] ?? 1;
            
            $totalScore += $score * $weight;
            $totalWeight += $weight;
        }

        return $totalWeight > 0 ? round($totalScore / $totalWeight, 2) : 0;
    }

    public function determineRating()
    {
        $score = $this->overall_score;

        if ($score >= 90) {
            return self::RATING_EXCELLENT;
        } elseif ($score >= 80) {
            return self::RATING_GOOD;
        } elseif ($score >= 70) {
            return self::RATING_AVERAGE;
        } elseif ($score >= 60) {
            return self::RATING_BELOW_AVERAGE;
        } else {
            return self::RATING_POOR;
        }
    }
} 