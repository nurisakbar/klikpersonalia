@extends('layouts.app')

@section('title', 'Assignment Komponen Gaji Karyawan')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Assignment Komponen Gaji Karyawan</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item">Master Data</li>
                    <li class="breadcrumb-item active">Assignment Komponen</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Summary Cards -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $employees->count() }}</h3>
                        <p>Total Karyawan</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $employees->sum(function($emp) { return $emp->salaryComponents->where('is_active', true)->count(); }) }}</h3>
                        <p>Total Assignment Aktif</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-link"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $employees->sum(function($emp) { return $emp->earningComponents->where('is_active', true)->count(); }) }}</h3>
                        <p>Total Pendapatan</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-plus-circle"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ $employees->sum(function($emp) { return $emp->deductionComponents->where('is_active', true)->count(); }) }}</h3>
                        <p>Total Potongan</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-minus-circle"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Daftar Assignment Komponen Gaji</h3>
                        <div class="card-tools">
                            <a href="{{ route('employee-salary-components.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> Assign Komponen
                            </a>
                            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#bulkAssignModal">
                                <i class="fas fa-layer-group"></i> Bulk Assign
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="employee-components-table">
                                <thead>
                                    <tr>
                                        <th width="50">No</th>
                                        <th width="200">Nama Karyawan</th>
                                        <th width="200">Komponen Gaji</th>
                                        <th width="100">Tipe</th>
                                        <th width="150">Nilai</th>
                                        <th width="120">Perhitungan</th>
                                        <th width="120">Referensi</th>
                                        <th width="100">Status</th>
                                        <th width="120">Tanggal Efektif</th>
                                        <th width="120">Tanggal Expired</th>
                                        <th width="150">Aksi</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Employee Components Summary -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Ringkasan per Karyawan</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($employees as $employee)
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="card border-primary">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="card-title mb-0">{{ $employee->name }}</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-6">
                                                <small class="text-muted">Gaji Pokok:</small><br>
                                                <strong>Rp {{ number_format($employee->basic_salary, 0, ',', '.') }}</strong>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted">Total Komponen:</small><br>
                                                <strong>{{ $employee->salaryComponents->where('is_active', true)->count() }}</strong>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="row">
                                            <div class="col-6">
                                                <small class="text-success">Pendapatan:</small><br>
                                                <strong class="text-success">{{ $employee->earningComponents->where('is_active', true)->count() }}</strong>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-danger">Potongan:</small><br>
                                                <strong class="text-danger">{{ $employee->deductionComponents->where('is_active', true)->count() }}</strong>
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <button type="button" class="btn btn-info btn-sm btn-block" 
                                                    onclick="viewEmployeeComponents('{{ $employee->id }}')">
                                                <i class="fas fa-eye"></i> Lihat Detail
                                            </button>
                                        </div>
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
</section>

<!-- Bulk Assign Modal -->
<div class="modal fade" id="bulkAssignModal" tabindex="-1" aria-labelledby="bulkAssignModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkAssignModalLabel">Bulk Assign Komponen Gaji</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('employee-salary-components.bulk-assign') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="bulk_employee_ids">Pilih Karyawan <span class="text-danger">*</span></label>
                                <select name="employee_ids[]" id="bulk_employee_ids" class="form-control select2" multiple required>
                                    @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="bulk_salary_component_id">Komponen Gaji <span class="text-danger">*</span></label>
                                <select name="salary_component_id" id="bulk_salary_component_id" class="form-control" required>
                                    <option value="">Pilih Komponen</option>
                                    @foreach(App\Models\SalaryComponent::where('company_id', Auth::user()->company_id)->where('is_active', true)->get() as $component)
                                    <option value="{{ $component->id }}">{{ $component->name }} ({{ $component->type_text }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="bulk_amount">Nilai <span class="text-danger">*</span></label>
                                <input type="number" name="amount" id="bulk_amount" class="form-control" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="bulk_calculation_type">Tipe Perhitungan <span class="text-danger">*</span></label>
                                <select name="calculation_type" id="bulk_calculation_type" class="form-control" required>
                                    <option value="fixed">Nilai Tetap</option>
                                    <option value="percentage">Persentase</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row" id="bulk_percentage_fields" style="display: none;">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="bulk_percentage_value">Nilai Persentase (%)</label>
                                <input type="number" name="percentage_value" id="bulk_percentage_value" class="form-control" step="0.01" min="0" max="100">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="bulk_reference_type">Referensi Gaji</label>
                                <select name="reference_type" id="bulk_reference_type" class="form-control">
                                    <option value="">Pilih Referensi</option>
                                    <option value="basic_salary">Gaji Pokok</option>
                                    <option value="gross_salary">Gaji Kotor</option>
                                    <option value="net_salary">Gaji Bersih</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="bulk_effective_date">Tanggal Efektif</label>
                                <input type="date" name="effective_date" id="bulk_effective_date" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="bulk_expiry_date">Tanggal Expired</label>
                                <input type="date" name="expiry_date" id="bulk_expiry_date" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="bulk_is_active" name="is_active" value="1" checked>
                            <label class="custom-control-label" for="bulk_is_active">Aktif</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Assign ke Semua Karyawan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Employee Components Detail Modal -->
<div class="modal fade" id="employeeComponentsModal" tabindex="-1" aria-labelledby="employeeComponentsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="employeeComponentsModalLabel">Detail Komponen Gaji Karyawan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="employeeComponentsContent">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.6.2/dist/select2-bootstrap4.min.css">
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize DataTable
    var table = $('#employee-components-table').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: '{{ route("employee-salary-components.data") }}',
            type: 'GET',
            dataSrc: 'data'
        },
        columns: [
            {data: null, name: 'no', orderable: false, searchable: false, width: '50px'},
            {data: 'employee_name', name: 'employee_name', width: '200px'},
            {data: 'component_name', name: 'component_name', width: '200px'},
            {data: 'component_type', name: 'component_type', width: '100px'},
            {data: 'amount', name: 'amount', width: '150px'},
            {data: 'calculation_type', name: 'calculation_type', width: '120px'},
            {data: 'reference_type', name: 'reference_type', width: '120px'},
            {data: 'is_active', name: 'is_active', width: '100px'},
            {data: 'effective_date', name: 'effective_date', width: '120px'},
            {data: 'expiry_date', name: 'expiry_date', width: '120px'},
            {data: 'action', name: 'action', orderable: false, searchable: false, width: '150px'}
        ],
        order: [[0, 'asc']],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
        },
        createdRow: function(row, data, dataIndex) {
            // Add row number
            $('td:eq(0)', row).html(dataIndex + 1);
            
            // Add color coding for component types
            if (data.component_type === 'earning') {
                $(row).addClass('table-success');
            } else if (data.component_type === 'deduction') {
                $(row).addClass('table-danger');
            }
        }
    });

    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%'
    });

    // Handle calculation type change for bulk assign
    $('#bulk_calculation_type').on('change', function() {
        if ($(this).val() === 'percentage') {
            $('#bulk_percentage_fields').show();
            $('#bulk_percentage_value, #bulk_reference_type').prop('required', true);
        } else {
            $('#bulk_percentage_fields').hide();
            $('#bulk_percentage_value, #bulk_reference_type').prop('required', false);
        }
    });
});

// Function to view employee components
function viewEmployeeComponents(employeeId) {
    $.get('{{ route("employee-salary-components.employee", ":id") }}'.replace(':id', employeeId))
        .done(function(response) {
            let content = `
                <div class="row">
                    <div class="col-md-6">
                        <h6>Informasi Karyawan</h6>
                        <table class="table table-sm">
                            <tr><td>Nama:</td><td><strong>${response.employee.name}</strong></td></tr>
                            <tr><td>Gaji Pokok:</td><td><strong>Rp ${Number(response.employee.basic_salary).toLocaleString('id-ID')}</strong></td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>Ringkasan</h6>
                        <table class="table table-sm">
                            <tr><td>Total Komponen:</td><td><strong>${response.components.length}</strong></td></tr>
                            <tr><td>Pendapatan:</td><td><strong class="text-success">${response.components.filter(c => c.component_type === 'earning').length}</strong></td></tr>
                            <tr><td>Potongan:</td><td><strong class="text-danger">${response.components.filter(c => c.component_type === 'deduction').length}</strong></td></tr>
                        </table>
                    </div>
                </div>
                <hr>
                <h6>Daftar Komponen Gaji</h6>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>Komponen</th>
                                <th>Tipe</th>
                                <th>Nilai</th>
                                <th>Perhitungan</th>
                                <th>Status</th>
                                <th>Efektif</th>
                                <th>Expired</th>
                            </tr>
                        </thead>
                        <tbody>`;
            
            response.components.forEach(function(component) {
                const typeClass = component.component_type === 'earning' ? 'text-success' : 'text-danger';
                const typeText = component.component_type === 'earning' ? 'Pendapatan' : 'Potongan';
                const statusClass = component.is_active ? 'badge badge-success' : 'badge badge-danger';
                const statusText = component.is_active ? 'Aktif' : 'Tidak Aktif';
                
                content += `
                    <tr>
                        <td><strong>${component.component_name}</strong></td>
                        <td><span class="${typeClass}">${typeText}</span></td>
                        <td>${component.amount}</td>
                        <td>${component.calculation_type}</td>
                        <td><span class="${statusClass}">${statusText}</span></td>
                        <td>${component.effective_date}</td>
                        <td>${component.expiry_date}</td>
                    </tr>`;
            });
            
            content += `
                        </tbody>
                    </table>
                </div>`;
            
            $('#employeeComponentsContent').html(content);
            $('#employeeComponentsModal').modal('show');
        })
        .fail(function() {
            alert('Gagal memuat data komponen gaji karyawan.');
        });
}
</script>
@endpush
