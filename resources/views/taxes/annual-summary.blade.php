@extends('layouts.app')

@section('title', 'Annual Tax Summary')

@section('content')
<div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Annual Tax Summary</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('taxes.index') }}">Tax Management</a></li>
                        <li class="breadcrumb-item active">Annual Summary</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Filters -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Filters</h3>
                        </div>
                        <div class="card-body">
                            <form method="GET" action="{{ route('taxes.annual-summary') }}">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="year">Year</label>
                                            <select class="form-control" id="year" name="year">
                                                @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                                                    <option value="{{ $y }}" {{ request('year', date('Y')) == $y ? 'selected' : '' }}>
                                                        {{ $y }}
                                                    </option>
                                                @endfor
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>&nbsp;</label>
                                            <div>
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fas fa-search"></i> Filter
                                                </button>
                                                <a href="{{ route('taxes.annual-summary') }}" class="btn btn-secondary">
                                                    <i class="fas fa-times"></i> Clear
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Annual Summary Cards -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{ number_format($summary['total_employees']) }}</h3>
                            <p>Total Employees ({{ $summary['year'] }})</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>Rp {{ number_format($summary['total_taxable_income']) }}</h3>
                            <p>Total Taxable Income</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>Rp {{ number_format($summary['total_tax_amount']) }}</h3>
                            <p>Total Tax Amount</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-calculator"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3>{{ number_format($summary['average_tax_rate'] * 100, 1) }}%</h3>
                            <p>Average Tax Rate</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-percentage"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Monthly Tax Trend ({{ $summary['year'] }})</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="monthlyTrendChart" style="height: 300px;"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Tax Distribution</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="taxDistributionChart" style="height: 300px;"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Monthly Breakdown -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Monthly Breakdown ({{ $summary['year'] }})</h3>
                            <div class="card-tools">
                                <a href="{{ route('taxes.export') }}?year={{ $summary['year'] }}" 
                                   class="btn btn-sm btn-success">
                                    <i class="fas fa-download"></i> Export Annual Report
                                </a>
                            </div>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover text-nowrap">
                                <thead>
                                    <tr>
                                        <th>Month</th>
                                        <th>Employees</th>
                                        <th>Taxable Income</th>
                                        <th>Tax Amount</th>
                                        <th>Average Tax Rate</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $months = [
                                            '01' => 'January', '02' => 'February', '03' => 'March',
                                            '04' => 'April', '05' => 'May', '06' => 'June',
                                            '07' => 'July', '08' => 'August', '09' => 'September',
                                            '10' => 'October', '11' => 'November', '12' => 'December'
                                        ];
                                    @endphp
                                    
                                    @foreach($months as $monthNum => $monthName)
                                        @php
                                            $monthKey = $summary['year'] . '-' . $monthNum;
                                            $monthData = $summary['monthly_data'][$monthKey] ?? null;
                                        @endphp
                                        <tr>
                                            <td>
                                                <strong>{{ $monthName }} {{ $summary['year'] }}</strong>
                                            </td>
                                            <td>
                                                @if($monthData)
                                                    <span class="badge badge-info">{{ $monthData['total_employees'] }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($monthData)
                                                    Rp {{ number_format($monthData['total_taxable_income']) }}
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($monthData)
                                                    <span class="font-weight-bold text-primary">
                                                        Rp {{ number_format($monthData['total_tax_amount']) }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($monthData)
                                                    {{ number_format($monthData['average_tax_rate'] * 100, 1) }}%
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($monthData)
                                                    <a href="{{ route('taxes.monthly-report') }}?month={{ $monthKey }}" 
                                                       class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i> View Details
                                                    </a>
                                                @else
                                                    <span class="text-muted">No data</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Yearly Statistics -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Yearly Statistics</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6">
                                    <div class="info-box bg-primary">
                                        <span class="info-box-icon"><i class="fas fa-calendar"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Months with Data</span>
                                            <span class="info-box-number">{{ $summary['monthly_data']->count() }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="info-box bg-success">
                                        <span class="info-box-icon"><i class="fas fa-chart-line"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Average Monthly Tax</span>
                                            <span class="info-box-number">
                                                Rp {{ number_format($summary['monthly_data']->avg('total_tax_amount')) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-6">
                                    <div class="info-box bg-warning">
                                        <span class="info-box-icon"><i class="fas fa-users"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Avg Employees/Month</span>
                                            <span class="info-box-number">
                                                {{ number_format($summary['monthly_data']->avg('total_employees'), 1) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="info-box bg-info">
                                        <span class="info-box-icon"><i class="fas fa-percentage"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Avg Tax Rate</span>
                                            <span class="info-box-number">
                                                {{ number_format($summary['monthly_data']->avg('average_tax_rate') * 100, 1) }}%
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Top Months by Tax Amount</h3>
                        </div>
                        <div class="card-body">
                            @php
                                $topMonths = $summary['monthly_data']->sortByDesc('total_tax_amount')->take(5);
                            @endphp
                            @foreach($topMonths as $monthKey => $monthData)
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <strong>{{ $months[substr($monthKey, 5, 2)] }} {{ $summary['year'] }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $monthData['total_employees'] }} employees</small>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-weight-bold text-primary">
                                            Rp {{ number_format($monthData['total_tax_amount']) }}
                                        </div>
                                        <small class="text-muted">{{ number_format($monthData['average_tax_rate'] * 100, 1) }}% avg rate</small>
                                    </div>
                                </div>
                                @if(!$loop->last)
                                    <hr>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Monthly Trend Chart
    const trendCtx = document.getElementById('monthlyTrendChart').getContext('2d');
    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: [
                @foreach($summary['monthly_data']->keys() as $monthKey)
                    '{{ $months[substr($monthKey, 5, 2)] }}',
                @endforeach
            ],
            datasets: [{
                label: 'Tax Amount',
                data: [
                    @foreach($summary['monthly_data'] as $monthData)
                        {{ $monthData['total_tax_amount'] }},
                    @endforeach
                ],
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4
            }, {
                label: 'Taxable Income',
                data: [
                    @foreach($summary['monthly_data'] as $monthData)
                        {{ $monthData['total_taxable_income'] }},
                    @endforeach
                ],
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                borderWidth: 2,
                fill: false,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                        }
                    }
                }
            }
        }
    });

    // Tax Distribution Chart
    const distCtx = document.getElementById('taxDistributionChart').getContext('2d');
    new Chart(distCtx, {
        type: 'doughnut',
        data: {
            labels: [
                @foreach($summary['monthly_data']->keys() as $monthKey)
                    '{{ $months[substr($monthKey, 5, 2)] }}',
                @endforeach
            ],
            datasets: [{
                data: [
                    @foreach($summary['monthly_data'] as $monthData)
                        {{ $monthData['total_tax_amount'] }},
                    @endforeach
                ],
                backgroundColor: [
                    '#007bff', '#28a745', '#ffc107', '#dc3545', '#6f42c1',
                    '#fd7e14', '#20c997', '#e83e8c', '#6c757d', '#17a2b8',
                    '#ffc107', '#28a745'
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
                        boxWidth: 12
                    }
                }
            }
        }
    });
});
</script>
@endpush
@endsection 