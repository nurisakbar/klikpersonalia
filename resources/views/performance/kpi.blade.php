@extends('layouts.app')

@section('title', 'KPI Management')

@section('content')
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">KPI Management</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('performance.index') }}">Performance</a></li>
                        <li class="breadcrumb-item active">KPI Management</li>
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
                            <h3>{{ $stats['total_kpis'] }}</h3>
                            <p>Total KPIs</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>{{ $stats['completed_kpis'] }}</h3>
                            <p>Completed KPIs</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>{{ $stats['pending_kpis'] }}</h3>
                            <p>Pending KPIs</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-primary">
                        <div class="inner">
                            <h3>{{ number_format($stats['average_score'], 1) }}</h3>
                            <p>Average Score</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-percentage"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KPI List -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">KPI List</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#createKpiModal">
                                    <i class="fas fa-plus"></i> Create KPI
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            @if($performances->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Employee</th>
                                                <th>Period</th>
                                                <th>Score</th>
                                                <th>Rating</th>
                                                <th>Status</th>
                                                <th>Reviewer</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($performances as $performance)
                                                <tr>
                                                    <td>
                                                        <strong>{{ $performance->employee->first_name }} {{ $performance->employee->last_name }}</strong>
                                                        <br><small class="text-muted">{{ $performance->employee->position }}</small>
                                                    </td>
                                                    <td>
                                                        {{ $performance->period_start->format('M d, Y') }} - 
                                                        {{ $performance->period_end->format('M d, Y') }}
                                                    </td>
                                                    <td>
                                                        @if($performance->overall_score)
                                                            <div class="progress-group">
                                                                <span class="float-right"><b>{{ $performance->overall_score }}</b>/100</span>
                                                                <div class="progress">
                                                                    <div class="progress-bar bg-{{ $performance->overall_score >= 80 ? 'success' : ($performance->overall_score >= 60 ? 'warning' : 'danger') }}" 
                                                                         style="width: {{ $performance->overall_score }}%"></div>
                                                                </div>
                                                            </div>
                                                        @else
                                                            <span class="text-muted">Not scored</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($performance->rating)
                                                            <span class="badge badge-{{ $performance->rating_badge }}">
                                                                {{ $performance->rating_label }}
                                                            </span>
                                                        @else
                                                            <span class="text-muted">Not rated</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-{{ $performance->status_badge }}">
                                                            {{ ucfirst($performance->status) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        @if($performance->reviewer)
                                                            {{ $performance->reviewer->name }}
                                                            <br><small class="text-muted">{{ $performance->reviewed_at->format('M d, Y H:i') }}</small>
                                                        @else
                                                            <span class="text-muted">Not reviewed</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="btn-group">
                                                            <a href="{{ route('performance.show', $performance) }}" 
                                                               class="btn btn-sm btn-info" title="View Details">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            <button type="button" 
                                                                    class="btn btn-sm btn-warning edit-kpi" 
                                                                    data-performance="{{ $performance->id }}"
                                                                    title="Edit KPI">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            @if($performance->canBeReviewed())
                                                                <button type="button" 
                                                                        class="btn btn-sm btn-success review-kpi" 
                                                                        data-performance="{{ $performance->id }}"
                                                                        title="Review KPI">
                                                                    <i class="fas fa-check"></i>
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                
                                <div class="d-flex justify-content-center">
                                    {{ $performances->links() }}
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No KPIs found</h5>
                                    <p class="text-muted">Get started by creating your first KPI.</p>
                                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createKpiModal">
                                        <i class="fas fa-plus"></i> Create KPI
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create KPI Modal -->
<div class="modal fade" id="createKpiModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New KPI</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form action="{{ route('performance.store-kpi') }}" method="POST" id="createKpiForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="employee_id">Employee <span class="text-danger">*</span></label>
                                <select class="form-control" id="employee_id" name="employee_id" required>
                                    <option value="">Select Employee</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}">
                                            {{ $employee->first_name }} {{ $employee->last_name }} - {{ $employee->position }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="period_start">Period Start <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="period_start" name="period_start" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="period_end">Period End <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="period_end" name="period_end" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="notes">Notes</label>
                                <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>KPI Items <span class="text-danger">*</span></label>
                        <div id="kpi-items">
                            <div class="kpi-item border p-3 mb-3">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>KPI Name</label>
                                            <input type="text" class="form-control" name="kpi_data[0][name]" required>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Target</label>
                                            <input type="number" class="form-control" name="kpi_data[0][target]" step="0.01" required>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Actual</label>
                                            <input type="number" class="form-control" name="kpi_data[0][actual]" step="0.01" required>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Weight (%)</label>
                                            <input type="number" class="form-control" name="kpi_data[0][weight]" min="0" max="100" required>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>&nbsp;</label>
                                            <button type="button" class="btn btn-danger btn-block remove-kpi-item">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-success btn-sm" id="add-kpi-item">
                            <i class="fas fa-plus"></i> Add KPI Item
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create KPI</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let kpiItemCount = 1;

    // Add KPI item
    $('#add-kpi-item').click(function() {
        const newItem = `
            <div class="kpi-item border p-3 mb-3">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>KPI Name</label>
                            <input type="text" class="form-control" name="kpi_data[${kpiItemCount}][name]" required>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Target</label>
                            <input type="number" class="form-control" name="kpi_data[${kpiItemCount}][target]" step="0.01" required>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Actual</label>
                            <input type="number" class="form-control" name="kpi_data[${kpiItemCount}][actual]" step="0.01" required>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Weight (%)</label>
                            <input type="number" class="form-control" name="kpi_data[${kpiItemCount}][weight]" min="0" max="100" required>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="button" class="btn btn-danger btn-block remove-kpi-item">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        $('#kpi-items').append(newItem);
        kpiItemCount++;
    });

    // Remove KPI item
    $(document).on('click', '.remove-kpi-item', function() {
        if ($('.kpi-item').length > 1) {
            $(this).closest('.kpi-item').remove();
        } else {
            toastr.warning('At least one KPI item is required.');
        }
    });

    // Form validation
    $('#createKpiForm').submit(function(e) {
        const totalWeight = 0;
        $('input[name$="[weight]"]').each(function() {
            totalWeight += parseFloat($(this).val()) || 0;
        });

        if (Math.abs(totalWeight - 100) > 0.01) {
            e.preventDefault();
            toastr.error('Total weight must equal 100%. Current total: ' + totalWeight + '%');
        }
    });
});
</script>
@endpush 