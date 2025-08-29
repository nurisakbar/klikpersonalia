@extends('layouts.app')

@section('title', 'Daftar Rekening Bank - Aplikasi Payroll KlikMedis')
@section('page-title', 'Daftar Rekening Bank')

@section('breadcrumb')
<li class="breadcrumb-item active">Rekening Bank</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
				<table class="table table-bordered table-striped" id="bank-accounts-table" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Karyawan</th>
                                <th>Nama Bank</th>
                                <th>Pemilik Rekening</th>
                                <th>Nomor Rekening</th>
                                <th>Jenis Rekening</th>
                                <th>Kode Cabang</th>
                                <th>Status</th>
                                <th>Utama</th>
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
                <h5 class="modal-title" id="detailModalLabel">Detail Rekening Bank</h5>
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
<script>
// Fallback jQuery if not present (ensure $ is defined before DataTables plugins)
if (typeof window.$ === 'undefined' || typeof window.jQuery === 'undefined') {
    var jq = document.createElement('script');
    jq.src = 'https://code.jquery.com/jquery-3.7.1.min.js';
    jq.integrity = 'sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=';
    jq.crossOrigin = 'anonymous';
    document.head.appendChild(jq);
}
</script>
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
        setTimeout(initDataTable, 500);
        return;
    }

    $(function () {
        // Global variables
        let currentBankAccountId = null;
        let isEditMode = false;
        
        // Setup CSRF token for AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

    // Initialize DataTable with server-side processing
    var table = $('#bank-accounts-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("bank-accounts.data") }}',
            type: 'GET',
            error: function(xhr, error, thrown) {
                // You can show a toast notification here if needed
                // SwalHelper.toastError('Gagal memuat data rekening bank');
            }
        },
        columns: [
            {data: null, name: 'row_number', width: '50px', orderable: false, searchable: false, 
             render: function (data, type, row, meta) {
                 return meta.row + meta.settings._iDisplayStart + 1;
             }},
            {data: 'employee.name', name: 'employee.name', width: '150px'},
            {data: 'bank_name', name: 'bank_name', width: '120px'},
            {data: 'account_holder_name', name: 'account_holder_name', width: '150px'},
            {data: 'account_number_masked', name: 'account_number', width: '130px'},
            {data: 'account_type_label', name: 'account_type', width: '100px'},
            {data: 'branch_code', name: 'branch_code', width: '100px'},
            {data: 'status_badge', name: 'is_active', width: '80px'},
            {data: 'primary_badge', name: 'is_primary', width: '80px'},
            {data: 'action', name: 'action', orderable: false, searchable: false, width: '120px'}
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
					window.location.href = '{{ route("bank-accounts.create") }}';
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
        order: [[2, 'asc']]
    });

    // Pastikan tombol Add tidak memakai btn-secondary (force primary)
    var bankAccountsButtons = table.buttons().container();
    bankAccountsButtons.find('.dt-add-btn').removeClass('btn-secondary').addClass('btn-primary');

    // Layout info/pagination sudah diatur global via CSS

    // Handle view button click
    $(document).on('click', '.view-btn', function() {
        var id = $(this).data('id');
        loadBankAccountDetail(id);
    });

    // Handle edit button click
    $(document).on('click', '.edit-btn', function() {
        var id = $(this).data('id');
        window.location.href = '/bank-accounts/' + id + '/edit';
    });

    // Load bank account detail
    function loadBankAccountDetail(id) {
        // Show loading
        $('#detailContent').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');
        $('#detailModal').modal('show');

        $.ajax({
            url: '/bank-accounts/' + id,
            type: 'GET',
            errorHandled: true, // Mark as manually handled
            headers: {
                'Accept': 'application/json'
            },
            success: function(response) {
                if (response.success) {
                    let bankAccount = response.data;
                    let detailHtml = `
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Karyawan:</strong></td>
                                        <td>${bankAccount.employee ? bankAccount.employee.name : '-'}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Nama Bank:</strong></td>
                                        <td>${bankAccount.bank_name}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Pemilik Rekening:</strong></td>
                                        <td>${bankAccount.account_holder_name}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Nomor Rekening:</strong></td>
                                        <td>${bankAccount.account_number}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Jenis Rekening:</strong></td>
                                        <td>${getAccountTypeLabel(bankAccount.account_type)}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Status:</strong></td>
                                        <td>${bankAccount.is_active ? '<span class="badge badge-success">Aktif</span>' : '<span class="badge badge-warning">Tidak Aktif</span>'}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Kode Cabang:</strong></td>
                                        <td>${bankAccount.branch_code || '-'}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Swift Code:</strong></td>
                                        <td>${bankAccount.swift_code || '-'}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Rekening Utama:</strong></td>
                                        <td>${bankAccount.is_primary ? '<span class="badge badge-primary">Ya</span>' : '<span class="badge badge-secondary">Tidak</span>'}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Tanggal Dibuat:</strong></td>
                                        <td>${formatDate(bankAccount.created_at)}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Terakhir Diupdate:</strong></td>
                                        <td>${formatDate(bankAccount.updated_at)}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        ${bankAccount.notes ? `
                        <div class="row">
                            <div class="col-12">
                                <strong>Catatan:</strong><br>
                                <p>${bankAccount.notes}</p>
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
                let message = 'Terjadi kesalahan saat memuat detail rekening bank';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                $('#detailContent').html('<div class="text-center text-muted">Data tidak dapat dimuat</div>');
                SwalHelper.error('Error!', message);
            }
        });
    }

    // Helper function to get account type label
    function getAccountTypeLabel(type) {
        const types = {
            'savings': 'Tabungan',
            'current': 'Giro',
            'salary': 'Gaji'
        };
        return types[type] || type;
    }

    // Helper function to format date
    function formatDate(dateString) {
        if (!dateString) return '-';
        const date = new Date(dateString);
        return date.toLocaleDateString('id-ID', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    // Handle delete button click
    $(document).on('click', '.delete-btn', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        
        SwalHelper.confirmDelete('Konfirmasi Hapus', 'Apakah Anda yakin ingin menghapus rekening bank "' + name + '" ?', function(result) {
            if (result.isConfirmed) {
                // Show loading
                SwalHelper.loading('Menghapus...');

                // Send delete request
                $.ajax({
                    url: '/bank-accounts/' + id,
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
