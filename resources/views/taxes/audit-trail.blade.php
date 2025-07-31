@extends('layouts.app')

@section('title', 'Tax Audit Trail')

@section('content')
<div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Tax Audit Trail</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('taxes.index') }}">Tax Management</a></li>
                        <li class="breadcrumb-item active">Audit Trail</li>
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
                            <form method="GET" action="{{ route('taxes.audit-trail') }}">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="employee_id">Employee</label>
                                            <select class="form-control" id="employee_id" name="employee_id">
                                                <option value="">All Employees</option>
                                                @foreach(\App\Models\Employee::where('company_id', auth()->user()->company_id)->get() as $employee)
                                                    <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
                                                        {{ $employee->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="period">Tax Period</label>
                                            <input type="month" class="form-control" id="period" name="period" 
                                                   value="{{ request('period') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="action">Action</label>
                                            <select class="form-control" id="action" name="action">
                                                <option value="">All Actions</option>
                                                <option value="calculated" {{ request('action') == 'calculated' ? 'selected' : '' }}>Calculated</option>
                                                <option value="paid" {{ request('action') == 'paid' ? 'selected' : '' }}>Paid</option>
                                                <option value="verified" {{ request('action') == 'verified' ? 'selected' : '' }}>Verified</option>
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
                                                <a href="{{ route('taxes.audit-trail') }}" class="btn btn-secondary">
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

            <!-- Audit Trail Summary -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Audit Trail Summary</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="info-box bg-info">
                                        <span class="info-box-icon"><i class="fas fa-users"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Total Employees</span>
                                            <span class="info-box-number">{{ $auditTrail->count() }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="info-box bg-success">
                                        <span class="info-box-icon"><i class="fas fa-calendar-alt"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Total Periods</span>
                                            <span class="info-box-number">
                                                {{ $auditTrail->sum(function($employee) { return $employee->count(); }) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="info-box bg-warning">
                                        <span class="info-box-icon"><i class="fas fa-calculator"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Total Calculations</span>
                                            <span class="info-box-number">
                                                {{ $auditTrail->sum(function($employee) { 
                                                    return $employee->sum(function($period) { 
                                                        return $period['calculations']; 
                                                    }); 
                                                }) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="info-box bg-primary">
                                        <span class="info-box-icon"><i class="fas fa-history"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Latest Update</span>
                                            <span class="info-box-number">
                                                @php
                                                    $latestUpdate = \App\Models\Tax::where('company_id', auth()->user()->company_id)
                                                        ->orderBy('updated_at', 'desc')
                                                        ->first();
                                                @endphp
                                                @if($latestUpdate)
                                                    {{ \Carbon\Carbon::parse($latestUpdate->updated_at)->format('d M Y') }}
                                                @else
                                                    -
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Audit Trail Details -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Audit Trail Details</h3>
                            <div class="card-tools">
                                <a href="{{ route('taxes.export') }}" 
                                   class="btn btn-sm btn-success">
                                    <i class="fas fa-download"></i> Export
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            @forelse($auditTrail as $employeeId => $employeePeriods)
                                @php
                                    $employee = \App\Models\Employee::find($employeeId);
                                @endphp
                                <div class="timeline">
                                    <div class="time-label">
                                        <span class="bg-primary">{{ $employee->name }}</span>
                                    </div>
                                    
                                    @foreach($employeePeriods as $periodKey => $periodData)
                                        <div>
                                            <i class="fas fa-calculator bg-blue"></i>
                                            <div class="timeline-item">
                                                <span class="time">
                                                    <i class="fas fa-calendar"></i> 
                                                    {{ \Carbon\Carbon::parse($periodData['latest_calculation']->updated_at)->format('d M Y H:i') }}
                                                </span>
                                                <h3 class="timeline-header">
                                                    <strong>{{ $periodData['period'] }}</strong>
                                                    <span class="badge badge-info">{{ $periodData['calculations'] }} calculations</span>
                                                </h3>
                                                <div class="timeline-body">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <h6>Period Summary</h6>
                                                            <ul class="list-unstyled">
                                                                <li><strong>Period:</strong> {{ $periodData['period'] }}</li>
                                                                <li><strong>Calculations:</strong> {{ $periodData['calculations'] }}</li>
                                                                <li><strong>Total Tax Amount:</strong> Rp {{ number_format($periodData['total_tax_amount']) }}</li>
                                                                <li><strong>Status Changes:</strong> 
                                                                    @foreach($periodData['status_changes'] as $status)
                                                                        <span class="badge badge-secondary">{{ ucfirst($status) }}</span>
                                                                    @endforeach
                                                                </li>
                                                            </ul>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <h6>Latest Calculation Details</h6>
                                                            <ul class="list-unstyled">
                                                                <li><strong>Taxable Income:</strong> Rp {{ number_format($periodData['latest_calculation']->taxable_income) }}</li>
                                                                <li><strong>PTKP Status:</strong> {{ $periodData['latest_calculation']->ptkp_status }}</li>
                                                                <li><strong>Tax Amount:</strong> Rp {{ number_format($periodData['latest_calculation']->tax_amount) }}</li>
                                                                <li><strong>Tax Rate:</strong> {{ number_format($periodData['latest_calculation']->tax_rate * 100, 1) }}%</li>
                                                                <li><strong>Current Status:</strong> 
                                                                    @if($periodData['latest_calculation']->status === 'calculated')
                                                                        <span class="badge badge-warning">Calculated</span>
                                                                    @elseif($periodData['latest_calculation']->status === 'paid')
                                                                        <span class="badge badge-success">Paid</span>
                                                                    @elseif($periodData['latest_calculation']->status === 'verified')
                                                                        <span class="badge badge-info">Verified</span>
                                                                    @else
                                                                        <span class="badge badge-secondary">{{ ucfirst($periodData['latest_calculation']->status) }}</span>
                                                                    @endif
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="timeline-footer">
                                                    <a href="{{ route('taxes.show', $periodData['latest_calculation']) }}" class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i> View Details
                                                    </a>
                                                    <a href="{{ route('taxes.edit', $periodData['latest_calculation']) }}" class="btn btn-sm btn-warning">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </a>
                                                    <a href="{{ route('taxes.index') }}?employee_id={{ $employeeId }}&period={{ $periodData['period'] }}" 
                                                       class="btn btn-sm btn-primary">
                                                        <i class="fas fa-list"></i> View All
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @empty
                                <div class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-history fa-3x mb-3"></i>
                                        <p>No audit trail data found</p>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Audit Trail Statistics -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Calculation Frequency</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6">
                                    <div class="info-box bg-success">
                                        <span class="info-box-icon"><i class="fas fa-chart-line"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Most Active Employee</span>
                                            <span class="info-box-number">
                                                @php
                                                    $mostActive = $auditTrail->sortByDesc(function($employee) {
                                                        return $employee->sum(function($period) {
                                                            return $period['calculations'];
                                                        });
                                                    })->first();
                                                @endphp
                                                @if($mostActive)
                                                    {{ \App\Models\Employee::find($auditTrail->keys()->first())->name }}
                                                @else
                                                    -
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="info-box bg-info">
                                        <span class="info-box-icon"><i class="fas fa-calendar-check"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Most Active Period</span>
                                            <span class="info-box-number">
                                                @php
                                                    $mostActivePeriod = collect();
                                                    foreach($auditTrail as $employee) {
                                                        foreach($employee as $period) {
                                                            $mostActivePeriod->push($period['period']);
                                                        }
                                                    }
                                                    $mostActivePeriod = $mostActivePeriod->countBy()->sortDesc()->keys()->first();
                                                @endphp
                                                {{ $mostActivePeriod ?? '-' }}
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
                            <h3 class="card-title">Status Distribution</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6">
                                    <div class="info-box bg-warning">
                                        <span class="info-box-icon"><i class="fas fa-calculator"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Calculated</span>
                                            <span class="info-box-number">
                                                @php
                                                    $calculatedCount = \App\Models\Tax::where('company_id', auth()->user()->company_id)
                                                        ->where('status', 'calculated')
                                                        ->count();
                                                @endphp
                                                {{ $calculatedCount }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="info-box bg-success">
                                        <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Paid</span>
                                            <span class="info-box-number">
                                                @php
                                                    $paidCount = \App\Models\Tax::where('company_id', auth()->user()->company_id)
                                                        ->where('status', 'paid')
                                                        ->count();
                                                @endphp
                                                {{ $paidCount }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-6">
                                    <div class="info-box bg-info">
                                        <span class="info-box-icon"><i class="fas fa-certificate"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Verified</span>
                                            <span class="info-box-number">
                                                @php
                                                    $verifiedCount = \App\Models\Tax::where('company_id', auth()->user()->company_id)
                                                        ->where('status', 'verified')
                                                        ->count();
                                                @endphp
                                                {{ $verifiedCount }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="info-box bg-primary">
                                        <span class="info-box-icon"><i class="fas fa-percentage"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Completion Rate</span>
                                            <span class="info-box-number">
                                                @php
                                                    $totalTaxes = \App\Models\Tax::where('company_id', auth()->user()->company_id)->count();
                                                    $completedTaxes = $paidCount + $verifiedCount;
                                                    $completionRate = $totalTaxes > 0 ? ($completedTaxes / $totalTaxes) * 100 : 0;
                                                @endphp
                                                {{ number_format($completionRate, 1) }}%
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add any additional JavaScript for audit trail functionality
    console.log('Audit trail page loaded');
});
</script>
@endpush
@endsection 