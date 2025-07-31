<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;
use Carbon\Carbon;

class Performance extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'employee_id',
        'company_id',
        'reviewer_id',
        'review_period',
        'review_date',
        'overall_rating',
        'job_knowledge',
        'quality_of_work',
        'productivity',
        'teamwork',
        'communication',
        'initiative',
        'attendance',
        'strengths',
        'weaknesses',
        'improvement_plan',
        'comments',
    ];

    protected $casts = [
        'review_date' => 'date',
        'overall_rating' => 'decimal:2',
        'job_knowledge' => 'decimal:2',
        'quality_of_work' => 'decimal:2',
        'productivity' => 'decimal:2',
        'teamwork' => 'decimal:2',
        'communication' => 'decimal:2',
        'initiative' => 'decimal:2',
        'attendance' => 'decimal:2',
    ];

    // Relationships
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
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    // Scopes
    public function scopeCurrentCompany($query)
    {
        return $query->where('company_id', auth()->user()->company_id);
    }

    public function scopeThisYear($query)
    {
        return $query->whereYear('review_date', date('Y'));
    }

    public function scopeThisMonth($query)
    {
        return $query->whereYear('review_date', date('Y'))
                    ->whereMonth('review_date', date('m'));
    }

    public function scopeTopPerformers($query)
    {
        return $query->where('overall_rating', '>=', 4.0);
    }

    // Accessors
    public function getFormattedReviewDateAttribute()
    {
        return $this->review_date->format('d/m/Y');
    }

    public function getRatingBadgeAttribute()
    {
        $rating = $this->overall_rating;
        
        if ($rating >= 4.5) {
            return '<span class="badge badge-success">Outstanding</span>';
        } elseif ($rating >= 4.0) {
            return '<span class="badge badge-primary">Excellent</span>';
        } elseif ($rating >= 3.5) {
            return '<span class="badge badge-info">Good</span>';
        } elseif ($rating >= 3.0) {
            return '<span class="badge badge-warning">Satisfactory</span>';
        } else {
            return '<span class="badge badge-danger">Below Satisfactory</span>';
        }
    }

    public function getPeriodBadgeAttribute()
    {
        $periodClass = [
            'monthly' => 'badge badge-info',
            'quarterly' => 'badge badge-warning',
            'yearly' => 'badge badge-success'
        ];
        
        $periodText = [
            'monthly' => 'Monthly',
            'quarterly' => 'Quarterly',
            'yearly' => 'Yearly'
        ];
        
        return '<span class="' . $periodClass[$this->review_period] . '">' . $periodText[$this->review_period] . '</span>';
    }

    // Methods
    public function getAverageRating()
    {
        $ratings = [
            $this->job_knowledge,
            $this->quality_of_work,
            $this->productivity,
            $this->teamwork,
            $this->communication,
            $this->initiative,
            $this->attendance
        ];
        
        return round(array_sum($ratings) / count($ratings), 2);
    }

    public function isOutstanding()
    {
        return $this->overall_rating >= 4.5;
    }

    public function isExcellent()
    {
        return $this->overall_rating >= 4.0 && $this->overall_rating < 4.5;
    }

    public function isGood()
    {
        return $this->overall_rating >= 3.5 && $this->overall_rating < 4.0;
    }

    public function isSatisfactory()
    {
        return $this->overall_rating >= 3.0 && $this->overall_rating < 3.5;
    }

    public function isBelowSatisfactory()
    {
        return $this->overall_rating < 3.0;
    }

    public function getPerformanceLevel()
    {
        if ($this->isOutstanding()) return 'Outstanding';
        if ($this->isExcellent()) return 'Excellent';
        if ($this->isGood()) return 'Good';
        if ($this->isSatisfactory()) return 'Satisfactory';
        return 'Below Satisfactory';
    }

    public function getBonusPercentage()
    {
        if ($this->isOutstanding()) return 20;
        if ($this->isExcellent()) return 15;
        if ($this->isGood()) return 10;
        if ($this->isSatisfactory()) return 5;
        return 0;
    }
} 