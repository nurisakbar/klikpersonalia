@extends('layouts.app')

@section('title', 'Detail Assignment Komponen Gaji')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Detail Assignment Komponen Gaji</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item">Master Data</li>
                    <li class="breadcrumb-item"><a href="{{ route('employee-salary-components.index') }}">Assignment Komponen</a></li>
                    <li class="breadcrumb-item active">Detail</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8">
                <!-- Main Information Card -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Informasi Assignment</h3>
                        <div class="card-tools">
                            <a href="{{ route('employee-salary-components.edit', $employeeSalaryComponent) }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <button type="button" class="btn btn-{{ $employeeSalaryComponent->is_active ? 'secondary' : 'success' }} btn-sm" 
                                    onclick="toggleStatus('{{ $employeeSalaryComponent->id }}')">
                                <i class="fas fa-{{ $employeeSalaryComponent->is_active ? 'pause' : 'play' }}"></i>
                                {{ $employeeSalaryComponent->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-muted">Informasi Karyawan</h6>
                                <table class="table table-sm">
                                    <tr><td>Nama:</td><td><strong>{{ $employeeSalaryComponent->employee->name }}</strong></td></tr>
                                    <tr><td>ID Karyawan:</td><td><strong>{{ $employeeSalaryComponent->employee->employee_id }}</strong></td></tr>
                                    <tr><td>Departemen:</td><td><strong>{{ $employeeSalaryComponent->employee->department }}</strong></td></tr>
                                    <tr><td>Jabatan:</td><td><strong>{{ $employeeSalaryComponent->employee->position }}</strong></td></tr>
                                    <tr><td>Gaji Pokok:</td><td><strong>Rp {{ number_format($employeeSalaryComponent->employee->basic_salary, 0, ',', '.') }}</strong></td></tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">Informasi Komponen</h6>
                                <table class="table table-sm">
                                    <tr><td>Nama Komponen:</td><td><strong>{{ $employeeSalaryComponent->salaryComponent->name }}</strong></td></tr>
                                    <tr><td>Tipe:</td><td><span class="badge badge-{{ $employeeSalaryComponent->salaryComponent->type === 'earning' ? 'success' : 'danger' }}">{{ $employeeSalaryComponent->salaryComponent->type_text }}</span></td></tr>
                                    <tr><td>Deskripsi:</td><td><strong>{{ $employeeSalaryComponent->salaryComponent->description ?: '-' }}</strong></td></tr>
                                    <tr><td>Nilai Default:</td><td><strong>Rp {{ number_format($employeeSalaryComponent->salaryComponent->default_value, 0, ',', '.') }}</strong></td></tr>
                                    <tr><td>Pajak:</td><td><span class="badge badge-{{ $employeeSalaryComponent->salaryComponent->is_taxable ? 'success' : 'secondary' }}">{{ $employeeSalaryComponent->salaryComponent->is_taxable ? 'Ya' : 'Tidak' }}</span></td></tr>
                                </table>
                            </div>
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-muted">Konfigurasi Assignment</h6>
                                <table class="table table-sm">
                                    <tr><td>Tipe Perhitungan:</td><td><strong>{{ $employeeSalaryComponent->calculation_type_text }}</strong></td></tr>
                                    <tr><td>Nilai:</td><td><strong>{{ $employeeSalaryComponent->formatted_amount }}</strong></td></tr>
                                    @if($employeeSalaryComponent->calculation_type === 'percentage')
                                    <tr><td>Persentase:</td><td><strong>{{ $employeeSalaryComponent->percentage_value }}%</strong></td></tr>
                                    <tr><td>Referensi:</td><td><strong>{{ $employeeSalaryComponent->reference_type_text }}</strong></td></tr>
                                    @endif
                                    <tr><td>Status:</td><td><span class="badge badge-{{ $employeeSalaryComponent->is_active ? 'success' : 'danger' }}">{{ $employeeSalaryComponent->is_active ? 'Aktif' : 'Tidak Aktif' }}</span></td></tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">Periode Berlaku</h6>
                                <table class="table table-sm">
                                    <tr><td>Tanggal Efektif:</td><td><strong>{{ $employeeSalaryComponent->effective_date ? $employeeSalaryComponent->effective_date->format('d/m/Y') : 'Sekarang' }}</strong></td></tr>
                                    <tr><td>Tanggal Expired:</td><td><strong>{{ $employeeSalaryComponent->expiry_date ? $employeeSalaryComponent->expiry_date->format('d/m/Y') : 'Selamanya' }}</strong></td></tr>
                                    <tr><td>Status Berlaku:</td><td><span class="badge badge-{{ $employeeSalaryComponent->isCurrentlyEffective() ? 'success' : 'warning' }}">{{ $employeeSalaryComponent->isCurrentlyEffective() ? 'Berlaku' : 'Tidak Berlaku' }}</span></td></tr>
                                    <tr><td>Dibuat:</td><td><strong>{{ $employeeSalaryComponent->created_at->format('d/m/Y H:i') }}</strong></td></tr>
                                    <tr><td>Terakhir Update:</td><td><strong>{{ $employeeSalaryComponent->updated_at->format('d/m/Y H:i') }}</strong></td></tr>
                                </table>
                            </div>
                        </div>

                        @if($employeeSalaryComponent->notes)
                        <hr>
                        <div class="row">
                            <div class="col-12">
                                <h6 class="text-muted">Catatan</h6>
                                <div class="alert alert-info">
                                    {{ $employeeSalaryComponent->notes }}
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Calculation Preview Card -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Preview Perhitungan</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Gaji Pokok:</h6>
                                <p class="h4 text-primary">Rp {{ number_format($employeeSalaryComponent->employee->basic_salary, 0, ',', '.') }}</p>
                            </div>
                            <div class="col-md-6">
                                <h6>Komponen {{ $employeeSalaryComponent->salaryComponent->type_text }}:</h6>
                                @if($employeeSalaryComponent->calculation_type === 'fixed')
                                    <p class="h4 text-{{ $employeeSalaryComponent->salaryComponent->type === 'earning' ? 'success' : 'danger' }}">
                                        {{ $employeeSalaryComponent->salaryComponent->type === 'earning' ? '+' : '-' }} Rp {{ number_format($employeeSalaryComponent->amount, 0, ',', '.') }}
                                    </p>
                                @else
                                    <p class="h4 text-{{ $employeeSalaryComponent->salaryComponent->type === 'earning' ? 'success' : 'danger' }}">
                                        {{ $employeeSalaryComponent->salaryComponent->type === 'earning' ? '+' : '-' }} {{ $employeeSalaryComponent->percentage_value }}% dari {{ $employeeSalaryComponent->reference_type_text }}
                                    </p>
                                    <p class="text-muted">
                                        = {{ $employeeSalaryComponent->salaryComponent->type === 'earning' ? '+' : '-' }} Rp {{ number_format($employeeSalaryComponent->calculateAmount($employeeSalaryComponent->employee->basic_salary), 0, ',', '.') }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Action Buttons -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Aksi</h3>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('employee-salary-components.edit', $employeeSalaryComponent) }}" class="btn btn-warning btn-block">
                                <i class="fas fa-edit"></i> Edit Assignment
                            </a>
                            
                            <button type="button" class="btn btn-{{ $employeeSalaryComponent->is_active ? 'secondary' : 'success' }} btn-block" 
                                    onclick="toggleStatus('{{ $employeeSalaryComponent->id }}')">
                                <i class="fas fa-{{ $employeeSalaryComponent->is_active ? 'pause' : 'play' }}"></i>
                                {{ $employeeSalaryComponent->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                            </button>
                            
                            <button type="button" class="btn btn-danger btn-block" 
                                    onclick="deleteAssignment('{{ $employeeSalaryComponent->id }}')">
                                <i class="fas fa-trash"></i> Hapus Assignment
                            </button>
                            
                            <a href="{{ route('employee-salary-components.index') }}" class="btn btn-secondary btn-block">
                                <i class="fas fa-arrow-left"></i> Kembali ke Daftar
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Related Information -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Informasi Terkait</h3>
                    </div>
                    <div class="card-body">
                        <h6>Komponen Lainnya:</h6>
                        @php
                            $otherComponents = $employeeSalaryComponent->employee->salaryComponents()
                                ->where('id', '!=', $employeeSalaryComponent->id)
                                ->with('salaryComponent')
                                ->get();
                        @endphp
                        
                        @if($otherComponents->count() > 0)
                            <ul class="list-unstyled">
                                @foreach($otherComponents as $component)
                                <li class="mb-2">
                                    <a href="{{ route('employee-salary-components.show', $component) }}" class="text-decoration-none">
                                        <span class="badge badge-{{ $component->salaryComponent->type === 'earning' ? 'success' : 'danger' }} mr-1">
                                            {{ $component->salaryComponent->type === 'earning' ? 'Pendapatan' : 'Potongan' }}
                                        </span>
                                        {{ $component->salaryComponent->name }}
                                    </a>
                                </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-muted">Tidak ada komponen lain yang di-assign</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
function toggleStatus(id) {
    Swal.fire({
        title: 'Konfirmasi',
        text: 'Apakah Anda yakin ingin mengubah status assignment ini?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Ubah!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('{{ route("employee-salary-components.toggle-status", ":id") }}'.replace(':id', id))
                .done(function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    }
                })
                .fail(function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Gagal mengubah status assignment.'
                    });
                });
        }
    });
}

function deleteAssignment(id) {
    Swal.fire({
        title: 'Konfirmasi Hapus',
        text: 'Apakah Anda yakin ingin menghapus assignment ini? Tindakan ini tidak dapat dibatalkan.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route("employee-salary-components.destroy", ":id") }}'.replace(':id', id),
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Assignment berhasil dihapus.',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = '{{ route("employee-salary-components.index") }}';
                    });
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Gagal menghapus assignment.'
                    });
                }
            });
        }
    });
}
</script>
@endpush
