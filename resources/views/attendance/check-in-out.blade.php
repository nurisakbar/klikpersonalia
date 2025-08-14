@extends('layouts.app')

@section('title', 'Check In/Out - Attendance')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-clock mr-2"></i>
                        Check In/Out System
                    </h3>
                </div>
                <div class="card-body">
                    <!-- Current Time Display -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="text-center">
                                <h2 class="text-primary" id="current-time">--:--:--</h2>
                                <p class="text-muted" id="current-date">--</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-center">
                                <h4 class="text-info">Selamat Datang, {{ auth()->user()->name }}</h4>
                                <p class="text-muted">{{ auth()->user()->position ?? 'Karyawan' }} - {{ auth()->user()->department ?? 'Umum' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Check In/Out Buttons -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h4><i class="fas fa-sign-in-alt mr-2"></i>Check In</h4>
                                    <p class="mb-3">Catat waktu kedatangan Anda</p>
                                     <button type="button" class="btn btn-light btn-lg" id="checkInBtn" onclick="performCheckIn()">
                                        <i class="fas fa-play mr-2"></i>Check In Sekarang
                                    </button>
                                    <div id="checkInStatus" class="mt-2"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-danger text-white">
                                <div class="card-body text-center">
                                    <h4><i class="fas fa-sign-out-alt mr-2"></i>Check Out</h4>
                                    <p class="mb-3">Catat waktu pulang Anda</p>
                                     <button type="button" class="btn btn-light btn-lg" id="checkOutBtn" onclick="performCheckOut()">
                                        <i class="fas fa-stop mr-2"></i>Check Out Sekarang
                                    </button>
                                    <div id="checkOutStatus" class="mt-2"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Today's Attendance Summary -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-calendar-day mr-2"></i>
                                        Ringkasan Kehadiran Hari Ini
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-info">
                                                    <i class="fas fa-clock"></i>
                                                </span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Waktu Check In</span>
                                                    <span class="info-box-number" id="checkInTime">--:--</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-warning">
                                                    <i class="fas fa-clock"></i>
                                                </span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Waktu Check Out</span>
                                                    <span class="info-box-number" id="checkOutTime">--:--</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-success">
                                                    <i class="fas fa-hourglass-half"></i>
                                                </span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Total Jam Kerja</span>
                                                    <span class="info-box-number" id="totalHours">--:--</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-primary">
                                                    <i class="fas fa-plus-circle"></i>
                                                </span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Jam Lembur</span>
                                                    <span class="info-box-number" id="overtimeHours">--:--</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Attendance History -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-history mr-2"></i>
                                        Riwayat Kehadiran Terbaru
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Tanggal</th>
                                                    <th>Check In</th>
                                                    <th>Check Out</th>
                                                    <th>Total Jam</th>
                                                    <th>Lembur</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody id="attendanceHistory">
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted">
                                                        <i class="fas fa-spinner fa-spin mr-2"></i>Memuat data...
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <p class="mt-2">Memproses...</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
let currentAttendance = null;
let employeeId = null;

// SweetAlert Helper
const SwalHelper = {
    confirm: (title, text, confirmText = 'Ya', cancelText = 'Batal') => {
        return Swal.fire({
            title: title,
            text: text,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: confirmText,
            cancelButtonText: cancelText
        });
    },
    toastSuccess: (message) => {
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: message,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000
        });
    },
    toastError: (message) => {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: message,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 5000
        });
    }
};

// Get employee ID for the current user
async function getEmployeeId() {
    try {
        const response = await fetch('{{ route("attendance.current") }}');
        const data = await response.json();
        
        if (data.success && data.employee_id) {
            employeeId = data.employee_id;
            currentAttendance = data.attendance;
            updateAttendanceDisplay();
        } else {
            showAlert('error', data.message || 'Employee not found. Please contact administrator.');
        }
    } catch (error) {
        console.error('Error fetching employee ID:', error);
        showAlert('error', 'Failed to get employee information. Please refresh the page.');
    }
}

// Update current time
function updateCurrentTime() {
    const now = new Date();
    const timeString = now.toLocaleTimeString('id-ID');
    const dateString = now.toLocaleDateString('id-ID', { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
    
    document.getElementById('current-time').textContent = timeString;
    document.getElementById('current-date').textContent = dateString;
}

// Update attendance display
function updateAttendanceDisplay() {
    if (currentAttendance) {
        document.getElementById('checkInTime').textContent = currentAttendance.check_in || '--:--';
        document.getElementById('checkOutTime').textContent = currentAttendance.check_out || '--:--';
        document.getElementById('totalHours').textContent = currentAttendance.total_hours || '--:--';
        document.getElementById('overtimeHours').textContent = currentAttendance.overtime_hours || '--:--';
        
        // Update button states
        if (currentAttendance.check_in && !currentAttendance.check_out) {
            document.getElementById('checkInBtn').disabled = true;
            document.getElementById('checkInBtn').innerHTML = '<i class="fas fa-check mr-2"></i>Sudah Check-In';
            document.getElementById('checkOutBtn').disabled = false;
        } else if (currentAttendance.check_out) {
            document.getElementById('checkInBtn').disabled = true;
            document.getElementById('checkOutBtn').disabled = true;
            document.getElementById('checkOutBtn').innerHTML = '<i class="fas fa-check mr-2"></i>Sudah Check-Out';
        }
    }
}

// Get current location
function getCurrentLocation() {
    return new Promise((resolve) => {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    resolve(position.coords.latitude + ',' + position.coords.longitude);
                },
                function(error) {
                    console.warn('Location error:', error);
                    resolve('Location not available');
                },
                {
                    timeout: 10000,
                    enableHighAccuracy: false
                }
            );
        } else {
            resolve('Location not available');
        }
    });
}

// Perform check-in
async function performCheckIn() {
    if (!employeeId) {
        showAlert('error', 'Employee information not available. Please refresh the page.');
        return;
    }

    try {
        const result = await SwalHelper.confirm('Konfirmasi Check-In', 'Apakah Anda yakin ingin melakukan check-in sekarang?', 'Ya, Check-In', 'Batal');
        
        if (!result.isConfirmed) return;
        
        $('#loadingModal').modal('show');
        const location = await getCurrentLocation();
        
        const response = await fetch('{{ route("attendance.check-in") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                employee_id: employeeId,
                location: location
            })
        });

        const data = await response.json();
        $('#loadingModal').modal('hide');
        
        if (data.success) {
            showAlert('success', data.message);
            await getEmployeeId(); // Refresh data
            loadAttendanceHistory();
        } else {
            showAlert('error', data.message || 'Terjadi kesalahan saat check-in.');
        }
    } catch (error) {
        $('#loadingModal').modal('hide');
        console.error('Check-in error:', error);
        showAlert('error', 'Terjadi kesalahan. Silakan coba lagi.');
    }
}

// Perform check-out
async function performCheckOut() {
    if (!employeeId) {
        showAlert('error', 'Employee information not available. Please refresh the page.');
        return;
    }

    try {
        const result = await SwalHelper.confirm('Konfirmasi Check-Out', 'Apakah Anda yakin ingin melakukan check-out sekarang?', 'Ya, Check-Out', 'Batal');
        
        if (!result.isConfirmed) return;
        
        $('#loadingModal').modal('show');
        const location = await getCurrentLocation();
        
        const response = await fetch('{{ route("attendance.check-out") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                employee_id: employeeId,
                location: location
            })
        });

        const data = await response.json();
        $('#loadingModal').modal('hide');
        
        if (data.success) {
            showAlert('success', data.message);
            await getEmployeeId(); // Refresh data
            loadAttendanceHistory();
        } else {
            showAlert('error', data.message || 'Terjadi kesalahan saat check-out.');
        }
    } catch (error) {
        $('#loadingModal').modal('hide');
        console.error('Check-out error:', error);
        showAlert('error', 'Terjadi kesalahan. Silakan coba lagi.');
    }
}

// Load attendance history
async function loadAttendanceHistory() {
    try {
        const response = await fetch('{{ route("attendance.history") }}');
        const data = await response.json();
        
        if (data.success) {
            const tbody = document.getElementById('attendanceHistory');
            tbody.innerHTML = '';
            
            if (data.attendance.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">Belum ada data kehadiran</td></tr>';
                return;
            }
            
            data.attendance.forEach(attendance => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${attendance.date}</td>
                    <td>${attendance.check_in}</td>
                    <td>${attendance.check_out}</td>
                    <td>${attendance.total_hours}</td>
                    <td>${attendance.overtime_hours}</td>
                    <td><span class="badge badge-${getStatusBadgeClass(attendance.status)}">${getStatusText(attendance.status)}</span></td>
                `;
                tbody.appendChild(row);
            });
        } else {
            console.error('Failed to load history:', data.message);
        }
    } catch (error) {
        console.error('Error loading attendance history:', error);
    }
}

// Get status badge class
function getStatusBadgeClass(status) {
    const classes = {
        'present': 'success',
        'absent': 'danger',
        'late': 'warning',
        'half_day': 'info',
        'leave': 'secondary',
        'holiday': 'primary'
    };
    return classes[status] || 'secondary';
}

// Get status text
function getStatusText(status) {
    const texts = {
        'present': 'Hadir',
        'absent': 'Tidak Hadir',
        'late': 'Terlambat',
        'half_day': 'Setengah Hari',
        'leave': 'Cuti',
        'holiday': 'Libur'
    };
    return texts[status] || status;
}

// Show alert menggunakan SweetAlert
function showAlert(type, message) {
    if (type === 'success') {
        SwalHelper.toastSuccess(message);
    } else {
        SwalHelper.toastError(message);
    }
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    getEmployeeId(); // Get employee ID first
    updateCurrentTime();
    loadAttendanceHistory();
    
    // Update time every second
    setInterval(updateCurrentTime, 1000);
    
    // Refresh attendance data every 30 seconds
    setInterval(async () => {
        await getEmployeeId();
    }, 30000);
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

.card {
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    margin-bottom: 1rem;
}

.btn-lg {
    padding: 12px 24px;
    font-size: 16px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .btn-lg {
        padding: 10px 20px;
        font-size: 14px;
    }
    
    .info-box-icon {
        height: 60px;
        width: 60px;
        font-size: 30px;
        line-height: 60px;
    }
    
    .info-box-content {
        margin-left: 60px;
    }
    
    .info-box-number {
        font-size: 16px;
    }
}

/* Loading animation */
.spinner-border {
    width: 3rem;
    height: 3rem;
}

/* Status badges */
.badge {
    font-size: 0.75em;
    padding: 0.375em 0.75em;
}

/* Table improvements */
.table th {
    background-color: #f8f9fa;
    border-top: 1px solid #dee2e6;
}

.table td {
    vertical-align: middle;
}

/* Button states */
.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Time display */
#current-time {
    font-size: 2.5rem;
    font-weight: bold;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
}

#current-date {
    font-size: 1.1rem;
    font-weight: 500;
}

/* Card hover effects */
.card:hover {
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 2px 6px rgba(0,0,0,.2);
    transition: box-shadow 0.3s ease;
}

/* Info box hover effects */
.info-box:hover {
    box-shadow: 0 2px 4px rgba(0,0,0,0.15);
    transition: box-shadow 0.3s ease;
}
</style>
@endpush 