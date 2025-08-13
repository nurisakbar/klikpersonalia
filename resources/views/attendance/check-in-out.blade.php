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
                                <h4 class="text-info">Welcome, {{ auth()->user()->name }}</h4>
                                <p class="text-muted">{{ auth()->user()->position }} - {{ auth()->user()->department }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Check In/Out Buttons -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h4><i class="fas fa-sign-in-alt mr-2"></i>Check In</h4>
                                    <p class="mb-3">Record your arrival time</p>
                                    <button type="button" class="btn btn-light btn-lg" id="checkInBtn" onclick="performCheckIn()">
                                        <i class="fas fa-play mr-2"></i>Check In Now
                                    </button>
                                    <div id="checkInStatus" class="mt-2"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-danger text-white">
                                <div class="card-body text-center">
                                    <h4><i class="fas fa-sign-out-alt mr-2"></i>Check Out</h4>
                                    <p class="mb-3">Record your departure time</p>
                                    <button type="button" class="btn btn-light btn-lg" id="checkOutBtn" onclick="performCheckOut()">
                                        <i class="fas fa-stop mr-2"></i>Check Out Now
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
                                        Today's Attendance Summary
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
                                                    <span class="info-box-text">Check In Time</span>
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
                                                    <span class="info-box-text">Check Out Time</span>
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
                                                    <span class="info-box-text">Total Hours</span>
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
                                                    <span class="info-box-text">Overtime</span>
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
                                        Recent Attendance History
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Check In</th>
                                                    <th>Check Out</th>
                                                    <th>Total Hours</th>
                                                    <th>Overtime</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody id="attendanceHistory">
                                                <!-- Will be populated by JavaScript -->
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
                <p class="mt-2">Processing...</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentAttendance = null;
let employeeId = null;

// Get employee ID for the current user
async function getEmployeeId() {
    try {
        const response = await fetch('{{ route("attendance.current") }}');
        const data = await response.json();
        
        if (data.success && data.employee_id) {
            employeeId = data.employee_id;
        } else {
            showAlert('error', 'Employee not found. Please contact administrator.');
        }
    } catch (error) {
        console.error('Error fetching employee ID:', error);
        showAlert('error', 'Failed to get employee information.');
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

// Get current attendance status
function getCurrentAttendance() {
    fetch('{{ route("attendance.current") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                currentAttendance = data.attendance;
                updateAttendanceDisplay();
            }
        })
        .catch(error => {
            console.error('Error fetching attendance:', error);
        });
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
            document.getElementById('checkInBtn').innerHTML = '<i class="fas fa-check mr-2"></i>Already Checked In';
            document.getElementById('checkOutBtn').disabled = false;
        } else if (currentAttendance.check_out) {
            document.getElementById('checkInBtn').disabled = true;
            document.getElementById('checkOutBtn').disabled = true;
            document.getElementById('checkOutBtn').innerHTML = '<i class="fas fa-check mr-2"></i>Already Checked Out';
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
                    resolve('Location not available');
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

    if (confirm('Are you sure you want to check in?')) {
        $('#loadingModal').modal('show');
        
        const location = await getCurrentLocation();
        
        fetch('{{ route("attendance.check-in") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                employee_id: employeeId,
                location: location
            })
        })
        .then(response => response.json())
        .then(data => {
            $('#loadingModal').modal('hide');
            
            if (data.success) {
                showAlert('success', data.message);
                getCurrentAttendance();
                loadAttendanceHistory();
            } else {
                showAlert('error', data.message);
            }
        })
        .catch(error => {
            $('#loadingModal').modal('hide');
            console.error('Error:', error);
            showAlert('error', 'An error occurred. Please try again.');
        });
    }
}

// Perform check-out
async function performCheckOut() {
    if (!employeeId) {
        showAlert('error', 'Employee information not available. Please refresh the page.');
        return;
    }

    if (confirm('Are you sure you want to check out?')) {
        $('#loadingModal').modal('show');
        
        const location = await getCurrentLocation();
        
        fetch('{{ route("attendance.check-out") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                employee_id: employeeId,
                location: location
            })
        })
        .then(response => response.json())
        .then(data => {
            $('#loadingModal').modal('hide');
            
            if (data.success) {
                showAlert('success', data.message);
                getCurrentAttendance();
                loadAttendanceHistory();
            } else {
                showAlert('error', data.message);
            }
        })
        .catch(error => {
            $('#loadingModal').modal('hide');
            console.error('Error:', error);
            showAlert('error', 'An error occurred. Please try again.');
        });
    }
}

// Load attendance history
function loadAttendanceHistory() {
    fetch('{{ route("attendance.history") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const tbody = document.getElementById('attendanceHistory');
                tbody.innerHTML = '';
                
                data.attendance.forEach(attendance => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${attendance.date}</td>
                        <td>${attendance.check_in || '--:--'}</td>
                        <td>${attendance.check_out || '--:--'}</td>
                        <td>${attendance.total_hours || '--:--'}</td>
                        <td>${attendance.overtime_hours || '--:--'}</td>
                        <td><span class="badge badge-${getStatusBadgeClass(attendance.status)}">${getStatusText(attendance.status)}</span></td>
                    `;
                    tbody.appendChild(row);
                });
            }
        })
        .catch(error => {
            console.error('Error loading attendance history:', error);
        });
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
    getCurrentAttendance();
    loadAttendanceHistory();
    
    // Update time every second
    setInterval(updateCurrentTime, 1000);
    
    // Refresh attendance data every 30 seconds
    setInterval(getCurrentAttendance, 30000);
});
</script>
@endpush

@push('styles')
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
</style>
@endpush 