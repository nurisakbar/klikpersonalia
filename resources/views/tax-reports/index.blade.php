@extends('layouts.app')

@section('title', 'Laporan Pajak - Aplikasi Payroll KlikMedis')
@section('page-title', 'Laporan Pajak')

@section('breadcrumb')
<li class="breadcrumb-item active">Laporan Pajak</li>
@endsection

@push('css')
<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
<style>
    .dataTables_wrapper .dataTables_processing {
        background: rgba(255, 255, 255, 0.9);
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 10px;
        text-align: center;
        z-index: 1;
    }
    .small-box {
        margin-bottom: 20px;
    }
    /* Smaller font for all summary totals */
    .small-box .inner h3 {
        font-size: 1.25rem;
        line-height: 1.2;
    }
    .table-responsive {
        margin-top: 20px;
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <!-- Filter Section -->
                <div class="row mb-3">
                    <div class="col-md-2">
                        <label for="start_date" class="form-label">Tanggal Mulai</label>
                        <input type="date" class="form-control form-control-sm" id="start_date" name="start_date">
                    </div>
                    <div class="col-md-2">
                        <label for="end_date" class="form-label">Tanggal Akhir</label>
                        <input type="date" class="form-control form-control-sm" id="end_date" name="end_date">
                    </div>
                    <div class="col-md-2">
                        <label for="employee_filter" class="form-label">Karyawan</label>
                        <select class="form-control form-control-sm" id="employee_filter" name="employee_id">
                            <option value="">Semua Karyawan</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="status_filter" class="form-label">Status</label>
                        <select class="form-control form-control-sm" id="status_filter" name="status">
                            <option value="">Semua Status</option>
                            <option value="pending">Menunggu</option>
                            <option value="calculated">Dihitung</option>
                            <option value="paid">Dibayar</option>
                            <option value="verified">Terverifikasi</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="period_filter" class="form-label">Periode Pajak</label>
                        <select class="form-control form-control-sm" id="period_filter" name="tax_period">
                            <option value="">Semua Periode</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <div>
                            <button type="button" class="btn btn-primary btn-sm" id="applyFilterBtn">
                                <i class="fas fa-search mr-1"></i> Filter
                            </button>
                            <button type="button" class="btn btn-secondary btn-sm" id="resetFilterBtn">
                                <i class="fas fa-undo mr-1"></i> Reset
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="row mb-3">
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3 id="totalRecords">0</h3>
                                <p>Total Data</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-file-invoice"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3 id="totalIncome">Rp 0</h3>
                                <p>Total Pendapatan</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-money-bill"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3 id="totalPtkp">Rp 0</h3>
                                <p>Total PTKP</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-calculator"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-danger">
                            <div class="inner">
                                <h3 id="totalTax">Rp 0</h3>
                                <p>Total Pajak</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-coins"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- DataTable -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="tax-report-table" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Karyawan</th>
                                <th>Periode Pajak</th>
                                <th>Pendapatan Kena Pajak</th>
                                <th>Jumlah Pajak</th>
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

<!-- CSRF Token for AJAX -->
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@push('css')
<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
<style>
    .dataTables_wrapper .dataTables_processing {
        background: rgba(255, 255, 255, 0.9);
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 10px;
        text-align: center;
    }
    .small-box {
        margin-bottom: 20px;
    }
    .table-responsive {
        margin-top: 20px;
    }
</style>
@endpush

@push('js')
<!-- DataTables & Plugins -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>

<script>
// Robust init like employees
function initTaxReportTable() {
    if (typeof $ === 'undefined') {
        setTimeout(initTaxReportTable, 300);
        return;
    }

    $(function () {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        var table = $('#tax-report-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("tax-reports.data") }}',
                type: 'GET',
                data: function(d) {
                    d.start_date = $('#start_date').val();
                    d.end_date = $('#end_date').val();
                    d.employee_id = $('#employee_filter').val();
                    d.status = $('#status_filter').val();
                    d.tax_period = $('#period_filter').val();
                }
            },
            columns: [
                {data: null, name: 'row_number', width: '50px', orderable: false, searchable: false,
                 render: function (data, type, row, meta) {
                     return meta.row + meta.settings._iDisplayStart + 1;
                 }},
                {data: 'employee_name', name: 'employee_name', width: '220px'},
                {data: 'tax_period_formatted', name: 'tax_period', width: '150px'},
                {data: 'taxable_income_formatted', name: 'taxable_income', width: '180px'},
                {data: 'tax_amount_formatted', name: 'tax_amount', width: '150px'},
                {data: 'status_badge', name: 'status', width: '100px'},
                {data: 'created_at', name: 'created_at', width: '130px', visible: false},
                {data: 'action', name: 'action', orderable: false, searchable: false, width: '120px'}
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
                    exportOptions: { columns: [0,1,2,3,4,5,6] }
                },
                {
                    extend: 'pdf',
                    text: '<i class="fas fa-file-pdf"></i> PDF',
                    className: 'btn btn-danger btn-sm',
                    exportOptions: { columns: [0,1,2,3,4,5,6] }
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print"></i> Print',
                    className: 'btn btn-info btn-sm',
                    exportOptions: { columns: [0,1,2,3,4,5,6] }
                }
            ],
            language: {
                sProcessing: 'Memproses...',
                sLengthMenu: 'Tampilkan _MENU_ entri',
                sZeroRecords: 'Tidak ditemukan data yang sesuai',
                sInfo: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ entri',
                sInfoEmpty: 'Menampilkan 0 sampai 0 dari 0 entri',
                sInfoFiltered: '(disaring dari _MAX_ entri keseluruhan)',
                sSearch: 'Cari:',
                oPaginate: { sFirst: 'Pertama', sPrevious: 'Sebelumnya', sNext: 'Selanjutnya', sLast: 'Terakhir' }
            },
            responsive: true,
            order: [[2, 'desc']]
        });

        // Filters
        $('#applyFilterBtn').on('click', function() { table.draw(); updateSummary(); });
        $('#resetFilterBtn').on('click', function() {
            $('#start_date, #end_date').val('');
            $('#employee_filter, #status_filter, #period_filter').val('');
            table.draw();
            updateSummary();
        });

        // Load filter selects
        loadFilterData();

        function loadFilterData() {
            $.get('{{ route("employees.data") }}', function(response) {
                if (response && response.data) {
                    var $emp = $('#employee_filter');
                    response.data.forEach(function(emp){
                        $emp.append('<option value="'+emp.id+'">'+emp.name+' ('+emp.employee_id+')</option>');
                    });
                }
            });

            $.get('{{ route("taxes.data") }}', function(response) {
                if (response && response.data) {
                    var periods = [];
                    response.data.forEach(function(t){ if (t.tax_period && !periods.includes(t.tax_period)) periods.push(t.tax_period); });
                    periods.sort().reverse();
                    var $period = $('#period_filter');
                    periods.forEach(function(p){ $period.append('<option value="'+p+'">'+p+'</option>'); });
                }
            });
        }

        function updateSummary() {
            var params = {
                start_date: $('#start_date').val(),
                end_date: $('#end_date').val(),
                employee_id: $('#employee_filter').val(),
                status: $('#status_filter').val(),
                tax_period: $('#period_filter').val()
            };

            $.ajax({
                url: '{{ route("tax-reports.data") }}',
                type: 'GET',
                data: params,
                success: function(resp){
                    var totalRecords = resp.recordsFiltered || 0;
                    var totalIncome = 0, totalPtkp = 0, totalTax = 0;
                    if (resp.data) {
                        resp.data.forEach(function(t){
                            totalIncome += parseFloat(t.taxable_income || 0);
                            totalPtkp += parseFloat(t.ptkp_amount || 0);
                            totalTax += parseFloat(t.tax_amount || 0);
                        });
                    }
                    $('#totalRecords').text(totalRecords);
                    $('#totalIncome').text('Rp ' + numberFormat(totalIncome));
                    $('#totalPtkp').text('Rp ' + numberFormat(totalPtkp));
                    $('#totalTax').text('Rp ' + numberFormat(totalTax));
                },
                error: function(){
                    $('#totalRecords').text('0');
                    $('#totalIncome').text('Rp 0');
                    $('#totalPtkp').text('Rp 0');
                    $('#totalTax').text('Rp 0');
                }
            });
        }

        function numberFormat(num) {
            if (!num) return '0';
            return (num.toFixed ? num : Number(num)).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        updateSummary();
    });
}

initTaxReportTable();
</script>
@endpush
