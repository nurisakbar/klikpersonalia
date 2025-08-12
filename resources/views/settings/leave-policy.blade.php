@extends('layouts.app')

@section('title', 'Leave Policy Settings')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-calendar-times mr-2"></i>
                        Leave Policy Settings
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('settings.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left mr-1"></i> Back to Settings
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('settings.update-leave-policy') }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <!-- Leave Quotas Configuration -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card card-primary card-outline">
                                    <div class="card-header">
                                        <h5 class="card-title">
                                            <i class="fas fa-calendar-check mr-2"></i>
                                            Leave Quotas Configuration
                                        </h5>
                                        <small class="text-muted">Set annual leave quotas for different leave types</small>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="annual_leave_quota" class="form-label">Annual Leave Quota *</label>
                                                    <div class="input-group">
                                                        <input type="number" 
                                                               class="form-control @error('annual_leave_quota') is-invalid @enderror" 
                                                               id="annual_leave_quota" 
                                                               name="annual_leave_quota" 
                                                               value="{{ old('annual_leave_quota', $company->leave_settings['quotas']['annual'] ?? 12) }}" 
                                                               min="0" 
                                                               max="365" 
                                                               required>
                                                        <div class="input-group-append">
                                                            <span class="input-group-text">days</span>
                                                        </div>
                                                    </div>
                                                    <small class="form-text text-muted">Annual leave days per year (e.g., 12 days)</small>
                                                    @error('annual_leave_quota')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="sick_leave_quota" class="form-label">Sick Leave Quota *</label>
                                                    <div class="input-group">
                                                        <input type="number" 
                                                               class="form-control @error('sick_leave_quota') is-invalid @enderror" 
                                                               id="sick_leave_quota" 
                                                               name="sick_leave_quota" 
                                                               value="{{ old('sick_leave_quota', $company->leave_settings['quotas']['sick'] ?? 12) }}" 
                                                               min="0" 
                                                               max="365" 
                                                               required>
                                                        <div class="input-group-append">
                                                            <span class="input-group-text">days</span>
                                                        </div>
                                                    </div>
                                                    <small class="form-text text-muted">Sick leave days per year (e.g., 12 days)</small>
                                                    @error('sick_leave_quota')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="other_leave_quota" class="form-label">Other Leave Quota *</label>
                                                    <div class="input-group">
                                                        <input type="number" 
                                                               class="form-control @error('other_leave_quota') is-invalid @enderror" 
                                                               id="other_leave_quota" 
                                                               name="other_leave_quota" 
                                                               value="{{ old('other_leave_quota', $company->leave_settings['quotas']['other'] ?? 6) }}" 
                                                               min="0" 
                                                               max="365" 
                                                               required>
                                                        <div class="input-group-append">
                                                            <span class="input-group-text">days</span>
                                                        </div>
                                                    </div>
                                                    <small class="form-text text-muted">Other leave days per year (e.g., 6 days)</small>
                                                    @error('other_leave_quota')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="maternity_leave_quota" class="form-label">Maternity Leave Quota *</label>
                                                    <div class="input-group">
                                                        <input type="number" 
                                                               class="form-control @error('maternity_leave_quota') is-invalid @enderror" 
                                                               id="maternity_leave_quota" 
                                                               name="maternity_leave_quota" 
                                                               value="{{ old('maternity_leave_quota', $company->leave_settings['quotas']['maternity'] ?? 90) }}" 
                                                               min="0" 
                                                               max="365" 
                                                               required>
                                                        <div class="input-group-append">
                                                            <span class="input-group-text">days</span>
                                                        </div>
                                                    </div>
                                                    <small class="form-text text-muted">Maternity leave days (e.g., 90 days)</small>
                                                    @error('maternity_leave_quota')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="paternity_leave_quota" class="form-label">Paternity Leave Quota *</label>
                                                    <div class="input-group">
                                                        <input type="number" 
                                                               class="form-control @error('paternity_leave_quota') is-invalid @enderror" 
                                                               id="paternity_leave_quota" 
                                                               name="paternity_leave_quota" 
                                                               value="{{ old('paternity_leave_quota', $company->leave_settings['quotas']['paternity'] ?? 3) }}" 
                                                               min="0" 
                                                               max="365" 
                                                               required>
                                                        <div class="input-group-append">
                                                            <span class="input-group-text">days</span>
                                                        </div>
                                                    </div>
                                                    <small class="form-text text-muted">Paternity leave days (e.g., 3 days)</small>
                                                    @error('paternity_leave_quota')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Leave Approval Configuration -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card card-success card-outline">
                                    <div class="card-header">
                                        <h5 class="card-title">
                                            <i class="fas fa-user-check mr-2"></i>
                                            Leave Approval Configuration
                                        </h5>
                                        <small class="text-muted">Configure leave approval workflow</small>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" 
                                                               class="custom-control-input" 
                                                               id="leave_approval_required" 
                                                               name="leave_approval_required" 
                                                               {{ old('leave_approval_required', $company->leave_settings['approval_required'] ?? true) ? 'checked' : '' }}>
                                                        <label class="custom-control-label" for="leave_approval_required">
                                                            Require Leave Approval
                                                        </label>
                                                    </div>
                                                    <small class="form-text text-muted">All leave requests require manager approval</small>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="leave_notice_days" class="form-label">Advance Notice Required *</label>
                                                    <div class="input-group">
                                                        <input type="number" 
                                                               class="form-control @error('leave_notice_days') is-invalid @enderror" 
                                                               id="leave_notice_days" 
                                                               name="leave_notice_days" 
                                                               value="{{ old('leave_notice_days', $company->leave_settings['notice_days'] ?? 3) }}" 
                                                               min="0" 
                                                               max="30" 
                                                               required>
                                                        <div class="input-group-append">
                                                            <span class="input-group-text">days</span>
                                                        </div>
                                                    </div>
                                                    <small class="form-text text-muted">Minimum days notice for leave requests</small>
                                                    @error('leave_notice_days')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Leave Carry Forward Configuration -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card card-info card-outline">
                                    <div class="card-header">
                                        <h5 class="card-title">
                                            <i class="fas fa-forward mr-2"></i>
                                            Leave Carry Forward Configuration
                                        </h5>
                                        <small class="text-muted">Configure unused leave carry forward policy</small>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" 
                                                               class="custom-control-input" 
                                                               id="leave_carry_forward" 
                                                               name="leave_carry_forward" 
                                                               {{ old('leave_carry_forward', $company->leave_settings['carry_forward']['enabled'] ?? true) ? 'checked' : '' }}>
                                                        <label class="custom-control-label" for="leave_carry_forward">
                                                            Enable Leave Carry Forward
                                                        </label>
                                                    </div>
                                                    <small class="form-text text-muted">Allow unused leave to carry forward to next year</small>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="leave_carry_forward_limit" class="form-label">Carry Forward Limit *</label>
                                                    <div class="input-group">
                                                        <input type="number" 
                                                               class="form-control @error('leave_carry_forward_limit') is-invalid @enderror" 
                                                               id="leave_carry_forward_limit" 
                                                               name="leave_carry_forward_limit" 
                                                               value="{{ old('leave_carry_forward_limit', $company->leave_settings['carry_forward']['limit'] ?? 6) }}" 
                                                               min="0" 
                                                               max="365" 
                                                               required>
                                                        <div class="input-group-append">
                                                            <span class="input-group-text">days</span>
                                                        </div>
                                                    </div>
                                                    <small class="form-text text-muted">Maximum days that can be carried forward</small>
                                                    @error('leave_carry_forward_limit')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Leave Policy Summary -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card card-secondary card-outline">
                                    <div class="card-header">
                                        <h5 class="card-title">
                                            <i class="fas fa-info-circle mr-2"></i>
                                            Leave Policy Summary
                                        </h5>
                                        <small class="text-muted">Current leave policy configuration</small>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h6>Leave Quotas</h6>
                                                <table class="table table-sm">
                                                    <tr>
                                                        <td><strong>Annual Leave:</strong></td>
                                                        <td>{{ $company->leave_settings['quotas']['annual'] ?? 12 }} days</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Sick Leave:</strong></td>
                                                        <td>{{ $company->leave_settings['quotas']['sick'] ?? 12 }} days</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Other Leave:</strong></td>
                                                        <td>{{ $company->leave_settings['quotas']['other'] ?? 6 }} days</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Maternity Leave:</strong></td>
                                                        <td>{{ $company->leave_settings['quotas']['maternity'] ?? 90 }} days</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Paternity Leave:</strong></td>
                                                        <td>{{ $company->leave_settings['quotas']['paternity'] ?? 3 }} days</td>
                                                    </tr>
                                                </table>
                                            </div>
                                            <div class="col-md-6">
                                                <h6>Approval & Carry Forward</h6>
                                                <table class="table table-sm">
                                                    <tr>
                                                        <td><strong>Approval Required:</strong></td>
                                                        <td>
                                                            <span class="badge badge-{{ $company->leave_settings['approval_required'] ?? true ? 'success' : 'danger' }}">
                                                                {{ $company->leave_settings['approval_required'] ?? true ? 'Yes' : 'No' }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Advance Notice:</strong></td>
                                                        <td>{{ $company->leave_settings['notice_days'] ?? 3 }} days</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Carry Forward:</strong></td>
                                                        <td>
                                                            <span class="badge badge-{{ $company->leave_settings['carry_forward']['enabled'] ?? true ? 'success' : 'danger' }}">
                                                                {{ $company->leave_settings['carry_forward']['enabled'] ?? true ? 'Enabled' : 'Disabled' }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Carry Forward Limit:</strong></td>
                                                        <td>{{ $company->leave_settings['carry_forward']['limit'] ?? 6 }} days</td>
                                                    </tr>
                                                </table>
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
                                        <i class="fas fa-save mr-1"></i> Update Leave Policy
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
// Real-time summary update
function updateSummary() {
    const annualQuota = parseInt(document.getElementById('annual_leave_quota').value) || 12;
    const sickQuota = parseInt(document.getElementById('sick_leave_quota').value) || 12;
    const otherQuota = parseInt(document.getElementById('other_leave_quota').value) || 6;
    const maternityQuota = parseInt(document.getElementById('maternity_leave_quota').value) || 90;
    const paternityQuota = parseInt(document.getElementById('paternity_leave_quota').value) || 3;
    const noticeDays = parseInt(document.getElementById('leave_notice_days').value) || 3;
    const carryForwardLimit = parseInt(document.getElementById('leave_carry_forward_limit').value) || 6;
    
    // Update summary tables
    const quotaTable = document.querySelector('.col-md-6:first-child table');
    if (quotaTable) {
        quotaTable.innerHTML = `
            <tr><td><strong>Annual Leave:</strong></td><td>${annualQuota} days</td></tr>
            <tr><td><strong>Sick Leave:</strong></td><td>${sickQuota} days</td></tr>
            <tr><td><strong>Other Leave:</strong></td><td>${otherQuota} days</td></tr>
            <tr><td><strong>Maternity Leave:</strong></td><td>${maternityQuota} days</td></tr>
            <tr><td><strong>Paternity Leave:</strong></td><td>${paternityQuota} days</td></tr>
        `;
    }
    
    const approvalTable = document.querySelector('.col-md-6:last-child table');
    if (approvalTable) {
        const approvalRequired = document.getElementById('leave_approval_required').checked;
        const carryForward = document.getElementById('leave_carry_forward').checked;
        
        approvalTable.innerHTML = `
            <tr><td><strong>Approval Required:</strong></td><td><span class="badge badge-${approvalRequired ? 'success' : 'danger'}">${approvalRequired ? 'Yes' : 'No'}</span></td></tr>
            <tr><td><strong>Advance Notice:</strong></td><td>${noticeDays} days</td></tr>
            <tr><td><strong>Carry Forward:</strong></td><td><span class="badge badge-${carryForward ? 'success' : 'danger'}">${carryForward ? 'Enabled' : 'Disabled'}</span></td></tr>
            <tr><td><strong>Carry Forward Limit:</strong></td><td>${carryForwardLimit} days</td></tr>
        `;
    }
}

// Add event listeners for real-time updates
document.addEventListener('DOMContentLoaded', function() {
    const inputs = [
        'annual_leave_quota', 'sick_leave_quota', 'other_leave_quota',
        'maternity_leave_quota', 'paternity_leave_quota',
        'leave_notice_days', 'leave_carry_forward_limit'
    ];
    
    inputs.forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.addEventListener('input', updateSummary);
        }
    });
    
    const checkboxes = ['leave_approval_required', 'leave_carry_forward'];
    checkboxes.forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.addEventListener('change', updateSummary);
        }
    });
    
    updateSummary();
});
</script>
@endpush 