@extends('layouts.app')

@section('title', 'Tambah Data BPJS - Aplikasi Payroll KlikMedis')
@section('page-title', 'Tambah Data BPJS')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('bpjs.index') }}">Kelola BPJS</a></li>
<li class="breadcrumb-item active">Tambah Data BPJS</li>
@endsection

@section('content')
<div class="container-fluid">
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body">
                            <form method="POST" action="{{ route('bpjs.store') }}" id="bpjsForm">
                                @csrf
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="employee_id">Karyawan <span class="text-danger">*</span></label>
                                            <select name="employee_id" id="employee_id" class="form-control @error('employee_id') is-invalid @enderror" required>
                                                <option value="">Pilih Karyawan</option>
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
                                            <label for="bpjs_period">Periode BPJS <span class="text-danger">*</span></label>
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
                                            <label for="bpjs_type">Jenis BPJS <span class="text-danger">*</span></label>
                                            <select name="bpjs_type" id="bpjs_type" class="form-control @error('bpjs_type') is-invalid @enderror" required>
                                                <option value="">Pilih Jenis BPJS</option>
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
                                            <label for="base_salary">Gaji Pokok <span class="text-danger">*</span></label>
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
                                    <label for="notes">Catatan</label>
                                    <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror" 
                                              rows="3" placeholder="Catatan tambahan...">{{ old('notes') }}</textarea>
                                    @error('notes')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary" id="submitBtn">
                                        <i class="fas fa-save mr-1"></i> Simpan
                                    </button>
                                    <a href="{{ route('bpjs.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-times mr-1"></i> Batal
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Preview Perhitungan</h3>
                        </div>
                        <div class="card-body">
                            <div id="calculationPreview">
                                <p class="text-muted">Isi form untuk melihat preview perhitungan</p>
                            </div>
                        </div>
                    </div>

                    <!-- BPJS Information Card -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Informasi BPJS</h3>
                        </div>
                        <div class="card-body">
                            <h6>BPJS Kesehatan (2024)</h6>
                            <ul class="list-unstyled">
                                <li><small>Karyawan: 1% dari gaji pokok</small></li>
                                <li><small>Perusahaan: 4% dari gaji pokok</small></li>
                                <li><small>Maksimal Gaji: Rp 12.000.000</small></li>
                            </ul>
                            
                            <hr>
                            
                            <h6>BPJS Ketenagakerjaan (2024)</h6>
                            <ul class="list-unstyled">
                                <li><small><strong>JHT:</strong> Karyawan 2%, Perusahaan 3.7%</small></li>
                                <li><small><strong>JKK:</strong> Perusahaan 0.24% (variabel)</small></li>
                                <li><small><strong>JKM:</strong> Perusahaan 0.3%</small></li>
                                <li><small><strong>JP:</strong> Karyawan 1%, Perusahaan 2%</small></li>
                                <li><small>Maksimal Gaji: Rp 12.000.000</small></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
<!-- Global SweetAlert Component -->
@include('components.sweet-alert')

<script>
$(function () {
    // Setup CSRF token for AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Handle form submission with AJAX
    $('#bpjsForm').on('submit', function(e) {
        e.preventDefault();
        
        // Show loading
        $('#submitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

        // Send AJAX request
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            errorHandled: true,
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    SwalHelper.success('Berhasil!', response.message, 2000);
                    setTimeout(() => {
                        window.location.href = '{{ route("bpjs.index") }}';
                    }, 2000);
                } else {
                    SwalHelper.error('Gagal!', response.message);
                    $('#submitBtn').prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan');
                }
            },
            error: function(xhr) {
                let message = 'Terjadi kesalahan saat menyimpan data BPJS';
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
                $('#submitBtn').prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan');
            }
        });
    });

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
            $('#calculationPreview').html('<p class="text-muted">Isi form untuk melihat preview perhitungan</p>');
            return;
        }

        // Check if employee is active for selected BPJS type
        var selectedOption = $('#employee_id option:selected');
        var isKesehatanActive = selectedOption.data('kesehatan') == '1';
        var isKetenagakerjaanActive = selectedOption.data('ketenagakerjaan') == '1';

        if ((type === 'kesehatan' && !isKesehatanActive) || 
            (type === 'ketenagakerjaan' && !isKetenagakerjaanActive)) {
            $('#calculationPreview').html('<div class="text-center text-muted">Preview tidak tersedia</div>');
            SwalHelper.warning('Peringatan!', 'Karyawan ini tidak aktif untuk BPJS ' + type);
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
                    <h6><i class="fas fa-heartbeat"></i> Perhitungan BPJS Kesehatan</h6>
                    <hr>
                    <div class="row">
                        <div class="col-6">
                            <small>Gaji Pokok:</small><br>
                            <strong>Rp ${cappedSalary.toLocaleString('id-ID')}</strong>
                        </div>
                        <div class="col-6">
                            <small>Maksimal Gaji:</small><br>
                            <strong>Rp 12.000.000</strong>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-6">
                            <small>Karyawan (1%):</small><br>
                            <strong>Rp ${employeeContribution.toLocaleString('id-ID')}</strong>
                        </div>
                        <div class="col-6">
                            <small>Perusahaan (4%):</small><br>
                            <strong>Rp ${companyContribution.toLocaleString('id-ID')}</strong>
                        </div>
                    </div>
                    <hr>
                    <div class="text-center">
                        <small>Total Kontribusi:</small><br>
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
                    <h6><i class="fas fa-briefcase"></i> Perhitungan BPJS Ketenagakerjaan</h6>
                    <hr>
                    <div class="row">
                        <div class="col-6">
                            <small>Gaji Pokok:</small><br>
                            <strong>Rp ${cappedSalary.toLocaleString('id-ID')}</strong>
                        </div>
                        <div class="col-6">
                            <small>Maksimal Gaji:</small><br>
                            <strong>Rp 12.000.000</strong>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-6">
                            <small>JHT Karyawan (2%):</small><br>
                            <strong>Rp ${jhtEmployee.toLocaleString('id-ID')}</strong>
                        </div>
                        <div class="col-6">
                            <small>JHT Perusahaan (3.7%):</small><br>
                            <strong>Rp ${jhtCompany.toLocaleString('id-ID')}</strong>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <small>JP Karyawan (1%):</small><br>
                            <strong>Rp ${jpEmployee.toLocaleString('id-ID')}</strong>
                        </div>
                        <div class="col-6">
                            <small>JP Perusahaan (2%):</small><br>
                            <strong>Rp ${jpCompany.toLocaleString('id-ID')}</strong>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <small>JKK Perusahaan (0.24%):</small><br>
                            <strong>Rp ${jkkCompany.toLocaleString('id-ID')}</strong>
                        </div>
                        <div class="col-6">
                            <small>JKM Perusahaan (0.3%):</small><br>
                            <strong>Rp ${jkmCompany.toLocaleString('id-ID')}</strong>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-6">
                            <small>Total Karyawan:</small><br>
                            <strong>Rp ${employeeContribution.toLocaleString('id-ID')}</strong>
                        </div>
                        <div class="col-6">
                            <small>Total Perusahaan:</small><br>
                            <strong>Rp ${companyContribution.toLocaleString('id-ID')}</strong>
                        </div>
                    </div>
                    <hr>
                    <div class="text-center">
                        <small>Total Kontribusi:</small><br>
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