<?php

namespace App\Repositories;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class EmployeeRepository
{
    /**
     * Get all employees for current company.
     */
    public function getAllForCurrentCompany(): Collection
    {
        return Employee::currentCompany()
            ->orderBy('name')
            ->get();
    }

    /**
     * Get employees with pagination.
     */
    public function getPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return Employee::currentCompany()
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Find employee by ID.
     */
    public function findById(string $id): ?Employee
    {
        return Employee::currentCompany()->find($id);
    }

    /**
     * Find employee by employee ID.
     */
    public function findByEmployeeId(string $employeeId): ?Employee
    {
        return Employee::currentCompany()
            ->where('employee_id', $employeeId)
            ->first();
    }

    /**
     * Find employee by email.
     */
    public function findByEmail(string $email): ?Employee
    {
        return Employee::currentCompany()
            ->where('email', $email)
            ->first();
    }

    /**
     * Create new employee.
     */
    public function create(array $data): Employee
    {
        // Add company_id to data
        $data['company_id'] = auth()->user()->company_id;
        
        // Generate employee ID
        $data['employee_id'] = $this->generateEmployeeId();
        
        return Employee::create($data);
    }

    /**
     * Update employee.
     */
    public function update(Employee $employee, array $data): bool
    {
        // Ensure company_id is set
        $data['company_id'] = auth()->user()->company_id;
        
        return $employee->update($data);
    }

    /**
     * Delete employee.
     */
    public function delete(Employee $employee): bool
    {
        return $employee->delete();
    }

    /**
     * Get active employees for current company.
     */
    public function getActiveEmployees(): Collection
    {
        return Employee::currentCompany()
            ->active()
            ->orderBy('name')
            ->get();
    }

    /**
     * Get limited employees for select (no status filter).
     */
    public function getForSelect(int $limit = 20): Collection
    {
        return Employee::currentCompany()
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }

    /**
     * Get employees by department.
     */
    public function getByDepartment(string $department): Collection
    {
        return Employee::currentCompany()
            ->where('department', $department)
            ->orderBy('name')
            ->get();
    }

    /**
     * Get employees by position.
     */
    public function getByPosition(string $position): Collection
    {
        return Employee::currentCompany()
            ->where('position', $position)
            ->orderBy('name')
            ->get();
    }

    /**
     * Search employees by name or email.
     */
    public function search(string $query): Collection
    {
        return Employee::currentCompany()
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%")
                  ->orWhere('employee_id', 'like', "%{$query}%");
            })
            ->orderBy('name')
            ->get();
    }

    /**
     * Get employees count by status.
     */
    public function getCountByStatus(): array
    {
        return Employee::currentCompany()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
    }

    /**
     * Get employees for DataTables.
     */
    public function getForDataTables()
    {
        return Employee::currentCompany()
            ->select([
                'id',
                'employee_id',
                'name',
                'email',
                'phone',
                'department',
                'position',
                'join_date',
                'basic_salary',
                'status'
            ]);
    }

    /**
     * Get departments list.
     */
    public function getDepartments(): array
    {
        return [
            'IT' => 'Information Technology',
            'HR' => 'Human Resources',
            'Finance' => 'Finance',
            'Marketing' => 'Marketing',
            'Sales' => 'Sales',
            'Operations' => 'Operations'
        ];
    }

    /**
     * Get positions list.
     */
    public function getPositions(): array
    {
        return [
            'Staff' => 'Staff',
            'Senior Staff' => 'Senior Staff',
            'Supervisor' => 'Supervisor',
            'Manager' => 'Manager',
            'Senior Manager' => 'Senior Manager',
            'Director' => 'Director'
        ];
    }

    /**
     * Get status list.
     */
    public function getStatuses(): array
    {
        return [
            'active' => 'Aktif',
            'inactive' => 'Tidak Aktif',
            'terminated' => 'Berhenti'
        ];
    }

    /**
     * Get list of Indonesian banks.
     */
    public function getBanks(): array
    {
        return [
            'BRI' => 'Bank Rakyat Indonesia (BRI)',
            'BCA' => 'Bank Central Asia (BCA)',
            'BNI' => 'Bank Negara Indonesia (BNI)',
            'Mandiri' => 'Bank Mandiri',
            'BTN' => 'Bank Tabungan Negara (BTN)',
            'CIMB Niaga' => 'CIMB Niaga',
            'Danamon' => 'Bank Danamon',
            'Permata' => 'Bank Permata',
            'OCBC NISP' => 'OCBC NISP',
            'Bank Mega' => 'Bank Mega',
            'Maybank' => 'Maybank Indonesia',
            'BII' => 'Bank Internasional Indonesia (BII)',
            'Panin' => 'Bank Panin',
            'UOB' => 'United Overseas Bank (UOB)',
            'Standard Chartered' => 'Standard Chartered Bank',
            'Citibank' => 'Citibank',
            'HSBC' => 'HSBC Indonesia',
            'Deutsche Bank' => 'Deutsche Bank',
            'ANZ' => 'ANZ Indonesia',
            'Bank Jateng' => 'Bank Jawa Tengah',
            'Bank Jatim' => 'Bank Jawa Timur',
            'Bank DKI' => 'Bank DKI',
            'Bank BJB' => 'Bank Jabar Banten',
            'Bank Sumut' => 'Bank Sumatra Utara',
            'Bank Kalbar' => 'Bank Kalimantan Barat',
            'Bank Kaltim' => 'Bank Kalimantan Timur',
            'Bank Sulsel' => 'Bank Sulawesi Selatan',
            'Bank Papua' => 'Bank Papua',
            'BSI' => 'Bank Syariah Indonesia (BSI)',
            'Bank Muamalat' => 'Bank Muamalat Indonesia',
            'Other' => 'Bank Lainnya'
        ];
    }

    /**
     * Generate unique employee ID.
     */
    private function generateEmployeeId(): string
    {
        $lastEmployee = Employee::where('company_id', auth()->user()->company_id)
            ->orderBy('id', 'desc')
            ->first();
            
        if ($lastEmployee && $lastEmployee->employee_id) {
            $lastId = intval(substr($lastEmployee->employee_id, 3));
            $newId = $lastId + 1;
        } else {
            $newId = 1;
        }
        
        return 'EMP' . str_pad($newId, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Check if email is unique (excluding specific employee).
     */
    public function isEmailUnique(string $email, ?string $excludeId = null): bool
    {
        $query = Employee::currentCompany()->where('email', $email);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return !$query->exists();
    }

    /**
     * Get employees with salary range.
     */
    public function getBySalaryRange(float $minSalary, float $maxSalary): Collection
    {
        return Employee::currentCompany()
            ->whereBetween('basic_salary', [$minSalary, $maxSalary])
            ->orderBy('basic_salary', 'desc')
            ->get();
    }

    /**
     * Get recent employees (joined in last N days).
     */
    public function getRecentEmployees(int $days = 30): Collection
    {
        return Employee::currentCompany()
            ->where('join_date', '>=', now()->subDays($days))
            ->orderBy('join_date', 'desc')
            ->get();
    }

    /**
     * Bulk update employee status.
     */
    public function bulkUpdateStatus(array $employeeIds, string $status): int
    {
        return Employee::currentCompany()
            ->whereIn('id', $employeeIds)
            ->update(['status' => $status]);
    }
}
