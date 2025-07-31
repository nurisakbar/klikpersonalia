@extends('layouts.app')

@section('title', 'Export Data')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-download mr-2"></i>
                        Export Data
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left mr-1"></i> Back to Dashboard
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Export Statistics -->
                    <div class="row mb-4">
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3>Excel</h3>
                                    <p>Spreadsheet Format</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-file-excel"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-danger">
                                <div class="inner">
                                    <h3>PDF</h3>
                                    <p>Document Format</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-file-pdf"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3>Bulk</h3>
                                    <p>Multiple Exports</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-download"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3>Custom</h3>
                                    <p>Filtered Data</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-filter"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Individual Export Options -->
                    <div class="row">
                        <!-- Employee Data Export -->
                        <div class="col-lg-4 col-md-6">
                            <div class="card card-primary card-outline">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-users mr-2"></i>
                                        Employee Data
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p class="card-text">Export complete employee information including personal details, salary, and status.</p>
                                    <div class="btn-group w-100" role="group">
                                        <a href="{{ route('exports.employees') }}?format=xlsx" class="btn btn-outline-success">
                                            <i class="fas fa-file-excel mr-1"></i> Excel
                                        </a>
                                        <a href="{{ route('exports.employees') }}?format=pdf" class="btn btn-outline-danger">
                                            <i class="fas fa-file-pdf mr-1"></i> PDF
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payroll Data Export -->
                        <div class="col-lg-4 col-md-6">
                            <div class="card card-success card-outline">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-money-bill-wave mr-2"></i>
                                        Payroll Data
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p class="card-text">Export payroll information including salary components, deductions, and net pay.</p>
                                    <div class="btn-group w-100" role="group">
                                        <a href="{{ route('exports.payrolls') }}?format=xlsx" class="btn btn-outline-success">
                                            <i class="fas fa-file-excel mr-1"></i> Excel
                                        </a>
                                        <a href="{{ route('exports.payrolls') }}?format=pdf" class="btn btn-outline-danger">
                                            <i class="fas fa-file-pdf mr-1"></i> PDF
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Attendance Data Export -->
                        <div class="col-lg-4 col-md-6">
                            <div class="card card-info card-outline">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-clock mr-2"></i>
                                        Attendance Data
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p class="card-text">Export attendance records including check-in/out times and working hours.</p>
                                    <div class="btn-group w-100" role="group">
                                        <a href="{{ route('exports.attendance') }}?format=xlsx" class="btn btn-outline-success">
                                            <i class="fas fa-file-excel mr-1"></i> Excel
                                        </a>
                                        <a href="{{ route('exports.attendance') }}?format=pdf" class="btn btn-outline-danger">
                                            <i class="fas fa-file-pdf mr-1"></i> PDF
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Leave Data Export -->
                        <div class="col-lg-4 col-md-6">
                            <div class="card card-warning card-outline">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-calendar-times mr-2"></i>
                                        Leave Data
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p class="card-text">Export leave records including leave types, dates, and approval status.</p>
                                    <div class="btn-group w-100" role="group">
                                        <a href="{{ route('exports.leaves') }}?format=xlsx" class="btn btn-outline-success">
                                            <i class="fas fa-file-excel mr-1"></i> Excel
                                        </a>
                                        <a href="{{ route('exports.leaves') }}?format=pdf" class="btn btn-outline-danger">
                                            <i class="fas fa-file-pdf mr-1"></i> PDF
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tax Data Export -->
                        <div class="col-lg-4 col-md-6">
                            <div class="card card-secondary card-outline">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-calculator mr-2"></i>
                                        Tax Data
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p class="card-text">Export tax calculations including PPh 21, PTKP, and tax brackets information.</p>
                                    <div class="btn-group w-100" role="group">
                                        <a href="{{ route('exports.taxes') }}?format=xlsx" class="btn btn-outline-success">
                                            <i class="fas fa-file-excel mr-1"></i> Excel
                                        </a>
                                        <a href="{{ route('exports.taxes') }}?format=pdf" class="btn btn-outline-danger">
                                            <i class="fas fa-file-pdf mr-1"></i> PDF
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- BPJS Data Export -->
                        <div class="col-lg-4 col-md-6">
                            <div class="card card-danger card-outline">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-heartbeat mr-2"></i>
                                        BPJS Data
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p class="card-text">Export BPJS contributions including Kesehatan and Ketenagakerjaan data.</p>
                                    <div class="btn-group w-100" role="group">
                                        <a href="{{ route('exports.bpjs') }}?format=xlsx" class="btn btn-outline-success">
                                            <i class="fas fa-file-excel mr-1"></i> Excel
                                        </a>
                                        <a href="{{ route('exports.bpjs') }}?format=pdf" class="btn btn-outline-danger">
                                            <i class="fas fa-file-pdf mr-1"></i> PDF
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bulk Export Section -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-download mr-2"></i>
                                        Bulk Export
                                    </h5>
                                    <small class="text-muted">Export multiple data types at once</small>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('exports.all') }}" method="POST">
                                        @csrf
                                        <div class="row">
                                            <div class="col-md-8">
                                                <div class="form-group">
                                                    <label class="form-label">Select Data Types to Export:</label>
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <div class="custom-control custom-checkbox">
                                                                <input type="checkbox" class="custom-control-input" id="employees" name="data_types[]" value="employees" checked>
                                                                <label class="custom-control-label" for="employees">Employees</label>
                                                            </div>
                                                            <div class="custom-control custom-checkbox">
                                                                <input type="checkbox" class="custom-control-input" id="payrolls" name="data_types[]" value="payrolls">
                                                                <label class="custom-control-label" for="payrolls">Payrolls</label>
                                                            </div>
                                                            <div class="custom-control custom-checkbox">
                                                                <input type="checkbox" class="custom-control-input" id="attendance" name="data_types[]" value="attendance">
                                                                <label class="custom-control-label" for="attendance">Attendance</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="custom-control custom-checkbox">
                                                                <input type="checkbox" class="custom-control-input" id="leaves" name="data_types[]" value="leaves">
                                                                <label class="custom-control-label" for="leaves">Leaves</label>
                                                            </div>
                                                            <div class="custom-control custom-checkbox">
                                                                <input type="checkbox" class="custom-control-input" id="taxes" name="data_types[]" value="taxes">
                                                                <label class="custom-control-label" for="taxes">Taxes</label>
                                                            </div>
                                                            <div class="custom-control custom-checkbox">
                                                                <input type="checkbox" class="custom-control-input" id="bpjs" name="data_types[]" value="bpjs">
                                                                <label class="custom-control-label" for="bpjs">BPJS</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="form-label">Export Format:</label>
                                                    <div class="btn-group w-100" role="group">
                                                        <input type="radio" class="btn-check" name="format" id="format_xlsx" value="xlsx" checked>
                                                        <label class="btn btn-outline-success" for="format_xlsx">
                                                            <i class="fas fa-file-excel mr-1"></i> Excel
                                                        </label>
                                                        <input type="radio" class="btn-check" name="format" id="format_pdf" value="pdf">
                                                        <label class="btn btn-outline-danger" for="format_pdf">
                                                            <i class="fas fa-file-pdf mr-1"></i> PDF
                                                        </label>
                                                    </div>
                                                </div>
                                                <button type="submit" class="btn btn-primary btn-block">
                                                    <i class="fas fa-download mr-1"></i> Export All Selected
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Export Information -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <h6><i class="fas fa-info-circle mr-2"></i>Export Information</h6>
                                <ul class="mb-0">
                                    <li><strong>Excel Format:</strong> Suitable for data analysis, calculations, and further processing</li>
                                    <li><strong>PDF Format:</strong> Suitable for printing, sharing, and official documentation</li>
                                    <li><strong>Data Range:</strong> All exports include data from your company only</li>
                                    <li><strong>File Naming:</strong> Files are automatically named with timestamp for easy identification</li>
                                    <li><strong>Security:</strong> All exports respect your role-based access permissions</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Exports -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-history mr-2"></i>
                                        Export History
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Data Type</th>
                                                    <th>Format</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted">
                                                        <i class="fas fa-info-circle mr-1"></i>
                                                        Export history will be displayed here
                                                    </td>
                                                </tr>
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
</div>
@endsection

@push('styles')
<style>
.small-box {
    position: relative;
    display: block;
    margin-bottom: 20px;
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    border-radius: 0.25rem;
}

.small-box > .inner {
    padding: 10px;
}

.small-box h3 {
    font-size: 2.2rem;
    font-weight: 700;
    margin: 0 0 10px 0;
    white-space: nowrap;
    padding: 0;
}

.small-box p {
    font-size: 1rem;
    margin-bottom: 0;
}

.small-box .icon {
    color: rgba(0,0,0,.15);
    z-index: 0;
}

.small-box .icon > i {
    font-size: 70px;
    position: absolute;
    right: 15px;
    top: 15px;
    transition: transform .3s linear;
}

.small-box:hover .icon > i {
    transform: scale(1.1);
}

.bg-info {
    background-color: #17a2b8 !important;
}

.bg-danger {
    background-color: #dc3545 !important;
}

.bg-success {
    background-color: #28a745 !important;
}

.bg-warning {
    background-color: #ffc107 !important;
}

.small-box > .inner {
    color: #fff;
}

.small-box .icon > i {
    color: #fff;
}

.btn-check:checked + .btn-outline-success {
    background-color: #28a745;
    border-color: #28a745;
    color: #fff;
}

.btn-check:checked + .btn-outline-danger {
    background-color: #dc3545;
    border-color: #dc3545;
    color: #fff;
}
</style>
@endpush

@push('scripts')
<script>
// Select all checkbox functionality
document.addEventListener('DOMContentLoaded', function() {
    // Add select all functionality
    const selectAllCheckbox = document.createElement('input');
    selectAllCheckbox.type = 'checkbox';
    selectAllCheckbox.id = 'select_all';
    selectAllCheckbox.className = 'custom-control-input';
    
    const selectAllLabel = document.createElement('label');
    selectAllLabel.className = 'custom-control-label';
    selectAllLabel.htmlFor = 'select_all';
    selectAllLabel.textContent = 'Select All';
    
    const selectAllDiv = document.createElement('div');
    selectAllDiv.className = 'custom-control custom-checkbox mb-2';
    selectAllDiv.appendChild(selectAllCheckbox);
    selectAllDiv.appendChild(selectAllLabel);
    
    const firstColumn = document.querySelector('.col-md-4');
    firstColumn.insertBefore(selectAllDiv, firstColumn.firstChild);
    
    // Select all functionality
    selectAllCheckbox.addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('input[name="data_types[]"]');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });
    
    // Update select all when individual checkboxes change
    const checkboxes = document.querySelectorAll('input[name="data_types[]"]');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            const anyChecked = Array.from(checkboxes).some(cb => cb.checked);
            
            selectAllCheckbox.checked = allChecked;
            selectAllCheckbox.indeterminate = anyChecked && !allChecked;
        });
    });
});
</script>
@endpush 