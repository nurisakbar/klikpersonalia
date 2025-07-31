@extends('layouts.app')

@section('title', 'Individual Attendance Report')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user mr-2"></i>
                        Individual Attendance Report - {{ $employee->name }}
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-success btn-sm" onclick="exportReport()">
                            <i class="fas fa-download mr-1"></i> Export Report
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Date Range Filter -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <form method="GET" action="{{ route('reports.individual') }}" class="form-inline">
                                <div class="form-group mr-2">
                                    <label for="start_date" class="mr-2">Start Date:</label>
                                    <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $startDate }}">
                                </div>
                                <div class="form-group mr-2">
                                    <label for="end_date" class="mr-2">End Date:</label>
                                    <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $endDate }}">
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter mr-1"></i> Filter
                                </button>
                            </form>
                        </div>
                        <div class="col-md-6 text-right">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-secondary" onclick="setDateRange('week')">This Week</button>
                                <button type="button" class="btn btn-outline-secondary" onclick="setDateRange('month')">This Month</button>
                                <button type="button" class="btn btn-outline-secondary" onclick="setDateRange('quarter')">This Quarter</button>
                                <button type="button" class="btn btn-outline-secondary" onclick="setDateRange('year')">This Year</button>
                            </div>
                        </div>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="row">
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3>{{ $statistics['present_days'] }}</h3>
                                    <p>Present Days</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-user-check"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3>{{ $statistics['late_days'] }}</h3>
                                    <p>Late Days</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-danger">
                                <div class="inner">
                                    <h3>{{ $statistics['absent_days'] }}</h3>
                                    <p>Absent Days</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-user-times"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3>{{ $statistics['attendance_rate'] }}%</h3>
                                    <p>Attendance Rate</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-primary">
                                <div class="inner">
                                    <h3>{{ $statistics['working_days'] }}</h3>
                                    <p>Working Days</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-calendar"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-secondary">
                                <div class="inner">
                                    <h3>{{ $statistics['leave_days'] }}</h3>
                                    <p>Leave Days</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-calendar-times"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-dark">
                                <div class="inner">
                                    <h3>{{ $statistics['overtime_hours'] }}h</h3>
                                    <p>Overtime Hours</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-light">
                                <div class="inner">
                                    <h3>{{ $statistics['overtime_days'] }}</h3>
                                    <p>Overtime Days</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-calendar-plus"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detailed Data Tabs -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header p-0">
                                    <ul class="nav nav-tabs card-header-tabs">
                                        <li class="nav-item">
                                            <a class="nav-link active" data-toggle="tab" href="#attendance-tab">
                                                <i class="fas fa-user-check mr-1"></i> Attendance Records
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-toggle="tab" href="#leave-tab">
                                                <i class="fas fa-calendar-times mr-1"></i> Leave Records
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-toggle="tab" href="#overtime-tab">
                                                <i class="fas fa-clock mr-1"></i> Overtime Records
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="card-body">
                                    <div class="tab-content">
                                        <!-- Attendance Tab -->
                                        <div class="tab-pane fade show active" id="attendance-tab">
                                            @if($attendances->count() > 0)
                                                <div class="table-responsive">
                                                    <table class="table table-bordered table-striped">
                                                        <thead>
                                                            <tr>
                                                                <th>Date</th>
                                                                <th>Check In</th>
                                                                <th>Check Out</th>
                                                                <th>Total Hours</th>
                                                                <th>Status</th>
                                                                <th>Location</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($attendances as $attendance)
                                                                <tr>
                                                                    <td>{{ $attendance->date->format('d/m/Y') }}</td>
                                                                    <td>
                                                                        @if($attendance->check_in)
                                                                            {{ Carbon\Carbon::parse($attendance->check_in)->format('H:i:s') }}
                                                                        @else
                                                                            <span class="text-muted">-</span>
                                                                        @endif
                                                                    </td>
                                                                    <td>
                                                                        @if($attendance->check_out)
                                                                            {{ Carbon\Carbon::parse($attendance->check_out)->format('H:i:s') }}
                                                                        @else
                                                                            <span class="text-muted">-</span>
                                                                        @endif
                                                                    </td>
                                                                    <td>{{ $attendance->total_hours ?? 0 }} hours</td>
                                                                    <td>
                                                                        @if($attendance->status === 'present')
                                                                            <span class="badge badge-success">Present</span>
                                                                        @elseif($attendance->status === 'late')
                                                                            <span class="badge badge-warning">Late</span>
                                                                        @else
                                                                            <span class="badge badge-secondary">{{ ucfirst($attendance->status) }}</span>
                                                                        @endif
                                                                    </td>
                                                                    <td>{{ $attendance->check_in_location ?? '-' }}</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @else
                                                <div class="text-center py-4">
                                                    <i class="fas fa-user-check fa-3x text-muted mb-3"></i>
                                                    <h5 class="text-muted">No Attendance Records</h5>
                                                    <p class="text-muted">No attendance records found for the selected date range.</p>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Leave Tab -->
                                        <div class="tab-pane fade" id="leave-tab">
                                            @if($leaves->count() > 0)
                                                <div class="table-responsive">
                                                    <table class="table table-bordered table-striped">
                                                        <thead>
                                                            <tr>
                                                                <th>Leave Type</th>
                                                                <th>Start Date</th>
                                                                <th>End Date</th>
                                                                <th>Total Days</th>
                                                                <th>Reason</th>
                                                                <th>Status</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($leaves as $leave)
                                                                <tr>
                                                                    <td>
                                                                        <span class="badge badge-info">{{ ucfirst($leave->leave_type) }}</span>
                                                                    </td>
                                                                    <td>{{ $leave->start_date->format('d/m/Y') }}</td>
                                                                    <td>{{ $leave->end_date->format('d/m/Y') }}</td>
                                                                    <td>{{ $leave->total_days }} days</td>
                                                                    <td>{{ Str::limit($leave->reason, 50) }}</td>
                                                                    <td>
                                                                        @if($leave->status === 'approved')
                                                                            <span class="badge badge-success">Approved</span>
                                                                        @elseif($leave->status === 'pending')
                                                                            <span class="badge badge-warning">Pending</span>
                                                                        @else
                                                                            <span class="badge badge-danger">Rejected</span>
                                                                        @endif
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @else
                                                <div class="text-center py-4">
                                                    <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                                    <h5 class="text-muted">No Leave Records</h5>
                                                    <p class="text-muted">No leave records found for the selected date range.</p>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Overtime Tab -->
                                        <div class="tab-pane fade" id="overtime-tab">
                                            @if($overtimes->count() > 0)
                                                <div class="table-responsive">
                                                    <table class="table table-bordered table-striped">
                                                        <thead>
                                                            <tr>
                                                                <th>Overtime Type</th>
                                                                <th>Date</th>
                                                                <th>Start Time</th>
                                                                <th>End Time</th>
                                                                <th>Total Hours</th>
                                                                <th>Reason</th>
                                                                <th>Status</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($overtimes as $overtime)
                                                                <tr>
                                                                    <td>
                                                                        <span class="badge badge-info">{{ ucfirst($overtime->overtime_type) }}</span>
                                                                    </td>
                                                                    <td>{{ $overtime->date->format('d/m/Y') }}</td>
                                                                    <td>{{ $overtime->start_time }}</td>
                                                                    <td>{{ $overtime->end_time }}</td>
                                                                    <td>{{ $overtime->total_hours }} hours</td>
                                                                    <td>{{ Str::limit($overtime->reason, 50) }}</td>
                                                                    <td>
                                                                        @if($overtime->status === 'approved')
                                                                            <span class="badge badge-success">Approved</span>
                                                                        @elseif($overtime->status === 'pending')
                                                                            <span class="badge badge-warning">Pending</span>
                                                                        @else
                                                                            <span class="badge badge-danger">Rejected</span>
                                                                        @endif
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @else
                                                <div class="text-center py-4">
                                                    <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                                                    <h5 class="text-muted">No Overtime Records</h5>
                                                    <p class="text-muted">No overtime records found for the selected date range.</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function setDateRange(range) {
    const today = new Date();
    let startDate, endDate;
    
    switch(range) {
        case 'week':
            startDate = new Date(today.getFullYear(), today.getMonth(), today.getDate() - today.getDay());
            endDate = new Date(today.getFullYear(), today.getMonth(), today.getDate() - today.getDay() + 6);
            break;
        case 'month':
            startDate = new Date(today.getFullYear(), today.getMonth(), 1);
            endDate = new Date(today.getFullYear(), today.getMonth() + 1, 0);
            break;
        case 'quarter':
            const quarter = Math.floor(today.getMonth() / 3);
            startDate = new Date(today.getFullYear(), quarter * 3, 1);
            endDate = new Date(today.getFullYear(), (quarter + 1) * 3, 0);
            break;
        case 'year':
            startDate = new Date(today.getFullYear(), 0, 1);
            endDate = new Date(today.getFullYear(), 11, 31);
            break;
    }
    
    document.getElementById('start_date').value = startDate.toISOString().split('T')[0];
    document.getElementById('end_date').value = endDate.toISOString().split('T')[0];
    
    // Submit the form
    document.querySelector('form').submit();
}

function exportReport() {
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    
    // Create export form
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("reports.export") }}';
    
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = '{{ csrf_token() }}';
    
    const typeInput = document.createElement('input');
    typeInput.type = 'hidden';
    typeInput.name = 'type';
    typeInput.value = 'individual';
    
    const startDateInput = document.createElement('input');
    startDateInput.type = 'hidden';
    startDateInput.name = 'start_date';
    startDateInput.value = startDate;
    
    const endDateInput = document.createElement('input');
    endDateInput.type = 'hidden';
    endDateInput.name = 'end_date';
    endDateInput.value = endDate;
    
    const formatInput = document.createElement('input');
    formatInput.type = 'hidden';
    formatInput.name = 'format';
    formatInput.value = 'pdf';
    
    form.appendChild(csrfToken);
    form.appendChild(typeInput);
    form.appendChild(startDateInput);
    form.appendChild(endDateInput);
    form.appendChild(formatInput);
    
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}
</script>
@endpush 