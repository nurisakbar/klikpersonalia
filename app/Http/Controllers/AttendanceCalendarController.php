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
        
        // Get employee for current user
        $employee = Employee::where('user_id', $user->id)->first();
        
        if (!$employee) {
            return redirect()->back()->with('error', 'Employee not found for this user.');
        }

        // Get current month and year
        $month = $request->get('month', Carbon::now()->month);
        $year = $request->get('year', Carbon::now()->year);
        
        // Create calendar data
        $calendarData = $this->generateCalendarData($employee->id, $month, $year);
        
        // Get statistics
        $statistics = $this->getMonthlyStatistics($employee->id, $month, $year);

        return view('attendance.calendar', compact('calendarData', 'statistics', 'month', 'year'));
    }

    /**
     * Get calendar data for specific month.
     */
    public function getCalendarData(Request $request)
    {
        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();
        
        if (!$employee) {
            return response()->json(['error' => 'Employee not found'], 404);
        }

        $month = $request->get('month', Carbon::now()->month);
        $year = $request->get('year', Carbon::now()->year);
        
        $calendarData = $this->generateCalendarData($employee->id, $month, $year);
        
        return response()->json($calendarData);
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
} 