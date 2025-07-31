@extends('layouts.app')

@section('title', 'Benefits Management')

@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Benefits Management</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('benefits.index') }}">Benefits</a></li>
                        <li class="breadcrumb-item active">Benefits List</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-gift mr-2"></i>
                        All Benefits
                    </h3>
                    @if(in_array(auth()->user()->role, ['admin', 'hr']))
                    <div class="card-tools">
                        <a href="{{ route('benefits.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus mr-1"></i>
                            Add New Benefit
                        </a>
                    </div>
                    @endif
                </div>
                <div class="card-body">
                    @if($benefits->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Type</th>
                                        <th>Amount</th>
                                        <th>Frequency</th>
                                        <th>Active Users</th>
                                        <th>Status</th>
                                        <th>Effective Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($benefits as $benefit)
                                    <tr>
                                        <td>
                                            <strong>{{ $benefit->name }}</strong>
                                            @if($benefit->description)
                                                <br><small class="text-muted">{{ Str::limit($benefit->description, 100) }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge {{ $benefit->type_badge }}">
                                                {{ ucfirst($benefit->type) }}
                                            </span>
                                        </td>
                                        <td>{{ $benefit->formatted_amount }}</td>
                                        <td>
                                            <span class="badge {{ $benefit->frequency_badge }}">
                                                {{ ucfirst($benefit->frequency) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-info">{{ $benefit->active_employees }}</span>
                                        </td>
                                        <td>
                                            <span class="badge {{ $benefit->status_badge }}">
                                                {{ $benefit->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                            @if($benefit->is_taxable)
                                                <br><small class="text-muted">Taxable</small>
                                            @endif
                                        </td>
                                        <td>
                                            <div>{{ $benefit->formatted_effective_date }}</div>
                                            @if($benefit->expiry_date)
                                                <small class="text-muted">Expires: {{ $benefit->formatted_expiry_date }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="#" class="btn btn-info btn-sm" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if(in_array(auth()->user()->role, ['admin', 'hr']))
                                                <a href="{{ route('benefits.edit', $benefit->id) }}" class="btn btn-warning btn-sm" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('benefits.destroy', $benefit->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this benefit?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-3">
                            {{ $benefits->links() }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-gift fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Benefits Found</h5>
                            <p class="text-muted">Start by creating your first benefit.</p>
                            @if(in_array(auth()->user()->role, ['admin', 'hr']))
                            <a href="{{ route('benefits.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus mr-2"></i>
                                Create First Benefit
                            </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
</div>
@endsection 