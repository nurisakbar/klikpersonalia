@extends('layouts.app')

@section('title', 'Pengaturan & Konfigurasi - Aplikasi Payroll KlikMedis')
@section('page-title', 'Pengaturan & Konfigurasi')

@section('breadcrumb')
<li class="breadcrumb-item active">Pengaturan & Konfigurasi</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <!-- Company Settings -->
                        <div class="col-lg-4 col-md-6">
                            <div class="card card-primary card-outline">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-building mr-2"></i>
                                        Pengaturan Perusahaan
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p class="card-text">Mengelola profil perusahaan, logo, dan informasi dasar.</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge badge-primary">Admin/HR</span>
                                        <a href="{{ route('settings.company') }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-edit mr-1"></i> Konfigurasi
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payroll Policy -->
                        <div class="col-lg-4 col-md-6">
                            <div class="card card-success card-outline">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-money-bill-wave mr-2"></i>
                                        Kebijakan Payroll
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p class="card-text">Mengkonfigurasi tarif lembur, bonus kehadiran, dan aturan payroll.</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge badge-success">Admin/HR</span>
                                        <a href="{{ route('settings.payroll-policy') }}" class="btn btn-success btn-sm">
                                            <i class="fas fa-edit mr-1"></i> Konfigurasi
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Leave Policy -->
                        <div class="col-lg-4 col-md-6">
                            <div class="card card-info card-outline">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-calendar-times mr-2"></i>
                                        Kebijakan Cuti
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p class="card-text">Mengatur kuota cuti, aturan persetujuan, dan kebijakan pembawa ke depan.</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge badge-info">Admin/HR</span>
                                        <a href="{{ route('settings.leave-policy') }}" class="btn btn-info btn-sm">
                                            <i class="fas fa-edit mr-1"></i> Konfigurasi
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- User Profile -->
                        <div class="col-lg-4 col-md-6">
                            <div class="card card-warning card-outline">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-user mr-2"></i>
                                        Profil Pengguna
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p class="card-text">Memperbarui informasi pribadi dan foto profil Anda.</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge badge-warning">All Users</span>
                                        <a href="{{ route('settings.profile') }}" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit mr-1"></i> Konfigurasi
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Password -->
                        <div class="col-lg-4 col-md-6">
                            <div class="card card-danger card-outline">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-lock mr-2"></i>
                                        Password Akun
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p class="card-text">Ganti password akun Anda dengan aman.</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge badge-danger">All Users</span>
                                        <a href="{{ route('settings.password') }}" class="btn btn-danger btn-sm">
                                            <i class="fas fa-edit mr-1"></i> Konfigurasi
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- User Management -->
                        <div class="col-lg-4 col-md-6">
                            <div class="card card-secondary card-outline">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-users mr-2"></i>
                                        Manajemen Pengguna
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p class="card-text">Mengelola pengguna, peran, dan izin untuk perusahaan Anda.</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge badge-secondary">Admin/HR</span>
                                        <a href="{{ route('settings.users') }}" class="btn btn-secondary btn-sm">
                                            <i class="fas fa-edit mr-1"></i> Konfigurasi
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- System Settings -->
                        <div class="col-lg-4 col-md-6">
                            <div class="card card-dark card-outline">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-server mr-2"></i>
                                        Pengaturan Sistem
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p class="card-text">Mengkonfigurasi pengaturan sistem seperti zona waktu dan mata uang.</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge badge-dark">Admin/HR</span>
                                        <a href="{{ route('settings.system') }}" class="btn btn-dark btn-sm">
                                            <i class="fas fa-edit mr-1"></i> Konfigurasi
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Backup & Restore -->
                        <div class="col-lg-4 col-md-6">
                            <div class="card card-light card-outline">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-database mr-2"></i>
                                        Backup & Restore Data
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p class="card-text">Membuat cadangan database dan mengelola pemulihan data.</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge badge-light text-dark">Admin/HR</span>
                                        <a href="{{ route('settings.backup') }}" class="btn btn-light btn-sm text-dark">
                                            <i class="fas fa-edit mr-1"></i> Konfigurasi
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Current Settings Summary -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-info-circle mr-2"></i>
                                        Ringkasan Pengaturan Saat Ini
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6>Informasi Perusahaan</h6>
                                            <table class="table table-sm">
                                                <tr>
                                                    <td><strong>Nama Perusahaan:</strong></td>
                                                    <td>{{ $company->name }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Email Perusahaan:</strong></td>
                                                    <td>{{ $company->email }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Telepon Perusahaan:</strong></td>
                                                    <td>{{ $company->phone }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Alamat Perusahaan:</strong></td>
                                                    <td>{{ $company->address }}, {{ $company->city }}</td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <h6>Informasi Sistem</h6>
                                            <table class="table table-sm">
                                                <tr>
                                                    <td><strong>Plan Langganan:</strong></td>
                                                    <td><span class="badge badge-{{ $company->subscription_plan === 'premium' ? 'success' : 'warning' }}">{{ ucfirst($company->subscription_plan) }}</span></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Maksimal Karyawan:</strong></td>
                                                    <td>{{ $company->max_employees }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Status Perusahaan:</strong></td>
                                                    <td><span class="badge badge-{{ $company->status === 'active' ? 'success' : 'danger' }}">{{ ucfirst($company->status) }}</span></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Dibuat:</strong></td>
                                                    <td>{{ $company->created_at->format('d/m/Y H:i') }}</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-bolt mr-2"></i>
                                        Aksi Cepat
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('settings.company') }}" class="btn btn-outline-primary">
                                            <i class="fas fa-building mr-1"></i> Perbarui Info Perusahaan
                                        </a>
                                        <a href="{{ route('settings.profile') }}" class="btn btn-outline-warning">
                                            <i class="fas fa-user mr-1"></i> Perbarui Profil
                                        </a>
                                        <a href="{{ route('settings.password') }}" class="btn btn-outline-danger">
                                            <i class="fas fa-lock mr-1"></i> Ganti Password
                                        </a>
                                        @if(in_array(auth()->user()->role, ['admin']))
                                        <a href="{{ route('settings.users') }}" class="btn btn-outline-secondary">
                                            <i class="fas fa-users mr-1"></i> Manage Users
                                        </a>
                                        @endif
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

@push('styles')
<style>
.card-outline {
    border-top: 3px solid #007bff;
}

.card-outline.card-primary {
    border-top-color: #007bff;
}

.card-outline.card-success {
    border-top-color: #28a745;
}

.card-outline.card-info {
    border-top-color: #17a2b8;
}

.card-outline.card-warning {
    border-top-color: #ffc107;
}

.card-outline.card-danger {
    border-top-color: #dc3545;
}

.card-outline.card-secondary {
    border-top-color: #6c757d;
}

.card-outline.card-dark {
    border-top-color: #343a40;
}

.card-outline.card-light {
    border-top-color: #f8f9fa;
}

.card {
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
</style>
@endpush 