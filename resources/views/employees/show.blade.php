@extends('layouts.app')

@section('title', 'Detail Karyawan - Aplikasi Payroll KlikMedis')
@section('page-title', 'Detail Karyawan')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('employees.index') }}">Karyawan</a></li>
<li class="breadcrumb-item active">Detail</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-4">
        <!-- Profile Image -->
        <div class="card card-primary card-outline">
            <div class="card-body box-profile">
                <div class="text-center">
                    <img class="profile-user-img img-fluid img-circle" src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/img/user4-128x128.jpg" alt="User profile picture">
                </div>

                <h3 class="profile-username text-center">{{ $employee->name }}</h3>

                <p class="text-muted text-center">{{ $employee->position }}</p>

                <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item">
                        <b>ID Karyawan</b> <a class="float-right">{{ $employee->employee_id }}</a>
                    </li>
                    <li class="list-group-item">
                        <b>Departemen</b> <a class="float-right">{{ $employee->department }}</a>
                    </li>
                    <li class="list-group-item">
                        <b>Status</b> <a class="float-right">
                            @if($employee->status == 'active')
                                <span class="badge badge-success">Aktif</span>
                            @elseif($employee->status == 'inactive')
                                <span class="badge badge-warning">Tidak Aktif</span>
                            @else
                                <span class="badge badge-danger">Berhenti</span>
                            @endif
                        </a>
                    </li>
                </ul>

                <a href="{{ route('employees.edit', $employee->id) }}" class="btn btn-primary btn-block">
                    <i class="fas fa-edit"></i> Edit Karyawan
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header p-2">
                <ul class="nav nav-pills">
                    <li class="nav-item"><a class="nav-link active" href="#info" data-toggle="tab">Informasi</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contact" data-toggle="tab">Kontak</a></li>
                    <li class="nav-item"><a class="nav-link" href="#bank" data-toggle="tab">Bank</a></li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <div class="active tab-pane" id="info">
                        <div class="row">
                            <div class="col-md-6">
                                <strong><i class="fas fa-user mr-1"></i> Nama Lengkap</strong>
                                <p class="text-muted">{{ $employee->name }}</p>
                            </div>
                            <div class="col-md-6">
                                <strong><i class="fas fa-envelope mr-1"></i> Email</strong>
                                <p class="text-muted">{{ $employee->email }}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <strong><i class="fas fa-briefcase mr-1"></i> Jabatan</strong>
                                <p class="text-muted">{{ $employee->position }}</p>
                            </div>
                            <div class="col-md-6">
                                <strong><i class="fas fa-building mr-1"></i> Departemen</strong>
                                <p class="text-muted">{{ $employee->department }}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <strong><i class="fas fa-calendar mr-1"></i> Tanggal Bergabung</strong>
                                <p class="text-muted">{{ date('d/m/Y', strtotime($employee->join_date)) }}</p>
                            </div>
                            <div class="col-md-6">
                                <strong><i class="fas fa-money-bill mr-1"></i> Gaji Pokok</strong>
                                <p class="text-muted">Rp {{ number_format($employee->basic_salary, 0, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane" id="contact">
                        <div class="row">
                            <div class="col-md-6">
                                <strong><i class="fas fa-phone mr-1"></i> Nomor Telepon</strong>
                                <p class="text-muted">{{ $employee->phone }}</p>
                            </div>
                            <div class="col-md-6">
                                <strong><i class="fas fa-map-marker-alt mr-1"></i> Alamat</strong>
                                <p class="text-muted">{{ $employee->address ?? 'Belum diisi' }}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <strong><i class="fas fa-phone-alt mr-1"></i> Kontak Darurat</strong>
                                <p class="text-muted">{{ $employee->emergency_contact ?? 'Belum diisi' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane" id="bank">
                        <div class="row">
                            <div class="col-md-6">
                                <strong><i class="fas fa-university mr-1"></i> Nama Bank</strong>
                                <p class="text-muted">{{ $employee->bank_name ?? 'Belum diisi' }}</p>
                            </div>
                            <div class="col-md-6">
                                <strong><i class="fas fa-credit-card mr-1"></i> Nomor Rekening</strong>
                                <p class="text-muted">{{ $employee->bank_account ?? 'Belum diisi' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 