@extends('layouts.app')

@section('title', 'Assign Komponen Gaji ke Karyawan')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Assign Komponen Gaji ke Karyawan</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item">Master Data</li>
                    <li class="breadcrumb-item"><a href="{{ route('employee-salary-components.index') }}">Assignment Komponen</a></li>
                    <li class="breadcrumb-item active">Create</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Form Assignment Komponen Gaji</h3>
                    </div>
                    <form action="{{ route('employee-salary-components.store') }}" method="POST" id="assignmentForm">
                        @csrf
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="employee_id">Pilih Karyawan <span class="text-danger">*</span></label>
                                        <select name="employee_id" id="employee_id" class="form-control select2" required>
                                            <option value="">Pilih Karyawan</option>
                                            @foreach($employees as $employee)
                                            <option value="{{ $employee->id }}" 
                                                    data-basic-salary="{{ $employee->basic_salary }}">
                                                {{ $employee->name }} - {{ $employee->employee_id }}
                                            </option>
                                            @endforeach
                                        </select>
                                        @error('employee_id')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="salary_component_id">Komponen Gaji <span class="text-danger">*</span></label>
                                        <select name="salary_component_id" id="salary_component_id" class="form-control select2" required>
                                            <option value="">Pilih Komponen</option>
                                            @foreach($salaryComponents as $component)
                                            <option value="{{ $component->id }}" 
                                                    data-type="{{ $component->type }}"
                                                    data-default-value="{{ $component->default_value }}">
                                                {{ $component->name }} ({{ $component->type_text }})
                                            </option>
                                            @endforeach
                                        </select>
                                        @error('salary_component_id')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="calculation_type">Tipe Perhitungan <span class="text-danger">*</span></label>
                                        <select name="calculation_type" id="calculation_type" class="form-control" required>
                                            <option value="fixed">Nilai Tetap</option>
                                            <option value="percentage">Persentase</option>
                                        </select>
                                        @error('calculation_type')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="amount">Nilai <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text" id="amount-prefix">Rp</span>
                                            </div>
                                            <input type="number" name="amount" id="amount" class="form-control" 
                                                   step="0.01" min="0" required>
                                        </div>
                                        @error('amount')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row" id="percentage_fields" style="display: none;">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="percentage_value">Nilai Persentase (%) <span class="text-danger">*</span></label>
                                        <input type="number" name="percentage_value" id="percentage_value" 
                                               class="form-control" step="0.01" min="0" max="100">
                                        @error('percentage_value')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="reference_type">Referensi Gaji <span class="text-danger">*</span></label>
                                        <select name="reference_type" id="reference_type" class="form-control">
                                            <option value="">Pilih Referensi</option>
                                            <option value="basic_salary">Gaji Pokok</option>
                                            <option value="gross_salary">Gaji Kotor</option>
                                            <option value="net_salary">Gaji Bersih</option>
                                        </select>
                                        @error('reference_type')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="effective_date">Tanggal Efektif</label>
                                        <input type="date" name="effective_date" id="effective_date" class="form-control">
                                        <small class="form-text text-muted">Kosongkan jika berlaku segera</small>
                                        @error('effective_date')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="expiry_date">Tanggal Expired</label>
                                        <input type="date" name="expiry_date" id="expiry_date" class="form-control">
                                        <small class="form-text text-muted">Kosongkan jika berlaku selamanya</small>
                                        @error('expiry_date')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="notes">Catatan</label>
                                <textarea name="notes" id="notes" class="form-control" rows="3" 
                                          placeholder="Tambahkan catatan jika diperlukan..."></textarea>
                                @error('notes')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" checked>
                                    <label class="custom-control-label" for="is_active">Aktif</label>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Assign Komponen
                            </button>
                            <a href="{{ route('employee-salary-components.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Preview Card -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Preview Assignment</h3>
                    </div>
                    <div class="card-body">
                        <div id="preview-content">
                            <p class="text-muted">Pilih karyawan dan komponen untuk melihat preview</p>
                        </div>
                    </div>
                </div>

                <!-- Help Card -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Bantuan</h3>
                    </div>
                    <div class="card-body">
                        <h6>Nilai Tetap:</h6>
                        <p class="text-sm">Komponen dengan nilai yang sama setiap periode gaji</p>
                        
                        <h6>Persentase:</h6>
                        <p class="text-sm">Komponen yang dihitung berdasarkan persentase dari gaji referensi</p>
                        
                        <h6>Tanggal Efektif:</h6>
                        <p class="text-sm">Tanggal mulai berlakunya komponen gaji</p>
                        
                        <h6>Tanggal Expired:</h6>
                        <p class="text-sm">Tanggal berakhirnya komponen gaji</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.6.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%'
    });

    // Handle calculation type change
    $('#calculation_type').on('change', function() {
        if ($(this).val() === 'percentage') {
            $('#percentage_fields').show();
            $('#percentage_value, #reference_type').prop('required', true);
            $('#amount-prefix').text('%');
        } else {
            $('#percentage_fields').hide();
            $('#percentage_value, #reference_type').prop('required', false);
            $('#amount-prefix').text('Rp');
        }
        updatePreview();
    });

    // Handle employee selection
    $('#employee_id').on('change', function() {
        updatePreview();
    });

    // Handle component selection
    $('#salary_component_id').on('change', function() {
        updatePreview();
    });

    // Handle amount change
    $('#amount, #percentage_value').on('input', function() {
        updatePreview();
    });

    // Handle reference type change
    $('#reference_type').on('change', function() {
        updatePreview();
    });

    // Update preview
    function updatePreview() {
        const employeeId = $('#employee_id').val();
        const componentId = $('#salary_component_id').val();
        const calculationType = $('#calculation_type').val();
        const amount = $('#amount').val();
        const percentageValue = $('#percentage_value').val();
        const referenceType = $('#reference_type').val();

        if (!employeeId || !componentId) {
            $('#preview-content').html('<p class="text-muted">Pilih karyawan dan komponen untuk melihat preview</p>');
            return;
        }

        const selectedEmployee = $('#employee_id option:selected');
        const selectedComponent = $('#salary_component_id option:selected');
        const basicSalary = selectedEmployee.data('basic-salary');
        const componentType = selectedComponent.data('type');
        const defaultValue = selectedComponent.data('default-value');

        let previewHtml = `
            <div class="border rounded p-3">
                <h6 class="font-weight-bold">Detail Assignment:</h6>
                <table class="table table-sm">
                    <tr><td>Karyawan:</td><td><strong>${selectedEmployee.text()}</strong></td></tr>
                    <tr><td>Komponen:</td><td><strong>${selectedComponent.text()}</strong></td></tr>
                    <tr><td>Tipe:</td><td><span class="badge badge-${componentType === 'earning' ? 'success' : 'danger'}">${componentType === 'earning' ? 'Pendapatan' : 'Potongan'}</span></td></tr>
                    <tr><td>Gaji Pokok:</td><td><strong>Rp ${Number(basicSalary).toLocaleString('id-ID')}</strong></td></tr>
                    <tr><td>Perhitungan:</td><td><strong>${calculationType === 'fixed' ? 'Nilai Tetap' : 'Persentase'}</strong></td></tr>
        `;

        if (calculationType === 'fixed') {
            previewHtml += `<tr><td>Nilai:</td><td><strong>Rp ${Number(amount).toLocaleString('id-ID')}</strong></td></tr>`;
        } else if (calculationType === 'percentage') {
            previewHtml += `
                <tr><td>Persentase:</td><td><strong>${percentageValue}%</strong></td></tr>
                <tr><td>Referensi:</td><td><strong>${referenceType === 'basic_salary' ? 'Gaji Pokok' : referenceType === 'gross_salary' ? 'Gaji Kotor' : 'Gaji Bersih'}</strong></td></tr>
            `;
        }

        previewHtml += `
                </table>
            </div>
        `;

        $('#preview-content').html(previewHtml);
    }

    // Form validation
    $('#assignmentForm').on('submit', function(e) {
        const calculationType = $('#calculation_type').val();
        
        if (calculationType === 'percentage') {
            const percentageValue = $('#percentage_value').val();
            const referenceType = $('#reference_type').val();
            
            if (!percentageValue) {
                e.preventDefault();
                alert('Nilai persentase harus diisi untuk perhitungan berbasis persentase.');
                $('#percentage_value').focus();
                return false;
            }
            
            if (!referenceType) {
                e.preventDefault();
                alert('Tipe referensi harus dipilih untuk perhitungan berbasis persentase.');
                $('#reference_type').focus();
                return false;
            }
        }
    });

    // Set default effective date to today
    $('#effective_date').val(new Date().toISOString().split('T')[0]);
});
</script>
@endpush
