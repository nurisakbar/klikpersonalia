@extends('layouts.app')

@section('title', 'Edit Karyawan - Aplikasi Payroll KlikMedis')
@section('page-title', 'Edit Karyawan')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('employees.index') }}">Karyawan</a></li>
<li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
            <form action="{{ route('employees.update', $employee->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $employee->name) }}" placeholder="Masukkan nama lengkap" required>
                                @error('name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $employee->email) }}" placeholder="Masukkan email" required>
                                @error('email')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="phone">Nomor Telepon <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $employee->phone) }}" placeholder="Masukkan nomor telepon" required>
                                @error('phone')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="join_date">Tanggal Bergabung <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('join_date') is-invalid @enderror" id="join_date" name="join_date" value="{{ old('join_date', optional($employee->join_date)->format('Y-m-d')) }}" required>
                                @error('join_date')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="department">Departemen <span class="text-danger">*</span></label>
                                <select class="form-control @error('department') is-invalid @enderror" id="department" name="department" required>
                                    <option value="">Pilih Departemen</option>
                                    @if(count($departments) > 0)
                                        @foreach($departments as $key => $department)
                                            <option value="{{ $key }}" {{ old('department', $employee->department) == $key ? 'selected' : '' }}>{{ $department }}</option>
                                        @endforeach
                                    @else
                                        <option value="" disabled>Belum ada departemen yang ditambahkan</option>
                                    @endif
                                </select>
                                @if(count($departments) == 0)
                                    <small class="form-text text-warning">
                                        <i class="fas fa-exclamation-triangle"></i> 
                                        Departemen belum ditambahkan. Silakan tambahkan departemen terlebih dahulu di menu Master Data > Departemen.
                                    </small>
                                @endif
                                @error('department')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="position">Jabatan <span class="text-danger">*</span></label>
                                <select class="form-control @error('position') is-invalid @enderror" id="position" name="position" required>
                                    <option value="">Pilih Jabatan</option>
                                    @if(count($positions) > 0)
                                        @foreach($positions as $key => $position)
                                            <option value="{{ $key }}" {{ old('position', $employee->position) == $key ? 'selected' : '' }}>{{ $position }}</option>
                                        @endforeach
                                    @else
                                        <option value="" disabled>Belum ada jabatan yang ditambahkan</option>
                                    @endif
                                </select>
                                @if(count($positions) == 0)
                                    <small class="form-text text-warning">
                                        <i class="fas fa-exclamation-triangle"></i> 
                                        Jabatan belum ditambahkan. Silakan tambahkan jabatan terlebih dahulu di menu Master Data > Jabatan.
                                    </small>
                                @endif
                                @error('position')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="basic_salary">Gaji Pokok <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp</span>
                                    </div>
                                    <input type="text" class="form-control @error('basic_salary') is-invalid @enderror" id="basic_salary" name="basic_salary" value="{{ old('basic_salary', $employee->basic_salary) }}" placeholder="Masukkan gaji pokok (min: 1.000.000)" required>
                                </div>
                                <small class="form-text text-muted">Minimal Rp 1.000.000, maksimal Rp 999.999.999.999</small>
                                @error('basic_salary')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="status">Status <span class="text-danger">*</span></label>
                                <select class="form-control @error('status') is-invalid @enderror" id="status" name="status" required>
                                    <option value="">Pilih Status</option>
                                    @foreach($statuses as $key => $status)
                                        <option value="{{ $key }}" {{ old('status', $employee->status) == $key ? 'selected' : '' }}>{{ $status }}</option>
                                    @endforeach
                                </select>
                                @error('status')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="address">Alamat</label>
                                <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="3" placeholder="Masukkan alamat">{{ old('address', $employee->address ?? '') }}</textarea>
                                @error('address')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="emergency_contact">Kontak Darurat</label>
                                <input type="text" class="form-control @error('emergency_contact') is-invalid @enderror" id="emergency_contact" name="emergency_contact" value="{{ old('emergency_contact', $employee->emergency_contact ?? '') }}" placeholder="Masukkan kontak darurat">
                                @error('emergency_contact')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="bank_account">Nomor Rekening</label>
                                <input type="text" class="form-control @error('bank_account') is-invalid @enderror" id="bank_account" name="bank_account" value="{{ old('bank_account', $employee->bank_account ?? '') }}" placeholder="Masukkan nomor rekening">
                                @error('bank_account')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="bank_name">Nama Bank</label>
                                <select class="form-control @error('bank_name') is-invalid @enderror" id="bank_name" name="bank_name">
                                    <option value="">Pilih Bank</option>
                                    @if(isset($banks))
                                        @foreach($banks as $code => $name)
                                            <option value="{{ $code }}" {{ old('bank_name', $employee->bank_name) == $code ? 'selected' : '' }}>{{ $name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                @error('bank_name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="fas fa-save mr-1"></i> Update
                        </button>
                        <a href="{{ route('employees.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times mr-1"></i> Batal
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('js')
<!-- Global SweetAlert Component -->
@include('components.sweet-alert')

<script>
$(function () {
    // Format input gaji dengan separator ribuan
    $('#basic_salary').on('input', function() {
        let value = $(this).val().replace(/[^\d]/g, '');
        if (value) {
            // Convert to number and format with thousand separators
            let number = parseInt(value);
            if (!isNaN(number)) {
                $(this).val(number.toLocaleString('id-ID'));
            }
        }
    });

    // Submit form dengan format angka yang benar
    $('form').on('submit', function(e) {
        e.preventDefault();
        
        // Get raw numeric value from formatted salary
        let salaryInput = $('#basic_salary');
        let formattedSalary = salaryInput.val();
        let rawSalary = formattedSalary.replace(/[^\d]/g, '');
        
        // Validate minimum salary
        if (parseInt(rawSalary) < 1000000) {
            SwalHelper.error('Error!', 'Gaji pokok minimal Rp 1.000.000');
            return false;
        }

        // Show loading
        $('#submitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

        // Prepare form data
        let formData = new FormData(this);
        formData.set('basic_salary', rawSalary); // Set raw salary value

        // Send AJAX request
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            errorHandled: true,
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    SwalHelper.success('Berhasil!', response.message, 2000);
                    setTimeout(() => {
                        window.location.href = '{{ route("employees.index") }}';
                    }, 2000);
                } else {
                    SwalHelper.error('Gagal!', response.message);
                    $('#submitBtn').prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Update');
                }
            },
            error: function(xhr) {
                let message = 'Terjadi kesalahan saat menyimpan data';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    let errors = xhr.responseJSON.errors;
                    let errorMessages = [];
                    for (let field in errors) {
                        errorMessages.push(errors[field][0]);
                    }
                    message = errorMessages.join('\n');
                }
                
                SwalHelper.error('Error!', message);
                $('#submitBtn').prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Update');
            }
        });
    });

    // Format initial value if exists
    let initialSalary = $('#basic_salary').val();
    if (initialSalary && !isNaN(parseInt(initialSalary.replace(/[^\d]/g, '')))) {
        let value = parseInt(initialSalary.replace(/[^\d]/g, ''));
        $('#basic_salary').val(value.toLocaleString('id-ID'));
    }
});
</script>
@endpush 