@extends('layouts.app')

@section('title', 'Leave Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-calendar-alt mr-2"></i>
                        Leave Management
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('leaves.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus mr-1"></i> Submit Leave Request
                        </a>
                        <a href="{{ route('leaves.balance') }}" class="btn btn-info btn-sm">
                            <i class="fas fa-chart-pie mr-1"></i> Leave Balance
                        </a>
                        @if(in_array(auth()->user()->role, ['admin', 'hr', 'manager']))
                        <a href="{{ route('leaves.approval') }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-check-circle mr-1"></i> Leave Approval
                        </a>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    @if($leaves->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Leave Type</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Total Days</th>
                                        <th>Status</th>
                                        <th>Submitted</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($leaves as $leave)
                                        <tr>
                                            <td>
                                                {!! $leave->type_badge !!}
                                            </td>
                                            <td>{{ $leave->formatted_start_date }}</td>
                                            <td>{{ $leave->formatted_end_date }}</td>
                                            <td>{{ $leave->total_days }} days</td>
                                            <td>{!! $leave->status_badge !!}</td>
                                            <td>{{ $leave->created_at->format('d/m/Y H:i') }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('leaves.show', $leave->id) }}" class="btn btn-sm btn-info" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if($leave->status === 'pending')
                                                        <a href="{{ route('leaves.edit', $leave->id) }}" class="btn btn-sm btn-warning" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-sm btn-danger delete-btn" 
                                                                data-id="{{ $leave->id }}" 
                                                                data-name="{{ $leave->leave_type }} leave from {{ $leave->formatted_start_date }} to {{ $leave->formatted_end_date }}"
                                                                title="Cancel">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="d-flex justify-content-center">
                            {{ $leaves->links() }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Leave Requests Found</h5>
                            <p class="text-muted">You haven't submitted any leave requests yet.</p>
                            <a href="{{ route('leaves.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus mr-1"></i> Submit Your First Leave Request
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>


@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Handle delete button click with AJAX
    $('.delete-btn').click(function() {
        const leaveId = $(this).data('id');
        const leaveName = $(this).data('name');
        
        SwalHelper.confirmDelete(
            'Konfirmasi Pembatalan Cuti',
            `Apakah Anda yakin ingin membatalkan permintaan cuti: <strong>${leaveName}</strong>?`,
            'Ya, Batalkan!',
            'Tidak'
        ).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                SwalHelper.loading('Membatalkan permintaan cuti...');
                
                // Send AJAX request
                $.ajax({
                    url: `{{ url('leaves') }}/${leaveId}`,
                    method: 'DELETE',
                    errorHandled: true, // Mark as manually handled
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        SwalHelper.closeLoading();
                        SwalHelper.toastSuccess('Permintaan cuti berhasil dibatalkan!');
                        
                        // Reload page after 1.5 seconds
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    },
                    error: function(xhr) {
                        SwalHelper.closeLoading();
                        let message = 'Terjadi kesalahan saat membatalkan permintaan cuti.';
                        
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