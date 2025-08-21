@extends('layouts.app')

@section('title', 'Kelola Pajak - Aplikasi Payroll KlikMedis')
@section('page-title', 'Kelola Pajak')

@section('breadcrumb')
<li class="breadcrumb-item active">Pajak</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
         
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-calculator"></i> Hitung Pajak Baru
                    </h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('taxes.calculate-for-payroll') }}" method="POST" class="form-inline">
                        @csrf
                        <div class="form-group mr-3">
                            <label for="month" class="mr-2">Bulan:</label>
                            <select name="month" id="month" class="form-control form-control-sm" required>
                                @for($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}">{{ date('F', mktime(0, 0, 0, $i, 1)) }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="form-group mr-3">
                            <label for="year" class="mr-2">Tahun:</label>
                            <select name="year" id="year" class="form-control form-control-sm" required>
                                @for($i = date('Y'); $i >= date('Y') - 5; $i--)
                                    <option value="{{ $i }}">{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success btn-sm">
                            <i class="fas fa-calculator"></i> Hitung Pajak untuk Semua Karyawan
                        </button>
                    </form>
                </div>
            </div>
                    

                                    <!-- DataTable Card -->
            <div class="card">
                <div class="card-body">
                    <!-- Filter Section -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="period_filter">Periode Pajak:</label>
                            <select id="period_filter" class="form-control form-control-sm">
                                <option value="">Semua Periode</option>
                                @php
                                    $currentYear = date('Y');
                                    $currentMonth = date('m');
                                @endphp
                                @for($year = $currentYear - 2; $year <= $currentYear + 1; $year++)
                                    @for($month = 1; $month <= 12; $month++)
                                        @php
                                            $periodValue = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT);
                                            $periodLabel = date('F Y', mktime(0, 0, 0, $month, 1, $year));
                                        @endphp
                                        <option value="{{ $periodValue }}" {{ $currentYear . '-' . $currentMonth == $periodValue ? 'selected' : '' }}>
                                            {{ $periodLabel }}
                                        </option>
                                    @endfor
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="employee_filter">Karyawan:</label>
                            <select id="employee_filter" class="form-control form-control-sm">
                                <option value="">Semua Karyawan</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="status_filter">Status:</label>
                            <select id="status_filter" class="form-control form-control-sm">
                                <option value="">Semua Status</option>
                                <option value="pending">Menunggu</option>
                                <option value="calculated">Dihitung</option>
                                <option value="paid">Dibayar</option>
                                <option value="verified">Terverifikasi</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>&nbsp;</label>
                            <div>
                                <button type="button" id="apply_filter" class="btn btn-primary btn-sm">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
                                <button type="button" id="reset_filter" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-undo"></i> Reset
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- DataTable -->
                    <table class="table table-bordered table-striped" id="taxes-table" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>Nomor</th>
                                <th>Nama Karyawan</th>
                                <th>ID Karyawan</th>
                                <th>Periode Pajak</th>
                                <th>Pendapatan Kena Pajak</th>
                                <th>PTKP</th>
                                <th>Jumlah Pajak</th>
                                <th>Tarif Pajak</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                    </table>
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
                <h5 class="modal-title" id="detailModalLabel">Detail Pajak</h5>
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

<!-- CSRF Token for AJAX -->
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@push('css')
<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
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

<!-- Global SweetAlert Component -->
@include('components.sweet-alert')

<script>
$(document).ready(function () {
    // Global variables
    let currentTaxId = null;
    let isEditMode = false;
    
    // Setup CSRF token for AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Initialize DataTable with server-side processing
    var table = $('#taxes-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("taxes.data") }}',
            type: 'GET',
            data: function(d) {
                d.period_filter = $('#period_filter').val();
                d.employee_filter = $('#employee_filter').val();
                d.status_filter = $('#status_filter').val();
            },
            error: function(xhr, error, thrown) {
                SwalHelper.error('Error', 'Gagal memuat data pajak: ' + (xhr.responseJSON ? xhr.responseJSON.error : error));
            }
        },
        columns: [
            {data: null, name: 'DT_RowIndex', width: '50px', title: 'Nomor', orderable: false, searchable: false, render: function (data, type, row, meta) {
                return meta.row + meta.settings._iDisplayStart + 1;
            }},
            {data: 'employee_name', name: 'employee_name', width: '200px'},
            {data: 'employee_id_display', name: 'employee_id_display', width: '120px'},
            {data: 'tax_period_formatted', name: 'tax_period_formatted', width: '120px'},
            {data: 'taxable_income_formatted', name: 'taxable_income_formatted', width: '180px'},
            {data: 'ptkp_amount_formatted', name: 'ptkp_amount_formatted', width: '120px'},
            {data: 'tax_amount_formatted', name: 'tax_amount_formatted', width: '150px'},
            {data: 'tax_rate_formatted', name: 'tax_rate_formatted', width: '100px'},
            {data: 'status_badge', name: 'status_badge', width: '100px'},
            {data: 'action', name: 'action', orderable: false, searchable: false, width: '150px'}
        ],
        scrollX: true,
        scrollCollapse: true,
        autoWidth: false,
        dom: 'Bfrtip',
        buttons: [
            {
                text: '<i class="fas fa-plus"></i> Tambah',
                className: 'btn btn-primary btn-sm mr-2',
                action: function () {
                    window.location.href = '{{ route("taxes.create") }}';
                }
            },
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel"></i> Excel',
                className: 'btn btn-success btn-sm',
                exportOptions: {
                    columns: [1, 2, 3, 4, 5, 6, 7, 8]
                }
            },
            {
                extend: 'pdf',
                text: '<i class="fas fa-file-pdf"></i> PDF',
                className: 'btn btn-danger btn-sm',
                exportOptions: {
                    columns: [1, 2, 3, 4, 5, 6, 7, 8]
                }
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print"></i> Print',
                className: 'btn btn-info btn-sm',
                exportOptions: {
                    columns: [1, 2, 3, 4, 5, 6, 7, 8]
                }
            }
        ],
        language: {
            "sProcessing":   "Sedang memproses...",
            "sLengthMenu":   "Tampilkan _MENU_ entri",
            "sZeroRecords":  "Tidak ditemukan data yang sesuai",
            "sInfo":         "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
            "sInfoEmpty":    "Menampilkan 0 sampai 0 dari 0 entri",
            "sInfoFiltered": "(disaring dari _MAX_ entri keseluruhan)",
            "sInfoPostFix":  "",
            "sSearch":       "Cari:",
            "sUrl":          "",
            "oPaginate": {
                "sFirst":    "Pertama",
                "sPrevious": "Sebelumnya",
                "sNext":     "Selanjutnya",
                "sLast":     "Terakhir"
            }
        },
        responsive: true,
        order: [[1, 'asc']]
    });

    // Pastikan tombol Add tidak memakai btn-secondary (force primary)
    var taxesButtons = table.buttons().container();
    taxesButtons.find('.dt-add-btn').removeClass('btn-secondary').addClass('btn-primary');

    // Layout info/pagination sudah diatur global via CSS

    // Apply filter
    $('#apply_filter').on('click', function() {
        table.ajax.reload();
    });

    // Reset filter
    $('#reset_filter').on('click', function() {
        $('#period_filter').val('');
        $('#employee_filter').val('');
        $('#status_filter').val('');
        table.ajax.reload();
    });

    // Handle view button click
    $(document).on('click', '.view-btn', function() {
        var id = $(this).data('id');
        loadTaxDetail(id);
    });

    // Handle edit button click
    $(document).on('click', '.edit-btn', function() {
        var id = $(this).data('id');
        window.location.href = '/taxes/' + id + '/edit';
    });

    // Load tax detail
    function loadTaxDetail(id) {
        // Show loading
        $('#detailContent').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');
        $('#detailModal').modal('show');

        $.ajax({
            url: '/taxes/' + id,
            type: 'GET',
            errorHandled: true, // Mark as manually handled
            headers: {
                'Accept': 'application/json'
            },
            success: function(response) {
                if (response.success) {
                    let tax = response.data;
                    let detailHtml = `
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Nama Karyawan:</strong></td>
                                        <td>${tax.employee_name}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>ID Karyawan:</strong></td>
                                        <td>${tax.employee_id}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Periode Pajak:</strong></td>
                                        <td>${tax.tax_period}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Pendapatan Kena Pajak:</strong></td>
                                        <td>${tax.taxable_income}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Status PTKP:</strong></td>
                                        <td>${tax.ptkp_status}</td>
                                </tr>
                                    <tr>
                                        <td><strong>Jumlah PTKP:</strong></td>
                                        <td>${tax.ptkp_amount}</td>
                                    </tr>
                                </table>
                                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Dasar Pengenaan Pajak:</strong></td>
                                        <td>${tax.taxable_base}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Jumlah Pajak:</strong></td>
                                        <td>${tax.tax_amount}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Tarif Pajak:</strong></td>
                                        <td>${tax.tax_rate}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Status:</strong></td>
                                        <td>${tax.status}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Catatan:</strong></td>
                                        <td>${tax.notes}</td>
                                    </tr>
                        </table>
                    </div>
                    </div>
                    `;
                    $('#detailContent').html(detailHtml);
                } else {
                    $('#detailContent').html('<div class="text-center text-muted">Data tidak dapat dimuat</div>');
                    SwalHelper.error('Error!', response.message);
                }
            },
            error: function(xhr) {
                let message = 'Terjadi kesalahan saat memuat detail pajak';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                $('#detailContent').html('<div class="text-center text-muted">Data tidak dapat dimuat</div>');
                SwalHelper.error('Error!', message);
            }
        });
    }

    // Handle delete button click
    $(document).on('click', '.delete-btn', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        
        SwalHelper.confirmDelete('Konfirmasi Hapus', 'Apakah Anda yakin ingin menghapus data pajak untuk "' + name + '" ?', function(result) {
            if (result.isConfirmed) {
                // Show loading
                SwalHelper.loading('Menghapus...');

                // Send delete request
                $.ajax({
                    url: '/taxes/' + id,
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



    // Session messages sudah ditangani oleh global SwalHelper di layout
});
</script>
@endpush 