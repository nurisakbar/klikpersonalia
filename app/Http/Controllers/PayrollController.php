<?php

namespace App\Http\Controllers;

use App\DataTables\PayrollDataTable;
use App\Models\Payroll;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\Overtime;
use App\Models\Company;
use App\Services\PayrollService;
use App\Http\Requests\PayrollRequest;
use App\Http\Resources\PayrollResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class PayrollController extends Controller
{
    public function __construct(private PayrollService $payrollService) {}
    /**
     * Display a listing of payrolls.
     */
    public function index(PayrollDataTable $dataTable, Request $request)
    {
        // Get filter parameters
        $status = $request->get('status', '');
        $period = $request->get('period', date('Y-m'));
        
        // Get summary statistics
        $summary = $this->payrollService->getPayrollSummary($period, $status);
        
        return $dataTable->render('payrolls.index', compact('status', 'period', 'summary'));
    }

    /**
     * Get payrolls data for DataTables.
     */
    public function data(Request $request): JsonResponse
    {
        $payrolls = $this->payrollService->getPayrollsForDataTables($request);

        return DataTables::of($payrolls)
            ->addColumn('action', function ($payroll) {
                $buttons = '<div class="btn-group" role="group">';
                $buttons .= '<button type="button" class="btn btn-sm btn-info view-btn" data-id="' . $payroll->id . '" title="Detail"><i class="fas fa-eye"></i></button>';
                
                if ($payroll->status === 'draft') {
                    $buttons .= '<button type="button" class="btn btn-sm btn-warning edit-btn" data-id="' . $payroll->id . '" title="Edit"><i class="fas fa-edit"></i></button>';
                    $buttons .= '<button type="button" class="btn btn-sm btn-success approve-btn" data-id="' . $payroll->id . '" data-name="' . htmlspecialchars($payroll->employee->name) . '" title="Approve"><i class="fas fa-check"></i></button>';
                    $buttons .= '<button type="button" class="btn btn-sm btn-danger reject-btn" data-id="' . $payroll->id . '" data-name="' . htmlspecialchars($payroll->employee->name) . '" title="Reject"><i class="fas fa-times"></i></button>';
                    $buttons .= '<button type="button" class="btn btn-sm btn-danger delete-btn" data-id="' . $payroll->id . '" data-name="' . htmlspecialchars($payroll->employee->name . ' - ' . $payroll->period) . '" title="Delete"><i class="fas fa-trash"></i></button>';
                }
                
                $buttons .= '</div>';
                return $buttons;
            })
            ->addColumn('employee_name', function ($payroll) {
                return $payroll->employee->name;
            })
            ->addColumn('employee_department', function ($payroll) {
                return $payroll->employee->department;
            })
            ->addColumn('period_formatted', function ($payroll) {
                // Convert period format to readable format
                if (preg_match('/^(\d{4})-(\d{2})$/', $payroll->period, $matches)) {
                    $year = $matches[1];
                    $month = $matches[2];
                    $monthName = date('F', mktime(0, 0, 0, $month, 1));
                    return $monthName . ' ' . $year;
                }
                return $payroll->period;
            })
            ->addColumn('status_badge', function ($payroll) {
                $statusClass = [
                    'draft' => 'badge badge-warning',
                    'approved' => 'badge badge-success',
                    'paid' => 'badge badge-info',
                    'rejected' => 'badge badge-danger'
                ];
                
                $statusText = [
                    'draft' => 'Draft',
                    'approved' => 'Disetujui',
                    'paid' => 'Dibayar',
                    'rejected' => 'Ditolak'
                ];
                
                return '<span class="' . $statusClass[$payroll->status] . '">' . $statusText[$payroll->status] . '</span>';
            })
            ->addColumn('salary_formatted', function ($payroll) {
                return 'Rp ' . number_format($payroll->basic_salary, 0, ',', '.');
            })
            ->addColumn('overtime_formatted', function ($payroll) {
                return 'Rp ' . number_format($payroll->overtime ?? 0, 0, ',', '.');
            })
            ->addColumn('bonus_formatted', function ($payroll) {
                return 'Rp ' . number_format($payroll->bonus ?? 0, 0, ',', '.');
            })
            ->addColumn('deductions_formatted', function ($payroll) {
                $totalDeductions = ($payroll->deduction ?? 0) + ($payroll->tax_amount ?? 0) + ($payroll->bpjs_amount ?? 0);
                return 'Rp ' . number_format($totalDeductions, 0, ',', '.');
            })
            ->addColumn('total_formatted', function ($payroll) {
                return 'Rp ' . number_format($payroll->total_salary, 0, ',', '.');
            })
            ->addColumn('generated_info', function ($payroll) {
                $generatedAt = $payroll->generated_at ? $payroll->generated_at->format('d/m/Y H:i') : '-';
                $generatedBy = $payroll->generatedBy->name ?? '-';
                return $generatedAt . '<br><small class="text-muted">by ' . $generatedBy . '</small>';
            })
            ->filterColumn('employee_name', function($query, $keyword) {
                $query->whereHas('employee', function($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%")
                      ->orWhere('employee_id', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('employee_department', function($query, $keyword) {
                $query->whereHas('employee', function($q) use ($keyword) {
                    $q->where('department', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('period_formatted', function($query, $keyword) {
                // Search in both formatted and original period
                $query->where('period', 'like', "%{$keyword}%");
            })
            ->rawColumns(['action', 'status_badge', 'salary_formatted', 'overtime_formatted', 'bonus_formatted', 'deductions_formatted', 'total_formatted', 'generated_info'])
            ->make(true);
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

        if ($request->expectsJson()) {
            return response()->json($result);
        }

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

        // Get employees for dropdown
        $employees = $this->payrollService->getActiveEmployees();
        
        // Status options
        $statuses = [
            'draft' => 'Draft',
            'approved' => 'Approved',
            'paid' => 'Paid',
            'rejected' => 'Rejected'
        ];

        return view('payrolls.edit', compact('payroll', 'employees', 'statuses'));
    }

    /**
     * Update the specified payroll in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = Auth::user();
        
        // Check if user has permission
        if (!in_array($user->role, ['admin', 'hr'])) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'You do not have permission to edit payrolls.'], 403);
            }
            return redirect()->back()->with('error', 'You do not have permission to edit payrolls.');
        }

        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'status' => 'required|in:draft,approved,paid,rejected',
            'basic_salary' => 'required|numeric|min:0',
            'allowance' => 'nullable|numeric|min:0',
            'deduction' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $result = $this->payrollService->updatePayroll($id, $request->all());

        if ($request->expectsJson()) {
            return response()->json($result);
        }

        if ($result['success']) {
            return redirect()->route('payrolls.index')->with('success', $result['message']);
        }
        return redirect()->back()->withInput()->with('error', $result['message']);
    }

    /**
     * Remove the specified payroll from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $result = $this->payrollService->deletePayroll($id);
        
        $statusCode = $result['success'] ? 200 : 422;
        return response()->json($result, $statusCode);
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
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Anda tidak memiliki izin untuk generate payroll.']);
            }
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk generate payroll.');
        }

        $request->validate([
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2020',
        ]);

        $month = $request->month;
        $year = $request->year;
        $period = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT);

        // Get all active employees
        $employees = Employee::where('company_id', $user->company_id)
            ->where('status', 'active')
            ->get();

        $generatedCount = 0;
        $errors = [];

        foreach ($employees as $employee) {
            // Check if payroll already exists
            $existingPayroll = Payroll::where('employee_id', $employee->id)
                ->where('period', $period)
                ->where('company_id', $user->company_id)
                ->first();

            if ($existingPayroll) {
                $errors[] = "Payroll sudah ada untuk {$employee->name}";
                continue;
            }

            // Calculate payroll
            $payrollData = $this->calculatePayroll($employee, $month, $year);

            // Create payroll
            Payroll::create([
                'employee_id' => $employee->id,
                'company_id' => $user->company_id,
                'period' => $period,
                'basic_salary' => $employee->basic_salary,
                'allowances' => 0,
                'deductions' => 0,
                'overtime_pay' => $payrollData['overtime_pay'],
                'leave_deduction' => $payrollData['leave_deduction'],
                'attendance_bonus' => $payrollData['attendance_bonus'],
                'total_salary' => $payrollData['total_salary'],
                'status' => 'draft',
                'generated_by' => $user->id,
                'generated_at' => now(),
            ]);

            $generatedCount++;
        }

        $message = "Berhasil generate {$generatedCount} payroll.";
        if (!empty($errors)) {
            $message .= " Error: " . implode(', ', $errors);
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
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
