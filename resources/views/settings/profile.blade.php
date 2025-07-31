@extends('layouts.app')

@section('title', 'Profile Settings')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user mr-2"></i>
                        Profile Settings
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('settings.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left mr-1"></i> Back to Settings
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('settings.update-profile') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <!-- Profile Picture -->
                            <div class="col-md-3">
                                <div class="text-center mb-4">
                                    <div class="profile-avatar-container">
                                        @if($user->avatar)
                                            <img src="{{ Storage::url($user->avatar) }}" 
                                                 alt="Profile Picture" 
                                                 class="img-fluid profile-avatar mb-3"
                                                 style="max-width: 200px; max-height: 200px;">
                                        @else
                                            <div class="profile-avatar-placeholder mb-3">
                                                <i class="fas fa-user fa-5x text-muted"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="form-group">
                                        <label for="avatar" class="form-label">Profile Picture</label>
                                        <input type="file" 
                                               class="form-control @error('avatar') is-invalid @enderror" 
                                               id="avatar" 
                                               name="avatar" 
                                               accept="image/*">
                                        <small class="form-text text-muted">
                                            Recommended size: 200x200px. Max size: 2MB.
                                        </small>
                                        @error('avatar')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Profile Information -->
                            <div class="col-md-9">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="name" class="form-label">Full Name *</label>
                                            <input type="text" 
                                                   class="form-control @error('name') is-invalid @enderror" 
                                                   id="name" 
                                                   name="name" 
                                                   value="{{ old('name', $user->name) }}" 
                                                   required>
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="email" class="form-label">Email Address *</label>
                                            <input type="email" 
                                                   class="form-control @error('email') is-invalid @enderror" 
                                                   id="email" 
                                                   name="email" 
                                                   value="{{ old('email', $user->email) }}" 
                                                   required>
                                            @error('email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="phone" class="form-label">Phone Number</label>
                                            <input type="text" 
                                                   class="form-control @error('phone') is-invalid @enderror" 
                                                   id="phone" 
                                                   name="phone" 
                                                   value="{{ old('phone', $user->phone) }}">
                                            @error('phone')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Role</label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   value="{{ ucfirst($user->role) }}" 
                                                   readonly>
                                            <small class="form-text text-muted">Role cannot be changed from profile settings</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Company</label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   value="{{ $user->company->name ?? 'N/A' }}" 
                                                   readonly>
                                            <small class="form-text text-muted">Company cannot be changed from profile settings</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Member Since</label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   value="{{ $user->created_at->format('d/m/Y H:i') }}" 
                                                   readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Account Information Summary -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">
                                            <i class="fas fa-info-circle mr-2"></i>
                                            Account Information Summary
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <table class="table table-sm">
                                                    <tr>
                                                        <td><strong>User ID:</strong></td>
                                                        <td>{{ $user->id }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Email Verified:</strong></td>
                                                        <td>
                                                            @if($user->email_verified_at)
                                                                <span class="badge badge-success">Verified</span>
                                                            @else
                                                                <span class="badge badge-warning">Not Verified</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Last Login:</strong></td>
                                                        <td>{{ $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : 'Never' }}</td>
                                                    </tr>
                                                </table>
                                            </div>
                                            <div class="col-md-6">
                                                <table class="table table-sm">
                                                    <tr>
                                                        <td><strong>Status:</strong></td>
                                                        <td>
                                                            <span class="badge badge-{{ $user->status === 'active' ? 'success' : 'danger' }}">
                                                                {{ ucfirst($user->status) }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Last Updated:</strong></td>
                                                        <td>{{ $user->updated_at->format('d/m/Y H:i') }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Account Age:</strong></td>
                                                        <td>{{ $user->created_at->diffForHumans() }}</td>
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
                                            <a href="{{ route('settings.password') }}" class="btn btn-outline-warning">
                                                <i class="fas fa-lock mr-1"></i> Change Password
                                            </a>
                                            @if(!$user->email_verified_at)
                                            <a href="{{ route('verification.notice') }}" class="btn btn-outline-info">
                                                <i class="fas fa-envelope mr-1"></i> Verify Email
                                            </a>
                                            @endif
                                            <a href="{{ route('dashboard') }}" class="btn btn-outline-primary">
                                                <i class="fas fa-tachometer-alt mr-1"></i> Go to Dashboard
                                            </a>
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
                                        <i class="fas fa-save mr-1"></i> Update Profile
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
.profile-avatar-container {
    border: 2px dashed #ddd;
    border-radius: 8px;
    padding: 20px;
    background-color: #f8f9fa;
}

.profile-avatar-placeholder {
    width: 200px;
    height: 200px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #e9ecef;
    border-radius: 8px;
    margin: 0 auto;
}

.profile-avatar {
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
</style>
@endpush

@push('scripts')
<script>
// Preview avatar before upload
document.getElementById('avatar').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const container = document.querySelector('.profile-avatar-container');
            container.innerHTML = `<img src="${e.target.result}" alt="Preview" class="img-fluid profile-avatar mb-3" style="max-width: 200px; max-height: 200px;">`;
        };
        reader.readAsDataURL(file);
    }
});
</script>
@endpush 