@extends('layouts.app')

@section('title', 'Monthly Tax Report')

@section('content')
<div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Monthly Tax Report</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('taxes.index') }}">Tax Management</a></li>
                        <li class="breadcrumb-item active">Monthly Report</li>
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
                            <form method="GET" action="{{ route('taxes.monthly-report') }}">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="month">Month</label>
                                            <input type="month" class="form-control" id="month" name="month" 
                                                   value="{{ request('month', date('Y-m')) }}">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>&nbsp;</label>
                                            <div>
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fas fa-search"></i> Filter
                                                </button>
                                                <a href="{{ route('taxes.monthly-report') }}" class="btn btn-secondary">
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
                            <p>Total Employees</p>
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

            <!-- Payment Status -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Payment Status</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6">
                                    <div class="info-box bg-success">
                                        <span class="info-box-icon"><i class="fas fa-check"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Paid Tax</span>
                                            <span class="info-box-number">Rp {{ number_format($summary['paid_tax']) }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="info-box bg-warning">
                                        <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Pending Tax</span>
                                            <span class="info-box-number">Rp {{ number_format($summary['pending_tax']) }}</span>
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
                            <h3 class="card-title">Tax Distribution</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="taxDistributionChart" style="height: 200px;"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tax Details Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Tax Details for {{ $summary['month'] }}</h3>
                            <div class="card-tools">
                                <a href="{{ route('taxes.export') }}?period={{ $summary['month'] }}" 
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
                                        <th>Taxable Income</th>
                                        <th>PTKP Status</th>
                                        <th>PTKP Amount</th>
                                        <th>Taxable Base</th>
                                        <th>Tax Amount</th>
                                        <th>Tax Rate</th>
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
                                        <td>Rp {{ number_format($tax->taxable_income) }}</td>
                                        <td>
                                            <span class="badge badge-info">{{ $tax->ptkp_status }}</span>
                                        </td>
                                        <td>Rp {{ number_format($tax->ptkp_amount) }}</td>
                                        <td>Rp {{ number_format($tax->taxable_base) }}</td>
                                        <td>
                                            <span class="font-weight-bold text-primary">
                                                Rp {{ number_format($tax->tax_amount) }}
                                            </span>
                                        </td>
                                        <td>{{ number_format($tax->tax_rate * 100, 1) }}%</td>
                                        <td>
                                            @if($tax->status === 'calculated')
                                                <span class="badge badge-warning">Calculated</span>
                                            @elseif($tax->status === 'paid')
                                                <span class="badge badge-success">Paid</span>
                                            @elseif($tax->status === 'verified')
                                                <span class="badge badge-info">Verified</span>
                                            @else
                                                <span class="badge badge-secondary">{{ ucfirst($tax->status) }}</span>
                                            @endif
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
                                        <td colspan="10" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                                <p>No tax data found for {{ $summary['month'] }}</p>
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
        </div>
    </section>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tax Distribution Chart
    const ctx = document.getElementById('taxDistributionChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Paid Tax', 'Pending Tax'],
            datasets: [{
                data: [
                    {{ $summary['paid_tax'] }},
                    {{ $summary['pending_tax'] }}
                ],
                backgroundColor: [
                    '#28a745',
                    '#ffc107'
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
                    position: 'bottom'
                }
            }
        }
    });
});
</script>
@endpush
@endsection 