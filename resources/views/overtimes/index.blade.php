@extends('layouts.app')

@section('title', 'Overtime Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-clock mr-2"></i>
                        Overtime Management
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('overtimes.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus mr-1"></i> Submit Overtime Request
                        </a>
                        <a href="{{ route('overtimes.statistics') }}" class="btn btn-info btn-sm">
                            <i class="fas fa-chart-bar mr-1"></i> Overtime Statistics
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($overtimes->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Overtime Type</th>
                                        <th>Date</th>
                                        <th>Start Time</th>
                                        <th>End Time</th>
                                        <th>Total Hours</th>
                                        <th>Status</th>
                                        <th>Submitted</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($overtimes as $overtime)
                                        <tr>
                                            <td>
                                                {!! $overtime->type_badge !!}
                                            </td>
                                            <td>{{ $overtime->formatted_date }}</td>
                                            <td>{{ $overtime->start_time }}</td>
                                            <td>{{ $overtime->end_time }}</td>
                                            <td>{{ $overtime->total_hours }} hours</td>
                                            <td>{!! $overtime->status_badge !!}</td>
                                            <td>{{ $overtime->created_at->format('d/m/Y H:i') }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('overtimes.show', $overtime->id) }}" class="btn btn-sm btn-info" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if($overtime->status === 'pending')
                                                        <a href="{{ route('overtimes.edit', $overtime->id) }}" class="btn btn-sm btn-warning" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-sm btn-danger delete-btn" 
                                                                data-id="{{ $overtime->id }}" 
                                                                data-name="{{ $overtime->overtime_type }} overtime on {{ $overtime->formatted_date }}"
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
                            {{ $overtimes->links() }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Overtime Requests Found</h5>
                            <p class="text-muted">You haven't submitted any overtime requests yet.</p>
                            <a href="{{ route('overtimes.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus mr-1"></i> Submit Your First Overtime Request
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
                <h5 class="modal-title">Cancel Overtime Request</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to cancel this overtime request?</p>
                <p><strong id="deleteOvertimeName"></strong></p>
                <p class="text-warning"><i class="fas fa-exclamation-triangle"></i> This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Yes, Cancel Overtime Request</button>
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
        const overtimeId = $(this).data('id');
        const overtimeName = $(this).data('name');
        
        $('#deleteOvertimeName').text(overtimeName);
        $('#deleteForm').attr('action', '{{ url("overtimes") }}/' + overtimeId);
        $('#deleteModal').modal('show');
    });
});
</script>
@endpush 