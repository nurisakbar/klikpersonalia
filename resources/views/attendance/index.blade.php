@extends('layouts.app')

@section('title', 'Daftar Absensi - Aplikasi Payroll KlikMedis')
@section('page-title', 'Daftar Absensi')

@section('breadcrumb')
<li class="breadcrumb-item active">Absensi</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <!-- Filter Section -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="date_filter">Periode Tanggal:</label>
                            <select id="date_filter" class="form-control form-control-sm">
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
                            <label for="status_filter">Status:</label>
                            <select id="status_filter" class="form-control form-control-sm">
                                <option value="">Semua Status</option>
                                <option value="present">Hadir</option>
                                <option value="absent">Tidak Hadir</option>
                                <option value="late">Terlambat</option>
                                <option value="half_day">Setengah Hari</option>
                                <option value="leave">Cuti</option>
                                <option value="holiday">Libur</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>&nbsp;</label>
                            <div>
                                <button type="button" id="apply_filter" class="btn btn-primary btn-sm">
                                    <i class="fas fa-filter mr-1"></i> Terapkan Filter
                                </button>
                                <button type="button" id="reset_filter" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-undo mr-1"></i> Reset
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- DataTable -->
                    <table class="table table-bordered table-striped" id="attendance-table" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Karyawan</th>
                                <th>Departemen</th>
                                <th>Tanggal</th>
                                <th>Check In</th>
                                <th>Check Out</th>
                                <th>Total Jam</th>
                                <th>Lembur</th>
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

<!-- CSRF Token for AJAX -->
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@push('css')
<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
@endpush

@push('js')
<!-- Global SweetAlert Component -->
@include('components.sweet-alert')

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
$(function () {
    // Setup CSRF token for AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Initialize DataTable with server-side processing
    var table = $('#attendance-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("attendance.data") }}',
            type: 'GET',
            data: function(d) {
                d.date_filter = $('#date_filter').val();
                d.status_filter = $('#status_filter').val();
            }
        },
        columns: [
            {data: null, name: 'row_number', width: '50px', orderable: false, searchable: false, render: function (data, type, row, meta) { return meta.row + meta.settings._iDisplayStart + 1; }},
            {data: 'employee_name', name: 'employee_name', width: '200px'},
            {data: 'employee_department', name: 'employee_department', width: '150px'},
            {data: 'date_formatted', name: 'date', width: '100px'},
            {data: 'check_in_formatted', name: 'check_in', width: '100px'},
            {data: 'check_out_formatted', name: 'check_out', width: '100px'},
            {data: 'total_hours_formatted', name: 'total_hours', width: '100px'},
            {data: 'overtime_hours_formatted', name: 'overtime_hours', width: '100px'},
            {data: 'status_badge', name: 'status', width: '100px'},
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
					window.location.href = '{{ route("attendance.create") }}';
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
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
        },
        responsive: true,
        order: [[3, 'desc']] // Order by date descending
    });

    // Apply filter
    $('#apply_filter').on('click', function() {
        table.ajax.reload();
    });

    // Reset filter
    $('#reset_filter').on('click', function() {
        $('#date_filter').val('');
        $('#status_filter').val('');
        table.ajax.reload();
    });

    // Handle delete button click
    $(document).on('click', '.delete-btn', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        
        Swal.fire({
            icon: 'warning',
            title: 'Konfirmasi Hapus',
            text: 'Apakah Anda yakin ingin menghapus absensi "' + name + '" ?',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                SwalHelper.loading('Menghapus...');

                $.ajax({
                    url: '/attendance/' + id,
                    type: 'DELETE',
                    errorHandled: true,
                    success: function(response) {
                        if (response.success) {
                            SwalHelper.success('Berhasil!', response.message, 2000);
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
});
</script>
@endpush 