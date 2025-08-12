@extends('layouts.app')

@section('title', 'Leave Approval')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-check-circle mr-2"></i>
                        Leave Approval
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('leaves.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left mr-1"></i> Back to Leave List
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($pendingLeaves->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
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
                                <tbody>
                                    @foreach($pendingLeaves as $leave)
                                        <tr>
                                            <td>
                                                <div>
                                                    <strong>{{ $leave->employee->name }}</strong><br>
                                                    <small class="text-muted">{{ $leave->employee->employee_id }}</small>
                                                </div>
                                            </td>
                                            <td>{!! $leave->type_badge !!}</td>
                                            <td>{{ $leave->formatted_start_date }}</td>
                                            <td>{{ $leave->formatted_end_date }}</td>
                                            <td>{{ $leave->total_days }} days</td>
                                            <td>
                                                <div class="text-truncate" style="max-width: 200px;" title="{{ $leave->reason }}">
                                                    {{ $leave->reason }}
                                                </div>
                                            </td>
                                            <td>{{ $leave->created_at->format('d/m/Y H:i') }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-success approve-btn" 
                                                            data-id="{{ $leave->id }}" 
                                                            data-employee="{{ $leave->employee->name }}"
                                                            data-type="{{ $leave->leave_type }}"
                                                            data-days="{{ $leave->total_days }}"
                                                            title="Approve">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-danger reject-btn" 
                                                            data-id="{{ $leave->id }}" 
                                                            data-employee="{{ $leave->employee->name }}"
                                                            data-type="{{ $leave->leave_type }}"
                                                            data-days="{{ $leave->total_days }}"
                                                            title="Reject">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                    <a href="{{ route('leaves.show', $leave->id) }}" class="btn btn-sm btn-info" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="d-flex justify-content-center">
                            {{ $pendingLeaves->links() }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <h5 class="text-success">No Pending Leave Requests</h5>
                            <p class="text-muted">All leave requests have been processed.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-check mr-2"></i> Approve Leave Request
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="approveForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        You are about to approve a leave request for <strong id="approveEmployeeName"></strong>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Leave Type:</strong> <span id="approveLeaveType"></span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Total Days:</strong> <span id="approveTotalDays"></span></p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="approval_notes">Approval Notes (Optional)</label>
                        <textarea name="approval_notes" id="approval_notes" rows="3" class="form-control" placeholder="Add any notes or comments..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check mr-1"></i> Approve Leave Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-times mr-2"></i> Reject Leave Request
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        You are about to reject a leave request for <strong id="rejectEmployeeName"></strong>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Leave Type:</strong> <span id="rejectLeaveType"></span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Total Days:</strong> <span id="rejectTotalDays"></span></p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="rejection_notes">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea name="approval_notes" id="rejection_notes" rows="3" class="form-control" placeholder="Please provide a reason for rejection..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times mr-1"></i> Reject Leave Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Handle approve button click
    $('.approve-btn').click(function() {
        const leaveId = $(this).data('id');
        const employeeName = $(this).data('employee');
        const leaveType = $(this).data('type');
        const totalDays = $(this).data('days');
        
        $('#approveEmployeeName').text(employeeName);
        $('#approveLeaveType').text(leaveType.charAt(0).toUpperCase() + leaveType.slice(1));
        $('#approveTotalDays').text(totalDays + ' days');
        $('#approveForm').attr('action', '{{ url("leaves") }}/' + leaveId + '/approve');
        $('#approveModal').modal('show');
    });
    
    // Handle reject button click
    $('.reject-btn').click(function() {
        const leaveId = $(this).data('id');
        const employeeName = $(this).data('employee');
        const leaveType = $(this).data('type');
        const totalDays = $(this).data('days');
        
        $('#rejectEmployeeName').text(employeeName);
        $('#rejectLeaveType').text(leaveType.charAt(0).toUpperCase() + leaveType.slice(1));
        $('#rejectTotalDays').text(totalDays + ' days');
        $('#rejectForm').attr('action', '{{ url("leaves") }}/' + leaveId + '/reject');
        $('#rejectModal').modal('show');
    });
    
    // Handle form submissions
    $('#approveForm').submit(function(e) {
        e.preventDefault();
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        submitBtn.html('<i class="fas fa-spinner fa-spin mr-1"></i> Processing...').prop('disabled', true);
        
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    $('#approveModal').modal('hide');
                    showAlert('success', response.message);
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    showAlert('error', response.message);
                }
            },
            error: function() {
                showAlert('error', 'An error occurred. Please try again.');
            },
            complete: function() {
                submitBtn.html(originalText).prop('disabled', false);
            }
        });
    });
    
    $('#rejectForm').submit(function(e) {
        e.preventDefault();
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        submitBtn.html('<i class="fas fa-spinner fa-spin mr-1"></i> Processing...').prop('disabled', true);
        
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    $('#rejectModal').modal('hide');
                    showAlert('success', response.message);
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    showAlert('error', response.message);
                }
            },
            error: function() {
                showAlert('error', 'An error occurred. Please try again.');
            },
            complete: function() {
                submitBtn.html(originalText).prop('disabled', false);
            }
        });
    });
});

function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    `;
    
    // Insert alert at the top of the card body
    const cardBody = document.querySelector('.card-body');
    cardBody.insertAdjacentHTML('afterbegin', alertHtml);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        const alert = document.querySelector('.alert');
        if (alert) {
            alert.remove();
        }
    }, 5000);
}
</script>
@endpush 