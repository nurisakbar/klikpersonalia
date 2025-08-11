@extends('layouts.app')

@section('title', 'BPJS Management')

@section('content')
<div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>BPJS Management</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">BPJS Management</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Info boxes -->
            <div class="row">
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-info"><i class="fas fa-heartbeat"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">BPJS Kesehatan</span>
                            <span class="info-box-number">{{ $bpjsRecords->where('bpjs_type', 'kesehatan')->count() }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-success"><i class="fas fa-briefcase"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">BPJS Ketenagakerjaan</span>
                            <span class="info-box-number">{{ $bpjsRecords->where('bpjs_type', 'ketenagakerjaan')->count() }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-warning"><i class="fas fa-clock"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Pending</span>
                            <span class="info-box-number">{{ $bpjsRecords->where('status', 'pending')->count() }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-success"><i class="fas fa-check"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Paid</span>
                            <span class="info-box-number">{{ $bpjsRecords->where('status', 'paid')->count() }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">BPJS Records</h3>
                            <div class="card-tools">
                                <a href="{{ route('bpjs.create') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus"></i> New BPJS Record
                                </a>
                                <a href="{{ route('bpjs.report') }}" class="btn btn-info btn-sm">
                                    <i class="fas fa-chart-bar"></i> Reports
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Filters -->
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <form method="GET" action="{{ route('bpjs.index') }}" class="form-inline">
                                        <div class="form-group mr-2">
                                            <label for="period" class="mr-1">Period:</label>
                                            <select name="period" id="period" class="form-control form-control-sm">
                                                <option value="">All Periods</option>
                                                @foreach($periods as $period)
                                                    <option value="{{ $period }}" {{ request('period') == $period ? 'selected' : '' }}>
                                                        {{ \Carbon\Carbon::parse($period)->format('F Y') }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group mr-2">
                                            <label for="type" class="mr-1">Type:</label>
                                            <select name="type" id="type" class="form-control form-control-sm">
                                                <option value="">All Types</option>
                                                <option value="kesehatan" {{ request('type') == 'kesehatan' ? 'selected' : '' }}>BPJS Kesehatan</option>
                                                <option value="ketenagakerjaan" {{ request('type') == 'ketenagakerjaan' ? 'selected' : '' }}>BPJS Ketenagakerjaan</option>
                                            </select>
                                        </div>
                                        <div class="form-group mr-2">
                                            <label for="status" class="mr-1">Status:</label>
                                            <select name="status" id="status" class="form-control form-control-sm">
                                                <option value="">All Status</option>
                                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                                <option value="calculated" {{ request('status') == 'calculated' ? 'selected' : '' }}>Calculated</option>
                                                <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                                                <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Verified</option>
                                            </select>
                                        </div>
                                        <div class="form-group mr-2">
                                            <label for="employee_id" class="mr-1">Employee:</label>
                                            <select name="employee_id" id="employee_id" class="form-control form-control-sm">
                                                <option value="">All Employees</option>
                                                @foreach($employees as $employee)
                                                    <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
                                                        {{ $employee->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-sm mr-2">
                                            <i class="fas fa-search"></i> Filter
                                        </button>
                                        <a href="{{ route('bpjs.index') }}" class="btn btn-secondary btn-sm">
                                            <i class="fas fa-times"></i> Clear
                                        </a>
                                    </form>
                                </div>
                            </div>

                            <!-- Bulk Calculation Form -->
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <div class="card card-outline card-info">
                                        <div class="card-header">
                                            <h3 class="card-title">Bulk BPJS Calculation</h3>
                                        </div>
                                        <div class="card-body">
                                            <form method="POST" action="{{ route('bpjs.calculateForPayroll') }}">
                                                @csrf
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="payroll_period">Payroll Period:</label>
                                                            <input type="month" name="payroll_period" id="payroll_period" 
                                                                   class="form-control" value="{{ date('Y-m') }}" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="bpjs_type">BPJS Type:</label>
                                                            <select name="bpjs_type" id="bpjs_type" class="form-control" required>
                                                                <option value="both">Both (Kesehatan & Ketenagakerjaan)</option>
                                                                <option value="kesehatan">BPJS Kesehatan Only</option>
                                                                <option value="ketenagakerjaan">BPJS Ketenagakerjaan Only</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label>&nbsp;</label>
                                                            <button type="submit" class="btn btn-success btn-block">
                                                                <i class="fas fa-calculator"></i> Calculate BPJS for All Employees
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- BPJS Records Table -->
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Employee</th>
                                            <th>Type</th>
                                            <th>Period</th>
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
                                                <td>{{ \Carbon\Carbon::parse($bpjs->bpjs_period)->format('F Y') }}</td>
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
                                                    <div class="btn-group">
                                                        <a href="{{ route('bpjs.show', $bpjs) }}" 
                                                           class="btn btn-sm btn-info" title="View">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="{{ route('bpjs.edit', $bpjs) }}" 
                                                           class="btn btn-sm btn-warning" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <form method="POST" action="{{ route('bpjs.destroy', $bpjs) }}" 
                                                              style="display: inline;" 
                                                              onsubmit="return confirm('Are you sure you want to delete this BPJS record?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="9" class="text-center">No BPJS records found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="d-flex justify-content-center">
                                {{ $bpjsRecords->appends(request()->query())->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-submit form when filters change
    $('#period, #type, #status, #employee_id').change(function() {
        $(this).closest('form').submit();
    });
});
</script>
@endpush 