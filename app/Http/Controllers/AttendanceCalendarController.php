<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Leave;
use App\Models\Overtime;
use App\Models\Employee;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AttendanceCalendarController extends Controller
{
    /**
     * Display the attendance calendar.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Jika user adalah admin/pemilik company, izinkan akses tanpa validasi employee
        if ($user->isAdmin() || $user->isCompanyOwner()) {
            return view('attendance.calendar');
        }
        
        // Jika user adalah employee, cek employee record
        $employee = Employee::where('user_id', $user->id)->first();
        
        if (!$employee) {
            return redirect()->back()->with('error', 'Employee not found for this user.');
        }

        // For FullCalendar, we don't need to pass data here
        // Data will be loaded via AJAX
        return view('attendance.calendar');
    }

    /**
     * Get calendar data for specific month.
     */
    public function getCalendarData(Request $request)
    {
        $user = Auth::user();
        
        // Jika user adalah admin/pemilik company, tampilkan data semua employee
        if ($user->isAdmin() || $user->isCompanyOwner()) {
            $startDate = $request->get('start', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $endDate = $request->get('end', Carbon::now()->endOfMonth()->format('Y-m-d'));
            
            // Get all employees for current company
            $employees = Employee::where('company_id', $user->company_id)->get();
            
            $allEvents = [];
            $allStatistics = [
                'present_days' => 0,
                'late_days' => 0,
                'absent_days' => 0,
                'leave_days' => 0,
                'overtime_hours' => 0,
                'attendance_rate' => 0
            ];
            
            foreach ($employees as $employee) {
                $events = $this->generateCalendarEvents($employee->id, $startDate, $endDate);
                $statistics = $this->getStatisticsForPeriod($employee->id, $startDate, $endDate);
                
                $allEvents = array_merge($allEvents, $events);
                
                // Aggregate statistics
                $allStatistics['present_days'] += $statistics['present_days'] ?? 0;
                $allStatistics['late_days'] += $statistics['late_days'] ?? 0;
                $allStatistics['absent_days'] += $statistics['absent_days'] ?? 0;
                $allStatistics['leave_days'] += $statistics['leave_days'] ?? 0;
                $allStatistics['overtime_hours'] += $statistics['overtime_hours'] ?? 0;
            }
            
            // Calculate average attendance rate
            $totalEmployees = $employees->count();
            if ($totalEmployees > 0) {
                $allStatistics['attendance_rate'] = round($allStatistics['attendance_rate'] / $totalEmployees, 1);
            }
            
            return response()->json([
                'success' => true,
                'events' => $allEvents,
                'statistics' => $allStatistics
            ]);
        }
        
        // Jika user adalah employee, tampilkan data employee tersebut
        $employee = Employee::where('user_id', $user->id)->first();
        
        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not found',
                'events' => [],
                'statistics' => []
            ]);
        }

        $startDate = $request->get('start', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end', Carbon::now()->endOfMonth()->format('Y-m-d'));
        
        $events = $this->generateCalendarEvents($employee->id, $startDate, $endDate);
        $statistics = $this->getStatisticsForPeriod($employee->id, $startDate, $endDate);
        
        return response()->json([
            'success' => true,
            'events' => $events,
            'statistics' => $statistics
        ]);
    }

    /**
     * Generate calendar data for a specific month.
     */
    private function generateCalendarData($employeeId, $month, $year)
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        
        // Get attendance data
        $attendances = Attendance::where('employee_id', $employeeId)
            ->whereBetween('date', [$startDate, $endDate])
            ->get()
            ->keyBy('date');
        
        // Get leave data
        $leaves = Leave::where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->where(function($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                      ->orWhereBetween('end_date', [$startDate, $endDate])
                      ->orWhere(function($q) use ($startDate, $endDate) {
                          $q->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                      });
            })
            ->get();
        
        // Get overtime data
        $overtimes = Overtime::where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->whereBetween('date', [$startDate, $endDate])
            ->get()
            ->keyBy('date');
        
        // Generate calendar days
        $calendar = [];
        $currentDate = $startDate->copy();
        
        while ($currentDate->lte($endDate)) {
            $dateKey = $currentDate->format('Y-m-d');
            $dayData = [
                'date' => $currentDate->format('Y-m-d'),
                'day' => $currentDate->day,
                'is_today' => $currentDate->isToday(),
                'is_weekend' => $currentDate->isWeekend(),
                'is_past' => $currentDate->isPast(),
                'attendance' => null,
                'leave' => null,
                'overtime' => null,
                'events' => []
            ];
            
            // Add attendance data
            if ($attendances->has($dateKey)) {
                $attendance = $attendances->get($dateKey);
                $dayData['attendance'] = [
                    'check_in' => $attendance->check_in ? Carbon::parse($attendance->check_in)->format('H:i') : null,
                    'check_out' => $attendance->check_out ? Carbon::parse($attendance->check_out)->format('H:i') : null,
                    'status' => $attendance->status,
                    'total_hours' => $attendance->total_hours ?? 0
                ];
                $dayData['events'][] = [
                    'type' => 'attendance',
                    'title' => 'Present',
                    'class' => $this->getAttendanceClass($attendance->status),
                    'icon' => 'fas fa-user-check'
                ];
            }
            
            // Add leave data
            foreach ($leaves as $leave) {
                if ($currentDate->between($leave->start_date, $leave->end_date)) {
                    $dayData['leave'] = [
                        'type' => $leave->leave_type,
                        'reason' => $leave->reason
                    ];
                    $dayData['events'][] = [
                        'type' => 'leave',
                        'title' => ucfirst($leave->leave_type) . ' Leave',
                        'class' => 'bg-warning',
                        'icon' => 'fas fa-calendar-times'
                    ];
                    break;
                }
            }
            
            // Add overtime data
            if ($overtimes->has($dateKey)) {
                $overtime = $overtimes->get($dateKey);
                $dayData['overtime'] = [
                    'type' => $overtime->overtime_type,
                    'start_time' => $overtime->start_time,
                    'end_time' => $overtime->end_time,
                    'total_hours' => $overtime->total_hours,
                    'reason' => $overtime->reason
                ];
                $dayData['events'][] = [
                    'type' => 'overtime',
                    'title' => ucfirst($overtime->overtime_type) . ' Overtime',
                    'class' => 'bg-info',
                    'icon' => 'fas fa-clock'
                ];
            }
            
            $calendar[] = $dayData;
            $currentDate->addDay();
        }
        
        return [
            'calendar' => $calendar,
            'month' => $startDate->format('F Y'),
            'prev_month' => $startDate->copy()->subMonth()->format('Y-m'),
            'next_month' => $startDate->copy()->addMonth()->format('Y-m'),
            'total_days' => $endDate->day,
            'working_days' => $this->countWorkingDays($startDate, $endDate),
            'weekends' => $this->countWeekends($startDate, $endDate)
        ];
    }

    /**
     * Get monthly statistics.
     */
    private function getMonthlyStatistics($employeeId, $month, $year)
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        
        // Attendance statistics
        $attendances = Attendance::where('employee_id', $employeeId)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();
        
        $presentDays = $attendances->where('status', 'present')->count();
        $lateDays = $attendances->where('status', 'late')->count();
        $absentDays = $this->countWorkingDays($startDate, $endDate) - $presentDays - $lateDays;
        
        // Leave statistics
        $leaves = Leave::where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->where(function($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                      ->orWhereBetween('end_date', [$startDate, $endDate])
                      ->orWhere(function($q) use ($startDate, $endDate) {
                          $q->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                      });
            })
            ->get();
        
        $leaveDays = $leaves->sum('total_days');
        
        // Overtime statistics
        $overtimes = Overtime::where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->whereBetween('date', [$startDate, $endDate])
            ->get();
        
        $overtimeHours = $overtimes->sum('total_hours');
        $overtimeDays = $overtimes->count();
        
        return [
            'present_days' => $presentDays,
            'late_days' => $lateDays,
            'absent_days' => $absentDays,
            'leave_days' => $leaveDays,
            'overtime_hours' => $overtimeHours,
            'overtime_days' => $overtimeDays,
            'total_working_days' => $this->countWorkingDays($startDate, $endDate),
            'attendance_rate' => $this->countWorkingDays($startDate, $endDate) > 0 
                ? round((($presentDays + $lateDays) / $this->countWorkingDays($startDate, $endDate)) * 100, 1)
                : 0
        ];
    }

    /**
     * Count working days between two dates.
     */
    private function countWorkingDays($startDate, $endDate)
    {
        $workingDays = 0;
        $currentDate = $startDate->copy();
        
        while ($currentDate->lte($endDate)) {
            if (!$currentDate->isWeekend()) {
                $workingDays++;
            }
            $currentDate->addDay();
        }
        
        return $workingDays;
    }

    /**
     * Count weekends between two dates.
     */
    private function countWeekends($startDate, $endDate)
    {
        $weekends = 0;
        $currentDate = $startDate->copy();
        
        while ($currentDate->lte($endDate)) {
            if ($currentDate->isWeekend()) {
                $weekends++;
            }
            $currentDate->addDay();
        }
        
        return $weekends;
    }

    /**
     * Get attendance status class.
     */
    private function getAttendanceClass($status)
    {
        $classes = [
            'present' => 'bg-success',
            'late' => 'bg-warning',
            'absent' => 'bg-danger',
            'half_day' => 'bg-info'
        ];
        
        return $classes[$status] ?? 'bg-secondary';
    }

    /**
     * Generate calendar events for a specific employee.
     */
    private function generateCalendarEvents($employeeId, $startDate, $endDate)
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        
        // Get employee name for admin view
        $employee = Employee::find($employeeId);
        $employeeName = $employee ? $employee->name : 'Unknown Employee';
        
        $events = [];
        
        // Get attendance data
        $attendances = Attendance::where('employee_id', $employeeId)
            ->whereBetween('date', [$start, $end])
            ->get();
        
        foreach ($attendances as $attendance) {
            $status = $attendance->status;
            $title = $this->getStatusText($status);
            
            // Add employee name for admin view
            $user = Auth::user();
            if ($user->isAdmin() || $user->isCompanyOwner()) {
                $title = $employeeName . ' - ' . $title;
            }
            
            $events[] = [
                'id' => 'attendance_' . $attendance->id,
                'title' => $title,
                'date' => $attendance->date->format('Y-m-d'),
                'status' => $status,
                'check_in' => $attendance->check_in ? Carbon::parse($attendance->check_in)->format('H:i') : null,
                'check_out' => $attendance->check_out ? Carbon::parse($attendance->check_out)->format('H:i') : null,
                'total_hours' => $attendance->total_hours ? number_format($attendance->total_hours, 2) . ' jam' : null,
                'overtime_hours' => $attendance->overtime_hours ? number_format($attendance->overtime_hours, 2) . ' jam' : null,
                'location' => $attendance->check_in_location
            ];
        }
        
        // Get leave data
        $leaves = Leave::where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->where(function($query) use ($start, $end) {
                $query->whereBetween('start_date', [$start, $end])
                      ->orWhereBetween('end_date', [$start, $end])
                      ->orWhere(function($q) use ($start, $end) {
                          $q->where('start_date', '<=', $start)
                            ->where('end_date', '>=', $end);
                      });
            })
            ->get();
        
        foreach ($leaves as $leave) {
            $currentDate = $leave->start_date->copy();
            while ($currentDate->lte($leave->end_date) && $currentDate->lte($end)) {
                if ($currentDate->gte($start)) {
                    $title = ucfirst($leave->leave_type) . ' Leave';
                    
                    // Add employee name for admin view
                    $user = Auth::user();
                    if ($user->isAdmin() || $user->isCompanyOwner()) {
                        $title = $employeeName . ' - ' . $title;
                    }
                    
                    $events[] = [
                        'id' => 'leave_' . $leave->id . '_' . $currentDate->format('Y-m-d'),
                        'title' => $title,
                        'date' => $currentDate->format('Y-m-d'),
                        'status' => 'leave',
                        'check_in' => null,
                        'check_out' => null,
                        'total_hours' => null,
                        'overtime_hours' => null,
                        'location' => null
                    ];
                }
                $currentDate->addDay();
            }
        }
        
        // Get overtime data
        $overtimes = Overtime::where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->whereBetween('date', [$start, $end])
            ->get();
        
        foreach ($overtimes as $overtime) {
            $title = ucfirst($overtime->overtime_type) . ' Overtime';
            
            // Add employee name for admin view
            $user = Auth::user();
            if ($user->isAdmin() || $user->isCompanyOwner()) {
                $title = $employeeName . ' - ' . $title;
            }
            
            $events[] = [
                'id' => 'overtime_' . $overtime->id,
                'title' => $title,
                'date' => $overtime->date->format('Y-m-d'),
                'status' => 'overtime',
                'check_in' => $overtime->start_time,
                'check_out' => $overtime->end_time,
                'total_hours' => $overtime->total_hours ? number_format($overtime->total_hours, 2) . ' jam' : null,
                'overtime_hours' => $overtime->total_hours ? number_format($overtime->total_hours, 2) . ' jam' : null,
                'location' => null
            ];
        }
        
        return $events;
    }

    /**
     * Get statistics for a specific period.
     */
    private function getStatisticsForPeriod($employeeId, $startDate, $endDate)
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        
        // Attendance statistics
        $attendances = Attendance::where('employee_id', $employeeId)
            ->whereBetween('date', [$start, $end])
            ->get();
        
        $presentDays = $attendances->where('status', 'present')->count();
        $lateDays = $attendances->where('status', 'late')->count();
        $absentDays = $this->countWorkingDays($start, $end) - $presentDays - $lateDays;
        
        // Leave statistics
        $leaves = Leave::where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->where(function($query) use ($start, $end) {
                $query->whereBetween('start_date', [$start, $end])
                      ->orWhereBetween('end_date', [$start, $end])
                      ->orWhere(function($q) use ($start, $end) {
                          $q->where('start_date', '<=', $start)
                            ->where('end_date', '>=', $end);
                      });
            })
            ->get();
        
        $leaveDays = $leaves->sum('total_days');
        
        // Overtime statistics
        $overtimes = Overtime::where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->whereBetween('date', [$start, $end])
            ->get();
        
        $overtimeHours = $overtimes->sum('total_hours');
        
        $totalWorkingDays = $this->countWorkingDays($start, $end);
        $attendanceRate = $totalWorkingDays > 0 
            ? round((($presentDays + $lateDays) / $totalWorkingDays) * 100, 1)
            : 0;
        
        return [
            'present_days' => $presentDays,
            'late_days' => $lateDays,
            'absent_days' => max(0, $absentDays),
            'leave_days' => $leaveDays,
            'overtime_hours' => $overtimeHours,
            'attendance_rate' => $attendanceRate
        ];
    }

    /**
     * Get status text.
     */
    private function getStatusText($status)
    {
        $texts = [
            'present' => 'Hadir',
            'late' => 'Terlambat',
            'absent' => 'Tidak Hadir',
            'half_day' => 'Setengah Hari',
            'leave' => 'Cuti',
            'overtime' => 'Lembur'
        ];
        
        return $texts[$status] ?? $status;
    }
} 