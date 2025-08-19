@extends('layouts.app')

@section('title', 'Overtime Approval')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-check-circle mr-2"></i>
                        Overtime Approval
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('overtimes.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left mr-1"></i> Back to Overtime
                        </a>
                        <a href="{{ route('overtimes.statistics') }}" class="btn btn-info btn-sm">
                            <i class="fas fa-chart-bar mr-1"></i> Overtime Statistics
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($pendingOvertimes->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Employee</th>
                                        <th>Department</th>
                                        <th>Overtime Type</th>
                                        <th>Date</th>
                                        <th>Start Time</th>
                                        <th>End Time</th>
                                        <th>Total Hours</th>
                                        <th>Reason</th>
                                        <th>Submitted</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pendingOvertimes as $overtime)
                                        <tr>
                                            <td>
                                                <strong>{{ $overtime->employee->name }}</strong><br>
                                                <small class="text-muted">{{ $overtime->employee->employee_id }}</small>
                                            </td>
                                            <td>{{ $overtime->employee->department }}</td>
                                            <td>
                                                {!! $overtime->type_badge !!}
                                            </td>
                                            <td>{{ $overtime->formatted_date }}</td>
                                            <td>{{ $overtime->start_time }}</td>
                                            <td>{{ $overtime->end_time }}</td>
                                            <td>{{ $overtime->total_hours }} hours</td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-info" 
                                                        data-toggle="modal" 
                                                        data-target="#reasonModal{{ $overtime->id }}">
                                                    <i class="fas fa-eye mr-1"></i> View
                                                </button>
                                            </td>
                                            <td>{{ $overtime->created_at->format('d/m/Y H:i') }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-success approve-btn" 
                                                            data-id="{{ $overtime->id }}" 
                                                            data-name="{{ $overtime->employee->name }} - {{ $overtime->overtime_type }} overtime on {{ $overtime->formatted_date }}"
                                                            title="Approve">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-danger reject-btn" 
                                                            data-id="{{ $overtime->id }}" 
                                                            data-name="{{ $overtime->employee->name }} - {{ $overtime->overtime_type }} overtime on {{ $overtime->formatted_date }}"
                                                            title="Reject">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="d-flex justify-content-center">
                            {{ $pendingOvertimes->links() }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <h5 class="text-success">No Pending Overtime Requests</h5>
                            <p class="text-muted">All overtime requests have been processed.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reason Modal for each overtime -->
@foreach($pendingOvertimes as $overtime)
<div class="modal fade" id="reasonModal{{ $overtime->id }}" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Overtime Reason</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong>Employee:</strong><br>
                        {{ $overtime->employee->name }} ({{ $overtime->employee->employee_id }})
                    </div>
                    <div class="col-md-6">
                        <strong>Date:</strong><br>
                        {{ $overtime->formatted_date }}
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <strong>Start Time:</strong><br>
                        {{ $overtime->start_time }}
                    </div>
                    <div class="col-md-6">
                        <strong>End Time:</strong><br>
                        {{ $overtime->end_time }}
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <strong>Total Hours:</strong><br>
                        {{ $overtime->total_hours }} hours
                    </div>
                    <div class="col-md-6">
                        <strong>Type:</strong><br>
                        {!! $overtime->type_badge !!}
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-12">
                        <strong>Reason:</strong><br>
                        <p class="mt-2">{{ $overtime->reason }}</p>
                    </div>
                </div>
                @if($overtime->attachment)
                <hr>
                <div class="row">
                    <div class="col-12">
                        <strong>Attachment:</strong><br>
                        <a href="{{ asset('storage/' . $overtime->attachment) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-download mr-1"></i> Download Attachment
                        </a>
                    </div>
                </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endforeach

<!-- Rejection Reason Modal -->
<div class="modal fade" id="rejectionModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject Overtime Request</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="rejectionForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="rejection_reason">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="3" required 
                                  placeholder="Please provide a reason for rejection..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times mr-1"></i> Reject
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
$(function () {
    // Approve overtime request
    $(document).on('click', '.approve-btn', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        
        SwalHelper.confirm(
            'Approve Overtime Request',
            'Are you sure you want to approve this overtime request?<br><strong>' + name + '</strong>',
            'Yes, Approve',
            'Cancel'
        ).then((result) => {
            if (result.isConfirmed) {
                SwalHelper.loading('Approving...');
                
                $.ajax({
                    url: '/overtimes/' + id + '/approve',
                    type: 'POST',
                    errorHandled: true, // Mark as manually handled
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            SwalHelper.success('Approved!', response.message).then(() => {
                                location.reload();
                            });
                        } else {
                            SwalHelper.error('Error!', response.message);
                        }
                    },
                    error: function(xhr) {
                        var message = 'An error occurred while approving the request';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        SwalHelper.error('Error!', message);
                    }
                });
            }
        });
    });

    // Reject overtime request
    $(document).on('click', '.reject-btn', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        
        // Set the form action
        $('#rejectionForm').attr('action', '/overtimes/' + id + '/reject');
        
        // Show the rejection modal
        $('#rejectionModal').modal('show');
    });

    // Handle rejection form submission
    $('#rejectionForm').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var url = form.attr('action');
        var reason = $('#rejection_reason').val();
        
        if (!reason.trim()) {
            SwalHelper.error('Error!', 'Please provide a rejection reason.');
            return;
        }
        
        SwalHelper.loading('Rejecting...');
        
        $.ajax({
            url: url,
            type: 'POST',
            errorHandled: true, // Mark as manually handled
            data: {
                _token: '{{ csrf_token() }}',
                rejection_reason: reason
            },
            success: function(response) {
                if (response.success) {
                    $('#rejectionModal').modal('hide');
                    $('#rejection_reason').val('');
                    SwalHelper.success('Rejected!', response.message).then(() => {
                        location.reload();
                    });
                } else {
                    SwalHelper.error('Error!', response.message);
                }
            },
            error: function(xhr) {
                var message = 'An error occurred while rejecting the request';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                SwalHelper.error('Error!', message);
            }
        });
    });
});
</script>
@endpush
