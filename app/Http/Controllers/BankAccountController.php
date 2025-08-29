<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Employee;
use App\Services\BankAccountService;
use App\DataTables\BankAccountDataTable;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class BankAccountController extends Controller
{
    protected $bankAccountService;

    public function __construct(BankAccountService $bankAccountService)
    {
        $this->bankAccountService = $bankAccountService;
    }

    /**
     * Display a listing of bank accounts.
     */
    public function index()
    {
        $user = Auth::user();
        $employees = Employee::where('company_id', $user->company_id)->get();

        return view('bank-accounts.index', compact('employees'));
    }

    /**
     * Get bank accounts data for DataTable
     */
    public function data()
    {
        $user = Auth::user();
        $query = BankAccount::with(['employee'])
            ->where('company_id', $user->company_id);

        return DataTables::of($query)
            ->addColumn('action', function ($bankAccount) {
                return '
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-info view-btn" data-id="' . $bankAccount->id . '" title="Detail">
                            <i class="fas fa-eye"></i>
                        </button>
                        <a href="' . route('bank-accounts.edit', $bankAccount->id) . '" class="btn btn-sm btn-warning" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button type="button" class="btn btn-sm btn-danger delete-btn" data-id="' . $bankAccount->id . '" data-name="' . htmlspecialchars($bankAccount->bank_name . ' - ' . $bankAccount->account_holder_name) . '" title="Hapus">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                ';
            })
            ->addColumn('status_badge', function ($bankAccount) {
                if ($bankAccount->is_active) {
                    return '<span class="badge badge-success">Aktif</span>';
                } else {
                    return '<span class="badge badge-warning">Tidak Aktif</span>';
                }
            })
            ->addColumn('primary_badge', function ($bankAccount) {
                if ($bankAccount->is_primary) {
                    return '<span class="badge badge-primary">Utama</span>';
                } else {
                    return '<span class="badge badge-secondary">-</span>';
                }
            })
            ->addColumn('account_number_masked', function ($bankAccount) {
                return $bankAccount->formatted_account_number;
            })
            ->addColumn('account_type_label', function ($bankAccount) {
                $types = [
                    'savings' => 'Tabungan',
                    'current' => 'Giro',
                    'salary' => 'Gaji'
                ];
                return $types[$bankAccount->account_type] ?? $bankAccount->account_type;
            })
            ->addColumn('employee.name', function ($bankAccount) {
                return optional($bankAccount->employee)->name;
            })
            ->rawColumns(['action', 'status_badge', 'primary_badge', 'account_number_masked', 'account_type_label'])
            ->make(true);
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
        try {
            $bankAccount = $this->bankAccountService->createBankAccount($request->all());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Rekening bank berhasil dibuat.',
                    'data' => $bankAccount
                ]);
            }

            return redirect()->route('bank-accounts.index')
                ->with('success', 'Rekening bank berhasil dibuat.');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified bank account.
     */
    public function show(BankAccount $bankAccount)
    {
        $this->authorize('view', $bankAccount);
        
        try {
            $bankAccount = $this->bankAccountService->getBankAccountById($bankAccount->id);
            
            if (!$bankAccount) {
                if (request()->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Rekening bank tidak ditemukan.'
                    ], 404);
                }
                
                return redirect()->route('bank-accounts.index')
                    ->with('error', 'Rekening bank tidak ditemukan.');
            }

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => $bankAccount
                ]);
            }
            
            return view('bank-accounts.show', compact('bankAccount'));
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
            
            return redirect()->route('bank-accounts.index')
                ->with('error', $e->getMessage());
        }
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
        
        try {
            $result = $this->bankAccountService->updateBankAccount($bankAccount, $request->all());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Rekening bank berhasil diperbarui.',
                    'data' => $bankAccount->fresh()
                ]);
            }

            return redirect()->route('bank-accounts.index')
                ->with('success', 'Rekening bank berhasil diperbarui.');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Remove the specified bank account.
     */
    public function destroy(BankAccount $bankAccount)
    {
        $this->authorize('delete', $bankAccount);

        try {
            $result = $this->bankAccountService->deleteBankAccount($bankAccount);

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Rekening bank berhasil dihapus.'
                ]);
            }

            return redirect()->route('bank-accounts.index')
                ->with('success', 'Rekening bank berhasil dihapus.');
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }

            return redirect()->route('bank-accounts.index')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Toggle active status of bank account.
     */
    public function toggleStatus(BankAccount $bankAccount)
    {
        $this->authorize('update', $bankAccount);

        try {
            $result = $this->bankAccountService->toggleStatus($bankAccount);

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Status rekening bank berhasil diubah.',
                    'data' => $bankAccount->fresh()
                ]);
            }

            $status = $bankAccount->is_active ? 'diaktifkan' : 'dinonaktifkan';
            return redirect()->route('bank-accounts.index')
                ->with('success', "Rekening bank berhasil {$status}.");
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }

            return redirect()->route('bank-accounts.index')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Set bank account as primary.
     */
    public function setPrimary(BankAccount $bankAccount)
    {
        $this->authorize('update', $bankAccount);

        try {
            $result = $this->bankAccountService->setPrimary($bankAccount);

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Rekening bank berhasil diatur sebagai utama.',
                    'data' => $bankAccount->fresh()
                ]);
            }

            return redirect()->route('bank-accounts.index')
                ->with('success', 'Rekening bank berhasil diatur sebagai utama.');
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }

            return redirect()->route('bank-accounts.index')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Get bank accounts for employee (AJAX).
     */
    public function getEmployeeAccounts(Request $request)
    {
        try {
            $request->validate([
                'employee_id' => 'required|exists:employees,id'
            ]);

            $bankAccounts = $this->bankAccountService->getActiveAccountsForEmployee($request->employee_id);

            return response()->json($bankAccounts);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
} 