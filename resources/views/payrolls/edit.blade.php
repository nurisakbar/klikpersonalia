@extends('layouts.app')

@section('title', 'Perbarui Payroll - Aplikasi Payroll KlikMedis')
@section('page-title', 'Perbarui Payroll')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('payrolls.index') }}">Payroll</a></li>
<li class="breadcrumb-item active">Perbarui</li>
@endsection

@push('css')
<style>
    /* Custom Select2 Bootstrap 4 Styling */
    .select2-container--bootstrap4 .select2-selection--single {
        height: calc(1.5em + 0.75rem + 2px) !important;
        padding: 0.375rem 0.75rem !important;
        font-size: 1rem !important;
        font-weight: 400 !important;
        line-height: 1.5 !important;
        color: #495057 !important;
        background-color: #fff !important;
        border: 1px solid #ced4da !important;
        border-radius: 0.25rem !important;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out !important;
    }
    
    .select2-container--bootstrap4 .select2-selection--single:focus {
        border-color: #80bdff !important;
        outline: 0 !important;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25) !important;
    }
    
    .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
        color: #495057 !important;
        line-height: 1.5 !important;
        padding-left: 0 !important;
    }
    
    .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow {
        height: calc(1.5em + 0.75rem) !important;
        right: 0.75rem !important;
    }
    
    .select2-container--bootstrap4 .select2-dropdown {
        border: 1px solid #ced4da !important;
        border-radius: 0.25rem !important;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
    }
    
    .select2-container--bootstrap4 .select2-results__option--highlighted[aria-selected] {
        background-color: #007bff !important;
        color: white !important;
    }

    .calculation-card {
        background: #6c757d;
        color: white;
        border: none;
    }

    .calculation-card .card-header {
        background: #5a6268;
        border-bottom: 1px solid #495057;
    }

    .info-card {
        background: #f8f9fa;
        border-left: 4px solid #007bff;
    }

    .policy-card {
        background: #fff3cd;
        border-left: 4px solid #ffc107;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('payrolls.update', $payroll->id) }}" method="POST" id="payrollForm">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <!-- Employee Selection -->
                                <div class="form-group">
                                    <label for="employee_id">Employee <span class="text-danger">*</span></label>
                                    <select name="employee_id" id="employee_id" class="form-control @error('employee_id') is-invalid @enderror" required>
                                        <option value="">Select Employee</option>
                                        @foreach($employees as $employee)
                                            <option value="{{ $employee->id }}" 
                                                    data-salary="{{ $employee->basic_salary }}"
                                                    {{ old('employee_id', $payroll->employee_id) == $employee->id ? 'selected' : '' }}>
                                                {{ $employee->name }} - {{ $employee->department }} ({{ $employee->employee_id }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('employee_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Period Display (Read-only) -->
                                <div class="form-group">
                                    <label for="period">Period</label>
                                    <input type="text" class="form-control" value="{{ $payroll->period }}" readonly>
                                    <small class="form-text text-muted">Period cannot be changed after creation</small>
                                </div>

                                <!-- Status Selection -->
                                <div class="form-group">
                                    <label for="status">Status <span class="text-danger">*</span></label>
                                    <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                                        @foreach($statuses as $value => $label)
                                            <option value="{{ $value }}" {{ old('status', $payroll->status) == $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Basic Salary -->
                                <div class="form-group">
                                    <label for="basic_salary">Basic Salary <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp</span>
                                        </div>
                                        <input type="number" name="basic_salary" id="basic_salary" 
                                               class="form-control @error('basic_salary') is-invalid @enderror" 
                                               value="{{ old('basic_salary', $payroll->basic_salary) }}" 
                                               step="1000" min="0" required>
                                    </div>
                                    @error('basic_salary')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Allowance -->
                                <div class="form-group">
                                    <label for="allowance">Allowance</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp</span>
                                        </div>
                                        <input type="number" name="allowance" id="allowance" 
                                               class="form-control @error('allowance') is-invalid @enderror" 
                                               value="{{ old('allowance', $payroll->allowance) }}" 
                                               step="1000" min="0">
                                    </div>
                                    @error('allowance')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Deduction -->
                                <div class="form-group">
                                    <label for="deduction">Deduction</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp</span>
                                        </div>
                                        <input type="number" name="deduction" id="deduction" 
                                               class="form-control @error('deduction') is-invalid @enderror" 
                                               value="{{ old('deduction', $payroll->deduction) }}" 
                                               step="1000" min="0">
                                    </div>
                                    @error('deduction')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Notes -->
                                <div class="form-group">
                                    <label for="notes">Notes</label>
                                    <textarea name="notes" id="notes" rows="3" 
                                              class="form-control @error('notes') is-invalid @enderror" 
                                              placeholder="Enter any additional notes...">{{ old('notes', $payroll->notes) }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <!-- Salary Calculation Preview -->
                                <div class="card calculation-card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-calculator mr-2"></i>
                                            Salary Calculation Preview
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div id="salaryCalculation">
                                            <div class="row">
                                                <div class="col-12">
                                                    <div class="d-flex justify-content-between mb-2">
                                                        <span>Basic Salary:</span>
                                                        <span>Rp {{ number_format($payroll->basic_salary, 0, ',', '.') }}</span>
                                                    </div>
                                                    <div class="d-flex justify-content-between mb-2">
                                                        <span>Allowance:</span>
                                                        <span>Rp {{ number_format($payroll->allowance, 0, ',', '.') }}</span>
                                                    </div>
                                                    <div class="d-flex justify-content-between mb-2">
                                                        <span>Deduction:</span>
                                                        <span>Rp {{ number_format($payroll->deduction, 0, ',', '.') }}</span>
                                                    </div>
                                                    <hr class="my-3">
                                                    <div class="d-flex justify-content-between">
                                                        <strong>Total Salary:</strong>
                                                        <strong>Rp {{ number_format($payroll->total_salary, 0, ',', '.') }}</strong>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Information Card -->
                                <div class="card info-card mt-3">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-info-circle mr-2"></i>
                                            Information
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-12">
                                                <h6><i class="fas fa-clock text-info mr-2"></i>Overtime Rates:</h6>
                                                <ul class="list-unstyled ml-3">
                                                    <li><i class="fas fa-circle text-info mr-2"></i>Regular: 1.5x hourly rate</li>
                                                    <li><i class="fas fa-circle text-warning mr-2"></i>Holiday/Weekend: 2.0x hourly rate</li>
                                                    <li><i class="fas fa-circle text-danger mr-2"></i>Emergency: 2.5x hourly rate</li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-12">
                                                <h6><i class="fas fa-gift text-success mr-2"></i>Attendance Bonus:</h6>
                                                <ul class="list-unstyled ml-3">
                                                    <li><i class="fas fa-circle text-success mr-2"></i>95%+ attendance: 5% bonus</li>
                                                    <li><i class="fas fa-circle text-info mr-2"></i>90%+ attendance: 3% bonus</li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-12">
                                                <h6><i class="fas fa-calendar-minus text-warning mr-2"></i>Leave Deductions:</h6>
                                                <ul class="list-unstyled ml-3">
                                                    <li><i class="fas fa-circle text-warning mr-2"></i>Non-annual leaves: Daily rate deduction</li>
                                                    <li><i class="fas fa-circle text-success mr-2"></i>Annual leaves: No deduction</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary" id="submitBtn">
                                        <i class="fas fa-save mr-1"></i> Update Payroll
                                    </button>
                                    <a href="{{ route('payrolls.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-times mr-1"></i> Cancel
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<!-- Global SweetAlert Component -->
@include('components.sweet-alert')

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(function () {
    // Setup CSRF token for AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Initialize Select2
    $('#employee_id').select2({
        theme: 'bootstrap4',
        placeholder: 'Select Employee',
        allowClear: true
    });

    $('#status').select2({
        theme: 'bootstrap4',
        placeholder: 'Select Status',
        allowClear: true
    });

    // Auto-calculate total salary
    function calculateTotal() {
        const basicSalary = parseFloat($('#basic_salary').val()) || 0;
        const allowance = parseFloat($('#allowance').val()) || 0;
        const deduction = parseFloat($('#deduction').val()) || 0;

        const totalSalary = basicSalary + allowance - deduction;

        // Update display
        const calculationHtml = `
            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Basic Salary:</span>
                        <span>Rp ${basicSalary.toLocaleString('id-ID')}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Allowance:</span>
                        <span>Rp ${allowance.toLocaleString('id-ID')}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Deduction:</span>
                        <span>Rp ${deduction.toLocaleString('id-ID')}</span>
                    </div>
                    <hr class="my-3">
                    <div class="d-flex justify-content-between">
                        <strong>Total Salary:</strong>
                        <strong>Rp ${totalSalary.toLocaleString('id-ID')}</strong>
                    </div>
                </div>
            </div>
        `;
        $('#salaryCalculation').html(calculationHtml);
    }

    // Bind calculation to input changes
    $('#basic_salary, #allowance, #deduction').on('input', calculateTotal);

    // Auto-fill basic salary when employee is selected
    $('#employee_id').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const salary = selectedOption.data('salary');
        if (salary) {
            $('#basic_salary').val(salary);
            calculateTotal();
        }
    });

    // Handle form submission with AJAX
    $('#payrollForm').on('submit', function(e) {
        e.preventDefault();
        
        const basicSalary = parseFloat($('#basic_salary').val()) || 0;
        if (basicSalary <= 0) {
            SwalHelper.error('Error!', 'Basic salary must be greater than 0');
            return false;
        }

        // Show loading
        $('#submitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Updating...');

        // Prepare form data
        let formData = new FormData(this);

        // Send AJAX request
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            errorHandled: true,
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    SwalHelper.success('Berhasil!', response.message, 2000);
                    setTimeout(() => {
                        window.location.href = '{{ route("payrolls.index") }}';
                    }, 2000);
                } else {
                    SwalHelper.error('Gagal!', response.message);
                    $('#submitBtn').prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Update Payroll');
                }
            },
            error: function(xhr) {
                let message = 'Terjadi kesalahan saat update payroll';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    let errors = xhr.responseJSON.errors;
                    let errorMessages = [];
                    for (let field in errors) {
                        errorMessages.push(errors[field][0]);
                    }
                    message = errorMessages.join('\n');
                }
                
                SwalHelper.error('Error!', message);
                $('#submitBtn').prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Update Payroll');
            }
        });
    });

    // Initial calculation
    calculateTotal();
});
</script>
@endpush
