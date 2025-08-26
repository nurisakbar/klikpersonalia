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
                    <form id="calculateTaxForm" action="{{ route('taxes.calculate-for-payroll') }}" method="POST" class="form-inline">
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
                        <button type="submit" class="btn btn-success btn-sm" id="calculateTaxBtn">
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
                            <label for="filter_month">Bulan:</label>
                            <select id="filter_month" class="form-control form-control-sm">
                                <option value="">Semua Bulan</option>
                                @for($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}">{{ date('F', mktime(0, 0, 0, $i, 1)) }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filter_year">Tahun:</label>
                            <select id="filter_year" class="form-control form-control-sm">
                                <option value="">Semua Tahun</option>
                                @for($i = date('Y'); $i >= date('Y') - 5; $i--)
                                    <option value="{{ $i }}">{{ $i }}</option>
                                @endfor
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
                                    <i class="fas fa-filter mr-1"></i> Filter
                                </button>
                                <button type="button" id="reset_filter" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-undo mr-1"></i> Reset
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
// Test jQuery availability with retry mechanism
function initDataTable() {
    if (typeof $ === 'undefined') {
        console.error('jQuery is not available in DataTable script, retrying in 500ms...');
        setTimeout(initDataTable, 500);
        return;
    }
    
    console.log('jQuery is available in DataTable script, version:', $.fn.jquery);
    
    $(function () {
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
                d.filter_month = $('#filter_month').val();
                d.filter_year = $('#filter_year').val();
                d.status_filter = $('#status_filter').val();
            },
            error: function(xhr, error, thrown) {
                // Handle DataTable errors silently or show a user-friendly message
                console.log('DataTable error:', error);
                // You can show a toast notification here if needed
                // SwalHelper.toastError('Gagal memuat data pajak');
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
        language: window.DataTablesLanguage,
        responsive: true,
        order: [[1, 'asc']]
    });

    // Pastikan tombol Add tidak memakai btn-secondary (force primary)
    var taxesButtons = table.buttons().container();
    taxesButtons.find('.dt-add-btn').removeClass('btn-secondary').addClass('btn-primary');

    // Layout info/pagination sudah diatur global via CSS

    // Apply filter
    $('#apply_filter').on('click', function() {
        var $btn = $(this);
        var originalText = $btn.html();
        
        // Show loading state
        $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Memproses...');
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
        $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Reset...');
        $btn.prop('disabled', true);
        
        // Reset filter values
        $('#filter_month').val('');
        $('#filter_year').val('');
        $('#status_filter').val('');
        
        table.ajax.reload(function() {
            // Restore button state
            $btn.html(originalText);
            $btn.prop('disabled', false);
        });
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
        $('#detailModal').modal('show');
        $('#detailContent').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Memuat data...</div>');
        
        $.ajax({
            url: '/taxes/' + id,
            type: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                if (response.success && response.data) {
                    var tax = response.data;
                    var detailHtml = `
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Nama Karyawan:</strong></td>
                                        <td>${tax.employee.name}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>ID Karyawan:</strong></td>
                                        <td>${tax.employee.employee_id}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Jabatan:</strong></td>
                                        <td>${tax.employee.position}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Departemen:</strong></td>
                                        <td>${tax.employee.department}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Periode Pajak:</strong></td>
                                        <td>${tax.tax_period_formatted}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Status PTKP:</strong></td>
                                        <td>${tax.ptkp_status}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Status:</strong></td>
                                        <td>${tax.status_badge}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Tanggal Dibuat:</strong></td>
                                        <td>${tax.created_at_formatted}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <h6 class="mt-3">Detail Perhitungan Pajak</h6>
                                <table class="table table-bordered">
                                    <tr>
                                        <td><strong>Pendapatan Kena Pajak:</strong></td>
                                        <td>${tax.taxable_income_formatted}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Jumlah PTKP:</strong></td>
                                        <td>${tax.ptkp_amount_formatted}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Dasar Pengenaan Pajak:</strong></td>
                                        <td>${tax.taxable_base_formatted}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Jumlah Pajak:</strong></td>
                                        <td>${tax.tax_amount_formatted}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Tarif Pajak:</strong></td>
                                        <td>${tax.tax_rate_formatted}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        ${tax.notes ? `
                        <div class="row">
                            <div class="col-12">
                                <strong>Catatan:</strong><br>
                                <p>${tax.notes}</p>
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



    // Handle tax calculation form submission
    $('#calculateTaxForm').on('submit', function(e) {
        e.preventDefault();
        
        var month = $('#month').val();
        var year = $('#year').val();
        var monthName = $('#month option:selected').text();
        
        // Show confirmation dialog
        SwalHelper.confirm(
            'Konfirmasi Perhitungan Pajak',
            `Apakah Anda yakin ingin menghitung pajak untuk periode ${monthName} ${year}?`,
            function(result) {
                if (result.isConfirmed) {
                    // Show loading
                    SwalHelper.loading('Menghitung pajak...');
                    
                    // Disable button
                    $('#calculateTaxBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menghitung...');
                    
                    // Submit form using AJAX
                    $.ajax({
                        url: $('#calculateTaxForm').attr('action'),
                        type: 'POST',
                        data: $('#calculateTaxForm').serialize(),
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        success: function(response) {
                            // Re-enable button
                            $('#calculateTaxBtn').prop('disabled', false).html('<i class="fas fa-calculator"></i> Hitung Pajak untuk Semua Karyawan');
                            
                            // Close loading
                            SwalHelper.closeLoading();
                            
                            // Show success message
                            SwalHelper.success('Berhasil!', response.message);
                            
                            // Reload DataTable
                            setTimeout(function() {
                                table.ajax.reload();
                            }, 1000);
                        },
                        error: function(xhr) {
                            // Re-enable button
                            $('#calculateTaxBtn').prop('disabled', false).html('<i class="fas fa-calculator"></i> Hitung Pajak untuk Semua Karyawan');
                            
                            // Close loading
                            SwalHelper.closeLoading();
                            
                            // Show error message
                            var message = 'Terjadi kesalahan saat menghitung pajak';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                message = xhr.responseJSON.message;
                            }
                            SwalHelper.error('Error!', message);
                        }
                    });
                }
            }
        );
    });

    // Session messages sudah ditangani oleh global SwalHelper di layout
    });
}

// Start initialization
initDataTable();
</script>
@endpush 