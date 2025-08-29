@extends('layouts.app')

@section('title', 'Rincian Payroll - Aplikasi Payroll KlikMedis')
@section('page-title', 'Rincian Payroll')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('payrolls.index') }}">Payroll</a></li>
<li class="breadcrumb-item active">Rincian</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Employee Information -->
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
                            <td>{{ $payroll->employee->name }}</td>
                        </tr>
                        <tr>
                            <td><strong>ID Karyawan:</strong></td>
                            <td>{{ $payroll->employee->employee_id }}</td>
                        </tr>
                        <tr>
                            <td><strong>Departemen:</strong></td>
                            <td>{{ $payroll->employee->department }}</td>
                        </tr>
                        <tr>
                            <td><strong>Jabatan:</strong></td>
                            <td>{{ $payroll->employee->position }}</td>
                        </tr>
                        <tr>
                            <td><strong>Periode:</strong></td>
                            <td>{{ $payroll->formatted_period }}</td>
                        </tr>
                        <tr>
                            <td><strong>Status:</strong></td>
                            <td>{!! $payroll->status_badge !!}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Payroll Summary -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-money-bill-wave mr-2"></i>
                        Ringkasan Payroll
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td width="40%"><strong>Gaji Pokok:</strong></td>
                                    <td class="text-right">{{ $payroll->formatted_basic_salary }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Tunjangan:</strong></td>
                                    <td class="text-right text-success">+ {{ number_format($payroll->allowance, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Lembur:</strong></td>
                                    <td class="text-right text-success">+ {{ number_format($payroll->overtime, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Bonus:</strong></td>
                                    <td class="text-right text-success">+ {{ number_format($payroll->bonus, 0, ',', '.') }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td width="40%"><strong>Potongan:</strong></td>
                                    <td class="text-right text-danger">- {{ number_format($payroll->deduction, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Pajak:</strong></td>
                                    <td class="text-right text-danger">- {{ number_format($payroll->tax_amount, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>BPJS:</strong></td>
                                    <td class="text-right text-danger">- {{ number_format($payroll->bpjs_amount, 0, ',', '.') }}</td>
                                </tr>
                                <tr class="table-primary">
                                    <td><strong>Total Gaji:</strong></td>
                                    <td class="text-right"><strong>{{ $payroll->formatted_total_salary }}</strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Information -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-info-circle mr-2"></i>
                        Informasi Tambahan
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td width="30%"><strong>Dibuat Oleh:</strong></td>
                            <td>{{ $payroll->generatedBy->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Dibuat Pada:</strong></td>
                            <td>{{ $payroll->generated_at ? $payroll->generated_at->format('d/m/Y H:i') : '-' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Tanggal Dibuat:</strong></td>
                            <td>{{ $payroll->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Terakhir Diupdate:</strong></td>
                            <td>{{ $payroll->updated_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-sticky-note mr-2"></i>
                        Catatan
                    </h5>
                </div>
                <div class="card-body">
                    @if($payroll->notes)
                        <div class="p-3 bg-light rounded">
                            {{ $payroll->notes }}
                        </div>
                    @else
                        <p class="text-muted">Tidak ada catatan tersedia.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
