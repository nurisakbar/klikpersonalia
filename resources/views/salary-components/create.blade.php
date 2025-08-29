@extends('layouts.app')

@section('title', 'Tambah Komponen Gaji - Aplikasi Payroll KlikMedis')
@section('page-title', 'Tambah Komponen Gaji')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('salary-components.index') }}">Komponen Gaji</a></li>
<li class="breadcrumb-item active">Tambah</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <form id="createComponentForm" action="{{ route('salary-components.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Nama Komponen <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name') }}" 
                                           placeholder="Contoh: Gaji Pokok, Tunjangan Makan, dll" required>
                                    @error('name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">Nama komponen gaji yang akan ditampilkan dalam sistem.</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="type">Tipe Komponen <span class="text-danger">*</span></label>
                                    <select class="form-control @error('type') is-invalid @enderror" id="type" name="type" required>
                                        <option value="">Pilih Tipe</option>
                                        <option value="earning" {{ old('type') == 'earning' ? 'selected' : '' }}>Pendapatan</option>
                                        <option value="deduction" {{ old('type') == 'deduction' ? 'selected' : '' }}>Potongan</option>
                                    </select>
                                    @error('type')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">Pendapatan akan menambah gaji, potongan akan mengurangi gaji.</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="default_value">Nilai Default <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp</span>
                                        </div>
                                        <input type="text" class="form-control @error('default_value') is-invalid @enderror" 
                                               id="default_value" name="default_value" value="{{ old('default_value') }}" 
                                               placeholder="Masukkan nilai default" required>
                                    </div>
                                    @error('default_value')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">Nilai default komponen gaji dalam rupiah.</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="sort_order">Urutan</label>
                                    <input type="number" class="form-control @error('sort_order') is-invalid @enderror" 
                                           id="sort_order" name="sort_order" value="{{ old('sort_order') }}" 
                                           min="0" placeholder="0">
                                    @error('sort_order')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">Urutan tampilan komponen (opsional, akan diatur otomatis jika kosong).</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description">Deskripsi</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3" 
                                      placeholder="Deskripsi detail komponen gaji (opsional)">{{ old('description') }}</textarea>
                            @error('description')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="form-text text-muted">Penjelasan detail tentang komponen gaji ini.</small>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" 
                                               value="1" {{ old('is_active', '1') == '1' ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="is_active">
                                            Status Aktif
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">Komponen aktif akan tersedia untuk digunakan dalam penggajian.</small>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="is_taxable" name="is_taxable" 
                                               value="1" {{ old('is_taxable') == '1' ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="is_taxable">
                                            Dikenakan Pajak
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">Komponen ini akan dihitung dalam perhitungan pajak penghasilan.</small>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="is_bpjs_calculated" name="is_bpjs_calculated" 
                                               value="1" {{ old('is_bpjs_calculated') == '1' ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="is_bpjs_calculated">
                                            Dihitung BPJS
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">Komponen ini akan dihitung dalam perhitungan BPJS.</small>
                                </div>
                            </div>
                        </div>

                        <div>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="fas fa-save mr-1"></i> Simpan
                            </button>
                            <a href="{{ route('salary-components.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times mr-1"></i> Batal
                            </a>
                        </div>
                    </div>
                </form>
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

    // Format input nilai default dengan separator ribuan
    $('#default_value').on('input', function() {
        let value = $(this).val().replace(/[^\d]/g, '');
        if (value) {
            // Convert to number and format with thousand separators
            let number = parseInt(value);
            if (!isNaN(number)) {
                $(this).val(number.toLocaleString('id-ID'));
            }
        }
    });

    // Handle form submission with AJAX
    $('#createComponentForm').on('submit', function(e) {
        e.preventDefault();
        
        // Get raw numeric value from formatted default_value
        let valueInput = $('#default_value');
        let formattedValue = valueInput.val();
        let rawValue = formattedValue.replace(/[^\d]/g, '');
        
        // Validate minimum value
        if (parseInt(rawValue) < 0) {
            SwalHelper.error('Error!', 'Nilai default tidak boleh negatif');
            return false;
        }

        // Show loading
        $('#submitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

        // Prepare form data
        let formData = new FormData(this);
        formData.set('default_value', rawValue); // Set raw value

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
                        window.location.href = '{{ route("salary-components.index") }}';
                    }, 2000);
                } else {
                    SwalHelper.error('Gagal!', response.message);
                    $('#submitBtn').prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan');
                }
            },
            error: function(xhr) {
                let message = 'Terjadi kesalahan saat menyimpan data';
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

    // Format initial value if exists
    let initialValue = $('#default_value').val();
    if (initialValue && !isNaN(parseInt(initialValue.replace(/[^\d]/g, '')))) {
        let value = parseInt(initialValue.replace(/[^\d]/g, ''));
        $('#default_value').val(value.toLocaleString('id-ID'));
    }
});
</script>
@endpush
