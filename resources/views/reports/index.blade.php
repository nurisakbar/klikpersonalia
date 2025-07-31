@extends('layouts.app')

@section('title', 'Laporan - Aplikasi Payroll KlikMedis')
@section('page-title', 'Laporan')

@section('breadcrumb')
<li class="breadcrumb-item active">Laporan</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Laporan</h3>
                <div class="card-tools">
                    <button class="btn btn-success btn-sm">
                        <i class="fas fa-download"></i> Export
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h5><i class="icon fas fa-info"></i> Info!</h5>
                    Halaman laporan sedang dalam pengembangan. Fitur ini akan segera tersedia.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 