@extends('layouts.app')

@section('title', 'Tax Calculation Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-calculator"></i> Tax Calculation Details
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('taxes.edit', $tax) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="{{ route('taxes.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Employee Information -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-user"></i> Employee Information
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td width="40%"><strong>Employee Name:</strong></td>
                                            <td>{{ $tax->employee->name }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Employee ID:</strong></td>
                                            <td>{{ $tax->employee->employee_id }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Position:</strong></td>
                                            <td>{{ $tax->employee->position }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Department:</strong></td>
                                            <td>{{ $tax->employee->department }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>PTKP Status:</strong></td>
                                            <td>
                                                <span class="badge badge-info">{{ $tax->ptkp_status }}</span>
                                                <br>
                                                <small class="text-muted">{{ \App\Models\Tax::PTKP_STATUSES[$tax->ptkp_status] ?? 'Unknown' }}</small>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Tax Period Information -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-calendar"></i> Tax Period Information
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td width="40%"><strong>Tax Period:</strong></td>
                                            <td>
                                                <span class="badge badge-primary">{{ $tax->tax_period }}</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Status:</strong></td>
                                            <td>
                                                @switch($tax->status)
                                                    @case('pending')
                                                        <span class="badge badge-secondary">Pending</span>
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
                                        <tr>
                                            <td><strong>Created:</strong></td>
                                            <td>{{ $tax->created_at->format('d M Y H:i') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Last Updated:</strong></td>
                                            <td>{{ $tax->updated_at->format('d M Y H:i') }}</td>
                                        </tr>
                                        @if($tax->payroll)
                                        <tr>
                                            <td><strong>Related Payroll:</strong></td>
                                            <td>
                                                <a href="{{ route('payrolls.show', $tax->payroll) }}" class="btn btn-sm btn-outline-info">
                                                    View Payroll
                                                </a>
                                            </td>
                                        </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tax Calculation Details -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-calculator"></i> Tax Calculation Details
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-info">
                                                    <i class="fas fa-money-bill"></i>
                                                </span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Taxable Income</span>
                                                    <span class="info-box-number">Rp {{ number_format($tax->taxable_income, 0, ',', '.') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-warning">
                                                    <i class="fas fa-shield-alt"></i>
                                                </span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">PTKP Amount</span>
                                                    <span class="info-box-number">Rp {{ number_format($tax->ptkp_amount, 0, ',', '.') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-success">
                                                    <i class="fas fa-calculator"></i>
                                                </span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Taxable Base</span>
                                                    <span class="info-box-number">Rp {{ number_format($tax->taxable_base, 0, ',', '.') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-danger">
                                                    <i class="fas fa-coins"></i>
                                                </span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Tax Amount</span>
                                                    <span class="info-box-number">Rp {{ number_format($tax->tax_amount, 0, ',', '.') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Detailed Calculation -->
                                    <div class="row mt-4">
                                        <div class="col-md-6">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h6 class="card-title">Calculation Breakdown</h6>
                                                </div>
                                                <div class="card-body">
                                                    <table class="table table-sm">
                                                        <tr>
                                                            <td>Taxable Income:</td>
                                                            <td class="text-right">Rp {{ number_format($tax->taxable_income, 0, ',', '.') }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td>PTKP Amount ({{ $tax->ptkp_status }}):</td>
                                                            <td class="text-right text-danger">- Rp {{ number_format($tax->ptkp_amount, 0, ',', '.') }}</td>
                                                        </tr>
                                                        <tr class="table-active">
                                                            <td><strong>Taxable Base:</strong></td>
                                                            <td class="text-right"><strong>Rp {{ number_format($tax->taxable_base, 0, ',', '.') }}</strong></td>
                                                        </tr>
                                                        <tr>
                                                            <td>Tax Rate:</td>
                                                            <td class="text-right">{{ number_format($tax->tax_rate * 100, 1) }}%</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Tax Bracket:</td>
                                                            <td class="text-right">{{ $tax->tax_bracket }}</td>
                                                        </tr>
                                                        <tr class="table-danger">
                                                            <td><strong>Tax Amount:</strong></td>
                                                            <td class="text-right"><strong>Rp {{ number_format($tax->tax_amount, 0, ',', '.') }}</strong></td>
                                                        </tr>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h6 class="card-title">Tax Bracket Information</h6>
                                                </div>
                                                <div class="card-body">
                                                    <div class="alert alert-info">
                                                        <h6>Current Tax Bracket (2024)</h6>
                                                        <p class="mb-0">
                                                            <strong>{{ $tax->tax_bracket }}</strong><br>
                                                            Rate: <strong>{{ number_format($tax->tax_rate * 100, 1) }}%</strong>
                                                        </p>
                                                    </div>
                                                    
                                                    <h6>All Tax Brackets (2024):</h6>
                                                    <div class="table-responsive">
                                                        <table class="table table-sm table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th>Bracket</th>
                                                                    <th>Rate</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach(\App\Models\Tax::TAX_BRACKETS as $bracket)
                                                                    <tr class="{{ $tax->tax_bracket == ($bracket['min'] . ' - ' . ($bracket['max'] ?? '∞')) ? 'table-warning' : '' }}">
                                                                        <td>
                                                                            Rp {{ number_format($bracket['min'], 0, ',', '.') }} - 
                                                                            {{ $bracket['max'] ? 'Rp ' . number_format($bracket['max'], 0, ',', '.') : '∞' }}
                                                                        </td>
                                                                        <td>{{ number_format($bracket['rate'] * 100, 1) }}%</td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    @if($tax->notes)
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-sticky-note"></i> Notes
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p class="mb-0">{{ $tax->notes }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="btn-group" role="group">
                                <a href="{{ route('taxes.edit', $tax) }}" class="btn btn-warning">
                                    <i class="fas fa-edit"></i> Edit Tax Calculation
                                </a>
                                <a href="{{ route('taxes.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Back to List
                                </a>
                                <form action="{{ route('taxes.destroy', $tax) }}" method="POST" class="d-inline" 
                                      onsubmit="return confirm('Are you sure you want to delete this tax calculation?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 