@extends('layouts.app')

@section('title', 'Add External Integration')

@section('content')
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Add External Integration</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('integrations.index') }}">External Integrations</a></li>
                        <li class="breadcrumb-item active">Add Integration</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Integration Configuration</h3>
                        </div>
                        <form action="{{ route('integrations.store') }}" method="POST">
                            @csrf
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="integration_type">Integration Type <span class="text-danger">*</span></label>
                                            <select class="form-control @error('integration_type') is-invalid @enderror" 
                                                    id="integration_type" 
                                                    name="integration_type" 
                                                    required>
                                                <option value="">Select Integration Type</option>
                                                @foreach($integrationTypes as $value => $label)
                                                    <option value="{{ $value }}" {{ old('integration_type') == $value ? 'selected' : '' }}>
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('integration_type')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="name">Integration Name <span class="text-danger">*</span></label>
                                            <input type="text" 
                                                   class="form-control @error('name') is-invalid @enderror" 
                                                   id="name" 
                                                   name="name" 
                                                   value="{{ old('name') }}" 
                                                   placeholder="e.g., Company HRIS System"
                                                   required>
                                            @error('name')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="api_endpoint">API Endpoint</label>
                                    <input type="url" 
                                           class="form-control @error('api_endpoint') is-invalid @enderror" 
                                           id="api_endpoint" 
                                           name="api_endpoint" 
                                           value="{{ old('api_endpoint') }}" 
                                           placeholder="https://api.example.com/v1">
                                    @error('api_endpoint')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="api_key">API Key</label>
                                            <input type="text" 
                                                   class="form-control @error('api_key') is-invalid @enderror" 
                                                   id="api_key" 
                                                   name="api_key" 
                                                   value="{{ old('api_key') }}" 
                                                   placeholder="Your API Key">
                                            @error('api_key')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="api_secret">API Secret</label>
                                            <input type="password" 
                                                   class="form-control @error('api_secret') is-invalid @enderror" 
                                                   id="api_secret" 
                                                   name="api_secret" 
                                                   value="{{ old('api_secret') }}" 
                                                   placeholder="Your API Secret">
                                            @error('api_secret')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="username">Username</label>
                                            <input type="text" 
                                                   class="form-control @error('username') is-invalid @enderror" 
                                                   id="username" 
                                                   name="username" 
                                                   value="{{ old('username') }}" 
                                                   placeholder="Username for authentication">
                                            @error('username')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="password">Password</label>
                                            <input type="password" 
                                                   class="form-control @error('password') is-invalid @enderror" 
                                                   id="password" 
                                                   name="password" 
                                                   value="{{ old('password') }}" 
                                                   placeholder="Password for authentication">
                                            @error('password')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="sync_frequency">Sync Frequency <span class="text-danger">*</span></label>
                                    <select class="form-control @error('sync_frequency') is-invalid @enderror" 
                                            id="sync_frequency" 
                                            name="sync_frequency" 
                                            required>
                                        @foreach($syncFrequencies as $value => $label)
                                            <option value="{{ $value }}" {{ old('sync_frequency') == $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('sync_frequency')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="notes">Notes</label>
                                    <textarea class="form-control @error('notes') is-invalid @enderror" 
                                              id="notes" 
                                              name="notes" 
                                              rows="3" 
                                              placeholder="Additional notes about this integration">{{ old('notes') }}</textarea>
                                    @error('notes')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Create Integration
                                </button>
                                <a href="{{ route('integrations.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Integration Types</h3>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <h6><i class="fas fa-users text-info"></i> HRIS System</h6>
                                <small class="text-muted">Integrate with Human Resource Information System for employee data synchronization.</small>
                            </div>
                            <div class="mb-3">
                                <h6><i class="fas fa-calculator text-success"></i> Accounting System</h6>
                                <small class="text-muted">Connect with accounting software for payroll journal entries and financial reporting.</small>
                            </div>
                            <div class="mb-3">
                                <h6><i class="fas fa-building text-warning"></i> Government Portal</h6>
                                <small class="text-muted">Integrate with government portals for compliance reporting and data submission.</small>
                            </div>
                            <div class="mb-3">
                                <h6><i class="fas fa-heartbeat text-danger"></i> BPJS Online</h6>
                                <small class="text-muted">Connect with BPJS online system for health and employment insurance data.</small>
                            </div>
                            <div class="mb-3">
                                <h6><i class="fas fa-file-invoice-dollar text-primary"></i> Tax Office</h6>
                                <small class="text-muted">Integrate with tax office systems for tax reporting and compliance.</small>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Security Notes</h3>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                <strong>Important:</strong> All API keys, secrets, and passwords are encrypted before storage.
                            </div>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success"></i> Use HTTPS endpoints only</li>
                                <li><i class="fas fa-check text-success"></i> Store credentials securely</li>
                                <li><i class="fas fa-check text-success"></i> Regular connection testing</li>
                                <li><i class="fas fa-check text-success"></i> Audit trail for all syncs</li>
                            </ul>
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
    // Show/hide fields based on integration type
    $('#integration_type').change(function() {
        const type = $(this).val();
        
        // Reset all fields
        $('.field-group').show();
        
        // Hide/show fields based on type
        switch(type) {
            case 'hris':
                $('.field-api').show();
                $('.field-auth').show();
                break;
            case 'accounting':
                $('.field-api').show();
                $('.field-auth').show();
                break;
            case 'government':
                $('.field-api').show();
                $('.field-auth').show();
                break;
            case 'bpjs':
                $('.field-api').show();
                $('.field-auth').show();
                break;
            case 'tax_office':
                $('.field-api').show();
                $('.field-auth').show();
                break;
        }
    });
});
</script>
@endpush 