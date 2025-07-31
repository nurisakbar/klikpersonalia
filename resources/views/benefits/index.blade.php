@extends('layouts.app')

@section('title', 'Benefits Dashboard')

@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Benefits Dashboard</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Benefits Dashboard</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Statistics -->
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
                        <a href="{{ route('benefits.benefits') }}" class="small-box-footer">
                            View Details <i class="fas fa-arrow-circle-right"></i>
                        </a>
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
                        <a href="{{ route('benefits.benefits') }}" class="small-box-footer">
                            View Details <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>{{ $stats['active_assignments'] }}</h3>
                            <p>Active Assignments</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <a href="{{ route('benefits.assignments') }}" class="small-box-footer">
                            View Details <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3>Rp {{ number_format($stats['total_cost'], 0, ',', '.') }}</h3>
                            <p>Total Cost</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <a href="{{ route('benefits.reports') }}" class="small-box-footer">
                            View Details <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Top Benefits -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-trophy mr-2"></i>
                                Top Benefits by Usage
                            </h3>
                        </div>
                        <div class="card-body">
                            @if($topBenefits->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Benefit</th>
                                                <th>Type</th>
                                                <th>Active Users</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($topBenefits as $benefit)
                                            <tr>
                                                <td>
                                                    <strong>{{ $benefit->name }}</strong>
                                                    @if($benefit->description)
                                                        <br><small class="text-muted">{{ Str::limit($benefit->description, 50) }}</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge {{ $benefit->type_badge }}">
                                                        {{ ucfirst($benefit->type) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-info">{{ $benefit->active_employees }}</span>
                                                </td>
                                                <td>
                                                    <span class="badge {{ $benefit->status_badge }}">
                                                        {{ $benefit->is_active ? 'Active' : 'Inactive' }}
                                                    </span>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted text-center">No benefits found.</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Recent Benefits -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-clock mr-2"></i>
                                Recent Benefits
                            </h3>
                        </div>
                        <div class="card-body">
                            @if($recentBenefits->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Benefit</th>
                                                <th>Amount</th>
                                                <th>Frequency</th>
                                                <th>Created</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($recentBenefits as $benefit)
                                            <tr>
                                                <td>
                                                    <strong>{{ $benefit->name }}</strong>
                                                    <br><small class="text-muted">{{ ucfirst($benefit->type) }}</small>
                                                </td>
                                                <td>{{ $benefit->formatted_amount }}</td>
                                                <td>
                                                    <span class="badge {{ $benefit->frequency_badge }}">
                                                        {{ ucfirst($benefit->frequency) }}
                                                    </span>
                                                </td>
                                                <td>{{ $benefit->created_at->format('d M Y') }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted text-center">No recent benefits found.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-bolt mr-2"></i>
                                Quick Actions
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @if(in_array(auth()->user()->role, ['admin', 'hr']))
                                <div class="col-md-3">
                                    <a href="{{ route('benefits.create') }}" class="btn btn-primary btn-block">
                                        <i class="fas fa-plus mr-2"></i>
                                        Create New Benefit
                                    </a>
                                </div>
                                <div class="col-md-3">
                                    <a href="{{ route('benefits.assignments') }}" class="btn btn-success btn-block">
                                        <i class="fas fa-user-plus mr-2"></i>
                                        Assign Benefits
                                    </a>
                                </div>
                                @endif
                                <div class="col-md-3">
                                    <a href="{{ route('benefits.benefits') }}" class="btn btn-info btn-block">
                                        <i class="fas fa-list mr-2"></i>
                                        View All Benefits
                                    </a>
                                </div>
                                @if(in_array(auth()->user()->role, ['admin', 'hr']))
                                <div class="col-md-3">
                                    <a href="{{ route('benefits.reports') }}" class="btn btn-warning btn-block">
                                        <i class="fas fa-chart-bar mr-2"></i>
                                        Benefit Reports
                                    </a>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alerts -->
            @if($stats['expired_benefits'] > 0)
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-warning alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <h5><i class="icon fas fa-exclamation-triangle"></i> Warning!</h5>
                        You have {{ $stats['expired_benefits'] }} expired benefit(s). Please review and update them.
                    </div>
                </div>
            </div>
            @endif
        </div>
    </section>
</div>
@endsection 