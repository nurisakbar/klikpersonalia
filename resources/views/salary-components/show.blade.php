@extends('layouts.app')

@section('title', 'Rincian Komponen Gaji - Aplikasi Payroll KlikMedis')
@section('page-title', 'Rincian Komponen Gaji')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('salary-components.index') }}">Komponen Gaji</a></li>
<li class="breadcrumb-item active">Rincian</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label fw-bold">Nama Komponen</label>
                                        <p class="form-control-plaintext">{{ $salaryComponent->name }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label fw-bold">Tipe Komponen</label>
                                        <p class="form-control-plaintext">
                                            <span class="badge {{ $salaryComponent->type === 'earning' ? 'badge-success' : 'badge-danger' }}">
                                                <i class="{{ $salaryComponent->type === 'earning' ? 'fas fa-plus-circle' : 'fas fa-minus-circle' }}"></i>
                                                {{ $salaryComponent->type_text }}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label fw-bold">Nilai Default</label>
                                        <p class="form-control-plaintext">
                                            <span class="text-primary fw-bold">
                                                Rp {{ number_format($salaryComponent->default_value, 0, ',', '.') }}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label fw-bold">Urutan</label>
                                        <p class="form-control-plaintext">
                                            <span class="badge badge-secondary">{{ $salaryComponent->sort_order }}</span>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            @if($salaryComponent->description)
                            <div class="form-group">
                                <label class="form-label fw-bold">Deskripsi</label>
                                <p class="form-control-plaintext">{{ $salaryComponent->description }}</p>
                            </div>
                            @endif

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label fw-bold">Status</label>
                                        <p class="form-control-plaintext">
                                            <span class="badge {{ $salaryComponent->is_active ? 'badge-success' : 'badge-danger' }}">
                                                <i class="fas {{ $salaryComponent->is_active ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
                                                {{ $salaryComponent->status_text }}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label fw-bold">Dikenakan Pajak</label>
                                        <p class="form-control-plaintext">
                                            <span class="badge {{ $salaryComponent->is_taxable ? 'badge-success' : 'badge-secondary' }}">
                                                <i class="fas {{ $salaryComponent->is_taxable ? 'fa-check' : 'fa-times' }}"></i>
                                                {{ $salaryComponent->is_taxable ? 'Ya' : 'Tidak' }}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label fw-bold">Dihitung BPJS</label>
                                        <p class="form-control-plaintext">
                                            <span class="badge {{ $salaryComponent->is_bpjs_calculated ? 'badge-info' : 'badge-secondary' }}">
                                                <i class="fas {{ $salaryComponent->is_bpjs_calculated ? 'fa-check' : 'fa-times' }}"></i>
                                                {{ $salaryComponent->is_bpjs_calculated ? 'Ya' : 'Tidak' }}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-info-circle"></i> Informasi Sistem
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label class="form-label fw-bold">Perusahaan</label>
                                        <p class="form-control-plaintext">{{ $salaryComponent->company->name }}</p>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label fw-bold">Dibuat</label>
                                        <p class="form-control-plaintext">{{ $salaryComponent->created_at->format('d/m/Y H:i') }}</p>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label fw-bold">Terakhir Diupdate</label>
                                        <p class="form-control-plaintext">{{ $salaryComponent->updated_at->format('d/m/Y H:i') }}</p>
                                    </div>
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
});

function toggleComponentStatus() {
    const isActive = {{ $salaryComponent->is_active ? 'true' : 'false' }};
    const action = isActive ? 'menonaktifkan' : 'mengaktifkan';
    const message = `Apakah Anda yakin ingin ${action} komponen "{{ $salaryComponent->name }}"?`;
    
    SwalHelper.confirm('Konfirmasi', message, function() {
        // Show loading
        $('#toggleStatusBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Memproses...');
        
        // Send AJAX request
        $.ajax({
            url: '{{ route("salary-components.toggle-status", $salaryComponent->id) }}',
            type: 'POST',
            errorHandled: true,
            success: function(response) {
                if (response.success) {
                    SwalHelper.success('Berhasil!', response.message, 2000);
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    SwalHelper.error('Gagal!', response.message);
                    $('#toggleStatusBtn').prop('disabled', false).html(
                        `<i class="fas ${isActive ? 'fa-times' : 'fa-check'}"></i> ${isActive ? 'Nonaktifkan' : 'Aktifkan'}`
                    );
                }
            },
            error: function(xhr) {
                let message = 'Terjadi kesalahan saat memproses permintaan';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                
                SwalHelper.error('Error!', message);
                $('#toggleStatusBtn').prop('disabled', false).html(
                    `<i class="fas ${isActive ? 'fa-times' : 'fa-check'}"></i> ${isActive ? 'Nonaktifkan' : 'Aktifkan'}`
                );
            }
        });
    });
}
</script>
@endpush
