@extends('layouts.app')

@section('title', 'Tambah Payroll - Aplikasi Payroll KlikMedis')

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
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-plus mr-2"></i>
                        Generate New Payroll
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('payrolls.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left mr-1"></i> Back to Payrolls
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('payrolls.store') }}" method="POST" id="payrollForm">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <!-- Employee Selection -->
                                <div class="form-group">
                                    <label for="employee_id">Employee <span class="text-danger">*</span></label>
                                    <!-- Debug: {{ count($employees) }} employees available for company: {{ auth()->user()->company_id }} -->
                                    <select name="employee_id" id="employee_id" class="form-control @error('employee_id') is-invalid @enderror" required>
                                        <option value="">Select Employee</option>
                                        @foreach($employees as $employee)
                                            <option value="{{ $employee->id }}" 
                                                    data-salary="{{ $employee->basic_salary }}"
                                                    {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                                                {{ $employee->name }} - {{ $employee->department }} ({{ $employee->employee_id }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('employee_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Period Selection -->
                                <div class="form-group">
                                    <label for="period">Period <span class="text-danger">*</span></label>
                                    <select name="period" id="period" class="form-control @error('period') is-invalid @enderror" required>
                                        @php
                                            $currentYear = date('Y');
                                            $currentMonth = date('m');
                                            $selectedPeriod = $currentPeriod ?? $currentYear . '-' . $currentMonth;
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
                                    @error('period')
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
                                               value="{{ old('basic_salary') }}" 
                                               min="0" step="1000" required>
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
                                               value="{{ old('allowance', 0) }}" 
                                               min="0" step="1000">
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
                                               value="{{ old('deduction', 0) }}" 
                                               min="0" step="1000">
                                    </div>
                                    @error('deduction')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Notes -->
                                <div class="form-group">
                                    <label for="notes">Notes</label>
                                    <textarea name="notes" id="notes" rows="3" 
                                              class="form-control @error('notes') is-invalid @enderror">{{ old('notes') }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <!-- Payroll Preview -->
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">
                                            <i class="fas fa-calculator mr-2"></i>
                                            Payroll Preview
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div id="payrollPreview">
                                            <div class="text-center text-muted py-4">
                                                <i class="fas fa-user fa-3x mb-3"></i>
                                                <p>Select an employee to see payroll preview</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Payroll Policy Info -->
                                <div class="card mt-3">
                                    <div class="card-header">
                                        <h5 class="card-title">
                                            <i class="fas fa-info-circle mr-2"></i>
                                            Payroll Policy
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-12">
                                                <h6>Overtime Rates:</h6>
                                                <ul class="list-unstyled">
                                                    <li><i class="fas fa-circle text-info"></i> Regular: 1.5x hourly rate</li>
                                                    <li><i class="fas fa-circle text-warning"></i> Holiday/Weekend: 2.0x hourly rate</li>
                                                    <li><i class="fas fa-circle text-danger"></i> Emergency: 2.5x hourly rate</li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-12">
                                                <h6>Attendance Bonus:</h6>
                                                <ul class="list-unstyled">
                                                    <li><i class="fas fa-circle text-success"></i> 95%+ attendance: 5% bonus</li>
                                                    <li><i class="fas fa-circle text-info"></i> 90%+ attendance: 3% bonus</li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-12">
                                                <h6>Leave Deductions:</h6>
                                                <ul class="list-unstyled">
                                                    <li><i class="fas fa-circle text-warning"></i> Non-annual leaves: Daily rate deduction</li>
                                                    <li><i class="fas fa-circle text-success"></i> Annual leaves: No deduction</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save mr-1"></i> Generate Payroll
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
<script>
$(document).ready(function() {
    console.log('Payroll create page loaded');
    console.log('Employee select element:', $('#employee_id').length);
    console.log('Employee options count:', $('#employee_id option').length);
    console.log('CSRF Token:', '{{ csrf_token() }}');
    console.log('User company ID:', '{{ auth()->user()->company_id }}');
    console.log('User name:', '{{ auth()->user()->name }}');
    
    // Initialize select2 for employees (using static data)
    $('#employee_id').select2({
        theme: 'bootstrap4',
        placeholder: 'Pilih Karyawan',
        allowClear: true,
        minimumInputLength: 0,
        width: '100%',
        dropdownParent: $('#employee_id').parent()
    });
    
    console.log('Select2 initialized');

    // Update basic salary when employee is selected
    $('#employee_id').on('select2:select', function (e) {
        const selectedOption = $(this).find('option:selected');
        const salary = selectedOption.data('salary');
        if (salary) {
            $('#basic_salary').val(salary);
        }
        updatePayrollPreview();
    });

    // Update preview when any field changes
    // Bind change to actual inputs used in this form
    $('#basic_salary, #allowance, #deduction, #period').change(function() {
        updatePayrollPreview();
    });

    function updatePayrollPreview() {
        const employeeId = $('#employee_id').val();
        const period = $('#period').val();
        const [yearStr, monthStr] = period ? period.split('-') : [null, null];
        const year = parseInt(yearStr);
        const month = parseInt(monthStr);
        const basicSalary = parseFloat($('#basic_salary').val()) || 0;
        const allowances = parseFloat($('#allowance').val()) || 0;
        const deductions = parseFloat($('#deduction').val()) || 0;
        
        console.log('Parsed values:', { period, yearStr, monthStr, year, month });

        if (!employeeId || !month || !year) {
            $('#payrollPreview').html(`
                <div class="text-center text-muted py-4">
                    <i class="fas fa-user fa-3x mb-3"></i>
                    <p>Select an employee to see payroll preview</p>
                </div>
            `);
            return;
        }

        // Show loading
        $('#payrollPreview').html(`
            <div class="text-center py-4">
                <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                <p class="mt-2">Calculating payroll...</p>
            </div>
        `);

        // Debug data being sent
        const requestData = {
            _token: '{{ csrf_token() }}',
            employee_id: employeeId,
            month: month,
            year: year,
            basic_salary: basicSalary,
            allowances: allowances,
            deductions: deductions
        };
        console.log('Sending data:', requestData);
        
        // Fetch payroll calculation from server
        $.ajax({
            url: '{{ route("payrolls.calculate") }}',
            method: 'POST',
            errorHandled: true, // Mark as manually handled
            data: requestData,
            success: function(response) {
                if (response.success) {
                    const data = response.data;
                    $('#payrollPreview').html(`
                        <div class="row">
                            <div class="col-12">
                                <h6 class="text-primary">${data.employee_name}</h6>
                                <p class="text-muted">${data.period}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <table class="table table-sm">
                                    <tbody>
                                        <tr>
                                            <td>Basic Salary:</td>
                                            <td class="text-right">Rp ${formatNumber(data.basic_salary)}</td>
                                        </tr>
                                        <tr>
                                            <td>Overtime Pay:</td>
                                            <td class="text-right text-success">+ Rp ${formatNumber(data.overtime_pay)}</td>
                                        </tr>
                                        <tr>
                                            <td>Attendance Bonus:</td>
                                            <td class="text-right text-success">+ Rp ${formatNumber(data.attendance_bonus)}</td>
                                        </tr>
                                        <tr>
                                            <td>Additional Allowances:</td>
                                            <td class="text-right text-success">+ Rp ${formatNumber(data.allowances)}</td>
                                        </tr>
                                        <tr class="table-warning">
                                            <td>Leave Deduction:</td>
                                            <td class="text-right text-danger">- Rp ${formatNumber(data.leave_deduction)}</td>
                                        </tr>
                                        <tr class="table-warning">
                                            <td>Additional Deductions:</td>
                                            <td class="text-right text-danger">- Rp ${formatNumber(data.deductions)}</td>
                                        </tr>
                                        <tr class="table-primary">
                                            <td><strong>Total Salary:</strong></td>
                                            <td class="text-right"><strong>Rp ${formatNumber(data.total_salary)}</strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <small>
                                        <strong>Attendance:</strong> ${data.present_days} present, ${data.late_days} late (${data.attendance_rate}% rate)<br>
                                        <strong>Working Days:</strong> ${data.total_working_days} days
                                    </small>
                                </div>
                            </div>
                        </div>
                    `);
                } else {
                    $('#payrollPreview').html('<div class="text-center text-muted">Preview tidak tersedia</div>');
                    SwalHelper.warning('Warning!', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.log('AJAX Error:', {xhr: xhr, status: status, error: error});
                console.log('Response Text:', xhr.responseText);
                $('#payrollPreview').html('<div class="text-center text-muted">Preview tidak tersedia</div>');
                SwalHelper.error('Error!', 'Error calculating payroll. Please try again.');
            }
        });
    }

    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }
});
</script>
@endpush 