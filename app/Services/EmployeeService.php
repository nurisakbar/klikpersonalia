<?php

namespace App\Services;

use App\Models\Employee;
use App\Repositories\EmployeeRepository;
use App\Http\Resources\EmployeeResource;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class EmployeeService
{
    public function __construct(
        private EmployeeRepository $employeeRepository
    ) {}

    /**
     * Get all employees for current company.
     */
    public function getAllEmployees(): Collection
    {
        return $this->employeeRepository->getAllForCurrentCompany();
    }

    /**
     * Get employees with pagination.
     */
    public function getPaginatedEmployees(int $perPage = 15): LengthAwarePaginator
    {
        return $this->employeeRepository->getPaginated($perPage);
    }

    /**
     * Find employee by ID.
     */
    public function findEmployee(string $id): ?Employee
    {
        return $this->employeeRepository->findById($id);
    }

    /**
     * Create new employee.
     */
    public function createEmployee(array $data): array
    {
        try {
            DB::beginTransaction();

            // Validate unique email
            if (!$this->employeeRepository->isEmailUnique($data['email'])) {
                return [
                    'success' => false,
                    'message' => 'Email sudah digunakan oleh karyawan lain.',
                    'data' => null
                ];
            }

            // Ensure default status for new employee
            $data['status'] = 'active';

            $employee = $this->employeeRepository->create($data);

            DB::commit();

            Log::info('Employee created successfully', [
                'employee_id' => $employee->id,
                'name' => $employee->name,
                'created_by' => auth()->id()
            ]);

            return [
                'success' => true,
                'message' => 'Karyawan berhasil ditambahkan!',
                'data' => new EmployeeResource($employee)
            ];

        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to create employee', [
                'error' => $e->getMessage(),
                'data' => $data,
                'user_id' => auth()->id()
            ]);

            return [
                'success' => false,
                'message' => 'Gagal menambahkan karyawan: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Update employee.
     */
    public function updateEmployee(string $id, array $data): array
    {
        try {
            DB::beginTransaction();

            $employee = $this->employeeRepository->findById($id);
            
            if (!$employee) {
                return [
                    'success' => false,
                    'message' => 'Karyawan tidak ditemukan.',
                    'data' => null
                ];
            }

            // Validate unique email (excluding current employee)
            if (isset($data['email']) && !$this->employeeRepository->isEmailUnique($data['email'], $id)) {
                return [
                    'success' => false,
                    'message' => 'Email sudah digunakan oleh karyawan lain.',
                    'data' => null
                ];
            }

            $this->employeeRepository->update($employee, $data);

            DB::commit();

            Log::info('Employee updated successfully', [
                'employee_id' => $employee->id,
                'name' => $employee->name,
                'updated_by' => auth()->id()
            ]);

            return [
                'success' => true,
                'message' => 'Data karyawan berhasil diperbarui!',
                'data' => new EmployeeResource($employee->fresh())
            ];

        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to update employee', [
                'employee_id' => $id,
                'error' => $e->getMessage(),
                'data' => $data,
                'user_id' => auth()->id()
            ]);

            return [
                'success' => false,
                'message' => 'Gagal memperbarui data karyawan: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Delete employee.
     */
    public function deleteEmployee(string $id): array
    {
        try {
            DB::beginTransaction();

            $employee = $this->employeeRepository->findById($id);
            
            if (!$employee) {
                return [
                    'success' => false,
                    'message' => 'Karyawan tidak ditemukan.',
                    'data' => null
                ];
            }

            // Check if employee has related records that prevent deletion
            if ($this->hasRelatedRecords($employee)) {
                return [
                    'success' => false,
                    'message' => 'Tidak dapat menghapus karyawan karena memiliki data terkait (payroll, absensi, dll).',
                    'data' => null
                ];
            }

            $employeeName = $employee->name;
            $this->employeeRepository->delete($employee);

            DB::commit();

            Log::info('Employee deleted successfully', [
                'employee_id' => $id,
                'name' => $employeeName,
                'deleted_by' => auth()->id()
            ]);

            return [
                'success' => true,
                'message' => "Karyawan {$employeeName} berhasil dihapus!",
                'data' => null
            ];

        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to delete employee', [
                'employee_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return [
                'success' => false,
                'message' => 'Gagal menghapus karyawan: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Get employees for DataTables.
     */
    public function getEmployeesForDataTables()
    {
        return $this->employeeRepository->getForDataTables();
    }

    /**
     * Get form data (departments, positions, etc.).
     */
    public function getFormData(): array
    {
        return [
            'departments' => $this->employeeRepository->getDepartments(),
            'positions' => $this->employeeRepository->getPositions(),
            'statuses' => $this->employeeRepository->getStatuses(),
            'banks' => $this->employeeRepository->getBanks(),
        ];
    }

    /**
     * Search employees.
     */
    public function searchEmployees(string $query): Collection
    {
        return $this->employeeRepository->search($query);
    }

    /**
     * Default list for select (no query), capped.
     */
    public function getDefaultEmployeesForSelect(int $limit = 20): Collection
    {
        return $this->employeeRepository->getForSelect($limit);
    }

    /**
     * Get active employees.
     */
    public function getActiveEmployees(): Collection
    {
        return $this->employeeRepository->getActiveEmployees();
    }

    /**
     * Get employees by department.
     */
    public function getEmployeesByDepartment(string $department): Collection
    {
        return $this->employeeRepository->getByDepartment($department);
    }

    /**
     * Get employees by position.
     */
    public function getEmployeesByPosition(string $position): Collection
    {
        return $this->employeeRepository->getByPosition($position);
    }

    /**
     * Get employee statistics.
     */
    public function getEmployeeStatistics(): array
    {
        $statusCounts = $this->employeeRepository->getCountByStatus();
        $totalEmployees = array_sum($statusCounts);

        $departmentCounts = Employee::currentCompany()
            ->selectRaw('department, COUNT(*) as count')
            ->groupBy('department')
            ->pluck('count', 'department')
            ->toArray();

        $averageSalary = Employee::currentCompany()
            ->avg('basic_salary');

        $recentEmployees = $this->employeeRepository->getRecentEmployees(30);

        return [
            'total_employees' => $totalEmployees,
            'status_breakdown' => $statusCounts,
            'department_breakdown' => $departmentCounts,
            'average_salary' => $averageSalary,
            'recent_employees' => $recentEmployees->count(),
            'active_employees' => $statusCounts['active'] ?? 0,
            'inactive_employees' => $statusCounts['inactive'] ?? 0,
            'terminated_employees' => $statusCounts['terminated'] ?? 0,
        ];
    }

    /**
     * Bulk update employee status.
     */
    public function bulkUpdateStatus(array $employeeIds, string $status): array
    {
        try {
            $validStatuses = ['active', 'inactive', 'terminated'];
            
            if (!in_array($status, $validStatuses)) {
                return [
                    'success' => false,
                    'message' => 'Status tidak valid.',
                    'data' => null
                ];
            }

            $updatedCount = $this->employeeRepository->bulkUpdateStatus($employeeIds, $status);

            Log::info('Bulk status update completed', [
                'employee_ids' => $employeeIds,
                'new_status' => $status,
                'updated_count' => $updatedCount,
                'updated_by' => auth()->id()
            ]);

            return [
                'success' => true,
                'message' => "Status {$updatedCount} karyawan berhasil diperbarui!",
                'data' => ['updated_count' => $updatedCount]
            ];

        } catch (Exception $e) {
            Log::error('Failed to bulk update employee status', [
                'employee_ids' => $employeeIds,
                'status' => $status,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return [
                'success' => false,
                'message' => 'Gagal memperbarui status karyawan: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Export employees data.
     */
    public function exportEmployees(string $format = 'excel'): array
    {
        try {
            $employees = $this->employeeRepository->getAllForCurrentCompany();
            
            // Here you would implement the actual export logic
            // For now, return the data structure
            
            return [
                'success' => true,
                'message' => 'Data karyawan berhasil diekspor!',
                'data' => [
                    'format' => $format,
                    'count' => $employees->count(),
                    'employees' => EmployeeResource::collection($employees)
                ]
            ];

        } catch (Exception $e) {
            Log::error('Failed to export employees', [
                'format' => $format,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return [
                'success' => false,
                'message' => 'Gagal mengekspor data karyawan: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Check if employee has related records.
     */
    private function hasRelatedRecords(Employee $employee): bool
    {
        // Check for payroll records
        if ($employee->payrolls()->exists()) {
            return true;
        }

        // Check for attendance records
        if ($employee->attendances()->exists()) {
            return true;
        }

        // Check for leave records
        if ($employee->leaves()->exists()) {
            return true;
        }

        // Check for tax records
        if ($employee->taxes()->exists()) {
            return true;
        }

        // Check for BPJS records
        if ($employee->bpjs()->exists()) {
            return true;
        }

        return false;
    }

    /**
     * Validate salary range.
     */
    public function validateSalaryRange(float $salary): bool
    {
        $minSalary = 1000000; // 1 juta
        $maxSalary = 999999999999; // 999 miliar

        return $salary >= $minSalary && $salary <= $maxSalary;
    }

    /**
     * Format salary for display.
     */
    public function formatSalary(float $salary): string
    {
        return 'Rp ' . number_format($salary, 0, ',', '.');
    }

    /**
     * Parse salary from formatted string.
     */
    public function parseSalary(string $formattedSalary): float
    {
        // Remove non-numeric characters except decimal point
        $cleanSalary = preg_replace('/[^\d.]/', '', $formattedSalary);
        return (float) $cleanSalary;
    }
}
