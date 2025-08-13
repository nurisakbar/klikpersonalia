<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\Overtime;
use App\Models\Company;
use App\Services\PayrollService;
use Illuminate\Http\Request;
use App\Http\Requests\PayrollRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PayrollController extends Controller
{
    public function __construct(private PayrollService $payrollService) {}
    /**
     * Display a listing of payrolls.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Check if user has permission
        if (!in_array($user->role, ['admin', 'hr'])) {
            return redirect()->back()->with('error', 'You do not have permission to view payrolls.');
        }

        $period = $request->get('period', Carbon::now()->format('Y-m'));
        $status = $request->get('status', '');
        
        // Use service to get payrolls
        $payrolls = $this->payrollService->getPaginatedPayrolls($period, $status, 15);
        
        // Get summary statistics
        $summary = $this->payrollService->getPayrollSummary($period);
        
        return view('payrolls.index', compact('payrolls', 'summary', 'period', 'status'));
    }

    /**
     * Show the form for creating a new payroll.
     */
    public function create()
    {
        $user = Auth::user();
        
        // Check if user has permission
        if (!in_array($user->role, ['admin', 'hr'])) {
            return redirect()->back()->with('error', 'You do not have permission to create payrolls.');
        }

        // Use service to get employees
        $employees = $this->payrollService->getActiveEmployees();
        $currentPeriod = Carbon::now()->format('Y-m');
        
        return view('payrolls.create', compact('employees', 'currentPeriod'));
    }

    /**
     * Store a newly created payroll in storage.
     */
    public function store(PayrollRequest $request)
    {
        $result = $this->payrollService->generateSingle($request->validated());

        if ($result['success']) {
            return redirect()->route('payrolls.index')->with('success', $result['message']);
        }
        return redirect()->back()->withInput()->with('error', $result['message']);
    }

    /**
     * Display the specified payroll.
     */
    public function show(string $id)
    {
        $user = Auth::user();
        
        // Check if user has permission
        if (!in_array($user->role, ['admin', 'hr'])) {
            return redirect()->back()->with('error', 'You do not have permission to view payrolls.');
        }

        // Use service to get payroll
        $payroll = $this->payrollService->findPayroll($id);
        
        if (!$payroll) {
            return redirect()->route('payrolls.index')->with('error', 'Payroll not found.');
        }

        return view('payrolls.show', compact('payroll'));
    }

    /**
     * Show the form for editing the specified payroll.
     */
    public function edit(string $id)
    {
        $user = Auth::user();
        
        // Check if user has permission
        if (!in_array($user->role, ['admin', 'hr'])) {
            return redirect()->back()->with('error', 'You do not have permission to edit payrolls.');
        }

        // Use service to get payroll
        $payroll = $this->payrollService->findPayroll($id);
        
        if (!$payroll) {
            return redirect()->route('payrolls.index')->with('error', 'Payroll not found.');
        }

        return view('payrolls.edit', compact('payroll'));
    }

    /**
     * Update the specified payroll in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = Auth::user();
        
        // Check if user has permission
        if (!in_array($user->role, ['admin', 'hr'])) {
            return redirect()->back()->with('error', 'You do not have permission to edit payrolls.');
        }

        $request->validate([
            'basic_salary' => 'required|numeric|min:0',
            'allowance' => 'nullable|numeric|min:0',
            'overtime' => 'nullable|numeric|min:0',
            'bonus' => 'nullable|numeric|min:0',
            'deduction' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'bpjs_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $result = $this->payrollService->updatePayroll($id, $request->all());

        if ($result['success']) {
            return redirect()->route('payrolls.index')->with('success', $result['message']);
        }
        return redirect()->back()->withInput()->with('error', $result['message']);
    }

    /**
     * Remove the specified payroll from storage.
     */
    public function destroy(string $id)
    {
        $user = Auth::user();
        
        // Check if user has permission
        if (!in_array($user->role, ['admin', 'hr'])) {
            return redirect()->back()->with('error', 'You do not have permission to delete payrolls.');
        }

        $result = $this->payrollService->deletePayroll($id);

        if ($request->expectsJson()) {
            return response()->json($result);
        }

        if ($result['success']) {
            return redirect()->route('payrolls.index')->with('success', $result['message']);
        }
        return redirect()->back()->with('error', $result['message']);
    }

    /**
     * Approve payroll.
     */
    public function approve(string $id)
    {
        $user = Auth::user();
        
        // Check if user has permission
        if (!in_array($user->role, ['admin', 'hr'])) {
            return response()->json(['success' => false, 'message' => 'You do not have permission to approve payrolls.'], 403);
        }

        $result = $this->payrollService->updatePayroll($id, [
            'status' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now()
        ]);

        return response()->json($result);
    }

    /**
     * Reject payroll.
     */
    public function reject(string $id)
    {
        $user = Auth::user();
        
        // Check if user has permission
        if (!in_array($user->role, ['admin', 'hr'])) {
            return response()->json(['success' => false, 'message' => 'You do not have permission to reject payrolls.'], 403);
        }

        $result = $this->payrollService->updatePayroll($id, [
            'status' => 'rejected',
            'rejected_by' => $user->id,
            'rejected_at' => now()
        ]);

        return response()->json($result);
    }

    /**
     * Mark payroll as paid.
     */
    public function markPaid(string $id)
    {
        $user = Auth::user();
        
        // Check if user has permission
        if (!in_array($user->role, ['admin', 'hr'])) {
            return response()->json(['success' => false, 'message' => 'You do not have permission to mark payrolls as paid.'], 403);
        }

        $result = $this->payrollService->updatePayroll($id, [
            'status' => 'paid',
            'paid_by' => $user->id,
            'paid_at' => now()
        ]);

        return response()->json($result);
    }

    /**
     * Generate payroll for all employees.
     */
    public function generateAll(Request $request)
    {
        $user = Auth::user();
        
        // Check if user has permission
        if (!in_array($user->role, ['admin', 'hr'])) {
            return redirect()->back()->with('error', 'You do not have permission to generate payrolls.');
        }

        $request->validate([
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2020',
        ]);

        $month = $request->month;
        $year = $request->year;

        // Get all active employees
        $employees = Employee::where('company_id', $user->company_id)
            ->where('status', 'active')
            ->get();

        $generatedCount = 0;
        $errors = [];

        foreach ($employees as $employee) {
            // Check if payroll already exists
            $existingPayroll = Payroll::where('employee_id', $employee->id)
                ->where('month', $month)
                ->where('year', $year)
                ->where('company_id', $user->company_id)
                ->first();

            if ($existingPayroll) {
                $errors[] = "Payroll already exists for {$employee->name}";
                continue;
            }

            // Calculate payroll
            $payrollData = $this->calculatePayroll($employee, $month, $year);

            // Create payroll
            Payroll::create([
                'employee_id' => $employee->id,
                'company_id' => $user->company_id,
                'month' => $month,
                'year' => $year,
                'basic_salary' => $employee->basic_salary,
                'allowances' => 0,
                'deductions' => 0,
                'overtime_pay' => $payrollData['overtime_pay'],
                'leave_deduction' => $payrollData['leave_deduction'],
                'attendance_bonus' => $payrollData['attendance_bonus'],
                'total_salary' => $payrollData['total_salary'],
                'status' => 'pending',
                'generated_by' => $user->id,
                'generated_at' => now(),
            ]);

            $generatedCount++;
        }

        $message = "Successfully generated {$generatedCount} payrolls.";
        if (!empty($errors)) {
            $message .= " Errors: " . implode(', ', $errors);
        }

        return redirect()->route('payrolls.index')
            ->with('success', $message);
    }

    /**
     * Export payroll data.
     */
    public function export(Request $request)
    {
        $user = Auth::user();
        
        // Check if user has permission
        if (!in_array($user->role, ['admin', 'hr'])) {
            return redirect()->back()->with('error', 'You do not have permission to export payrolls.');
        }

        $month = $request->get('month', Carbon::now()->month);
        $year = $request->get('year', Carbon::now()->year);
        $format = $request->get('format', 'pdf');

        $payrolls = Payroll::with(['employee'])
            ->where('company_id', $user->company_id)
            ->where('month', $month)
            ->where('year', $year)
            ->get();

        // Implementation for export functionality
        return response()->json([
            'message' => 'Export functionality will be implemented',
            'data' => $payrolls
        ]);
    }

    /**
     * Calculate payroll preview.
     */
    public function calculate(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Check if user has permission
            if (!in_array($user->role, ['admin', 'hr'])) {
                return response()->json(['success' => false, 'message' => 'Permission denied.'], 403);
            }

            $request->validate([
                'employee_id' => 'required|exists:employees,id',
                'month' => 'required|integer|between:1,12',
                'year' => 'required|integer|min:2020',
                'basic_salary' => 'required|numeric|min:0',
                'allowances' => 'nullable|numeric|min:0',
                'deductions' => 'nullable|numeric|min:0',
            ]);

            $employee = Employee::findOrFail($request->employee_id);
            
            // Check if employee belongs to user's company
            if ($employee->company_id !== $user->company_id) {
                return response()->json(['success' => false, 'message' => 'Employee not found.'], 404);
            }

            // Calculate payroll components
            $payrollData = $this->calculatePayroll($employee, $request->month, $request->year);
            
            // Add additional allowances and deductions
            $payrollData['allowances'] = $request->allowances ?? 0;
            $payrollData['deductions'] = $request->deductions ?? 0;
            $payrollData['total_salary'] += $payrollData['allowances'] - $payrollData['deductions'];
            
            // Format period
            $monthNames = [
                1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
            ];
            
            return response()->json([
                'success' => true,
                'data' => [
                    'employee_name' => $employee->name,
                    'period' => $monthNames[$request->month] . ' ' . $request->year,
                    'basic_salary' => $request->basic_salary,
                    'overtime_pay' => $payrollData['overtime_pay'],
                    'attendance_bonus' => $payrollData['attendance_bonus'],
                    'allowances' => $payrollData['allowances'],
                    'leave_deduction' => $payrollData['leave_deduction'],
                    'deductions' => $payrollData['deductions'],
                    'total_salary' => $payrollData['total_salary'],
                    'attendance_rate' => round($payrollData['attendance_rate'], 1),
                    'present_days' => $payrollData['present_days'],
                    'late_days' => $payrollData['late_days'],
                    'total_working_days' => $payrollData['total_working_days'],
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Payroll calculation error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false, 
                'message' => 'Error calculating payroll: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate payroll components.
     */
    private function calculatePayroll($employee, $month, $year)
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // Calculate overtime pay
        $overtimes = Overtime::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        $overtimePay = 0;
        foreach ($overtimes as $overtime) {
            $hourlyRate = $employee->basic_salary / 173; // 173 working hours per month
            $overtimeRate = $this->getOvertimeRate($overtime->overtime_type);
            $overtimePay += $overtime->total_hours * $hourlyRate * $overtimeRate;
        }

        // Calculate leave deduction
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

        $leaveDeduction = 0;
        foreach ($leaves as $leave) {
            if ($leave->leave_type !== 'annual') { // Only deduct for non-annual leaves
                $dailyRate = $employee->basic_salary / 22; // 22 working days per month
                $leaveDeduction += $leave->total_days * $dailyRate;
            }
        }

        // Calculate attendance bonus
        $attendances = Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        $presentDays = $attendances->where('status', 'present')->count();
        $lateDays = $attendances->where('status', 'late')->count();
        $totalWorkingDays = $this->countWorkingDays($startDate, $endDate);
        
        $attendanceRate = $totalWorkingDays > 0 ? (($presentDays + $lateDays) / $totalWorkingDays) * 100 : 0;
        
        $attendanceBonus = 0;
        if ($attendanceRate >= 95) {
            $attendanceBonus = $employee->basic_salary * 0.05; // 5% bonus for 95%+ attendance
        } elseif ($attendanceRate >= 90) {
            $attendanceBonus = $employee->basic_salary * 0.03; // 3% bonus for 90%+ attendance
        }

        // Calculate total salary
        $totalSalary = $employee->basic_salary + $overtimePay + $attendanceBonus - $leaveDeduction;

        return [
            'overtime_pay' => $overtimePay,
            'leave_deduction' => $leaveDeduction,
            'attendance_bonus' => $attendanceBonus,
            'total_salary' => $totalSalary,
            'attendance_rate' => $attendanceRate,
            'present_days' => $presentDays,
            'late_days' => $lateDays,
            'total_working_days' => $totalWorkingDays,
        ];
    }

    /**
     * Get overtime rate multiplier.
     */
    private function getOvertimeRate($overtimeType)
    {
        $rates = [
            'regular' => 1.5,
            'holiday' => 2.0,
            'weekend' => 2.0,
            'emergency' => 2.5,
        ];

        return $rates[$overtimeType] ?? 1.5;
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
     * Get payroll summary statistics.
     */
    private function getPayrollSummary($companyId, $period)
    {
        $payrolls = Payroll::where('company_id', $companyId)
            ->where('period', 'LIKE', $period . '%')
            ->get();

        return [
            'total_payrolls' => $payrolls->count(),
            'draft_payrolls' => $payrolls->where('status', 'draft')->count(),
            'approved_payrolls' => $payrolls->where('status', 'approved')->count(),
            'paid_payrolls' => $payrolls->where('status', 'paid')->count(),
            'total_salary' => $payrolls->sum('total_salary'),
            'total_overtime' => $payrolls->sum('overtime'),
            'total_bonus' => $payrolls->sum('bonus'),
            'total_deductions' => $payrolls->sum('deduction'),
        ];
    }
}
