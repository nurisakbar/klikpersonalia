@extends('layouts.app')

@section('title', 'Edit Leave Request')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-edit mr-2"></i>
                        Edit Leave Request
                    </h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('leaves.update', $leave->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="leave_type">Leave Type <span class="text-danger">*</span></label>
                                    <select name="leave_type" id="leave_type" class="form-control @error('leave_type') is-invalid @enderror" required>
                                        <option value="">Select Leave Type</option>
                                        <option value="annual" {{ old('leave_type', $leave->leave_type) == 'annual' ? 'selected' : '' }}>Annual Leave</option>
                                        <option value="sick" {{ old('leave_type', $leave->leave_type) == 'sick' ? 'selected' : '' }}>Sick Leave</option>
                                        <option value="maternity" {{ old('leave_type', $leave->leave_type) == 'maternity' ? 'selected' : '' }}>Maternity Leave</option>
                                        <option value="paternity" {{ old('leave_type', $leave->leave_type) == 'paternity' ? 'selected' : '' }}>Paternity Leave</option>
                                        <option value="other" {{ old('leave_type', $leave->leave_type) == 'other' ? 'selected' : '' }}>Other Leave</option>
                                    </select>
                                    @error('leave_type')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="attachment">Attachment (Optional)</label>
                                    <input type="file" name="attachment" id="attachment" class="form-control @error('attachment') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png">
                                    <small class="form-text text-muted">Supported formats: PDF, JPG, JPEG, PNG (Max: 2MB)</small>
                                    @if($leave->attachment)
                                        <div class="mt-2">
                                            <small class="text-info">
                                                <i class="fas fa-info-circle mr-1"></i>
                                                Current attachment: {{ basename($leave->attachment) }}
                                            </small>
                                        </div>
                                    @endif
                                    @error('attachment')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="start_date">Start Date <span class="text-danger">*</span></label>
                                    <input type="date" name="start_date" id="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date', $leave->start_date->format('Y-m-d')) }}" required min="{{ date('Y-m-d') }}">
                                    @error('start_date')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="end_date">End Date <span class="text-danger">*</span></label>
                                    <input type="date" name="end_date" id="end_date" class="form-control @error('end_date') is-invalid @enderror" value="{{ old('end_date', $leave->end_date->format('Y-m-d')) }}" required min="{{ date('Y-m-d') }}">
                                    @error('end_date')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="reason">Reason <span class="text-danger">*</span></label>
                            <textarea name="reason" id="reason" rows="4" class="form-control @error('reason') is-invalid @enderror" placeholder="Please provide a detailed reason for your leave request..." required>{{ old('reason', $leave->reason) }}</textarea>
                            @error('reason')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i> Update Leave Request
                            </button>
                            <a href="{{ route('leaves.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left mr-1"></i> Back to Leave List
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <!-- Current Leave Details -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-info-circle mr-2"></i>
                        Current Leave Details
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Leave Type:</strong></td>
                            <td>{!! $leave->type_badge !!}</td>
                        </tr>
                        <tr>
                            <td><strong>Start Date:</strong></td>
                            <td>{{ $leave->formatted_start_date }}</td>
                        </tr>
                        <tr>
                            <td><strong>End Date:</strong></td>
                            <td>{{ $leave->formatted_end_date }}</td>
                        </tr>
                        <tr>
                            <td><strong>Total Days:</strong></td>
                            <td>{{ $leave->total_days }} days</td>
                        </tr>
                        <tr>
                            <td><strong>Status:</strong></td>
                            <td>{!! $leave->status_badge !!}</td>
                        </tr>
                        <tr>
                            <td><strong>Submitted:</strong></td>
                            <td>{{ $leave->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Leave Balance Card -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-chart-pie mr-2"></i>
                        Leave Balance ({{ date('Y') }})
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="info-box bg-info">
                                <span class="info-box-icon">
                                    <i class="fas fa-calendar-check"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Annual Leave</span>
                                    <span class="info-box-number">{{ $leaveBalance['annual_remaining'] }}/{{ $leaveBalance['annual_total'] }}</span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: {{ ($leaveBalance['annual_used'] / $leaveBalance['annual_total']) * 100 }}%"></div>
                                    </div>
                                    <span class="progress-description">
                                        {{ $leaveBalance['annual_used'] }} days used
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="info-box bg-warning">
                                <span class="info-box-icon">
                                    <i class="fas fa-user-injured"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Sick Leave</span>
                                    <span class="info-box-number">{{ $leaveBalance['sick_remaining'] }}/{{ $leaveBalance['sick_total'] }}</span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: {{ ($leaveBalance['sick_used'] / $leaveBalance['sick_total']) * 100 }}%"></div>
                                    </div>
                                    <span class="progress-description">
                                        {{ $leaveBalance['sick_used'] }} days used
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="info-box bg-success">
                                <span class="info-box-icon">
                                    <i class="fas fa-baby"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Maternity Leave</span>
                                    <span class="info-box-number">{{ $leaveBalance['maternity_remaining'] }}/{{ $leaveBalance['maternity_total'] }}</span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: {{ ($leaveBalance['maternity_used'] / $leaveBalance['maternity_total']) * 100 }}%"></div>
                                    </div>
                                    <span class="progress-description">
                                        {{ $leaveBalance['maternity_used'] }} days used
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="info-box bg-primary">
                                <span class="info-box-icon">
                                    <i class="fas fa-user-tie"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Paternity Leave</span>
                                    <span class="info-box-number">{{ $leaveBalance['paternity_remaining'] }}/{{ $leaveBalance['paternity_total'] }}</span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: {{ ($leaveBalance['paternity_used'] / $leaveBalance['paternity_total']) * 100 }}%"></div>
                                    </div>
                                    <span class="progress-description">
                                        {{ $leaveBalance['paternity_used'] }} days used
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="info-box bg-secondary">
                                <span class="info-box-icon">
                                    <i class="fas fa-ellipsis-h"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Other Leave</span>
                                    <span class="info-box-number">{{ $leaveBalance['other_remaining'] }}/{{ $leaveBalance['other_total'] }}</span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: {{ ($leaveBalance['other_used'] / $leaveBalance['other_total']) * 100 }}%"></div>
                                    </div>
                                    <span class="progress-description">
                                        {{ $leaveBalance['other_used'] }} days used
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Set minimum end date based on start date
    $('#start_date').change(function() {
        const startDate = $(this).val();
        if (startDate) {
            $('#end_date').attr('min', startDate);
        }
    });
    
    // Calculate total days when dates change
    function calculateDays() {
        const startDate = $('#start_date').val();
        const endDate = $('#end_date').val();
        
        if (startDate && endDate) {
            const start = new Date(startDate);
            const end = new Date(endDate);
            const diffTime = Math.abs(end - start);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
            
            // Show total days info
            if (diffDays > 0) {
                $('.form-group').each(function() {
                    if ($(this).find('label').text().includes('End Date')) {
                        if ($(this).find('.days-info').length === 0) {
                            $(this).append('<small class="form-text text-info days-info"><i class="fas fa-calendar-day mr-1"></i> Total: ' + diffDays + ' day(s)</small>');
                        } else {
                            $(this).find('.days-info').html('<i class="fas fa-calendar-day mr-1"></i> Total: ' + diffDays + ' day(s)');
                        }
                    }
                });
            }
        }
    }
    
    $('#start_date, #end_date').change(calculateDays);
    
    // Calculate initial days
    calculateDays();
});
</script>
@endpush

@push('styles')
<style>
.info-box {
    display: block;
    min-height: 80px;
    background: #fff;
    width: 100%;
    box-shadow: 0 1px 1px rgba(0,0,0,0.1);
    border-radius: 2px;
    margin-bottom: 15px;
}

.info-box-icon {
    border-radius: 2px 0 0 2px;
    display: block;
    float: left;
    height: 80px;
    width: 80px;
    text-align: center;
    font-size: 40px;
    line-height: 80px;
    background: rgba(0,0,0,0.2);
}

.info-box-content {
    padding: 5px 10px;
    margin-left: 80px;
}

.info-box-text {
    display: block;
    font-size: 14px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.info-box-number {
    display: block;
    font-weight: bold;
    font-size: 18px;
}

.progress {
    height: 3px;
    margin: 5px 0;
}

.progress-description {
    display: block;
    font-size: 12px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
</style>
@endpush 