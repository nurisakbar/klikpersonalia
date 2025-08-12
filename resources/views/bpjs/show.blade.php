@extends('layouts.app')

@section('title', 'BPJS Record Details')

@section('content')
<div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>BPJS Record Details</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('bpjs.index') }}">BPJS Management</a></li>
                        <li class="breadcrumb-item active">BPJS Record Details</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-8">
                    <!-- BPJS Details Card -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                @if($bpjs->bpjs_type === 'kesehatan')
                                    <i class="fas fa-heartbeat text-info"></i> BPJS Kesehatan
                                @else
                                    <i class="fas fa-briefcase text-success"></i> BPJS Ketenagakerjaan
                                @endif
                                Record
                            </h3>
                            <div class="card-tools">
                                <a href="{{ route('bpjs.edit', $bpjs) }}" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="{{ route('bpjs.index') }}" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-arrow-left"></i> Back
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Employee:</strong></td>
                                            <td>{{ $bpjs->employee->name }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Employee ID:</strong></td>
                                            <td>{{ $bpjs->employee->employee_id }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>BPJS Type:</strong></td>
                                            <td>
                                                @if($bpjs->bpjs_type === 'kesehatan')
                                                    <span class="badge badge-info">
                                                        <i class="fas fa-heartbeat"></i> BPJS Kesehatan
                                                    </span>
                                                @else
                                                    <span class="badge badge-success">
                                                        <i class="fas fa-briefcase"></i> BPJS Ketenagakerjaan
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Period:</strong></td>
                                            <td>{{ \Carbon\Carbon::parse($bpjs->bpjs_period)->format('F Y') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Status:</strong></td>
                                            <td>
                                                @switch($bpjs->status)
                                                    @case('pending')
                                                        <span class="badge badge-warning">Pending</span>
                                                        @break
                                                    @case('calculated')
                                                        <span class="badge badge-info">Calculated</span>
                                                        @break
                                                    @case('paid')
                                                        <span class="badge badge-success">Paid</span>
                                                        @break
                                                    @case('verified')
                                                        <span class="badge badge-primary">Verified</span>
                                                        @break
                                                @endswitch
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Base Salary:</strong></td>
                                            <td>Rp {{ number_format($bpjs->base_salary, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Employee Rate:</strong></td>
                                            <td>{{ number_format($bpjs->contribution_rate_employee * 100, 2) }}%</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Company Rate:</strong></td>
                                            <td>{{ number_format($bpjs->contribution_rate_company * 100, 2) }}%</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Created:</strong></td>
                                            <td>{{ $bpjs->created_at->format('d M Y H:i') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Updated:</strong></td>
                                            <td>{{ $bpjs->updated_at->format('d M Y H:i') }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            @if($bpjs->notes)
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <h6>Notes:</h6>
                                        <p class="text-muted">{{ $bpjs->notes }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Contribution Details Card -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Contribution Details</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="info-box bg-info">
                                        <span class="info-box-icon"><i class="fas fa-user"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Employee Contribution</span>
                                            <span class="info-box-number">Rp {{ number_format($bpjs->employee_contribution, 0, ',', '.') }}</span>
                                            <div class="progress">
                                                <div class="progress-bar" style="width: 100%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="info-box bg-success">
                                        <span class="info-box-icon"><i class="fas fa-building"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Company Contribution</span>
                                            <span class="info-box-number">Rp {{ number_format($bpjs->company_contribution, 0, ',', '.') }}</span>
                                            <div class="progress">
                                                <div class="progress-bar" style="width: 100%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="info-box bg-primary">
                                        <span class="info-box-icon"><i class="fas fa-calculator"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Total Contribution</span>
                                            <span class="info-box-number">Rp {{ number_format($bpjs->total_contribution, 0, ',', '.') }}</span>
                                            <div class="progress">
                                                <div class="progress-bar" style="width: 100%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if($bpjs->bpjs_type === 'ketenagakerjaan')
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <h6>BPJS Ketenagakerjaan Breakdown:</h6>
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>Component</th>
                                                        <th>Employee</th>
                                                        <th>Company</th>
                                                        <th>Total</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php
                                                        $baseSalary = $bpjs->base_salary;
                                                        $jhtEmployee = $baseSalary * 0.02;
                                                        $jhtCompany = $baseSalary * 0.037;
                                                        $jkkCompany = $baseSalary * 0.0024;
                                                        $jkmCompany = $baseSalary * 0.003;
                                                        $jpEmployee = $baseSalary * 0.01;
                                                        $jpCompany = $baseSalary * 0.02;
                                                    @endphp
                                                    <tr>
                                                        <td><strong>JHT (Jaminan Hari Tua)</strong></td>
                                                        <td>Rp {{ number_format($jhtEmployee, 0, ',', '.') }}</td>
                                                        <td>Rp {{ number_format($jhtCompany, 0, ',', '.') }}</td>
                                                        <td>Rp {{ number_format($jhtEmployee + $jhtCompany, 0, ',', '.') }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>JKK (Jaminan Kecelakaan Kerja)</strong></td>
                                                        <td>-</td>
                                                        <td>Rp {{ number_format($jkkCompany, 0, ',', '.') }}</td>
                                                        <td>Rp {{ number_format($jkkCompany, 0, ',', '.') }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>JKM (Jaminan Kematian)</strong></td>
                                                        <td>-</td>
                                                        <td>Rp {{ number_format($jkmCompany, 0, ',', '.') }}</td>
                                                        <td>Rp {{ number_format($jkmCompany, 0, ',', '.') }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>JP (Jaminan Pensiun)</strong></td>
                                                        <td>Rp {{ number_format($jpEmployee, 0, ',', '.') }}</td>
                                                        <td>Rp {{ number_format($jpCompany, 0, ',', '.') }}</td>
                                                        <td>Rp {{ number_format($jpEmployee + $jpCompany, 0, ',', '.') }}</td>
                                                    </tr>
                                                    <tr class="table-active">
                                                        <td><strong>TOTAL</strong></td>
                                                        <td><strong>Rp {{ number_format($bpjs->employee_contribution, 0, ',', '.') }}</strong></td>
                                                        <td><strong>Rp {{ number_format($bpjs->company_contribution, 0, ',', '.') }}</strong></td>
                                                        <td><strong>Rp {{ number_format($bpjs->total_contribution, 0, ',', '.') }}</strong></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <!-- Employee Information Card -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Employee Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <img src="https://via.placeholder.com/100x100?text={{ substr($bpjs->employee->name, 0, 1) }}" 
                                     class="img-circle" alt="Employee Photo">
                            </div>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Name:</strong></td>
                                    <td>{{ $bpjs->employee->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>ID:</strong></td>
                                    <td>{{ $bpjs->employee->employee_id }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Position:</strong></td>
                                    <td>{{ $bpjs->employee->position }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Department:</strong></td>
                                    <td>{{ $bpjs->employee->department }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Basic Salary:</strong></td>
                                    <td>Rp {{ number_format($bpjs->employee->basic_salary, 0, ',', '.') }}</td>
                                </tr>
                            </table>

                            <hr>

                            <h6>BPJS Status:</h6>
                            <div class="row">
                                <div class="col-6">
                                    <small>BPJS Kesehatan:</small><br>
                                    @if($bpjs->employee->bpjs_kesehatan_active)
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-secondary">Inactive</span>
                                    @endif
                                </div>
                                <div class="col-6">
                                    <small>BPJS Ketenagakerjaan:</small><br>
                                    @if($bpjs->employee->bpjs_ketenagakerjaan_active)
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-secondary">Inactive</span>
                                    @endif
                                </div>
                            </div>

                            @if($bpjs->employee->bpjs_kesehatan_number)
                                <div class="mt-2">
                                    <small>BPJS Kesehatan Number:</small><br>
                                    <strong>{{ $bpjs->employee->bpjs_kesehatan_number }}</strong>
                                </div>
                            @endif

                            @if($bpjs->employee->bpjs_ketenagakerjaan_number)
                                <div class="mt-2">
                                    <small>BPJS Ketenagakerjaan Number:</small><br>
                                    <strong>{{ $bpjs->employee->bpjs_ketenagakerjaan_number }}</strong>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Payment Information Card -->
                    @if($bpjs->payment_date)
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Payment Information</h3>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Payment Date:</strong></td>
                                        <td>{{ \Carbon\Carbon::parse($bpjs->payment_date)->format('d M Y') }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Status:</strong></td>
                                        <td>
                                            @switch($bpjs->status)
                                                @case('paid')
                                                    <span class="badge badge-success">Paid</span>
                                                    @break
                                                @case('verified')
                                                    <span class="badge badge-primary">Verified</span>
                                                    @break
                                                @default
                                                    <span class="badge badge-warning">{{ ucfirst($bpjs->status) }}</span>
                                            @endswitch
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    @endif

                    <!-- Related Payroll Card -->
                    @if($bpjs->payroll)
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Related Payroll</h3>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Payroll Period:</strong></td>
                                        <td>{{ \Carbon\Carbon::parse($bpjs->payroll->payroll_period)->format('F Y') }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Basic Salary:</strong></td>
                                        <td>Rp {{ number_format($bpjs->payroll->basic_salary, 0, ',', '.') }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Total Salary:</strong></td>
                                        <td>Rp {{ number_format($bpjs->payroll->total_salary, 0, ',', '.') }}</td>
                                    </tr>
                                </table>
                                <a href="{{ route('payrolls.show', $bpjs->payroll) }}" class="btn btn-info btn-sm btn-block">
                                    <i class="fas fa-eye"></i> View Payroll Details
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
</div>
@endsection 