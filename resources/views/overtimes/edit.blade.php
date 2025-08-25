@extends('layouts.app')

@section('title', 'Perbarui Permintaan Lembur - Aplikasi Payroll KlikMedis')
@section('page-title', 'Perbarui Permintaan Lembur')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('overtimes.index') }}">Lembur</a></li>
<li class="breadcrumb-item active">Perbarui Lembur</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('overtimes.update', $overtime->id) }}" method="POST" enctype="multipart/form-data" id="overtimeForm">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="overtime_type">Jenis Lembur <span class="text-danger">*</span></label>
                                    <select name="overtime_type" id="overtime_type" class="form-control @error('overtime_type') is-invalid @enderror" required>
                                        <option value="">Pilih Jenis Lembur</option>
                                        <option value="regular" {{ old('overtime_type', $overtime->overtime_type) == 'regular' ? 'selected' : '' }}>Lembur Regular</option>
                                        <option value="holiday" {{ old('overtime_type', $overtime->overtime_type) == 'holiday' ? 'selected' : '' }}>Lembur Hari Libur</option>
                                        <option value="weekend" {{ old('overtime_type', $overtime->overtime_type) == 'weekend' ? 'selected' : '' }}>Lembur Akhir Pekan</option>
                                        <option value="emergency" {{ old('overtime_type', $overtime->overtime_type) == 'emergency' ? 'selected' : '' }}>Lembur Darurat</option>
                                    </select>
                                    @error('overtime_type')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="attachment">Lampiran (Opsional)</label>
                                    <input type="file" name="attachment" id="attachment" class="form-control @error('attachment') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png">
                                    <small class="form-text text-muted">Format yang didukung: PDF, JPG, JPEG, PNG (Maks: 2MB)</small>
                                    @if($overtime->attachment)
                                        <div class="mt-2">
                                            <small class="text-info">
                                                <i class="fas fa-info-circle mr-1"></i>
                                                Lampiran saat ini: {{ basename($overtime->attachment) }}
                                            </small>
                                        </div>
                                    @endif
                                    @error('attachment')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="date">Tanggal Lembur <span class="text-danger">*</span></label>
                                    <input type="date" name="date" id="date" class="form-control @error('date') is-invalid @enderror" value="{{ old('date', $overtime->date->format('Y-m-d')) }}" required>
                                    @error('date')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="start_time">Waktu Mulai <span class="text-danger">*</span></label>
                                    <input type="time" name="start_time" id="start_time" class="form-control @error('start_time') is-invalid @enderror" value="{{ old('start_time', $overtime->formatted_start_time) }}" required>
                                    @error('start_time')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="end_time">Waktu Selesai <span class="text-danger">*</span></label>
                                    <input type="time" name="end_time" id="end_time" class="form-control @error('end_time') is-invalid @enderror" value="{{ old('end_time', $overtime->formatted_end_time) }}" required>
                                    @error('end_time')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="reason">Alasan <span class="text-danger">*</span></label>
                            <textarea name="reason" id="reason" rows="4" class="form-control @error('reason') is-invalid @enderror" placeholder="Berikan alasan detail untuk permintaan lembur Anda..." required>{{ old('reason', $overtime->reason) }}</textarea>
                            @error('reason')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="fas fa-save mr-1"></i> Perbarui
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <!-- Current Overtime Details -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-info-circle mr-2"></i>
                        Detail Lembur Saat Ini
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Baris 1: Total Jam & Waktu -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="d-flex align-items-center p-3 bg-info text-white rounded statistics-card">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-clock fa-2x"></i>
                                </div>
                                <div class="flex-grow-1 ml-3">
                                    <div class="h5 mb-0">{{ $overtime->total_hours }} jam</div>
                                    <small>{{ $overtime->formatted_start_time }} - {{ $overtime->formatted_end_time }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Baris 2: Tanggal & Status -->
                    <div class="row mb-3">
                        <div class="col-6">
                            <div class="d-flex align-items-center p-3 bg-success text-white rounded statistics-card">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-calendar fa-lg"></i>
                                </div>
                                <div class="flex-grow-1 ml-2">
                                    <div class="h6 mb-0">{{ $overtime->formatted_date }}</div>
                                    <small>Tanggal Lembur</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center p-3 bg-warning text-white rounded statistics-card">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-tag fa-lg"></i>
                                </div>
                                <div class="flex-grow-1 ml-2">
                                    <div class="h6 mb-0">{!! $overtime->status_badge !!}</div>
                                    <small>Status</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Baris 3: Jenis Lembur -->
                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex align-items-center p-3 bg-primary text-white rounded statistics-card">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-briefcase fa-lg"></i>
                                </div>
                                <div class="flex-grow-1 ml-2">
                                    <div class="h6 mb-0">{!! $overtime->type_badge !!}</div>
                                    <small>Jenis Lembur</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Overtime Policy Card -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-info-circle mr-2"></i>
                        Kebijakan Lembur
                    </h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success mr-2"></i> Maksimal 8 jam lembur per hari</li>
                        <li><i class="fas fa-check text-success mr-2"></i> Minimal 1 jam lembur per permintaan</li>
                        <li><i class="fas fa-check text-success mr-2"></i> Lembur Regular: 1.5x gaji per jam</li>
                        <li><i class="fas fa-check text-success mr-2"></i> Lembur Hari Libur: 2x gaji per jam</li>
                        <li><i class="fas fa-check text-success mr-2"></i> Lembur Akhir Pekan: 2x gaji per jam</li>
                        <li><i class="fas fa-check text-success mr-2"></i> Lembur Darurat: 2x gaji per jam</li>
                    </ul>
                    <hr>
                    <small class="text-muted">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        Hanya permintaan dengan status "Menunggu" yang dapat diperbarui.
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
    // Calculate total hours when times change
    function calculateHours() {
        const startTime = $('#start_time').val();
        const endTime = $('#end_time').val();
        
        if (startTime && endTime) {
            const start = new Date('2000-01-01 ' + startTime);
            const end = new Date('2000-01-01 ' + endTime);
            
            if (end < start) {
                end.setDate(end.getDate() + 1); // Add one day if end time is before start time
            }
            
            const diffTime = end - start;
            const diffMinutes = diffTime / (1000 * 60);
            const diffHours = Math.max(1, Math.ceil(diffMinutes / 60));
            
            // Show total hours info
            if (diffHours > 0) {
                $('.form-group').each(function() {
                    if ($(this).find('label').text().includes('Waktu Selesai')) {
                        if ($(this).find('.hours-info').length === 0) {
                            $(this).append('<small class="form-text text-info hours-info"><i class="fas fa-clock mr-1"></i> Total: ' + diffHours + ' jam</small>');
                        } else {
                            $(this).find('.hours-info').html('<i class="fas fa-clock mr-1"></i> Total: ' + diffHours + ' jam');
                        }
                    }
                });
            }
        }
    }
    
    $('#start_time, #end_time').change(calculateHours);
    
    // Calculate initial hours
    calculateHours();

    // Submit form dengan AJAX
    $('#overtimeForm').on('submit', function(e) {
        e.preventDefault();
        
        // Basic validation
        let date = $('#date').val();
        let startTime = $('#start_time').val();
        let endTime = $('#end_time').val();
        let overtimeType = $('#overtime_type').val();
        let reason = $('#reason').val();
        
        if (!date || !startTime || !endTime || !overtimeType || !reason.trim()) {
            SwalHelper.error('Error!', 'Mohon lengkapi semua field yang wajib diisi.');
            return;
        }
        
        if (startTime >= endTime) {
            SwalHelper.error('Error!', 'Waktu selesai harus lebih besar dari waktu mulai.');
            return;
        }
        
        // Calculate hours for validation
        const start = new Date('2000-01-01 ' + startTime);
        const end = new Date('2000-01-01 ' + endTime);
        if (end < start) {
            end.setDate(end.getDate() + 1);
        }
        const diffTime = end - start;
        const diffMinutes = diffTime / (1000 * 60);
        const diffHours = Math.max(1, Math.ceil(diffMinutes / 60));
        
        if (diffHours > 8) {
            SwalHelper.error('Error!', 'Durasi lembur maksimal 8 jam per hari.');
            return;
        }
        
        if (diffHours < 1) {
            SwalHelper.error('Error!', 'Durasi lembur minimal 1 jam.');
            return;
        }
        
        // Show loading
        $('#submitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Memperbarui...');

        // Prepare form data
        let formData = new FormData(this);

        // Debug: Log form data
        console.log('Form data being sent:');
        for (let pair of formData.entries()) {
            console.log(pair[0] + ': ' + pair[1]);
        }

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
                        window.location.href = '{{ route("overtimes.index") }}';
                    }, 2000);
                } else {
                    SwalHelper.error('Gagal!', response.message);
                    $('#submitBtn').prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Perbarui');
                }
            },
            error: function(xhr) {
                let message = 'Terjadi kesalahan saat memperbarui permintaan lembur';
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
                $('#submitBtn').prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Perbarui');
            }
        });
    });
});
</script>
@endpush

@push('css')
<style>
/* Legacy info-box styles (for backward compatibility) */
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

/* Custom styling for new statistics cards */
.statistics-card {
    transition: all 0.3s ease;
}

.statistics-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.statistics-card .d-flex {
    min-height: 60px;
}

.statistics-card .flex-shrink-0 {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 50px;
}

.statistics-card .flex-grow-1 {
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.statistics-card .h5,
.statistics-card .h6 {
    font-weight: 600;
    margin-bottom: 0;
}

.statistics-card small {
    opacity: 0.9;
    font-size: 0.85rem;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .statistics-card .d-flex {
        min-height: 50px;
    }
    
    .statistics-card .flex-shrink-0 {
        width: 40px;
    }
    
    .statistics-card .h5 {
        font-size: 1.1rem;
    }
    
    .statistics-card .h6 {
        font-size: 1rem;
    }
}
</style>
@endpush
