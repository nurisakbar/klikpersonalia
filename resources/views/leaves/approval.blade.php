@extends('layouts.app')

@section('title', 'Leave Approval')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-check-circle mr-2"></i>
                        Leave Approval
                    </h6>
                    <div class="card-tools">
                        <a href="{{ route('leaves.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left mr-1"></i> Back to Leave List
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="leave-approval-table" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Leave Type</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Total Days</th>
                                    <th>Reason</th>
                                    <th>Submitted</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
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

<style>
/* Custom DataTable Pagination Styling */
.dataTables_wrapper .dataTables_paginate .paginate_button {
    border: 1px solid #dee2e6 !important;
    background: #fff !important;
    color: #007bff !important;
    padding: 0.375rem 0.75rem !important;
    margin: 0 2px !important;
    border-radius: 0.25rem !important;
    transition: all 0.15s ease-in-out !important;
}

.dataTables_wrapper .dataTables_paginate .paginate_button:hover {
    background: #e9ecef !important;
    border-color: #adb5bd !important;
    color: #0056b3 !important;
}

.dataTables_wrapper .dataTables_paginate .paginate_button.current {
    background: #007bff !important;
    border-color: #007bff !important;
    color: #fff !important;
}

.dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
    background: #f8f9fa !important;
    border-color: #dee2e6 !important;
    color: #6c757d !important;
    cursor: not-allowed !important;
}

.dataTables_wrapper .dataTables_info {
    padding-top: 0.5rem !important;
    color: #6c757d !important;
}

.dataTables_wrapper .dataTables_length select {
    border: 1px solid #ced4da !important;
    border-radius: 0.25rem !important;
    padding: 0.375rem 0.75rem !important;
}

.dataTables_wrapper .dataTables_filter input {
    border: 1px solid #ced4da !important;
    border-radius: 0.25rem !important;
    padding: 0.375rem 0.75rem !important;
}

/* Button styling */
.dt-buttons .btn {
    margin-right: 0.25rem !important;
}

/* Table styling */
#leave-approval-table {
    border-collapse: collapse !important;
}

#leave-approval-table th {
    background-color: #f8f9fa !important;
    border-color: #dee2e6 !important;
    font-weight: 600 !important;
}

#leave-approval-table td {
    border-color: #dee2e6 !important;
    vertical-align: middle !important;
}

/* Action buttons styling */
.btn-group .btn {
    margin-right: 2px !important;
}

.btn-group .btn:last-child {
    margin-right: 0 !important;
}
</style>
@endpush

@push('js')
<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(function () {
    // Global variables
    let table;
    
    // Setup CSRF token for AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    console.log('Starting Leave Approval DataTable initialization...');

    // Initialize DataTable with server-side processing
    table = $('#leave-approval-table').DataTable({
        processing: true,
        serverSide: false, // Changed to false for testing
        ajax: {
            url: '{{ route("leaves.approval.data") }}',
            type: 'GET',
            dataSrc: 'data', // Added for client-side processing
            error: function (xhr, error, thrown) {
                console.error('DataTable AJAX Error:', error);
                console.error('XHR Status:', xhr.status);
                console.error('XHR Response:', xhr.responseText);
                SwalHelper.toastError('Error loading data: ' + error);
            }
        },
        columns: [
            {data: 'employee_info', name: 'employee.name', width: '200px'},
            {data: 'leave_type_badge', name: 'leave_type', width: '150px'},
            {data: 'start_date_formatted', name: 'start_date', width: '120px'},
            {data: 'end_date_formatted', name: 'end_date', width: '120px'},
            {data: 'total_days', name: 'total_days', width: '100px'},
            {data: 'reason', name: 'reason', width: '250px'},
            {data: 'created_at_formatted', name: 'created_at', width: '150px'},
            {data: 'action', name: 'action', orderable: false, searchable: false, width: '150px'}
        ],
        scrollX: true,
        scrollCollapse: true,
        autoWidth: false,
        pageLength: 10,
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel"></i> Excel',
                className: 'btn btn-success btn-sm',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6]
                }
            },
            {
                extend: 'pdf',
                text: '<i class="fas fa-file-pdf"></i> PDF',
                className: 'btn btn-danger btn-sm',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6]
                }
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print"></i> Print',
                className: 'btn btn-info btn-sm',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6]
                }
            }
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
        },
        responsive: true,
        order: [[6, 'desc']],
        initComplete: function () {
            console.log('DataTable initialization completed successfully');
        },
        drawCallback: function () {
            console.log('DataTable draw completed');
        }
    });

    console.log('DataTable initialized:', table);

    // Handle approve button click (delegated event)
    $(document).on('click', '.approve-btn', function() {
        const leaveId = $(this).data('id');
        const employeeName = $(this).data('employee');
        const leaveType = $(this).data('type');
        const totalDays = $(this).data('days');
        
        console.log('Approve button clicked for leave ID:', leaveId);
        
        Swal.fire({
            title: 'Konfirmasi Approval Cuti',
            html: `
                <div class="text-left">
                    <p><strong>Karyawan:</strong> ${employeeName}</p>
                    <p><strong>Jenis Cuti:</strong> ${leaveType.charAt(0).toUpperCase() + leaveType.slice(1)}</p>
                    <p><strong>Total Hari:</strong> ${totalDays} hari</p>
                    <div class="form-group mt-3">
                        <label for="approval_notes">Catatan Approval (Opsional)</label>
                        <textarea id="approval_notes" class="form-control" rows="3" placeholder="Tambahkan catatan atau komentar..."></textarea>
                    </div>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Setujui!',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            reverseButtons: true,
            preConfirm: () => {
                return {
                    notes: document.getElementById('approval_notes').value
                };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                SwalHelper.loading('Menyetujui permintaan cuti...');
                
                // Send AJAX request
                $.ajax({
                    url: `{{ url('leaves') }}/${leaveId}/approve`,
                    method: 'POST',
                    data: {
                        approval_notes: result.value.notes
                    },
                    success: function(response) {
                        SwalHelper.close();
                        if (response.success) {
                            SwalHelper.toastSuccess(response.message);
                            setTimeout(function() {
                                table.ajax.reload();
                            }, 1500);
                        } else {
                            SwalHelper.toastError(response.message);
                        }
                    },
                    error: function(xhr) {
                        SwalHelper.close();
                        let message = 'Terjadi kesalahan saat menyetujui permintaan cuti.';
                        
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        
                        SwalHelper.toastError(message);
                    }
                });
            }
        });
    });
    
    // Handle reject button click (delegated event)
    $(document).on('click', '.reject-btn', function() {
        const leaveId = $(this).data('id');
        const employeeName = $(this).data('employee');
        const leaveType = $(this).data('type');
        const totalDays = $(this).data('days');
        
        console.log('Reject button clicked for leave ID:', leaveId);
        
        Swal.fire({
            title: 'Konfirmasi Penolakan Cuti',
            html: `
                <div class="text-left">
                    <p><strong>Karyawan:</strong> ${employeeName}</p>
                    <p><strong>Jenis Cuti:</strong> ${leaveType.charAt(0).toUpperCase() + leaveType.slice(1)}</p>
                    <p><strong>Total Hari:</strong> ${totalDays} hari</p>
                    <div class="form-group mt-3">
                        <label for="rejection_notes">Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea id="rejection_notes" class="form-control" rows="3" placeholder="Berikan alasan penolakan..." required></textarea>
                    </div>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Tolak!',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            reverseButtons: true,
            preConfirm: () => {
                const notes = document.getElementById('rejection_notes').value;
                if (!notes.trim()) {
                    Swal.showValidationMessage('Alasan penolakan harus diisi');
                    return false;
                }
                return {
                    notes: notes
                };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                SwalHelper.loading('Menolak permintaan cuti...');
                
                // Send AJAX request
                $.ajax({
                    url: `{{ url('leaves') }}/${leaveId}/reject`,
                    method: 'POST',
                    data: {
                        approval_notes: result.value.notes
                    },
                    success: function(response) {
                        SwalHelper.close();
                        if (response.success) {
                            SwalHelper.toastSuccess(response.message);
                            setTimeout(function() {
                                table.ajax.reload();
                            }, 1500);
                        } else {
                            SwalHelper.toastError(response.message);
                        }
                    },
                    error: function(xhr) {
                        SwalHelper.close();
                        let message = 'Terjadi kesalahan saat menolak permintaan cuti.';
                        
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        
                        SwalHelper.toastError(message);
                    }
                });
            }
        });
    });
});
</script>
@endpush 