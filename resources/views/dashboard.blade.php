@extends('layouts.app')
@section('title', 'Dashboard - Aplikasi Payroll KlikMedis')
@section('page-title', 'Dashboard')
@section('breadcrumb')
<li class="breadcrumb-item active">Dashboard</li>
@endsection
@section('content')
<!-- Info boxes -->
<div class="row">
    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-info elevation-1"><i class="fas fa-users"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Karyawan</span>
                <span class="info-box-number">{{ $totalEmployees }}</span>
                <span class="info-box-text">
                    <small class="text-muted">Aktif</small>
                </span>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-success elevation-1"><i class="fas fa-money-bill-wave"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Payroll</span>
                <span class="info-box-number">Rp {{ number_format($totalPayroll, 0, ',', '.') }}</span>
                <span class="info-box-text">
                    <small class="text-muted">Bulan Ini</small>
                </span>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-clock"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Kehadiran Hari Ini</span>
                <span class="info-box-number">{{ $todayAttendance }}</span>
                <span class="info-box-text">
                    <small class="text-muted">Dari {{ $totalEmployees }} Karyawan</small>
                </span>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-calendar-times"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Cuti</span>
                <span class="info-box-number">{{ $onLeave }}</span>
                <span class="info-box-text">
                    <small class="text-muted">Hari Ini</small>
                </span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- AREA CHART -->
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-area mr-2"></i>
                    Grafik Kehadiran Bulanan
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                    <button type="button" class="btn btn-tool" data-card-widget="remove">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="chart">
                    <canvas id="areaChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                </div>
            </div>
        </div>
        
        <!-- DONUT CHART -->
        <div class="card card-danger">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-pie mr-2"></i>
                    Distribusi Departemen
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                    <button type="button" class="btn btn-tool" data-card-widget="remove">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <canvas id="donutChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <!-- KARYAWAN TERBARU -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-user-plus mr-2"></i>
                    Karyawan Terbaru
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <ul class="products-list product-list-in-card pl-2 pr-2">
                    @forelse($recentEmployees as $employee)
                    <li class="item">
                        <div class="product-img">
                            <img src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/img/default-150x150.png" alt="Product Image" class="img-size-50">
                        </div>
                        <div class="product-info">
                            <a href="{{ route('employees.show', $employee->id) }}" class="product-title">{{ $employee->name }}
                                <span class="badge badge-warning float-right">{{ $employee->department }}</span></a>
                            <span class="product-description">
                                {{ $employee->position }} - Bergabung {{ $employee->join_date->format('d/m/Y') }}
                            </span>
                        </div>
                    </li>
                    @empty
                    <li class="item">
                        <div class="product-info">
                            <span class="product-description">Belum ada data karyawan</span>
                        </div>
                    </li>
                    @endforelse
                </ul>
            </div>
            <div class="card-footer text-center">
                <a href="{{ route('employees.index') }}" class="uppercase">Lihat Semua Karyawan</a>
            </div>
        </div>
        
        <!-- Calendar -->
        <div class="card bg-gradient-success">
            <div class="card-header border-0">
                <h3 class="card-title">
                    <i class="far fa-calendar-alt"></i>
                    Calendar
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-default btn-sm" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body pt-0">
                <div id="calendar" style="width: 100%"></div>
            </div>
        </div>
        
        <!-- STATISTIK TAMBAHAN -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Statistik Hari Ini
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <div class="info-box bg-warning">
                            <span class="info-box-icon">
                                <i class="fas fa-exclamation-triangle"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Terlambat</span>
                                <span class="info-box-number">{{ $lateToday }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="info-box bg-info">
                            <span class="info-box-icon">
                                <i class="fas fa-clock"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Lembur</span>
                                <span class="info-box-number">{{ $overtimeToday ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- TABLE: PAYROLL TERBARU -->
<div class="card">
    <div class="card-header border-transparent">
        <h3 class="card-title">
            <i class="fas fa-money-bill-wave mr-2"></i>
            Payroll Terbaru
        </h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
            </button>
            <button type="button" class="btn btn-tool" data-card-widget="remove">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table m-0">
                <thead>
                    <tr>
                        <th>ID Karyawan</th>
                        <th>Nama</th>
                        <th>Departemen</th>
                        <th>Gaji Pokok</th>
                        <th>Tunjangan</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentPayroll as $payroll)
                    <tr>
                        <td>{{ $payroll->employee->employee_id ?? 'EMP001' }}</td>
                        <td>{{ $payroll->employee->name ?? 'John Doe' }}</td>
                        <td>{{ $payroll->employee->department ?? 'IT' }}</td>
                        <td>Rp {{ number_format($payroll->basic_salary, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($payroll->allowance, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($payroll->total_salary, 0, ',', '.') }}</td>
                        <td>{!! $payroll->status_badge !!}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">Belum ada data payroll</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer clearfix">
        <a href="{{ route('payrolls.index') }}" class="btn btn-sm btn-info float-left">Lihat Semua Payroll</a>
        <a href="{{ route('payrolls.create') }}" class="btn btn-sm btn-secondary float-right">Buat Payroll Baru</a>
    </div>
</div>
@endsection

@push('js')
<!-- ChartJS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
<script>
$(function () {
    'use strict'
    // Area Chart
    var areaChartCanvas = $('#areaChart').get(0).getContext('2d')
    var areaChartData = {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        datasets: [
            {
                label: 'Kehadiran',
                backgroundColor: 'rgba(60,141,188,0.9)',
                borderColor: 'rgba(60,141,188,0.8)',
                pointRadius: 3,
                pointColor: '#3b8bba',
                pointStrokeColor: 'rgba(60,141,188,1)',
                pointHighlightFill: '#fff',
                pointHighlightStroke: 'rgba(60,141,188,1)',
                data: [28, 48, 40, 19, 86, 27, 90, 85, 78, 65, 45, 32]
            }
        ]
    }
    var areaChartOptions = {
        maintainAspectRatio: false,
        responsive: true,
        legend: {
            display: false
        },
        scales: {
            xAxes: [{
                gridLines: {
                    display: false,
                }
            }],
            yAxes: [{
                gridLines: {
                    display: true,
                }
            }]
        }
    }
    new Chart(areaChartCanvas, {
        type: 'line',
        data: areaChartData,
        options: areaChartOptions
    })
    
    // Donut Chart
    var donutChartCanvas = $('#donutChart').get(0).getContext('2d')
    var donutData = {
        labels: [
            'IT',
            'HR',
            'Finance',
            'Marketing',
            'Sales'
        ],
        datasets: [
            {
                data: [700, 500, 400, 600, 300],
                backgroundColor: ['#f56954', '#00a65a', '#f39c12', '#00c0ef', '#3c8dbc'],
            }
        ]
    }
    var donutOptions = {
        maintainAspectRatio: false,
        responsive: true,
    }
    new Chart(donutChartCanvas, {
        type: 'doughnut',
        data: donutData,
        options: donutOptions
    })
})
</script>
@endpush
