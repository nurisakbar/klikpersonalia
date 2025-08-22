@extends('layouts.app')

@section('title', 'Sisa Cuti - Aplikasi Payroll KlikMedis')
@section('page-title', 'Sisa Cuti')

@section('breadcrumb')
<li class="breadcrumb-item active">Sisa Cuti</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
      
            <!-- Loading Spinner -->
            <div id="loadingSpinner" class="text-center py-4" style="display: none;">
                <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                <p class="mt-2">Memuat data sisa cuti...</p>
            </div>

            <!-- Content Container -->
            <div id="balanceContent">
                <div class="row">
                    <!-- Annual Leave -->
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Cuti Tahunan
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="annual-remaining">
                                            {{ $leaveBalance['annual_remaining'] }}/{{ $leaveBalance['annual_total'] }}
                                        </div>
                                        <div class="text-xs text-muted" id="annual-used">
                                            {{ $leaveBalance['annual_used'] }} hari digunakan
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                                <div class="progress mt-2" style="height: 4px;">
                                    <div class="progress-bar bg-primary" id="annual-progress" style="width: {{ $leaveBalance['annual_total'] > 0 ? ($leaveBalance['annual_used'] / $leaveBalance['annual_total']) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sick Leave -->
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card border-left-warning shadow h-100">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Cuti Sakit
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="sick-remaining">
                                            {{ $leaveBalance['sick_remaining'] }}/{{ $leaveBalance['sick_total'] }}
                                        </div>
                                        <div class="text-xs text-muted" id="sick-used">
                                            {{ $leaveBalance['sick_used'] }} hari digunakan
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-user-injured fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                                <div class="progress mt-2" style="height: 4px;">
                                    <div class="progress-bar bg-warning" id="sick-progress" style="width: {{ $leaveBalance['sick_total'] > 0 ? ($leaveBalance['sick_used'] / $leaveBalance['sick_total']) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Maternity Leave -->
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Cuti Melahirkan
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="maternity-remaining">
                                            {{ $leaveBalance['maternity_remaining'] }}/{{ $leaveBalance['maternity_total'] }}
                                        </div>
                                        <div class="text-xs text-muted" id="maternity-used">
                                            {{ $leaveBalance['maternity_used'] }} hari digunakan
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-baby fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                                <div class="progress mt-2" style="height: 4px;">
                                    <div class="progress-bar bg-success" id="maternity-progress" style="width: {{ $leaveBalance['maternity_total'] > 0 ? ($leaveBalance['maternity_used'] / $leaveBalance['maternity_total']) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Paternity Leave -->
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card border-left-info shadow h-100">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            Cuti Melahirkan (Pria)
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="paternity-remaining">
                                            {{ $leaveBalance['paternity_remaining'] }}/{{ $leaveBalance['paternity_total'] }}
                                        </div>
                                        <div class="text-xs text-muted" id="paternity-used">
                                            {{ $leaveBalance['paternity_used'] }} hari digunakan
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-user-tie fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                                <div class="progress mt-2" style="height: 4px;">
                                    <div class="progress-bar bg-info" id="paternity-progress" style="width: {{ $leaveBalance['paternity_total'] > 0 ? ($leaveBalance['paternity_used'] / $leaveBalance['paternity_total']) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Other Leave -->
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card border-left-secondary shadow h-100">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">
                                            Cuti Lainnya
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="other-remaining">
                                            {{ $leaveBalance['other_remaining'] }}/{{ $leaveBalance['other_total'] }}
                                        </div>
                                        <div class="text-xs text-muted" id="other-used">
                                            {{ $leaveBalance['other_used'] }} hari digunakan
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-ellipsis-h fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                                <div class="progress mt-2" style="height: 4px;">
                                    <div class="progress-bar bg-secondary" id="other-progress" style="width: {{ $leaveBalance['other_total'] > 0 ? ($leaveBalance['other_used'] / $leaveBalance['other_total']) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Leave Summary -->
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card border-left-dark shadow h-100">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">
                                            Total Tersedia
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="total-remaining">
                                            {{ $leaveBalance['annual_remaining'] + $leaveBalance['sick_remaining'] + $leaveBalance['maternity_remaining'] + $leaveBalance['paternity_remaining'] + $leaveBalance['other_remaining'] }}
                                        </div>
                                        <div class="text-xs text-muted">
                                            hari tersisa
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-chart-bar fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                                <div class="progress mt-2" style="height: 4px;">
                                    <div class="progress-bar bg-dark" style="width: 100%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Leave Usage Chart -->
                <div class="row">
                    <div class="col-md-8">
                        <div class="card shadow">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">
                                    <i class="fas fa-chart-pie mr-2"></i>
                                    Penggunaan Cuti
                                </h6>
                            </div>
                            <div class="card-body">
                                <canvas id="leaveChart" width="400" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card shadow">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    Rincian Kebijakan Cuti
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Jenis</th>
                                                <th>Kuota</th>
                                                <th>Digunakan</th>
                                                <th>Tersisa</th>
                                            </tr>
                                        </thead>
                                        <tbody id="balance-table">
                                            <tr>
                                                <td>Tahunan</td>
                                                <td>{{ $leaveBalance['annual_total'] }}</td>
                                                <td>{{ $leaveBalance['annual_used'] }}</td>
                                                <td><span class="badge badge-primary">{{ $leaveBalance['annual_remaining'] }}</span></td>
                                            </tr>
                                            <tr>
                                                <td>Sakit</td>
                                                <td>{{ $leaveBalance['sick_total'] }}</td>
                                                <td>{{ $leaveBalance['sick_used'] }}</td>
                                                <td><span class="badge badge-warning">{{ $leaveBalance['sick_remaining'] }}</span></td>
                                            </tr>
                                            <tr>
                                                <td>Melahirkan</td>
                                                <td>{{ $leaveBalance['maternity_total'] }}</td>
                                                <td>{{ $leaveBalance['maternity_used'] }}</td>
                                                <td><span class="badge badge-success">{{ $leaveBalance['maternity_remaining'] }}</span></td>
                                            </tr>
                                            <tr>
                                                <td>Melahirkan (Pria)</td>
                                                <td>{{ $leaveBalance['paternity_total'] }}</td>
                                                <td>{{ $leaveBalance['paternity_used'] }}</td>
                                                <td><span class="badge badge-info">{{ $leaveBalance['paternity_remaining'] }}</span></td>
                                            </tr>
                                            <tr>
                                                <td>Lainnya</td>
                                                <td>{{ $leaveBalance['other_total'] }}</td>
                                                <td>{{ $leaveBalance['other_used'] }}</td>
                                                <td><span class="badge badge-secondary">{{ $leaveBalance['other_remaining'] }}</span></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Leave History -->
                <div class="row>
                    <div class="col-12">
                        <div class="card shadow">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">
                                    <i class="fas fa-history mr-2"></i>
                                    Riwayat Cuti
                                </h6>
                            </div>
                            <div class="card-body">
                                <div id="history-container">
                                    @if($leaveHistory->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Jenis Cuti</th>
                                                        <th>Tanggal Mulai</th>
                                                        <th>Tanggal Selesai</th>
                                                        <th>Total Hari</th>
                                                        <th>Status</th>
                                                        <th>Dibuat</th>
                                                        <th>Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="history-table">
                                                    @foreach($leaveHistory as $leave)
                                                        <tr>
                                                            <td>
                                                                {!! $leave->type_badge !!}
                                                            </td>
                                                            <td>{{ $leave->formatted_start_date }}</td>
                                                            <td>{{ $leave->formatted_end_date }}</td>
                                                            <td>{{ $leave->total_days }} hari</td>
                                                            <td>{!! $leave->status_badge !!}</td>
                                                            <td>{{ $leave->created_at->format('d/m/Y H:i') }}</td>
                                                            <td>
                                                                <div class="btn-group" role="group">
                                                                    <button type="button" class="btn btn-sm btn-info view-btn" data-id="{{ $leave->id }}" title="Detail">
                                                                        <i class="fas fa-eye"></i>
                                                                    </button>
                                                                    @if($leave->status === 'pending')
                                                                        <a href="{{ route('leaves.edit', $leave->id) }}" class="btn btn-sm btn-warning" title="Edit">
                                                                            <i class="fas fa-edit"></i>
                                                                        </a>
                                                                    @endif
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        
                                        <!-- Pagination -->
                                        <div class="d-flex justify-content-center mt-3">
                                            {{ $leaveHistory->links() }}
                                        </div>
                                    @else
                                        <div class="text-center py-4">
                                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                            <h5 class="text-muted">Tidak Ada Riwayat Cuti</h5>
                                            <p class="text-muted">Anda belum mengajukan permintaan cuti.</p>
                                            <a href="{{ route('leaves.create') }}" class="btn btn-primary">
                                                <i class="fas fa-plus mr-1"></i> Ajukan Permintaan Cuti Pertama
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card shadow">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">
                                    <i class="fas fa-bolt mr-2"></i>
                                    Aksi Cepat
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4 mb-2">
                                        <a href="{{ route('leaves.create') }}" class="btn btn-primary btn-block">
                                            <i class="fas fa-plus mr-2"></i> Ajukan Permintaan Cuti
                                        </a>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <a href="{{ route('leaves.index') }}" class="btn btn-info btn-block">
                                            <i class="fas fa-list mr-2"></i> Lihat Riwayat Cuti
                                        </a>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <button type="button" class="btn btn-success btn-block" onclick="window.print()">
                                            <i class="fas fa-print mr-2"></i> Cetak Sisa Cuti
                                        </button>
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

<!-- Detail Modal -->
<div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailModalLabel">Detail Permintaan Cuti</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="detailContent">
                <!-- Detail content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<!-- Global SweetAlert Component -->
@include('components.sweet-alert')

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(function () {
    let leaveChart = null;

    // Initialize chart
    function initChart(data) {
        const ctx = document.getElementById('leaveChart');
        if (ctx) {
            if (leaveChart) {
                leaveChart.destroy();
            }
            
            leaveChart = new Chart(ctx.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['Cuti Tahunan', 'Cuti Sakit', 'Cuti Melahirkan', 'Cuti Melahirkan (Pria)', 'Cuti Lainnya'],
                    datasets: [{
                        data: [
                            data.annual_used || 0,
                            data.sick_used || 0,
                            data.maternity_used || 0,
                            data.paternity_used || 0,
                            data.other_used || 0
                        ],
                        backgroundColor: [
                            '#007bff', '#ffc107', '#28a745', '#17a2b8', '#6c757d'
                        ],
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                usePointStyle: true
                            }
                        }
                    }
                }
            });
        }
    }

    // Load balance data via AJAX
    function loadBalanceData() {
        $('#loadingSpinner').show();
        $('#balanceContent').hide();

        $.ajax({
            url: '{{ route("api.leaves.balance") }}',
            type: 'GET',
            errorHandled: true,
            success: function(response) {
                if (response.success) {
                    updateBalanceDisplay(response.data.balance);
                    updateHistoryTable(response.data.history.data);
                    initChart(response.data.balance);
                } else {
                    SwalHelper.error('Error!', response.message || 'Gagal memuat data sisa cuti');
                }
            },
            error: function(xhr) {
                let message = 'Terjadi kesalahan saat memuat data sisa cuti';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                SwalHelper.error('Error!', message);
            },
            complete: function() {
                $('#loadingSpinner').hide();
                $('#balanceContent').show();
            }
        });
    }

    // Update balance display
    function updateBalanceDisplay(balance) {
        // Update cards
        $('#annual-remaining').text(balance.annual_remaining + '/' + balance.annual_total);
        $('#annual-used').text(balance.annual_used + ' hari digunakan');
        $('#annual-progress').css('width', (balance.annual_total > 0 ? (balance.annual_used / balance.annual_total) * 100 : 0) + '%');

        $('#sick-remaining').text(balance.sick_remaining + '/' + balance.sick_total);
        $('#sick-used').text(balance.sick_used + ' hari digunakan');
        $('#sick-progress').css('width', (balance.sick_total > 0 ? (balance.sick_used / balance.sick_total) * 100 : 0) + '%');

        $('#maternity-remaining').text(balance.maternity_remaining + '/' + balance.maternity_total);
        $('#maternity-used').text(balance.maternity_used + ' hari digunakan');
        $('#maternity-progress').css('width', (balance.maternity_total > 0 ? (balance.maternity_used / balance.maternity_total) * 100 : 0) + '%');

        $('#paternity-remaining').text(balance.paternity_remaining + '/' + balance.paternity_total);
        $('#paternity-used').text(balance.paternity_used + ' hari digunakan');
        $('#paternity-progress').css('width', (balance.paternity_total > 0 ? (balance.paternity_used / balance.paternity_total) * 100 : 0) + '%');

        $('#other-remaining').text(balance.other_remaining + '/' + balance.other_total);
        $('#other-used').text(balance.other_used + ' hari digunakan');
        $('#other-progress').css('width', (balance.other_total > 0 ? (balance.other_used / balance.other_total) * 100 : 0) + '%');

        $('#total-remaining').text(balance.annual_remaining + balance.sick_remaining + balance.maternity_remaining + balance.paternity_remaining + balance.other_remaining);

        // Update table
        $('#balance-table').html(`
            <tr>
                <td>Tahunan</td>
                <td>${balance.annual_total}</td>
                <td>${balance.annual_used}</td>
                <td><span class="badge badge-primary">${balance.annual_remaining}</span></td>
            </tr>
            <tr>
                <td>Sakit</td>
                <td>${balance.sick_total}</td>
                <td>${balance.sick_used}</td>
                <td><span class="badge badge-warning">${balance.sick_remaining}</span></td>
            </tr>
            <tr>
                <td>Melahirkan</td>
                <td>${balance.maternity_total}</td>
                <td>${balance.maternity_used}</td>
                <td><span class="badge badge-success">${balance.maternity_remaining}</span></td>
            </tr>
            <tr>
                <td>Melahirkan (Pria)</td>
                <td>${balance.paternity_total}</td>
                <td>${balance.paternity_used}</td>
                <td><span class="badge badge-info">${balance.paternity_remaining}</span></td>
            </tr>
            <tr>
                <td>Lainnya</td>
                <td>${balance.other_total}</td>
                <td>${balance.other_used}</td>
                <td><span class="badge badge-secondary">${balance.other_remaining}</span></td>
            </tr>
        `);
    }

    // Update history table
    function updateHistoryTable(history) {
        if (history.length === 0) {
            $('#history-container').html(`
                <div class="text-center py-4">
                    <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Tidak Ada Riwayat Cuti</h5>
                    <p class="text-muted">Anda belum mengajukan permintaan cuti.</p>
                    <a href="{{ route("leaves.create") }}" class="btn btn-primary">
                        <i class="fas fa-plus mr-1"></i> Ajukan Permintaan Cuti Pertama
                    </a>
                </div>
            `);
        } else {
            let tableHtml = `
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Jenis Cuti</th>
                                <th>Tanggal Mulai</th>
                                <th>Tanggal Selesai</th>
                                <th>Total Hari</th>
                                <th>Status</th>
                                <th>Dibuat</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            history.forEach(function(leave) {
                tableHtml += `
                    <tr>
                        <td>${leave.type_badge}</td>
                        <td>${leave.formatted_start_date}</td>
                        <td>${leave.formatted_end_date}</td>
                        <td>${leave.total_days} hari</td>
                        <td>${leave.status_badge}</td>
                        <td>${leave.formatted_created_at}</td>
                        <td>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-info view-btn" data-id="${leave.id}" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </button>
                                ${leave.can_edit ? `
                                    <a href="/leaves/${leave.id}/edit" class="btn btn-sm btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                ` : ''}
                            </div>
                        </td>
                    </tr>
                `;
            });

            tableHtml += `
                        </tbody>
                    </table>
                </div>
            `;

            $('#history-container').html(tableHtml);
        }
    }

    // Handle view button click
    $(document).on('click', '.view-btn', function() {
        var id = $(this).data('id');
        loadLeaveDetail(id);
    });

    // Load leave detail
    function loadLeaveDetail(id) {
        $('#detailContent').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');
        $('#detailModal').modal('show');

        $.ajax({
            url: '/leaves/' + id,
            type: 'GET',
            errorHandled: true,
            headers: {
                'Accept': 'application/json'
            },
            success: function(response) {
                if (response.success) {
                    let leave = response.data;
                    let detailHtml = `
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Jenis Cuti:</strong></td>
                                        <td>${leave.type_badge}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Tanggal Mulai:</strong></td>
                                        <td>${leave.formatted_start_date}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Tanggal Selesai:</strong></td>
                                        <td>${leave.formatted_end_date}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Total Hari:</strong></td>
                                        <td>${leave.total_days} hari</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Status:</strong></td>
                                        <td>${leave.status_badge}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Dibuat:</strong></td>
                                        <td>${leave.formatted_created_at}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Disetujui Oleh:</strong></td>
                                        <td>${leave.approved_by || '-'}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Tanggal Persetujuan:</strong></td>
                                        <td>${leave.formatted_approved_at || '-'}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Catatan:</strong></td>
                                        <td>${leave.approval_notes || '-'}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <strong>Alasan:</strong><br>
                                <p>${leave.reason}</p>
                            </div>
                        </div>
                        ${leave.attachment_url ? `
                        <div class="row">
                            <div class="col-12">
                                <strong>Lampiran:</strong><br>
                                <a href="${leave.attachment_url}" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-download"></i> Download Lampiran
                                </a>
                            </div>
                        </div>
                        ` : ''}
                    `;
                    $('#detailContent').html(detailHtml);
                } else {
                    $('#detailContent').html('<div class="text-center text-muted">Data tidak dapat dimuat</div>');
                    SwalHelper.error('Error!', response.message);
                }
            },
            error: function(xhr) {
                let message = 'Terjadi kesalahan saat memuat detail cuti';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                $('#detailContent').html('<div class="text-center text-muted">Data tidak dapat dimuat</div>');
                SwalHelper.error('Error!', message);
            }
        });
    }

    // Initialize chart with initial data
    initChart({
        annual_used: {{ $leaveBalance['annual_used'] ?? 0 }},
        sick_used: {{ $leaveBalance['sick_used'] ?? 0 }},
        maternity_used: {{ $leaveBalance['maternity_used'] ?? 0 }},
        paternity_used: {{ $leaveBalance['paternity_used'] ?? 0 }},
        other_used: {{ $leaveBalance['other_used'] ?? 0 }}
    });

    // Refresh button (optional)
    $('#refreshBalance').on('click', function() {
        loadBalanceData();
    });
});
</script>
@endpush

@push('css')
<style>
.border-left-primary {
    border-left: 0.25rem solid #007bff !important;
}

.border-left-warning {
    border-left: 0.25rem solid #ffc107 !important;
}

.border-left-success {
    border-left: 0.25rem solid #28a745 !important;
}

.border-left-info {
    border-left: 0.25rem solid #17a2b8 !important;
}

.border-left-secondary {
    border-left: 0.25rem solid #6c757d !important;
}

.border-left-dark {
    border-left: 0.25rem solid #343a40 !important;
}

.text-xs {
    font-size: 0.7rem;
}

.text-gray-300 {
    color: #dddfeb !important;
}

.text-gray-800 {
    color: #5a5c69 !important;
}

.font-weight-bold {
    font-weight: 700 !important;
}

@media print {
    .card-tools, .btn, .progress {
        display: none !important;
    }
    
    .card {
        break-inside: avoid;
    }
}
</style>
@endpush 