@extends('layouts.app')

@section('title', 'Tax Compliance Report')

@section('content')
<div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Tax Compliance Report</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('taxes.index') }}">Tax Management</a></li>
                        <li class="breadcrumb-item active">Compliance Report</li>
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
                            <form method="GET" action="{{ route('taxes.compliance-report') }}">
                                <div class="row">
                                    <div class="col-md-3">
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
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="compliance_rate">Compliance Rate</label>
                                            <select class="form-control" id="compliance_rate" name="compliance_rate">
                                                <option value="">All Rates</option>
                                                <option value="100" {{ request('compliance_rate') == '100' ? 'selected' : '' }}>100% (Fully Compliant)</option>
                                                <option value="partial" {{ request('compliance_rate') == 'partial' ? 'selected' : '' }}>Partial (1-99%)</option>
                                                <option value="0" {{ request('compliance_rate') == '0' ? 'selected' : '' }}>0% (Non-Compliant)</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>&nbsp;</label>
                                            <div>
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fas fa-search"></i> Filter
                                                </button>
                                                <a href="{{ route('taxes.compliance-report') }}" class="btn btn-secondary">
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
                            <h3>{{ number_format($summary['average_compliance_rate'], 1) }}%</h3>
                            <p>Average Compliance Rate</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-percentage"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>{{ number_format($summary['fully_compliant']) }}</h3>
                            <p>Fully Compliant</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3>{{ number_format($summary['non_compliant']) }}</h3>
                            <p>Non-Compliant</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Compliance Overview -->
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Compliance Distribution ({{ $summary['year'] }})</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="complianceDistributionChart" style="height: 300px;"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Compliance Status</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12">
                                    <div class="info-box bg-success">
                                        <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Fully Compliant</span>
                                            <span class="info-box-number">{{ $summary['fully_compliant'] }}</span>
                                            <div class="progress">
                                                <div class="progress-bar" style="width: {{ ($summary['fully_compliant'] / $summary['total_employees']) * 100 }}%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="info-box bg-warning">
                                        <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Partially Compliant</span>
                                            <span class="info-box-number">{{ $summary['partially_compliant'] }}</span>
                                            <div class="progress">
                                                <div class="progress-bar" style="width: {{ ($summary['partially_compliant'] / $summary['total_employees']) * 100 }}%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="info-box bg-danger">
                                        <span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Non-Compliant</span>
                                            <span class="info-box-number">{{ $summary['non_compliant'] }}</span>
                                            <div class="progress">
                                                <div class="progress-bar" style="width: {{ ($summary['non_compliant'] / $summary['total_employees']) * 100 }}%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Employee Compliance Details -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Employee Compliance Details ({{ $summary['year'] }})</h3>
                            <div class="card-tools">
                                <a href="{{ route('taxes.export') }}?year={{ $summary['year'] }}" 
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
                                        <th>Department</th>
                                        <th>Total Months</th>
                                        <th>Calculated Months</th>
                                        <th>Compliance Rate</th>
                                        <th>Total Tax Amount</th>
                                        <th>Paid Amount</th>
                                        <th>Pending Amount</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($complianceData as $data)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm mr-3">
                                                    @if($data['employee']->avatar)
                                                        <img src="{{ asset('storage/' . $data['employee']->avatar) }}" 
                                                             class="img-circle" alt="Avatar" style="width: 40px; height: 40px;">
                                                    @else
                                                        <div class="img-circle bg-primary d-flex align-items-center justify-content-center" 
                                                             style="width: 40px; height: 40px;">
                                                            <span class="text-white font-weight-bold">
                                                                {{ strtoupper(substr($data['employee']->name, 0, 1)) }}
                                                            </span>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div>
                                                    <div class="font-weight-bold">{{ $data['employee']->name }}</div>
                                                    <small class="text-muted">{{ $data['employee']->position }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $data['employee']->department ?? '-' }}</td>
                                        <td>
                                            <span class="badge badge-info">{{ $data['total_months'] }}</span>
                                        </td>
                                        <td>
                                            <span class="badge badge-primary">{{ $data['calculated_months'] }}</span>
                                        </td>
                                        <td>
                                            @if($data['compliance_rate'] == 100)
                                                <span class="badge badge-success">{{ number_format($data['compliance_rate'], 1) }}%</span>
                                            @elseif($data['compliance_rate'] > 0)
                                                <span class="badge badge-warning">{{ number_format($data['compliance_rate'], 1) }}%</span>
                                            @else
                                                <span class="badge badge-danger">{{ number_format($data['compliance_rate'], 1) }}%</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="font-weight-bold text-primary">
                                                Rp {{ number_format($data['total_tax_amount']) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="font-weight-bold text-success">
                                                Rp {{ number_format($data['paid_amount']) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="font-weight-bold text-warning">
                                                Rp {{ number_format($data['pending_amount']) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($data['compliance_rate'] == 100)
                                                <span class="badge badge-success">Fully Compliant</span>
                                            @elseif($data['compliance_rate'] > 0)
                                                <span class="badge badge-warning">Partially Compliant</span>
                                            @else
                                                <span class="badge badge-danger">Non-Compliant</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('taxes.index') }}?employee_id={{ $data['employee']->id }}&period={{ $summary['year'] }}" 
                                               class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i> View Taxes
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="10" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-users fa-3x mb-3"></i>
                                                <p>No compliance data found for {{ $summary['year'] }}</p>
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

            <!-- Compliance Analysis -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Compliance Analysis</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6">
                                    <div class="info-box bg-success">
                                        <span class="info-box-icon"><i class="fas fa-chart-line"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Compliance Trend</span>
                                            <span class="info-box-number">Improving</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="info-box bg-info">
                                        <span class="info-box-icon"><i class="fas fa-calendar-alt"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Missing Months</span>
                                            <span class="info-box-number">
                                                @php
                                                    $totalMissing = collect($complianceData)->sum(function($data) {
                                                        return $data['total_months'] - $data['calculated_months'];
                                                    });
                                                @endphp
                                                {{ $totalMissing }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-6">
                                    <div class="info-box bg-warning">
                                        <span class="info-box-icon"><i class="fas fa-money-bill-wave"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Total Pending Tax</span>
                                            <span class="info-box-number">
                                                Rp {{ number_format(collect($complianceData)->sum('pending_amount')) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="info-box bg-primary">
                                        <span class="info-box-icon"><i class="fas fa-percentage"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Payment Rate</span>
                                            <span class="info-box-number">
                                                @php
                                                    $totalTax = collect($complianceData)->sum('total_tax_amount');
                                                    $totalPaid = collect($complianceData)->sum('paid_amount');
                                                    $paymentRate = $totalTax > 0 ? ($totalPaid / $totalTax) * 100 : 0;
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
                            <h3 class="card-title">Top Compliant Employees</h3>
                        </div>
                        <div class="card-body">
                            @php
                                $topCompliant = collect($complianceData)
                                    ->where('compliance_rate', 100)
                                    ->sortByDesc('total_tax_amount')
                                    ->take(5);
                            @endphp
                            @foreach($topCompliant as $data)
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <strong>{{ $data['employee']->name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $data['employee']->position }}</small>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-weight-bold text-success">
                                            {{ number_format($data['compliance_rate'], 1) }}% compliant
                                        </div>
                                        <small class="text-muted">
                                            Rp {{ number_format($data['total_tax_amount']) }}
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
    // Compliance Distribution Chart
    const ctx = document.getElementById('complianceDistributionChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Fully Compliant', 'Partially Compliant', 'Non-Compliant'],
            datasets: [{
                label: 'Number of Employees',
                data: [
                    {{ $summary['fully_compliant'] }},
                    {{ $summary['partially_compliant'] }},
                    {{ $summary['non_compliant'] }}
                ],
                backgroundColor: [
                    '#28a745',
                    '#ffc107',
                    '#dc3545'
                ],
                borderWidth: 1,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
});
</script>
@endpush
@endsection 