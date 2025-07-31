@extends('layouts.app')

@section('title', 'Edit Tax Calculation')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-edit"></i> Edit Tax Calculation
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('taxes.show', $tax) }}" class="btn btn-info btn-sm">
                            <i class="fas fa-eye"></i> View Details
                        </a>
                        <a href="{{ route('taxes.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('taxes.update', $tax) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="employee_name">Employee</label>
                                    <input type="text" class="form-control" value="{{ $tax->employee->name }} ({{ $tax->employee->employee_id }})" readonly>
                                    <small class="form-text text-muted">Employee cannot be changed</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tax_period">Tax Period</label>
                                    <input type="text" class="form-control" value="{{ $tax->tax_period }}" readonly>
                                    <small class="form-text text-muted">Tax period cannot be changed</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="taxable_income">Taxable Income <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp</span>
                                        </div>
                                        <input type="number" name="taxable_income" id="taxable_income" 
                                               class="form-control @error('taxable_income') is-invalid @enderror" 
                                               value="{{ old('taxable_income', $tax->taxable_income) }}" 
                                               step="0.01" min="0" required 
                                               onchange="calculateTax()">
                                    </div>
                                    @error('taxable_income')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Total income subject to tax (Basic Salary + Allowances + Overtime + Bonus)
                                    </small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="ptkp_status">PTKP Status <span class="text-danger">*</span></label>
                                    <select name="ptkp_status" id="ptkp_status" 
                                            class="form-control @error('ptkp_status') is-invalid @enderror" 
                                            onchange="calculateTax()" required>
                                        <option value="">Select PTKP Status</option>
                                        @foreach($ptkpStatuses as $status => $description)
                                            <option value="{{ $status }}" {{ old('ptkp_status', $tax->ptkp_status) == $status ? 'selected' : '' }}>
                                                {{ $status }} - {{ $description }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('ptkp_status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status">Status <span class="text-danger">*</span></label>
                                    <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                                        <option value="pending" {{ old('status', $tax->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="calculated" {{ old('status', $tax->status) == 'calculated' ? 'selected' : '' }}>Calculated</option>
                                        <option value="paid" {{ old('status', $tax->status) == 'paid' ? 'selected' : '' }}>Paid</option>
                                        <option value="verified" {{ old('status', $tax->status) == 'verified' ? 'selected' : '' }}>Verified</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Tax Calculation Preview -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">
                                            <i class="fas fa-calculator"></i> Tax Calculation Preview
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
                                                        <span class="info-box-text">PTKP Amount</span>
                                                        <span class="info-box-number" id="ptkp_amount">Rp 0</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="info-box">
                                                    <span class="info-box-icon bg-warning">
                                                        <i class="fas fa-calculator"></i>
                                                    </span>
                                                    <div class="info-box-content">
                                                        <span class="info-box-text">Taxable Base</span>
                                                        <span class="info-box-number" id="taxable_base">Rp 0</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="info-box">
                                                    <span class="info-box-icon bg-danger">
                                                        <i class="fas fa-percentage"></i>
                                                    </span>
                                                    <div class="info-box-content">
                                                        <span class="info-box-text">Tax Rate</span>
                                                        <span class="info-box-number" id="tax_rate">0%</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="info-box">
                                                    <span class="info-box-icon bg-success">
                                                        <i class="fas fa-coins"></i>
                                                    </span>
                                                    <div class="info-box-content">
                                                        <span class="info-box-text">Tax Amount</span>
                                                        <span class="info-box-number" id="tax_amount">Rp 0</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="notes">Notes</label>
                                    <textarea name="notes" id="notes" rows="3" 
                                              class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $tax->notes) }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Tax Calculation
                                </button>
                                <a href="{{ route('taxes.show', $tax) }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// PTKP Amounts (2024)
const PTKP_AMOUNTS = {
    'TK/0': 54000000,
    'TK/1': 58500000,
    'TK/2': 63000000,
    'TK/3': 67500000,
    'K/0': 58500000,
    'K/1': 63000000,
    'K/2': 67500000,
    'K/3': 72000000,
};

// Tax Brackets (2024)
const TAX_BRACKETS = [
    {min: 0, max: 60000000, rate: 0.05},
    {min: 60000000, max: 250000000, rate: 0.15},
    {min: 250000000, max: 500000000, rate: 0.25},
    {min: 500000000, max: 5000000000, rate: 0.30},
    {min: 5000000000, max: null, rate: 0.35},
];

function calculateTax() {
    const taxableIncome = parseFloat(document.getElementById('taxable_income').value) || 0;
    const ptkpStatus = document.getElementById('ptkp_status').value;
    
    if (!ptkpStatus) {
        resetCalculation();
        return;
    }
    
    const ptkpAmount = PTKP_AMOUNTS[ptkpStatus] || 0;
    const taxableBase = Math.max(0, taxableIncome - ptkpAmount);
    
    // Calculate tax amount
    let taxAmount = 0;
    let taxRate = 0;
    
    for (const bracket of TAX_BRACKETS) {
        if (taxableBase > bracket.min) {
            const bracketMax = bracket.max || Number.MAX_SAFE_INTEGER;
            const bracketAmount = Math.min(taxableBase, bracketMax) - bracket.min;
            taxAmount += bracketAmount * bracket.rate;
            
            if (taxableBase <= bracketMax) {
                taxRate = bracket.rate;
                break;
            }
        }
    }
    
    // Update display
    document.getElementById('ptkp_amount').textContent = 'Rp ' + numberFormat(ptkpAmount);
    document.getElementById('taxable_base').textContent = 'Rp ' + numberFormat(taxableBase);
    document.getElementById('tax_rate').textContent = (taxRate * 100).toFixed(1) + '%';
    document.getElementById('tax_amount').textContent = 'Rp ' + numberFormat(taxAmount);
}

function resetCalculation() {
    document.getElementById('ptkp_amount').textContent = 'Rp 0';
    document.getElementById('taxable_base').textContent = 'Rp 0';
    document.getElementById('tax_rate').textContent = '0%';
    document.getElementById('tax_amount').textContent = 'Rp 0';
}

function numberFormat(number) {
    return new Intl.NumberFormat('id-ID').format(number);
}

// Calculate on page load
document.addEventListener('DOMContentLoaded', function() {
    calculateTax();
});
</script>
@endsection 