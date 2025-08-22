@extends('layouts.app')

@section('title', 'Ajukan Permintaan Cuti - Aplikasi Payroll KlikMedis')
@section('page-title', 'Ajukan Permintaan Cuti')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('leaves.index') }}">Cuti</a></li>
<li class="breadcrumb-item active">Ajukan Cuti</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('leaves.store') }}" method="POST" enctype="multipart/form-data" id="leaveForm">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="leave_type">Jenis Cuti <span class="text-danger">*</span></label>
                                    <select name="leave_type" id="leave_type" class="form-control @error('leave_type') is-invalid @enderror" required>
                                        <option value="">Pilih Jenis Cuti</option>
                                        <option value="annual" {{ old('leave_type') == 'annual' ? 'selected' : '' }}>Cuti Tahunan</option>
                                        <option value="sick" {{ old('leave_type') == 'sick' ? 'selected' : '' }}>Cuti Sakit</option>
                                        <option value="maternity" {{ old('leave_type') == 'maternity' ? 'selected' : '' }}>Cuti Melahirkan</option>
                                        <option value="paternity" {{ old('leave_type') == 'paternity' ? 'selected' : '' }}>Cuti Melahirkan (Pria)</option>
                                        <option value="other" {{ old('leave_type') == 'other' ? 'selected' : '' }}>Cuti Lainnya</option>
                                    </select>
                                    @error('leave_type')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="attachment">Lampiran (Opsional)</label>
                                    <input type="file" name="attachment" id="attachment" class="form-control @error('attachment') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png">
                                    <small class="form-text text-muted">Format yang didukung: PDF, JPG, JPEG, PNG (Maks: 2MB)</small>
                                    @error('attachment')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="start_date">Tanggal Mulai <span class="text-danger">*</span></label>
                                    <input type="date" name="start_date" id="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date') }}" required min="{{ date('Y-m-d') }}">
                                    @error('start_date')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="end_date">Tanggal Selesai <span class="text-danger">*</span></label>
                                    <input type="date" name="end_date" id="end_date" class="form-control @error('end_date') is-invalid @enderror" value="{{ old('end_date') }}" required min="{{ date('Y-m-d') }}">
                                    @error('end_date')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="reason">Alasan <span class="text-danger">*</span></label>
                            <textarea name="reason" id="reason" rows="4" class="form-control @error('reason') is-invalid @enderror" placeholder="Berikan alasan detail untuk permintaan cuti Anda..." required>{{ old('reason') }}</textarea>
                            @error('reason')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="fas fa-paper-plane mr-1"></i> Ajukan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <!-- Leave Balance Card -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-chart-pie mr-2"></i>
                        Sisa Cuti ({{ date('Y') }})
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="info-box bg-info">
                                <span class="info-box-icon">
                                    <i class="fas fa-calendar-check"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Cuti Tahunan</span>
                                    <span class="info-box-number">{{ $leaveBalance['annual_remaining'] }}/{{ $leaveBalance['annual_total'] }}</span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: {{ ($leaveBalance['annual_used'] / $leaveBalance['annual_total']) * 100 }}%"></div>
                                    </div>
                                    <span class="progress-description">
                                        {{ $leaveBalance['annual_used'] }} hari digunakan
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="info-box bg-warning">
                                <span class="info-box-icon">
                                    <i class="fas fa-user-injured"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Cuti Sakit</span>
                                    <span class="info-box-number">{{ $leaveBalance['sick_remaining'] }}/{{ $leaveBalance['sick_total'] }}</span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: {{ ($leaveBalance['sick_used'] / $leaveBalance['sick_total']) * 100 }}%"></div>
                                    </div>
                                    <span class="progress-description">
                                        {{ $leaveBalance['sick_used'] }} hari digunakan
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="info-box bg-success">
                                <span class="info-box-icon">
                                    <i class="fas fa-baby"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Cuti Melahirkan</span>
                                    <span class="info-box-number">{{ $leaveBalance['maternity_remaining'] }}/{{ $leaveBalance['maternity_total'] }}</span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: {{ ($leaveBalance['maternity_used'] / $leaveBalance['maternity_total']) * 100 }}%"></div>
                                    </div>
                                    <span class="progress-description">
                                        {{ $leaveBalance['maternity_used'] }} hari digunakan
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="info-box bg-primary">
                                <span class="info-box-icon">
                                    <i class="fas fa-user-tie"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Cuti Melahirkan (Pria)</span>
                                    <span class="info-box-number">{{ $leaveBalance['paternity_remaining'] }}/{{ $leaveBalance['paternity_total'] }}</span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: {{ ($leaveBalance['paternity_used'] / $leaveBalance['paternity_total']) * 100 }}%"></div>
                                    </div>
                                    <span class="progress-description">
                                        {{ $leaveBalance['paternity_used'] }} hari digunakan
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="info-box bg-secondary">
                                <span class="info-box-icon">
                                    <i class="fas fa-ellipsis-h"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Cuti Lainnya</span>
                                    <span class="info-box-number">{{ $leaveBalance['other_remaining'] }}/{{ $leaveBalance['other_total'] }}</span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: {{ ($leaveBalance['other_used'] / $leaveBalance['other_total']) * 100 }}%"></div>
                                    </div>
                                    <span class="progress-description">
                                        {{ $leaveBalance['other_used'] }} hari digunakan
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Leave Policy Card -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-info-circle mr-2"></i>
                        Kebijakan Cuti
                    </h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success mr-2"></i> Cuti Tahunan: 12 hari per tahun</li>
                        <li><i class="fas fa-check text-success mr-2"></i> Cuti Sakit: 12 hari per tahun</li>
                        <li><i class="fas fa-check text-success mr-2"></i> Cuti Melahirkan: 90 hari</li>
                        <li><i class="fas fa-check text-success mr-2"></i> Cuti Melahirkan (Pria): 2 hari</li>
                        <li><i class="fas fa-check text-success mr-2"></i> Cuti Lainnya: 5 hari per tahun</li>
                    </ul>
                    <hr>
                    <small class="text-muted">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        Permintaan cuti tahunan harus diajukan minimal 3 hari sebelumnya.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<!-- Global SweetAlert Component -->
@include('components.sweet-alert')

<script>
$(function () {
    // Set minimum end date based on start date
    $('#start_date').change(function() {
        const startDate = $(this).val();
        if (startDate) {
            $('#end_date').attr('min', startDate);
        }
    });
    
    // Calculate total days when dates change
    function calculateDays() {
        const startDate = $('#start_date').val();
        const endDate = $('#end_date').val();
        
        if (startDate && endDate) {
            const start = new Date(startDate);
            const end = new Date(endDate);
            const diffTime = Math.abs(end - start);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
            
            // Show total days info
            if (diffDays > 0) {
                $('.form-group').each(function() {
                    if ($(this).find('label').text().includes('Tanggal Selesai')) {
                        if ($(this).find('.days-info').length === 0) {
                            $(this).append('<small class="form-text text-info days-info"><i class="fas fa-calendar-day mr-1"></i> Total: ' + diffDays + ' hari</small>');
                        } else {
                            $(this).find('.days-info').html('<i class="fas fa-calendar-day mr-1"></i> Total: ' + diffDays + ' hari');
                        }
                    }
                });
            }
        }
    }
    
    $('#start_date, #end_date').change(calculateDays);

    // Submit form dengan AJAX
    $('#leaveForm').on('submit', function(e) {
        e.preventDefault();
        
        // Basic validation
        let startDate = $('#start_date').val();
        let endDate = $('#end_date').val();
        let leaveType = $('#leave_type').val();
        let reason = $('#reason').val();
        
        if (!startDate || !endDate || !leaveType || !reason.trim()) {
            SwalHelper.error('Error!', 'Mohon lengkapi semua field yang wajib diisi.');
            return;
        }
        
        if (new Date(startDate) > new Date(endDate)) {
            SwalHelper.error('Error!', 'Tanggal selesai harus lebih besar atau sama dengan tanggal mulai.');
            return;
        }
        
        // Check if dates are in the past
        let today = new Date();
        today.setHours(0, 0, 0, 0);
        if (new Date(startDate) < today) {
            SwalHelper.error('Error!', 'Tanggal mulai tidak boleh di masa lalu.');
            return;
        }
        
        // Show loading
        $('#submitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Mengajukan...');

        // Prepare form data
        let formData = new FormData(this);

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
                        window.location.href = '{{ route("leaves.index") }}';
                    }, 2000);
                } else {
                    SwalHelper.error('Gagal!', response.message);
                    $('#submitBtn').prop('disabled', false).html('<i class="fas fa-paper-plane mr-1"></i> Ajukan Permintaan Cuti');
                }
            },
            error: function(xhr) {
                let message = 'Terjadi kesalahan saat mengajukan permintaan cuti';
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
                $('#submitBtn').prop('disabled', false).html('<i class="fas fa-paper-plane mr-1"></i> Ajukan Permintaan Cuti');
            }
        });
    });
});
</script>
@endpush

@push('css')
<style>
.info-box {
    display: block;
    min-height: 80px;
    background: #fff;
    width: 100%;
    box-shadow: 0 1px 1px rgba(0,0,0,0.1);
    border-radius: 2px;
    margin-bottom: 15px;
}

.info-box-icon {
    border-radius: 2px 0 0 2px;
    display: block;
    float: left;
    height: 80px;
    width: 80px;
    text-align: center;
    font-size: 40px;
    line-height: 80px;
    background: rgba(0,0,0,0.2);
}

.info-box-content {
    padding: 5px 10px;
    margin-left: 80px;
}

.info-box-text {
    display: block;
    font-size: 14px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.info-box-number {
    display: block;
    font-weight: bold;
    font-size: 18px;
}

.progress {
    height: 3px;
    margin: 5px 0;
}

.progress-description {
    display: block;
    font-size: 12px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
</style>
@endpush 