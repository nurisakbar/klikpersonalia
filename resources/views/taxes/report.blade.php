@extends('layouts.app')

@section('title', 'Tax Report')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar"></i> Tax Report
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('taxes.export') }}?{{ request()->getQueryString() }}" class="btn btn-success btn-sm">
                            <i class="fas fa-download"></i> Export to Excel
                        </a>
                        <a href="{{ route('taxes.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to Tax List
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="period">Tax Period</label>
                            <input type="month" class="form-control" id="period" name="period" 
                                   value="{{ request('period') }}" onchange="applyFilters()">
                        </div>
                        <div class="col-md-4">
                            <label for="status">Status</label>
                            <select class="form-control" id="status" name="status" onchange="applyFilters()">
                                <option value="">All Status</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="calculated" {{ request('status') == 'calculated' ? 'selected' : '' }}>Calculated</option>
                                <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                                <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Verified</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>&nbsp;</label>
                            <div>
                                <button type="button" class="btn btn-secondary" onclick="clearFilters()">
                                    <i class="fas fa-times"></i> Clear Filters
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Summary Statistics -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-info">
                                    <i class="fas fa-users"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Employees</span>
                                    <span class="info-box-number">{{ $summary['total_employees'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning">
                                    <i class="fas fa-money-bill"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Taxable Income</span>
                                    <span class="info-box-number">Rp {{ number_format($summary['total_taxable_income'], 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-danger">
                                    <i class="fas fa-coins"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Tax Amount</span>
                                    <span class="info-box-number">Rp {{ number_format($summary['total_tax_amount'], 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-success">
                                    <i class="fas fa-percentage"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Average Tax Rate</span>
                                    <span class="info-box-number">{{ number_format($summary['average_tax_rate'] * 100, 1) }}%</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tax Report Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Employee</th>
                                    <th>Tax Period</th>
                                    <th>Taxable Income</th>
                                    <th>PTKP Status</th>
                                    <th>PTKP Amount</th>
                                    <th>Taxable Base</th>
                                    <th>Tax Amount</th>
                                    <th>Tax Rate</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($taxes as $index => $tax)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <strong>{{ $tax->employee->name }}</strong><br>
                                            <small class="text-muted">{{ $tax->employee->employee_id }}</small>
                                        </td>
                                        <td>
                                            <span class="badge badge-info">{{ $tax->tax_period }}</span>
                                        </td>
                                        <td class="text-right">
                                            <strong>Rp {{ number_format($tax->taxable_income, 0, ',', '.') }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge badge-secondary">{{ $tax->ptkp_status }}</span>
                                        </td>
                                        <td class="text-right">
                                            Rp {{ number_format($tax->ptkp_amount, 0, ',', '.') }}
                                        </td>
                                        <td class="text-right">
                                            <strong>Rp {{ number_format($tax->taxable_base, 0, ',', '.') }}</strong>
                                        </td>
                                        <td class="text-right">
                                            <strong class="text-danger">Rp {{ number_format($tax->tax_amount, 0, ',', '.') }}</strong>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-warning">{{ number_format($tax->tax_rate * 100, 1) }}%</span>
                                        </td>
                                        <td class="text-center">
                                            @switch($tax->status)
                                                @case('pending')
                                                    <span class="badge badge-secondary">Pending</span>
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
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center">
                                            <div class="alert alert-info">
                                                <i class="fas fa-info-circle"></i> No tax data found for the selected criteria.
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if($taxes->count() > 0)
                            <tfoot>
                                <tr class="table-active">
                                    <td colspan="3"><strong>TOTAL</strong></td>
                                    <td class="text-right"><strong>Rp {{ number_format($taxes->sum('taxable_income'), 0, ',', '.') }}</strong></td>
                                    <td></td>
                                    <td class="text-right"><strong>Rp {{ number_format($taxes->sum('ptkp_amount'), 0, ',', '.') }}</strong></td>
                                    <td class="text-right"><strong>Rp {{ number_format($taxes->sum('taxable_base'), 0, ',', '.') }}</strong></td>
                                    <td class="text-right"><strong class="text-danger">Rp {{ number_format($taxes->sum('tax_amount'), 0, ',', '.') }}</strong></td>
                                    <td class="text-center"><strong>{{ number_format($taxes->avg('tax_rate') * 100, 1) }}%</strong></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>

                    <!-- Status Summary -->
                    @if($taxes->count() > 0)
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Status Summary</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="info-box bg-secondary">
                                                <span class="info-box-icon">
                                                    <i class="fas fa-clock"></i>
                                                </span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Pending</span>
                                                    <span class="info-box-number">{{ $taxes->where('status', 'pending')->count() }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info-box bg-info">
                                                <span class="info-box-icon">
                                                    <i class="fas fa-calculator"></i>
                                                </span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Calculated</span>
                                                    <span class="info-box-number">{{ $taxes->where('status', 'calculated')->count() }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="info-box bg-success">
                                                <span class="info-box-icon">
                                                    <i class="fas fa-check"></i>
                                                </span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Paid</span>
                                                    <span class="info-box-number">{{ $taxes->where('status', 'paid')->count() }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info-box bg-primary">
                                                <span class="info-box-icon">
                                                    <i class="fas fa-shield-alt"></i>
                                                </span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Verified</span>
                                                    <span class="info-box-number">{{ $taxes->where('status', 'verified')->count() }}</span>
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
                                    <h5 class="card-title">Tax Bracket Distribution</h5>
                                </div>
                                <div class="card-body">
                                    @php
                                        $bracketDistribution = $taxes->groupBy('tax_bracket')->map->count();
                                    @endphp
                                    @foreach($bracketDistribution as $bracket => $count)
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span>{{ $bracket }}</span>
                                            <span class="badge badge-info">{{ $count }} employees</span>
                                        </div>
                                    @endforeach
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

<script>
function applyFilters() {
    const period = document.getElementById('period').value;
    const status = document.getElementById('status').value;
    
    let url = new URL(window.location);
    
    if (period) url.searchParams.set('period', period);
    else url.searchParams.delete('period');
    
    if (status) url.searchParams.set('status', status);
    else url.searchParams.delete('status');
    
    window.location.href = url.toString();
}

function clearFilters() {
    window.location.href = '{{ route("taxes.report") }}';
}
</script>
@endsection 