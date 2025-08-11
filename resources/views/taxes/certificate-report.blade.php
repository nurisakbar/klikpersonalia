@extends('layouts.app')

@section('title', 'Tax Certificate Report')

@section('content')
<div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Tax Certificate Report</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('taxes.index') }}">Tax Management</a></li>
                        <li class="breadcrumb-item active">Certificate Report</li>
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
                            <form method="GET" action="{{ route('taxes.certificate-report') }}">
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
                                            <label for="certificate_type">Certificate Type</label>
                                            <select class="form-control" id="certificate_type" name="certificate_type">
                                                <option value="">All Types</option>
                                                <option value="A1" {{ request('certificate_type') == 'A1' ? 'selected' : '' }}>A1</option>
                                                <option value="A2" {{ request('certificate_type') == 'A2' ? 'selected' : '' }}>A2</option>
                                                <option value="1721" {{ request('certificate_type') == '1721' ? 'selected' : '' }}>1721</option>
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
                                                <a href="{{ route('taxes.certificate-report') }}" class="btn btn-secondary">
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
                            <h3>{{ number_format($summary['total_certificates']) }}</h3>
                            <p>Total Certificates</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-certificate"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>Rp {{ number_format($summary['total_verified_amount']) }}</h3>
                            <p>Total Verified Amount</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-check-double"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>{{ number_format($summary['certificates_by_type']['A1']) }}</h3>
                            <p>A1 Certificates</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3>{{ number_format($summary['certificates_by_type']['1721']) }}</h3>
                            <p>1721 Certificates</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-file-invoice"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Certificate Type Distribution -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Certificate Type Distribution</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="certificateTypeChart" style="height: 300px;"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Certificate Status Overview</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6">
                                    <div class="info-box bg-success">
                                        <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Verified</span>
                                            <span class="info-box-number">{{ $summary['total_certificates'] }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="info-box bg-warning">
                                        <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Pending Verification</span>
                                            <span class="info-box-number">0</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-6">
                                    <div class="info-box bg-info">
                                        <span class="info-box-icon"><i class="fas fa-file-alt"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">A2 Certificates</span>
                                            <span class="info-box-number">{{ $summary['certificates_by_type']['A2'] }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="info-box bg-primary">
                                        <span class="info-box-icon"><i class="fas fa-percentage"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Verification Rate</span>
                                            <span class="info-box-number">100%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Certificate Details Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Certificate Details</h3>
                            <div class="card-tools">
                                <a href="{{ route('taxes.export') }}?status=verified" 
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
                                        <th>Certificate Type</th>
                                        <th>Certificate No</th>
                                        <th>Tax Amount</th>
                                        <th>Verification Date</th>
                                        <th>Verified By</th>
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
                                            @if($tax->certificate_type)
                                                <span class="badge badge-info">{{ $tax->certificate_type }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($tax->certificate_number)
                                                <code>{{ $tax->certificate_number }}</code>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="font-weight-bold text-primary">
                                                Rp {{ number_format($tax->tax_amount) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($tax->verified_at)
                                                <span class="badge badge-success">
                                                    {{ \Carbon\Carbon::parse($tax->verified_at)->format('d M Y') }}
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($tax->verified_by)
                                                <span class="badge badge-secondary">{{ $tax->verified_by }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-success">Verified</span>
                                        </td>
                                        <td>
                                            <a href="{{ route('taxes.show', $tax) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('taxes.edit', $tax) }}" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @if($tax->certificate_type)
                                                <button class="btn btn-sm btn-success" onclick="downloadCertificate('{{ $tax->id }}')">
                                                    <i class="fas fa-download"></i>
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-certificate fa-3x mb-3"></i>
                                                <p>No certificate data found</p>
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

            <!-- Certificate Statistics -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Certificate Statistics</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6">
                                    <div class="info-box bg-info">
                                        <span class="info-box-icon"><i class="fas fa-calendar-check"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Latest Verification</span>
                                            <span class="info-box-number">
                                                @if($taxes->count() > 0)
                                                    {{ \Carbon\Carbon::parse($taxes->first()->verified_at)->format('d M Y') }}
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
                                            <span class="info-box-text">Highest Tax Amount</span>
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
                                            <span class="info-box-text">Average Tax Amount</span>
                                            <span class="info-box-number">
                                                Rp {{ number_format($taxes->avg('tax_amount')) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="info-box bg-success">
                                        <span class="info-box-icon"><i class="fas fa-users"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Unique Employees</span>
                                            <span class="info-box-number">
                                                {{ $taxes->unique('employee_id')->count() }}
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
                            <h3 class="card-title">Certificate Type Breakdown</h3>
                        </div>
                        <div class="card-body">
                            @foreach($summary['certificates_by_type'] as $type => $count)
                                @if($count > 0)
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div>
                                            <strong>Certificate {{ $type }}</strong>
                                            <br>
                                            <small class="text-muted">
                                                @if($type == 'A1')
                                                    Annual Tax Certificate
                                                @elseif($type == 'A2')
                                                    Monthly Tax Certificate
                                                @elseif($type == '1721')
                                                    Annual Tax Report
                                                @endif
                                            </small>
                                        </div>
                                        <div class="text-right">
                                            <div class="font-weight-bold text-primary">
                                                {{ $count }} certificates
                                            </div>
                                            <small class="text-muted">
                                                Rp {{ number_format($taxes->where('certificate_type', $type)->sum('tax_amount')) }}
                                            </small>
                                        </div>
                                    </div>
                                    @if(!$loop->last)
                                        <hr>
                                    @endif
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
    // Certificate Type Chart
    const ctx = document.getElementById('certificateTypeChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['A1 Certificates', 'A2 Certificates', '1721 Certificates'],
            datasets: [{
                data: [
                    {{ $summary['certificates_by_type']['A1'] }},
                    {{ $summary['certificates_by_type']['A2'] }},
                    {{ $summary['certificates_by_type']['1721'] }}
                ],
                backgroundColor: [
                    '#007bff',
                    '#28a745',
                    '#dc3545'
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

function downloadCertificate(taxId) {
    // Implement certificate download functionality
    alert('Certificate download functionality will be implemented here for tax ID: ' + taxId);
}
</script>
@endpush
@endsection 