<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;
use Carbon\Carbon;

class Goal extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'employee_id',
        'company_id',
        'assigned_by',
        'goal_title',
        'description',
        'target_value',
        'current_value',
        'unit',
        'priority',
        'start_date',
        'due_date',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'due_date' => 'date',
        'target_value' => 'decimal:2',
        'current_value' => 'decimal:2',
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

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    // Scopes
    public function scopeCurrentCompany($query)
    {
        return $query->where('company_id', auth()->user()->company_id);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['not_started', 'in_progress']);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue');
    }

    public function scopeThisMonth($query)
    {
        return $query->whereYear('start_date', date('Y'))
                    ->whereMonth('start_date', date('m'));
    }

    public function scopeHighPriority($query)
    {
        return $query->whereIn('priority', ['high', 'critical']);
    }

    // Accessors
    public function getFormattedStartDateAttribute()
    {
        return $this->start_date->format('d/m/Y');
    }

    public function getFormattedDueDateAttribute()
    {
        return $this->due_date->format('d/m/Y');
    }

    public function getPriorityBadgeAttribute()
    {
        $priorityClass = [
            'low' => 'badge badge-info',
            'medium' => 'badge badge-warning',
            'high' => 'badge badge-danger',
            'critical' => 'badge badge-dark'
        ];
        
        $priorityText = [
            'low' => 'Low',
            'medium' => 'Medium',
            'high' => 'High',
            'critical' => 'Critical'
        ];
        
        return '<span class="' . $priorityClass[$this->priority] . '">' . $priorityText[$this->priority] . '</span>';
    }

    public function getStatusBadgeAttribute()
    {
        $statusClass = [
            'not_started' => 'badge badge-secondary',
            'in_progress' => 'badge badge-primary',
            'completed' => 'badge badge-success',
            'overdue' => 'badge badge-danger'
        ];
        
        $statusText = [
            'not_started' => 'Not Started',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'overdue' => 'Overdue'
        ];
        
        return '<span class="' . $statusClass[$this->status] . '">' . $statusText[$this->status] . '</span>';
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

    public function isOverdue()
    {
        return $this->due_date->isPast() && $this->status !== 'completed';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isInProgress()
    {
        return $this->status === 'in_progress';
    }

    public function isNotStarted()
    {
        return $this->status === 'not_started';
    }

    public function getRemainingDays()
    {
        return max(0, $this->due_date->diffInDays(now()));
    }

    public function getDaysElapsed()
    {
        return $this->start_date->diffInDays(now());
    }

    public function getTotalDays()
    {
        return $this->start_date->diffInDays($this->due_date);
    }

    public function isOnTrack()
    {
        if ($this->isCompleted()) return true;
        
        $progress = $this->getProgressPercentage();
        $daysElapsed = $this->getDaysElapsed();
        $totalDays = $this->getTotalDays();
        
        if ($totalDays == 0) return true;
        
        $expectedProgress = ($daysElapsed / $totalDays) * 100;
        return $progress >= $expectedProgress;
    }

    public function isAtRisk()
    {
        return !$this->isOnTrack() && !$this->isCompleted() && !$this->isOverdue();
    }

    public function getUrgencyLevel()
    {
        $remainingDays = $this->getRemainingDays();
        $progress = $this->getProgressPercentage();
        
        if ($this->isOverdue()) return 'Critical';
        if ($remainingDays <= 3) return 'Urgent';
        if ($remainingDays <= 7) return 'High';
        if ($remainingDays <= 14) return 'Medium';
        return 'Low';
    }

    public function getUrgencyBadgeAttribute()
    {
        $urgency = $this->getUrgencyLevel();
        
        $urgencyClass = [
            'Critical' => 'badge badge-danger',
            'Urgent' => 'badge badge-warning',
            'High' => 'badge badge-info',
            'Medium' => 'badge badge-secondary',
            'Low' => 'badge badge-light'
        ];
        
        return '<span class="' . $urgencyClass[$urgency] . '">' . $urgency . '</span>';
    }

    public function updateStatus()
    {
        if ($this->isOverdue()) {
            $this->update(['status' => 'overdue']);
        } elseif ($this->getProgressPercentage() >= 100) {
            $this->update(['status' => 'completed']);
        } elseif ($this->getProgressPercentage() > 0) {
            $this->update(['status' => 'in_progress']);
        }
    }
} 