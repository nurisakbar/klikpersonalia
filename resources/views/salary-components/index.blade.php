@extends('layouts.app')

@section('title', 'Komponen Gaji')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">Komponen Gaji</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Komponen Gaji</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Daftar Komponen Gaji</h5>
                        <div>
                            <a href="{{ route('salary-components.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Tambah Komponen
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="salary-components-table">
                            <thead>
                                <tr>
                                    <th width="30">
                                        <input type="checkbox" id="select-all">
                                    </th>
                                    <th width="80">Urutan</th>
                                    <th width="200">Nama Komponen</th>
                                    <th width="100">Tipe</th>
                                    <th width="150">Nilai Default</th>
                                    <th width="250">Deskripsi</th>
                                    <th width="100">Status</th>
                                    <th width="80">Pajak</th>
                                    <th width="80">BPJS</th>
                                    <th width="120">Dibuat</th>
                                    <th width="150">Aksi</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
    }
</style>
@endpush

@push('js')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js"></script>
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
$(function () {
    // Setup CSRF token for AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
    console.log('Document ready for salary components');
    
    // Initialize DataTable with server-side processing
    var table = $('#salary-components-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("salary-components.data") }}',
            type: 'GET',
            error: function(xhr, error, thrown) {
                console.log('DataTable error:', error);
                console.log('Response:', xhr.responseText);
                console.log('Status:', xhr.status);
            }
        },
        columns: [
            {data: 'checkbox', name: 'checkbox', orderable: false, searchable: false, width: '30px'},
            {data: 'sort_order', name: 'sort_order', width: '80px'},
            {data: 'name', name: 'name', width: '200px'},
            {data: 'type_badge', name: 'type', orderable: false, searchable: false, width: '100px'},
            {data: 'formatted_value', name: 'default_value', orderable: false, searchable: false, width: '150px'},
            {data: 'description', name: 'description', width: '250px'},
            {data: 'status_badge', name: 'is_active', orderable: false, searchable: false, width: '100px'},
            {data: 'is_taxable', name: 'is_taxable', orderable: false, searchable: false, width: '80px'},
            {data: 'is_bpjs_calculated', name: 'is_bpjs_calculated', orderable: false, searchable: false, width: '80px'},
            {data: 'created_at', name: 'created_at', width: '120px'},
            {data: 'action', name: 'action', orderable: false, searchable: false, width: '150px'}
        ],
        scrollX: true,
        scrollCollapse: true,
        autoWidth: false,
        dom: 'Bfrtip',
        buttons: [
            {
                text: '<i class="fas fa-plus"></i> Tambah Komponen',
                className: 'btn btn-primary btn-sm',
                action: function () {
                    window.location.href = '{{ route("salary-components.create") }}';
                }
            },
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel"></i> Excel',
                className: 'btn btn-success btn-sm',
                exportOptions: {
                    columns: [1, 2, 3, 4, 5, 6, 7, 8, 9]
                }
            },
            {
                extend: 'pdf',
                text: '<i class="fas fa-file-pdf"></i> PDF',
                className: 'btn btn-danger btn-sm',
                exportOptions: {
                    columns: [1, 2, 3, 4, 5, 6, 7, 8, 9]
                }
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print"></i> Print',
                className: 'btn btn-info btn-sm',
                exportOptions: {
                    columns: [1, 2, 3, 4, 5, 6, 7, 8, 9]
                }
            },
            {
                extend: 'reload',
                text: '<i class="fas fa-sync-alt"></i> Reload',
                className: 'btn btn-secondary btn-sm'
            }
        ],
        order: [[1, 'asc'], [2, 'asc']],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
        },
        responsive: true
    });
    
    console.log('DataTable initialized for salary components');
    
    // Test AJAX call manually
    $.get('{{ route("salary-components.data") }}')
        .done(function(response) {
            console.log('Manual AJAX success:', response);
        })
        .fail(function(xhr, status, error) {
            console.log('Manual AJAX failed:', {xhr, status, error});
        });
    
    // Select all checkbox functionality
    $('#select-all').on('change', function() {
        $('.component-checkbox').prop('checked', this.checked);
        updateSelectedCount();
    });
    
    // Individual checkbox change
    $(document).on('change', '.component-checkbox', function() {
        updateSelectedCount();
        updateSelectAllState();
    });
    
    // Update selected count
    function updateSelectedCount() {
        const selectedCount = $('.component-checkbox:checked').length;
        $('#selectedCount').text(selectedCount);
    }
    
    // Update select all state
    function updateSelectAllState() {
        const totalCheckboxes = $('.component-checkbox').length;
        const checkedCheckboxes = $('.component-checkbox:checked').length;
        
        if (checkedCheckboxes === 0) {
            $('#select-all').prop('indeterminate', false).prop('checked', false);
        } else if (checkedCheckboxes === totalCheckboxes) {
            $('#select-all').prop('indeterminate', false).prop('checked', true);
        } else {
            $('#select-all').prop('indeterminate', true);
        }
    }
    
    // Bulk actions
    $('#bulkActivate').on('click', function() {
        const selectedIds = getSelectedIds();
        if (selectedIds.length > 0) {
            bulkToggleStatus(selectedIds, true);
        }
    });
    
    $('#bulkDeactivate').on('click', function() {
        const selectedIds = getSelectedIds();
        if (selectedIds.length > 0) {
            bulkToggleStatus(selectedIds, false);
        }
    });
    
    $('#bulkDelete').on('click', function() {
        const selectedIds = getSelectedIds();
        if (selectedIds.length > 0) {
            if (confirm('Apakah Anda yakin ingin menghapus komponen yang dipilih?')) {
                bulkDelete(selectedIds);
            }
        }
    });
    
    // Get selected component IDs
    function getSelectedIds() {
        return $('.component-checkbox:checked').map(function() {
            return $(this).val();
        }).get();
    }
    
    // Bulk toggle status
    function bulkToggleStatus(ids, status) {
        $.ajax({
            url: '{{ route("salary-components.bulk-toggle-status") }}',
            method: 'POST',
            data: {
                ids: ids,
                status: status,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    table.ajax.reload();
                    $('#bulkActionsModal').modal('hide');
                    showAlert('success', response.message);
                } else {
                    showAlert('error', response.message);
                }
            },
            error: function() {
                showAlert('error', 'Terjadi kesalahan saat mengubah status komponen.');
            }
        });
    }
    
    // Bulk delete
    function bulkDelete(ids) {
        $.ajax({
            url: '{{ route("salary-components.bulk-delete") }}',
            method: 'POST',
            data: {
                ids: ids,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    table.ajax.reload();
                    $('#bulkActionsModal').modal('hide');
                    showAlert('success', response.message);
                } else {
                    showAlert('error', response.message);
                }
            },
            error: function() {
                showAlert('error', 'Terjadi kesalahan saat menghapus komponen.');
            }
        });
    }
    
    // Show alert
    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        
        $('.card-body').prepend(alertHtml);
        
        setTimeout(function() {
            $('.alert').fadeOut();
        }, 5000);
    }
    
    // Sort order functionality
    $('#sortOrderModal').on('show.bs.modal', function() {
        loadComponentsForSorting();
    });
    
    function loadComponentsForSorting() {
        $.ajax({
            url: '{{ route("salary-components.index") }}',
            method: 'GET',
            success: function(response) {
                const components = response.data || [];
                renderSortableComponents(components);
            },
            error: function() {
                showAlert('error', 'Gagal memuat data komponen untuk pengurutan.');
            }
        });
    }
    
    function renderSortableComponents(components) {
        const container = $('#sortableComponents');
        container.empty();
        
        components.forEach(function(component, index) {
            const item = `
                <div class="list-group-item component-item d-flex justify-content-between align-items-center" data-id="${component.id}">
                    <div>
                        <strong>${component.name}</strong>
                        <small class="text-muted d-block">${component.type_text}</small>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="badge bg-secondary me-2">${component.sort_order}</span>
                        <i class="fas fa-grip-vertical text-muted"></i>
                    </div>
                </div>
            `;
            container.append(item);
        });
        
        // Initialize Sortable
        new Sortable(container[0], {
            animation: 150,
            ghostClass: 'sortable-ghost',
            dragClass: 'sortable-drag',
            onEnd: function() {
                updateSortOrderNumbers();
            }
        });
    }
    
    function updateSortOrderNumbers() {
        $('#sortableComponents .component-item').each(function(index) {
            $(this).find('.badge').text(index + 1);
        });
    }
    
    $('#saveSortOrder').on('click', function() {
        const components = [];
        $('#sortableComponents .component-item').each(function(index) {
            components.push({
                id: $(this).data('id'),
                sort_order: index + 1
            });
        });
        
        $.ajax({
            url: '{{ route("salary-components.update-sort-order") }}',
            method: 'POST',
            data: {
                components: components,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                $('#sortOrderModal').modal('hide');
                table.ajax.reload();
                showAlert('success', 'Urutan komponen berhasil diperbarui.');
            },
            error: function() {
                showAlert('error', 'Gagal memperbarui urutan komponen.');
            }
        });
    });
});
</script>
@endpush
