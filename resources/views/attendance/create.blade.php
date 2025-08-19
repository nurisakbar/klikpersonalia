@extends('layouts.app')

@section('title', 'Tambah Absensi - Aplikasi Payroll KlikMedis')
@section('page-title', 'Tambah Absensi')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('attendance.index') }}">Absensi</a></li>
<li class="breadcrumb-item active">Tambah</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
            <form action="{{ route('attendance.store') }}" method="POST" id="attendanceForm">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="employee_id">Karyawan <span class="text-danger">*</span></label>
                                <select name="employee_id" id="employee_id" class="form-control @error('employee_id') is-invalid @enderror" required>
                                    <option value="">Pilih Karyawan</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                                            {{ $employee->name }} - {{ $employee->department }}
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
                                <label for="date">Tanggal <span class="text-danger">*</span></label>
                                <input type="date" name="date" id="date" class="form-control @error('date') is-invalid @enderror" value="{{ old('date', date('Y-m-d')) }}" required>
                                @error('date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="check_in">Check In</label>
                                <input type="time" name="check_in" id="check_in" class="form-control @error('check_in') is-invalid @enderror" value="{{ old('check_in') }}">
                                @error('check_in')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="check_out">Check Out</label>
                                <input type="time" name="check_out" id="check_out" class="form-control @error('check_out') is-invalid @enderror" value="{{ old('check_out') }}">
                                @error('check_out')
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
                                    <option value="">Pilih Status</option>
                                    <option value="present" {{ old('status') == 'present' ? 'selected' : '' }}>Hadir</option>
                                    <option value="absent" {{ old('status') == 'absent' ? 'selected' : '' }}>Tidak Hadir</option>
                                    <option value="late" {{ old('status') == 'late' ? 'selected' : '' }}>Terlambat</option>
                                    <option value="half_day" {{ old('status') == 'half_day' ? 'selected' : '' }}>Setengah Hari</option>
                                    <option value="leave" {{ old('status') == 'leave' ? 'selected' : '' }}>Cuti</option>
                                    <option value="holiday" {{ old('status') == 'holiday' ? 'selected' : '' }}>Libur</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="notes">Catatan</label>
                                <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror" rows="3" placeholder="Masukkan catatan (opsional)">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div>
                        <button type="submit" class="btn btn-primary" id="btnSaveAttendance">
                            <i class="fas fa-save mr-1"></i> Simpan
                        </button>
                        <a href="{{ route('attendance.index') }}" class="btn btn-secondary">
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
$(document).ready(function() {
    // Auto-calculate total hours when check-in and check-out are filled
    $('#check_in, #check_out').on('change', function() {
        var checkIn = $('#check_in').val();
        var checkOut = $('#check_out').val();
        
        if (checkIn && checkOut) {
            var startTime = new Date('2000-01-01 ' + checkIn);
            var endTime = new Date('2000-01-01 ' + checkOut);
            
            if (endTime > startTime) {
                var diffMs = endTime - startTime;
                var diffHrs = Math.floor(diffMs / (1000 * 60 * 60));
                var diffMins = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60));
                
                var totalHours = diffHrs + (diffMins / 60);
                var overtimeHours = Math.max(0, totalHours - 8);
                
                // You can display this information if needed
                console.log('Total Hours:', totalHours.toFixed(2));
                console.log('Overtime Hours:', overtimeHours.toFixed(2));
            }
        }
    });

    // Set default status based on check-in time
    $('#check_in').on('change', function() {
        var checkInTime = $(this).val();
        if (checkInTime) {
            var hour = parseInt(checkInTime.split(':')[0]);
            if (hour > 8) {
                $('#status').val('late');
            } else {
                $('#status').val('present');
            }
        }
    });
    // Submit via AJAX with SweetAlert feedback
    $('#attendanceForm').on('submit', function(e) {
        e.preventDefault();
        const $btn = $('#btnSaveAttendance');
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

        const formData = new FormData(this);
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            errorHandled: true,
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    SwalHelper.success('Berhasil!', response.message, 2000);
                    setTimeout(() => {
                        window.location.href = '{{ route('attendance.index') }}';
                    }, 2000);
                } else {
                    SwalHelper.error('Gagal!', response.message);
                    $btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan');
                }
            },
            error: function(xhr) {
                let message = 'Terjadi kesalahan saat menyimpan data';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errs = xhr.responseJSON.errors;
                    message = Object.values(errs).flat().join('\n');
                }
                SwalHelper.error('Error!', message);
                $btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan');
            }
        });
    });
});
</script>
@endpush 