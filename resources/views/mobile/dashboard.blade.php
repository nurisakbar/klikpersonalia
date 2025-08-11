<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll Mobile App</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .mobile-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .profile-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .quick-action-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.2s;
        }
        
        .quick-action-card:hover {
            transform: translateY(-2px);
        }
        
        .quick-action-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.5rem;
            color: white;
        }
        
        .attendance-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .attendance-btn {
            border-radius: 25px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            border: none;
            transition: all 0.3s;
        }
        
        .check-in-btn {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }
        
        .check-out-btn {
            background: linear-gradient(135deg, #dc3545, #fd7e14);
            color: white;
        }
        
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 1rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .stat-item {
            text-align: center;
            padding: 0.5rem;
        }
        
        .stat-number {
            font-size: 1.5rem;
            font-weight: bold;
            color: #667eea;
        }
        
        .stat-label {
            font-size: 0.875rem;
            color: #6c757d;
        }
        
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            border-top: 1px solid #dee2e6;
            padding: 0.5rem;
            z-index: 1000;
        }
        
        .nav-item {
            text-align: center;
            padding: 0.5rem;
            color: #6c757d;
            text-decoration: none;
            font-size: 0.875rem;
        }
        
        .nav-item.active {
            color: #667eea;
        }
        
        .nav-icon {
            font-size: 1.25rem;
            margin-bottom: 0.25rem;
        }
        
        .content-wrapper {
            padding-bottom: 80px;
        }
        
        .loading {
            display: none;
            text-align: center;
            padding: 2rem;
        }
        
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
        }
        
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <!-- Mobile Header -->
    <div class="mobile-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Payroll Mobile</h5>
                <small>Welcome back, {{ auth()->user()->name }}</small>
            </div>
            <div class="dropdown">
                <button class="btn btn-link text-white" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" onclick="showProfile()"><i class="fas fa-user me-2"></i>Profile</a></li>
                    <li><a class="dropdown-item" href="#" onclick="showSettings()"><i class="fas fa-cog me-2"></i>Settings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#" onclick="logout()"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="content-wrapper">
        <div class="container-fluid p-3">
            <!-- Loading -->
            <div class="loading">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>

            <!-- Messages -->
            <div id="messages"></div>

            <!-- Profile Card -->
            <div class="profile-card">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <img src="{{ auth()->user()->avatar_url ?? 'https://via.placeholder.com/60' }}" 
                             alt="Profile" class="rounded-circle" width="60" height="60">
                    </div>
                    <div>
                        <h6 class="mb-1">{{ auth()->user()->name }}</h6>
                        <small class="text-muted">{{ auth()->user()->employee->position ?? 'Employee' }}</small>
                        <br>
                        <small class="text-muted">{{ auth()->user()->employee->department ?? 'Department' }}</small>
                    </div>
                </div>
            </div>

            <!-- Attendance Card -->
            <div class="attendance-card">
                <h6 class="mb-3">Today's Attendance</h6>
                <div id="attendance-status">
                    <div class="text-center">
                        <p class="text-muted">Loading attendance...</p>
                    </div>
                </div>
                <div class="d-grid gap-2 mt-3">
                    <button class="btn attendance-btn check-in-btn" id="check-in-btn" onclick="checkInOut('check_in')">
                        <i class="fas fa-sign-in-alt me-2"></i>Check In
                    </button>
                    <button class="btn attendance-btn check-out-btn" id="check-out-btn" onclick="checkInOut('check_out')" style="display: none;">
                        <i class="fas fa-sign-out-alt me-2"></i>Check Out
                    </button>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row">
                <div class="col-6">
                    <div class="quick-action-card" onclick="showPayslips()">
                        <div class="quick-action-icon" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <h6>Payslips</h6>
                        <small class="text-muted">View salary details</small>
                    </div>
                </div>
                <div class="col-6">
                    <div class="quick-action-card" onclick="showLeaveRequest()">
                        <div class="quick-action-icon" style="background: linear-gradient(135deg, #28a745, #20c997);">
                            <i class="fas fa-calendar-plus"></i>
                        </div>
                        <h6>Leave Request</h6>
                        <small class="text-muted">Submit leave application</small>
                    </div>
                </div>
                <div class="col-6">
                    <div class="quick-action-card" onclick="showOvertimeRequest()">
                        <div class="quick-action-icon" style="background: linear-gradient(135deg, #fd7e14, #ffc107);">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h6>Overtime</h6>
                        <small class="text-muted">Request overtime</small>
                    </div>
                </div>
                <div class="col-6">
                    <div class="quick-action-card" onclick="showAttendanceHistory()">
                        <div class="quick-action-icon" style="background: linear-gradient(135deg, #6f42c1, #e83e8c);">
                            <i class="fas fa-history"></i>
                        </div>
                        <h6>History</h6>
                        <small class="text-muted">View attendance history</small>
                    </div>
                </div>
            </div>

            <!-- Monthly Stats -->
            <div class="stats-card">
                <h6 class="mb-3">This Month</h6>
                <div class="row">
                    <div class="col-3">
                        <div class="stat-item">
                            <div class="stat-number" id="present-days">-</div>
                            <div class="stat-label">Present</div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="stat-item">
                            <div class="stat-number" id="absent-days">-</div>
                            <div class="stat-label">Absent</div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="stat-item">
                            <div class="stat-number" id="late-days">-</div>
                            <div class="stat-label">Late</div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="stat-item">
                            <div class="stat-number" id="total-hours">-</div>
                            <div class="stat-label">Hours</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Leave Balance -->
            <div class="stats-card">
                <h6 class="mb-3">Leave Balance</h6>
                <div class="row">
                    <div class="col-4">
                        <div class="stat-item">
                            <div class="stat-number" id="annual-leave">-</div>
                            <div class="stat-label">Annual</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="stat-item">
                            <div class="stat-number" id="sick-leave">-</div>
                            <div class="stat-label">Sick</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="stat-item">
                            <div class="stat-number" id="personal-leave">-</div>
                            <div class="stat-label">Personal</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Navigation -->
    <div class="bottom-nav">
        <div class="row">
            <div class="col-3">
                <a href="#" class="nav-item active" onclick="showDashboard()">
                    <div class="nav-icon"><i class="fas fa-home"></i></div>
                    <div>Home</div>
                </a>
            </div>
            <div class="col-3">
                <a href="#" class="nav-item" onclick="showAttendance()">
                    <div class="nav-icon"><i class="fas fa-clock"></i></div>
                    <div>Attendance</div>
                </a>
            </div>
            <div class="col-3">
                <a href="#" class="nav-item" onclick="showPayroll()">
                    <div class="nav-icon"><i class="fas fa-money-bill"></i></div>
                    <div>Payroll</div>
                </a>
            </div>
            <div class="col-3">
                <a href="#" class="nav-item" onclick="showProfile()">
                    <div class="nav-icon"><i class="fas fa-user"></i></div>
                    <div>Profile</div>
                </a>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Global variables
        let currentAttendance = null;
        
        // Initialize app
        document.addEventListener('DOMContentLoaded', function() {
            loadDashboard();
        });
        
        // Load dashboard data
        function loadDashboard() {
            showLoading(true);
            
            fetch('/api/mobile/dashboard', {
                method: 'GET',
                headers: {
                    'Authorization': 'Bearer ' + getToken(),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                showLoading(false);
                if (data.success) {
                    updateDashboard(data.data);
                } else {
                    showError(data.message);
                }
            })
            .catch(error => {
                showLoading(false);
                showError('Failed to load dashboard data');
                console.error('Error:', error);
            });
        }
        
        // Update dashboard with data
        function updateDashboard(data) {
            // Update attendance status
            updateAttendanceStatus(data.today_attendance);
            
            // Update monthly stats
            if (data.monthly_summary) {
                document.getElementById('present-days').textContent = data.monthly_summary.present_days;
                document.getElementById('absent-days').textContent = data.monthly_summary.absent_days;
                document.getElementById('late-days').textContent = data.monthly_summary.late_days;
                document.getElementById('total-hours').textContent = data.monthly_summary.total_hours;
            }
            
            // Update leave balance
            if (data.leave_balance) {
                document.getElementById('annual-leave').textContent = data.leave_balance.annual.remaining;
                document.getElementById('sick-leave').textContent = data.leave_balance.sick.remaining;
                document.getElementById('personal-leave').textContent = data.leave_balance.personal.remaining;
            }
        }
        
        // Update attendance status
        function updateAttendanceStatus(attendance) {
            const statusDiv = document.getElementById('attendance-status');
            const checkInBtn = document.getElementById('check-in-btn');
            const checkOutBtn = document.getElementById('check-out-btn');
            
            if (attendance) {
                currentAttendance = attendance;
                
                let statusHtml = `
                    <div class="row">
                        <div class="col-6">
                            <small class="text-muted">Check In</small>
                            <div>${attendance.check_in ? formatTime(attendance.check_in) : 'Not checked in'}</div>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Check Out</small>
                            <div>${attendance.check_out ? formatTime(attendance.check_out) : 'Not checked out'}</div>
                        </div>
                    </div>
                `;
                
                statusDiv.innerHTML = statusHtml;
                
                if (attendance.check_in && !attendance.check_out) {
                    checkInBtn.style.display = 'none';
                    checkOutBtn.style.display = 'block';
                } else if (attendance.check_in && attendance.check_out) {
                    checkInBtn.style.display = 'none';
                    checkOutBtn.style.display = 'none';
                } else {
                    checkInBtn.style.display = 'block';
                    checkOutBtn.style.display = 'none';
                }
            } else {
                statusDiv.innerHTML = '<div class="text-center"><p class="text-muted">No attendance record for today</p></div>';
                checkInBtn.style.display = 'block';
                checkOutBtn.style.display = 'none';
            }
        }
        
        // Check in/out function
        function checkInOut(action) {
            if (!navigator.geolocation) {
                showError('Geolocation is not supported by this browser');
                return;
            }
            
            navigator.geolocation.getCurrentPosition(function(position) {
                const data = {
                    action: action,
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude,
                    location: 'Current Location',
                    device_info: navigator.userAgent
                };
                
                fetch('/api/mobile/attendance/check-in-out', {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + getToken(),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showSuccess(data.message);
                        loadDashboard(); // Reload dashboard
                    } else {
                        showError(data.message);
                    }
                })
                .catch(error => {
                    showError('Failed to process attendance');
                    console.error('Error:', error);
                });
            }, function(error) {
                showError('Unable to get location. Please enable location services.');
            });
        }
        
        // Utility functions
        function showLoading(show) {
            document.querySelector('.loading').style.display = show ? 'block' : 'none';
        }
        
        function showError(message) {
            const messagesDiv = document.getElementById('messages');
            messagesDiv.innerHTML = `<div class="error-message">${message}</div>`;
            setTimeout(() => messagesDiv.innerHTML = '', 5000);
        }
        
        function showSuccess(message) {
            const messagesDiv = document.getElementById('messages');
            messagesDiv.innerHTML = `<div class="success-message">${message}</div>`;
            setTimeout(() => messagesDiv.innerHTML = '', 5000);
        }
        
        function formatTime(timeString) {
            return new Date(timeString).toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit'
            });
        }
        
        function getToken() {
            // Get token from localStorage or session
            return localStorage.getItem('mobile_token') || '';
        }
        
        // Navigation functions (placeholders)
        function showDashboard() { /* Implementation */ }
        function showAttendance() { /* Implementation */ }
        function showPayroll() { /* Implementation */ }
        function showProfile() { /* Implementation */ }
        function showSettings() { /* Implementation */ }
        function showPayslips() { /* Implementation */ }
        function showLeaveRequest() { /* Implementation */ }
        function showOvertimeRequest() { /* Implementation */ }
        function showAttendanceHistory() { /* Implementation */ }
        
        function logout() {
            fetch('/api/mobile/logout', {
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + getToken(),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(() => {
                localStorage.removeItem('mobile_token');
                window.location.href = '/mobile/login';
            })
            .catch(error => {
                console.error('Logout error:', error);
                localStorage.removeItem('mobile_token');
                window.location.href = '/mobile/login';
            });
        }
    </script>
</body>
</html> 