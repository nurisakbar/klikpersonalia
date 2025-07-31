@extends('layouts.app')

@section('title', 'Settings & Configuration')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-cogs mr-2"></i>
                        Settings & Configuration
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Company Settings -->
                        <div class="col-lg-4 col-md-6">
                            <div class="card card-primary card-outline">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-building mr-2"></i>
                                        Company Settings
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p class="card-text">Manage company profile, logo, and basic information.</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge badge-primary">Admin Only</span>
                                        <a href="{{ route('settings.company') }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-edit mr-1"></i> Configure
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payroll Policy -->
                        <div class="col-lg-4 col-md-6">
                            <div class="card card-success card-outline">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-money-bill-wave mr-2"></i>
                                        Payroll Policy
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p class="card-text">Configure overtime rates, attendance bonus, and payroll rules.</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge badge-success">Admin/HR</span>
                                        <a href="{{ route('settings.payroll-policy') }}" class="btn btn-success btn-sm">
                                            <i class="fas fa-edit mr-1"></i> Configure
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Leave Policy -->
                        <div class="col-lg-4 col-md-6">
                            <div class="card card-info card-outline">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-calendar-times mr-2"></i>
                                        Leave Policy
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p class="card-text">Set leave quotas, approval rules, and carry forward policies.</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge badge-info">Admin/HR</span>
                                        <a href="{{ route('settings.leave-policy') }}" class="btn btn-info btn-sm">
                                            <i class="fas fa-edit mr-1"></i> Configure
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- User Profile -->
                        <div class="col-lg-4 col-md-6">
                            <div class="card card-warning card-outline">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-user mr-2"></i>
                                        User Profile
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p class="card-text">Update your personal information and profile picture.</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge badge-warning">All Users</span>
                                        <a href="{{ route('settings.profile') }}" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit mr-1"></i> Configure
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Password -->
                        <div class="col-lg-4 col-md-6">
                            <div class="card card-danger card-outline">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-lock mr-2"></i>
                                        Password
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p class="card-text">Change your account password securely.</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge badge-danger">All Users</span>
                                        <a href="{{ route('settings.password') }}" class="btn btn-danger btn-sm">
                                            <i class="fas fa-edit mr-1"></i> Configure
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- User Management -->
                        <div class="col-lg-4 col-md-6">
                            <div class="card card-secondary card-outline">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-users mr-2"></i>
                                        User Management
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p class="card-text">Manage users, roles, and permissions for your company.</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge badge-secondary">Admin Only</span>
                                        <a href="{{ route('settings.users') }}" class="btn btn-secondary btn-sm">
                                            <i class="fas fa-edit mr-1"></i> Configure
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- System Settings -->
                        <div class="col-lg-4 col-md-6">
                            <div class="card card-dark card-outline">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-server mr-2"></i>
                                        System Settings
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p class="card-text">Configure system-wide settings like timezone and currency.</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge badge-dark">Admin Only</span>
                                        <a href="{{ route('settings.system') }}" class="btn btn-dark btn-sm">
                                            <i class="fas fa-edit mr-1"></i> Configure
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Backup & Restore -->
                        <div class="col-lg-4 col-md-6">
                            <div class="card card-light card-outline">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-database mr-2"></i>
                                        Backup & Restore
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p class="card-text">Create database backups and manage data recovery.</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge badge-light text-dark">Admin Only</span>
                                        <a href="{{ route('settings.backup') }}" class="btn btn-light btn-sm text-dark">
                                            <i class="fas fa-edit mr-1"></i> Configure
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Current Settings Summary -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-info-circle mr-2"></i>
                                        Current Settings Summary
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6>Company Information</h6>
                                            <table class="table table-sm">
                                                <tr>
                                                    <td><strong>Company Name:</strong></td>
                                                    <td>{{ $company->name }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Email:</strong></td>
                                                    <td>{{ $company->email }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Phone:</strong></td>
                                                    <td>{{ $company->phone }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Address:</strong></td>
                                                    <td>{{ $company->address }}, {{ $company->city }}</td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <h6>System Information</h6>
                                            <table class="table table-sm">
                                                <tr>
                                                    <td><strong>Subscription Plan:</strong></td>
                                                    <td><span class="badge badge-{{ $company->subscription_plan === 'premium' ? 'success' : 'warning' }}">{{ ucfirst($company->subscription_plan) }}</span></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Max Employees:</strong></td>
                                                    <td>{{ $company->max_employees }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Status:</strong></td>
                                                    <td><span class="badge badge-{{ $company->status === 'active' ? 'success' : 'danger' }}">{{ ucfirst($company->status) }}</span></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Created:</strong></td>
                                                    <td>{{ $company->created_at->format('d/m/Y H:i') }}</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-bolt mr-2"></i>
                                        Quick Actions
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('settings.company') }}" class="btn btn-outline-primary">
                                            <i class="fas fa-building mr-1"></i> Update Company Info
                                        </a>
                                        <a href="{{ route('settings.profile') }}" class="btn btn-outline-warning">
                                            <i class="fas fa-user mr-1"></i> Update Profile
                                        </a>
                                        <a href="{{ route('settings.password') }}" class="btn btn-outline-danger">
                                            <i class="fas fa-lock mr-1"></i> Change Password
                                        </a>
                                        @if(in_array(auth()->user()->role, ['admin']))
                                        <a href="{{ route('settings.users') }}" class="btn btn-outline-secondary">
                                            <i class="fas fa-users mr-1"></i> Manage Users
                                        </a>
                                        @endif
                                    </div>
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

.card-outline.card-danger {
    border-top-color: #dc3545;
}

.card-outline.card-secondary {
    border-top-color: #6c757d;
}

.card-outline.card-dark {
    border-top-color: #343a40;
}

.card-outline.card-light {
    border-top-color: #f8f9fa;
}

.card {
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
</style>
@endpush 