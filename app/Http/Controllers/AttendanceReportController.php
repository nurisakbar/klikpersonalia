<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Leave;
use App\Models\Overtime;
use App\Models\Employee;
use App\Models\Company;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttendanceReportController extends Controller
{
    /**
     * Display the main reports dashboard.
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get company data
        $company = Company::find($user->company_id);
        
        // Get current month statistics
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        
        $monthlyStats = $this->getMonthlyCompanyStats($currentMonth, $currentYear, $user->company_id);
        
        // Get recent activities
        $recentActivities = $this->getRecentActivities($user->company_id);
        
        return view('reports.index', compact('monthlyStats', 'recentActivities', 'company'));
    }

    /**
     * Display individual employee report.
     */
    public function individual(Request $request)
    {
        $user = Auth::user();
        
        // Get employee for current user
        $employee = Employee::where('user_id', $user->id)->first();
        
        if (!$employee) {
            return redirect()->back()->with('error', 'Employee not found for this user.');
        }

        // Get date range
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        
        // Get attendance data
        $attendances = Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'desc')
            ->get();
        
        // Get leave data
        $leaves = Leave::where('employee_id', $employee->id)
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
        $overtimes = Overtime::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->whereBetween('date', [$startDate, $endDate])
            ->get();
        
        // Calculate statistics
        $statistics = $this->calculateIndividualStatistics($attendances, $leaves, $overtimes, $startDate, $endDate);
        
        return view('reports.individual', compact('attendances', 'leaves', 'overtimes', 'statistics', 'employee', 'startDate', 'endDate'));
    }

    /**
     * Display team/department report (for managers/HR).
     */
    public function team(Request $request)
    {
        $user = Auth::user();
        
        // Check if user has permission
        if (!in_array($user->role, ['admin', 'hr', 'manager'])) {
            return redirect()->back()->with('error', 'You do not have permission to view team reports.');
        }

        // Get date range
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $department = $request->get('department', '');
        
        // Get employees
        $employeesQuery = Employee::where('company_id', $user->company_id);
        if ($department) {
            $employeesQuery->where('department', $department);
        }
        $employees = $employeesQuery->get();
        
        // Get team statistics
        $teamStats = $this->getTeamStatistics($employees, $startDate, $endDate);
        
        // Get departments for filter
        $departments = Employee::where('company_id', $user->company_id)
            ->distinct()
            ->pluck('department')
            ->filter()
            ->values();
        
        return view('reports.team', compact('teamStats', 'employees', 'departments', 'startDate', 'endDate', 'department'));
    }

    /**
     * Display company-wide report (for admin/HR).
     */
    public function company(Request $request)
    {
        $user = Auth::user();
        
        // Check if user has permission
        if (!in_array($user->role, ['admin', 'hr'])) {
            return redirect()->back()->with('error', 'You do not have permission to view company reports.');
        }

        // Get date range
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        
        // Get company statistics
        $companyStats = $this->getCompanyStatistics($user->company_id, $startDate, $endDate);
        
        // Get department breakdown
        $departmentStats = $this->getDepartmentBreakdown($user->company_id, $startDate, $endDate);
        
        // Get attendance trends
        $attendanceTrends = $this->getAttendanceTrends($user->company_id, $startDate, $endDate);
        
        return view('reports.company', compact('companyStats', 'departmentStats', 'attendanceTrends', 'startDate', 'endDate'));
    }

    /**
     * Export report data.
     */
    public function export(Request $request)
    {
        $user = Auth::user();
        $reportType = $request->get('type', 'individual');
        $format = $request->get('format', 'pdf');
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        
        switch ($reportType) {
            case 'individual':
                return $this->exportIndividualReport($user, $startDate, $endDate, $format);
            case 'team':
                if (!in_array($user->role, ['admin', 'hr', 'manager'])) {
                    return redirect()->back()->with('error', 'Permission denied.');
                }
                return $this->exportTeamReport($user, $startDate, $endDate, $format);
            case 'company':
                if (!in_array($user->role, ['admin', 'hr'])) {
                    return redirect()->back()->with('error', 'Permission denied.');
                }
                return $this->exportCompanyReport($user, $startDate, $endDate, $format);
            default:
                return redirect()->back()->with('error', 'Invalid report type.');
        }
    }

    /**
     * Get monthly company statistics.
     */
    private function getMonthlyCompanyStats($month, $year, $companyId)
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        
        // Get total employees
        $totalEmployees = Employee::where('company_id', $companyId)->count();
        
        // Get attendance statistics
        $attendances = Attendance::where('company_id', $companyId)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();
        
        $presentDays = $attendances->where('status', 'present')->count();
        $lateDays = $attendances->where('status', 'late')->count();
        $totalAttendanceDays = $presentDays + $lateDays;
        
        // Get leave statistics
        $leaves = Leave::where('company_id', $companyId)
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
        
        // Get overtime statistics
        $overtimes = Overtime::where('company_id', $companyId)
            ->where('status', 'approved')
            ->whereBetween('date', [$startDate, $endDate])
            ->get();
        
        $overtimeHours = $overtimes->sum('total_hours');
        
        return [
            'total_employees' => $totalEmployees,
            'present_days' => $presentDays,
            'late_days' => $lateDays,
            'leave_days' => $leaveDays,
            'overtime_hours' => $overtimeHours,
            'attendance_rate' => $totalEmployees > 0 ? round(($totalAttendanceDays / ($totalEmployees * $startDate->daysInMonth)) * 100, 1) : 0
        ];
    }

    /**
     * Get recent activities.
     */
    private function getRecentActivities($companyId)
    {
        $activities = collect();
        
        // Get recent attendances
        $recentAttendances = Attendance::with('employee')
            ->where('company_id', $companyId)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        foreach ($recentAttendances as $attendance) {
            $activities->push([
                'type' => 'attendance',
                'employee' => $attendance->employee->name,
                'action' => 'Checked in at ' . Carbon::parse($attendance->check_in)->format('H:i'),
                'date' => $attendance->date,
                'time' => $attendance->created_at
            ]);
        }
        
        // Get recent leaves
        $recentLeaves = Leave::with('employee')
            ->where('company_id', $companyId)
            ->where('status', 'approved')
            ->orderBy('approved_at', 'desc')
            ->limit(5)
            ->get();
        
        foreach ($recentLeaves as $leave) {
            $activities->push([
                'type' => 'leave',
                'employee' => $leave->employee->name,
                'action' => 'Leave approved: ' . $leave->leave_type,
                'date' => $leave->start_date,
                'time' => $leave->approved_at
            ]);
        }
        
        // Get recent overtimes
        $recentOvertimes = Overtime::with('employee')
            ->where('company_id', $companyId)
            ->where('status', 'approved')
            ->orderBy('approved_at', 'desc')
            ->limit(5)
            ->get();
        
        foreach ($recentOvertimes as $overtime) {
            $activities->push([
                'type' => 'overtime',
                'employee' => $overtime->employee->name,
                'action' => 'Overtime approved: ' . $overtime->total_hours . ' hours',
                'date' => $overtime->date,
                'time' => $overtime->approved_at
            ]);
        }
        
        return $activities->sortByDesc('time')->take(10);
    }

    /**
     * Calculate individual statistics.
     */
    private function calculateIndividualStatistics($attendances, $leaves, $overtimes, $startDate, $endDate)
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        
        // Calculate working days
        $workingDays = 0;
        $currentDate = $start->copy();
        while ($currentDate->lte($end)) {
            if (!$currentDate->isWeekend()) {
                $workingDays++;
            }
            $currentDate->addDay();
        }
        
        // Attendance statistics
        $presentDays = $attendances->where('status', 'present')->count();
        $lateDays = $attendances->where('status', 'late')->count();
        $absentDays = $workingDays - $presentDays - $lateDays;
        
        // Leave statistics
        $leaveDays = $leaves->sum('total_days');
        
        // Overtime statistics
        $overtimeHours = $overtimes->sum('total_hours');
        $overtimeDays = $overtimes->count();
        
        return [
            'working_days' => $workingDays,
            'present_days' => $presentDays,
            'late_days' => $lateDays,
            'absent_days' => $absentDays,
            'leave_days' => $leaveDays,
            'overtime_hours' => $overtimeHours,
            'overtime_days' => $overtimeDays,
            'attendance_rate' => $workingDays > 0 ? round((($presentDays + $lateDays) / $workingDays) * 100, 1) : 0
        ];
    }

    /**
     * Get team statistics.
     */
    private function getTeamStatistics($employees, $startDate, $endDate)
    {
        $stats = [];
        
        foreach ($employees as $employee) {
            $attendances = Attendance::where('employee_id', $employee->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->get();
            
            $leaves = Leave::where('employee_id', $employee->id)
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
            
            $overtimes = Overtime::where('employee_id', $employee->id)
                ->where('status', 'approved')
                ->whereBetween('date', [$startDate, $endDate])
                ->get();
            
            $employeeStats = $this->calculateIndividualStatistics($attendances, $leaves, $overtimes, $startDate, $endDate);
            
            $stats[] = [
                'employee' => $employee,
                'statistics' => $employeeStats
            ];
        }
        
        return $stats;
    }

    /**
     * Get company statistics.
     */
    private function getCompanyStatistics($companyId, $startDate, $endDate)
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        
        // Get total employees
        $totalEmployees = Employee::where('company_id', $companyId)->count();
        
        // Get attendance data
        $attendances = Attendance::where('company_id', $companyId)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();
        
        $presentDays = $attendances->where('status', 'present')->count();
        $lateDays = $attendances->where('status', 'late')->count();
        
        // Get leave data
        $leaves = Leave::where('company_id', $companyId)
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
        
        // Get overtime data
        $overtimes = Overtime::where('company_id', $companyId)
            ->where('status', 'approved')
            ->whereBetween('date', [$startDate, $endDate])
            ->get();
        
        $overtimeHours = $overtimes->sum('total_hours');
        
        return [
            'total_employees' => $totalEmployees,
            'present_days' => $presentDays,
            'late_days' => $lateDays,
            'leave_days' => $leaveDays,
            'overtime_hours' => $overtimeHours,
            'period_days' => $start->diffInDays($end) + 1
        ];
    }

    /**
     * Get department breakdown.
     */
    private function getDepartmentBreakdown($companyId, $startDate, $endDate)
    {
        $departments = Employee::where('company_id', $companyId)
            ->distinct()
            ->pluck('department')
            ->filter();
        
        $breakdown = [];
        
        foreach ($departments as $department) {
            $employees = Employee::where('company_id', $companyId)
                ->where('department', $department)
                ->get();
            
            $totalEmployees = $employees->count();
            $totalPresent = 0;
            $totalLate = 0;
            $totalLeave = 0;
            $totalOvertime = 0;
            
            foreach ($employees as $employee) {
                $attendances = Attendance::where('employee_id', $employee->id)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->get();
                
                $totalPresent += $attendances->where('status', 'present')->count();
                $totalLate += $attendances->where('status', 'late')->count();
                
                $leaves = Leave::where('employee_id', $employee->id)
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
                
                $totalLeave += $leaves->sum('total_days');
                
                $overtimes = Overtime::where('employee_id', $employee->id)
                    ->where('status', 'approved')
                    ->whereBetween('date', [$startDate, $endDate])
                    ->get();
                
                $totalOvertime += $overtimes->sum('total_hours');
            }
            
            $breakdown[] = [
                'department' => $department,
                'total_employees' => $totalEmployees,
                'present_days' => $totalPresent,
                'late_days' => $totalLate,
                'leave_days' => $totalLeave,
                'overtime_hours' => $totalOvertime
            ];
        }
        
        return $breakdown;
    }

    /**
     * Get attendance trends.
     */
    private function getAttendanceTrends($companyId, $startDate, $endDate)
    {
        $trends = [];
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $current = $start->copy();
        
        while ($current->lte($end)) {
            $date = $current->format('Y-m-d');
            
            $attendances = Attendance::where('company_id', $companyId)
                ->where('date', $date)
                ->get();
            
            $present = $attendances->where('status', 'present')->count();
            $late = $attendances->where('status', 'late')->count();
            $total = $present + $late;
            
            $trends[] = [
                'date' => $date,
                'present' => $present,
                'late' => $late,
                'total' => $total
            ];
            
            $current->addDay();
        }
        
        return $trends;
    }

    /**
     * Export individual report.
     */
    private function exportIndividualReport($user, $startDate, $endDate, $format)
    {
        // Implementation for individual report export
        return response()->json(['message' => 'Export functionality will be implemented']);
    }

    /**
     * Export team report.
     */
    private function exportTeamReport($user, $startDate, $endDate, $format)
    {
        // Implementation for team report export
        return response()->json(['message' => 'Export functionality will be implemented']);
    }

    /**
     * Export company report.
     */
    private function exportCompanyReport($user, $startDate, $endDate, $format)
    {
        // Implementation for company report export
        return response()->json(['message' => 'Export functionality will be implemented']);
    }
} 