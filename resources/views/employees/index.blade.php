@extends('layouts.app')

@section('title', 'Daftar Karyawan - Aplikasi Payroll KlikMedis')
@section('page-title', 'Daftar Karyawan')

@section('breadcrumb')
<li class="breadcrumb-item active">Karyawan</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
				<table class="table table-bordered table-striped" id="employees-table" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>ID Karyawan</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Telepon</th>
                                <th>Departemen</th>
                                <th>Jabatan</th>
                                <th>Tanggal Bergabung</th>
                                <th>Gaji Pokok</th>
                                <th>Status</th>
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
                <h5 class="modal-title" id="detailModalLabel">Detail Karyawan</h5>
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

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(function () {
    // Global variables
    let currentEmployeeId = null;
    let isEditMode = false;
    
    // Setup CSRF token for AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Initialize DataTable with server-side processing
    var table = $('#employees-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("employees.data") }}',
            type: 'GET'
        },
        columns: [
            {data: 'employee_id', name: 'employee_id', width: '120px'},
            {data: 'name', name: 'name', width: '200px'},
            {data: 'email', name: 'email', width: '200px'},
            {data: 'phone', name: 'phone', width: '120px'},
            {data: 'department', name: 'department', width: '150px'},
            {data: 'position', name: 'position', width: '150px'},
            {data: 'join_date_formatted', name: 'join_date', width: '130px'},
            {data: 'salary_formatted', name: 'basic_salary', width: '130px'},
            {data: 'status_badge', name: 'status', width: '100px'},
            {data: 'action', name: 'action', orderable: false, searchable: false, width: '150px'}
        ],
        scrollX: true,
        scrollCollapse: true,
        autoWidth: false,
        dom: 'Bfrtip',
        buttons: [
			{
				text: '<i class="fas fa-plus"></i> Tambah Karyawan',
				className: 'btn btn-primary btn-sm mr-2',
				action: function () {
					window.location.href = '{{ route("employees.create") }}';
				}
			},
			{
				extend: 'excel',
				text: '<i class="fas fa-file-excel"></i> Excel',
				className: 'btn btn-success btn-sm',
				exportOptions: {
					columns: [0, 1, 2, 3, 4, 5, 6, 7, 8]
				}
			},
			{
				extend: 'pdf',
				text: '<i class="fas fa-file-pdf"></i> PDF',
				className: 'btn btn-danger btn-sm',
				exportOptions: {
					columns: [0, 1, 2, 3, 4, 5, 6, 7, 8]
				}
			},
			{
				extend: 'print',
				text: '<i class="fas fa-print"></i> Print',
				className: 'btn btn-info btn-sm',
				exportOptions: {
					columns: [0, 1, 2, 3, 4, 5, 6, 7, 8]
				}
			}
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
        },
        responsive: true,
        order: [[0, 'asc']]
    });

    // Pastikan tombol Add tidak memakai btn-secondary (force primary)
    var employeesButtons = table.buttons().container();
    employeesButtons.find('.dt-add-btn').removeClass('btn-secondary').addClass('btn-primary');

    // Layout info/pagination sudah diatur global via CSS

    // Handle view button click
    $(document).on('click', '.view-btn', function() {
        var id = $(this).data('id');
        loadEmployeeDetail(id);
    });

    // Handle edit button click
    $(document).on('click', '.edit-btn', function() {
        var id = $(this).data('id');
        window.location.href = '/employees/' + id + '/edit';
    });

    // Load employee detail
    function loadEmployeeDetail(id) {
        // Show loading
        $('#detailContent').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');
        $('#detailModal').modal('show');

        $.ajax({
            url: '/employees/' + id,
            type: 'GET',
            headers: {
                'Accept': 'application/json'
            },
            success: function(response) {
                if (response.success) {
                    let employee = response.data;
                    let detailHtml = `
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>ID Karyawan:</strong></td>
                                        <td>${employee.employee_id}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Nama Lengkap:</strong></td>
                                        <td>${employee.name}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Email:</strong></td>
                                        <td>${employee.email}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Telepon:</strong></td>
                                        <td>${employee.phone}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Departemen:</strong></td>
                                        <td>${employee.department}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Jabatan:</strong></td>
                                        <td>${employee.position}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Tanggal Bergabung:</strong></td>
                                        <td>${employee.join_date_formatted}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Gaji Pokok:</strong></td>
                                        <td>${employee.salary_formatted}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Status:</strong></td>
                                        <td>${employee.status_badge}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Kontak Darurat:</strong></td>
                                        <td>${employee.emergency_contact || '-'}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Bank:</strong></td>
                                        <td>${employee.bank_name || '-'}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>No. Rekening:</strong></td>
                                        <td>${employee.bank_account || '-'}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        ${employee.address ? `
                        <div class="row">
                            <div class="col-12">
                                <strong>Alamat:</strong><br>
                                <p>${employee.address}</p>
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
                let message = 'Terjadi kesalahan saat memuat detail karyawan';
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
        
        SwalHelper.confirm(
            'Konfirmasi Hapus',
            'Apakah Anda yakin ingin menghapus karyawan "' + name + '" ?'
        ).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                SwalHelper.loading('Menghapus...');

                // Send delete request
                $.ajax({
                    url: '/employees/' + id,
                    type: 'DELETE',
                    success: function(response) {
                        if (response.success) {
                            SwalHelper.success('Berhasil!', response.message).then(() => {
                                // Reload DataTable
                                table.ajax.reload();
                            });
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