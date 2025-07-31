@extends('layouts.app')

@section('title', 'Tax Payment Report')

@section('content')
<div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Tax Payment Report</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('taxes.index') }}">Tax Management</a></li>
                        <li class="breadcrumb-item active">Payment Report</li>
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
                            <form method="GET" action="{{ route('taxes.payment-report') }}">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="period">Tax Period</label>
                                            <input type="month" class="form-control" id="period" name="period" 
                                                   value="{{ request('period') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="payment_date">Payment Date</label>
                                            <input type="date" class="form-control" id="payment_date" name="payment_date" 
                                                   value="{{ request('payment_date') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>&nbsp;</label>
                                            <div>
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fas fa-search"></i> Filter
                                                </button>
                                                <a href="{{ route('taxes.payment-report') }}" class="btn btn-secondary">
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

            <!-- Summary Cards -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>{{ number_format($summary['total_payments']) }}</h3>
                            <p>Total Payments</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-primary">
                        <div class="inner">
                            <h3>Rp {{ number_format($summary['total_paid_amount']) }}</h3>
                            <p>Total Paid Amount</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>Rp {{ number_format($summary['average_payment']) }}</h3>
                            <p>Average Payment</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>{{ $summary['payment_by_month']->count() }}</h3>
                            <p>Months with Payments</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Payment Trend</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="paymentTrendChart" style="height: 300px;"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Payment Distribution</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="paymentDistributionChart" style="height: 300px;"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Details Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Payment Details</h3>
                            <div class="card-tools">
                                <a href="{{ route('taxes.export') }}?status=paid" 
                                   class="btn btn-sm btn-success">
                                    <i class="fas fa-download"></i> Export
                                </a>
                            </div>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover text-nowrap">
                                <thead>
                                    <tr>
                                        <th>Employee</th>
                                        <th>Tax Period</th>
                                        <th>Tax Amount</th>
                                        <th>Payment Date</th>
                                        <th>Payment Method</th>
                                        <th>Reference No</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($taxes as $tax)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm mr-3">
                                                    @if($tax->employee->avatar)
                                                        <img src="{{ asset('storage/' . $tax->employee->avatar) }}" 
                                                             class="img-circle" alt="Avatar" style="width: 40px; height: 40px;">
                                                    @else
                                                        <div class="img-circle bg-primary d-flex align-items-center justify-content-center" 
                                                             style="width: 40px; height: 40px;">
                                                            <span class="text-white font-weight-bold">
                                                                {{ strtoupper(substr($tax->employee->name, 0, 1)) }}
                                                            </span>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div>
                                                    <div class="font-weight-bold">{{ $tax->employee->name }}</div>
                                                    <small class="text-muted">{{ $tax->employee->position }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $tax->tax_period }}</td>
                                        <td>
                                            <span class="font-weight-bold text-success">
                                                Rp {{ number_format($tax->tax_amount) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($tax->payment_date)
                                                <span class="badge badge-success">
                                                    {{ \Carbon\Carbon::parse($tax->payment_date)->format('d M Y') }}
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($tax->payment_method)
                                                <span class="badge badge-info">{{ $tax->payment_method }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($tax->payment_reference)
                                                <code>{{ $tax->payment_reference }}</code>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-success">Paid</span>
                                        </td>
                                        <td>
                                            <a href="{{ route('taxes.show', $tax) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('taxes.edit', $tax) }}" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-money-bill-wave fa-3x mb-3"></i>
                                                <p>No payment data found</p>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Statistics -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Payment Statistics</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6">
                                    <div class="info-box bg-success">
                                        <span class="info-box-icon"><i class="fas fa-calendar-check"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Latest Payment</span>
                                            <span class="info-box-number">
                                                @if($taxes->count() > 0)
                                                    {{ \Carbon\Carbon::parse($taxes->first()->payment_date)->format('d M Y') }}
                                                @else
                                                    -
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="info-box bg-primary">
                                        <span class="info-box-icon"><i class="fas fa-chart-bar"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Highest Payment</span>
                                            <span class="info-box-number">
                                                Rp {{ number_format($taxes->max('tax_amount')) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-6">
                                    <div class="info-box bg-warning">
                                        <span class="info-box-icon"><i class="fas fa-chart-line"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Lowest Payment</span>
                                            <span class="info-box-number">
                                                Rp {{ number_format($taxes->min('tax_amount')) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="info-box bg-info">
                                        <span class="info-box-icon"><i class="fas fa-percentage"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Payment Rate</span>
                                            <span class="info-box-number">
                                                @php
                                                    $totalTaxes = \App\Models\Tax::where('company_id', auth()->user()->company_id)->count();
                                                    $paidTaxes = $taxes->count();
                                                    $paymentRate = $totalTaxes > 0 ? ($paidTaxes / $totalTaxes) * 100 : 0;
                                                @endphp
                                                {{ number_format($paymentRate, 1) }}%
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
                            <h3 class="card-title">Top Payments by Amount</h3>
                        </div>
                        <div class="card-body">
                            @php
                                $topPayments = $taxes->sortByDesc('tax_amount')->take(5);
                            @endphp
                            @foreach($topPayments as $tax)
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <strong>{{ $tax->employee->name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $tax->tax_period }}</small>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-weight-bold text-success">
                                            Rp {{ number_format($tax->tax_amount) }}
                                        </div>
                                        <small class="text-muted">
                                            {{ \Carbon\Carbon::parse($tax->payment_date)->format('d M Y') }}
                                        </small>
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
    // Payment Trend Chart
    const trendCtx = document.getElementById('paymentTrendChart').getContext('2d');
    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: [
                @foreach($summary['payment_by_month']->keys() as $monthKey)
                    '{{ \Carbon\Carbon::parse($monthKey . '-01')->format('M Y') }}',
                @endforeach
            ],
            datasets: [{
                label: 'Payment Amount',
                data: [
                    @foreach($summary['payment_by_month'] as $amount)
                        {{ $amount }},
                    @endforeach
                ],
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                borderWidth: 3,
                fill: true,
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

    // Payment Distribution Chart
    const distCtx = document.getElementById('paymentDistributionChart').getContext('2d');
    new Chart(distCtx, {
        type: 'doughnut',
        data: {
            labels: [
                @foreach($summary['payment_by_month']->keys() as $monthKey)
                    '{{ \Carbon\Carbon::parse($monthKey . '-01')->format('M Y') }}',
                @endforeach
            ],
            datasets: [{
                data: [
                    @foreach($summary['payment_by_month'] as $amount)
                        {{ $amount }},
                    @endforeach
                ],
                backgroundColor: [
                    '#28a745', '#007bff', '#ffc107', '#dc3545', '#6f42c1',
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