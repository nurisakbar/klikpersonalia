@extends('layouts.app')

@section('title', 'Detail Payroll - Aplikasi Payroll KlikMedis')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-eye mr-2"></i>
                        Detail Payroll
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('payrolls.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left mr-1"></i> Back to Payrolls
                        </a>
                        @if($payroll->status === 'draft')
                            <a href="{{ route('payrolls.edit', $payroll->id) }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit mr-1"></i> Edit
                            </a>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Employee Information -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-user mr-2"></i>
                                        Employee Information
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td width="30%"><strong>Name:</strong></td>
                                            <td>{{ $payroll->employee->name }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Employee ID:</strong></td>
                                            <td>{{ $payroll->employee->employee_id }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Department:</strong></td>
                                            <td>{{ $payroll->employee->department }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Position:</strong></td>
                                            <td>{{ $payroll->employee->position }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Period:</strong></td>
                                            <td>{{ $payroll->formatted_period }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Status:</strong></td>
                                            <td>{!! $payroll->status_badge !!}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Payroll Details -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-money-bill-wave mr-2"></i>
                                        Payroll Details
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td width="30%"><strong>Basic Salary:</strong></td>
                                            <td class="text-right">{{ $payroll->formatted_basic_salary }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Allowance:</strong></td>
                                            <td class="text-right text-success">+ Rp {{ number_format($payroll->allowance, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Overtime:</strong></td>
                                            <td class="text-right text-success">+ Rp {{ number_format($payroll->overtime, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Bonus:</strong></td>
                                            <td class="text-right text-success">+ Rp {{ number_format($payroll->bonus, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Deductions:</strong></td>
                                            <td class="text-right text-danger">- Rp {{ number_format($payroll->deduction, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Tax:</strong></td>
                                            <td class="text-right text-danger">- Rp {{ number_format($payroll->tax_amount, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>BPJS:</strong></td>
                                            <td class="text-right text-danger">- Rp {{ number_format($payroll->bpjs_amount, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr class="table-primary">
                                            <td><strong>Total Salary:</strong></td>
                                            <td class="text-right"><strong>{{ $payroll->formatted_total_salary }}</strong></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Information -->
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-info-circle mr-2"></i>
                                        Additional Information
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td width="30%"><strong>Generated By:</strong></td>
                                            <td>{{ $payroll->generatedBy->name ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Generated At:</strong></td>
                                            <td>{{ $payroll->generated_at ? $payroll->generated_at->format('d/m/Y H:i') : '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Created At:</strong></td>
                                            <td>{{ $payroll->created_at->format('d/m/Y H:i') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Updated At:</strong></td>
                                            <td>{{ $payroll->updated_at->format('d/m/Y H:i') }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-sticky-note mr-2"></i>
                                        Notes
                                    </h5>
                                </div>
                                <div class="card-body">
                                    @if($payroll->notes)
                                        <p>{{ $payroll->notes }}</p>
                                    @else
                                        <p class="text-muted">No notes available.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body text-center">
                                    @if($payroll->status === 'draft')
                                        <button type="button" class="btn btn-success approve-btn" 
                                                data-id="{{ $payroll->id }}" 
                                                data-name="{{ $payroll->employee->name }}">
                                            <i class="fas fa-check mr-1"></i> Approve Payroll
                                        </button>
                                        <button type="button" class="btn btn-danger reject-btn" 
                                                data-id="{{ $payroll->id }}" 
                                                data-name="{{ $payroll->employee->name }}">
                                            <i class="fas fa-times mr-1"></i> Reject Payroll
                                        </button>
                                        <button type="button" class="btn btn-danger delete-btn" 
                                                data-id="{{ $payroll->id }}" 
                                                data-name="{{ $payroll->employee->name }} - {{ $payroll->formatted_period }}">
                                            <i class="fas fa-trash mr-1"></i> Delete Payroll
                                        </button>
                                    @endif
                                    
                                    @if($payroll->status === 'approved')
                                        <button type="button" class="btn btn-info mark-paid-btn" 
                                                data-id="{{ $payroll->id }}" 
                                                data-name="{{ $payroll->employee->name }}">
                                            <i class="fas fa-money-bill-wave mr-1"></i> Mark as Paid
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
$(document).ready(function() {
    // Approve Payroll
    $('.approve-btn').click(function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        
        SwalHelper.confirm(
            'Approve Payroll',
            `Are you sure you want to approve payroll for ${name}?`,
            'Ya, Approve!'
        ).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/payrolls/${id}/approve`,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            SwalHelper.success('Success!', response.message);
                            setTimeout(() => {
                                window.location.reload();
                            }, 1500);
                        } else {
                            SwalHelper.error('Error!', response.message);
                        }
                    },
                    error: function() {
                        SwalHelper.error('Error!', 'Failed to approve payroll.');
                    }
                });
            }
        });
    });

    // Reject Payroll
    $('.reject-btn').click(function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        
        SwalHelper.confirm(
            'Reject Payroll',
            `Are you sure you want to reject payroll for ${name}?`,
            'Ya, Reject!'
        ).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/payrolls/${id}/reject`,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            SwalHelper.success('Success!', response.message);
                            setTimeout(() => {
                                window.location.reload();
                            }, 1500);
                        } else {
                            SwalHelper.error('Error!', response.message);
                        }
                    },
                    error: function() {
                        SwalHelper.error('Error!', 'Failed to reject payroll.');
                    }
                });
            }
        });
    });

    // Delete Payroll
    $('.delete-btn').click(function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        
        SwalHelper.confirm(
            'Delete Payroll',
            `Are you sure you want to delete payroll for ${name}?`,
            'Ya, Hapus!'
        ).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/payrolls/${id}`,
                    method: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            SwalHelper.success('Success!', response.message);
                            setTimeout(() => {
                                window.location.href = '{{ route("payrolls.index") }}';
                            }, 1500);
                        } else {
                            SwalHelper.error('Error!', response.message);
                        }
                    },
                    error: function() {
                        SwalHelper.error('Error!', 'Failed to delete payroll.');
                    }
                });
            }
        });
    });

    // Mark as Paid
    $('.mark-paid-btn').click(function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        
        SwalHelper.confirm(
            'Mark as Paid',
            `Are you sure you want to mark payroll for ${name} as paid?`,
            'Ya, Mark as Paid!'
        ).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/payrolls/${id}/mark-paid`,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            SwalHelper.success('Success!', response.message);
                            setTimeout(() => {
                                window.location.reload();
                            }, 1500);
                        } else {
                            SwalHelper.error('Error!', response.message);
                        }
                    },
                    error: function() {
                        SwalHelper.error('Error!', 'Failed to mark payroll as paid.');
                    }
                });
            }
        });
    });
});
</script>
@endpush
