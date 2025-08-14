@extends('layouts.app')

@section('title', 'Attendance Calendar')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-9">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-calendar-alt mr-2"></i>
                        Attendance Calendar
                    </h3>
                    <div class="card-tools">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-outline-primary" id="todayBtn">
                                <i class="fas fa-home"></i> Today
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="prevBtn">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="nextBtn">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                        <div class="btn-group ml-2" role="group">
                            <button type="button" class="btn btn-sm btn-outline-info" id="monthBtn">Month</button>
                            <button type="button" class="btn btn-sm btn-outline-info" id="weekBtn">Week</button>
                            <button type="button" class="btn btn-sm btn-outline-info" id="dayBtn">Day</button>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-success ml-2" onclick="exportCalendar()">
                            <i class="fas fa-download"></i> Export
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Calendar Legend -->
                    <div class="calendar-legend mb-3">
                        <div class="d-flex flex-wrap">
                            <div class="legend-item mr-3 mb-2">
                                <span class="legend-color bg-success"></span>
                                <small>Present</small>
                            </div>
                            <div class="legend-item mr-3 mb-2">
                                <span class="legend-color bg-warning"></span>
                                <small>Late</small>
                            </div>
                            <div class="legend-item mr-3 mb-2">
                                <span class="legend-color bg-danger"></span>
                                <small>Absent</small>
                            </div>
                            <div class="legend-item mr-3 mb-2">
                                <span class="legend-color bg-info"></span>
                                <small>Overtime</small>
                            </div>
                            <div class="legend-item mr-3 mb-2">
                                <span class="legend-color bg-secondary"></span>
                                <small>Leave</small>
                            </div>
                        </div>
                    </div>

                    <!-- FullCalendar Container -->
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <!-- Monthly Statistics -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-chart-pie mr-2"></i>
                        Monthly Statistics
                    </h5>
                </div>
                <div class="card-body">
                    <div class="stat-item">
                        <div class="stat-label">Present Days</div>
                        <div class="stat-value text-success" id="presentDays">0</div>
                    </div>
                    
                    <div class="stat-item">
                        <div class="stat-label">Late Days</div>
                        <div class="stat-value text-warning" id="lateDays">0</div>
                    </div>
                    
                    <div class="stat-item">
                        <div class="stat-label">Absent Days</div>
                        <div class="stat-value text-danger" id="absentDays">0</div>
                    </div>
                    
                    <div class="stat-item">
                        <div class="stat-label">Leave Days</div>
                        <div class="stat-value text-info" id="leaveDays">0</div>
                    </div>
                    
                    <div class="stat-item">
                        <div class="stat-label">Overtime Hours</div>
                        <div class="stat-value text-primary" id="overtimeHours">0h</div>
                    </div>
                    
                    <div class="stat-item">
                        <div class="stat-label">Attendance Rate</div>
                        <div class="stat-value text-success" id="attendanceRate">0%</div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-bolt mr-2"></i>
                        Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="quick-actions">
                        <a href="{{ route('attendance.check-in-out') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-sign-in-alt"></i> Check In/Out
                        </a>
                        <a href="{{ route('leaves.create') }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-calendar-plus"></i> Submit Leave
                        </a>
                        <a href="{{ route('overtimes.create') }}" class="btn btn-info btn-sm">
                            <i class="fas fa-clock"></i> Submit Overtime
                        </a>
                        <a href="{{ route('attendance.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-list"></i> View All Records
                        </a>
                    </div>
                </div>
            </div>

            <!-- Calendar Info -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-info-circle mr-2"></i>
                        Calendar Info
                    </h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li><i class="fas fa-circle text-success mr-2"></i> Green: Present</li>
                        <li><i class="fas fa-circle text-warning mr-2"></i> Yellow: Late</li>
                        <li><i class="fas fa-circle text-danger mr-2"></i> Red: Absent</li>
                        <li><i class="fas fa-circle text-info mr-2"></i> Blue: Overtime</li>
                        <li><i class="fas fa-circle text-secondary mr-2"></i> Gray: Leave</li>
                    </ul>
                    <hr>
                    <small class="text-muted">
                        <i class="fas fa-mouse-pointer mr-1"></i>
                        Click on events to see details
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Event Details Modal -->
<div class="modal fade" id="eventModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eventModalTitle">Event Details</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="eventModalBody">
                <!-- Event details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('css')
<!-- FullCalendar CSS -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet">

<style>
.calendar-legend {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border: 1px solid #dee2e6;
}

.legend-item {
    display: flex;
    align-items: center;
}

.legend-color {
    width: 16px;
    height: 16px;
    border-radius: 50%;
    margin-right: 8px;
}

.stat-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #f0f0f0;
}

.stat-item:last-child {
    border-bottom: none;
}

.stat-label {
    font-size: 14px;
    color: #6c757d;
}

.stat-value {
    font-weight: bold;
    font-size: 16px;
}

/* Quick Actions styling */
.quick-actions .btn {
    width: 100%;
    margin-bottom: 15px;
    padding: 12px 18px;
    font-size: 14px;
    border-radius: 8px;
    transition: all 0.3s ease;
    text-align: left;
    display: flex;
    align-items: center;
    font-weight: 500;
}

.quick-actions .btn:last-child {
    margin-bottom: 0;
}

.quick-actions .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(0,0,0,0.15);
}

.quick-actions .btn i {
    margin-right: 12px;
    font-size: 18px;
    width: 20px;
    text-align: center;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .quick-actions .btn {
        margin-bottom: 8px;
        padding: 12px 15px;
        font-size: 13px;
    }
}

/* FullCalendar Custom Styles */
.fc-event {
    cursor: pointer;
    border-radius: 4px;
    font-size: 12px;
    padding: 2px 4px;
}

.fc-event:hover {
    opacity: 0.8;
}

.fc-toolbar-title {
    font-size: 1.5rem;
    font-weight: bold;
}

.fc-button {
    background-color: #007bff;
    border-color: #007bff;
}

.fc-button:hover {
    background-color: #0056b3;
    border-color: #0056b3;
}

.fc-button-active {
    background-color: #0056b3;
    border-color: #0056b3;
}

/* Event colors */
.event-present {
    background-color: #28a745;
    border-color: #28a745;
}

.event-late {
    background-color: #ffc107;
    border-color: #ffc107;
    color: #212529;
}

.event-absent {
    background-color: #dc3545;
    border-color: #dc3545;
}

.event-overtime {
    background-color: #17a2b8;
    border-color: #17a2b8;
}

.event-leave {
    background-color: #6c757d;
    border-color: #6c757d;
}

@media (max-width: 768px) {
    .fc-toolbar {
        flex-direction: column;
        gap: 10px;
    }
    
    .fc-toolbar-chunk {
        display: flex;
        justify-content: center;
    }
}
</style>
@endpush

@push('js')
<!-- FullCalendar JS -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let calendar;
    let currentEvents = [];

    // Initialize FullCalendar
    const calendarEl = document.getElementById('calendar');
    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: '',
            center: '',
            right: ''
        },
        height: 'auto',
        locale: 'id',
        firstDay: 1, // Monday
        selectable: true,
        selectMirror: true,
        dayMaxEvents: true,
        weekends: true,
        events: function(fetchInfo, successCallback, failureCallback) {
            loadCalendarEvents(fetchInfo.start, fetchInfo.end, successCallback);
        },
        eventClick: function(info) {
            showEventDetails(info.event);
        },
        eventDidMount: function(info) {
            // Add tooltips
            $(info.el).tooltip({
                title: info.event.title,
                placement: 'top',
                trigger: 'hover',
                container: 'body'
            });
        },
        loading: function(isLoading) {
            if (isLoading) {
                // Show loading indicator
                $('#calendar').append('<div class="fc-loading">Loading...</div>');
            } else {
                $('.fc-loading').remove();
            }
        }
    });

    calendar.render();

    // Button handlers
    document.getElementById('todayBtn').addEventListener('click', function() {
        calendar.today();
    });

    document.getElementById('prevBtn').addEventListener('click', function() {
        calendar.prev();
    });

    document.getElementById('nextBtn').addEventListener('click', function() {
        calendar.next();
    });

    document.getElementById('monthBtn').addEventListener('click', function() {
        calendar.changeView('dayGridMonth');
    });

    document.getElementById('weekBtn').addEventListener('click', function() {
        calendar.changeView('timeGridWeek');
    });

    document.getElementById('dayBtn').addEventListener('click', function() {
        calendar.changeView('timeGridDay');
    });

    // Load calendar events
    function loadCalendarEvents(start, end, successCallback) {
        const startDate = start.toISOString().split('T')[0];
        const endDate = end.toISOString().split('T')[0];

        fetch(`{{ route('attendance.calendar.data') }}?start=${startDate}&end=${endDate}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const events = formatEvents(data.events);
                    currentEvents = events;
                    successCallback(events);
                    updateStatistics(data.statistics);
                } else {
                    successCallback([]);
                }
            })
            .catch(error => {
                console.error('Error loading calendar events:', error);
                successCallback([]);
            });
    }

    // Format events for FullCalendar
    function formatEvents(events) {
        return events.map(event => ({
            id: event.id,
            title: event.title,
            start: event.date,
            end: event.date,
            className: `event-${event.status}`,
            extendedProps: {
                check_in: event.check_in,
                check_out: event.check_out,
                total_hours: event.total_hours,
                overtime_hours: event.overtime_hours,
                status: event.status,
                location: event.location
            }
        }));
    }

    // Show event details modal
    function showEventDetails(event) {
        const props = event.extendedProps;
        const date = new Date(event.start).toLocaleDateString('id-ID', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });

        let details = `
            <div class="event-details">
                <h6>${event.title}</h6>
                <p class="text-muted">${date}</p>
                <hr>
                <div class="row">
                    <div class="col-6">
                        <strong>Check In:</strong><br>
                        <span class="text-primary">${props.check_in || '--:--'}</span>
                    </div>
                    <div class="col-6">
                        <strong>Check Out:</strong><br>
                        <span class="text-primary">${props.check_out || '--:--'}</span>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-6">
                        <strong>Total Hours:</strong><br>
                        <span class="text-success">${props.total_hours || '--:--'}</span>
                    </div>
                    <div class="col-6">
                        <strong>Overtime:</strong><br>
                        <span class="text-info">${props.overtime_hours || '--:--'}</span>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-12">
                        <strong>Status:</strong><br>
                        <span class="badge badge-${getStatusBadgeClass(props.status)}">${getStatusText(props.status)}</span>
                    </div>
                </div>
                ${props.location ? `
                <div class="row mt-2">
                    <div class="col-12">
                        <strong>Location:</strong><br>
                        <small class="text-muted">${props.location}</small>
                    </div>
                </div>
                ` : ''}
            </div>
        `;

        document.getElementById('eventModalTitle').textContent = event.title;
        document.getElementById('eventModalBody').innerHTML = details;
        $('#eventModal').modal('show');
    }

    // Update statistics
    function updateStatistics(statistics) {
        document.getElementById('presentDays').textContent = statistics.present_days || 0;
        document.getElementById('lateDays').textContent = statistics.late_days || 0;
        document.getElementById('absentDays').textContent = statistics.absent_days || 0;
        document.getElementById('leaveDays').textContent = statistics.leave_days || 0;
        document.getElementById('overtimeHours').textContent = (statistics.overtime_hours || 0) + 'h';
        document.getElementById('attendanceRate').textContent = (statistics.attendance_rate || 0) + '%';
    }

    // Helper functions
    function getStatusBadgeClass(status) {
        const classes = {
            'present': 'success',
            'late': 'warning',
            'absent': 'danger',
            'overtime': 'info',
            'leave': 'secondary'
        };
        return classes[status] || 'secondary';
    }

    function getStatusText(status) {
        const texts = {
            'present': 'Hadir',
            'late': 'Terlambat',
            'absent': 'Tidak Hadir',
            'overtime': 'Lembur',
            'leave': 'Cuti'
        };
        return texts[status] || status;
    }

    // Export calendar function
    window.exportCalendar = function() {
        const currentView = calendar.view.type;
        const currentDate = calendar.getDate();
        
        // Create export data
        const exportData = {
            view: currentView,
            date: currentDate.toISOString(),
            events: currentEvents
        };

        // For now, just show alert. You can implement actual export later
        Swal.fire({
            title: 'Export Calendar',
            text: 'Export functionality will be implemented here',
            icon: 'info',
            confirmButtonText: 'OK'
        });
    };
});
</script>
@endpush 