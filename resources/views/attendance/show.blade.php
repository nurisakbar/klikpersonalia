@extends('layouts.app')

@section('title', 'Detail Absensi - Aplikasi Payroll KlikMedis')
@section('page-title', 'Detail Absensi')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('attendance.index') }}">Absensi</a></li>
<li class="breadcrumb-item active">Detail</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Detail Absensi</h3>
                <div class="card-tools">
                    <a href="{{ route('attendance.edit', $attendance->id) }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <a href="{{ route('attendance.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td width="150"><strong>Nama Karyawan</strong></td>
                                <td>: {{ $attendance->employee->name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Departemen</strong></td>
                                <td>: {{ $attendance->employee->department }}</td>
                            </tr>
                            <tr>
                                <td><strong>Posisi</strong></td>
                                <td>: {{ $attendance->employee->position }}</td>
                            </tr>
                            <tr>
                                <td><strong>Tanggal</strong></td>
                                <td>: {{ $attendance->date->format('d/m/Y') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Status</strong></td>
                                <td>: {!! $attendance->status_badge !!}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td width="150"><strong>Check In</strong></td>
                                <td>: {{ $attendance->check_in ? $attendance->check_in->format('H:i:s') : '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Check Out</strong></td>
                                <td>: {{ $attendance->check_out ? $attendance->check_out->format('H:i:s') : '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Total Jam</strong></td>
                                <td>: {{ $attendance->total_hours ? $attendance->total_hours . ' jam' : '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Jam Lembur</strong></td>
                                <td>: {{ $attendance->overtime_hours ? $attendance->overtime_hours . ' jam' : '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Catatan</strong></td>
                                <td>: {{ $attendance->notes ?: '-' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Additional Information -->
                @if($attendance->check_in_location || $attendance->check_in_ip || $attendance->check_in_device)
                <hr>
                <h5><i class="fas fa-info-circle"></i> Informasi Teknis</h5>
                <div class="row">
                    <div class="col-md-6">
                        <h6>Check In</h6>
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td width="120"><strong>Lokasi</strong></td>
                                <td>: {{ $attendance->check_in_location ?: '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>IP Address</strong></td>
                                <td>: {{ $attendance->check_in_ip ?: '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Device</strong></td>
                                <td>: {{ $attendance->check_in_device ?: '-' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>Check Out</h6>
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td width="120"><strong>Lokasi</strong></td>
                                <td>: {{ $attendance->check_out_location ?: '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>IP Address</strong></td>
                                <td>: {{ $attendance->check_out_ip ?: '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Device</strong></td>
                                <td>: {{ $attendance->check_out_device ?: '-' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                @endif

                <!-- Timestamp Information -->
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <small class="text-muted">
                            <i class="fas fa-clock"></i> Dibuat: {{ $attendance->created_at->format('d/m/Y H:i:s') }}
                        </small>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted">
                            <i class="fas fa-edit"></i> Diupdate: {{ $attendance->updated_at->format('d/m/Y H:i:s') }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 