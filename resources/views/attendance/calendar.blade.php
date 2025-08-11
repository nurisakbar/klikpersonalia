@extends('layouts.app')

@section('title', 'Attendance Calendar')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-9">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-calendar-alt mr-2"></i>
                        Attendance Calendar - {{ $calendarData['month'] }}
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('attendance.calendar', ['month' => $calendarData['prev_month']]) }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-chevron-left"></i> Previous
                        </a>
                        <a href="{{ route('attendance.calendar', ['month' => $calendarData['next_month']]) }}" class="btn btn-sm btn-secondary">
                            Next <i class="fas fa-chevron-right"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Calendar Legend -->
                    <div class="calendar-legend mb-3">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="d-flex flex-wrap">
                                    <div class="legend-item mr-3 mb-2">
                                        <span class="legend-color bg-success"></span>
                                        <small>Present</small>
                                    </div>
                                    <div class="legend-item mr-3 mb-2">
                                        <span class="legend-color bg-warning"></span>
                                        <small>Late</small>
                                    </div>
                                    <div class="legend-item mr-3 mb-2">
                                        <span class="legend-color bg-danger"></span>
                                        <small>Absent</small>
                                    </div>
                                    <div class="legend-item mr-3 mb-2">
                                        <span class="legend-color bg-info"></span>
                                        <small>Overtime</small>
                                    </div>
                                    <div class="legend-item mr-3 mb-2">
                                        <span class="legend-color bg-warning"></span>
                                        <small>Leave</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 text-right">
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="loadCalendarData()">
                                        <i class="fas fa-sync-alt"></i> Refresh
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-success" onclick="exportCalendar()">
                                        <i class="fas fa-download"></i> Export
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Calendar Grid -->
                    <div class="calendar-container">
                        <div class="calendar-header">
                            <div class="calendar-day-header">Sun</div>
                            <div class="calendar-day-header">Mon</div>
                            <div class="calendar-day-header">Tue</div>
                            <div class="calendar-day-header">Wed</div>
                            <div class="calendar-day-header">Thu</div>
                            <div class="calendar-day-header">Fri</div>
                            <div class="calendar-day-header">Sat</div>
                        </div>
                        
                        <div class="calendar-grid" id="calendarGrid">
                            @php
                                $firstDay = Carbon\Carbon::create($year, $month, 1);
                                $lastDay = $firstDay->copy()->endOfMonth();
                                $startDate = $firstDay->copy()->startOfWeek(Carbon\Carbon::SUNDAY);
                                $endDate = $lastDay->copy()->endOfWeek(Carbon\Carbon::SATURDAY);
                                $currentDate = $startDate->copy();
                            @endphp
                            
                            @while($currentDate->lte($endDate))
                                @php
                                    $isCurrentMonth = $currentDate->month == $month;
                                    $isToday = $currentDate->isToday();
                                    $isWeekend = $currentDate->isWeekend();
                                    $dateKey = $currentDate->format('Y-m-d');
                                    $dayData = collect($calendarData['calendar'])->firstWhere('date', $dateKey);
                                @endphp
                                
                                <div class="calendar-day {{ !$isCurrentMonth ? 'other-month' : '' }} {{ $isToday ? 'today' : '' }} {{ $isWeekend ? 'weekend' : '' }}" 
                                     data-date="{{ $dateKey }}">
                                    <div class="calendar-day-number">{{ $currentDate->day }}</div>
                                    
                                    @if($dayData)
                                        <div class="calendar-events">
                                            @foreach($dayData['events'] as $event)
                                                <div class="calendar-event {{ $event['class'] }}" 
                                                     data-toggle="tooltip" 
                                                     title="{{ $event['title'] }}">
                                                    <i class="{{ $event['icon'] }}"></i>
                                                </div>
                                            @endforeach
                                        </div>
                                        
                                        @if($dayData['attendance'])
                                            <div class="calendar-details">
                                                <small class="text-muted">
                                                    @if($dayData['attendance']['check_in'])
                                                        In: {{ $dayData['attendance']['check_in'] }}
                                                    @endif
                                                    @if($dayData['attendance']['check_out'])
                                                        <br>Out: {{ $dayData['attendance']['check_out'] }}
                                                    @endif
                                                </small>
                                            </div>
                                        @endif
                                        
                                        @if($dayData['overtime'])
                                            <div class="calendar-details">
                                                <small class="text-info">
                                                    OT: {{ $dayData['overtime']['total_hours'] }}h
                                                </small>
                                            </div>
                                        @endif
                                    @endif
                                </div>
                                
                                @php
                                    $currentDate->addDay();
                                @endphp
                            @endwhile
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <!-- Monthly Statistics -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-chart-pie mr-2"></i>
                        Monthly Statistics
                    </h5>
                </div>
                <div class="card-body">
                    <div class="stat-item">
                        <div class="stat-label">Present Days</div>
                        <div class="stat-value text-success">{{ $statistics['present_days'] }}</div>
                    </div>
                    
                    <div class="stat-item">
                        <div class="stat-label">Late Days</div>
                        <div class="stat-value text-warning">{{ $statistics['late_days'] }}</div>
                    </div>
                    
                    <div class="stat-item">
                        <div class="stat-label">Absent Days</div>
                        <div class="stat-value text-danger">{{ $statistics['absent_days'] }}</div>
                    </div>
                    
                    <div class="stat-item">
                        <div class="stat-label">Leave Days</div>
                        <div class="stat-value text-info">{{ $statistics['leave_days'] }}</div>
                    </div>
                    
                    <div class="stat-item">
                        <div class="stat-label">Overtime Hours</div>
                        <div class="stat-value text-primary">{{ $statistics['overtime_hours'] }}h</div>
                    </div>
                    
                    <div class="stat-item">
                        <div class="stat-label">Attendance Rate</div>
                        <div class="stat-value text-success">{{ $statistics['attendance_rate'] }}%</div>
                    </div>
                    
                    <hr>
                    
                    <div class="stat-item">
                        <div class="stat-label">Working Days</div>
                        <div class="stat-value">{{ $statistics['total_working_days'] }}</div>
                    </div>
                    
                    <div class="stat-item">
                        <div class="stat-label">Weekends</div>
                        <div class="stat-value">{{ $calendarData['weekends'] }}</div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-bolt mr-2"></i>
                        Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('attendance.check-in-out') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-sign-in-alt mr-1"></i> Check In/Out
                        </a>
                        <a href="{{ route('leaves.create') }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-calendar-plus mr-1"></i> Submit Leave
                        </a>
                        <a href="{{ route('overtimes.create') }}" class="btn btn-info btn-sm">
                            <i class="fas fa-clock mr-1"></i> Submit Overtime
                        </a>
                        <a href="{{ route('attendance.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-list mr-1"></i> View All Records
                        </a>
                    </div>
                </div>
            </div>

            <!-- Calendar Info -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-info-circle mr-2"></i>
                        Calendar Info
                    </h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li><i class="fas fa-circle text-success mr-2"></i> Green: Present</li>
                        <li><i class="fas fa-circle text-warning mr-2"></i> Yellow: Late/Leave</li>
                        <li><i class="fas fa-circle text-danger mr-2"></i> Red: Absent</li>
                        <li><i class="fas fa-circle text-info mr-2"></i> Blue: Overtime</li>
                        <li><i class="fas fa-circle text-muted mr-2"></i> Gray: Other Month</li>
                    </ul>
                    <hr>
                    <small class="text-muted">
                        <i class="fas fa-mouse-pointer mr-1"></i>
                        Hover over events to see details
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Calendar day click handler
    $('.calendar-day').click(function() {
        const date = $(this).data('date');
        const events = $(this).find('.calendar-event');
        
        if (events.length > 0) {
            showDayDetails(date, events);
        }
    });
});

function loadCalendarData() {
    const urlParams = new URLSearchParams(window.location.search);
    const month = urlParams.get('month') || '{{ date("Y-m") }}';
    
    $.get('{{ route("attendance.calendar.data") }}', { month: month })
        .done(function(data) {
            // Update calendar with new data
            updateCalendar(data);
        })
        .fail(function() {
            alert('Failed to load calendar data');
        });
}

function updateCalendar(data) {
    // Implementation for updating calendar with new data
    console.log('Calendar data updated:', data);
}

function showDayDetails(date, events) {
    let details = `<strong>${date}</strong><br>`;
    
    events.each(function() {
        const title = $(this).attr('title');
        const icon = $(this).find('i').attr('class');
        details += `<i class="${icon}"></i> ${title}<br>`;
    });
    
    // Show details in a modal or tooltip
    alert(details);
}

function exportCalendar() {
    // Implementation for exporting calendar data
    alert('Export functionality will be implemented here');
}
</script>
@endpush

@push('styles')
<style>
.calendar-container {
    background: #fff;
    border-radius: 8px;
    overflow: hidden;
}

.calendar-header {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    background: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.calendar-day-header {
    padding: 12px;
    text-align: center;
    font-weight: bold;
    color: #495057;
    border-right: 1px solid #dee2e6;
}

.calendar-day-header:last-child {
    border-right: none;
}

.calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
}

.calendar-day {
    min-height: 100px;
    padding: 8px;
    border-right: 1px solid #dee2e6;
    border-bottom: 1px solid #dee2e6;
    position: relative;
    cursor: pointer;
    transition: background-color 0.2s;
}

.calendar-day:hover {
    background-color: #f8f9fa;
}

.calendar-day:nth-child(7n) {
    border-right: none;
}

.calendar-day.other-month {
    background-color: #f8f9fa;
    color: #adb5bd;
}

.calendar-day.today {
    background-color: #e3f2fd;
    border: 2px solid #2196f3;
}

.calendar-day.weekend {
    background-color: #fff3e0;
}

.calendar-day-number {
    font-weight: bold;
    margin-bottom: 4px;
}

.calendar-events {
    display: flex;
    flex-wrap: wrap;
    gap: 2px;
    margin-bottom: 4px;
}

.calendar-event {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    color: white;
}

.calendar-details {
    font-size: 10px;
    line-height: 1.2;
}

.calendar-legend {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border: 1px solid #dee2e6;
}

.legend-item {
    display: flex;
    align-items: center;
}

.legend-color {
    width: 16px;
    height: 16px;
    border-radius: 50%;
    margin-right: 8px;
}

.stat-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #f0f0f0;
}

.stat-item:last-child {
    border-bottom: none;
}

.stat-label {
    font-size: 14px;
    color: #6c757d;
}

.stat-value {
    font-weight: bold;
    font-size: 16px;
}

@media (max-width: 768px) {
    .calendar-day {
        min-height: 80px;
        padding: 4px;
    }
    
    .calendar-event {
        width: 16px;
        height: 16px;
        font-size: 8px;
    }
    
    .calendar-details {
        font-size: 8px;
    }
}
</style>
@endpush 