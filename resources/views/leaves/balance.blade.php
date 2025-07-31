@extends('layouts.app')

@section('title', 'Leave Balance')

@section('content')
<div class="container-fluid">
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
                        <div class="col-lg-4 col-md-6">
                            <div class="info-box bg-info">
                                <span class="info-box-icon">
                                    <i class="fas fa-calendar-check"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Annual Leave</span>
                                    <span class="info-box-number">{{ $leaveBalance['annual_remaining'] }}/{{ $leaveBalance['annual_total'] }}</span>
                                    <div class="progress">
                                        <div class="progress-bar bg-light" style="width: {{ ($leaveBalance['annual_used'] / $leaveBalance['annual_total']) * 100 }}%"></div>
                                    </div>
                                    <span class="progress-description">
                                        {{ $leaveBalance['annual_used'] }} days used
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Sick Leave -->
                        <div class="col-lg-4 col-md-6">
                            <div class="info-box bg-warning">
                                <span class="info-box-icon">
                                    <i class="fas fa-user-injured"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Sick Leave</span>
                                    <span class="info-box-number">{{ $leaveBalance['sick_remaining'] }}/{{ $leaveBalance['sick_total'] }}</span>
                                    <div class="progress">
                                        <div class="progress-bar bg-light" style="width: {{ ($leaveBalance['sick_used'] / $leaveBalance['sick_total']) * 100 }}%"></div>
                                    </div>
                                    <span class="progress-description">
                                        {{ $leaveBalance['sick_used'] }} days used
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Maternity Leave -->
                        <div class="col-lg-4 col-md-6">
                            <div class="info-box bg-success">
                                <span class="info-box-icon">
                                    <i class="fas fa-baby"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Maternity Leave</span>
                                    <span class="info-box-number">{{ $leaveBalance['maternity_remaining'] }}/{{ $leaveBalance['maternity_total'] }}</span>
                                    <div class="progress">
                                        <div class="progress-bar bg-light" style="width: {{ ($leaveBalance['maternity_used'] / $leaveBalance['maternity_total']) * 100 }}%"></div>
                                    </div>
                                    <span class="progress-description">
                                        {{ $leaveBalance['maternity_used'] }} days used
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Paternity Leave -->
                        <div class="col-lg-4 col-md-6">
                            <div class="info-box bg-primary">
                                <span class="info-box-icon">
                                    <i class="fas fa-user-tie"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Paternity Leave</span>
                                    <span class="info-box-number">{{ $leaveBalance['paternity_remaining'] }}/{{ $leaveBalance['paternity_total'] }}</span>
                                    <div class="progress">
                                        <div class="progress-bar bg-light" style="width: {{ ($leaveBalance['paternity_used'] / $leaveBalance['paternity_total']) * 100 }}%"></div>
                                    </div>
                                    <span class="progress-description">
                                        {{ $leaveBalance['paternity_used'] }} days used
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Other Leave -->
                        <div class="col-lg-4 col-md-6">
                            <div class="info-box bg-secondary">
                                <span class="info-box-icon">
                                    <i class="fas fa-ellipsis-h"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Other Leave</span>
                                    <span class="info-box-number">{{ $leaveBalance['other_remaining'] }}/{{ $leaveBalance['other_total'] }}</span>
                                    <div class="progress">
                                        <div class="progress-bar bg-light" style="width: {{ ($leaveBalance['other_used'] / $leaveBalance['other_total']) * 100 }}%"></div>
                                    </div>
                                    <span class="progress-description">
                                        {{ $leaveBalance['other_used'] }} days used
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Total Leave Summary -->
                        <div class="col-lg-4 col-md-6">
                            <div class="info-box bg-dark">
                                <span class="info-box-icon">
                                    <i class="fas fa-chart-bar"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Available</span>
                                    <span class="info-box-number">
                                        {{ $leaveBalance['annual_remaining'] + $leaveBalance['sick_remaining'] + $leaveBalance['maternity_remaining'] + $leaveBalance['paternity_remaining'] + $leaveBalance['other_remaining'] }}
                                    </span>
                                    <div class="progress">
                                        <div class="progress-bar bg-light" style="width: 100%"></div>
                                    </div>
                                    <span class="progress-description">
                                        days remaining
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Leave Usage Chart -->
                    <div class="row">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-chart-pie mr-2"></i>
                                        Leave Usage Overview
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="leaveChart" width="400" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-info-circle mr-2"></i>
                                        Leave Policy
                                    </h5>
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
                                                    <td><span class="badge badge-info">{{ $leaveBalance['annual_remaining'] }}</span></td>
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
                                                    <td><span class="badge badge-primary">{{ $leaveBalance['paternity_remaining'] }}</span></td>
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

                    <!-- Quick Actions -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-bolt mr-2"></i>
                                        Quick Actions
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <a href="{{ route('leaves.create') }}" class="btn btn-primary btn-block">
                                                <i class="fas fa-plus mr-2"></i> Submit Leave Request
                                            </a>
                                        </div>
                                        <div class="col-md-4">
                                            <a href="{{ route('leaves.index') }}" class="btn btn-info btn-block">
                                                <i class="fas fa-list mr-2"></i> View Leave History
                                            </a>
                                        </div>
                                        <div class="col-md-4">
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
    // Leave Usage Chart
    const ctx = document.getElementById('leaveChart').getContext('2d');
    const leaveChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Annual Leave', 'Sick Leave', 'Maternity Leave', 'Paternity Leave', 'Other Leave'],
            datasets: [{
                data: [
                    {{ $leaveBalance['annual_used'] }},
                    {{ $leaveBalance['sick_used'] }},
                    {{ $leaveBalance['maternity_used'] }},
                    {{ $leaveBalance['paternity_used'] }},
                    {{ $leaveBalance['other_used'] }}
                ],
                backgroundColor: [
                    '#17a2b8', // info
                    '#ffc107', // warning
                    '#28a745', // success
                    '#007bff', // primary
                    '#6c757d'  // secondary
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
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${label}: ${value} days (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
});
</script>
@endpush

@push('styles')
<style>
.info-box {
    display: block;
    min-height: 100px;
    background: #fff;
    width: 100%;
    box-shadow: 0 1px 1px rgba(0,0,0,0.1);
    border-radius: 2px;
    margin-bottom: 15px;
}

.info-box-icon {
    border-radius: 2px 0 0 2px;
    display: block;
    float: left;
    height: 100px;
    width: 100px;
    text-align: center;
    font-size: 50px;
    line-height: 100px;
    background: rgba(0,0,0,0.2);
}

.info-box-content {
    padding: 10px 15px;
    margin-left: 100px;
}

.info-box-text {
    display: block;
    font-size: 16px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.info-box-number {
    display: block;
    font-weight: bold;
    font-size: 24px;
}

.progress {
    height: 4px;
    margin: 10px 0;
}

.progress-description {
    display: block;
    font-size: 14px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

@media print {
    .card-tools, .btn, .progress {
        display: none !important;
    }
    
    .info-box {
        break-inside: avoid;
    }
}
</style>
@endpush 