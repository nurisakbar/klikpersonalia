<?php

namespace App\Repositories;

use App\Models\BankAccount;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Collection;

class BankAccountRepository
{
    protected $model;

    public function __construct(BankAccount $model)
    {
        $this->model = $model;
    }

    /**
     * Get all bank accounts for current company
     */
    public function getAllForCompany(array $filters = []): Collection
    {
        $user = Auth::user();
        
        if (!$user) {
            return collect();
        }
        
        $query = $this->model->with(['employee'])
            ->where('company_id', $user->company_id);

        // Apply filters
        if (isset($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }

        if (isset($filters['bank_name'])) {
            $query->where('bank_name', 'LIKE', '%' . $filters['bank_name'] . '%');
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (isset($filters['account_type'])) {
            $query->where('account_type', $filters['account_type']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get bank account by ID
     */
    public function findById(string $id): ?BankAccount
    {
        $user = Auth::user();
        
        return $this->model->with(['employee', 'salaryTransfers.payroll'])
            ->where('company_id', $user->company_id)
            ->find($id);
    }

    /**
     * Create new bank account
     */
    public function create(array $data): BankAccount
    {
        $user = Auth::user();
        
        // If this is primary account, unset other primary accounts for this employee
        if (isset($data['is_primary']) && $data['is_primary']) {
            $this->model->where('employee_id', $data['employee_id'])
                ->where('company_id', $user->company_id)
                ->update(['is_primary' => false]);
        }

        return $this->model->create([
            'company_id' => $user->company_id,
            'employee_id' => $data['employee_id'],
            'bank_name' => $data['bank_name'],
            'account_number' => $data['account_number'],
            'account_holder_name' => $data['account_holder_name'],
            'branch_code' => $data['branch_code'] ?? null,
            'swift_code' => $data['swift_code'] ?? null,
            'account_type' => $data['account_type'],
            'is_active' => $data['is_active'] ?? true,
            'is_primary' => $data['is_primary'] ?? false,
            'notes' => $data['notes'] ?? null,
        ]);
    }

    /**
     * Update bank account
     */
    public function update(BankAccount $bankAccount, array $data): bool
    {
        // If this is primary account, unset other primary accounts for this employee
        if (isset($data['is_primary']) && $data['is_primary'] && !$bankAccount->is_primary) {
            $this->model->where('employee_id', $data['employee_id'])
                ->where('company_id', $bankAccount->company_id)
                ->where('id', '!=', $bankAccount->id)
                ->update(['is_primary' => false]);
        }

        return $bankAccount->update([
            'employee_id' => $data['employee_id'],
            'bank_name' => $data['bank_name'],
            'account_number' => $data['account_number'],
            'account_holder_name' => $data['account_holder_name'],
            'branch_code' => $data['branch_code'] ?? null,
            'swift_code' => $data['swift_code'] ?? null,
            'account_type' => $data['account_type'],
            'is_active' => $data['is_active'] ?? true,
            'is_primary' => $data['is_primary'] ?? false,
            'notes' => $data['notes'] ?? null,
        ]);
    }

    /**
     * Delete bank account
     */
    public function delete(BankAccount $bankAccount): bool
    {
        return $bankAccount->delete();
    }

    /**
     * Toggle active status
     */
    public function toggleStatus(BankAccount $bankAccount): bool
    {
        return $bankAccount->update([
            'is_active' => !$bankAccount->is_active
        ]);
    }

    /**
     * Set as primary account
     */
    public function setPrimary(BankAccount $bankAccount): bool
    {
        // Unset other primary accounts for this employee
        $this->model->where('employee_id', $bankAccount->employee_id)
            ->where('company_id', $bankAccount->company_id)
            ->update(['is_primary' => false]);

        // Set this account as primary
        return $bankAccount->update(['is_primary' => true]);
    }

    /**
     * Get active bank accounts for employee
     */
    public function getActiveAccountsForEmployee(string $employeeId): Collection
    {
        $user = Auth::user();
        
        return $this->model->where('company_id', $user->company_id)
            ->where('employee_id', $employeeId)
            ->where('is_active', true)
            ->get(['id', 'bank_name', 'account_number', 'account_holder_name', 'is_primary']);
    }

    /**
     * Check if bank account has salary transfers
     */
    public function hasSalaryTransfers(BankAccount $bankAccount): bool
    {
        return $bankAccount->salaryTransfers()->exists();
    }

    /**
     * Get bank accounts with pagination
     */
    public function getPaginated(array $filters = [], int $perPage = 15)
    {
        $user = Auth::user();
        
        $query = $this->model->with(['employee'])
            ->where('company_id', $user->company_id);

        // Apply filters
        if (isset($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }

        if (isset($filters['bank_name'])) {
            $query->where('bank_name', 'LIKE', '%' . $filters['bank_name'] . '%');
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (isset($filters['account_type'])) {
            $query->where('account_type', $filters['account_type']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }
}
