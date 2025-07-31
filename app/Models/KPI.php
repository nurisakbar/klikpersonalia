<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;
use Carbon\Carbon;

class KPI extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'employee_id',
        'company_id',
        'kpi_name',
        'description',
        'target_value',
        'current_value',
        'unit',
        'weight',
        'period',
        'start_date',
        'end_date',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'target_value' => 'decimal:2',
        'current_value' => 'decimal:2',
        'weight' => 'decimal:2',
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

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeCurrentCompany($query)
    {
        return $query->where('company_id', auth()->user()->company_id);
    }

    public function scopeActive($query)
    {
        return $query->where('end_date', '>=', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('end_date', '<', now());
    }

    public function scopeThisPeriod($query, $period = 'monthly')
    {
        $now = now();
        
        switch ($period) {
            case 'monthly':
                return $query->whereYear('start_date', $now->year)
                            ->whereMonth('start_date', $now->month);
            case 'quarterly':
                $quarter = ceil($now->month / 3);
                $startMonth = ($quarter - 1) * 3 + 1;
                $endMonth = $quarter * 3;
                return $query->whereYear('start_date', $now->year)
                            ->whereBetween('start_date', [
                                $now->year . '-' . str_pad($startMonth, 2, '0', STR_PAD_LEFT) . '-01',
                                $now->year . '-' . str_pad($endMonth, 2, '0', STR_PAD_LEFT) . '-' . $now->daysInMonth
                            ]);
            case 'yearly':
                return $query->whereYear('start_date', $now->year);
            default:
                return $query;
        }
    }

    // Accessors
    public function getFormattedStartDateAttribute()
    {
        return $this->start_date->format('d/m/Y');
    }

    public function getFormattedEndDateAttribute()
    {
        return $this->end_date->format('d/m/Y');
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
        
        return '<span class="' . $periodClass[$this->period] . '">' . $periodText[$this->period] . '</span>';
    }

    public function getStatusBadgeAttribute()
    {
        if ($this->isExpired()) {
            return '<span class="badge badge-danger">Expired</span>';
        } elseif ($this->isCompleted()) {
            return '<span class="badge badge-success">Completed</span>';
        } elseif ($this->isOnTrack()) {
            return '<span class="badge badge-primary">On Track</span>';
        } else {
            return '<span class="badge badge-warning">At Risk</span>';
        }
    }

    public function getProgressBadgeAttribute()
    {
        $progress = $this->getProgressPercentage();
        
        if ($progress >= 100) {
            return '<span class="badge badge-success">100%</span>';
        } elseif ($progress >= 80) {
            return '<span class="badge badge-primary">' . $progress . '%</span>';
        } elseif ($progress >= 60) {
            return '<span class="badge badge-warning">' . $progress . '%</span>';
        } else {
            return '<span class="badge badge-danger">' . $progress . '%</span>';
        }
    }

    // Methods
    public function getProgressPercentage()
    {
        if ($this->target_value == 0) return 0;
        return round(($this->current_value / $this->target_value) * 100, 1);
    }

    public function isExpired()
    {
        return $this->end_date->isPast();
    }

    public function isCompleted()
    {
        return $this->current_value >= $this->target_value;
    }

    public function isOnTrack()
    {
        $progress = $this->getProgressPercentage();
        $daysElapsed = $this->start_date->diffInDays(now());
        $totalDays = $this->start_date->diffInDays($this->end_date);
        
        if ($totalDays == 0) return true;
        
        $expectedProgress = ($daysElapsed / $totalDays) * 100;
        return $progress >= $expectedProgress;
    }

    public function isAtRisk()
    {
        return !$this->isOnTrack() && !$this->isCompleted();
    }

    public function getRemainingDays()
    {
        return max(0, $this->end_date->diffInDays(now()));
    }

    public function getWeightedScore()
    {
        $progress = $this->getProgressPercentage();
        return ($progress * $this->weight) / 100;
    }

    public function getPerformanceLevel()
    {
        $progress = $this->getProgressPercentage();
        
        if ($progress >= 100) return 'Exceeded';
        if ($progress >= 90) return 'Excellent';
        if ($progress >= 80) return 'Good';
        if ($progress >= 70) return 'Satisfactory';
        if ($progress >= 60) return 'Needs Improvement';
        return 'Poor';
    }
} 