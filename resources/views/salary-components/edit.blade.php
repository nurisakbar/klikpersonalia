@extends('layouts.app')

@section('title', 'Perbarui Komponen Gaji - Aplikasi Payroll KlikMedis')
@section('page-title', 'Perbarui Komponen Gaji')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('salary-components.index') }}">Komponen Gaji</a></li>
<li class="breadcrumb-item active">Perbarui</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <form id="editComponentForm" action="{{ route('salary-components.update', $salaryComponent->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Nama Komponen <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', $salaryComponent->name) }}" 
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
                                        @foreach(\App\Models\SalaryComponent::getTypeOptions() as $value => $label)
                                            <option value="{{ $value }}" {{ old('type', $salaryComponent->type) == $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
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
                                        <input type="number" class="form-control @error('default_value') is-invalid @enderror" 
                                               id="default_value" name="default_value" value="{{ old('default_value', $salaryComponent->default_value) }}" 
                                               placeholder="Masukkan nilai default" min="0" step="0.01" required>
                                    </div>
                                    <small class="form-text text-muted">Nilai default komponen gaji dalam rupiah (maksimal Rp 999.999.999.999.999.999,99).</small>
                                    @error('default_value')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="sort_order">Urutan</label>
                                    <input type="number" class="form-control @error('sort_order') is-invalid @enderror" 
                                           id="sort_order" name="sort_order" value="{{ old('sort_order', $salaryComponent->sort_order) }}" 
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
                                      placeholder="Deskripsi detail komponen gaji (opsional)">{{ old('description', $salaryComponent->description) }}</textarea>
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
                                               value="1" {{ old('is_active', $salaryComponent->is_active) ? 'checked' : '' }}>
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
                                               value="1" {{ old('is_taxable', $salaryComponent->is_taxable) ? 'checked' : '' }}>
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
                                               value="1" {{ old('is_bpjs_calculated', $salaryComponent->is_bpjs_calculated) ? 'checked' : '' }}>
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
                                <i class="fas fa-save mr-1"></i> Perbarui
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

    <!-- Component Information Card -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">Informasi Komponen</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="mb-2">
                                    <i class="fas fa-calendar-alt fa-2x text-primary"></i>
                                </div>
                                <h6>Dibuat</h6>
                                <p class="text-muted">{{ $salaryComponent->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="mb-2">
                                    <i class="fas fa-edit fa-2x text-warning"></i>
                                </div>
                                <h6>Terakhir Diupdate</h6>
                                <p class="text-muted">{{ $salaryComponent->updated_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="mb-2">
                                    <i class="fas fa-building fa-2x text-info"></i>
                                </div>
                                <h6>Perusahaan</h6>
                                <p class="text-muted">{{ $salaryComponent->company->name }}</p>
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
    $('#editComponentForm').on('submit', function(e) {
        e.preventDefault();
        
        // Get numeric value from input
        let valueInput = $('#default_value');
        let numericValue = parseFloat(valueInput.val());
        
        // Debug: log the values
        console.log('Input Value:', valueInput.val());
        console.log('Numeric Value:', numericValue);
        
        // Validate minimum value
        if (numericValue < 0) {
            SwalHelper.error('Error!', 'Nilai default tidak boleh negatif');
            return false;
        }

        // Validate maximum value (999,999,999,999,999,999)
        const maxValue = 999999999999999999;
        if (numericValue > maxValue) {
            SwalHelper.error('Error!', 'Nilai default maksimal Rp 999.999.999.999.999.999,99');
            return false;
        }

        // Show loading
        $('#submitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

        // Prepare form data
        let formData = new FormData(this);

        // Handle checkbox values - if not checked, set to 0
        if (!$('#is_active').is(':checked')) {
            formData.set('is_active', '0');
        }
        if (!$('#is_taxable').is(':checked')) {
            formData.set('is_taxable', '0');
        }
        if (!$('#is_bpjs_calculated').is(':checked')) {
            formData.set('is_bpjs_calculated', '0');
        }

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
                    $('#submitBtn').prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Perbarui');
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
                $('#submitBtn').prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Perbarui');
            }
        });
    });
});
</script>
@endpush
