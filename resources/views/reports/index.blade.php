@extends('layouts.app')

@section('title', 'Attendance Reports')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar mr-2"></i>
                        Attendance Reports Dashboard
                    </h3>
                    <div class="card-tools">
                        <div class="btn-group" role="group">
                            <a href="{{ route('reports.individual') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-user mr-1"></i> Individual Report
                            </a>
                            @if(in_array(auth()->user()->role, ['admin', 'hr', 'manager']))
                            <a href="{{ route('reports.team') }}" class="btn btn-info btn-sm">
                                <i class="fas fa-users mr-1"></i> Team Report
                            </a>
                            @endif
                            @if(in_array(auth()->user()->role, ['admin', 'hr']))
                            <a href="{{ route('reports.company') }}" class="btn btn-success btn-sm">
                                <i class="fas fa-building mr-1"></i> Company Report
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Monthly Statistics Cards -->
                    <div class="row">
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3>{{ $monthlyStats['total_employees'] }}</h3>
                                    <p>Total Employees</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <a href="{{ route('employees.index') }}" class="small-box-footer">
                                    More info <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                        
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3>{{ $monthlyStats['present_days'] }}</h3>
                                    <p>Present Days (This Month)</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-user-check"></i>
                                </div>
                                <a href="{{ route('attendance.index') }}" class="small-box-footer">
                                    More info <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                        
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3>{{ $monthlyStats['late_days'] }}</h3>
                                    <p>Late Days (This Month)</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <a href="{{ route('attendance.index') }}" class="small-box-footer">
                                    More info <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                        
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-danger">
                                <div class="inner">
                                    <h3>{{ $monthlyStats['attendance_rate'] }}%</h3>
                                    <p>Attendance Rate</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <a href="{{ route('reports.individual') }}" class="small-box-footer">
                                    More info <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-primary">
                                <div class="inner">
                                    <h3>{{ $monthlyStats['leave_days'] }}</h3>
                                    <p>Leave Days (This Month)</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-calendar-times"></i>
                                </div>
                                <a href="{{ route('leaves.index') }}" class="small-box-footer">
                                    More info <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                        
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-secondary">
                                <div class="inner">
                                    <h3>{{ $monthlyStats['overtime_hours'] }}h</h3>
                                    <p>Overtime Hours (This Month)</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <a href="{{ route('overtimes.index') }}" class="small-box-footer">
                                    More info <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                        
                        <div class="col-lg-6">
                            <div class="info-box bg-gradient-success">
                                <span class="info-box-icon">
                                    <i class="fas fa-chart-pie"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Monthly Overview</span>
                                    <span class="info-box-number">{{ date('F Y') }}</span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: {{ $monthlyStats['attendance_rate'] }}%"></div>
                                    </div>
                                    <span class="progress-description">
                                        Overall attendance performance
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Activities -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-history mr-2"></i>
                        Recent Activities
                    </h3>
                </div>
                <div class="card-body">
                    @if($recentActivities->count() > 0)
                        <div class="timeline">
                            @foreach($recentActivities as $activity)
                                <div class="time-label">
                                    <span class="bg-{{ $this->getActivityColor($activity['type']) }}">
                                        {{ Carbon\Carbon::parse($activity['time'])->format('d M Y') }}
                                    </span>
                                </div>
                                <div>
                                    <i class="fas fa-{{ $this->getActivityIcon($activity['type']) }} bg-{{ $this->getActivityColor($activity['type']) }}"></i>
                                    <div class="timeline-item">
                                        <span class="time">
                                            <i class="fas fa-clock"></i> 
                                            {{ Carbon\Carbon::parse($activity['time'])->format('H:i') }}
                                        </span>
                                        <h3 class="timeline-header">
                                            <strong>{{ $activity['employee'] }}</strong>
                                        </h3>
                                        <div class="timeline-body">
                                            {{ $activity['action'] }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Recent Activities</h5>
                            <p class="text-muted">No activities have been recorded yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-bolt mr-2"></i>
                        Quick Actions
                    </h3>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('reports.individual') }}" class="btn btn-primary">
                            <i class="fas fa-user mr-2"></i> My Attendance Report
                        </a>
                        
                        @if(in_array(auth()->user()->role, ['admin', 'hr', 'manager']))
                        <a href="{{ route('reports.team') }}" class="btn btn-info">
                            <i class="fas fa-users mr-2"></i> Team Report
                        </a>
                        @endif
                        
                        @if(in_array(auth()->user()->role, ['admin', 'hr']))
                        <a href="{{ route('reports.company') }}" class="btn btn-success">
                            <i class="fas fa-building mr-2"></i> Company Report
                        </a>
                        @endif
                        
                        <a href="{{ route('attendance.calendar') }}" class="btn btn-warning">
                            <i class="fas fa-calendar mr-2"></i> Attendance Calendar
                        </a>
                        
                        <a href="{{ route('attendance.index') }}" class="btn btn-secondary">
                            <i class="fas fa-list mr-2"></i> All Attendance Records
                        </a>
                    </div>
                </div>
            </div>

            <!-- Report Types Info -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle mr-2"></i>
                        Report Types
                    </h3>
                </div>
                <div class="card-body">
                    <div class="report-type-item">
                        <h6><i class="fas fa-user text-primary"></i> Individual Report</h6>
                        <small class="text-muted">Personal attendance, leave, and overtime summary</small>
                    </div>
                    <hr>
                    <div class="report-type-item">
                        <h6><i class="fas fa-users text-info"></i> Team Report</h6>
                        <small class="text-muted">Department/team performance overview (Managers/HR)</small>
                    </div>
                    <hr>
                    <div class="report-type-item">
                        <h6><i class="fas fa-building text-success"></i> Company Report</h6>
                        <small class="text-muted">Company-wide statistics and trends (Admin/HR)</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.report-type-item {
    margin-bottom: 15px;
}

.report-type-item:last-child {
    margin-bottom: 0;
}

.timeline {
    position: relative;
    margin: 0 0 30px 0;
    padding: 0;
    list-style: none;
}

.timeline:before {
    content: '';
    position: absolute;
    top: 0;
    bottom: 0;
    width: 4px;
    background: #ddd;
    left: 31px;
    margin: 0;
    border-radius: 2px;
}

.timeline > div {
    position: relative;
    margin-right: 10px;
    margin-bottom: 15px;
}

.timeline > div:before,
.timeline > div:after {
    content: '';
    display: table;
}

.timeline > div:after {
    clear: both;
}

.timeline > div > .timeline-item {
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    border-radius: 0.25rem;
    background-color: #fff;
    color: #495057;
    margin-left: 60px;
    margin-top: 0;
    margin-bottom: 0;
    margin-right: 0;
    position: relative;
}

.timeline > div > .timeline-item > .time {
    color: #999;
    float: right;
    padding: 10px;
    font-size: 12px;
}

.timeline > div > .timeline-item > .timeline-header {
    color: #495057;
    font-size: 16px;
    line-height: 1.1;
    margin: 0;
    padding: 10px;
    border-bottom: 0 solid rgba(0,0,0,.125);
    background-color: transparent;
}

.timeline > div > .timeline-item > .timeline-body,
.timeline > div > .timeline-item > .timeline-footer {
    padding: 10px;
}

.timeline > div > i,
.timeline > div > .fa,
.timeline > div > .fas,
.timeline > div > .far,
.timeline > div > .fab,
.timeline > div > .glyphicon {
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    border-radius: 50%;
    background-color: #fff;
    color: #fff;
    width: 30px;
    height: 30px;
    line-height: 30px;
    font-size: 15px;
    text-align: center;
    position: absolute;
    top: 0;
    left: 18px;
    margin-right: auto;
    margin-left: auto;
}

.timeline > .time-label > span {
    font-weight: 600;
    padding: 5px 10px;
    background-color: #fff;
    border-radius: 4px;
    color: #fff;
    font-size: 12px;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-refresh every 5 minutes
    setInterval(function() {
        location.reload();
    }, 300000);
});
</script>
@endpush 