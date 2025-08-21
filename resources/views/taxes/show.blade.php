@extends('layouts.app')

@section('title', 'Rincian Pajak - Aplikasi Payroll KlikMedis')
@section('page-title', 'Rincian Pajak')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('taxes.index') }}">Pajak</a></li>
<li class="breadcrumb-item active">Rincian</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-4">
            <!-- Profile Image -->
            <div class="card card-primary card-outline">
                <div class="card-body box-profile">
                    <div class="text-center">
                        <i class="fas fa-calculator fa-4x text-primary"></i>
                    </div>

                    <h3 class="profile-username text-center">{{ $tax->employee->name }}</h3>

                    <p class="text-muted text-center">{{ $tax->employee->position }}</p>

                    <ul class="list-group list-group-unbordered mb-3">
                        <li class="list-group-item">
                            <b>ID Karyawan</b> <a class="float-right">{{ $tax->employee->employee_id }}</a>
                        </li>
                        <li class="list-group-item">
                            <b>Departemen</b> <a class="float-right">{{ $tax->employee->department }}</a>
                        </li>
                        <li class="list-group-item">
                            <b>Status Pajak</b> <a class="float-right">
                                @if($tax->status == 'pending')
                                    <span class="badge badge-secondary">Menunggu</span>
                                @elseif($tax->status == 'calculated')
                                    <span class="badge badge-info">Dihitung</span>
                                @elseif($tax->status == 'paid')
                                    <span class="badge badge-success">Dibayar</span>
                                @elseif($tax->status == 'verified')
                                    <span class="badge badge-primary">Terverifikasi</span>
                                @else
                                    <span class="badge badge-secondary">{{ ucfirst($tax->status) }}</span>
                                @endif
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <!-- Tax Calculation Summary -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-calculator"></i> Ringkasan Perhitungan Pajak
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-info">
                                    <i class="fas fa-money-bill"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Pendapatan Kena Pajak</span>
                                    <span class="info-box-number">Rp {{ number_format($tax->taxable_income ?? 0, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning">
                                    <i class="fas fa-shield-alt"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">PTKP</span>
                                    <span class="info-box-number">Rp {{ number_format($tax->ptkp_amount ?? 0, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-danger">
                                    <i class="fas fa-percentage"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Tarif Pajak</span>
                                    <span class="info-box-number">{{ number_format(($tax->tax_rate ?? 0) * 100, 1) }}%</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-success">
                                    <i class="fas fa-coins"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Jumlah Pajak</span>
                                    <span class="info-box-number">Rp {{ number_format($tax->tax_amount ?? 0, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rincianed Information -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle"></i> Informasi Rincian
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-calendar mr-1"></i> Periode Pajak</label>
                                <p class="form-control-static">
                                    @if($tax->tax_period)
                                        {{ \Carbon\Carbon::createFromFormat('Y-m', $tax->tax_period)->format('F Y') }}
                                    @else
                                        -
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-user mr-1"></i> Status PTKP</label>
                                <p class="form-control-static">{{ $tax->ptkp_status ?? '-' }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-layer-group mr-1"></i> Lapisan Pajak</label>
                                <p class="form-control-static">{{ $tax->tax_bracket ?? '-' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-calculator mr-1"></i> Dasar Pengenaan Pajak</label>
                                <p class="form-control-static">Rp {{ number_format($tax->taxable_base ?? 0, 0, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label><i class="fas fa-sticky-note mr-1"></i> Catatan</label>
                                <p class="form-control-static">{{ $tax->notes ?? 'Tidak ada catatan' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- History Information -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-history"></i> Riwayat
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-calendar-plus mr-1"></i> Dibuat Pada</label>
                                <p class="form-control-static">{{ $tax->created_at ? $tax->created_at->format('d/m/Y H:i') : '-' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-calendar-check mr-1"></i> Diperbarui Pada</label>
                                <p class="form-control-static">{{ $tax->updated_at ? $tax->updated_at->format('d/m/Y H:i') : '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 