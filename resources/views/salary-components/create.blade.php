@extends('layouts.app')

@section('title', 'Tambah Komponen Gaji')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">Tambah Komponen Gaji</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('salary-components.index') }}">Komponen Gaji</a></li>
                        <li class="breadcrumb-item active">Tambah</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Form Tambah Komponen Gaji</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('salary-components.store') }}" method="POST" id="createComponentForm">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nama Komponen <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name') }}" 
                                           placeholder="Contoh: Gaji Pokok, Tunjangan Makan, dll" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Nama komponen gaji yang akan ditampilkan dalam sistem.</div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="type" class="form-label">Tipe Komponen <span class="text-danger">*</span></label>
                                    <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                                        <option value="">Pilih Tipe</option>
                                        <option value="earning" {{ old('type') == 'earning' ? 'selected' : '' }}>Pendapatan</option>
                                        <option value="deduction" {{ old('type') == 'deduction' ? 'selected' : '' }}>Potongan</option>
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Pendapatan akan menambah gaji, potongan akan mengurangi gaji.</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="default_value" class="form-label">Nilai Default <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" class="form-control @error('default_value') is-invalid @enderror" 
                                               id="default_value" name="default_value" value="{{ old('default_value') }}" 
                                               step="0.01" min="0" placeholder="0.00" required>
                                    </div>
                                    @error('default_value')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Nilai default komponen gaji dalam rupiah.</div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="sort_order" class="form-label">Urutan</label>
                                    <input type="number" class="form-control @error('sort_order') is-invalid @enderror" 
                                           id="sort_order" name="sort_order" value="{{ old('sort_order') }}" 
                                           min="0" placeholder="0">
                                    @error('sort_order')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Urutan tampilan komponen (opsional, akan diatur otomatis jika kosong).</div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Deskripsi</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3" 
                                      placeholder="Deskripsi detail komponen gaji (opsional)">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Penjelasan detail tentang komponen gaji ini.</div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                               value="1" {{ old('is_active', '1') == '1' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            Status Aktif
                                        </label>
                                    </div>
                                    <div class="form-text">Komponen aktif akan tersedia untuk digunakan dalam penggajian.</div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="is_taxable" name="is_taxable" 
                                               value="1" {{ old('is_taxable') == '1' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_taxable">
                                            Dikenakan Pajak
                                        </label>
                                    </div>
                                    <div class="form-text">Komponen ini akan dihitung dalam perhitungan pajak penghasilan.</div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="is_bpjs_calculated" name="is_bpjs_calculated" 
                                               value="1" {{ old('is_bpjs_calculated') == '1' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_bpjs_calculated">
                                            Dihitung BPJS
                                        </label>
                                    </div>
                                    <div class="form-text">Komponen ini akan dihitung dalam perhitungan BPJS.</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('salary-components.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Kembali
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Simpan Komponen
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="previewModalLabel">Preview Komponen Gaji</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Informasi Dasar</h6>
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Nama:</strong></td>
                                <td id="preview-name">-</td>
                            </tr>
                            <tr>
                                <td><strong>Tipe:</strong></td>
                                <td id="preview-type">-</td>
                            </tr>
                            <tr>
                                <td><strong>Nilai Default:</strong></td>
                                <td id="preview-value">-</td>
                            </tr>
                            <tr>
                                <td><strong>Urutan:</strong></td>
                                <td id="preview-order">-</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>Pengaturan</h6>
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td id="preview-status">-</td>
                            </tr>
                            <tr>
                                <td><strong>Pajak:</strong></td>
                                <td id="preview-taxable">-</td>
                            </tr>
                            <tr>
                                <td><strong>BPJS:</strong></td>
                                <td id="preview-bpjs">-</td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <h6>Deskripsi</h6>
                        <p id="preview-description" class="text-muted">-</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    .form-check-input:checked {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }
    .form-text {
        font-size: 0.875em;
        color: #6c757d;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Form validation
    $('#createComponentForm').on('submit', function(e) {
        if (!validateForm()) {
            e.preventDefault();
            showAlert('error', 'Mohon lengkapi semua field yang wajib diisi.');
        }
    });
    
    // Real-time validation
    $('#name').on('blur', function() {
        validateField('name', $(this).val());
    });
    
    $('#type').on('change', function() {
        validateField('type', $(this).val());
    });
    
    $('#default_value').on('blur', function() {
        validateField('default_value', $(this).val());
    });
    
    // Format currency input
    $('#default_value').on('input', function() {
        let value = $(this).val();
        if (value && !isNaN(value)) {
            $(this).val(parseFloat(value).toFixed(2));
        }
    });
    
    // Preview functionality
    $('#previewModal').on('show.bs.modal', function() {
        updatePreview();
    });
    
    function validateForm() {
        let isValid = true;
        
        isValid = validateField('name', $('#name').val()) && isValid;
        isValid = validateField('type', $('#type').val()) && isValid;
        isValid = validateField('default_value', $('#default_value').val()) && isValid;
        
        return isValid;
    }
    
    function validateField(fieldName, value) {
        const field = $(`#${fieldName}`);
        const feedback = field.siblings('.invalid-feedback');
        
        switch (fieldName) {
            case 'name':
                if (!value || value.trim().length === 0) {
                    field.addClass('is-invalid');
                    feedback.text('Nama komponen wajib diisi.');
                    return false;
                } else if (value.trim().length > 255) {
                    field.addClass('is-invalid');
                    feedback.text('Nama komponen maksimal 255 karakter.');
                    return false;
                } else {
                    field.removeClass('is-invalid');
                    return true;
                }
                break;
                
            case 'type':
                if (!value) {
                    field.addClass('is-invalid');
                    feedback.text('Tipe komponen wajib dipilih.');
                    return false;
                } else {
                    field.removeClass('is-invalid');
                    return true;
                }
                break;
                
            case 'default_value':
                if (!value || isNaN(value) || parseFloat(value) < 0) {
                    field.addClass('is-invalid');
                    feedback.text('Nilai default harus berupa angka positif.');
                    return false;
                } else {
                    field.removeClass('is-invalid');
                    return true;
                }
                break;
        }
        
        return true;
    }
    
    function updatePreview() {
        $('#preview-name').text($('#name').val() || '-');
        $('#preview-type').text($('#type option:selected').text() || '-');
        $('#preview-value').text($('#default_value').val() ? 'Rp ' + parseFloat($('#default_value').val()).toLocaleString('id-ID') : '-');
        $('#preview-order').text($('#sort_order').val() || 'Otomatis');
        $('#preview-status').text($('#is_active').is(':checked') ? 'Aktif' : 'Tidak Aktif');
        $('#preview-taxable').text($('#is_taxable').is(':checked') ? 'Ya' : 'Tidak');
        $('#preview-bpjs').text($('#is_bpjs_calculated').is(':checked') ? 'Ya' : 'Tidak');
        $('#preview-description').text($('#description').val() || 'Tidak ada deskripsi');
    }
    
    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        
        $('.card-body').prepend(alertHtml);
        
        setTimeout(function() {
            $('.alert').fadeOut();
        }, 5000);
    }
    
    // Add preview button
    $('.btn-primary').after(`
        <button type="button" class="btn btn-info" onclick="$('#previewModal').modal('show')">
            <i class="fas fa-eye"></i> Preview
        </button>
    `);
});
</script>
@endpush
