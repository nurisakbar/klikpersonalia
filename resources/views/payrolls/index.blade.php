@extends('layouts.app')

@section('title', 'Payroll Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-money-bill-wave mr-2"></i>
                        Payroll Management
                    </h3>
                    <div class="card-tools">
                        <div class="btn-group" role="group">
                            <a href="{{ route('payrolls.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus mr-1"></i> Generate Payroll
                            </a>
                            <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#generateAllModal">
                                <i class="fas fa-cogs mr-1"></i> Generate All
                            </button>
                            <button type="button" class="btn btn-info btn-sm" onclick="exportPayrolls()">
                                <i class="fas fa-download mr-1"></i> Export
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filter Section -->
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <form method="GET" action="{{ route('payrolls.index') }}" class="form-inline">
                                <div class="form-group mr-2">
                                    <label for="period" class="mr-2">Period:</label>
                                    <select name="period" id="period" class="form-control">
                                        @php
                                            $currentYear = date('Y');
                                            $currentMonth = date('m');
                                            $selectedPeriod = $period ?? $currentYear . '-' . $currentMonth;
                                        @endphp
                                        @for($year = $currentYear - 2; $year <= $currentYear + 1; $year++)
                                            @for($month = 1; $month <= 12; $month++)
                                                @php
                                                    $periodValue = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT);
                                                    $periodLabel = date('F Y', mktime(0, 0, 0, $month, 1, $year));
                                                @endphp
                                                <option value="{{ $periodValue }}" {{ $selectedPeriod == $periodValue ? 'selected' : '' }}>
                                                    {{ $periodLabel }}
                                                </option>
                                            @endfor
                                        @endfor
                                    </select>
                                </div>
                                <div class="form-group mr-2">
                                    <label for="status" class="mr-2">Status:</label>
                                    <select name="status" id="status" class="form-control">
                                        <option value="">All Status</option>
                                        <option value="draft" {{ $status == 'draft' ? 'selected' : '' }}>Draft</option>
                                        <option value="approved" {{ $status == 'approved' ? 'selected' : '' }}>Approved</option>
                                        <option value="paid" {{ $status == 'paid' ? 'selected' : '' }}>Paid</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter mr-1"></i> Filter
                                </button>
                            </form>
                        </div>
                        <div class="col-md-4 text-right">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-secondary" onclick="setPeriod('current')">Current Month</button>
                                <button type="button" class="btn btn-outline-secondary" onclick="setPeriod('previous')">Previous Month</button>
                            </div>
                        </div>
                    </div>

                    <!-- Summary Statistics -->
                    <div class="row">
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3>{{ $summary['total_payrolls'] }}</h3>
                                    <p>Total Payrolls</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-list"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3>{{ $summary['draft_payrolls'] }}</h3>
                                    <p>Draft</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3>{{ $summary['approved_payrolls'] }}</h3>
                                    <p>Approved</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-check"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3>{{ $summary['paid_payrolls'] }}</h3>
                                    <p>Paid</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-check-double"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-primary">
                                <div class="inner">
                                    <h3>Rp {{ number_format($summary['total_salary'], 0, ',', '.') }}</h3>
                                    <p>Total Salary</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-money-bill-wave"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-secondary">
                                <div class="inner">
                                    <h3>Rp {{ number_format($summary['total_overtime'], 0, ',', '.') }}</h3>
                                    <p>Total Overtime</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3>Rp {{ number_format($summary['total_bonus'], 0, ',', '.') }}</h3>
                                    <p>Total Bonus</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-gift"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-danger">
                                <div class="inner">
                                    <h3>Rp {{ number_format($summary['total_deductions'], 0, ',', '.') }}</h3>
                                    <p>Total Deductions</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-minus-circle"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payroll List -->
                    <div class="row">
                        <div class="col-12">
                            @if($payrolls->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Employee</th>
                                                <th>Period</th>
                                                <th>Basic Salary</th>
                                                <th>Overtime</th>
                                                <th>Bonus</th>
                                                <th>Deductions</th>
                                                <th>Total Salary</th>
                                                <th>Status</th>
                                                <th>Generated</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($payrolls as $payroll)
                                                <tr>
                                                    <td>
                                                        <strong>{{ $payroll->employee->name }}</strong><br>
                                                        <small class="text-muted">{{ $payroll->employee->department }}</small>
                                                    </td>
                                                    <td>{{ $payroll->formatted_period }}</td>
                                                    <td>{{ $payroll->formatted_basic_salary }}</td>
                                                    <td>{{ 'Rp ' . number_format($payroll->overtime_pay, 0, ',', '.') }}</td>
                                                    <td>{{ 'Rp ' . number_format($payroll->attendance_bonus, 0, ',', '.') }}</td>
                                                    <td>{{ 'Rp ' . number_format($payroll->deductions + $payroll->leave_deduction, 0, ',', '.') }}</td>
                                                    <td>
                                                        <strong>{{ $payroll->formatted_total_salary }}</strong>
                                                    </td>
                                                    <td>{!! $payroll->status_badge !!}</td>
                                                    <td>
                                                        {{ $payroll->generated_at ? $payroll->generated_at->format('d/m/Y H:i') : '-' }}<br>
                                                        <small class="text-muted">by {{ $payroll->generatedBy->name ?? '-' }}</small>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group" role="group">
                                                            <a href="{{ route('payrolls.show', $payroll->id) }}" class="btn btn-sm btn-info" title="View Details">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            @if($payroll->isPending())
                                                                <a href="{{ route('payrolls.edit', $payroll->id) }}" class="btn btn-sm btn-warning" title="Edit">
                                                                    <i class="fas fa-edit"></i>
                                                                </a>
                                                                <button type="button" class="btn btn-sm btn-success approve-btn" 
                                                                        data-id="{{ $payroll->id }}" 
                                                                        data-name="{{ $payroll->employee->name }}"
                                                                        title="Approve">
                                                                    <i class="fas fa-check"></i>
                                                                </button>
                                                                <button type="button" class="btn btn-sm btn-danger reject-btn" 
                                                                        data-id="{{ $payroll->id }}" 
                                                                        data-name="{{ $payroll->employee->name }}"
                                                                        title="Reject">
                                                                    <i class="fas fa-times"></i>
                                                                </button>
                                                            @endif
                                                            @if($payroll->isPending())
                                                                <button type="button" class="btn btn-sm btn-danger delete-btn" 
                                                                        data-id="{{ $payroll->id }}" 
                                                                        data-name="{{ $payroll->employee->name }} - {{ $payroll->formatted_period }}"
                                                                        title="Delete">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                
                                <div class="d-flex justify-content-center">
                                    {{ $payrolls->links() }}
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-money-bill-wave fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No Payrolls Found</h5>
                                    <p class="text-muted">No payrolls found for the selected criteria.</p>
                                    <a href="{{ route('payrolls.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus mr-1"></i> Generate First Payroll
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Generate All Modal -->
<div class="modal fade" id="generateAllModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Generate Payroll for All Employees</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('payrolls.generate-all') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="generate_month">Month</label>
                        <select name="month" id="generate_month" class="form-control" required>
                            @for($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ $month == $i ? 'selected' : '' }}>
                                    {{ date('F', mktime(0, 0, 0, $i, 1)) }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="generate_year">Year</label>
                        <select name="year" id="generate_year" class="form-control" required>
                            @for($i = date('Y') - 2; $i <= date('Y') + 1; $i++)
                                <option value="{{ $i }}" {{ $year == $i ? 'selected' : '' }}>
                                    {{ $i }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-1"></i>
                        This will generate payroll for all active employees for the selected period.
                        Existing payrolls will be skipped.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-cogs mr-1"></i> Generate All
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Payroll</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this payroll?</p>
                <p><strong id="deletePayrollName"></strong></p>
                <p class="text-warning"><i class="fas fa-exclamation-triangle"></i> This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Yes, Delete Payroll</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Handle delete button click
    $('.delete-btn').click(function() {
        const payrollId = $(this).data('id');
        const payrollName = $(this).data('name');
        
        $('#deletePayrollName').text(payrollName);
        $('#deleteForm').attr('action', '{{ url("payrolls") }}/' + payrollId);
        $('#deleteModal').modal('show');
    });

    // Handle approve button click
    $('.approve-btn').click(function() {
        const payrollId = $(this).data('id');
        const payrollName = $(this).data('name');
        
        if (confirm(`Are you sure you want to approve payroll for ${payrollName}?`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ url("payrolls") }}/' + payrollId + '/approve';
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            
            form.appendChild(csrfToken);
            document.body.appendChild(form);
            form.submit();
        }
    });

    // Handle reject button click
    $('.reject-btn').click(function() {
        const payrollId = $(this).data('id');
        const payrollName = $(this).data('name');
        
        const reason = prompt(`Please provide a reason for rejecting ${payrollName}'s payroll:`);
        if (reason !== null) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ url("payrolls") }}/' + payrollId + '/reject';
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            
            const reasonInput = document.createElement('input');
            reasonInput.type = 'hidden';
            reasonInput.name = 'rejection_reason';
            reasonInput.value = reason;
            
            form.appendChild(csrfToken);
            form.appendChild(reasonInput);
            document.body.appendChild(form);
            form.submit();
        }
    });
});

function setPeriod(period) {
    const today = new Date();
    let selectedPeriod;
    
    if (period === 'current') {
        const year = today.getFullYear();
        const month = String(today.getMonth() + 1).padStart(2, '0');
        selectedPeriod = year + '-' + month;
    } else if (period === 'previous') {
        let year = today.getFullYear();
        let month = today.getMonth();
        if (month === 0) {
            month = 12;
            year--;
        }
        selectedPeriod = year + '-' + String(month).padStart(2, '0');
    }
    
    document.getElementById('period').value = selectedPeriod;
    document.querySelector('form').submit();
}

function exportPayrolls() {
    const period = document.getElementById('period').value;
    
    // Create export form
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("payrolls.export") }}';
    
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = '{{ csrf_token() }}';
    
    const periodInput = document.createElement('input');
    periodInput.type = 'hidden';
    periodInput.name = 'period';
    periodInput.value = period;
    
    const formatInput = document.createElement('input');
    formatInput.type = 'hidden';
    formatInput.name = 'format';
    formatInput.value = 'pdf';
    
    form.appendChild(csrfToken);
    form.appendChild(periodInput);
    form.appendChild(formatInput);
    
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}
</script>
@endpush 