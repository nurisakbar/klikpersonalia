@extends('layouts.app')

@section('title', 'Employee Benefits')

@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Employee Benefits</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('benefits.index') }}">Benefits</a></li>
                        <li class="breadcrumb-item active">Employee Benefits</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Employee Information -->
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-user mr-2"></i>
                                Employee Information
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <i class="fas fa-user-circle fa-4x text-primary"></i>
                            </div>
                            <h5 class="text-center">{{ $employee->name }}</h5>
                            <p class="text-center text-muted">{{ $employee->employee_id }}</p>
                            
                            <hr>
                            
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Position:</strong></td>
                                    <td>{{ $employee->position }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Department:</strong></td>
                                    <td>{{ $employee->department }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Join Date:</strong></td>
                                    <td>{{ $employee->join_date ? $employee->join_date->format('d M Y') : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        <span class="badge badge-{{ $employee->status == 'active' ? 'success' : 'secondary' }}">
                                            {{ ucfirst($employee->status) }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-gift mr-2"></i>
                                Benefits Summary
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="info-box bg-info">
                                        <span class="info-box-icon"><i class="fas fa-gift"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Total Benefits</span>
                                            <span class="info-box-number">{{ $benefits->count() }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="info-box bg-success">
                                        <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Active Benefits</span>
                                            <span class="info-box-number">{{ $benefits->where('status', 'active')->count() }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="info-box bg-warning">
                                        <span class="info-box-icon"><i class="fas fa-dollar-sign"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Total Value</span>
                                            <span class="info-box-number">Rp {{ number_format($totalValue, 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Benefits by Type -->
                            <div class="row mt-3">
                                <div class="col-12">
                                    <h6>Benefits by Type:</h6>
                                    <div class="row">
                                        @php
                                            $benefitTypes = $benefits->groupBy('benefit.type');
                                        @endphp
                                        @foreach($benefitTypes as $type => $typeBenefits)
                                        <div class="col-md-3 mb-2">
                                            <div class="small-box bg-light">
                                                <div class="inner">
                                                    <h3>{{ $typeBenefits->count() }}</h3>
                                                    <p>{{ ucfirst($type) }}</p>
                                                </div>
                                                <div class="icon">
                                                    @switch($type)
                                                        @case('health')
                                                            <i class="fas fa-heartbeat text-success"></i>
                                                            @break
                                                        @case('insurance')
                                                            <i class="fas fa-shield-alt text-info"></i>
                                                            @break
                                                        @case('allowance')
                                                            <i class="fas fa-money-bill-wave text-warning"></i>
                                                            @break
                                                        @case('bonus')
                                                            <i class="fas fa-star text-primary"></i>
                                                            @break
                                                        @default
                                                            <i class="fas fa-gift text-secondary"></i>
                                                    @endswitch
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Benefits List -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list mr-2"></i>
                        All Assigned Benefits
                    </h3>
                </div>
                <div class="card-body">
                    @if($benefits->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Benefit</th>
                                        <th>Type</th>
                                        <th>Amount</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Status</th>
                                        <th>Assigned By</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($benefits as $benefit)
                                    <tr>
                                        <td>
                                            <strong>{{ $benefit->benefit->name }}</strong>
                                            @if($benefit->benefit->description)
                                                <br><small class="text-muted">{{ Str::limit($benefit->benefit->description, 50) }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge {{ $benefit->benefit->type_badge }}">
                                                {{ ucfirst($benefit->benefit->type) }}
                                            </span>
                                        </td>
                                        <td>
                                            <strong>{{ $benefit->formatted_amount }}</strong>
                                            @if($benefit->benefit->frequency)
                                                <br><small class="text-muted">{{ ucfirst($benefit->benefit->frequency) }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $benefit->formatted_start_date }}</td>
                                        <td>
                                            {{ $benefit->formatted_end_date }}
                                            @if($benefit->isExpired())
                                                <br><small class="text-danger">Expired</small>
                                            @elseif($benefit->getRemainingDays() !== null)
                                                <br><small class="text-info">{{ $benefit->getRemainingDays() }} days left</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge {{ $benefit->status_badge }}">
                                                {{ ucfirst($benefit->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            {{ $benefit->assignedBy ? $benefit->assignedBy->name : 'System' }}
                                            <br><small class="text-muted">{{ $benefit->created_at->format('d M Y') }}</small>
                                        </td>
                                        <td>
                                            @if($benefit->notes)
                                                <small class="text-muted">{{ Str::limit($benefit->notes, 100) }}</small>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-gift fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Benefits Assigned</h5>
                            <p class="text-muted">This employee has not been assigned any benefits yet.</p>
                            @if(in_array(auth()->user()->role, ['admin', 'hr']))
                            <a href="{{ route('benefits.assignments') }}" class="btn btn-primary">
                                <i class="fas fa-plus mr-2"></i>
                                Assign Benefits
                            </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Benefits Timeline -->
            @if($benefits->count() > 0)
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-calendar-alt mr-2"></i>
                        Benefits Timeline
                    </h3>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        @foreach($benefits->sortBy('start_date') as $benefit)
                        <div>
                            <i class="fas fa-gift bg-{{ $benefit->status == 'active' ? 'success' : ($benefit->status == 'pending' ? 'warning' : 'secondary') }}"></i>
                            <div class="timeline-item">
                                <span class="time">
                                    <i class="fas fa-clock"></i> 
                                    {{ $benefit->created_at->format('d M Y H:i') }}
                                </span>
                                <h3 class="timeline-header">
                                    <strong>{{ $benefit->benefit->name }}</strong>
                                    <span class="badge {{ $benefit->status_badge }} ml-2">
                                        {{ ucfirst($benefit->status) }}
                                    </span>
                                </h3>
                                <div class="timeline-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong>Type:</strong> {{ ucfirst($benefit->benefit->type) }}<br>
                                            <strong>Amount:</strong> {{ $benefit->formatted_amount }}<br>
                                            <strong>Frequency:</strong> {{ ucfirst($benefit->benefit->frequency) }}
                                        </div>
                                        <div class="col-md-6">
                                            <strong>Start:</strong> {{ $benefit->formatted_start_date }}<br>
                                            <strong>End:</strong> {{ $benefit->formatted_end_date ?: 'No end date' }}<br>
                                            <strong>Assigned by:</strong> {{ $benefit->assignedBy ? $benefit->assignedBy->name : 'System' }}
                                        </div>
                                    </div>
                                    @if($benefit->notes)
                                        <hr>
                                        <small class="text-muted"><strong>Notes:</strong> {{ $benefit->notes }}</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                        <div>
                            <i class="fas fa-clock bg-gray"></i>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </section>
</div>
@endsection 