<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\Attendance;

class DashboardController extends Controller
{
    public function index()
    {
        // Get real data from database
        $totalEmployees = Employee::active()->count();
        $totalPayroll = Payroll::where('status', 'paid')->sum('total_salary');
        $todayAttendance = Attendance::today()->where('status', 'present')->count();
        $onLeave = Attendance::today()->where('status', 'leave')->count();
        $lateToday = Attendance::today()->where('status', 'late')->count();
        
        // Get recent employees
        $recentEmployees = Employee::latest()->take(5)->get();
        
        // Get recent payrolls
        $recentPayrolls = Payroll::with('employee')->latest()->take(5)->get();
        
        $data = [
            'totalEmployees' => $totalEmployees,
            'totalPayroll' => $totalPayroll,
            'todayAttendance' => $todayAttendance,
            'onLeave' => $onLeave,
            'lateToday' => $lateToday,
            'recentEmployees' => $recentEmployees,
            'recentPayroll' => $recentPayrolls
        ];

        return view('dashboard', $data);
    }
}
