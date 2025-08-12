@extends('layouts.app')

@section('title', 'BPJS Reports')

@section('content')
<div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>BPJS Reports</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('bpjs.index') }}">BPJS Management</a></li>
                        <li class="breadcrumb-item active">BPJS Reports</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Summary Cards -->
            <div class="row">
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-info"><i class="fas fa-heartbeat"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">BPJS Kesehatan</span>
                            <span class="info-box-number">{{ $summary['kesehatan_count'] }}</span>
                            <div class="progress">
                                <div class="progress-bar" style="width: 100%"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-success"><i class="fas fa-briefcase"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">BPJS Ketenagakerjaan</span>
                            <span class="info-box-number">{{ $summary['ketenagakerjaan_count'] }}</span>
                            <div class="progress">
                                <div class="progress-bar" style="width: 100%"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-warning"><i class="fas fa-user"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Employee Contribution</span>
                            <span class="info-box-number">Rp {{ number_format($summary['total_employee_contribution'], 0, ',', '.') }}</span>
                            <div class="progress">
                                <div class="progress-bar" style="width: 100%"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-primary"><i class="fas fa-building"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Company Contribution</span>
                            <span class="info-box-number">Rp {{ number_format($summary['total_company_contribution'], 0, ',', '.') }}</span>
                            <div class="progress">
                                <div class="progress-bar" style="width: 100%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">BPJS Report - {{ \Carbon\Carbon::parse($period)->format('F Y') }}</h3>
                            <div class="card-tools">
                                <a href="{{ route('bpjs.export', ['period' => $period, 'type' => $type]) }}" class="btn btn-success btn-sm">
                                    <i class="fas fa-download"></i> Export CSV
                                </a>
                                <a href="{{ route('bpjs.index') }}" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-arrow-left"></i> Back to BPJS
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Filters -->
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <form method="GET" action="{{ route('bpjs.report') }}" class="form-inline">
                                        <div class="form-group mr-2">
                                            <label for="period" class="mr-1">Period:</label>
                                            <select name="period" id="period" class="form-control form-control-sm">
                                                @foreach($periods as $p)
                                                    <option value="{{ $p }}" {{ $period == $p ? 'selected' : '' }}>
                                                        {{ \Carbon\Carbon::parse($p)->format('F Y') }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group mr-2">
                                            <label for="type" class="mr-1">Type:</label>
                                            <select name="type" id="type" class="form-control form-control-sm">
                                                <option value="both" {{ $type == 'both' ? 'selected' : '' }}>Both Types</option>
                                                <option value="kesehatan" {{ $type == 'kesehatan' ? 'selected' : '' }}>BPJS Kesehatan</option>
                                                <option value="ketenagakerjaan" {{ $type == 'ketenagakerjaan' ? 'selected' : '' }}>BPJS Ketenagakerjaan</option>
                                            </select>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-sm mr-2">
                                            <i class="fas fa-search"></i> Filter
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- Summary Table -->
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th colspan="2" class="text-center">Summary for {{ \Carbon\Carbon::parse($period)->format('F Y') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><strong>Total Records:</strong></td>
                                                    <td>{{ $bpjsRecords->count() }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>BPJS Kesehatan Records:</strong></td>
                                                    <td>{{ $summary['kesehatan_count'] }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>BPJS Ketenagakerjaan Records:</strong></td>
                                                    <td>{{ $summary['ketenagakerjaan_count'] }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Total Employee Contribution:</strong></td>
                                                    <td><strong>Rp {{ number_format($summary['total_employee_contribution'], 0, ',', '.') }}</strong></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Total Company Contribution:</strong></td>
                                                    <td><strong>Rp {{ number_format($summary['total_company_contribution'], 0, ',', '.') }}</strong></td>
                                                </tr>
                                                <tr class="table-active">
                                                    <td><strong>Total Contribution:</strong></td>
                                                    <td><strong>Rp {{ number_format($summary['total_contribution'], 0, ',', '.') }}</strong></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Detailed Records Table -->
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Employee</th>
                                            <th>Type</th>
                                            <th>Base Salary</th>
                                            <th>Employee Contribution</th>
                                            <th>Company Contribution</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($bpjsRecords as $bpjs)
                                            <tr>
                                                <td>
                                                    <strong>{{ $bpjs->employee->name }}</strong><br>
                                                    <small class="text-muted">{{ $bpjs->employee->employee_id }}</small>
                                                </td>
                                                <td>
                                                    @if($bpjs->bpjs_type === 'kesehatan')
                                                        <span class="badge badge-info">
                                                            <i class="fas fa-heartbeat"></i> Kesehatan
                                                        </span>
                                                    @else
                                                        <span class="badge badge-success">
                                                            <i class="fas fa-briefcase"></i> Ketenagakerjaan
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>Rp {{ number_format($bpjs->base_salary, 0, ',', '.') }}</td>
                                                <td>Rp {{ number_format($bpjs->employee_contribution, 0, ',', '.') }}</td>
                                                <td>Rp {{ number_format($bpjs->company_contribution, 0, ',', '.') }}</td>
                                                <td>
                                                    <strong>Rp {{ number_format($bpjs->total_contribution, 0, ',', '.') }}</strong>
                                                </td>
                                                <td>
                                                    @switch($bpjs->status)
                                                        @case('pending')
                                                            <span class="badge badge-warning">Pending</span>
                                                            @break
                                                        @case('calculated')
                                                            <span class="badge badge-info">Calculated</span>
                                                            @break
                                                        @case('paid')
                                                            <span class="badge badge-success">Paid</span>
                                                            @break
                                                        @case('verified')
                                                            <span class="badge badge-primary">Verified</span>
                                                            @break
                                                    @endswitch
                                                </td>
                                                <td>
                                                    <a href="{{ route('bpjs.show', $bpjs) }}" 
                                                       class="btn btn-sm btn-info" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center">No BPJS records found for the selected period and type.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <!-- Charts Section -->
                            @if($bpjsRecords->count() > 0)
                                <div class="row mt-4">
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-header">
                                                <h3 class="card-title">Contribution Distribution</h3>
                                            </div>
                                            <div class="card-body">
                                                <canvas id="contributionChart" style="height: 300px;"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-header">
                                                <h3 class="card-title">BPJS Type Distribution</h3>
                                            </div>
                                            <div class="card-body">
                                                <canvas id="typeChart" style="height: 300px;"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Auto-submit form when filters change
    $('#period, #type').change(function() {
        $(this).closest('form').submit();
    });

    @if($bpjsRecords->count() > 0)
        // Contribution Distribution Chart
        var contributionCtx = document.getElementById('contributionChart').getContext('2d');
        var contributionChart = new Chart(contributionCtx, {
            type: 'doughnut',
            data: {
                labels: ['Employee Contribution', 'Company Contribution'],
                datasets: [{
                    data: [
                        {{ $summary['total_employee_contribution'] }},
                        {{ $summary['total_company_contribution'] }}
                    ],
                    backgroundColor: [
                        '#17a2b8',
                        '#28a745'
                    ],
                    borderWidth: 2
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

        // BPJS Type Distribution Chart
        var typeCtx = document.getElementById('typeChart').getContext('2d');
        var typeChart = new Chart(typeCtx, {
            type: 'pie',
            data: {
                labels: ['BPJS Kesehatan', 'BPJS Ketenagakerjaan'],
                datasets: [{
                    data: [
                        {{ $summary['kesehatan_count'] }},
                        {{ $summary['ketenagakerjaan_count'] }}
                    ],
                    backgroundColor: [
                        '#17a2b8',
                        '#28a745'
                    ],
                    borderWidth: 2
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
    @endif
});
</script>
@endpush 