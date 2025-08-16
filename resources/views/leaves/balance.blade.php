@extends('layouts.app')

@section('title', 'Leave Balance')

@section('content')
<div class="container-fluid">
    <!-- Error Messages -->
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-2"></i>
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-pie mr-2"></i>
                        Leave Balance ({{ date('Y') }})
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('leaves.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left mr-1"></i> Back to Leave List
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Annual Leave -->
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Annual Leave
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                {{ $leaveBalance['annual_remaining'] }}/{{ $leaveBalance['annual_total'] }}
                                            </div>
                                            <div class="text-xs text-muted">
                                                {{ $leaveBalance['annual_used'] }} days used
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                    <div class="progress mt-2" style="height: 4px;">
                                        <div class="progress-bar bg-primary" style="width: {{ $leaveBalance['annual_total'] > 0 ? ($leaveBalance['annual_used'] / $leaveBalance['annual_total']) * 100 : 0 }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sick Leave -->
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                Sick Leave
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                {{ $leaveBalance['sick_remaining'] }}/{{ $leaveBalance['sick_total'] }}
                                            </div>
                                            <div class="text-xs text-muted">
                                                {{ $leaveBalance['sick_used'] }} days used
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-user-injured fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                    <div class="progress mt-2" style="height: 4px;">
                                        <div class="progress-bar bg-warning" style="width: {{ $leaveBalance['sick_total'] > 0 ? ($leaveBalance['sick_used'] / $leaveBalance['sick_total']) * 100 : 0 }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Maternity Leave -->
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                Maternity Leave
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                {{ $leaveBalance['maternity_remaining'] }}/{{ $leaveBalance['maternity_total'] }}
                                            </div>
                                            <div class="text-xs text-muted">
                                                {{ $leaveBalance['maternity_used'] }} days used
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-baby fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                    <div class="progress mt-2" style="height: 4px;">
                                        <div class="progress-bar bg-success" style="width: {{ $leaveBalance['maternity_total'] > 0 ? ($leaveBalance['maternity_used'] / $leaveBalance['maternity_total']) * 100 : 0 }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Paternity Leave -->
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                                Paternity Leave
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                {{ $leaveBalance['paternity_remaining'] }}/{{ $leaveBalance['paternity_total'] }}
                                            </div>
                                            <div class="text-xs text-muted">
                                                {{ $leaveBalance['paternity_used'] }} days used
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-user-tie fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                    <div class="progress mt-2" style="height: 4px;">
                                        <div class="progress-bar bg-info" style="width: {{ $leaveBalance['paternity_total'] > 0 ? ($leaveBalance['paternity_used'] / $leaveBalance['paternity_total']) * 100 : 0 }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Other Leave -->
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card border-left-secondary shadow h-100">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">
                                                Other Leave
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                {{ $leaveBalance['other_remaining'] }}/{{ $leaveBalance['other_total'] }}
                                            </div>
                                            <div class="text-xs text-muted">
                                                {{ $leaveBalance['other_used'] }} days used
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-ellipsis-h fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                    <div class="progress mt-2" style="height: 4px;">
                                        <div class="progress-bar bg-secondary" style="width: {{ $leaveBalance['other_total'] > 0 ? ($leaveBalance['other_used'] / $leaveBalance['other_total']) * 100 : 0 }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Total Leave Summary -->
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card border-left-dark shadow h-100">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">
                                                Total Available
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                {{ $leaveBalance['annual_remaining'] + $leaveBalance['sick_remaining'] + $leaveBalance['maternity_remaining'] + $leaveBalance['paternity_remaining'] + $leaveBalance['other_remaining'] }}
                                            </div>
                                            <div class="text-xs text-muted">
                                                days remaining
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-chart-bar fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                    <div class="progress mt-2" style="height: 4px;">
                                        <div class="progress-bar bg-dark" style="width: 100%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Leave Usage Chart -->
                    <div class="row">
                        <div class="col-md-8">
                            <div class="card shadow">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-chart-pie mr-2"></i>
                                        Leave Usage Overview
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="leaveChart" width="400" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card shadow">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-info-circle mr-2"></i>
                                        Leave Policy Summary
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Type</th>
                                                    <th>Quota</th>
                                                    <th>Used</th>
                                                    <th>Remaining</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>Annual</td>
                                                    <td>{{ $leaveBalance['annual_total'] }}</td>
                                                    <td>{{ $leaveBalance['annual_used'] }}</td>
                                                    <td><span class="badge badge-primary">{{ $leaveBalance['annual_remaining'] }}</span></td>
                                                </tr>
                                                <tr>
                                                    <td>Sick</td>
                                                    <td>{{ $leaveBalance['sick_total'] }}</td>
                                                    <td>{{ $leaveBalance['sick_used'] }}</td>
                                                    <td><span class="badge badge-warning">{{ $leaveBalance['sick_remaining'] }}</span></td>
                                                </tr>
                                                <tr>
                                                    <td>Maternity</td>
                                                    <td>{{ $leaveBalance['maternity_total'] }}</td>
                                                    <td>{{ $leaveBalance['maternity_used'] }}</td>
                                                    <td><span class="badge badge-success">{{ $leaveBalance['maternity_remaining'] }}</span></td>
                                                </tr>
                                                <tr>
                                                    <td>Paternity</td>
                                                    <td>{{ $leaveBalance['paternity_total'] }}</td>
                                                    <td>{{ $leaveBalance['paternity_used'] }}</td>
                                                    <td><span class="badge badge-info">{{ $leaveBalance['paternity_remaining'] }}</span></td>
                                                </tr>
                                                <tr>
                                                    <td>Other</td>
                                                    <td>{{ $leaveBalance['other_total'] }}</td>
                                                    <td>{{ $leaveBalance['other_used'] }}</td>
                                                    <td><span class="badge badge-secondary">{{ $leaveBalance['other_remaining'] }}</span></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Leave History -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card shadow">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-history mr-2"></i>
                                        Leave History
                                    </h6>
                                </div>
                                <div class="card-body">
                                    @if($leaveHistory->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Leave Type</th>
                                                        <th>Start Date</th>
                                                        <th>End Date</th>
                                                        <th>Total Days</th>
                                                        <th>Status</th>
                                                        <th>Submitted</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($leaveHistory as $leave)
                                                        <tr>
                                                            <td>
                                                                @if($leave->leave_type === 'annual')
                                                                    <span class="badge badge-primary">Annual Leave</span>
                                                                @elseif($leave->leave_type === 'sick')
                                                                    <span class="badge badge-danger">Sick Leave</span>
                                                                @elseif($leave->leave_type === 'maternity')
                                                                    <span class="badge badge-success">Maternity Leave</span>
                                                                @elseif($leave->leave_type === 'paternity')
                                                                    <span class="badge badge-info">Paternity Leave</span>
                                                                @else
                                                                    <span class="badge badge-warning">Other Leave</span>
                                                                @endif
                                                            </td>
                                                            <td>{{ \Carbon\Carbon::parse($leave->start_date)->format('d/m/Y') }}</td>
                                                            <td>{{ \Carbon\Carbon::parse($leave->end_date)->format('d/m/Y') }}</td>
                                                            <td>{{ $leave->total_days }} days</td>
                                                            <td>
                                                                @if($leave->status === 'approved')
                                                                    <span class="badge badge-success">Approved</span>
                                                                @elseif($leave->status === 'pending')
                                                                    <span class="badge badge-warning">Pending</span>
                                                                @elseif($leave->status === 'rejected')
                                                                    <span class="badge badge-danger">Rejected</span>
                                                                @else
                                                                    <span class="badge badge-secondary">{{ ucfirst($leave->status) }}</span>
                                                                @endif
                                                            </td>
                                                            <td>{{ \Carbon\Carbon::parse($leave->created_at)->format('d/m/Y H:i') }}</td>
                                                            <td>
                                                                <div class="btn-group" role="group">
                                                                    <a href="{{ route('leaves.show', $leave->id) }}" class="btn btn-sm btn-info" title="View Details">
                                                                        <i class="fas fa-eye"></i>
                                                                    </a>
                                                                    @if($leave->status === 'pending')
                                                                        <a href="{{ route('leaves.edit', $leave->id) }}" class="btn btn-sm btn-warning" title="Edit">
                                                                            <i class="fas fa-edit"></i>
                                                                        </a>
                                                                    @endif
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        
                                        <!-- Pagination -->
                                        <div class="d-flex justify-content-center mt-3">
                                            {{ $leaveHistory->links() }}
                                        </div>
                                    @else
                                        <div class="text-center py-4">
                                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                            <h5 class="text-muted">No Leave History Found</h5>
                                            <p class="text-muted">You haven't submitted any leave requests yet.</p>
                                            <a href="{{ route('leaves.create') }}" class="btn btn-primary">
                                                <i class="fas fa-plus mr-1"></i> Submit Your First Leave Request
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card shadow">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-bolt mr-2"></i>
                                        Quick Actions
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4 mb-2">
                                            <a href="{{ route('leaves.create') }}" class="btn btn-primary btn-block">
                                                <i class="fas fa-plus mr-2"></i> Submit Leave Request
                                            </a>
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <a href="{{ route('leaves.index') }}" class="btn btn-info btn-block">
                                                <i class="fas fa-list mr-2"></i> View Leave History
                                            </a>
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <button type="button" class="btn btn-success btn-block" onclick="window.print()">
                                                <i class="fas fa-print mr-2"></i> Print Balance
                                            </button>
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
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Simple Chart
    const ctx = document.getElementById('leaveChart');
    if (ctx) {
        new Chart(ctx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Annual Leave', 'Sick Leave', 'Maternity Leave', 'Paternity Leave', 'Other Leave'],
                datasets: [{
                    data: [
                        {{ $leaveBalance['annual_used'] ?? 0 }},
                        {{ $leaveBalance['sick_used'] ?? 0 }},
                        {{ $leaveBalance['maternity_used'] ?? 0 }},
                        {{ $leaveBalance['paternity_used'] ?? 0 }},
                        {{ $leaveBalance['other_used'] ?? 0 }}
                    ],
                    backgroundColor: [
                        '#007bff', '#ffc107', '#28a745', '#17a2b8', '#6c757d'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    }
                }
            }
        });
    }
});
</script>
@endpush

@push('styles')
<style>
.border-left-primary {
    border-left: 0.25rem solid #007bff !important;
}

.border-left-warning {
    border-left: 0.25rem solid #ffc107 !important;
}

.border-left-success {
    border-left: 0.25rem solid #28a745 !important;
}

.border-left-info {
    border-left: 0.25rem solid #17a2b8 !important;
}

.border-left-secondary {
    border-left: 0.25rem solid #6c757d !important;
}

.border-left-dark {
    border-left: 0.25rem solid #343a40 !important;
}

.text-xs {
    font-size: 0.7rem;
}

.text-gray-300 {
    color: #dddfeb !important;
}

.text-gray-800 {
    color: #5a5c69 !important;
}

.font-weight-bold {
    font-weight: 700 !important;
}

@media print {
    .card-tools, .btn, .progress {
        display: none !important;
    }
    
    .card {
        break-inside: avoid;
    }
}
</style>
@endpush 