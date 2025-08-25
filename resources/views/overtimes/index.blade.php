@extends('layouts.app')

@section('title', 'Kelola Lembur - Aplikasi Payroll KlikMedis')
@section('page-title', 'Kelola Lembur')

@section('breadcrumb')
<li class="breadcrumb-item active">Lembur</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <!-- Filter Section -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="start_date">Dari Tanggal:</label>
                            <input type="date" id="start_date" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-3">
                            <label for="end_date">Sampai Tanggal:</label>
                            <input type="date" id="end_date" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-3">
                            <label for="status_filter">Status:</label>
                            <select id="status_filter" class="form-control form-control-sm">
                                <option value="">Semua Status</option>
                                <option value="pending">Menunggu</option>
                                <option value="approved">Disetujui</option>
                                <option value="rejected">Ditolak</option>
                                <option value="cancelled">Dibatalkan</option>
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

                    <table class="table table-bordered table-striped" id="overtimes-table" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>No</th>
                            @if(auth()->user()->role === 'admin' || auth()->user()->role === 'hr' || auth()->user()->role === 'manager')
                                <th>Karyawan</th>
                            @endif
                            <th>Jenis Lembur</th>
                            <th>Tanggal</th>
                            <th>Waktu</th>
                            <th>Total Jam</th>
                            <th>Status</th>
                            <th>Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Detail Modal -->
<div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailModalLabel">Detail Permintaan Lembur</h5>
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
$(function () {
    // Global variables
    let currentOvertimeId = null;
    
    // Setup CSRF token for AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Initialize DataTable with server-side processing
    var table = $('#overtimes-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("overtimes.data") }}',
            type: 'GET',
            data: function(d) {
                d.start_date = $('#start_date').val();
                d.end_date = $('#end_date').val();
                d.status_filter = $('#status_filter').val();
            },
            error: function(xhr, error, thrown) {
                console.log('DataTable error:', error);
            }
        },
        columns: [
            {data: null, name: 'row_number', width: '50px', orderable: false, searchable: false, 
             render: function (data, type, row, meta) {
                 return meta.row + meta.settings._iDisplayStart + 1;
             }},
            @if(auth()->user()->role === 'admin' || auth()->user()->role === 'hr' || auth()->user()->role === 'manager')
            {data: 'employee_name', name: 'employee.name', width: '150px'},
            @endif
            {data: 'type_badge', name: 'overtime_type', width: '120px'},
            {data: 'formatted_date', name: 'date', width: '120px'},
            {data: 'time_range', name: 'start_time', width: '150px'},
            {data: 'total_hours_formatted', name: 'total_hours', width: '100px'},
            {data: 'status_badge', name: 'status', width: '100px'},
            {data: 'created_at_formatted', name: 'created_at', width: '130px'},
            {data: 'action', name: 'action', orderable: false, searchable: false, width: '150px'}
        ],
        scrollX: true,
        scrollCollapse: true,
        autoWidth: false,
        dom: 'Bfrtip',
        buttons: [
            {
                text: '<i class="fas fa-plus"></i> Ajukan Lembur',
                className: 'btn btn-primary btn-sm mr-2',
                action: function () {
                    window.location.href = '{{ route("overtimes.create") }}';
                }
            },
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel"></i> Excel',
                className: 'btn btn-success btn-sm',
                exportOptions: {
                    columns: @if(auth()->user()->role === 'admin' || auth()->user()->role === 'hr' || auth()->user()->role === 'manager')
                        [1, 2, 3, 4, 5, 6, 7]
                    @else
                        [1, 2, 3, 4, 5, 6]
                    @endif
                }
            },
            {
                extend: 'pdf',
                text: '<i class="fas fa-file-pdf"></i> PDF',
                className: 'btn btn-danger btn-sm',
                exportOptions: {
                    columns: @if(auth()->user()->role === 'admin' || auth()->user()->role === 'hr' || auth()->user()->role === 'manager')
                        [1, 2, 3, 4, 5, 6, 7]
                    @else
                        [1, 2, 3, 4, 5, 6]
                    @endif
                }
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print"></i> Print',
                className: 'btn btn-info btn-sm',
                exportOptions: {
                    columns: @if(auth()->user()->role === 'admin' || auth()->user()->role === 'hr' || auth()->user()->role === 'manager')
                        [1, 2, 3, 4, 5, 6, 7]
                    @else
                        [1, 2, 3, 4, 5, 6]
                    @endif
                }
            }
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
        },
        responsive: true,
        order: @if(auth()->user()->role === 'admin' || auth()->user()->role === 'hr' || auth()->user()->role === 'manager')
            [[7, 'desc']]
        @else
            [[6, 'desc']]
        @endif
    });

    // Handle view button click
    $(document).on('click', '.view-btn', function() {
        var id = $(this).data('id');
        loadOvertimeDetail(id);
    });

    // Handle edit button click
    $(document).on('click', '.edit-btn', function() {
        var id = $(this).data('id');
        window.location.href = '/overtimes/' + id + '/edit';
    });

    // Load overtime detail
    function loadOvertimeDetail(id) {
        // Show loading
        $('#detailContent').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');
        $('#detailModal').modal('show');

        $.ajax({
            url: '/overtimes/' + id,
            type: 'GET',
            errorHandled: true,
            headers: {
                'Accept': 'application/json'
            },
            success: function(response) {
                if (response.success) {
                    let overtime = response.data;
                    let detailHtml = `
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Jenis Lembur:</strong></td>
                                        <td>${overtime.type_badge}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Tanggal:</strong></td>
                                        <td>${overtime.formatted_date}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Waktu Mulai:</strong></td>
                                        <td>${overtime.start_time}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Waktu Selesai:</strong></td>
                                        <td>${overtime.end_time}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Total Jam:</strong></td>
                                        <td>${overtime.total_hours} jam</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Status:</strong></td>
                                        <td>${overtime.status_badge}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Dibuat:</strong></td>
                                        <td>${overtime.created_at_formatted}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Disetujui Oleh:</strong></td>
                                        <td>${overtime.approver_name || '-'}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Tanggal Persetujuan:</strong></td>
                                        <td>${overtime.approved_at_formatted || '-'}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Catatan:</strong></td>
                                        <td>${overtime.approval_notes || '-'}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <strong>Alasan:</strong><br>
                                <p>${overtime.reason}</p>
                            </div>
                        </div>
                        ${overtime.attachment_path ? `
                        <div class="row">
                            <div class="col-12">
                                <strong>Lampiran:</strong><br>
                                <a href="/storage/${overtime.attachment_path}" target="_blank" class="btn btn-sm btn-outline-primary">
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
                let message = 'Terjadi kesalahan saat memuat detail lembur';
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
        
        SwalHelper.confirmDelete('Konfirmasi Pembatalan Lembur', 'Apakah Anda yakin ingin membatalkan permintaan lembur: ' + name + '?', function(result) {
            if (result.isConfirmed) {
                // Show loading
                SwalHelper.loading('Membatalkan permintaan lembur...');

                // Send delete request
                $.ajax({
                    url: '/overtimes/' + id,
                    type: 'DELETE',
                    errorHandled: true,
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
                        var message = 'Terjadi kesalahan saat membatalkan permintaan lembur';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        
                        SwalHelper.error('Error!', message);
                    }
                });
            }
        });
    });



    // Initialize filter info
    updateFilterInfo();

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
            updateFilterInfo();
        });
    });

    // Reset filter
    $('#reset_filter').on('click', function() {
        var $btn = $(this);
        var originalText = $btn.html();
        
        // Show loading state
        $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Reset...');
        $btn.prop('disabled', true);
        
        // Clear all filters
        $('#start_date').val('');
        $('#end_date').val('');
        $('#status_filter').val('');
        
        table.ajax.reload(function() {
            // Restore button state
            $btn.html(originalText);
            $btn.prop('disabled', false);
            updateFilterInfo();
        });
    });

    // Date validation and auto-filter with debounce
    var filterTimeout;
    
    function applyFilterWithDelay() {
        clearTimeout(filterTimeout);
        filterTimeout = setTimeout(function() {
            table.ajax.reload();
            updateFilterInfo();
        }, 500); // 500ms delay
    }
    
    function updateFilterInfo() {
        var startDate = $('#start_date').val();
        var endDate = $('#end_date').val();
        var status = $('#status_filter').val();
        var info = 'Menampilkan ';
        
        if (startDate && endDate) {
            info += 'data dari ' + formatDate(startDate) + ' sampai ' + formatDate(endDate);
        } else if (startDate) {
            info += 'data dari ' + formatDate(startDate);
        } else if (endDate) {
            info += 'data sampai ' + formatDate(endDate);
        } else {
            info += 'semua data';
        }
        
        if (status) {
            var statusText = $('#status_filter option:selected').text();
            info += ' dengan status ' + statusText;
        }
        
        info += ' lembur';
        $('#filter-info').text(info);
    }
    
    function formatDate(dateString) {
        var date = new Date(dateString);
        return date.toLocaleDateString('id-ID', { 
            day: '2-digit', 
            month: '2-digit', 
            year: 'numeric' 
        });
    }
    
    $('#end_date').on('change', function() {
        var startDate = $('#start_date').val();
        var endDate = $(this).val();
        
        if (startDate && endDate && startDate > endDate) {
            SwalHelper.warning('Peringatan!', 'Tanggal akhir tidak boleh lebih kecil dari tanggal awal.');
            $(this).val('');
            return;
        }
        
        // Auto apply filter if both dates are selected
        if (startDate && endDate) {
            applyFilterWithDelay();
        }
    });

    $('#start_date').on('change', function() {
        var startDate = $(this).val();
        var endDate = $('#end_date').val();
        
        if (startDate && endDate && startDate > endDate) {
            SwalHelper.warning('Peringatan!', 'Tanggal awal tidak boleh lebih besar dari tanggal akhir.');
            $('#end_date').val('');
            return;
        }
        
        // Auto apply filter if both dates are selected
        if (startDate && endDate) {
            applyFilterWithDelay();
        }
    });

    // Status filter change
    $('#status_filter').on('change', function() {
        applyFilterWithDelay();
    });

    // Session messages sudah ditangani oleh global SwalHelper di layout
});
</script>
@endpush 