@extends('layouts.app')
@section('title', 'Detail Jabatan')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td width="150"><strong>Kode Jabatan</strong></td>
                                    <td>: -</td>
                                </tr>
                                <tr>
                                    <td><strong>Nama Jabatan</strong></td>
                                    <td>: {{ $position->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Status</strong></td>
                                    <td>: {!! $position->status_badge !!}</td>
                                </tr>
                                <tr>
                                    <td><strong>Jumlah Karyawan</strong></td>
                                    <td>: {{ $position->employees->count() }} orang</td>
                                </tr>
                                <tr>
                                    <td><strong>Dibuat</strong></td>
                                    <td>: {{ $position->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Terakhir Update</strong></td>
                                    <td>: {{ $position->updated_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>Deskripsi</strong></label>
                                <div class="border rounded p-3 bg-light">
                                    {{ $position->description ?: 'Tidak ada deskripsi' }}
                                </div>
                            </div>
                        </div>
                    </div>
                    @if($position->employees->count() > 0)
                    <div class="mt-4">
                        <h5><i class="fas fa-users mr-2"></i> Daftar Karyawan</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama</th>
                                        <th>Email</th>
                                        <th>Departemen</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($position->employees as $index => $employee)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $employee->name }}</td>
                                        <td>{{ $employee->email }}</td>
                                        <td>{{ $employee->department->name ?? '-' }}</td>
                                        <td>
                                            @if($employee->status)
                                                <span class="badge badge-success">Active</span>
                                            @else
                                                <span class="badge badge-danger">Inactive</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
