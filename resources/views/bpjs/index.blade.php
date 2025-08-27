@extends('layouts.app')

@section('title', 'Kelola BPJS- Aplikasi Payroll KlikMedis')
@section('page-title', 'Kelola BPJS')

@section('breadcrumb')
<li class="breadcrumb-item active">Kelola BPJS</li>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Info boxes -->
    <div class="row">
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-info"><i class="fas fa-heartbeat"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">BPJS Kesehatan</span>
                    <span class="info-box-number">{{ $summary['kesehatan_count'] ?? 0 }}</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-success"><i class="fas fa-briefcase"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">BPJS Ketenagakerjaan</span>
                    <span class="info-box-number">{{ $summary['ketenagakerjaan_count'] ?? 0 }}</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-warning"><i class="fas fa-clock"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Pending</span>
                    <span class="info-box-number">{{ $summary['pending_count'] ?? 0 }}</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-success"><i class="fas fa-check"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Paid</span>
                    <span class="info-box-number">{{ $summary['paid_count'] ?? 0 }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- BPJS Records Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <table class="table table-bordered table-striped" id="bpjs-table" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Karyawan</th>
                                <th>ID Karyawan</th>
                                <th>Jenis BPJS</th>
                                <th>Periode</th>
                                <th>Gaji Pokok</th>
                                <th>Kontribusi Karyawan</th>
                                <th>Kontribusi Perusahaan</th>
                                <th>Total Kontribusi</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Calculation Form -->
    <div class="row">
        <div class="col-12">
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title">Bulk BPJS Calculation</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('bpjs.calculateForPayroll') }}" id="bulkCalculationForm">
                        @csrf
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="payroll_period">Payroll Period:</label>
                                    <input type="month" name="payroll_period" id="payroll_period" 
                                           class="form-control" value="{{ date('Y-m') }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="bpjs_type">BPJS Type:</label>
                                    <select name="bpjs_type" id="bpjs_type" class="form-control" required>
                                        <option value="both">Both (Kesehatan & Ketenagakerjaan)</option>
                                        <option value="kesehatan">BPJS Kesehatan Only</option>
                                        <option value="ketenagakerjaan">BPJS Ketenagakerjaan Only</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button type="submit" class="btn btn-success btn-block" id="calculateBtn">
                                        <i class="fas fa-calculator"></i> Calculate BPJS for All Employees
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
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
                <h5 class="modal-title" id="detailModalLabel">Detail BPJS</h5>
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

<script>
$(document).ready(function() {
    console.log('Document ready, jQuery version:', $.fn.jquery);
    
    // Check if DataTable is available
    if (typeof $.fn.DataTable === 'undefined') {
        console.error('DataTable is not available!');
        return;
    }
    
    console.log('DataTable is available');
    
    // Setup CSRF token for AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Initialize DataTable
    var table = $('#bpjs-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("bpjs.data") }}',
            type: 'GET'
        },
        columns: [
            {data: null, name: 'row_number', orderable: false, searchable: false, 
             render: function (data, type, row, meta) {
                 return meta.row + meta.settings._iDisplayStart + 1;
             }},
            {data: 'employee_name', name: 'employee_name'},
            {data: 'employee_id', name: 'employee_id'},
            {data: 'bpjs_type_badge', name: 'bpjs_type'},
            {data: 'period_formatted', name: 'bpjs_period'},
            {data: 'base_salary_formatted', name: 'base_salary'},
            {data: 'employee_contribution_formatted', name: 'employee_contribution'},
            {data: 'company_contribution_formatted', name: 'company_contribution'},
            {data: 'total_contribution_formatted', name: 'total_contribution'},
            {data: 'status_badge', name: 'status'},
            {data: 'action', name: 'action', orderable: false, searchable: false}
        ],
        scrollX: true,
        dom: 'Bfrtip',
        buttons: [
            {
                text: '<i class="fas fa-plus"></i> Tambah',
                className: 'btn btn-primary btn-sm',
                action: function () {
                    window.location.href = '{{ route("bpjs.create") }}';
                }
            },
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel"></i> Excel',
                className: 'btn btn-success btn-sm'
            },
            {
                extend: 'pdf',
                text: '<i class="fas fa-file-pdf"></i> PDF',
                className: 'btn btn-danger btn-sm'
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print"></i> Print',
                className: 'btn btn-info btn-sm'
            }
        ],
        language: {
            'sProcessing': 'Memproses...',
            'sLengthMenu': 'Tampilkan _MENU_ entri',
            'sZeroRecords': 'Tidak ditemukan data yang sesuai',
            'sInfo': 'Menampilkan _START_ sampai _END_ dari _TOTAL_ entri',
            'sInfoEmpty': 'Menampilkan 0 sampai 0 dari 0 entri',
            'sInfoFiltered': '(disaring dari _MAX_ entri keseluruhan)',
            'sSearch': 'Cari:',
            'oPaginate': {
                'sFirst': 'Pertama',
                'sPrevious': 'Sebelumnya',
                'sNext': 'Selanjutnya',
                'sLast': 'Terakhir'
            }
        },
        order: [[4, 'desc']]
    });

    // Handle view button
    $(document).on('click', '.view-btn', function() {
        var id = $(this).data('id');
        loadBpjsDetail(id);
    });

    // Handle edit button
    $(document).on('click', '.edit-btn', function() {
        var id = $(this).data('id');
        window.location.href = '/bpjs/' + id + '/edit';
    });

    // Load BPJS detail
    function loadBpjsDetail(id) {
        $('#detailContent').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');
        $('#detailModal').modal('show');

        $.ajax({
            url: '/bpjs/' + id,
            type: 'GET',
            errorHandled: true, // Mark as manually handled
            headers: {
                'Accept': 'application/json'
            },
            success: function(response) {
                if (response.success) {
                    let bpjs = response.data;
                    let detailHtml = `
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr><td><strong>Karyawan:</strong></td><td>${bpjs.employee_name}</td></tr>
                                    <tr><td><strong>ID Karyawan:</strong></td><td>${bpjs.employee_id}</td></tr>
                                    <tr><td><strong>Jenis BPJS:</strong></td><td>${bpjs.bpjs_type_badge}</td></tr>
                                    <tr><td><strong>Periode:</strong></td><td>${bpjs.period_formatted}</td></tr>
                                    <tr><td><strong>Gaji Pokok:</strong></td><td>${bpjs.base_salary_formatted}</td></tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr><td><strong>Kontribusi Karyawan:</strong></td><td>${bpjs.employee_contribution_formatted}</td></tr>
                                    <tr><td><strong>Kontribusi Perusahaan:</strong></td><td>${bpjs.company_contribution_formatted}</td></tr>
                                    <tr><td><strong>Total Kontribusi:</strong></td><td>${bpjs.total_contribution_formatted}</td></tr>
                                    <tr><td><strong>Status:</strong></td><td>${bpjs.status_badge}</td></tr>
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
                let message = 'Terjadi kesalahan saat memuat detail BPJS';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                $('#detailContent').html('<div class="text-center text-muted">Data tidak dapat dimuat</div>');
                SwalHelper.error('Error!', message);
            }
        });
    }

    // Handle delete button
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

    // Handle bulk calculation form
    $('#bulkCalculationForm').on('submit', function(e) {
        e.preventDefault();
        
        var payrollPeriod = $('#payroll_period').val();
        var bpjsType = $('#bpjs_type option:selected').text();
        
        // Show confirmation dialog
        SwalHelper.confirm(
            'Konfirmasi Perhitungan BPJS',
            `Apakah Anda yakin ingin menghitung BPJS ${bpjsType} untuk periode ${payrollPeriod}?`,
            function(result) {
                if (result.isConfirmed) {
                    // Show loading
                    SwalHelper.loading('Menghitung BPJS...');
                    
                    // Disable button
                    $('#calculateBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menghitung...');
                    
                    // Submit form using AJAX
                    $.ajax({
                        url: $('#bulkCalculationForm').attr('action'),
                        type: 'POST',
                        data: $('#bulkCalculationForm').serialize(),
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        success: function(response) {
                            // Re-enable button
                            $('#calculateBtn').prop('disabled', false).html('<i class="fas fa-calculator"></i> Calculate BPJS for All Employees');
                            
                            // Close loading
                            SwalHelper.closeLoading();
                            
                            // Show success message
                            SwalHelper.success('Berhasil!', response.message || 'Perhitungan BPJS berhasil diselesaikan');
                            
                            // Reload DataTable
                            setTimeout(function() {
                                table.ajax.reload();
                            }, 1000);
                        },
                        error: function(xhr) {
                            // Re-enable button
                            $('#calculateBtn').prop('disabled', false).html('<i class="fas fa-calculator"></i> Calculate BPJS for All Employees');
                            
                            // Close loading
                            SwalHelper.closeLoading();
                            
                            // Show error message
                            var message = 'Terjadi kesalahan saat menghitung BPJS';
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
});
</script>
@endpush 