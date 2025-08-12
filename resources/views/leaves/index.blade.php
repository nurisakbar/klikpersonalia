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

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cancel Leave Request</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to cancel this leave request?</p>
                <p><strong id="deleteLeaveName"></strong></p>
                <p class="text-warning"><i class="fas fa-exclamation-triangle"></i> This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Yes, Cancel Leave Request</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Handle delete button click
    $('.delete-btn').click(function() {
        const leaveId = $(this).data('id');
        const leaveName = $(this).data('name');
        
        $('#deleteLeaveName').text(leaveName);
        $('#deleteForm').attr('action', '{{ url("leaves") }}/' + leaveId);
        $('#deleteModal').modal('show');
    });
});
</script>
@endpush 