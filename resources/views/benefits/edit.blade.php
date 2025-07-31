@extends('layouts.app')

@section('title', 'Edit Benefit')

@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Edit Benefit</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('benefits.index') }}">Benefits</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('benefits.benefits') }}">Benefits List</a></li>
                        <li class="breadcrumb-item active">Edit Benefit</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-edit mr-2"></i>
                                Edit Benefit: {{ $benefit->name }}
                            </h3>
                        </div>
                        <form action="{{ route('benefits.update', $benefit->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="name">Benefit Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                                   id="name" name="name" value="{{ old('name', $benefit->name) }}" 
                                                   placeholder="Enter benefit name" required>
                                            @error('name')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="type">Benefit Type <span class="text-danger">*</span></label>
                                            <select class="form-control @error('type') is-invalid @enderror" 
                                                    id="type" name="type" required>
                                                <option value="">Select benefit type</option>
                                                <option value="health" {{ old('type', $benefit->type) == 'health' ? 'selected' : '' }}>Health</option>
                                                <option value="insurance" {{ old('type', $benefit->type) == 'insurance' ? 'selected' : '' }}>Insurance</option>
                                                <option value="allowance" {{ old('type', $benefit->type) == 'allowance' ? 'selected' : '' }}>Allowance</option>
                                                <option value="bonus" {{ old('type', $benefit->type) == 'bonus' ? 'selected' : '' }}>Bonus</option>
                                                <option value="other" {{ old('type', $benefit->type) == 'other' ? 'selected' : '' }}>Other</option>
                                            </select>
                                            @error('type')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" name="description" rows="3" 
                                              placeholder="Enter benefit description">{{ old('description', $benefit->description) }}</textarea>
                                    @error('description')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="amount">Amount</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">Rp</span>
                                                </div>
                                                <input type="number" class="form-control @error('amount') is-invalid @enderror" 
                                                       id="amount" name="amount" value="{{ old('amount', $benefit->amount) }}" 
                                                       placeholder="0" step="0.01" min="0">
                                            </div>
                                            @error('amount')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="frequency">Frequency <span class="text-danger">*</span></label>
                                            <select class="form-control @error('frequency') is-invalid @enderror" 
                                                    id="frequency" name="frequency" required>
                                                <option value="">Select frequency</option>
                                                <option value="monthly" {{ old('frequency', $benefit->frequency) == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                                <option value="quarterly" {{ old('frequency', $benefit->frequency) == 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                                                <option value="yearly" {{ old('frequency', $benefit->frequency) == 'yearly' ? 'selected' : '' }}>Yearly</option>
                                                <option value="one_time" {{ old('frequency', $benefit->frequency) == 'one_time' ? 'selected' : '' }}>One Time</option>
                                            </select>
                                            @error('frequency')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="effective_date">Effective Date</label>
                                            <input type="date" class="form-control @error('effective_date') is-invalid @enderror" 
                                                   id="effective_date" name="effective_date" 
                                                   value="{{ old('effective_date', $benefit->effective_date ? $benefit->effective_date->format('Y-m-d') : '') }}">
                                            @error('effective_date')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="expiry_date">Expiry Date</label>
                                            <input type="date" class="form-control @error('expiry_date') is-invalid @enderror" 
                                                   id="expiry_date" name="expiry_date" 
                                                   value="{{ old('expiry_date', $benefit->expiry_date ? $benefit->expiry_date->format('Y-m-d') : '') }}">
                                            @error('expiry_date')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" 
                                                       id="is_taxable" name="is_taxable" 
                                                       {{ old('is_taxable', $benefit->is_taxable) ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="is_taxable">
                                                    Is Taxable
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" 
                                                       id="is_active" name="is_active" 
                                                       {{ old('is_active', $benefit->is_active) ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="is_active">
                                                    Is Active
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-md-6">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save mr-2"></i>
                                            Update Benefit
                                        </button>
                                        <a href="{{ route('benefits.benefits') }}" class="btn btn-secondary">
                                            <i class="fas fa-times mr-2"></i>
                                            Cancel
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-info-circle mr-2"></i>
                                Benefit Information
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="info-box bg-info">
                                <span class="info-box-icon"><i class="fas fa-gift"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Current Status</span>
                                    <span class="info-box-number">
                                        <span class="badge {{ $benefit->status_badge }}">
                                            {{ $benefit->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </span>
                                </div>
                            </div>

                            <div class="info-box bg-success">
                                <span class="info-box-icon"><i class="fas fa-users"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Active Users</span>
                                    <span class="info-box-number">{{ $benefit->getActiveEmployeeCount() }}</span>
                                </div>
                            </div>

                            <div class="info-box bg-warning">
                                <span class="info-box-icon"><i class="fas fa-dollar-sign"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Cost</span>
                                    <span class="info-box-number">{{ $benefit->getTotalCost() ? 'Rp ' . number_format($benefit->getTotalCost(), 0, ',', '.') : 'N/A' }}</span>
                                </div>
                            </div>

                            <hr>

                            <h6>Benefit Details:</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Type:</strong></td>
                                    <td><span class="badge {{ $benefit->type_badge }}">{{ ucfirst($benefit->type) }}</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Frequency:</strong></td>
                                    <td><span class="badge {{ $benefit->frequency_badge }}">{{ ucfirst($benefit->frequency) }}</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Amount:</strong></td>
                                    <td>{{ $benefit->formatted_amount }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Taxable:</strong></td>
                                    <td>{{ $benefit->is_taxable ? 'Yes' : 'No' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Created:</strong></td>
                                    <td>{{ $benefit->created_at->format('d M Y') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Last Updated:</strong></td>
                                    <td>{{ $benefit->updated_at->format('d M Y') }}</td>
                                </tr>
                            </table>

                            @if($benefit->isExpired())
                            <div class="alert alert-warning">
                                <h6><i class="fas fa-exclamation-triangle mr-2"></i>Warning:</h6>
                                This benefit has expired on {{ $benefit->formatted_expiry_date }}.
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection 