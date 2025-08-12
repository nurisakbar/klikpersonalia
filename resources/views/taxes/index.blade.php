@extends('layouts.app')

@section('title', 'Tax Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-calculator"></i> Tax Management
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('taxes.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> New Tax Calculation
                        </a>
                        <a href="{{ route('taxes.report') }}" class="btn btn-info btn-sm">
                            <i class="fas fa-chart-bar"></i> Tax Report
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="period">Tax Period</label>
                            <input type="month" class="form-control" id="period" name="period" 
                                   value="{{ request('period') }}" onchange="applyFilters()">
                        </div>
                        <div class="col-md-3">
                            <label for="employee_id">Employee</label>
                            <select class="form-control" id="employee_id" name="employee_id" onchange="applyFilters()">
                                <option value="">All Employees</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
                                        {{ $employee->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="status">Status</label>
                            <select class="form-control" id="status" name="status" onchange="applyFilters()">
                                <option value="">All Status</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="calculated" {{ request('status') == 'calculated' ? 'selected' : '' }}>Calculated</option>
                                <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                                <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Verified</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>&nbsp;</label>
                            <div>
                                <button type="button" class="btn btn-secondary" onclick="clearFilters()">
                                    <i class="fas fa-times"></i> Clear Filters
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Bulk Actions -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Bulk Tax Calculation</h5>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('taxes.calculate-for-payroll') }}" method="POST" class="form-inline">
                                        @csrf
                                        <div class="form-group mr-3">
                                            <label for="month" class="mr-2">Month:</label>
                                            <select name="month" id="month" class="form-control" required>
                                                @for($i = 1; $i <= 12; $i++)
                                                    <option value="{{ $i }}">{{ date('F', mktime(0, 0, 0, $i, 1)) }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                        <div class="form-group mr-3">
                                            <label for="year" class="mr-2">Year:</label>
                                            <select name="year" id="year" class="form-control" required>
                                                @for($i = date('Y'); $i >= date('Y') - 5; $i--)
                                                    <option value="{{ $i }}">{{ $i }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-calculator"></i> Calculate Tax for All Employees
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tax List -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Tax Period</th>
                                    <th>Taxable Income</th>
                                    <th>PTKP Status</th>
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
                                            <strong>{{ $tax->employee->name }}</strong><br>
                                            <small class="text-muted">{{ $tax->employee->employee_id }}</small>
                                        </td>
                                        <td>
                                            <span class="badge badge-info">{{ $tax->tax_period }}</span>
                                        </td>
                                        <td>
                                            <strong>Rp {{ number_format($tax->taxable_income, 0, ',', '.') }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge badge-secondary">{{ $tax->ptkp_status }}</span><br>
                                            <small class="text-muted">Rp {{ number_format($tax->ptkp_amount, 0, ',', '.') }}</small>
                                        </td>
                                        <td>
                                            <strong class="text-danger">Rp {{ number_format($tax->tax_amount, 0, ',', '.') }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge badge-warning">{{ number_format($tax->tax_rate * 100, 1) }}%</span>
                                        </td>
                                        <td>
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
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('taxes.show', $tax) }}" class="btn btn-sm btn-info" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('taxes.edit', $tax) }}" class="btn btn-sm btn-warning" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('taxes.destroy', $tax) }}" method="POST" class="d-inline" 
                                                      onsubmit="return confirm('Are you sure you want to delete this tax calculation?')">
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
                                        <td colspan="8" class="text-center">
                                            <div class="alert alert-info">
                                                <i class="fas fa-info-circle"></i> No tax calculations found.
                                                <br>
                                                <a href="{{ route('taxes.create') }}" class="btn btn-primary btn-sm mt-2">
                                                    Create First Tax Calculation
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $taxes->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function applyFilters() {
    const period = document.getElementById('period').value;
    const employeeId = document.getElementById('employee_id').value;
    const status = document.getElementById('status').value;
    
    let url = new URL(window.location);
    
    if (period) url.searchParams.set('period', period);
    else url.searchParams.delete('period');
    
    if (employeeId) url.searchParams.set('employee_id', employeeId);
    else url.searchParams.delete('employee_id');
    
    if (status) url.searchParams.set('status', status);
    else url.searchParams.delete('status');
    
    window.location.href = url.toString();
}

function clearFilters() {
    window.location.href = '{{ route("taxes.index") }}';
}
</script>
@endsection 