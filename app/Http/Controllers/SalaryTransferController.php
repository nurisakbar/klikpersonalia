<?php

namespace App\Http\Controllers;

use App\Models\SalaryTransfer;
use App\Models\BankAccount;
use App\Models\Payroll;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SalaryTransferController extends Controller
{
    /**
     * Display a listing of salary transfers.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = SalaryTransfer::with(['employee', 'bankAccount', 'payroll'])
            ->where('company_id', $user->company_id);

        // Filter by employee
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->where('transfer_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('transfer_date', '<=', $request->end_date);
        }

        // Filter by transfer method
        if ($request->filled('transfer_method')) {
            $query->where('transfer_method', $request->transfer_method);
        }

        $salaryTransfers = $query->orderBy('transfer_date', 'desc')->paginate(15);
        $employees = Employee::where('company_id', $user->company_id)->get();

        return view('salary-transfers.index', compact('salaryTransfers', 'employees'));
    }

    /**
     * Show the form for creating a new salary transfer.
     */
    public function create()
    {
        $user = Auth::user();
        $employees = Employee::where('company_id', $user->company_id)->get();
        $transferMethods = SalaryTransfer::TRANSFER_METHODS;

        return view('salary-transfers.create', compact('employees', 'transferMethods'));
    }

    /**
     * Store a newly created salary transfer.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'payroll_id' => 'required|exists:payrolls,id',
            'transfer_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'transfer_method' => ['required', 'in:' . implode(',', array_keys(SalaryTransfer::TRANSFER_METHODS))],
            'notes' => 'nullable|string',
        ]);

        // Check if employee belongs to company
        $employee = Employee::where('id', $request->employee_id)
            ->where('company_id', $user->company_id)
            ->firstOrFail();

        // Check if bank account belongs to employee
        $bankAccount = BankAccount::where('id', $request->bank_account_id)
            ->where('employee_id', $request->employee_id)
            ->where('company_id', $user->company_id)
            ->firstOrFail();

        // Check if payroll belongs to employee
        $payroll = Payroll::where('id', $request->payroll_id)
            ->where('employee_id', $request->employee_id)
            ->where('company_id', $user->company_id)
            ->firstOrFail();

        // Generate reference number
        $referenceNumber = 'ST' . date('Ymd') . Str::random(6);

        $salaryTransfer = SalaryTransfer::create([
            'company_id' => $user->company_id,
            'employee_id' => $request->employee_id,
            'bank_account_id' => $request->bank_account_id,
            'payroll_id' => $request->payroll_id,
            'transfer_date' => $request->transfer_date,
            'amount' => $request->amount,
            'transfer_method' => $request->transfer_method,
            'reference_number' => $referenceNumber,
            'status' => SalaryTransfer::STATUS_PENDING,
            'notes' => $request->notes,
            'processed_by' => $user->id,
        ]);

        return redirect()->route('salary-transfers.index')
            ->with('success', 'Salary transfer created successfully.');
    }

    /**
     * Display the specified salary transfer.
     */
    public function show(SalaryTransfer $salaryTransfer)
    {
        $this->authorize('view', $salaryTransfer);
        
        $salaryTransfer->load(['employee', 'bankAccount', 'payroll', 'processor']);
        
        return view('salary-transfers.show', compact('salaryTransfer'));
    }

    /**
     * Show the form for editing the specified salary transfer.
     */
    public function edit(SalaryTransfer $salaryTransfer)
    {
        $this->authorize('update', $salaryTransfer);
        
        $user = Auth::user();
        $employees = Employee::where('company_id', $user->company_id)->get();
        $transferMethods = SalaryTransfer::TRANSFER_METHODS;

        return view('salary-transfers.edit', compact('salaryTransfer', 'employees', 'transferMethods'));
    }

    /**
     * Update the specified salary transfer.
     */
    public function update(Request $request, SalaryTransfer $salaryTransfer)
    {
        $this->authorize('update', $salaryTransfer);
        
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'payroll_id' => 'required|exists:payrolls,id',
            'transfer_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'transfer_method' => ['required', 'in:' . implode(',', array_keys(SalaryTransfer::TRANSFER_METHODS))],
            'status' => ['required', 'in:' . implode(',', [
                SalaryTransfer::STATUS_PENDING,
                SalaryTransfer::STATUS_PROCESSING,
                SalaryTransfer::STATUS_COMPLETED,
                SalaryTransfer::STATUS_FAILED,
                SalaryTransfer::STATUS_CANCELLED
            ])],
            'confirmation_date' => 'nullable|date',
            'bank_statement_reference' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        // Check if employee belongs to company
        $employee = Employee::where('id', $request->employee_id)
            ->where('company_id', $salaryTransfer->company_id)
            ->firstOrFail();

        // Check if bank account belongs to employee
        $bankAccount = BankAccount::where('id', $request->bank_account_id)
            ->where('employee_id', $request->employee_id)
            ->where('company_id', $salaryTransfer->company_id)
            ->firstOrFail();

        // Check if payroll belongs to employee
        $payroll = Payroll::where('id', $request->payroll_id)
            ->where('employee_id', $request->employee_id)
            ->where('company_id', $salaryTransfer->company_id)
            ->firstOrFail();

        $updateData = [
            'employee_id' => $request->employee_id,
            'bank_account_id' => $request->bank_account_id,
            'payroll_id' => $request->payroll_id,
            'transfer_date' => $request->transfer_date,
            'amount' => $request->amount,
            'transfer_method' => $request->transfer_method,
            'status' => $request->status,
            'bank_statement_reference' => $request->bank_statement_reference,
            'notes' => $request->notes,
        ];

        // Set confirmation date if status is completed
        if ($request->status === SalaryTransfer::STATUS_COMPLETED && !$salaryTransfer->confirmation_date) {
            $updateData['confirmation_date'] = now();
        }

        $salaryTransfer->update($updateData);

        return redirect()->route('salary-transfers.index')
            ->with('success', 'Salary transfer updated successfully.');
    }

    /**
     * Remove the specified salary transfer.
     */
    public function destroy(SalaryTransfer $salaryTransfer)
    {
        $this->authorize('delete', $salaryTransfer);

        if ($salaryTransfer->status !== SalaryTransfer::STATUS_PENDING) {
            return redirect()->route('salary-transfers.index')
                ->with('error', 'Only pending transfers can be deleted.');
        }

        $salaryTransfer->delete();

        return redirect()->route('salary-transfers.index')
            ->with('success', 'Salary transfer deleted successfully.');
    }

    /**
     * Process salary transfer (change status to processing).
     */
    public function process(SalaryTransfer $salaryTransfer)
    {
        $this->authorize('update', $salaryTransfer);

        if ($salaryTransfer->status !== SalaryTransfer::STATUS_PENDING) {
            return redirect()->route('salary-transfers.index')
                ->with('error', 'Only pending transfers can be processed.');
        }

        $salaryTransfer->update([
            'status' => SalaryTransfer::STATUS_PROCESSING,
            'processed_by' => Auth::id(),
        ]);

        return redirect()->route('salary-transfers.index')
            ->with('success', 'Salary transfer processing started.');
    }

    /**
     * Complete salary transfer.
     */
    public function complete(SalaryTransfer $salaryTransfer)
    {
        $this->authorize('update', $salaryTransfer);

        if (!in_array($salaryTransfer->status, [SalaryTransfer::STATUS_PENDING, SalaryTransfer::STATUS_PROCESSING])) {
            return redirect()->route('salary-transfers.index')
                ->with('error', 'Transfer must be pending or processing to be completed.');
        }

        $salaryTransfer->update([
            'status' => SalaryTransfer::STATUS_COMPLETED,
            'confirmation_date' => now(),
            'processed_by' => Auth::id(),
        ]);

        return redirect()->route('salary-transfers.index')
            ->with('success', 'Salary transfer completed successfully.');
    }

    /**
     * Cancel salary transfer.
     */
    public function cancel(SalaryTransfer $salaryTransfer)
    {
        $this->authorize('update', $salaryTransfer);

        if (!$salaryTransfer->canBeCancelled()) {
            return redirect()->route('salary-transfers.index')
                ->with('error', 'Transfer cannot be cancelled.');
        }

        $salaryTransfer->update([
            'status' => SalaryTransfer::STATUS_CANCELLED,
            'processed_by' => Auth::id(),
        ]);

        return redirect()->route('salary-transfers.index')
            ->with('success', 'Salary transfer cancelled successfully.');
    }

    /**
     * Retry failed salary transfer.
     */
    public function retry(SalaryTransfer $salaryTransfer)
    {
        $this->authorize('update', $salaryTransfer);

        if (!$salaryTransfer->canBeRetried()) {
            return redirect()->route('salary-transfers.index')
                ->with('error', 'Only failed transfers can be retried.');
        }

        $salaryTransfer->update([
            'status' => SalaryTransfer::STATUS_PENDING,
            'processed_by' => Auth::id(),
        ]);

        return redirect()->route('salary-transfers.index')
            ->with('success', 'Salary transfer retry initiated.');
    }

    /**
     * Batch salary transfer from payroll.
     */
    public function batchTransfer(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'payroll_period' => 'required|string',
            'transfer_date' => 'required|date',
            'transfer_method' => ['required', 'in:' . implode(',', array_keys(SalaryTransfer::TRANSFER_METHODS))],
            'employee_ids' => 'required|array',
            'employee_ids.*' => 'exists:employees,id',
        ]);

        $successCount = 0;
        $errorCount = 0;
        $errors = [];

        foreach ($request->employee_ids as $employeeId) {
            try {
                // Get employee's primary bank account
                $bankAccount = BankAccount::where('employee_id', $employeeId)
                    ->where('company_id', $user->company_id)
                    ->where('is_active', true)
                    ->where('is_primary', true)
                    ->first();

                if (!$bankAccount) {
                    $errors[] = "Employee ID {$employeeId}: No primary bank account found";
                    $errorCount++;
                    continue;
                }

                // Get payroll for this employee and period
                $payroll = Payroll::where('employee_id', $employeeId)
                    ->where('company_id', $user->company_id)
                    ->where('payroll_period', $request->payroll_period)
                    ->where('status', 'approved')
                    ->first();

                if (!$payroll) {
                    $errors[] = "Employee ID {$employeeId}: No approved payroll found for period {$request->payroll_period}";
                    $errorCount++;
                    continue;
                }

                // Check if transfer already exists
                $existingTransfer = SalaryTransfer::where('employee_id', $employeeId)
                    ->where('payroll_id', $payroll->id)
                    ->exists();

                if ($existingTransfer) {
                    $errors[] = "Employee ID {$employeeId}: Transfer already exists for this payroll";
                    $errorCount++;
                    continue;
                }

                // Create salary transfer
                $referenceNumber = 'ST' . date('Ymd') . Str::random(6);
                
                SalaryTransfer::create([
                    'company_id' => $user->company_id,
                    'employee_id' => $employeeId,
                    'bank_account_id' => $bankAccount->id,
                    'payroll_id' => $payroll->id,
                    'transfer_date' => $request->transfer_date,
                    'amount' => $payroll->net_salary,
                    'transfer_method' => $request->transfer_method,
                    'reference_number' => $referenceNumber,
                    'status' => SalaryTransfer::STATUS_PENDING,
                    'processed_by' => $user->id,
                ]);

                $successCount++;
            } catch (\Exception $e) {
                $errors[] = "Employee ID {$employeeId}: " . $e->getMessage();
                $errorCount++;
            }
        }

        $message = "Batch transfer completed. Success: {$successCount}, Errors: {$errorCount}";
        if (!empty($errors)) {
            $message .= ". Errors: " . implode(', ', array_slice($errors, 0, 5));
        }

        return redirect()->route('salary-transfers.index')
            ->with('success', $message);
    }

    /**
     * Import bank statement for reconciliation.
     */
    public function importBankStatement(Request $request)
    {
        $request->validate([
            'bank_statement_file' => 'required|file|mimes:csv,txt',
            'bank_name' => 'required|string',
        ]);

        // This is a placeholder for bank statement import functionality
        // In a real implementation, you would parse the CSV file and match transactions
        
        return redirect()->route('salary-transfers.index')
            ->with('success', 'Bank statement import functionality will be implemented here.');
    }

    /**
     * Get salary transfer statistics.
     */
    public function statistics()
    {
        $user = Auth::user();
        
        $statistics = [
            'total_transfers' => SalaryTransfer::where('company_id', $user->company_id)->count(),
            'pending_transfers' => SalaryTransfer::where('company_id', $user->company_id)
                ->where('status', SalaryTransfer::STATUS_PENDING)->count(),
            'completed_transfers' => SalaryTransfer::where('company_id', $user->company_id)
                ->where('status', SalaryTransfer::STATUS_COMPLETED)->count(),
            'failed_transfers' => SalaryTransfer::where('company_id', $user->company_id)
                ->where('status', SalaryTransfer::STATUS_FAILED)->count(),
            'total_amount' => SalaryTransfer::where('company_id', $user->company_id)
                ->where('status', SalaryTransfer::STATUS_COMPLETED)->sum('amount'),
        ];

        return response()->json($statistics);
    }
} 