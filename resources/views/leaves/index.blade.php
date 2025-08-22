@extends('layouts.app')

@section('title', 'Kelola Cuti - Aplikasi Payroll KlikMedis')
@section('page-title', 'Kelola Cuti')

@section('breadcrumb')
<li class="breadcrumb-item active">Cuti</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <table class="table table-bordered table-striped" id="leaves-table" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>No</th>
                            @if(auth()->user()->role === 'admin' || auth()->user()->role === 'hr' || auth()->user()->role === 'manager')
                                <th>Karyawan</th>
                            @endif
                            <th>Jenis Cuti</th>
                            <th>Periode Cuti</th>
                            <th>Total Hari</th>
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
    let currentLeaveId = null;
    
    // Setup CSRF token for AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Initialize DataTable with server-side processing
    var table = $('#leaves-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("leaves.data") }}',
            type: 'GET',
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
            {data: 'type_badge', name: 'leave_type', width: '120px'},
            {data: 'date_range', name: 'start_date', width: '180px'},
            {data: 'total_days_formatted', name: 'total_days', width: '100px'},
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
                text: '<i class="fas fa-plus"></i> Ajukan Cuti',
                className: 'btn btn-primary btn-sm mr-2',
                action: function () {
                    window.location.href = '{{ route("leaves.create") }}';
                }
            },
            @if(auth()->user()->role === 'admin' || auth()->user()->role === 'hr' || auth()->user()->role === 'manager')
            {
                text: '<i class="fas fa-check"></i> Persetujuan Cuti',
                className: 'btn btn-warning btn-sm mr-2',
                action: function () {
                    window.location.href = '{{ route("leaves.approval") }}';
                }
            },
            @endif
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel"></i> Excel',
                className: 'btn btn-success btn-sm',
                exportOptions: {
                    columns: @if(auth()->user()->role === 'admin' || auth()->user()->role === 'hr' || auth()->user()->role === 'manager')
                        [1, 2, 3, 4, 5, 6]
                    @else
                        [1, 2, 3, 4, 5]
                    @endif
                }
            },
            {
                extend: 'pdf',
                text: '<i class="fas fa-file-pdf"></i> PDF',
                className: 'btn btn-danger btn-sm',
                exportOptions: {
                    columns: @if(auth()->user()->role === 'admin' || auth()->user()->role === 'hr' || auth()->user()->role === 'manager')
                        [1, 2, 3, 4, 5, 6]
                    @else
                        [1, 2, 3, 4, 5]
                    @endif
                }
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print"></i> Print',
                className: 'btn btn-info btn-sm',
                exportOptions: {
                    columns: @if(auth()->user()->role === 'admin' || auth()->user()->role === 'hr' || auth()->user()->role === 'manager')
                        [1, 2, 3, 4, 5, 6]
                    @else
                        [1, 2, 3, 4, 5]
                    @endif
                }
            }
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
        },
        responsive: true,
        order: @if(auth()->user()->role === 'admin' || auth()->user()->role === 'hr' || auth()->user()->role === 'manager')
            [[6, 'desc']]
        @else
            [[5, 'desc']]
        @endif
    });

    // Handle view button click
    $(document).on('click', '.view-btn', function() {
        var id = $(this).data('id');
        loadLeaveDetail(id);
    });

    // Handle edit button click
    $(document).on('click', '.edit-btn', function() {
        var id = $(this).data('id');
        window.location.href = '/leaves/' + id + '/edit';
    });

    // Load leave detail
    function loadLeaveDetail(id) {
        // Show loading
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
                                        <td>${leave.created_at_formatted}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Disetujui Oleh:</strong></td>
                                        <td>${leave.approver_name || '-'}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Tanggal Persetujuan:</strong></td>
                                        <td>${leave.approved_at_formatted || '-'}</td>
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
                        ${leave.attachment_path ? `
                        <div class="row">
                            <div class="col-12">
                                <strong>Lampiran:</strong><br>
                                <a href="/storage/${leave.attachment_path}" target="_blank" class="btn btn-sm btn-outline-primary">
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

    // Handle delete button click
    $(document).on('click', '.delete-btn', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        
        SwalHelper.confirmDelete('Konfirmasi Pembatalan Cuti', 'Apakah Anda yakin ingin membatalkan permintaan cuti: <strong>' + name + '</strong>?', function(result) {
            if (result.isConfirmed) {
                // Show loading
                SwalHelper.loading('Membatalkan permintaan cuti...');

                // Send delete request
                $.ajax({
                    url: '/leaves/' + id,
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
                        var message = 'Terjadi kesalahan saat membatalkan permintaan cuti';
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