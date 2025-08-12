@extends('layouts.app')

@section('title', 'Change Password')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-lock mr-2"></i>
                        Change Password
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('settings.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left mr-1"></i> Back to Settings
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('settings.update-password') }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row justify-content-center">
                            <div class="col-md-8">
                                <!-- Password Security Info -->
                                <div class="alert alert-info">
                                    <h5><i class="fas fa-shield-alt mr-2"></i>Password Security Guidelines</h5>
                                    <ul class="mb-0">
                                        <li>Use at least 8 characters</li>
                                        <li>Include uppercase and lowercase letters</li>
                                        <li>Include numbers and special characters</li>
                                        <li>Avoid common words or personal information</li>
                                        <li>Don't reuse passwords from other accounts</li>
                                    </ul>
                                </div>

                                <!-- Current Password -->
                                <div class="form-group">
                                    <label for="current_password" class="form-label">Current Password *</label>
                                    <div class="input-group">
                                        <input type="password" 
                                               class="form-control @error('current_password') is-invalid @enderror" 
                                               id="current_password" 
                                               name="current_password" 
                                               required>
                                        <div class="input-group-append">
                                            <button type="button" 
                                                    class="btn btn-outline-secondary" 
                                                    onclick="togglePassword('current_password')">
                                                <i class="fas fa-eye" id="current_password_icon"></i>
                                            </button>
                                        </div>
                                    </div>
                                    @error('current_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- New Password -->
                                <div class="form-group">
                                    <label for="password" class="form-label">New Password *</label>
                                    <div class="input-group">
                                        <input type="password" 
                                               class="form-control @error('password') is-invalid @enderror" 
                                               id="password" 
                                               name="password" 
                                               required>
                                        <div class="input-group-append">
                                            <button type="button" 
                                                    class="btn btn-outline-secondary" 
                                                    onclick="togglePassword('password')">
                                                <i class="fas fa-eye" id="password_icon"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="password-strength mt-2">
                                        <div class="progress" style="height: 5px;">
                                            <div class="progress-bar" id="password_strength_bar" role="progressbar" style="width: 0%"></div>
                                        </div>
                                        <small class="text-muted" id="password_strength_text">Password strength: Very Weak</small>
                                    </div>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Confirm New Password -->
                                <div class="form-group">
                                    <label for="password_confirmation" class="form-label">Confirm New Password *</label>
                                    <div class="input-group">
                                        <input type="password" 
                                               class="form-control @error('password_confirmation') is-invalid @enderror" 
                                               id="password_confirmation" 
                                               name="password_confirmation" 
                                               required>
                                        <div class="input-group-append">
                                            <button type="button" 
                                                    class="btn btn-outline-secondary" 
                                                    onclick="togglePassword('password_confirmation')">
                                                <i class="fas fa-eye" id="password_confirmation_icon"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="password-match mt-2">
                                        <small class="text-muted" id="password_match_text">Passwords do not match</small>
                                    </div>
                                    @error('password_confirmation')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Password Requirements Checklist -->
                                <div class="card mt-4">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">
                                            <i class="fas fa-check-circle mr-2"></i>
                                            Password Requirements
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="requirement-item" id="req_length">
                                                    <i class="fas fa-times text-danger"></i>
                                                    <span>At least 8 characters</span>
                                                </div>
                                                <div class="requirement-item" id="req_uppercase">
                                                    <i class="fas fa-times text-danger"></i>
                                                    <span>One uppercase letter</span>
                                                </div>
                                                <div class="requirement-item" id="req_lowercase">
                                                    <i class="fas fa-times text-danger"></i>
                                                    <span>One lowercase letter</span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="requirement-item" id="req_number">
                                                    <i class="fas fa-times text-danger"></i>
                                                    <span>One number</span>
                                                </div>
                                                <div class="requirement-item" id="req_special">
                                                    <i class="fas fa-times text-danger"></i>
                                                    <span>One special character</span>
                                                </div>
                                                <div class="requirement-item" id="req_match">
                                                    <i class="fas fa-times text-danger"></i>
                                                    <span>Passwords match</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Security Tips -->
                                <div class="alert alert-warning mt-4">
                                    <h6><i class="fas fa-exclamation-triangle mr-2"></i>Security Tips</h6>
                                    <ul class="mb-0">
                                        <li>Never share your password with anyone</li>
                                        <li>Log out from all devices after changing password</li>
                                        <li>Consider using a password manager</li>
                                        <li>Enable two-factor authentication if available</li>
                                    </ul>
                                </div>

                                <div class="d-flex justify-content-between mt-4">
                                    <a href="{{ route('settings.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-times mr-1"></i> Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary" id="submit_btn" disabled>
                                        <i class="fas fa-key mr-1"></i> Change Password
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
.requirement-item {
    margin-bottom: 8px;
    font-size: 14px;
}

.requirement-item i {
    margin-right: 8px;
    width: 16px;
}

.requirement-item.valid i {
    color: #28a745 !important;
}

.password-strength .progress-bar {
    transition: width 0.3s ease;
}

.password-match .text-success {
    color: #28a745 !important;
}

.password-match .text-danger {
    color: #dc3545 !important;
}
</style>
@endpush

@push('scripts')
<script>
// Toggle password visibility
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '_icon');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Password strength checker
function checkPasswordStrength(password) {
    let strength = 0;
    let requirements = {
        length: password.length >= 8,
        uppercase: /[A-Z]/.test(password),
        lowercase: /[a-z]/.test(password),
        number: /[0-9]/.test(password),
        special: /[^A-Za-z0-9]/.test(password)
    };
    
    // Calculate strength score
    Object.values(requirements).forEach(met => {
        if (met) strength += 20;
    });
    
    return { strength, requirements };
}

// Update password requirements display
function updateRequirements(requirements) {
    const reqIds = ['length', 'uppercase', 'lowercase', 'number', 'special'];
    
    reqIds.forEach(req => {
        const element = document.getElementById('req_' + req);
        const icon = element.querySelector('i');
        
        if (requirements[req]) {
            element.classList.add('valid');
            icon.classList.remove('fa-times', 'text-danger');
            icon.classList.add('fa-check', 'text-success');
        } else {
            element.classList.remove('valid');
            icon.classList.remove('fa-check', 'text-success');
            icon.classList.add('fa-times', 'text-danger');
        }
    });
}

// Update password strength bar
function updateStrengthBar(strength) {
    const bar = document.getElementById('password_strength_bar');
    const text = document.getElementById('password_strength_text');
    
    bar.style.width = strength + '%';
    
    if (strength <= 20) {
        bar.className = 'progress-bar bg-danger';
        text.textContent = 'Password strength: Very Weak';
    } else if (strength <= 40) {
        bar.className = 'progress-bar bg-warning';
        text.textContent = 'Password strength: Weak';
    } else if (strength <= 60) {
        bar.className = 'progress-bar bg-info';
        text.textContent = 'Password strength: Fair';
    } else if (strength <= 80) {
        bar.className = 'progress-bar bg-primary';
        text.textContent = 'Password strength: Good';
    } else {
        bar.className = 'progress-bar bg-success';
        text.textContent = 'Password strength: Strong';
    }
}

// Check if passwords match
function checkPasswordMatch() {
    const password = document.getElementById('password').value;
    const confirmation = document.getElementById('password_confirmation').value;
    const matchText = document.getElementById('password_match_text');
    const matchReq = document.getElementById('req_match');
    const matchIcon = matchReq.querySelector('i');
    
    if (confirmation === '') {
        matchText.textContent = 'Passwords do not match';
        matchText.className = 'text-muted';
        matchReq.classList.remove('valid');
        matchIcon.classList.remove('fa-check', 'text-success');
        matchIcon.classList.add('fa-times', 'text-danger');
        return false;
    } else if (password === confirmation) {
        matchText.textContent = 'Passwords match';
        matchText.className = 'text-success';
        matchReq.classList.add('valid');
        matchIcon.classList.remove('fa-times', 'text-danger');
        matchIcon.classList.add('fa-check', 'text-success');
        return true;
    } else {
        matchText.textContent = 'Passwords do not match';
        matchText.className = 'text-danger';
        matchReq.classList.remove('valid');
        matchIcon.classList.remove('fa-check', 'text-success');
        matchIcon.classList.add('fa-times', 'text-danger');
        return false;
    }
}

// Enable/disable submit button
function updateSubmitButton() {
    const password = document.getElementById('password').value;
    const confirmation = document.getElementById('password_confirmation').value;
    const currentPassword = document.getElementById('current_password').value;
    const submitBtn = document.getElementById('submit_btn');
    
    const { requirements } = checkPasswordStrength(password);
    const allRequirementsMet = Object.values(requirements).every(met => met);
    const passwordsMatch = password === confirmation && confirmation !== '';
    const hasCurrentPassword = currentPassword !== '';
    
    submitBtn.disabled = !(allRequirementsMet && passwordsMatch && hasCurrentPassword);
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    const passwordField = document.getElementById('password');
    const confirmationField = document.getElementById('password_confirmation');
    const currentPasswordField = document.getElementById('current_password');
    
    passwordField.addEventListener('input', function() {
        const { strength, requirements } = checkPasswordStrength(this.value);
        updateStrengthBar(strength);
        updateRequirements(requirements);
        updateSubmitButton();
    });
    
    confirmationField.addEventListener('input', function() {
        checkPasswordMatch();
        updateSubmitButton();
    });
    
    currentPasswordField.addEventListener('input', updateSubmitButton);
});
</script>
@endpush 