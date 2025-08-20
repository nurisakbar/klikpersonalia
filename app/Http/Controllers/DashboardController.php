<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\Attendance;
use App\Models\Overtime;

class DashboardController extends Controller
{
    public function index()
    {
        // Get real data from database filtered by current company
        $totalEmployees = Employee::active()->currentCompany()->count();
        $totalPayroll = Payroll::currentCompany()->where('status', 'paid')->sum('total_salary');
        $todayAttendance = Attendance::today()->currentCompany()->where('status', 'present')->count();
        $onLeave = Attendance::today()->currentCompany()->where('status', 'leave')->count();
        $lateToday = Attendance::today()->currentCompany()->where('status', 'late')->count();
        $overtimeToday = Overtime::currentCompany()->whereDate('date', today())->count();
        
        // Get recent employees
        $recentEmployees = Employee::currentCompany()->latest()->take(5)->get();
        
        // Get recent payrolls
        $recentPayrolls = Payroll::with('employee')->currentCompany()->latest()->take(5)->get();
        
        $data = [
            'totalEmployees' => $totalEmployees,
            'totalPayroll' => $totalPayroll,
            'todayAttendance' => $todayAttendance,
            'onLeave' => $onLeave,
            'lateToday' => $lateToday,
            'overtimeToday' => $overtimeToday,
            'recentEmployees' => $recentEmployees,
            'recentPayroll' => $recentPayrolls
        ];

        return view('dashboard', $data);
    }
}
