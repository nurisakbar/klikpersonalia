@extends('layouts.app')

@section('title', 'Benefits Management')

@section('content')
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Benefits Management</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Benefits Management</li>
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
                            <h3>{{ $stats['total_benefits'] }}</h3>
                            <p>Total Benefits</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-gift"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>{{ $stats['active_benefits'] }}</h3>
                            <p>Active Benefits</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>{{ $stats['enrolled_employees'] }}</h3>
                            <p>Enrolled Employees</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-primary">
                        <div class="inner">
                            <h3>Rp {{ number_format($stats['total_monthly_cost']) }}</h3>
                            <p>Total Monthly Cost</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Benefits Overview -->
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Recent Benefits</h3>
                            <div class="card-tools">
                                <a href="{{ route('benefits.benefits') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-list"></i> View All Benefits
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            @if($recentBenefits->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Benefit Name</th>
                                                <th>Type</th>
                                                <th>Cost</th>
                                                <th>Status</th>
                                                <th>Enrolled</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($recentBenefits as $benefit)
                                                <tr>
                                                    <td>
                                                        <strong>{{ $benefit->name }}</strong>
                                                        @if($benefit->provider)
                                                            <br><small class="text-muted">{{ $benefit->provider }}</small>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-info">{{ $benefit->type_label }}</span>
                                                    </td>
                                                    <td>
                                                        @if($benefit->cost_type === 'fixed')
                                                            Rp {{ number_format($benefit->cost_amount) }}
                                                        @elseif($benefit->cost_type === 'percentage')
                                                            {{ $benefit->cost_percentage }}%
                                                        @else
                                                            Mixed
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-{{ $benefit->status_badge }}">
                                                            {{ $benefit->status_label }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-secondary">{{ $benefit->active_employees }}</span>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group">
                                                            <a href="{{ route('benefits.edit', $benefit) }}" 
                                                               class="btn btn-sm btn-warning" title="Edit">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <a href="{{ route('benefits.assignments') }}?benefit={{ $benefit->id }}" 
                                                               class="btn btn-sm btn-info" title="View Assignments">
                                                                <i class="fas fa-users"></i>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-gift fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No benefits configured</h5>
                                    <p class="text-muted">Get started by creating your first benefit.</p>
                                    <a href="{{ route('benefits.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Create Benefit
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Top Benefits by Enrollment</h3>
                        </div>
                        <div class="card-body">
                            @if($topBenefits->count() > 0)
                                @foreach($topBenefits as $benefit)
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                            <h6 class="mb-0">{{ $benefit->name }}</h6>
                                            <small class="text-muted">{{ $benefit->type_label }}</small>
                                        </div>
                                        <div class="text-right">
                                            <span class="badge badge-primary">{{ $benefit->enrolled_count }}</span>
                                            <br><small class="text-muted">employees</small>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <p class="text-muted text-center">No enrollment data available</p>
                            @endif
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Quick Actions</h3>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="{{ route('benefits.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Create New Benefit
                                </a>
                                <a href="{{ route('benefits.assignments') }}" class="btn btn-success">
                                    <i class="fas fa-user-plus"></i> Assign Benefits
                                </a>
                                <a href="{{ route('benefits.reports') }}" class="btn btn-info">
                                    <i class="fas fa-chart-bar"></i> View Reports
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cost Analysis -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Monthly Cost Analysis</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-success"><i class="fas fa-building"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Employer Cost</span>
                                            <span class="info-box-number">Rp {{ number_format($stats['employer_cost']) }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-info"><i class="fas fa-user"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Employee Cost</span>
                                            <span class="info-box-number">Rp {{ number_format($stats['employee_cost']) }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-warning"><i class="fas fa-chart-pie"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Cost per Employee</span>
                                            <span class="info-box-number">Rp {{ number_format($stats['cost_per_employee']) }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-primary"><i class="fas fa-percentage"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Coverage Rate</span>
                                            <span class="info-box-number">{{ number_format($stats['coverage_rate'], 1) }}%</span>
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
</div>
@endsection 