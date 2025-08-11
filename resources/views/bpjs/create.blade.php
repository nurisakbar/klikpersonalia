@extends('layouts.app')

@section('title', 'Create BPJS Record')

@section('content')
<div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Create BPJS Record</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('bpjs.index') }}">BPJS Management</a></li>
                        <li class="breadcrumb-item active">Create BPJS Record</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">BPJS Record Information</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('bpjs.store') }}" id="bpjsForm">
                                @csrf
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="employee_id">Employee <span class="text-danger">*</span></label>
                                            <select name="employee_id" id="employee_id" class="form-control @error('employee_id') is-invalid @enderror" required>
                                                <option value="">Select Employee</option>
                                                @foreach($employees as $employee)
                                                    <option value="{{ $employee->id }}" 
                                                            data-salary="{{ $employee->basic_salary }}"
                                                            data-kesehatan="{{ $employee->bpjs_kesehatan_active ? '1' : '0' }}"
                                                            data-ketenagakerjaan="{{ $employee->bpjs_ketenagakerjaan_active ? '1' : '0' }}">
                                                        {{ $employee->name }} ({{ $employee->employee_id }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('employee_id')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="bpjs_period">BPJS Period <span class="text-danger">*</span></label>
                                            <input type="month" name="bpjs_period" id="bpjs_period" 
                                                   class="form-control @error('bpjs_period') is-invalid @enderror" 
                                                   value="{{ old('bpjs_period', date('Y-m')) }}" required>
                                            @error('bpjs_period')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="bpjs_type">BPJS Type <span class="text-danger">*</span></label>
                                            <select name="bpjs_type" id="bpjs_type" class="form-control @error('bpjs_type') is-invalid @enderror" required>
                                                <option value="">Select BPJS Type</option>
                                                <option value="kesehatan" {{ old('bpjs_type') == 'kesehatan' ? 'selected' : '' }}>BPJS Kesehatan</option>
                                                <option value="ketenagakerjaan" {{ old('bpjs_type') == 'ketenagakerjaan' ? 'selected' : '' }}>BPJS Ketenagakerjaan</option>
                                            </select>
                                            @error('bpjs_type')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="base_salary">Base Salary <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">Rp</span>
                                                </div>
                                                <input type="number" name="base_salary" id="base_salary" 
                                                       class="form-control @error('base_salary') is-invalid @enderror" 
                                                       value="{{ old('base_salary') }}" 
                                                       min="0" step="1000" required>
                                            </div>
                                            @error('base_salary')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="notes">Notes</label>
                                    <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror" 
                                              rows="3" placeholder="Additional notes...">{{ old('notes') }}</textarea>
                                    @error('notes')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Create BPJS Record
                                    </button>
                                    <a href="{{ route('bpjs.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Cancel
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Calculation Preview</h3>
                        </div>
                        <div class="card-body">
                            <div id="calculationPreview">
                                <p class="text-muted">Fill in the form to see calculation preview</p>
                            </div>
                        </div>
                    </div>

                    <!-- BPJS Information Card -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">BPJS Information</h3>
                        </div>
                        <div class="card-body">
                            <h6>BPJS Kesehatan (2024)</h6>
                            <ul class="list-unstyled">
                                <li><small>Employee: 1% dari gaji pokok</small></li>
                                <li><small>Company: 4% dari gaji pokok</small></li>
                                <li><small>Max Base: Rp 12.000.000</small></li>
                            </ul>
                            
                            <hr>
                            
                            <h6>BPJS Ketenagakerjaan (2024)</h6>
                            <ul class="list-unstyled">
                                <li><small><strong>JHT:</strong> Employee 2%, Company 3.7%</small></li>
                                <li><small><strong>JKK:</strong> Company 0.24% (variabel)</small></li>
                                <li><small><strong>JKM:</strong> Company 0.3%</small></li>
                                <li><small><strong>JP:</strong> Employee 1%, Company 2%</small></li>
                                <li><small>Max Base: Rp 12.000.000</small></li>
                            </ul>
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
    // Auto-fill base salary when employee is selected
    $('#employee_id').change(function() {
        var selectedOption = $(this).find('option:selected');
        var salary = selectedOption.data('salary');
        if (salary) {
            $('#base_salary').val(salary);
            updateCalculationPreview();
        }
    });

    // Update calculation preview when form changes
    $('#bpjs_type, #base_salary').on('input change', function() {
        updateCalculationPreview();
    });

    function updateCalculationPreview() {
        var type = $('#bpjs_type').val();
        var baseSalary = parseFloat($('#base_salary').val()) || 0;
        var employeeId = $('#employee_id').val();

        if (!type || !baseSalary || !employeeId) {
            $('#calculationPreview').html('<p class="text-muted">Fill in the form to see calculation preview</p>');
            return;
        }

        // Check if employee is active for selected BPJS type
        var selectedOption = $('#employee_id option:selected');
        var isKesehatanActive = selectedOption.data('kesehatan') == '1';
        var isKetenagakerjaanActive = selectedOption.data('ketenagakerjaan') == '1';

        if ((type === 'kesehatan' && !isKesehatanActive) || 
            (type === 'ketenagakerjaan' && !isKetenagakerjaanActive)) {
            $('#calculationPreview').html('<div class="alert alert-warning">This employee is not active for ' + type + ' BPJS</div>');
            return;
        }

        // Calculate based on type
        var employeeContribution = 0;
        var companyContribution = 0;
        var totalContribution = 0;
        var maxBaseSalary = 12000000;
        var cappedSalary = Math.min(baseSalary, maxBaseSalary);

        if (type === 'kesehatan') {
            employeeContribution = cappedSalary * 0.01; // 1%
            companyContribution = cappedSalary * 0.04; // 4%
            totalContribution = employeeContribution + companyContribution;

            var html = `
                <div class="alert alert-info">
                    <h6><i class="fas fa-heartbeat"></i> BPJS Kesehatan Calculation</h6>
                    <hr>
                    <div class="row">
                        <div class="col-6">
                            <small>Base Salary:</small><br>
                            <strong>Rp ${cappedSalary.toLocaleString('id-ID')}</strong>
                        </div>
                        <div class="col-6">
                            <small>Max Base:</small><br>
                            <strong>Rp 12.000.000</strong>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-6">
                            <small>Employee (1%):</small><br>
                            <strong>Rp ${employeeContribution.toLocaleString('id-ID')}</strong>
                        </div>
                        <div class="col-6">
                            <small>Company (4%):</small><br>
                            <strong>Rp ${companyContribution.toLocaleString('id-ID')}</strong>
                        </div>
                    </div>
                    <hr>
                    <div class="text-center">
                        <small>Total Contribution:</small><br>
                        <strong class="text-primary">Rp ${totalContribution.toLocaleString('id-ID')}</strong>
                    </div>
                </div>
            `;
        } else if (type === 'ketenagakerjaan') {
            // JHT
            var jhtEmployee = cappedSalary * 0.02; // 2%
            var jhtCompany = cappedSalary * 0.037; // 3.7%
            
            // JKK
            var jkkCompany = cappedSalary * 0.0024; // 0.24%
            
            // JKM
            var jkmCompany = cappedSalary * 0.003; // 0.3%
            
            // JP
            var jpEmployee = cappedSalary * 0.01; // 1%
            var jpCompany = cappedSalary * 0.02; // 2%

            employeeContribution = jhtEmployee + jpEmployee;
            companyContribution = jhtCompany + jkkCompany + jkmCompany + jpCompany;
            totalContribution = employeeContribution + companyContribution;

            var html = `
                <div class="alert alert-success">
                    <h6><i class="fas fa-briefcase"></i> BPJS Ketenagakerjaan Calculation</h6>
                    <hr>
                    <div class="row">
                        <div class="col-6">
                            <small>Base Salary:</small><br>
                            <strong>Rp ${cappedSalary.toLocaleString('id-ID')}</strong>
                        </div>
                        <div class="col-6">
                            <small>Max Base:</small><br>
                            <strong>Rp 12.000.000</strong>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-6">
                            <small>JHT Employee (2%):</small><br>
                            <strong>Rp ${jhtEmployee.toLocaleString('id-ID')}</strong>
                        </div>
                        <div class="col-6">
                            <small>JHT Company (3.7%):</small><br>
                            <strong>Rp ${jhtCompany.toLocaleString('id-ID')}</strong>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <small>JP Employee (1%):</small><br>
                            <strong>Rp ${jpEmployee.toLocaleString('id-ID')}</strong>
                        </div>
                        <div class="col-6">
                            <small>JP Company (2%):</small><br>
                            <strong>Rp ${jpCompany.toLocaleString('id-ID')}</strong>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <small>JKK Company (0.24%):</small><br>
                            <strong>Rp ${jkkCompany.toLocaleString('id-ID')}</strong>
                        </div>
                        <div class="col-6">
                            <small>JKM Company (0.3%):</small><br>
                            <strong>Rp ${jkmCompany.toLocaleString('id-ID')}</strong>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-6">
                            <small>Total Employee:</small><br>
                            <strong>Rp ${employeeContribution.toLocaleString('id-ID')}</strong>
                        </div>
                        <div class="col-6">
                            <small>Total Company:</small><br>
                            <strong>Rp ${companyContribution.toLocaleString('id-ID')}</strong>
                        </div>
                    </div>
                    <hr>
                    <div class="text-center">
                        <small>Total Contribution:</small><br>
                        <strong class="text-primary">Rp ${totalContribution.toLocaleString('id-ID')}</strong>
                    </div>
                </div>
            `;
        }

        $('#calculationPreview').html(html);
    }
});
</script>
@endpush 