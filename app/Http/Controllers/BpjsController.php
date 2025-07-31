<?php

namespace App\Http\Controllers;

use App\Models\Bpjs;
use App\Models\Employee;
use App\Models\Payroll;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BpjsController extends Controller
{
    /**
     * Display a listing of BPJS records
     */
    public function index(Request $request)
    {
        $companyId = Auth::user()->company_id;
        
        $query = Bpjs::with(['employee', 'payroll'])
            ->forCompany($companyId);

        // Filter by period
        if ($request->filled('period')) {
            $query->forPeriod($request->period);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->forType($request->type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by employee
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        $bpjsRecords = $query->orderBy('bpjs_period', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $employees = Employee::forCompany($companyId)->get();
        $periods = Bpjs::forCompany($companyId)
            ->distinct()
            ->pluck('bpjs_period')
            ->sort()
            ->reverse();

        return view('bpjs.index', compact('bpjsRecords', 'employees', 'periods'));
    }

    /**
     * Show the form for creating a new BPJS record
     */
    public function create()
    {
        $companyId = Auth::user()->company_id;
        $employees = Employee::forCompany($companyId)
            ->where('bpjs_kesehatan_active', true)
            ->orWhere('bpjs_ketenagakerjaan_active', true)
            ->get();

        return view('bpjs.create', compact('employees'));
    }

    /**
     * Store a newly created BPJS record
     */
    public function store(Request $request)
    {
        $companyId = Auth::user()->company_id;

        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'bpjs_period' => 'required|date_format:Y-m',
            'bpjs_type' => ['required', Rule::in(['kesehatan', 'ketenagakerjaan'])],
            'base_salary' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        // Check if BPJS record already exists for this employee, period, and type
        $existingBpjs = Bpjs::forCompany($companyId)
            ->where('employee_id', $request->employee_id)
            ->where('bpjs_period', $request->bpjs_period)
            ->where('bpjs_type', $request->bpjs_type)
            ->first();

        if ($existingBpjs) {
            return back()->withErrors(['bpjs_period' => 'BPJS record already exists for this employee, period, and type.']);
        }

        $employee = Employee::findOrFail($request->employee_id);

        // Calculate BPJS contribution
        if ($request->bpjs_type === 'kesehatan') {
            $calculation = Bpjs::calculateKesehatan($employee, $request->base_salary, $request->bpjs_period);
        } else {
            $calculation = Bpjs::calculateKetenagakerjaan($employee, $request->base_salary, $request->bpjs_period);
        }

        $bpjs = Bpjs::create([
            'company_id' => $companyId,
            'employee_id' => $request->employee_id,
            'bpjs_period' => $request->bpjs_period,
            'bpjs_type' => $request->bpjs_type,
            'employee_contribution' => $calculation['employee_contribution'],
            'company_contribution' => $calculation['company_contribution'],
            'total_contribution' => $calculation['total_contribution'],
            'base_salary' => $calculation['base_salary'],
            'contribution_rate_employee' => $calculation['contribution_rate_employee'],
            'contribution_rate_company' => $calculation['contribution_rate_company'],
            'status' => Bpjs::STATUS_CALCULATED,
            'notes' => $request->notes,
        ]);

        return redirect()->route('bpjs.show', $bpjs)
            ->with('success', 'BPJS record created successfully.');
    }

    /**
     * Display the specified BPJS record
     */
    public function show(Bpjs $bpjs)
    {
        $this->authorize('view', $bpjs);
        
        $bpjs->load(['employee', 'payroll']);
        
        return view('bpjs.show', compact('bpjs'));
    }

    /**
     * Show the form for editing the specified BPJS record
     */
    public function edit(Bpjs $bpjs)
    {
        $this->authorize('update', $bpjs);
        
        $companyId = Auth::user()->company_id;
        $employees = Employee::forCompany($companyId)->get();
        
        return view('bpjs.edit', compact('bpjs', 'employees'));
    }

    /**
     * Update the specified BPJS record
     */
    public function update(Request $request, Bpjs $bpjs)
    {
        $this->authorize('update', $bpjs);

        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'bpjs_period' => 'required|date_format:Y-m',
            'bpjs_type' => ['required', Rule::in(['kesehatan', 'ketenagakerjaan'])],
            'base_salary' => 'required|numeric|min:0',
            'status' => ['required', Rule::in(['pending', 'calculated', 'paid', 'verified'])],
            'payment_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $employee = Employee::findOrFail($request->employee_id);

        // Calculate BPJS contribution
        if ($request->bpjs_type === 'kesehatan') {
            $calculation = Bpjs::calculateKesehatan($employee, $request->base_salary, $request->bpjs_period);
        } else {
            $calculation = Bpjs::calculateKetenagakerjaan($employee, $request->base_salary, $request->bpjs_period);
        }

        $bpjs->update([
            'employee_id' => $request->employee_id,
            'bpjs_period' => $request->bpjs_period,
            'bpjs_type' => $request->bpjs_type,
            'employee_contribution' => $calculation['employee_contribution'],
            'company_contribution' => $calculation['company_contribution'],
            'total_contribution' => $calculation['total_contribution'],
            'base_salary' => $calculation['base_salary'],
            'contribution_rate_employee' => $calculation['contribution_rate_employee'],
            'contribution_rate_company' => $calculation['contribution_rate_company'],
            'status' => $request->status,
            'payment_date' => $request->payment_date,
            'notes' => $request->notes,
        ]);

        return redirect()->route('bpjs.show', $bpjs)
            ->with('success', 'BPJS record updated successfully.');
    }

    /**
     * Remove the specified BPJS record
     */
    public function destroy(Bpjs $bpjs)
    {
        $this->authorize('delete', $bpjs);
        
        $bpjs->delete();
        
        return redirect()->route('bpjs.index')
            ->with('success', 'BPJS record deleted successfully.');
    }

    /**
     * Calculate BPJS for all employees in a payroll period
     */
    public function calculateForPayroll(Request $request)
    {
        $companyId = Auth::user()->company_id;

        $request->validate([
            'payroll_period' => 'required|date_format:Y-m',
            'bpjs_type' => ['required', Rule::in(['kesehatan', 'ketenagakerjaan', 'both'])],
        ]);

        $employees = Employee::forCompany($companyId)->get();
        $payrolls = Payroll::forCompany($companyId)
            ->forPeriod($request->payroll_period)
            ->get();

        $createdCount = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($employees as $employee) {
                // Check if employee is active for BPJS
                if ($request->bpjs_type === 'kesehatan' && !$employee->bpjs_kesehatan_active) {
                    continue;
                }
                if ($request->bpjs_type === 'ketenagakerjaan' && !$employee->bpjs_ketenagakerjaan_active) {
                    continue;
                }

                // Find payroll for this employee
                $payroll = $payrolls->where('employee_id', $employee->id)->first();
                if (!$payroll) {
                    $errors[] = "No payroll found for employee: {$employee->name}";
                    continue;
                }

                $baseSalary = $payroll->basic_salary;

                // Calculate and create BPJS records
                if ($request->bpjs_type === 'kesehatan' || $request->bpjs_type === 'both') {
                    if ($employee->bpjs_kesehatan_active) {
                        $this->createBpjsRecord($companyId, $employee, $payroll, $baseSalary, 'kesehatan', $request->payroll_period);
                        $createdCount++;
                    }
                }

                if ($request->bpjs_type === 'ketenagakerjaan' || $request->bpjs_type === 'both') {
                    if ($employee->bpjs_ketenagakerjaan_active) {
                        $this->createBpjsRecord($companyId, $employee, $payroll, $baseSalary, 'ketenagakerjaan', $request->payroll_period);
                        $createdCount++;
                    }
                }
            }

            DB::commit();

            $message = "Successfully created {$createdCount} BPJS records.";
            if (!empty($errors)) {
                $message .= " Errors: " . implode(', ', $errors);
            }

            return redirect()->route('bpjs.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to create BPJS records: ' . $e->getMessage()]);
        }
    }

    /**
     * Create BPJS record helper method
     */
    private function createBpjsRecord($companyId, $employee, $payroll, $baseSalary, $type, $period)
    {
        // Check if record already exists
        $existing = Bpjs::forCompany($companyId)
            ->where('employee_id', $employee->id)
            ->where('bpjs_period', $period)
            ->where('bpjs_type', $type)
            ->first();

        if ($existing) {
            return;
        }

        // Calculate BPJS contribution
        if ($type === 'kesehatan') {
            $calculation = Bpjs::calculateKesehatan($employee, $baseSalary, $period);
        } else {
            $calculation = Bpjs::calculateKetenagakerjaan($employee, $baseSalary, $period);
        }

        Bpjs::create([
            'company_id' => $companyId,
            'employee_id' => $employee->id,
            'payroll_id' => $payroll->id,
            'bpjs_period' => $period,
            'bpjs_type' => $type,
            'employee_contribution' => $calculation['employee_contribution'],
            'company_contribution' => $calculation['company_contribution'],
            'total_contribution' => $calculation['total_contribution'],
            'base_salary' => $calculation['base_salary'],
            'contribution_rate_employee' => $calculation['contribution_rate_employee'],
            'contribution_rate_company' => $calculation['contribution_rate_company'],
            'status' => Bpjs::STATUS_CALCULATED,
        ]);
    }

    /**
     * Display BPJS reports
     */
    public function report(Request $request)
    {
        $companyId = Auth::user()->company_id;

        $period = $request->get('period', date('Y-m'));
        $type = $request->get('type', 'both');

        $query = Bpjs::with(['employee'])
            ->forCompany($companyId)
            ->forPeriod($period);

        if ($type !== 'both') {
            $query->forType($type);
        }

        $bpjsRecords = $query->get();

        // Calculate summary
        $summary = [
            'total_employee_contribution' => $bpjsRecords->sum('employee_contribution'),
            'total_company_contribution' => $bpjsRecords->sum('company_contribution'),
            'total_contribution' => $bpjsRecords->sum('total_contribution'),
            'kesehatan_count' => $bpjsRecords->where('bpjs_type', 'kesehatan')->count(),
            'ketenagakerjaan_count' => $bpjsRecords->where('bpjs_type', 'ketenagakerjaan')->count(),
        ];

        $periods = Bpjs::forCompany($companyId)
            ->distinct()
            ->pluck('bpjs_period')
            ->sort()
            ->reverse();

        return view('bpjs.report', compact('bpjsRecords', 'summary', 'periods', 'period', 'type'));
    }

    /**
     * Export BPJS data
     */
    public function export(Request $request)
    {
        $companyId = Auth::user()->company_id;

        $period = $request->get('period', date('Y-m'));
        $type = $request->get('type', 'both');

        $query = Bpjs::with(['employee'])
            ->forCompany($companyId)
            ->forPeriod($period);

        if ($type !== 'both') {
            $query->forType($type);
        }

        $bpjsRecords = $query->get();

        // Generate CSV
        $filename = "bpjs_report_{$period}_{$type}.csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($bpjsRecords) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'Employee ID',
                'Employee Name',
                'BPJS Type',
                'Period',
                'Base Salary',
                'Employee Contribution',
                'Company Contribution',
                'Total Contribution',
                'Status',
                'Payment Date',
                'Notes'
            ]);

            // CSV data
            foreach ($bpjsRecords as $record) {
                fputcsv($file, [
                    $record->employee->employee_id,
                    $record->employee->name,
                    $record->bpjs_type,
                    $record->bpjs_period,
                    $record->base_salary,
                    $record->employee_contribution,
                    $record->company_contribution,
                    $record->total_contribution,
                    $record->status,
                    $record->payment_date,
                    $record->notes
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
} 