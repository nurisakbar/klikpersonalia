@extends('layouts.app')

@section('title', 'Rincian Cuti - Aplikasi Payroll KlikMedis')
@section('page-title', 'Rincian Cuti')

@section('breadcrumb')
<li class="breadcrumb-item active">Rincian Cuti</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td width="40%"><strong>Jenis Cuti:</strong></td>
                                    <td>{!! $leave->type_badge !!}</td>
                                </tr>
                                <tr>
                                    <td><strong>Tanggal Mulai:</strong></td>
                                    <td>{{ $leave->formatted_start_date }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Tanggal Selesai:</strong></td>
                                    <td>{{ $leave->formatted_end_date }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Total Hari:</strong></td>
                                    <td>{{ $leave->total_days }} hari</td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>{!! $leave->status_badge !!}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td width="40%"><strong>Dibuat:</strong></td>
                                    <td>{{ $leave->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                @if($leave->approved_at)
                                <tr>
                                    <td><strong>Dilakukan:</strong></td>
                                    <td>{{ $leave->approved_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                @endif
                                @if($leave->approver)
                                <tr>
                                    <td><strong>Dilakukan Oleh:</strong></td>
                                    <td>{{ $leave->approver->name }}</td>
                                </tr>
                                @endif
                                @if($leave->approval_notes)
                                <tr>
                                    <td><strong>Catatan:</strong></td>
                                    <td>{{ $leave->approval_notes }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    <hr>

                    <div class="form-group">
                        <label><strong>Alasan Cuti:</strong></label>
                        <div class="p-3 bg-light rounded">
                            {{ $leave->reason }}
                        </div>
                    </div>

                    @if($leave->attachment)
                    <div class="form-group">
                        <label><strong>Lampiran:</strong></label>
                        <div>
                            <a href="{{ Storage::url($leave->attachment) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-download mr-1"></i> Unduh Lampiran
                            </a>
                            <small class="text-muted ml-2">
                                {{ pathinfo($leave->attachment, PATHINFO_EXTENSION) }} file
                            </small>
                        </div>
                    </div>
                    @endif

                    @if($leave->status === 'pending')
                    <div class="mt-4">
                        <a href="{{ route('leaves.edit', $leave->id) }}" class="btn btn-warning">
                            <i class="fas fa-edit mr-1"></i> Edit Permintaan Cuti
                        </a>
                        <button type="button" class="btn btn-danger delete-btn" 
                                data-id="{{ $leave->id }}" 
                                data-name="{{ $leave->leave_type }} leave from {{ $leave->formatted_start_date }} to {{ $leave->formatted_end_date }}">
                            <i class="fas fa-trash mr-1"></i> Batalkan Permintaan Cuti
                        </button>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Employee Information -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-user mr-2"></i>
                        Informasi Karyawan
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="avatar-placeholder bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px; font-size: 2rem;">
                            {{ strtoupper(substr($leave->employee->name, 0, 1)) }}
                        </div>
                    </div>
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Nama:</strong></td>
                            <td>{{ $leave->employee->name }}</td>
                        </tr>
                        <tr>
                            <td><strong>ID Karyawan:</strong></td>
                            <td>{{ $leave->employee->employee_id }}</td>
                        </tr>
                        <tr>
                            <td><strong>Departemen:</strong></td>
                            <td>{{ $leave->employee->department }}</td>
                        </tr>
                        <tr>
                            <td><strong>Jabatan:</strong></td>
                            <td>{{ $leave->employee->position }}</td>
                        </tr>
                        <tr>
                            <td><strong>Tanggal Bergabung:</strong></td>
                            <td>{{ $leave->employee->join_date->format('d/m/Y') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Leave Timeline -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-clock mr-2"></i>
                        Timeline Cuti
                    </h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Permintaan Cuti Dibuat</h6>
                                <p class="timeline-text">{{ $leave->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                        
                        @if($leave->status === 'approved')
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Permintaan Cuti Disetujui</h6>
                                <p class="timeline-text">{{ $leave->approved_at->format('d/m/Y H:i') }}</p>
                                @if($leave->approver)
                                <small class="text-muted">by {{ $leave->approver->name }}</small>
                                @endif
                            </div>
                        </div>
                        @endif
                        
                        @if($leave->status === 'rejected')
                        <div class="timeline-item">
                            <div class="timeline-marker bg-danger"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Permintaan Cuti Ditolak</h6>
                                <p class="timeline-text">{{ $leave->approved_at->format('d/m/Y H:i') }}</p>
                                @if($leave->approver)
                                <small class="text-muted">by {{ $leave->approver->name }}</small>
                                @endif
                            </div>
                        </div>
                        @endif
                        
                        @if($leave->status === 'pending')
                        <div class="timeline-item">
                            <div class="timeline-marker bg-warning"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Menunggu Persetujuan</h6>
                                <p class="timeline-text">Menunggu persetujuan manager/HR</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Batalkan Permintaan Cuti</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin membatalkan permintaan cuti ini?</p>
                <p><strong id="deleteLeaveName"></strong></p>
                <p class="text-warning"><i class="fas fa-exclamation-triangle"></i> Tindakan ini tidak dapat dibatalkan.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Ya, Batalkan Permintaan Cuti</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Handle delete button click
    $('.delete-btn').click(function() {
        const leaveId = $(this).data('id');
        const leaveName = $(this).data('name');
        
        $('#deleteLeaveName').text(leaveName);
        $('#deleteForm').attr('action', '{{ url("leaves") }}/' + leaveId);
        $('#deleteModal').modal('show');
    });
});
</script>
@endpush

@push('styles')
<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -22px;
    top: 0;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #e9ecef;
}

.timeline-content {
    padding-left: 10px;
}

.timeline-title {
    margin: 0 0 5px 0;
    font-size: 14px;
    font-weight: 600;
}

.timeline-text {
    margin: 0;
    font-size: 12px;
    color: #6c757d;
}

.avatar-placeholder {
    background: linear-gradient(45deg, #007bff, #0056b3);
}
</style>
@endpush 