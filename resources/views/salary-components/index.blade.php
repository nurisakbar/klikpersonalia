@extends('layouts.app')

@section('title', 'Kelola Komponen Gaji - Aplikasi Payroll KlikMedis')
@section('page-title', 'Kelola Komponen Gaji')

@section('breadcrumb')
<li class="breadcrumb-item active">Komponen Gaji</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
				<table class="table table-bordered table-striped" id="salary-components-table" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>Urutan</th>
                                <th>Nama Komponen</th>
                                <th>Tipe</th>
                                <th>Nilai Default</th>
                                <th>Deskripsi</th>
                                <th>Status</th>
                                <th>Pajak</th>
                                <th>BPJS</th>
                                <th>Dibuat</th>
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

<!-- Bulk Actions Modal -->
<div class="modal fade" id="bulkActionsModal" tabindex="-1" aria-labelledby="bulkActionsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkActionsModalLabel">Aksi Massal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Pilih aksi yang akan dilakukan untuk <span id="selectedCount">0</span> komponen yang dipilih:</p>
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-success" id="bulkActivate">
                        <i class="fas fa-check"></i> Aktifkan Semua
                    </button>
                    <button type="button" class="btn btn-warning" id="bulkDeactivate">
                        <i class="fas fa-times"></i> Nonaktifkan Semua
                    </button>
                    <button type="button" class="btn btn-danger" id="bulkDelete">
                        <i class="fas fa-trash"></i> Hapus Semua
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            </div>
        </div>
    </div>
</div>

<!-- Sort Order Modal -->
<div class="modal fade" id="sortOrderModal" tabindex="-1" aria-labelledby="sortOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sortOrderModalLabel">Atur Urutan Komponen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted">Drag and drop untuk mengatur urutan komponen gaji:</p>
                <div id="sortableComponents" class="list-group">
                    <!-- Components will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="saveSortOrder">Simpan Urutan</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('css')
<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
<style>
    .sortable-ghost {
        opacity: 0.5;
        background: #f8f9fa;
    }
    .sortable-drag {
        background: #fff;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .component-item {
        cursor: move;
        user-select: none;
    }
    .component-item:hover {
        background-color: #f8f9fa;
    }
    .badge {
        font-size: 0.75em;
        padding: 0.25em 0.5em;
        border-radius: 0.25rem;
    }
    .badge-success {
        background-color: #28a745;
        color: white;
    }
    .badge-secondary {
        background-color: #6c757d;
        color: white;
    }
    .badge-info {
        background-color: #17a2b8;
        color: white;
    }
    .badge-warning {
        background-color: #ffc107;
        color: #212529;
    }
    .badge-danger {
        background-color: #dc3545;
        color: white;
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>

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
        let currentComponentId = null;
        
        // Setup CSRF token for AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

    // Initialize DataTable with server-side processing
    var table = $('#salary-components-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("salary-components.data") }}',
            type: 'GET',
            error: function(xhr, error, thrown) {
                // Handle DataTable errors silently or show a user-friendly message
                console.log('DataTable error:', error);
                // You can show a toast notification here if needed
                // SwalHelper.toastError('Gagal memuat data komponen gaji');
            }
        },
        columns: [
            {data: 'sort_order', name: 'sort_order', width: '80px'},
            {data: 'name', name: 'name', width: '200px'},
            {data: 'type_text', name: 'type', width: '100px'},
            {data: 'formatted_value', name: 'default_value', width: '150px'},
            {data: 'description', name: 'description', width: '250px'},
            {data: 'status_text', name: 'is_active', width: '100px'},
            {data: 'is_taxable_badge', name: 'is_taxable', width: '80px'},
            {data: 'is_bpjs_calculated_badge', name: 'is_bpjs_calculated', width: '80px'},
            {data: 'created_at_formatted', name: 'created_at', width: '120px'},
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
					window.location.href = '{{ route("salary-components.create") }}';
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
        language: window.DataTablesLanguage,
        responsive: true,
        order: [[0, 'asc']]
    });

    // Pastikan tombol Add tidak memakai btn-secondary (force primary)
    var salaryComponentsButtons = table.buttons().container();
    salaryComponentsButtons.find('.dt-add-btn').removeClass('btn-secondary').addClass('btn-primary');

    // Layout info/pagination sudah diatur global via CSS

    // Handle view button click
    $(document).on('click', '.view-btn', function() {
        var id = $(this).data('id');
        window.location.href = '/salary-components/' + id;
    });

    // Handle edit button click
    $(document).on('click', '.edit-btn', function() {
        var id = $(this).data('id');
        window.location.href = '/salary-components/' + id + '/edit';
    });



    // Handle delete button click
    $(document).on('click', '.delete-btn', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        
        SwalHelper.confirmDelete('Konfirmasi Hapus', 'Apakah Anda yakin ingin menghapus komponen gaji "' + name + '" ?', function(result) {
            if (result.isConfirmed) {
                // Show loading
                SwalHelper.loading('Menghapus...');

                // Send delete request
                $.ajax({
                    url: '/salary-components/' + id,
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
}

// Start initialization
initDataTable();
</script>
@endpush
