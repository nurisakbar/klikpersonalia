@extends('layouts.app')

@section('title', 'Payroll Policy Settings')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-money-bill-wave mr-2"></i>
                        Payroll Policy Settings
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('settings.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left mr-1"></i> Back to Settings
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('settings.update-payroll-policy') }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <!-- Working Hours Configuration -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card card-primary card-outline">
                                    <div class="card-header">
                                        <h5 class="card-title">
                                            <i class="fas fa-clock mr-2"></i>
                                            Working Hours Configuration
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="working_hours_per_day" class="form-label">Working Hours per Day *</label>
                                                    <input type="number" 
                                                           class="form-control @error('working_hours_per_day') is-invalid @enderror" 
                                                           id="working_hours_per_day" 
                                                           name="working_hours_per_day" 
                                                           value="{{ old('working_hours_per_day', $company->payroll_settings['working_hours_per_day'] ?? 8) }}" 
                                                           min="1" 
                                                           max="24" 
                                                           step="0.5" 
                                                           required>
                                                    <small class="form-text text-muted">Standard working hours per day (e.g., 8 hours)</small>
                                                    @error('working_hours_per_day')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="working_days_per_week" class="form-label">Working Days per Week *</label>
                                                    <input type="number" 
                                                           class="form-control @error('working_days_per_week') is-invalid @enderror" 
                                                           id="working_days_per_week" 
                                                           name="working_days_per_week" 
                                                           value="{{ old('working_days_per_week', $company->payroll_settings['working_days_per_week'] ?? 5) }}" 
                                                           min="1" 
                                                           max="7" 
                                                           required>
                                                    <small class="form-text text-muted">Standard working days per week (e.g., 5 days)</small>
                                                    @error('working_days_per_week')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Overtime Rates Configuration -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card card-success card-outline">
                                    <div class="card-header">
                                        <h5 class="card-title">
                                            <i class="fas fa-plus-circle mr-2"></i>
                                            Overtime Rates Configuration
                                        </h5>
                                        <small class="text-muted">Multiplier rates for different overtime types</small>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="overtime_regular_rate" class="form-label">Regular Overtime Rate *</label>
                                                    <div class="input-group">
                                                        <input type="number" 
                                                               class="form-control @error('overtime_regular_rate') is-invalid @enderror" 
                                                               id="overtime_regular_rate" 
                                                               name="overtime_regular_rate" 
                                                               value="{{ old('overtime_regular_rate', $company->payroll_settings['overtime_rates']['regular'] ?? 1.5) }}" 
                                                               min="1" 
                                                               max="5" 
                                                               step="0.1" 
                                                               required>
                                                        <div class="input-group-append">
                                                            <span class="input-group-text">x</span>
                                                        </div>
                                                    </div>
                                                    <small class="form-text text-muted">Regular overtime (e.g., 1.5x)</small>
                                                    @error('overtime_regular_rate')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="overtime_holiday_rate" class="form-label">Holiday Overtime Rate *</label>
                                                    <div class="input-group">
                                                        <input type="number" 
                                                               class="form-control @error('overtime_holiday_rate') is-invalid @enderror" 
                                                               id="overtime_holiday_rate" 
                                                               name="overtime_holiday_rate" 
                                                               value="{{ old('overtime_holiday_rate', $company->payroll_settings['overtime_rates']['holiday'] ?? 2.0) }}" 
                                                               min="1" 
                                                               max="5" 
                                                               step="0.1" 
                                                               required>
                                                        <div class="input-group-append">
                                                            <span class="input-group-text">x</span>
                                                        </div>
                                                    </div>
                                                    <small class="form-text text-muted">Holiday overtime (e.g., 2.0x)</small>
                                                    @error('overtime_holiday_rate')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="overtime_weekend_rate" class="form-label">Weekend Overtime Rate *</label>
                                                    <div class="input-group">
                                                        <input type="number" 
                                                               class="form-control @error('overtime_weekend_rate') is-invalid @enderror" 
                                                               id="overtime_weekend_rate" 
                                                               name="overtime_weekend_rate" 
                                                               value="{{ old('overtime_weekend_rate', $company->payroll_settings['overtime_rates']['weekend'] ?? 2.0) }}" 
                                                               min="1" 
                                                               max="5" 
                                                               step="0.1" 
                                                               required>
                                                        <div class="input-group-append">
                                                            <span class="input-group-text">x</span>
                                                        </div>
                                                    </div>
                                                    <small class="form-text text-muted">Weekend overtime (e.g., 2.0x)</small>
                                                    @error('overtime_weekend_rate')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="overtime_emergency_rate" class="form-label">Emergency Overtime Rate *</label>
                                                    <div class="input-group">
                                                        <input type="number" 
                                                               class="form-control @error('overtime_emergency_rate') is-invalid @enderror" 
                                                               id="overtime_emergency_rate" 
                                                               name="overtime_emergency_rate" 
                                                               value="{{ old('overtime_emergency_rate', $company->payroll_settings['overtime_rates']['emergency'] ?? 3.0) }}" 
                                                               min="1" 
                                                               max="5" 
                                                               step="0.1" 
                                                               required>
                                                        <div class="input-group-append">
                                                            <span class="input-group-text">x</span>
                                                        </div>
                                                    </div>
                                                    <small class="form-text text-muted">Emergency overtime (e.g., 3.0x)</small>
                                                    @error('overtime_emergency_rate')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Attendance Bonus Configuration -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card card-info card-outline">
                                    <div class="card-header">
                                        <h5 class="card-title">
                                            <i class="fas fa-star mr-2"></i>
                                            Attendance Bonus Configuration
                                        </h5>
                                        <small class="text-muted">Bonus rates for good attendance</small>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="attendance_bonus_95_rate" class="form-label">95%+ Attendance Bonus Rate *</label>
                                                    <div class="input-group">
                                                        <input type="number" 
                                                               class="form-control @error('attendance_bonus_95_rate') is-invalid @enderror" 
                                                               id="attendance_bonus_95_rate" 
                                                               name="attendance_bonus_95_rate" 
                                                               value="{{ old('attendance_bonus_95_rate', $company->payroll_settings['attendance_bonus']['95_percent_rate'] ?? 0.05) }}" 
                                                               min="0" 
                                                               max="1" 
                                                               step="0.01" 
                                                               required>
                                                        <div class="input-group-append">
                                                            <span class="input-group-text">%</span>
                                                        </div>
                                                    </div>
                                                    <small class="form-text text-muted">Bonus rate for 95%+ attendance (e.g., 0.05 = 5%)</small>
                                                    @error('attendance_bonus_95_rate')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="attendance_bonus_90_rate" class="form-label">90%+ Attendance Bonus Rate *</label>
                                                    <div class="input-group">
                                                        <input type="number" 
                                                               class="form-control @error('attendance_bonus_90_rate') is-invalid @enderror" 
                                                               id="attendance_bonus_90_rate" 
                                                               name="attendance_bonus_90_rate" 
                                                               value="{{ old('attendance_bonus_90_rate', $company->payroll_settings['attendance_bonus']['90_percent_rate'] ?? 0.03) }}" 
                                                               min="0" 
                                                               max="1" 
                                                               step="0.01" 
                                                               required>
                                                        <div class="input-group-append">
                                                            <span class="input-group-text">%</span>
                                                        </div>
                                                    </div>
                                                    <small class="form-text text-muted">Bonus rate for 90%+ attendance (e.g., 0.03 = 3%)</small>
                                                    @error('attendance_bonus_90_rate')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Leave Deduction Configuration -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card card-warning card-outline">
                                    <div class="card-header">
                                        <h5 class="card-title">
                                            <i class="fas fa-minus-circle mr-2"></i>
                                            Leave Deduction Configuration
                                        </h5>
                                        <small class="text-muted">Configure leave-related deductions</small>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" 
                                                               class="custom-control-input" 
                                                               id="leave_deduction_enabled" 
                                                               name="leave_deduction_enabled" 
                                                               {{ old('leave_deduction_enabled', $company->payroll_settings['leave_deduction']['enabled'] ?? false) ? 'checked' : '' }}>
                                                        <label class="custom-control-label" for="leave_deduction_enabled">
                                                            Enable Leave Deduction
                                                        </label>
                                                    </div>
                                                    <small class="form-text text-muted">Deduct salary for excessive leave</small>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" 
                                                               class="custom-control-input" 
                                                               id="annual_leave_deduction" 
                                                               name="annual_leave_deduction" 
                                                               {{ old('annual_leave_deduction', $company->payroll_settings['leave_deduction']['annual_leave_deduction'] ?? false) ? 'checked' : '' }}>
                                                        <label class="custom-control-label" for="annual_leave_deduction">
                                                            Annual Leave Deduction
                                                        </label>
                                                    </div>
                                                    <small class="form-text text-muted">Deduct for unused annual leave</small>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="late_threshold_minutes" class="form-label">Late Threshold (Minutes) *</label>
                                                    <input type="number" 
                                                           class="form-control @error('late_threshold_minutes') is-invalid @enderror" 
                                                           id="late_threshold_minutes" 
                                                           name="late_threshold_minutes" 
                                                           value="{{ old('late_threshold_minutes', $company->payroll_settings['late_threshold_minutes'] ?? 15) }}" 
                                                           min="0" 
                                                           max="120" 
                                                           required>
                                                    <small class="form-text text-muted">Minutes before considered late</small>
                                                    @error('late_threshold_minutes')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payroll Schedule Configuration -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card card-secondary card-outline">
                                    <div class="card-header">
                                        <h5 class="card-title">
                                            <i class="fas fa-calendar-alt mr-2"></i>
                                            Payroll Schedule Configuration
                                        </h5>
                                        <small class="text-muted">Configure payroll processing schedule</small>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="payroll_day" class="form-label">Payroll Day of Month *</label>
                                                    <input type="number" 
                                                           class="form-control @error('payroll_day') is-invalid @enderror" 
                                                           id="payroll_day" 
                                                           name="payroll_day" 
                                                           value="{{ old('payroll_day', $company->payroll_settings['payroll_day'] ?? 25) }}" 
                                                           min="1" 
                                                           max="31" 
                                                           required>
                                                    <small class="form-text text-muted">Day of month for payroll processing (e.g., 25)</small>
                                                    @error('payroll_day')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-label">Current Settings Summary</label>
                                                    <div class="alert alert-info">
                                                        <strong>Working Hours:</strong> {{ $company->payroll_settings['working_hours_per_day'] ?? 8 }} hours/day, {{ $company->payroll_settings['working_days_per_week'] ?? 5 }} days/week<br>
                                                        <strong>Overtime Rates:</strong> Regular {{ $company->payroll_settings['overtime_rates']['regular'] ?? 1.5 }}x, Holiday {{ $company->payroll_settings['overtime_rates']['holiday'] ?? 2.0 }}x<br>
                                                        <strong>Attendance Bonus:</strong> 95%+ {{ ($company->payroll_settings['attendance_bonus']['95_percent_rate'] ?? 0.05) * 100 }}%, 90%+ {{ ($company->payroll_settings['attendance_bonus']['90_percent_rate'] ?? 0.03) * 100 }}%<br>
                                                        <strong>Payroll Day:</strong> {{ $company->payroll_settings['payroll_day'] ?? 25 }}th of month
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('settings.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-times mr-1"></i> Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save mr-1"></i> Update Payroll Policy
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.card-outline {
    border-top: 3px solid #007bff;
}

.card-outline.card-primary {
    border-top-color: #007bff;
}

.card-outline.card-success {
    border-top-color: #28a745;
}

.card-outline.card-info {
    border-top-color: #17a2b8;
}

.card-outline.card-warning {
    border-top-color: #ffc107;
}

.card-outline.card-secondary {
    border-top-color: #6c757d;
}

.input-group-text {
    background-color: #f8f9fa;
    border-color: #ced4da;
    color: #495057;
}
</style>
@endpush

@push('scripts')
<script>
// Real-time calculation preview
function updatePreview() {
    const workingHours = parseFloat(document.getElementById('working_hours_per_day').value) || 8;
    const workingDays = parseInt(document.getElementById('working_days_per_week').value) || 5;
    const regularRate = parseFloat(document.getElementById('overtime_regular_rate').value) || 1.5;
    const holidayRate = parseFloat(document.getElementById('overtime_holiday_rate').value) || 2.0;
    const weekendRate = parseFloat(document.getElementById('overtime_weekend_rate').value) || 2.0;
    const emergencyRate = parseFloat(document.getElementById('overtime_emergency_rate').value) || 3.0;
    const bonus95 = parseFloat(document.getElementById('attendance_bonus_95_rate').value) || 0.05;
    const bonus90 = parseFloat(document.getElementById('attendance_bonus_90_rate').value) || 0.03;
    
    // Update summary
    const summary = document.querySelector('.alert-info');
    if (summary) {
        summary.innerHTML = `
            <strong>Working Hours:</strong> ${workingHours} hours/day, ${workingDays} days/week<br>
            <strong>Overtime Rates:</strong> Regular ${regularRate}x, Holiday ${holidayRate}x<br>
            <strong>Attendance Bonus:</strong> 95%+ ${(bonus95 * 100).toFixed(0)}%, 90%+ ${(bonus90 * 100).toFixed(0)}%<br>
            <strong>Payroll Day:</strong> ${document.getElementById('payroll_day').value || 25}th of month
        `;
    }
}

// Add event listeners for real-time updates
document.addEventListener('DOMContentLoaded', function() {
    const inputs = [
        'working_hours_per_day', 'working_days_per_week',
        'overtime_regular_rate', 'overtime_holiday_rate', 'overtime_weekend_rate', 'overtime_emergency_rate',
        'attendance_bonus_95_rate', 'attendance_bonus_90_rate', 'payroll_day'
    ];
    
    inputs.forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.addEventListener('input', updatePreview);
        }
    });
    
    updatePreview();
});
</script>
@endpush 