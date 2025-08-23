@extends('layouts.app')

@section('title', 'Edit Assignment Komponen Gaji')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Edit Assignment Komponen Gaji</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item">Master Data</li>
                    <li class="breadcrumb-item"><a href="{{ route('employee-salary-components.index') }}">Assignment Komponen</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Edit Assignment Komponen Gaji</h3>
                    </div>
                    <form action="{{ route('employee-salary-components.update', $employeeSalaryComponent) }}" method="POST" id="editAssignmentForm">
                        @csrf
                        @method('PUT')
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="employee_id">Karyawan</label>
                                        <input type="text" class="form-control" value="{{ $employeeSalaryComponent->employee->name }} - {{ $employeeSalaryComponent->employee->employee_id }}" readonly>
                                        <small class="form-text text-muted">Karyawan tidak dapat diubah</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="salary_component_id">Komponen Gaji</label>
                                        <input type="text" class="form-control" value="{{ $employeeSalaryComponent->salaryComponent->name }} ({{ $employeeSalaryComponent->salaryComponent->type_text }})" readonly>
                                        <small class="form-text text-muted">Komponen tidak dapat diubah</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="calculation_type">Tipe Perhitungan <span class="text-danger">*</span></label>
                                        <select name="calculation_type" id="calculation_type" class="form-control" required>
                                            <option value="fixed" {{ $employeeSalaryComponent->calculation_type === 'fixed' ? 'selected' : '' }}>Nilai Tetap</option>
                                            <option value="percentage" {{ $employeeSalaryComponent->calculation_type === 'percentage' ? 'selected' : '' }}>Persentase</option>
                                        </select>
                                        @error('calculation_type')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="amount">Nilai <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text" id="amount-prefix">{{ $employeeSalaryComponent->calculation_type === 'percentage' ? '%' : 'Rp' }}</span>
                                            </div>
                                            <input type="number" name="amount" id="amount" class="form-control" 
                                                   step="0.01" min="0" value="{{ $employeeSalaryComponent->amount }}" required>
                                        </div>
                                        @error('amount')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row" id="percentage_fields" style="display: {{ $employeeSalaryComponent->calculation_type === 'percentage' ? 'block' : 'none' }};">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="percentage_value">Nilai Persentase (%) <span class="text-danger">*</span></label>
                                        <input type="number" name="percentage_value" id="percentage_value" 
                                               class="form-control" step="0.01" min="0" max="100" 
                                               value="{{ $employeeSalaryComponent->percentage_value }}">
                                        @error('percentage_value')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="reference_type">Referensi Gaji <span class="text-danger">*</span></label>
                                        <select name="reference_type" id="reference_type" class="form-control">
                                            <option value="">Pilih Referensi</option>
                                            <option value="basic_salary" {{ $employeeSalaryComponent->reference_type === 'basic_salary' ? 'selected' : '' }}>Gaji Pokok</option>
                                            <option value="gross_salary" {{ $employeeSalaryComponent->reference_type === 'gross_salary' ? 'selected' : '' }}>Gaji Kotor</option>
                                            <option value="net_salary" {{ $employeeSalaryComponent->reference_type === 'net_salary' ? 'selected' : '' }}>Gaji Bersih</option>
                                        </select>
                                        @error('reference_type')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="effective_date">Tanggal Efektif</label>
                                        <input type="date" name="effective_date" id="effective_date" class="form-control" 
                                               value="{{ $employeeSalaryComponent->effective_date ? $employeeSalaryComponent->effective_date->format('Y-m-d') : '' }}">
                                        <small class="form-text text-muted">Kosongkan jika berlaku segera</small>
                                        @error('effective_date')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="expiry_date">Tanggal Expired</label>
                                        <input type="date" name="expiry_date" id="expiry_date" class="form-control" 
                                               value="{{ $employeeSalaryComponent->expiry_date ? $employeeSalaryComponent->expiry_date->format('Y-m-d') : '' }}">
                                        <small class="form-text text-muted">Kosongkan jika berlaku selamanya</small>
                                        @error('expiry_date')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="notes">Catatan</label>
                                <textarea name="notes" id="notes" class="form-control" rows="3" 
                                          placeholder="Tambahkan catatan jika diperlukan...">{{ $employeeSalaryComponent->notes }}</textarea>
                                @error('notes')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" 
                                           {{ $employeeSalaryComponent->is_active ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_active">Aktif</label>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Assignment
                            </button>
                            <a href="{{ route('employee-salary-components.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Current Assignment Info -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Informasi Assignment Saat Ini</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <tr><td>Karyawan:</td><td><strong>{{ $employeeSalaryComponent->employee->name }}</strong></td></tr>
                            <tr><td>Komponen:</td><td><strong>{{ $employeeSalaryComponent->salaryComponent->name }}</strong></td></tr>
                            <tr><td>Tipe:</td><td><span class="badge badge-{{ $employeeSalaryComponent->salaryComponent->type === 'earning' ? 'success' : 'danger' }}">{{ $employeeSalaryComponent->salaryComponent->type_text }}</span></td></tr>
                            <tr><td>Gaji Pokok:</td><td><strong>Rp {{ number_format($employeeSalaryComponent->employee->basic_salary, 0, ',', '.') }}</strong></td></tr>
                            <tr><td>Perhitungan:</td><td><strong>{{ $employeeSalaryComponent->calculation_type_text }}</strong></td></tr>
                            <tr><td>Nilai:</td><td><strong>{{ $employeeSalaryComponent->formatted_amount }}</strong></td></tr>
                            @if($employeeSalaryComponent->calculation_type === 'percentage')
                            <tr><td>Referensi:</td><td><strong>{{ $employeeSalaryComponent->reference_type_text }}</strong></td></tr>
                            @endif
                            <tr><td>Status:</td><td><span class="badge badge-{{ $employeeSalaryComponent->is_active ? 'success' : 'danger' }}">{{ $employeeSalaryComponent->is_active ? 'Aktif' : 'Tidak Aktif' }}</span></td></tr>
                            <tr><td>Efektif:</td><td><strong>{{ $employeeSalaryComponent->effective_date ? $employeeSalaryComponent->effective_date->format('d/m/Y') : 'Sekarang' }}</strong></td></tr>
                            <tr><td>Expired:</td><td><strong>{{ $employeeSalaryComponent->expiry_date ? $employeeSalaryComponent->expiry_date->format('d/m/Y') : 'Selamanya' }}</strong></td></tr>
                        </table>
                    </div>
                </div>

                <!-- Help Card -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Bantuan</h3>
                    </div>
                    <div class="card-body">
                        <h6>Nilai Tetap:</h6>
                        <p class="text-sm">Komponen dengan nilai yang sama setiap periode gaji</p>
                        
                        <h6>Persentase:</h6>
                        <p class="text-sm">Komponen yang dihitung berdasarkan persentase dari gaji referensi</p>
                        
                        <h6>Tanggal Efektif:</h6>
                        <p class="text-sm">Tanggal mulai berlakunya komponen gaji</p>
                        
                        <h6>Tanggal Expired:</h6>
                        <p class="text-sm">Tanggal berakhirnya komponen gaji</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Handle calculation type change
    $('#calculation_type').on('change', function() {
        if ($(this).val() === 'percentage') {
            $('#percentage_fields').show();
            $('#percentage_value, #reference_type').prop('required', true);
            $('#amount-prefix').text('%');
        } else {
            $('#percentage_fields').hide();
            $('#percentage_value, #reference_type').prop('required', false);
            $('#amount-prefix').text('Rp');
        }
    });

    // Form validation
    $('#editAssignmentForm').on('submit', function(e) {
        const calculationType = $('#calculation_type').val();
        
        if (calculationType === 'percentage') {
            const percentageValue = $('#percentage_value').val();
            const referenceType = $('#reference_type').val();
            
            if (!percentageValue) {
                e.preventDefault();
                alert('Nilai persentase harus diisi untuk perhitungan berbasis persentase.');
                $('#percentage_value').focus();
                return false;
            }
            
            if (!referenceType) {
                e.preventDefault();
                alert('Tipe referensi harus dipilih untuk perhitungan berbasis persentase.');
                $('#reference_type').focus();
                return false;
            }
        }
    });
});
</script>
@endpush
