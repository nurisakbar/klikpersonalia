@extends('layouts.app')

@section('title', 'Kelola Laporan BPJS')
@section('page-title', 'Kelola Laporan BPJS')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('bpjs.index') }}">Kelola BPJS</a></li>
<li class="breadcrumb-item active">Laporan BPJS</li>
@endsection

@section('content')
            <!-- Summary Cards -->
            <div class="row">
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-info"><i class="fas fa-heartbeat"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">BPJS Kesehatan</span>
                            <span class="info-box-number">{{ $summary['kesehatan_count'] }}</span>
                            <div class="progress">
                                <div class="progress-bar" style="width: 100%"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-success"><i class="fas fa-briefcase"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">BPJS Ketenagakerjaan</span>
                            <span class="info-box-number">{{ $summary['ketenagakerjaan_count'] }}</span>
                            <div class="progress">
                                <div class="progress-bar" style="width: 100%"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-warning"><i class="fas fa-user"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Kontribusi Karyawan</span>
                            <span class="info-box-number">Rp {{ number_format($summary['total_employee_contribution'], 0, ',', '.') }}</span>
                            <div class="progress">
                                <div class="progress-bar" style="width: 100%"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-primary"><i class="fas fa-building"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Kontribusi Perusahaan</span>
                            <span class="info-box-number">Rp {{ number_format($summary['total_company_contribution'], 0, ',', '.') }}</span>
                            <div class="progress">
                                <div class="progress-bar" style="width: 100%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-bar"></i> Laporan BPJS - {{ \Carbon\Carbon::parse($period)->format('F Y') }}
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-success btn-sm export-btn" 
                            data-url="{{ route('bpjs-report.export', ['period' => $period, 'type' => $type]) }}">
                        <i class="fas fa-download"></i> Ekspor CSV
                    </button>
                    <a href="{{ route('bpjs.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali ke BPJS
                    </a>
                </div>
            </div>
            <div class="card-body">
                            <!-- Filters -->
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="filter_period">Periode:</label>
                                    <select id="filter_period" class="form-control form-control-sm">
                                        <option value="">Semua Periode</option>
                                        @foreach($periods as $p)
                                            <option value="{{ $p }}" {{ $period == $p ? 'selected' : '' }}>
                                                {{ \Carbon\Carbon::parse($p)->format('F Y') }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="filter_type">Jenis:</label>
                                    <select id="filter_type" class="form-control form-control-sm">
                                        <option value="">Semua Jenis</option>
                                        <option value="kesehatan" {{ $type == 'kesehatan' ? 'selected' : '' }}>BPJS Kesehatan</option>
                                        <option value="ketenagakerjaan" {{ $type == 'ketenagakerjaan' ? 'selected' : '' }}>BPJS Ketenagakerjaan</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label>&nbsp;</label>
                                    <div>
                                        <button type="button" id="apply_filter" class="btn btn-primary btn-sm">
                                            <i class="fas fa-filter mr-1"></i> Filter
                                        </button>
                                        <button type="button" id="reset_filter" class="btn btn-secondary btn-sm">
                                            <i class="fas fa-undo mr-1"></i> Reset
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Summary Table -->
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th colspan="2" class="text-center">Ringkasan untuk {{ \Carbon\Carbon::parse($period)->format('F Y') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><strong>Total Data:</strong></td>
                                                    <td>{{ $summary['total_records'] }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Data BPJS Kesehatan:</strong></td>
                                                    <td>{{ $summary['kesehatan_count'] }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Data BPJS Ketenagakerjaan:</strong></td>
                                                    <td>{{ $summary['ketenagakerjaan_count'] }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Total Kontribusi Karyawan:</strong></td>
                                                    <td><strong>Rp {{ number_format($summary['total_employee_contribution'], 0, ',', '.') }}</strong></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Total Kontribusi Perusahaan:</strong></td>
                                                    <td><strong>Rp {{ number_format($summary['total_company_contribution'], 0, ',', '.') }}</strong></td>
                                                </tr>
                                                <tr class="table-active">
                                                    <td><strong>Total Kontribusi:</strong></td>
                                                    <td><strong>Rp {{ number_format($summary['total_contribution'], 0, ',', '.') }}</strong></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

            </div>
        </div>
    </div>
</div>

<!-- Data Table Card -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-table"></i> Data Detail BPJS
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
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="bpjs-report-table" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>Nama Karyawan</th>
                                <th>ID Karyawan</th>
                                <th>Departemen</th>
                                <th>Jenis BPJS</th>
                                <th>Gaji Pokok</th>
                                <th>Kontribusi Karyawan</th>
                                <th>Kontribusi Perusahaan</th>
                                <th>Total Kontribusi</th>
                                <th>Status</th>
                                <th>Tanggal Dibuat</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Section -->
@if(isset($summary))
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-pie"></i> Distribusi Kontribusi
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
                        <canvas id="contributionChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-doughnut"></i> Distribusi Jenis BPJS
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
                        <canvas id="typeChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
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
@endsection

@push('css')
<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">


@endpush

@push('js')
<!-- DataTables & Plugins -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>

<!-- ChartJS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>

<script>
// Test jQuery availability with retry mechanism
function initDataTable() {
    if (typeof $ === 'undefined') {
        console.error('jQuery is not available in DataTable script, retrying in 500ms...');
        setTimeout(initDataTable, 500);
        return;
    }
    
    console.log('jQuery is available in DataTable script, version:', $.fn.jquery);
    
    $(function () {
        // Setup CSRF token for AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Apply filter
        $('#apply_filter').on('click', function() {
            var $btn = $(this);
            var originalText = $btn.html();
            
            // Show loading state
            $btn.html('<i class="fas fa-spinner fa-spin mr-1"></i> Memproses...');
            $btn.prop('disabled', true);
            
            table.ajax.reload(function() {
                // Restore button state
                $btn.html(originalText);
                $btn.prop('disabled', false);
            });
        });

        // Reset filter
        $('#reset_filter').on('click', function() {
            var $btn = $(this);
            var originalText = $btn.html();
            
            // Show loading state
            $btn.html('<i class="fas fa-spinner fa-spin mr-1"></i> Reset...');
            $btn.prop('disabled', true);
            
            // Reset filter values
            $('#filter_period').val('');
            $('#filter_type').val('');
            
            table.ajax.reload(function() {
                // Restore button state
                $btn.html(originalText);
                $btn.prop('disabled', false);
            });
        });

        // Initialize DataTable with server-side processing
        var table = $('#bpjs-report-table').DataTable({
            processing: true,
            serverSide: false, // We're using client-side processing for now
            ajax: {
                url: '{{ route("bpjs-report.data") }}',
                type: 'GET',
                data: function(d) {
                    d.period = $('#filter_period').val();
                    d.type = $('#filter_type').val();
                },
                error: function(xhr, error, thrown) {
                    console.log('DataTable error:', error);
                }
            },
            columns: [
                {data: 'employee_name', name: 'employee_name', width: '150px'},
                {data: 'employee_id', name: 'employee_id', width: '120px'},
                {data: 'department', name: 'department', width: '120px'},
                {data: 'bpjs_type_badge', name: 'bpjs_type', width: '120px'},
                {data: 'base_salary_formatted', name: 'base_salary', width: '120px'},
                {data: 'employee_contribution_formatted', name: 'employee_contribution', width: '150px'},
                {data: 'company_contribution_formatted', name: 'company_contribution', width: '150px'},
                {data: 'total_contribution_formatted', name: 'total_contribution', width: '130px'},
                {data: 'status_badge', name: 'status', width: '100px'},
                {data: 'created_at_formatted', name: 'created_at', width: '120px'},
                {data: 'actions', name: 'action', orderable: false, searchable: false, width: '100px'}
            ],
            scrollX: true,
            scrollCollapse: true,
            autoWidth: false,
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'excel',
                    text: '<i class="fas fa-file-excel"></i> Excel',
                    className: 'btn btn-success btn-sm',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9]
                    }
                },
                {
                    extend: 'pdf',
                    text: '<i class="fas fa-file-pdf"></i> PDF',
                    className: 'btn btn-danger btn-sm',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9]
                    }
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print"></i> Print',
                    className: 'btn btn-info btn-sm',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9]
                    }
                }
            ],
            language: window.DataTablesLanguage,
            responsive: true,
            order: [[0, 'asc']]
        });

        // Handle delete button clicks
        $(document).on('click', '.delete-btn', function() {
            var id = $(this).data('id');
            var name = $(this).data('name');
            
            SwalHelper.confirmDelete('Konfirmasi Hapus', 'Apakah Anda yakin ingin menghapus data BPJS "' + name + '" ?', function(result) {
                if (result.isConfirmed) {
                    // Show loading
                    SwalHelper.loading('Menghapus...');

                    // Send delete request
                    $.ajax({
                        url: '/bpjs/' + id,
                        type: 'DELETE',
                        errorHandled: true, // Mark as manually handled
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response.success) {
                                SwalHelper.success('Berhasil!', response.message, 2000);
                                // Reload DataTable
                                table.ajax.reload();
                            } else {
                                SwalHelper.error('Gagal!', response.message);
                            }
                        },
                        error: function(xhr) {
                            var message = 'Terjadi kesalahan saat menghapus data';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                message = xhr.responseJSON.message;
                            }
                            
                            SwalHelper.error('Error!', message);
                        }
                    });
                }
            });
        });

        // Handle export button clicks
        $(document).on('click', '.export-btn', function() {
            var url = $(this).data('url');
            
            if (url) {
                SwalHelper.loading('Exporting...', 'Sedang mempersiapkan file export...');
                // Redirect to export URL
                window.location.href = url;
            }
        });

        @if(isset($summary))
            console.log('Creating charts with data:', {
                employee_contribution: {{ $summary['total_employee_contribution'] ?? 0 }},
                company_contribution: {{ $summary['total_company_contribution'] ?? 0 }},
                kesehatan_count: {{ $summary['kesehatan_count'] ?? 0 }},
                ketenagakerjaan_count: {{ $summary['ketenagakerjaan_count'] ?? 0 }}
            });
            
            // Contribution Distribution Chart
            var contributionChartCanvas = $('#contributionChart').get(0).getContext('2d')
            var contributionData = {
                labels: ['Kontribusi Karyawan', 'Kontribusi Perusahaan'],
                datasets: [
                    {
                        data: [
                            {{ $summary['total_employee_contribution'] ?? 0 }},
                            {{ $summary['total_company_contribution'] ?? 0 }}
                        ],
                        backgroundColor: ['#17a2b8', '#28a745'],
                    }
                ]
            }
            var contributionOptions = {
                maintainAspectRatio: false,
                responsive: true,
            }
            new Chart(contributionChartCanvas, {
                type: 'doughnut',
                data: contributionData,
                options: contributionOptions
            })
            console.log('Contribution chart created');
            
            // BPJS Type Distribution Chart
            var typeChartCanvas = $('#typeChart').get(0).getContext('2d')
            var typeData = {
                labels: ['BPJS Kesehatan', 'BPJS Ketenagakerjaan'],
                datasets: [
                    {
                        data: [
                            {{ $summary['kesehatan_count'] ?? 0 }},
                            {{ $summary['ketenagakerjaan_count'] ?? 0 }}
                        ],
                        backgroundColor: ['#17a2b8', '#28a745'],
                    }
                ]
            }
            var typeOptions = {
                maintainAspectRatio: false,
                responsive: true,
            }
            new Chart(typeChartCanvas, {
                type: 'pie',
                data: typeData,
                options: typeOptions
            })
            console.log('Type chart created');
        @else
            console.log('No summary data available for charts');
        @endif
    });
}

// Initialize DataTable when page loads
initDataTable();
</script>
@endpush 