@extends('layouts.app')

@section('title', 'Detail Komponen Gaji')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">Detail Komponen Gaji</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('salary-components.index') }}">Komponen Gaji</a></li>
                        <li class="breadcrumb-item active">Detail</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">{{ $salaryComponent->name }}</h5>
                        <div class="d-flex gap-2">
                            <a href="{{ route('salary-components.edit', $salaryComponent->id) }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="{{ route('salary-components.index') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Nama Komponen</label>
                                        <p class="form-control-plaintext">{{ $salaryComponent->name }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Tipe Komponen</label>
                                        <p class="form-control-plaintext">
                                            @if($salaryComponent->type == 'earning')
                                                <span class="badge bg-success">{{ $salaryComponent->type_text }}</span>
                                            @else
                                                <span class="badge bg-warning">{{ $salaryComponent->type_text }}</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Nilai Default</label>
                                        <p class="form-control-plaintext">
                                            <span class="text-primary fw-bold">
                                                Rp {{ number_format($salaryComponent->default_value, 0, ',', '.') }}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Urutan</label>
                                        <p class="form-control-plaintext">
                                            <span class="badge bg-secondary">{{ $salaryComponent->sort_order }}</span>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Deskripsi</label>
                                <p class="form-control-plaintext">
                                    {{ $salaryComponent->description ?: 'Tidak ada deskripsi' }}
                                </p>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Status</label>
                                        <p class="form-control-plaintext">
                                            @if($salaryComponent->is_active)
                                                <span class="badge bg-success">{{ $salaryComponent->status_text }}</span>
                                            @else
                                                <span class="badge bg-danger">{{ $salaryComponent->status_text }}</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Dikenakan Pajak</label>
                                        <p class="form-control-plaintext">
                                            @if($salaryComponent->is_taxable)
                                                <span class="badge bg-success">Ya</span>
                                            @else
                                                <span class="badge bg-secondary">Tidak</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Dihitung BPJS</label>
                                        <p class="form-control-plaintext">
                                            @if($salaryComponent->is_bpjs_calculated)
                                                <span class="badge bg-info">Ya</span>
                                            @else
                                                <span class="badge bg-secondary">Tidak</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Informasi Sistem</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">ID Komponen</label>
                                        <p class="form-control-plaintext small">{{ $salaryComponent->id }}</p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Perusahaan</label>
                                        <p class="form-control-plaintext">{{ $salaryComponent->company->name }}</p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Dibuat</label>
                                        <p class="form-control-plaintext">{{ $salaryComponent->created_at->format('d/m/Y H:i') }}</p>
                                    </div>
                                    <div class="mb-3">
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

    <!-- Component Usage Information -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">Informasi Penggunaan</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="mb-2">
                                    <i class="fas fa-calculator fa-2x text-primary"></i>
                                </div>
                                <h6>Total Penggunaan</h6>
                                <p class="text-muted">0 kali</p>
                                <small class="text-muted">Dalam penggajian</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="mb-2">
                                    <i class="fas fa-users fa-2x text-success"></i>
                                </div>
                                <h6>Karyawan Terdaftar</h6>
                                <p class="text-muted">0 orang</p>
                                <small class="text-muted">Menggunakan komponen ini</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="mb-2">
                                    <i class="fas fa-calendar fa-2x text-warning"></i>
                                </div>
                                <h6>Periode Terakhir</h6>
                                <p class="text-muted">-</p>
                                <small class="text-muted">Digunakan dalam penggajian</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="mb-2">
                                    <i class="fas fa-chart-line fa-2x text-info"></i>
                                </div>
                                <h6>Rata-rata Nilai</h6>
                                <p class="text-muted">Rp 0</p>
                                <small class="text-muted">Nilai rata-rata penggunaan</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="row mt-4">
        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Aksi</h6>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex gap-2 flex-wrap">
                                        <a href="{{ route('salary-components.edit', $salaryComponent->id) }}" class="btn btn-warning">
                                            <i class="fas fa-edit"></i> Edit Komponen
                                        </a>
                                        @if($salaryComponent->is_active)
                                            <form action="{{ route('salary-components.toggle-status', $salaryComponent->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-warning" onclick="return confirm('Apakah Anda yakin ingin menonaktifkan komponen ini?')">
                                                    <i class="fas fa-times"></i> Nonaktifkan
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('salary-components.toggle-status', $salaryComponent->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-success" onclick="return confirm('Apakah Anda yakin ingin mengaktifkan komponen ini?')">
                                                    <i class="fas fa-check"></i> Aktifkan
                                                </button>
                                            </form>
                                        @endif
                                        <a href="{{ route('salary-components.index') }}" class="btn btn-secondary">
                                            <i class="fas fa-arrow-left"></i> Kembali ke Daftar
                                        </a>
                                        <button type="button" class="btn btn-info" onclick="showComponentHistory()">
                                            <i class="fas fa-history"></i> Riwayat Perubahan
                                        </button>
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

<!-- Component History Modal -->
<div class="modal fade" id="componentHistoryModal" tabindex="-1" aria-labelledby="componentHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="componentHistoryModalLabel">Riwayat Perubahan Komponen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <i class="fas fa-history fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Fitur riwayat perubahan komponen akan segera tersedia.</p>
                    <p class="text-muted">Anda dapat melihat perubahan yang telah dilakukan pada komponen ini.</p>
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
    .form-control-plaintext {
        padding: 0.375rem 0;
        margin-bottom: 0;
        color: #212529;
        background-color: transparent;
        border: solid transparent;
        border-width: 1px 0;
    }
    .badge {
        font-size: 0.875em;
    }
    .card-body .text-center {
        padding: 1rem;
    }
    .card-body .text-center i {
        margin-bottom: 0.5rem;
    }
    .text-muted {
        color: #6c757d !important;
    }
</style>
@endpush

@push('scripts')
<script>
function showComponentHistory() {
    $('#componentHistoryModal').modal('show');
}

// Add confirmation for status toggle
document.addEventListener('DOMContentLoaded', function() {
    const toggleButtons = document.querySelectorAll('form[action*="toggle-status"] button');
    toggleButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const isActive = {{ $salaryComponent->is_active ? 'true' : 'false' }};
            const action = isActive ? 'menonaktifkan' : 'mengaktifkan';
            const message = `Apakah Anda yakin ingin ${action} komponen "${document.title}"?`;
            
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
});
</script>
@endpush
