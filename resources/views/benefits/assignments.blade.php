@extends('layouts.app')

@section('title', 'Benefit Assignments')

@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Benefit Assignments</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('benefits.index') }}">Benefits</a></li>
                        <li class="breadcrumb-item active">Assignments</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Assign New Benefit -->
            @if(in_array(auth()->user()->role, ['admin', 'hr']))
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user-plus mr-2"></i>
                        Assign New Benefit
                    </h3>
                </div>
                <form action="{{ route('benefits.assign') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="employee_id">Employee <span class="text-danger">*</span></label>
                                    <select class="form-control @error('employee_id') is-invalid @enderror" 
                                            id="employee_id" name="employee_id" required>
                                        <option value="">Select employee</option>
                                        @foreach($employees as $employee)
                                            <option value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                                                {{ $employee->name }} ({{ $employee->employee_id }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('employee_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="benefit_id">Benefit <span class="text-danger">*</span></label>
                                    <select class="form-control @error('benefit_id') is-invalid @enderror" 
                                            id="benefit_id" name="benefit_id" required>
                                        <option value="">Select benefit</option>
                                        @foreach($benefits as $benefit)
                                            <option value="{{ $benefit->id }}" {{ old('benefit_id') == $benefit->id ? 'selected' : '' }}>
                                                {{ $benefit->name }} ({{ ucfirst($benefit->type) }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('benefit_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="amount">Amount</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp</span>
                                        </div>
                                        <input type="number" class="form-control @error('amount') is-invalid @enderror" 
                                               id="amount" name="amount" value="{{ old('amount') }}" 
                                               placeholder="0" step="0.01" min="0">
                                    </div>
                                    @error('amount')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="start_date">Start Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('start_date') is-invalid @enderror" 
                                           id="start_date" name="start_date" value="{{ old('start_date') }}" required>
                                    @error('start_date')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="status">Status <span class="text-danger">*</span></label>
                                    <select class="form-control @error('status') is-invalid @enderror" 
                                            id="status" name="status" required>
                                        <option value="">Select status</option>
                                        <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                    @error('status')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="end_date">End Date</label>
                                    <input type="date" class="form-control @error('end_date') is-invalid @enderror" 
                                           id="end_date" name="end_date" value="{{ old('end_date') }}">
                                    @error('end_date')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="notes">Notes</label>
                                    <textarea class="form-control @error('notes') is-invalid @enderror" 
                                              id="notes" name="notes" rows="1" 
                                              placeholder="Additional notes">{{ old('notes') }}</textarea>
                                    @error('notes')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus mr-2"></i>
                            Assign Benefit
                        </button>
                    </div>
                </form>
            </div>
            @endif

            <!-- Assignments List -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list mr-2"></i>
                        All Assignments
                    </h3>
                </div>
                <div class="card-body">
                    @if($assignments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Employee</th>
                                        <th>Benefit</th>
                                        <th>Amount</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Status</th>
                                        <th>Assigned By</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($assignments as $assignment)
                                    <tr>
                                        <td>
                                            <strong>{{ $assignment->employee->name }}</strong>
                                            <br><small class="text-muted">{{ $assignment->employee->employee_id }}</small>
                                        </td>
                                        <td>
                                            <strong>{{ $assignment->benefit->name }}</strong>
                                            <br><small class="text-muted">{{ ucfirst($assignment->benefit->type) }}</small>
                                        </td>
                                        <td>{{ $assignment->formatted_amount }}</td>
                                        <td>{{ $assignment->formatted_start_date }}</td>
                                        <td>{{ $assignment->formatted_end_date }}</td>
                                        <td>
                                            <span class="badge {{ $assignment->status_badge }}">
                                                {{ ucfirst($assignment->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            {{ $assignment->assignedBy ? $assignment->assignedBy->name : 'System' }}
                                            <br><small class="text-muted">{{ $assignment->created_at->format('d M Y') }}</small>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                @if(in_array(auth()->user()->role, ['admin', 'hr']))
                                                <button type="button" class="btn btn-warning btn-sm" 
                                                        data-toggle="modal" data-target="#editModal{{ $assignment->id }}" 
                                                        title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form action="{{ route('benefits.remove-assignment', $assignment->id) }}" 
                                                      method="POST" class="d-inline" 
                                                      onsubmit="return confirm('Are you sure you want to remove this assignment?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" title="Remove">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                                @endif
                                            </div>

                                            <!-- Edit Modal -->
                                            @if(in_array(auth()->user()->role, ['admin', 'hr']))
                                            <div class="modal fade" id="editModal{{ $assignment->id }}" tabindex="-1" role="dialog">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <form action="{{ route('benefits.update-assignment', $assignment->id) }}" method="POST">
                                                            @csrf
                                                            @method('PUT')
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Edit Assignment</h5>
                                                                <button type="button" class="close" data-dismiss="modal">
                                                                    <span>&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="form-group">
                                                                    <label>Employee</label>
                                                                    <input type="text" class="form-control" 
                                                                           value="{{ $assignment->employee->name }}" readonly>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>Benefit</label>
                                                                    <input type="text" class="form-control" 
                                                                           value="{{ $assignment->benefit->name }}" readonly>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="amount">Amount</label>
                                                                    <div class="input-group">
                                                                        <div class="input-group-prepend">
                                                                            <span class="input-group-text">Rp</span>
                                                                        </div>
                                                                        <input type="number" class="form-control" 
                                                                               name="amount" value="{{ $assignment->amount }}" 
                                                                               step="0.01" min="0">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="start_date">Start Date</label>
                                                                    <input type="date" class="form-control" 
                                                                           name="start_date" 
                                                                           value="{{ $assignment->start_date ? $assignment->start_date->format('Y-m-d') : '' }}">
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="end_date">End Date</label>
                                                                    <input type="date" class="form-control" 
                                                                           name="end_date" 
                                                                           value="{{ $assignment->end_date ? $assignment->end_date->format('Y-m-d') : '' }}">
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="status">Status</label>
                                                                    <select class="form-control" name="status">
                                                                        <option value="active" {{ $assignment->status == 'active' ? 'selected' : '' }}>Active</option>
                                                                        <option value="pending" {{ $assignment->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                                                        <option value="inactive" {{ $assignment->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                                                        <option value="expired" {{ $assignment->status == 'expired' ? 'selected' : '' }}>Expired</option>
                                                                    </select>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="notes">Notes</label>
                                                                    <textarea class="form-control" name="notes" rows="2">{{ $assignment->notes }}</textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                                <button type="submit" class="btn btn-primary">Update Assignment</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-3">
                            {{ $assignments->links() }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-user-plus fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Assignments Found</h5>
                            <p class="text-muted">Start by assigning benefits to employees.</p>
                            @if(in_array(auth()->user()->role, ['admin', 'hr']))
                            <button type="button" class="btn btn-primary" onclick="scrollToTop()">
                                <i class="fas fa-plus mr-2"></i>
                                Assign First Benefit
                            </button>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
</div>

<script>
function scrollToTop() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
</script>
@endsection 