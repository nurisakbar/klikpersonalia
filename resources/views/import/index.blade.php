@extends('layouts.app')

@section('title', 'Data Import')

@section('content')
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Data Import</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Data Import</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <!-- Import Results -->
            @if(session('import_results'))
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Import Results</h3>
                            </div>
                            <div class="card-body">
                                @php $results = session('import_results') @endphp
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-info"><i class="fas fa-list"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Total Records</span>
                                                <span class="info-box-number">{{ $results['total'] }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-success"><i class="fas fa-check"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Successful</span>
                                                <span class="info-box-number">{{ $results['success'] }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-danger"><i class="fas fa-times"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Failed</span>
                                                <span class="info-box-number">{{ $results['failed'] }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-warning"><i class="fas fa-percentage"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Success Rate</span>
                                                <span class="info-box-number">{{ $results['total'] > 0 ? round(($results['success'] / $results['total']) * 100, 1) : 0 }}%</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @if(!empty($results['errors']))
                                    <div class="alert alert-danger">
                                        <h5><i class="icon fas fa-exclamation-triangle"></i> Import Errors</h5>
                                        <ul class="mb-0">
                                            @foreach(array_slice($results['errors'], 0, 10) as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                            @if(count($results['errors']) > 10)
                                                <li>... and {{ count($results['errors']) - 10 }} more errors</li>
                                            @endif
                                        </ul>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Import Options -->
            <div class="row">
                <!-- Employee Import -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-users text-primary"></i> Employee Import
                            </h3>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('import.employees') }}" method="POST" enctype="multipart/form-data" id="employeeImportForm">
                                @csrf
                                <div class="form-group">
                                    <label for="employee_file">Select File</label>
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" id="employee_file" name="file" accept=".xlsx,.xls,.csv" required>
                                            <label class="custom-file-label" for="employee_file">Choose file</label>
                                        </div>
                                    </div>
                                    <small class="form-text text-muted">Supported formats: XLSX, XLS, CSV (Max: 2MB)</small>
                                </div>

                                <div class="form-group">
                                    <label for="employee_import_type">Import Type</label>
                                    <select class="form-control" id="employee_import_type" name="import_type" required>
                                        <option value="">Select Import Type</option>
                                        <option value="create">Create New Records Only</option>
                                        <option value="update">Update Existing Records Only</option>
                                        <option value="upsert">Create or Update Records</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="employee_skip_header" name="skip_header" value="1" checked>
                                        <label class="custom-control-label" for="employee_skip_header">Skip first row (header)</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <button type="button" class="btn btn-info btn-sm" onclick="validateFile('employee')">
                                        <i class="fas fa-check"></i> Validate File
                                    </button>
                                    <a href="{{ route('import.template', 'employees') }}" class="btn btn-success btn-sm">
                                        <i class="fas fa-download"></i> Download Template
                                    </a>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-upload"></i> Import Employees
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Payroll Import -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-calculator text-success"></i> Payroll Import
                            </h3>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('import.payroll') }}" method="POST" enctype="multipart/form-data" id="payrollImportForm">
                                @csrf
                                <div class="form-group">
                                    <label for="payroll_file">Select File</label>
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" id="payroll_file" name="file" accept=".xlsx,.xls,.csv" required>
                                            <label class="custom-file-label" for="payroll_file">Choose file</label>
                                        </div>
                                    </div>
                                    <small class="form-text text-muted">Supported formats: XLSX, XLS, CSV (Max: 2MB)</small>
                                </div>

                                <div class="form-group">
                                    <label for="payroll_import_type">Import Type</label>
                                    <select class="form-control" id="payroll_import_type" name="import_type" required>
                                        <option value="">Select Import Type</option>
                                        <option value="create">Create New Records Only</option>
                                        <option value="update">Update Existing Records Only</option>
                                        <option value="upsert">Create or Update Records</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="payroll_skip_header" name="skip_header" value="1" checked>
                                        <label class="custom-control-label" for="payroll_skip_header">Skip first row (header)</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <button type="button" class="btn btn-info btn-sm" onclick="validateFile('payroll')">
                                        <i class="fas fa-check"></i> Validate File
                                    </button>
                                    <a href="{{ route('import.template', 'payroll') }}" class="btn btn-success btn-sm">
                                        <i class="fas fa-download"></i> Download Template
                                    </a>
                                </div>

                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-upload"></i> Import Payroll
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attendance Import -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-clock text-warning"></i> Attendance Import
                            </h3>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('import.attendance') }}" method="POST" enctype="multipart/form-data" id="attendanceImportForm">
                                @csrf
                                <div class="form-group">
                                    <label for="attendance_file">Select File</label>
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" id="attendance_file" name="file" accept=".xlsx,.xls,.csv" required>
                                            <label class="custom-file-label" for="attendance_file">Choose file</label>
                                        </div>
                                    </div>
                                    <small class="form-text text-muted">Supported formats: XLSX, XLS, CSV (Max: 2MB)</small>
                                </div>

                                <div class="form-group">
                                    <label for="attendance_import_type">Import Type</label>
                                    <select class="form-control" id="attendance_import_type" name="import_type" required>
                                        <option value="">Select Import Type</option>
                                        <option value="create">Create New Records Only</option>
                                        <option value="update">Update Existing Records Only</option>
                                        <option value="upsert">Create or Update Records</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="attendance_skip_header" name="skip_header" value="1" checked>
                                        <label class="custom-control-label" for="attendance_skip_header">Skip first row (header)</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <button type="button" class="btn btn-info btn-sm" onclick="validateFile('attendance')">
                                        <i class="fas fa-check"></i> Validate File
                                    </button>
                                    <a href="{{ route('import.template', 'attendance') }}" class="btn btn-success btn-sm">
                                        <i class="fas fa-download"></i> Download Template
                                    </a>
                                </div>

                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-upload"></i> Import Attendance
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Import Guidelines -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-info-circle text-info"></i> Import Guidelines
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <h5><i class="icon fas fa-lightbulb"></i> Tips for Successful Import</h5>
                                <ul class="mb-0">
                                    <li>Use the provided templates to ensure correct format</li>
                                    <li>Validate your file before importing</li>
                                    <li>Ensure all required fields are filled</li>
                                    <li>Check date formats (YYYY-MM-DD)</li>
                                    <li>Use proper email formats</li>
                                    <li>Keep file size under 2MB</li>
                                </ul>
                            </div>

                            <div class="alert alert-warning">
                                <h5><i class="icon fas fa-exclamation-triangle"></i> Important Notes</h5>
                                <ul class="mb-0">
                                    <li>Import operations cannot be undone</li>
                                    <li>Backup your data before large imports</li>
                                    <li>Test with small files first</li>
                                    <li>Check import results carefully</li>
                                </ul>
                            </div>

                            <div class="alert alert-success">
                                <h5><i class="icon fas fa-check-circle"></i> Supported Features</h5>
                                <ul class="mb-0">
                                    <li>Excel (.xlsx, .xls) and CSV files</li>
                                    <li>Header row detection</li>
                                    <li>Data validation</li>
                                    <li>Error reporting</li>
                                    <li>Progress tracking</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Validation Modal -->
<div class="modal fade" id="validationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">File Validation Results</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="validationResults">
                <!-- Validation results will be displayed here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // File input change handler
    $('.custom-file-input').on('change', function() {
        let fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').html(fileName);
    });
});

function validateFile(type) {
    const fileInput = document.getElementById(type + '_file');
    const skipHeader = document.getElementById(type + '_skip_header').checked;
    
    if (!fileInput.files[0]) {
        toastr.error('Please select a file first.');
        return;
    }

    const formData = new FormData();
    formData.append('file', fileInput.files[0]);
    formData.append('skip_header', skipHeader);
    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

    // Show loading
    $('#validationResults').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i><p>Validating file...</p></div>');
    $('#validationModal').modal('show');

    $.ajax({
        url: '/import/validate',
        method: 'POST',
        errorHandled: true, // Mark as manually handled
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                const results = response.results;
                let html = `
                    <div class="row">
                        <div class="col-md-4">
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="fas fa-list"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Rows</span>
                                    <span class="info-box-number">${results.total_rows}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="fas fa-check"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Valid Rows</span>
                                    <span class="info-box-number">${results.valid_rows}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box">
                                <span class="info-box-icon bg-danger"><i class="fas fa-times"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Invalid Rows</span>
                                    <span class="info-box-number">${results.invalid_rows}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                if (results.errors.length > 0) {
                    html += `
                        <div class="alert alert-danger">
                            <h6>Validation Errors:</h6>
                            <ul class="mb-0">
                                ${results.errors.slice(0, 10).map(error => `<li>${error}</li>`).join('')}
                                ${results.errors.length > 10 ? `<li>... and ${results.errors.length - 10} more errors</li>` : ''}
                            </ul>
                        </div>
                    `;
                }

                if (results.valid_rows === results.total_rows) {
                    html += '<div class="alert alert-success"><i class="fas fa-check-circle"></i> All rows are valid! You can proceed with import.</div>';
                }

                $('#validationResults').html(html);
            } else {
                $('#validationResults').html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> Validation failed: ${response.message}
                    </div>
                `);
            }
        },
        error: function() {
            $('#validationResults').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> Validation failed. Please try again.
                </div>
            `);
        }
    });
}
</script>
@endpush 