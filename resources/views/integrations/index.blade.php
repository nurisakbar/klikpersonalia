@extends('layouts.app')

@section('title', 'External Integrations')

@section('content')
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">External Integrations</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">External Integrations</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <!-- Statistics Cards -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{ $stats['total'] }}</h3>
                            <p>Total Integrations</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-plug"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>{{ $stats['active'] }}</h3>
                            <p>Active Integrations</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>{{ $stats['syncing'] }}</h3>
                            <p>Currently Syncing</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-sync-alt"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3>{{ $stats['error'] }}</h3>
                            <p>Error Status</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Integrations List -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Integration List</h3>
                            <div class="card-tools">
                                <a href="{{ route('integrations.create') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus"></i> Add Integration
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            @if($integrations->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Type</th>
                                                <th>Status</th>
                                                <th>Last Sync</th>
                                                <th>Sync Frequency</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($integrations as $integration)
                                                <tr>
                                                    <td>
                                                        <strong>{{ $integration->name }}</strong>
                                                        @if($integration->notes)
                                                            <br><small class="text-muted">{{ Str::limit($integration->notes, 50) }}</small>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-info">{{ $integration->type_label }}</span>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-{{ $integration->status_badge }}">
                                                            {{ ucfirst($integration->status) }}
                                                        </span>
                                                        @if($integration->is_active)
                                                            <span class="badge badge-success">Active</span>
                                                        @else
                                                            <span class="badge badge-secondary">Inactive</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($integration->last_sync_at)
                                                            {{ $integration->last_sync_at->format('M d, Y H:i') }}
                                                            <br><small class="text-muted">{{ $integration->last_sync_at->diffForHumans() }}</small>
                                                        @else
                                                            <span class="text-muted">Never</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-secondary">{{ $integration->frequency_label }}</span>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group">
                                                            <a href="{{ route('integrations.show', $integration) }}" 
                                                               class="btn btn-sm btn-info" title="View Details">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            <a href="{{ route('integrations.edit', $integration) }}" 
                                                               class="btn btn-sm btn-warning" title="Edit">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <button type="button" 
                                                                    class="btn btn-sm btn-success test-connection" 
                                                                    data-id="{{ $integration->id }}"
                                                                    title="Test Connection">
                                                                <i class="fas fa-plug"></i>
                                                            </button>
                                                            <button type="button" 
                                                                    class="btn btn-sm btn-primary sync-now" 
                                                                    data-id="{{ $integration->id }}"
                                                                    title="Sync Now">
                                                                <i class="fas fa-sync-alt"></i>
                                                            </button>
                                                            <form action="{{ route('integrations.destroy', $integration) }}" 
                                                                  method="POST" 
                                                                  class="d-inline"
                                                                  onsubmit="return confirm('Are you sure you want to delete this integration?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-plug fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No integrations configured</h5>
                                    <p class="text-muted">Get started by adding your first external system integration.</p>
                                    <a href="{{ route('integrations.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Add Integration
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Sync Modal -->
<div class="modal fade" id="syncModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Sync Integration</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="syncForm">
                    <div class="form-group">
                        <label for="sync_type">Sync Type</label>
                        <select class="form-control" id="sync_type" name="sync_type" required>
                            <option value="employee">Employee Data</option>
                            <option value="payroll">Payroll Data</option>
                            <option value="attendance">Attendance Data</option>
                            <option value="tax">Tax Data</option>
                            <option value="bpjs">BPJS Data</option>
                            <option value="leave">Leave Data</option>
                            <option value="overtime">Overtime Data</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmSync">Start Sync</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Test Connection
    $('.test-connection').click(function() {
        const integrationId = $(this).data('id');
        const button = $(this);
        
        button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        
        $.ajax({
            url: `/integrations/${integrationId}/test-connection`,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('Connection test failed. Please try again.');
            },
            complete: function() {
                button.prop('disabled', false).html('<i class="fas fa-plug"></i>');
                setTimeout(() => location.reload(), 2000);
            }
        });
    });

    // Sync Now
    let currentIntegrationId = null;
    
    $('.sync-now').click(function() {
        currentIntegrationId = $(this).data('id');
        $('#syncModal').modal('show');
    });
    
    $('#confirmSync').click(function() {
        const syncType = $('#sync_type').val();
        const button = $(this);
        
        button.prop('disabled', true).text('Syncing...');
        
        $.ajax({
            url: `/integrations/${currentIntegrationId}/sync-now`,
            method: 'POST',
            data: {
                sync_type: syncType,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('Sync failed. Please try again.');
            },
            complete: function() {
                button.prop('disabled', false).text('Start Sync');
                $('#syncModal').modal('hide');
                setTimeout(() => location.reload(), 2000);
            }
        });
    });
});
</script>
@endpush 