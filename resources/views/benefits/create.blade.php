@extends('layouts.app')

@section('title', 'Create New Benefit')

@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Create New Benefit</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('benefits.index') }}">Benefits</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('benefits.benefits') }}">Benefits List</a></li>
                        <li class="breadcrumb-item active">Create Benefit</li>
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
                                <i class="fas fa-plus mr-2"></i>
                                Benefit Information
                            </h3>
                        </div>
                        <form action="{{ route('benefits.store') }}" method="POST">
                            @csrf
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="name">Benefit Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                                   id="name" name="name" value="{{ old('name') }}" 
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
                                                <option value="health" {{ old('type') == 'health' ? 'selected' : '' }}>Health</option>
                                                <option value="insurance" {{ old('type') == 'insurance' ? 'selected' : '' }}>Insurance</option>
                                                <option value="allowance" {{ old('type') == 'allowance' ? 'selected' : '' }}>Allowance</option>
                                                <option value="bonus" {{ old('type') == 'bonus' ? 'selected' : '' }}>Bonus</option>
                                                <option value="other" {{ old('type') == 'other' ? 'selected' : '' }}>Other</option>
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
                                              placeholder="Enter benefit description">{{ old('description') }}</textarea>
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
                                                       id="amount" name="amount" value="{{ old('amount') }}" 
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
                                                <option value="monthly" {{ old('frequency') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                                <option value="quarterly" {{ old('frequency') == 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                                                <option value="yearly" {{ old('frequency') == 'yearly' ? 'selected' : '' }}>Yearly</option>
                                                <option value="one_time" {{ old('frequency') == 'one_time' ? 'selected' : '' }}>One Time</option>
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
                                                   id="effective_date" name="effective_date" value="{{ old('effective_date') }}">
                                            @error('effective_date')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="expiry_date">Expiry Date</label>
                                            <input type="date" class="form-control @error('expiry_date') is-invalid @enderror" 
                                                   id="expiry_date" name="expiry_date" value="{{ old('expiry_date') }}">
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
                                                       {{ old('is_taxable') ? 'checked' : '' }}>
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
                                                       {{ old('is_active', true) ? 'checked' : '' }}>
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
                                            Create Benefit
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
                                Information
                            </h3>
                        </div>
                        <div class="card-body">
                            <h6>Benefit Types:</h6>
                            <ul class="list-unstyled">
                                <li><span class="badge badge-success">Health</span> - Medical, dental, vision</li>
                                <li><span class="badge badge-info">Insurance</span> - Life, disability, liability</li>
                                <li><span class="badge badge-warning">Allowance</span> - Transportation, meal, housing</li>
                                <li><span class="badge badge-primary">Bonus</span> - Performance, holiday, signing</li>
                                <li><span class="badge badge-secondary">Other</span> - Custom benefits</li>
                            </ul>

                            <hr>

                            <h6>Frequency Options:</h6>
                            <ul class="list-unstyled">
                                <li><strong>Monthly</strong> - Paid every month</li>
                                <li><strong>Quarterly</strong> - Paid every 3 months</li>
                                <li><strong>Yearly</strong> - Paid annually</li>
                                <li><strong>One Time</strong> - Single payment</li>
                            </ul>

                            <hr>

                            <div class="alert alert-info">
                                <h6><i class="fas fa-lightbulb mr-2"></i>Tips:</h6>
                                <ul class="mb-0">
                                    <li>Leave amount empty for non-monetary benefits</li>
                                    <li>Set effective date to control when benefit becomes available</li>
                                    <li>Mark as taxable if it should be included in tax calculations</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection 