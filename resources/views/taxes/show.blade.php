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
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-eye mr-2"></i>
                        Rincian Pajak
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('taxes.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali
                        </a>
                        <a href="{{ route('taxes.edit', $tax->id) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit mr-1"></i> Edit
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Informasi Karyawan -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-user mr-2"></i>
                                        Informasi Karyawan
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td width="30%"><strong>Nama:</strong></td>
                                            <td>{{ $tax->employee->name ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>ID Karyawan:</strong></td>
                                            <td>{{ $tax->employee->employee_id ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Jabatan:</strong></td>
                                            <td>{{ $tax->employee->position ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Departemen:</strong></td>
                                            <td>{{ $tax->employee->department ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Email:</strong></td>
                                            <td>{{ $tax->employee->email ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Telepon:</strong></td>
                                            <td>{{ $tax->employee->phone ?? '-' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Informasi Pajak -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-calculator mr-2"></i>
                                        Informasi Pajak
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td width="30%"><strong>Periode Pajak:</strong></td>
                                            <td>{{ $tax->tax_period_formatted }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Status PTKP:</strong></td>
                                            <td>{{ $tax->ptkp_status ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Status Pajak:</strong></td>
                                            <td>{!! $tax->status_badge !!}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Tanggal Dibuat:</strong></td>
                                            <td>{{ $tax->created_at_formatted }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Terakhir Diupdate:</strong></td>
                                            <td>{{ $tax->updated_at_formatted }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Sumber Data:</strong></td>
                                            <td>
                                                @if($tax->payroll)
                                                    <span class="badge badge-info">Data Payroll</span>
                                                @else
                                                    <span class="badge badge-warning">Gaji Pokok</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Rincian Perhitungan Pajak -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-chart-line mr-2"></i>
                                        Rincian Perhitungan Pajak
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th width="40%">Komponen</th>
                                                    <th class="text-right">Jumlah</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><strong>Pendapatan Kena Pajak</strong></td>
                                                    <td class="text-right">{{ $tax->taxable_income_formatted }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Jumlah PTKP ({{ $tax->ptkp_status ?? '-' }})</strong></td>
                                                    <td class="text-right text-success">- {{ $tax->ptkp_amount_formatted }}</td>
                                                </tr>
                                                <tr class="table-info">
                                                    <td><strong>Dasar Pengenaan Pajak</strong></td>
                                                    <td class="text-right"><strong>{{ $tax->taxable_base_formatted }}</strong></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Tarif Pajak</strong></td>
                                                    <td class="text-right">{{ $tax->tax_rate_formatted }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Bracket Pajak</strong></td>
                                                    <td class="text-right">{{ $tax->tax_bracket ?? '-' }}</td>
                                                </tr>
                                                <tr class="table-warning">
                                                    <td><strong>Jumlah Pajak</strong></td>
                                                    <td class="text-right"><strong>{{ $tax->tax_amount_formatted }}</strong></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($tax->notes)
                    <!-- Catatan -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-sticky-note mr-2"></i>
                                        Catatan
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-info mb-0">
                                        <i class="fas fa-info-circle mr-2"></i>
                                        {{ $tax->notes }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($tax->payroll)
                    <!-- Data Payroll Terkait -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-file-invoice mr-2"></i>
                                        Data Payroll Terkait
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th width="40%">Komponen</th>
                                                    <th class="text-right">Jumlah</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>Gaji Pokok</td>
                                                    <td class="text-right">{{ number_format($tax->payroll->basic_salary ?? 0, 0, ',', '.') }}</td>
                                                </tr>
                                                <tr>
                                                    <td>Tunjangan</td>
                                                    <td class="text-right text-success">+ {{ number_format($tax->payroll->allowances ?? 0, 0, ',', '.') }}</td>
                                                </tr>
                                                <tr>
                                                    <td>Lembur</td>
                                                    <td class="text-right text-success">+ {{ number_format($tax->payroll->overtime ?? 0, 0, ',', '.') }}</td>
                                                </tr>
                                                <tr>
                                                    <td>Bonus</td>
                                                    <td class="text-right text-success">+ {{ number_format($tax->payroll->bonus ?? 0, 0, ',', '.') }}</td>
                                                </tr>
                                                <tr class="table-success">
                                                    <td><strong>Total Pendapatan</strong></td>
                                                    <td class="text-right"><strong>{{ number_format(($tax->payroll->basic_salary ?? 0) + ($tax->payroll->allowances ?? 0) + ($tax->payroll->overtime ?? 0) + ($tax->payroll->bonus ?? 0), 0, ',', '.') }}</strong></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 