<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Payroll;
use App\Models\Leave;
use App\Models\Overtime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class MobileController extends Controller
{
    /**
     * Mobile dashboard data
     */
    public function dashboard()
    {
        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not found'
            ], 404);
        }

        // Get today's attendance
        $todayAttendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('date', today())
            ->first();

        // Get current month attendance summary
        $monthlyAttendance = Attendance::where('employee_id', $employee->id)
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->get();

        // Get recent payslips
        $recentPayslips = Payroll::where('employee_id', $employee->id)
            ->orderBy('payroll_period', 'desc')
            ->limit(3)
            ->get();

        // Get leave balance
        $leaveBalance = $this->calculateLeaveBalance($employee);

        // Get pending requests
        $pendingLeaves = Leave::where('employee_id', $employee->id)
            ->whereIn('status', ['pending', 'approved'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $pendingOvertimes = Overtime::where('employee_id', $employee->id)
            ->whereIn('status', ['pending', 'approved'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'employee' => [
                    'id' => $employee->id,
                    'name' => $employee->first_name . ' ' . $employee->last_name,
                    'position' => $employee->position,
                    'department' => $employee->department,
                    'avatar' => $employee->avatar_url ?? null
                ],
                'today_attendance' => $todayAttendance ? [
                    'check_in' => $todayAttendance->check_in,
                    'check_out' => $todayAttendance->check_out,
                    'status' => $todayAttendance->status,
                    'total_hours' => $todayAttendance->total_hours
                ] : null,
                'monthly_summary' => [
                    'total_days' => $monthlyAttendance->count(),
                    'present_days' => $monthlyAttendance->where('status', 'present')->count(),
                    'absent_days' => $monthlyAttendance->where('status', 'absent')->count(),
                    'late_days' => $monthlyAttendance->where('status', 'late')->count(),
                    'total_hours' => $monthlyAttendance->sum('total_hours')
                ],
                'leave_balance' => $leaveBalance,
                'recent_payslips' => $recentPayslips->map(function($payslip) {
                    return [
                        'id' => $payslip->id,
                        'period' => $payslip->payroll_period,
                        'gross_salary' => $payslip->gross_salary,
                        'net_salary' => $payslip->net_salary,
                        'status' => $payslip->status
                    ];
                }),
                'pending_requests' => [
                    'leaves' => $pendingLeaves->map(function($leave) {
                        return [
                            'id' => $leave->id,
                            'type' => $leave->leave_type,
                            'start_date' => $leave->start_date,
                            'end_date' => $leave->end_date,
                            'status' => $leave->status
                        ];
                    }),
                    'overtimes' => $pendingOvertimes->map(function($overtime) {
                        return [
                            'id' => $overtime->id,
                            'date' => $overtime->date,
                            'hours' => $overtime->hours,
                            'status' => $overtime->status
                        ];
                    })
                ]
            ]
        ]);
    }

    /**
     * Mobile check-in/check-out
     */
    public function checkInOut(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:check_in,check_out',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'location' => 'nullable|string',
            'device_info' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not found'
            ], 404);
        }

        $today = today();
        $now = now();

        // Get or create today's attendance record
        $attendance = Attendance::firstOrCreate([
            'employee_id' => $employee->id,
            'company_id' => $employee->company_id,
            'date' => $today
        ], [
            'status' => 'present',
            'check_in' => null,
            'check_out' => null,
            'total_hours' => 0
        ]);

        if ($request->action === 'check_in') {
            if ($attendance->check_in) {
                return response()->json([
                    'success' => false,
                    'message' => 'Already checked in today'
                ], 400);
            }

            $attendance->update([
                'check_in' => $now,
                'latitude_in' => $request->latitude,
                'longitude_in' => $request->longitude,
                'location_in' => $request->location,
                'device_info_in' => $request->device_info
            ]);

            $message = 'Check-in successful';
        } else {
            if (!$attendance->check_in) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please check in first'
                ], 400);
            }

            if ($attendance->check_out) {
                return response()->json([
                    'success' => false,
                    'message' => 'Already checked out today'
                ], 400);
            }

            $attendance->update([
                'check_out' => $now,
                'latitude_out' => $request->latitude,
                'longitude_out' => $request->longitude,
                'location_out' => $request->location,
                'device_info_out' => $request->device_info,
                'total_hours' => $attendance->check_in->diffInHours($now, true)
            ]);

            $message = 'Check-out successful';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'check_in' => $attendance->check_in,
                'check_out' => $attendance->check_out,
                'total_hours' => $attendance->total_hours
            ]
        ]);
    }

    /**
     * Get payslip details
     */
    public function getPayslip($id)
    {
        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not found'
            ], 404);
        }

        $payslip = Payroll::where('id', $id)
            ->where('employee_id', $employee->id)
            ->first();

        if (!$payslip) {
            return response()->json([
                'success' => false,
                'message' => 'Payslip not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $payslip->id,
                'period' => $payslip->payroll_period,
                'basic_salary' => $payslip->basic_salary,
                'allowances' => $payslip->allowances,
                'deductions' => $payslip->deductions,
                'overtime_pay' => $payslip->overtime_pay,
                'gross_salary' => $payslip->gross_salary,
                'net_salary' => $payslip->net_salary,
                'status' => $payslip->status,
                'payment_date' => $payslip->payment_date,
                'created_at' => $payslip->created_at
            ]
        ]);
    }

    /**
     * Get attendance history
     */
    public function attendanceHistory(Request $request)
    {
        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not found'
            ], 404);
        }

        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        $attendance = Attendance::where('employee_id', $employee->id)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->orderBy('date', 'desc')
            ->get()
            ->map(function($record) {
                return [
                    'date' => $record->date,
                    'check_in' => $record->check_in,
                    'check_out' => $record->check_out,
                    'total_hours' => $record->total_hours,
                    'status' => $record->status,
                    'location_in' => $record->location_in,
                    'location_out' => $record->location_out
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $attendance
        ]);
    }

    /**
     * Submit leave request
     */
    public function submitLeaveRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'leave_type' => 'required|in:annual,sick,personal,other',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:500',
            'attachment' => 'nullable|file|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not found'
            ], 404);
        }

        $leave = Leave::create([
            'company_id' => $employee->company_id,
            'employee_id' => $employee->id,
            'leave_type' => $request->leave_type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'days' => Carbon::parse($request->start_date)->diffInDays($request->end_date) + 1,
            'reason' => $request->reason,
            'status' => 'pending',
            'submitted_by' => $user->id
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Leave request submitted successfully',
            'data' => [
                'id' => $leave->id,
                'leave_type' => $leave->leave_type,
                'start_date' => $leave->start_date,
                'end_date' => $leave->end_date,
                'status' => $leave->status
            ]
        ]);
    }

    /**
     * Submit overtime request
     */
    public function submitOvertimeRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date|after_or_equal:today',
            'hours' => 'required|numeric|min:0.5|max:24',
            'reason' => 'required|string|max:500',
            'overtime_type' => 'required|in:regular,holiday,weekend,emergency'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not found'
            ], 404);
        }

        $overtime = Overtime::create([
            'company_id' => $employee->company_id,
            'employee_id' => $employee->id,
            'date' => $request->date,
            'hours' => $request->hours,
            'reason' => $request->reason,
            'overtime_type' => $request->overtime_type,
            'status' => 'pending',
            'submitted_by' => $user->id
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Overtime request submitted successfully',
            'data' => [
                'id' => $overtime->id,
                'date' => $overtime->date,
                'hours' => $overtime->hours,
                'status' => $overtime->status
            ]
        ]);
    }

    /**
     * Calculate leave balance
     */
    private function calculateLeaveBalance($employee)
    {
        $currentYear = now()->year;
        
        // Get leave policy from company settings
        $company = $employee->company;
        $leavePolicy = $company->leave_policy ?? [];
        
        $annualLeave = $leavePolicy['annual_leave'] ?? 12;
        $sickLeave = $leavePolicy['sick_leave'] ?? 12;
        $personalLeave = $leavePolicy['personal_leave'] ?? 6;

        // Get used leaves
        $usedAnnual = Leave::where('employee_id', $employee->id)
            ->where('leave_type', 'annual')
            ->whereYear('start_date', $currentYear)
            ->where('status', 'approved')
            ->sum('days');

        $usedSick = Leave::where('employee_id', $employee->id)
            ->where('leave_type', 'sick')
            ->whereYear('start_date', $currentYear)
            ->where('status', 'approved')
            ->sum('days');

        $usedPersonal = Leave::where('employee_id', $employee->id)
            ->where('leave_type', 'personal')
            ->whereYear('start_date', $currentYear)
            ->where('status', 'approved')
            ->sum('days');

        return [
            'annual' => [
                'total' => $annualLeave,
                'used' => $usedAnnual,
                'remaining' => max(0, $annualLeave - $usedAnnual)
            ],
            'sick' => [
                'total' => $sickLeave,
                'used' => $usedSick,
                'remaining' => max(0, $sickLeave - $usedSick)
            ],
            'personal' => [
                'total' => $personalLeave,
                'used' => $usedPersonal,
                'remaining' => max(0, $personalLeave - $usedPersonal)
            ]
        ];
    }
} 