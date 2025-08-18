@extends('layouts.app')

@section('title', 'Daftar Payroll - Aplikasi Payroll KlikMedis')
@section('page-title', 'Daftar Payroll')

@section('breadcrumb')
<li class="breadcrumb-item active">Payroll</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
				<table class="table table-bordered table-striped" id="payroll-table" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama Karyawan</th>
                                <th>Departemen</th>
                                <th>Periode</th>
                                <th>Gaji Pokok</th>
                                <th>Total Gaji</th>
                                <th>Status</th>
                                <th>Tanggal Bayar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
				</table>
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
    // Setup CSRF token for AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Initialize DataTable with server-side processing
    var table = $('#payroll-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("payrolls.data") }}',
            type: 'GET'
        },
        columns: [
            {data: 'id', name: 'id', width: '50px'},
            {data: 'employee_name', name: 'employee_name', width: '200px'},
            {data: 'employee_department', name: 'employee_department', width: '150px'},
            {data: 'period', name: 'period', width: '100px'},
            {data: 'salary_formatted', name: 'basic_salary', width: '130px'},
            {data: 'total_formatted', name: 'total_salary', width: '130px'},
            {data: 'status_badge', name: 'status', width: '100px'},
            {data: 'payment_date_formatted', name: 'payment_date', width: '120px'},
            {data: 'action', name: 'action', orderable: false, searchable: false, width: '150px'}
        ],
        scrollX: true,
        scrollCollapse: true,
        autoWidth: false,
        dom: 'Bfrtip',
		buttons: [
			{
				text: '<i class="fas fa-plus"></i> Tambah Payroll',
				class: 'btn btn-primary dt-add-btn',
				action: function () {
					window.location.href = '{{ route("payrolls.create") }}';
				}
			},
			{
				extend: 'excel',
				text: '<i class="fas fa-file-excel"></i> Excel',
				class: 'btn btn-success btn-sm',
				exportOptions: {
					columns: [0, 1, 2, 3, 4, 5, 6, 7]
				}
			},
			{
				extend: 'pdf',
				text: '<i class="fas fa-file-pdf"></i> PDF',
				class: 'btn btn-danger',
				exportOptions: {
					columns: [0, 1, 2, 3, 4, 5, 6, 7]
				}
			},
			{
				extend: 'print',
				text: '<i class="fas fa-print"></i> Print',
				class: 'btn btn-info btn-xs',
				exportOptions: {
					columns: [0, 1, 2, 3, 4, 5, 6, 7]
				}
			}
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
        },
        responsive: true,
        order: [[1, 'asc']]
    });

    // Layout info/pagination sudah diatur global via CSS

    // Pastikan tombol Add tidak memakai btn-secondary (force primary)
    var payrollButtons = table.buttons().container();
    payrollButtons.find('.dt-add-btn').removeClass('btn-secondary').addClass('btn-primary');

    // Handle delete button click
    $(document).on('click', '.delete-btn', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        
        SwalHelper.confirm(
            'Konfirmasi Hapus',
            'Apakah Anda yakin ingin menghapus payroll "' + name + '" ?'
        ).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                SwalHelper.loading('Menghapus...');

                // Send delete request
                $.ajax({
                    url: '/payroll/' + id,
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