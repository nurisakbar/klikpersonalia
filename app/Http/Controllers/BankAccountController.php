<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BankAccountController extends Controller
{
    /**
     * Display a listing of bank accounts.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = BankAccount::with(['employee'])
            ->where('company_id', $user->company_id);

        // Filter by employee
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        // Filter by bank name
        if ($request->filled('bank_name')) {
            $query->where('bank_name', 'LIKE', '%' . $request->bank_name . '%');
        }

        // Filter by status
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        $bankAccounts = $query->orderBy('created_at', 'desc')->paginate(15);
        $employees = Employee::where('company_id', $user->company_id)->get();

        return view('bank-accounts.index', compact('bankAccounts', 'employees'));
    }

    /**
     * Show the form for creating a new bank account.
     */
    public function create()
    {
        $user = Auth::user();
        $employees = Employee::where('company_id', $user->company_id)->get();
        $accountTypes = BankAccount::ACCOUNT_TYPES;

        return view('bank-accounts.create', compact('employees', 'accountTypes'));
    }

    /**
     * Store a newly created bank account.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'bank_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:50',
            'account_holder_name' => 'required|string|max:255',
            'branch_code' => 'nullable|string|max:20',
            'swift_code' => 'nullable|string|max:20',
            'account_type' => ['required', Rule::in(array_keys(BankAccount::ACCOUNT_TYPES))],
            'is_active' => 'boolean',
            'is_primary' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        // Check if employee belongs to company
        $employee = Employee::where('id', $request->employee_id)
            ->where('company_id', $user->company_id)
            ->firstOrFail();

        // If this is primary account, unset other primary accounts for this employee
        if ($request->is_primary) {
            BankAccount::where('employee_id', $request->employee_id)
                ->where('company_id', $user->company_id)
                ->update(['is_primary' => false]);
        }

        $bankAccount = BankAccount::create([
            'company_id' => $user->company_id,
            'employee_id' => $request->employee_id,
            'bank_name' => $request->bank_name,
            'account_number' => $request->account_number,
            'account_holder_name' => $request->account_holder_name,
            'branch_code' => $request->branch_code,
            'swift_code' => $request->swift_code,
            'account_type' => $request->account_type,
            'is_active' => $request->boolean('is_active', true),
            'is_primary' => $request->boolean('is_primary', false),
            'notes' => $request->notes,
        ]);

        return redirect()->route('bank-accounts.index')
            ->with('success', 'Bank account created successfully.');
    }

    /**
     * Display the specified bank account.
     */
    public function show(BankAccount $bankAccount)
    {
        $this->authorize('view', $bankAccount);
        
        $bankAccount->load(['employee', 'salaryTransfers.payroll']);
        
        return view('bank-accounts.show', compact('bankAccount'));
    }

    /**
     * Show the form for editing the specified bank account.
     */
    public function edit(BankAccount $bankAccount)
    {
        $this->authorize('update', $bankAccount);
        
        $user = Auth::user();
        $employees = Employee::where('company_id', $user->company_id)->get();
        $accountTypes = BankAccount::ACCOUNT_TYPES;

        return view('bank-accounts.edit', compact('bankAccount', 'employees', 'accountTypes'));
    }

    /**
     * Update the specified bank account.
     */
    public function update(Request $request, BankAccount $bankAccount)
    {
        $this->authorize('update', $bankAccount);
        
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'bank_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:50',
            'account_holder_name' => 'required|string|max:255',
            'branch_code' => 'nullable|string|max:20',
            'swift_code' => 'nullable|string|max:20',
            'account_type' => ['required', Rule::in(array_keys(BankAccount::ACCOUNT_TYPES))],
            'is_active' => 'boolean',
            'is_primary' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        // Check if employee belongs to company
        $employee = Employee::where('id', $request->employee_id)
            ->where('company_id', $bankAccount->company_id)
            ->firstOrFail();

        // If this is primary account, unset other primary accounts for this employee
        if ($request->is_primary && !$bankAccount->is_primary) {
            BankAccount::where('employee_id', $request->employee_id)
                ->where('company_id', $bankAccount->company_id)
                ->where('id', '!=', $bankAccount->id)
                ->update(['is_primary' => false]);
        }

        $bankAccount->update([
            'employee_id' => $request->employee_id,
            'bank_name' => $request->bank_name,
            'account_number' => $request->account_number,
            'account_holder_name' => $request->account_holder_name,
            'branch_code' => $request->branch_code,
            'swift_code' => $request->swift_code,
            'account_type' => $request->account_type,
            'is_active' => $request->boolean('is_active', true),
            'is_primary' => $request->boolean('is_primary', false),
            'notes' => $request->notes,
        ]);

        return redirect()->route('bank-accounts.index')
            ->with('success', 'Bank account updated successfully.');
    }

    /**
     * Remove the specified bank account.
     */
    public function destroy(BankAccount $bankAccount)
    {
        $this->authorize('delete', $bankAccount);

        // Check if account has any salary transfers
        if ($bankAccount->salaryTransfers()->exists()) {
            return redirect()->route('bank-accounts.index')
                ->with('error', 'Cannot delete bank account with existing salary transfers.');
        }

        $bankAccount->delete();

        return redirect()->route('bank-accounts.index')
            ->with('success', 'Bank account deleted successfully.');
    }

    /**
     * Toggle active status of bank account.
     */
    public function toggleStatus(BankAccount $bankAccount)
    {
        $this->authorize('update', $bankAccount);

        $bankAccount->update([
            'is_active' => !$bankAccount->is_active
        ]);

        $status = $bankAccount->is_active ? 'activated' : 'deactivated';

        return redirect()->route('bank-accounts.index')
            ->with('success', "Bank account {$status} successfully.");
    }

    /**
     * Set bank account as primary.
     */
    public function setPrimary(BankAccount $bankAccount)
    {
        $this->authorize('update', $bankAccount);

        // Unset other primary accounts for this employee
        BankAccount::where('employee_id', $bankAccount->employee_id)
            ->where('company_id', $bankAccount->company_id)
            ->update(['is_primary' => false]);

        // Set this account as primary
        $bankAccount->update(['is_primary' => true]);

        return redirect()->route('bank-accounts.index')
            ->with('success', 'Bank account set as primary successfully.');
    }

    /**
     * Get bank accounts for employee (AJAX).
     */
    public function getEmployeeAccounts(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'employee_id' => 'required|exists:employees,id'
        ]);

        $bankAccounts = BankAccount::where('company_id', $user->company_id)
            ->where('employee_id', $request->employee_id)
            ->where('is_active', true)
            ->get(['id', 'bank_name', 'account_number', 'account_holder_name', 'is_primary']);

        return response()->json($bankAccounts);
    }
} 