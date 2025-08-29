@extends('layouts.app')

@section('title', 'Tambah Rekening Bank - Aplikasi Payroll KlikMedis')
@section('page-title', 'Tambah Rekening Bank')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('bank-accounts.index') }}">Rekening Bank</a></li>
<li class="breadcrumb-item active">Tambah</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Form Tambah Rekening Bank</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('bank-accounts.store') }}" method="POST" id="bankAccountForm">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="employee_id">Karyawan <span class="text-danger">*</span></label>
                                <select name="employee_id" id="employee_id" class="form-control @error('employee_id') is-invalid @enderror" required>
                                    <option value="">Pilih Karyawan</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                                            {{ $employee->name }} ({{ $employee->employee_id }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('employee_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="bank_name">Nama Bank <span class="text-danger">*</span></label>
                                <input type="text" name="bank_name" id="bank_name" class="form-control @error('bank_name') is-invalid @enderror" 
                                       value="{{ old('bank_name') }}" placeholder="Contoh: Bank Central Asia" required>
                                @error('bank_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="account_holder_name">Pemilik Rekening <span class="text-danger">*</span></label>
                                <input type="text" name="account_holder_name" id="account_holder_name" class="form-control @error('account_holder_name') is-invalid @enderror" 
                                       value="{{ old('account_holder_name') }}" placeholder="Nama pemilik rekening" required>
                                @error('account_holder_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="account_number">Nomor Rekening <span class="text-danger">*</span></label>
                                <input type="text" name="account_number" id="account_number" class="form-control @error('account_number') is-invalid @enderror" 
                                       value="{{ old('account_number') }}" placeholder="Nomor rekening" required>
                                @error('account_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="account_type">Jenis Rekening <span class="text-danger">*</span></label>
                                <select name="account_type" id="account_type" class="form-control @error('account_type') is-invalid @enderror" required>
                                    <option value="">Pilih Jenis Rekening</option>
                                    @foreach($accountTypes as $key => $value)
                                        <option value="{{ $key }}" {{ old('account_type') == $key ? 'selected' : '' }}>
                                            {{ $value }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('account_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="branch_code">Kode Cabang</label>
                                <input type="text" name="branch_code" id="branch_code" class="form-control @error('branch_code') is-invalid @enderror" 
                                       value="{{ old('branch_code') }}" placeholder="Kode cabang bank">
                                @error('branch_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="swift_code">Swift Code</label>
                                <input type="text" name="swift_code" id="swift_code" class="form-control @error('swift_code') is-invalid @enderror" 
                                       value="{{ old('swift_code') }}" placeholder="Swift code bank">
                                @error('swift_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="notes">Catatan</label>
                                <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror" 
                                          rows="3" placeholder="Catatan tambahan">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" name="is_active" id="is_active" class="custom-control-input" value="1" 
                                           {{ old('is_active', '1') ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_active">Aktif</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" name="is_primary" id="is_primary" class="custom-control-input" value="1" 
                                           {{ old('is_primary') ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_primary">Rekening Utama</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Simpan
                                </button>
                                <a href="{{ route('bank-accounts.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Kembali
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Wait for jQuery to be available
    if (typeof $ === 'undefined') {
        console.error('jQuery is not available in Bank Account Create');
        return;
    }
    
    $(document).ready(function() {
    // Form validation
    $('#bankAccountForm').on('submit', function(e) {
        var isValid = true;
        
        // Clear previous error states
        $('.is-invalid').removeClass('is-invalid');
        
        // Required field validation
        var requiredFields = ['employee_id', 'bank_name', 'account_holder_name', 'account_number', 'account_type'];
        requiredFields.forEach(function(field) {
            var value = $('#' + field).val();
            if (!value || value.trim() === '') {
                $('#' + field).addClass('is-invalid');
                isValid = false;
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            SwalHelper.error('Error!', 'Mohon lengkapi semua field yang wajib diisi.');
            return false;
        }
    });

    // Auto-fill account holder name when employee is selected
    $('#employee_id').on('change', function() {
        var employeeId = $(this).val();
        if (employeeId) {
            var selectedOption = $(this).find('option:selected');
            var employeeName = selectedOption.text().split(' (')[0]; // Get name part only
            $('#account_holder_name').val(employeeName);
        } else {
            $('#account_holder_name').val('');
                 }
     });
     });
 });
 </script>
 @endpush
