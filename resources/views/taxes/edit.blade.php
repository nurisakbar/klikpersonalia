@extends('layouts.app')

@section('title', 'Perbarui Pajak - Aplikasi Payroll KlikMedis')
@section('page-title', 'Perbarui Pajak')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('taxes.index') }}">Pajak</a></li>
<li class="breadcrumb-item active">Perbarui</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <form id="editTaxForm" action="{{ route('taxes.update', $tax->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="employee_id">Karyawan <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" value="{{ $tax->employee->name }} ({{ $tax->employee->employee_id }})" readonly>
                                    <input type="hidden" name="employee_id" value="{{ $tax->employee->id }}">
                                    <small class="form-text text-muted">Karyawan tidak dapat diubah</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tax_period">Periode Pajak <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" value="{{ \Carbon\Carbon::createFromFormat('Y-m', $tax->tax_period)->format('F Y') }}" readonly>
                                    <input type="hidden" name="tax_period" value="{{ $tax->tax_period }}">
                                    <small class="form-text text-muted">Periode pajak tidak dapat diubah</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="taxable_income">Pendapatan Kena Pajak <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp</span>
                                        </div>
                                        <input type="text" class="form-control @error('taxable_income') is-invalid @enderror" id="taxable_income" name="taxable_income" value="{{ old('taxable_income', number_format($tax->taxable_income, 0, ',', '.')) }}" placeholder="Masukkan pendapatan kena pajak" required>
                                    </div>
                                    <small class="form-text text-muted">Minimal Rp 1.000.000</small>
                                    @error('taxable_income')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="ptkp_status">Status PTKP <span class="text-danger">*</span></label>
                                    <select class="form-control @error('ptkp_status') is-invalid @enderror" id="ptkp_status" name="ptkp_status" required>
                                        <option value="">Pilih Status PTKP</option>
                                        @foreach($ptkpStatuses as $key => $status)
                                            <option value="{{ $key }}" {{ old('ptkp_status', $tax->ptkp_status) == $key ? 'selected' : '' }}>{{ $key }} - {{ $status }}</option>
                                        @endforeach
                                    </select>
                                    @error('ptkp_status')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status">Status <span class="text-danger">*</span></label>
                                    <select class="form-control @error('status') is-invalid @enderror" id="status" name="status" required>
                                        <option value="">Pilih Status</option>
                                        <option value="pending" {{ old('status', $tax->status) == 'pending' ? 'selected' : '' }}>Menunggu</option>
                                        <option value="calculated" {{ old('status', $tax->status) == 'calculated' ? 'selected' : '' }}>Dihitung</option>
                                        <option value="paid" {{ old('status', $tax->status) == 'paid' ? 'selected' : '' }}>Dibayar</option>
                                        <option value="verified" {{ old('status', $tax->status) == 'verified' ? 'selected' : '' }}>Terverifikasi</option>
                                    </select>
                                    @error('status')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="notes">Catatan</label>
                                    <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3" placeholder="Masukkan catatan (opsional)">{{ old('notes', $tax->notes) }}</textarea>
                                    @error('notes')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="fas fa-save mr-1"></i> Perbarui
                        </button>
                        <a href="{{ route('taxes.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times mr-1"></i> Batal
                        </a>
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

    // Format input pendapatan dengan separator ribuan
    $('#taxable_income').on('input', function() {
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
    $('#editTaxForm').on('submit', function(e) {
        e.preventDefault();
        
        // Get raw numeric value from formatted income
        let incomeInput = $('#taxable_income');
        let formattedIncome = incomeInput.val();
        let rawIncome = formattedIncome.replace(/[^\d]/g, '');
        
        // Validate minimum income
        if (parseInt(rawIncome) < 1000000) {
            SwalHelper.error('Error!', 'Pendapatan kena pajak minimal Rp 1.000.000');
            return false;
        }

        // Show loading
        $('#submitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

        // Prepare form data
        let formData = new FormData(this);
        formData.set('taxable_income', rawIncome); // Set raw income value

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
                        window.location.href = '{{ route("taxes.index") }}';
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

    // Format initial value if exists
    let initialIncome = $('#taxable_income').val();
    if (initialIncome && !isNaN(parseInt(initialIncome.replace(/[^\d]/g, '')))) {
        let value = parseInt(initialIncome.replace(/[^\d]/g, ''));
        $('#taxable_income').val(value.toLocaleString('id-ID'));
    }
});
</script>
@endpush 