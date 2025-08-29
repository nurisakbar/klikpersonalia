<?php

namespace App\Services;

use App\Repositories\BankAccountRepository;
use App\Models\BankAccount;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Exception;

class BankAccountService
{
    protected $bankAccountRepository;

    public function __construct(BankAccountRepository $bankAccountRepository)
    {
        $this->bankAccountRepository = $bankAccountRepository;
    }

    /**
     * Get all bank accounts with filters
     */
    public function getAllBankAccounts(array $filters = [])
    {
        try {
            return $this->bankAccountRepository->getAllForCompany($filters);
        } catch (Exception $e) {
            throw new Exception('Gagal mengambil data rekening bank: ' . $e->getMessage());
        }
    }

    /**
     * Get bank account by ID
     */
    public function getBankAccountById(string $id): ?BankAccount
    {
        try {
            return $this->bankAccountRepository->findById($id);
        } catch (Exception $e) {
            throw new Exception('Gagal mengambil data rekening bank: ' . $e->getMessage());
        }
    }

    /**
     * Create new bank account
     */
    public function createBankAccount(array $data): BankAccount
    {
        try {
            // Validate data
            $validator = Validator::make($data, [
                'employee_id' => 'required|exists:employees,id',
                'bank_name' => 'required|string|max:255',
                'account_number' => 'required|string|max:50',
                'account_holder_name' => 'required|string|max:255',
                'branch_code' => 'nullable|string|max:20',
                'swift_code' => 'nullable|string|max:20',
                'account_type' => 'required|in:savings,current,salary',
                'is_active' => 'boolean',
                'is_primary' => 'boolean',
                'notes' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                throw new Exception($validator->errors()->first());
            }

            // Check if employee belongs to company
            $user = Auth::user();
            $employee = Employee::where('id', $data['employee_id'])
                ->where('company_id', $user->company_id)
                ->first();

            if (!$employee) {
                throw new Exception('Karyawan tidak ditemukan atau tidak memiliki akses.');
            }

            // Check for duplicate account number for same employee
            $existingAccount = BankAccount::where('employee_id', $data['employee_id'])
                ->where('account_number', $data['account_number'])
                ->where('company_id', $user->company_id)
                ->first();

            if ($existingAccount) {
                throw new Exception('Nomor rekening sudah terdaftar untuk karyawan ini.');
            }

            DB::beginTransaction();

            $bankAccount = $this->bankAccountRepository->create($data);

            DB::commit();

            return $bankAccount;

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update bank account
     */
    public function updateBankAccount(BankAccount $bankAccount, array $data): bool
    {
        try {
            // Validate data
            $validator = Validator::make($data, [
                'employee_id' => 'required|exists:employees,id',
                'bank_name' => 'required|string|max:255',
                'account_number' => 'required|string|max:50',
                'account_holder_name' => 'required|string|max:255',
                'branch_code' => 'nullable|string|max:20',
                'swift_code' => 'nullable|string|max:20',
                'account_type' => 'required|in:savings,current,salary',
                'is_active' => 'boolean',
                'is_primary' => 'boolean',
                'notes' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                throw new Exception($validator->errors()->first());
            }

            // Check if employee belongs to company
            $employee = Employee::where('id', $data['employee_id'])
                ->where('company_id', $bankAccount->company_id)
                ->first();

            if (!$employee) {
                throw new Exception('Karyawan tidak ditemukan atau tidak memiliki akses.');
            }

            // Check for duplicate account number for same employee (excluding current account)
            $existingAccount = BankAccount::where('employee_id', $data['employee_id'])
                ->where('account_number', $data['account_number'])
                ->where('company_id', $bankAccount->company_id)
                ->where('id', '!=', $bankAccount->id)
                ->first();

            if ($existingAccount) {
                throw new Exception('Nomor rekening sudah terdaftar untuk karyawan ini.');
            }

            DB::beginTransaction();

            $result = $this->bankAccountRepository->update($bankAccount, $data);

            DB::commit();

            return $result;

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete bank account
     */
    public function deleteBankAccount(BankAccount $bankAccount): bool
    {
        try {
            // Check if account has salary transfers
            if ($this->bankAccountRepository->hasSalaryTransfers($bankAccount)) {
                throw new Exception('Tidak dapat menghapus rekening bank yang memiliki riwayat transfer gaji.');
            }

            DB::beginTransaction();

            $result = $this->bankAccountRepository->delete($bankAccount);

            DB::commit();

            return $result;

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Toggle active status
     */
    public function toggleStatus(BankAccount $bankAccount): bool
    {
        try {
            return $this->bankAccountRepository->toggleStatus($bankAccount);
        } catch (Exception $e) {
            throw new Exception('Gagal mengubah status rekening bank: ' . $e->getMessage());
        }
    }

    /**
     * Set as primary account
     */
    public function setPrimary(BankAccount $bankAccount): bool
    {
        try {
            return $this->bankAccountRepository->setPrimary($bankAccount);
        } catch (Exception $e) {
            throw new Exception('Gagal mengatur rekening bank sebagai utama: ' . $e->getMessage());
        }
    }

    /**
     * Get active bank accounts for employee
     */
    public function getActiveAccountsForEmployee(string $employeeId)
    {
        try {
            return $this->bankAccountRepository->getActiveAccountsForEmployee($employeeId);
        } catch (Exception $e) {
            throw new Exception('Gagal mengambil data rekening bank karyawan: ' . $e->getMessage());
        }
    }

    /**
     * Get bank accounts with pagination
     */
    public function getPaginatedBankAccounts(array $filters = [], int $perPage = 15)
    {
        try {
            return $this->bankAccountRepository->getPaginated($filters, $perPage);
        } catch (Exception $e) {
            throw new Exception('Gagal mengambil data rekening bank: ' . $e->getMessage());
        }
    }

    /**
     * Validate bank account data
     */
    public function validateBankAccountData(array $data): array
    {
        $validator = Validator::make($data, [
            'employee_id' => 'required|exists:employees,id',
            'bank_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:50',
            'account_holder_name' => 'required|string|max:255',
            'branch_code' => 'nullable|string|max:20',
            'swift_code' => 'nullable|string|max:20',
            'account_type' => 'required|in:savings,current,salary',
            'is_active' => 'boolean',
            'is_primary' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return [
                'valid' => false,
                'errors' => $validator->errors()
            ];
        }

        return [
            'valid' => true,
            'errors' => null
        ];
    }
}
