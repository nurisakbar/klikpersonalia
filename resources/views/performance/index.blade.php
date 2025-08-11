@extends('layouts.app')

@section('title', 'Performance Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line mr-2"></i>
                        Performance Management Dashboard
                    </h3>
                </div>
                <div class="card-body">
                    <!-- Performance Statistics -->
                    <div class="row">
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3>{{ $stats['total_employees'] }}</h3>
                                    <p>Total Employees</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <a href="#" class="small-box-footer">
                                    View Details <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3>{{ $stats['total_appraisals'] }}</h3>
                                    <p>Total Appraisals</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-clipboard-check"></i>
                                </div>
                                <a href="{{ route('performance.appraisal') }}" class="small-box-footer">
                                    View Details <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3>{{ $stats['average_rating'] }}</h3>
                                    <p>Average Rating</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-star"></i>
                                </div>
                                <a href="{{ route('performance.reports') }}" class="small-box-footer">
                                    View Details <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-danger">
                                <div class="inner">
                                    <h3>{{ $stats['top_performers'] }}</h3>
                                    <p>Top Performers</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-trophy"></i>
                                </div>
                                <a href="#" class="small-box-footer">
                                    View Details <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Performance Overview -->
                    <div class="row">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-chart-bar mr-2"></i>
                                        Performance Overview
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6>Performance Rate</h6>
                                            <div class="progress mb-3">
                                                <div class="progress-bar bg-success" role="progressbar" 
                                                     style="width: {{ $stats['performance_rate'] }}%" 
                                                     aria-valuenow="{{ $stats['performance_rate'] }}" 
                                                     aria-valuemin="0" aria-valuemax="100">
                                                    {{ $stats['performance_rate'] }}%
                                                </div>
                                            </div>
                                            <small class="text-muted">Employees with performance reviews</small>
                                        </div>
                                        <div class="col-md-6">
                                            <h6>Average Rating Distribution</h6>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>Outstanding (4.5-5.0)</span>
                                                <span class="badge badge-success">{{ $topPerformers->where('overall_rating', '>=', 4.5)->count() }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>Excellent (4.0-4.4)</span>
                                                <span class="badge badge-primary">{{ $topPerformers->where('overall_rating', '>=', 4.0)->where('overall_rating', '<', 4.5)->count() }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>Good (3.5-3.9)</span>
                                                <span class="badge badge-info">{{ $topPerformers->where('overall_rating', '>=', 3.5)->where('overall_rating', '<', 4.0)->count() }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-trophy mr-2"></i>
                                        Top Performers
                                    </h5>
                                </div>
                                <div class="card-body">
                                    @if($topPerformers->count() > 0)
                                        @foreach($topPerformers->take(5) as $performance)
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <div>
                                                    <strong>{{ $performance->employee->name }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $performance->employee->position }}</small>
                                                </div>
                                                <div class="text-right">
                                                    <span class="badge badge-success">{{ $performance->overall_rating }}</span>
                                                    <br>
                                                    <small class="text-muted">{{ $performance->formatted_review_date }}</small>
                                                </div>
                                            </div>
                                            @if(!$loop->last)
                                                <hr>
                                            @endif
                                        @endforeach
                                    @else
                                        <p class="text-muted text-center">No performance data available</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Performance Reviews -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-history mr-2"></i>
                                        Recent Performance Reviews
                                    </h5>
                                </div>
                                <div class="card-body">
                                    @if($recentReviews->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Employee</th>
                                                        <th>Position</th>
                                                        <th>Rating</th>
                                                        <th>Period</th>
                                                        <th>Review Date</th>
                                                        <th>Reviewer</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($recentReviews as $review)
                                                        <tr>
                                                            <td>
                                                                <strong>{{ $review->employee->name }}</strong>
                                                            </td>
                                                            <td>{{ $review->employee->position }}</td>
                                                            <td>{!! $review->rating_badge !!}</td>
                                                            <td>{!! $review->period_badge !!}</td>
                                                            <td>{{ $review->formatted_review_date }}</td>
                                                            <td>{{ $review->reviewer->name }}</td>
                                                            <td>
                                                                <a href="#" class="btn btn-sm btn-info">
                                                                    <i class="fas fa-eye"></i> View
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <p class="text-muted text-center">No recent performance reviews</p>
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
                                    <h5 class="card-title">
                                        <i class="fas fa-bolt mr-2"></i>
                                        Quick Actions
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="btn-group" role="group">
                                        @if(in_array(auth()->user()->role, ['admin', 'hr']))
                                        <a href="{{ route('performance.kpi') }}" class="btn btn-outline-primary">
                                            <i class="fas fa-target mr-1"></i> Manage KPIs
                                        </a>
                                        @endif
                                        @if(in_array(auth()->user()->role, ['admin', 'hr', 'manager']))
                                        <a href="{{ route('performance.appraisal') }}" class="btn btn-outline-success">
                                            <i class="fas fa-clipboard-check mr-1"></i> Performance Appraisal
                                        </a>
                                        @endif
                                        @if(in_array(auth()->user()->role, ['admin', 'hr']))
                                        <a href="{{ route('performance.bonus') }}" class="btn btn-outline-warning">
                                            <i class="fas fa-money-bill-wave mr-1"></i> Performance Bonus
                                        </a>
                                        @endif
                                        <a href="{{ route('performance.goals') }}" class="btn btn-outline-info">
                                            <i class="fas fa-bullseye mr-1"></i> Goal Setting
                                        </a>
                                        @if(in_array(auth()->user()->role, ['admin', 'hr']))
                                        <a href="{{ route('performance.reports') }}" class="btn btn-outline-secondary">
                                            <i class="fas fa-chart-pie mr-1"></i> Performance Reports
                                        </a>
                                        @endif
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

@push('styles')
<style>
.small-box {
    transition: transform 0.2s;
}

.small-box:hover {
    transform: translateY(-2px);
}

.progress {
    height: 25px;
}

.progress-bar {
    line-height: 25px;
    font-weight: bold;
}
</style>
@endpush 