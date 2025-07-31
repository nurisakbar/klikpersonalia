@extends('layouts.app')

@section('title', 'Submit Overtime Request')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-plus mr-2"></i>
                        Submit Overtime Request
                    </h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('overtimes.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="overtime_type">Overtime Type <span class="text-danger">*</span></label>
                                    <select name="overtime_type" id="overtime_type" class="form-control @error('overtime_type') is-invalid @enderror" required>
                                        <option value="">Select Overtime Type</option>
                                        <option value="regular" {{ old('overtime_type') == 'regular' ? 'selected' : '' }}>Regular Overtime</option>
                                        <option value="holiday" {{ old('overtime_type') == 'holiday' ? 'selected' : '' }}>Holiday Overtime</option>
                                        <option value="weekend" {{ old('overtime_type') == 'weekend' ? 'selected' : '' }}>Weekend Overtime</option>
                                        <option value="emergency" {{ old('overtime_type') == 'emergency' ? 'selected' : '' }}>Emergency Overtime</option>
                                    </select>
                                    @error('overtime_type')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="attachment">Attachment (Optional)</label>
                                    <input type="file" name="attachment" id="attachment" class="form-control @error('attachment') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png">
                                    <small class="form-text text-muted">Supported formats: PDF, JPG, JPEG, PNG (Max: 2MB)</small>
                                    @error('attachment')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="date">Date <span class="text-danger">*</span></label>
                                    <input type="date" name="date" id="date" class="form-control @error('date') is-invalid @enderror" value="{{ old('date') }}" required min="{{ date('Y-m-d') }}">
                                    @error('date')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="start_time">Start Time <span class="text-danger">*</span></label>
                                    <input type="time" name="start_time" id="start_time" class="form-control @error('start_time') is-invalid @enderror" value="{{ old('start_time') }}" required>
                                    @error('start_time')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="end_time">End Time <span class="text-danger">*</span></label>
                                    <input type="time" name="end_time" id="end_time" class="form-control @error('end_time') is-invalid @enderror" value="{{ old('end_time') }}" required>
                                    @error('end_time')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="reason">Reason <span class="text-danger">*</span></label>
                            <textarea name="reason" id="reason" rows="4" class="form-control @error('reason') is-invalid @enderror" placeholder="Please provide a detailed reason for your overtime request..." required>{{ old('reason') }}</textarea>
                            @error('reason')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane mr-1"></i> Submit Overtime Request
                            </button>
                            <a href="{{ route('overtimes.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left mr-1"></i> Back to Overtime List
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <!-- Overtime Statistics Card -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-chart-bar mr-2"></i>
                        This Month's Overtime ({{ date('M Y') }})
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="info-box bg-primary">
                                <span class="info-box-icon">
                                    <i class="fas fa-clock"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Hours</span>
                                    <span class="info-box-number">{{ $overtimeStats['total_hours'] }} hours</span>
                                    <div class="progress">
                                        <div class="progress-bar bg-light" style="width: 100%"></div>
                                    </div>
                                    <span class="progress-description">
                                        {{ $overtimeStats['total_requests'] }} requests
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="info-box bg-success">
                                <span class="info-box-icon">
                                    <i class="fas fa-chart-line"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Average Hours</span>
                                    <span class="info-box-number">{{ $overtimeStats['average_hours'] }} hours</span>
                                    <div class="progress">
                                        <div class="progress-bar bg-light" style="width: 100%"></div>
                                    </div>
                                    <span class="progress-description">
                                        per request
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="info-box bg-info">
                                <span class="info-box-icon">
                                    <i class="fas fa-calendar-day"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Regular Overtime</span>
                                    <span class="info-box-number">{{ $overtimeStats['regular_hours'] }} hours</span>
                                    <div class="progress">
                                        <div class="progress-bar bg-light" style="width: 100%"></div>
                                    </div>
                                    <span class="progress-description">
                                        weekday overtime
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="info-box bg-warning">
                                <span class="info-box-icon">
                                    <i class="fas fa-calendar-week"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Weekend Overtime</span>
                                    <span class="info-box-number">{{ $overtimeStats['weekend_hours'] }} hours</span>
                                    <div class="progress">
                                        <div class="progress-bar bg-light" style="width: 100%"></div>
                                    </div>
                                    <span class="progress-description">
                                        weekend work
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="info-box bg-danger">
                                <span class="info-box-icon">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Emergency Overtime</span>
                                    <span class="info-box-number">{{ $overtimeStats['emergency_hours'] }} hours</span>
                                    <div class="progress">
                                        <div class="progress-bar bg-light" style="width: 100%"></div>
                                    </div>
                                    <span class="progress-description">
                                        urgent tasks
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Overtime Policy Card -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-info-circle mr-2"></i>
                        Overtime Policy
                    </h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success mr-2"></i> Regular Overtime: 1.5x hourly rate</li>
                        <li><i class="fas fa-check text-success mr-2"></i> Holiday Overtime: 2x hourly rate</li>
                        <li><i class="fas fa-check text-success mr-2"></i> Weekend Overtime: 2x hourly rate</li>
                        <li><i class="fas fa-check text-success mr-2"></i> Emergency Overtime: 2.5x hourly rate</li>
                    </ul>
                    <hr>
                    <small class="text-muted">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        Maximum 8 hours overtime per day. Requests must be submitted at least 1 day in advance.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Calculate total hours when times change
    function calculateHours() {
        const startTime = $('#start_time').val();
        const endTime = $('#end_time').val();
        
        if (startTime && endTime) {
            const start = new Date('2000-01-01 ' + startTime);
            const end = new Date('2000-01-01 ' + endTime);
            
            if (end < start) {
                end.setDate(end.getDate() + 1); // Add one day if end time is before start time
            }
            
            const diffTime = Math.abs(end - start);
            const diffHours = Math.ceil(diffTime / (1000 * 60 * 60));
            
            // Show total hours info
            if (diffHours > 0) {
                $('.form-group').each(function() {
                    if ($(this).find('label').text().includes('End Time')) {
                        if ($(this).find('.hours-info').length === 0) {
                            $(this).append('<small class="form-text text-info hours-info"><i class="fas fa-clock mr-1"></i> Total: ' + diffHours + ' hour(s)</small>');
                        } else {
                            $(this).find('.hours-info').html('<i class="fas fa-clock mr-1"></i> Total: ' + diffHours + ' hour(s)');
                        }
                    }
                });
            }
        }
    }
    
    $('#start_time, #end_time').change(calculateHours);
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