@extends('layouts.app')

@section('title', 'Tambah Payroll - Aplikasi Payroll KlikMedis')
@section('page-title', 'Tambah Payroll')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('payroll.index') }}">Payroll</a></li>
<li class="breadcrumb-item active">Tambah</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Form Tambah Payroll</h3>
            </div>
            <form action="{{ route('payroll.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="employee_id">Karyawan <span class="text-danger">*</span></label>
                                <select class="form-control @error('employee_id') is-invalid @enderror" id="employee_id" name="employee_id" required>
                                    <option value="">Pilih Karyawan</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                                            {{ $employee->employee_id }} - {{ $employee->name }} ({{ $employee->department }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('employee_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="period">Periode <span class="text-danger">*</span></label>
                                <select class="form-control @error('period') is-invalid @enderror" id="period" name="period" required>
                                    <option value="">Pilih Periode</option>
                                    @foreach($periods as $period)
                                        <option value="{{ $period }}" {{ old('period') == $period ? 'selected' : '' }}>{{ $period }}</option>
                                    @endforeach
                                </select>
                                @error('period')
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
                                    <input type="number" class="form-control @error('basic_salary') is-invalid @enderror" id="basic_salary" name="basic_salary" value="{{ old('basic_salary') }}" placeholder="Masukkan gaji pokok" required>
                                </div>
                                @error('basic_salary')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="allowance">Tunjangan <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp</span>
                                    </div>
                                    <input type="number" class="form-control @error('allowance') is-invalid @enderror" id="allowance" name="allowance" value="{{ old('allowance') }}" placeholder="Masukkan tunjangan" required>
                                </div>
                                @error('allowance')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="overtime">Lembur <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp</span>
                                    </div>
                                    <input type="number" class="form-control @error('overtime') is-invalid @enderror" id="overtime" name="overtime" value="{{ old('overtime') }}" placeholder="Masukkan lembur" required>
                                </div>
                                @error('overtime')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="bonus">Bonus <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp</span>
                                    </div>
                                    <input type="number" class="form-control @error('bonus') is-invalid @enderror" id="bonus" name="bonus" value="{{ old('bonus') }}" placeholder="Masukkan bonus" required>
                                </div>
                                @error('bonus')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="deduction">Potongan <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp</span>
                                    </div>
                                    <input type="number" class="form-control @error('deduction') is-invalid @enderror" id="deduction" name="deduction" value="{{ old('deduction') }}" placeholder="Masukkan potongan" required>
                                </div>
                                @error('deduction')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="payment_date">Tanggal Pembayaran <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('payment_date') is-invalid @enderror" id="payment_date" name="payment_date" value="{{ old('payment_date') }}" required>
                                @error('payment_date')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <h5><i class="icon fas fa-info"></i> Total Gaji</h5>
                                <p>Gaji Pokok + Tunjangan + Lembur + Bonus - Potongan = <strong id="total-salary">Rp 0</strong></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                    <a href="{{ route('payroll.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
$(function () {
    // Format input dengan separator ribuan
    $('input[type="number"]').on('input', function() {
        let value = $(this).val().replace(/[^\d]/g, '');
        if (value) {
            value = parseInt(value).toLocaleString('id-ID');
            $(this).val(value);
        }
    });

    // Hitung total gaji secara otomatis
    function calculateTotal() {
        let basicSalary = parseInt($('#basic_salary').val().replace(/[^\d]/g, '')) || 0;
        let allowance = parseInt($('#allowance').val().replace(/[^\d]/g, '')) || 0;
        let overtime = parseInt($('#overtime').val().replace(/[^\d]/g, '')) || 0;
        let bonus = parseInt($('#bonus').val().replace(/[^\d]/g, '')) || 0;
        let deduction = parseInt($('#deduction').val().replace(/[^\d]/g, '')) || 0;

        let total = basicSalary + allowance + overtime + bonus - deduction;
        $('#total-salary').text('Rp ' + total.toLocaleString('id-ID'));
    }

    // Event listener untuk input gaji
    $('#basic_salary, #allowance, #overtime, #bonus, #deduction').on('input', calculateTotal);

    // Submit form dengan format angka yang benar
    $('form').on('submit', function() {
        $('input[type="number"]').each(function() {
            let value = $(this).val().replace(/[^\d]/g, '');
            $(this).val(value);
        });
    });
});
</script>
@endpush 