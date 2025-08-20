<?php

namespace App\Services;

use App\Models\Employee;
use App\Repositories\PayrollRepository;
use App\Repositories\EmployeeRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

class PayrollService
{
    public function __construct(
        private PayrollRepository $payrollRepository,
        private EmployeeRepository $employeeRepository
    ) {}

    public function generateSingle(array $data): array
    {
        $user = Auth::user();
        
        // Check if user has permission
        if (!in_array($user->role, ['admin', 'hr'])) {
            return [
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk generate payroll.'
            ];
        }

        // Validate employee belongs to user's company
        $employee = Employee::where('id', $data['employee_id'])
            ->where('company_id', $user->company_id)
            ->first();

        if (!$employee) {
            return [
                'success' => false,
                'message' => 'Karyawan tidak ditemukan atau tidak memiliki akses.'
            ];
        }

        if ($this->payrollRepository->existsForEmployeeAndPeriod($data['employee_id'], $data['period'], $user->company_id)) {
            return [
                'success' => false,
                'message' => 'Payroll sudah ada untuk karyawan dan periode ini.'
            ];
        }

        // Calculate total salary
        $totalSalary = ($data['basic_salary'] ?? 0) + ($data['allowance'] ?? 0) - ($data['deduction'] ?? 0);

        // Ensure period is in YYYY-MM format
        $period = $data['period'];
        if (preg_match('/^(\d{4})-(\d{1,2})$/', $period, $matches)) {
            $year = $matches[1];
            $month = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
            $period = $year . '-' . $month;
        }

        $payload = [
            'employee_id' => $data['employee_id'],
            'company_id' => $user->company_id,
            'period' => $period,
            'basic_salary' => $data['basic_salary'],
            'allowance' => $data['allowance'] ?? 0,
            'overtime' => 0, // Will be calculated from overtime records
            'bonus' => 0, // Will be calculated from attendance
            'deduction' => $data['deduction'] ?? 0,
            'tax_amount' => 0, // Will be calculated later
            'bpjs_amount' => 0, // Will be calculated later
            'total_salary' => $totalSalary,
            'status' => 'draft',
            'notes' => $data['notes'] ?? null,
            'generated_by' => $user->id,
            'generated_at' => now(),
        ];

        $payroll = $this->payrollRepository->create($payload);

        Log::info('Payroll generated', [
            'payroll_id' => $payroll->id,
            'employee_id' => $employee->id,
            'period' => $data['period'],
            'created_by' => $user->id,
        ]);

        return [
            'success' => true,
            'message' => 'Payroll berhasil di generate untuk ' . $employee->name,
            'data' => $payroll,
        ];
    }

    /**
     * Get paginated payrolls with filters
     */
    public function getPaginatedPayrolls(string $period = null, string $status = null, int $perPage = 15): LengthAwarePaginator
    {
        return $this->payrollRepository->getPaginatedWithFilters($period, $status, $perPage);
    }

    /**
     * Get payroll summary statistics
     */
    public function getPayrollSummary(string $period = null, string $status = null): array
    {
        return $this->payrollRepository->getSummaryStatistics($period, $status);
    }

    /**
     * Get active employees for current company
     */
    public function getActiveEmployees()
    {
        return $this->employeeRepository->getActiveEmployees();
    }

    /**
     * Get payrolls for DataTables
     */
    public function getPayrollsForDataTables($request = null)
    {
        return $this->payrollRepository->getForDataTables($request);
    }

    /**
     * Find payroll by ID with relations
     */
    public function findPayroll(string $id)
    {
        return $this->payrollRepository->findByIdWithRelations($id);
    }

    /**
     * Update payroll
     */
    public function updatePayroll(string $id, array $data): array
    {
        $user = Auth::user();
        
        // Check if user has permission
        if (!in_array($user->role, ['admin', 'hr'])) {
            return [
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk mengedit payroll.'
            ];
        }

        $payroll = $this->payrollRepository->findById($id);
        
        if (!$payroll) {
            return [
                'success' => false,
                'message' => 'Payroll tidak ditemukan.'
            ];
        }

        // Calculate total salary
        $basicSalary = $data['basic_salary'] ?? $payroll->basic_salary;
        $allowance = $data['allowance'] ?? $payroll->allowance ?? 0;
        $deduction = $data['deduction'] ?? $payroll->deduction ?? 0;

        $totalSalary = $basicSalary + $allowance - $deduction;

        // Add total salary to data
        $data['total_salary'] = $totalSalary;

        $result = $this->payrollRepository->update($payroll, $data);

        if ($result) {
            return [
                'success' => true,
                'message' => 'Payroll berhasil diperbarui.'
            ];
        }

        return [
            'success' => false,
            'message' => 'Gagal memperbarui payroll.'
        ];
    }

    /**
     * Delete payroll
     */
    public function deletePayroll(string $id): array
    {
        $user = Auth::user();
        
        // Check if user has permission
        if (!in_array($user->role, ['admin', 'hr'])) {
            return [
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk menghapus payroll.'
            ];
        }

        $payroll = $this->payrollRepository->findById($id);
        
        if (!$payroll) {
            return [
                'success' => false,
                'message' => 'Payroll tidak ditemukan.'
            ];
        }

        $result = $this->payrollRepository->delete($payroll);

        if ($result) {
            return [
                'success' => true,
                'message' => 'Payroll berhasil dihapus.'
            ];
        }

        return [
            'success' => false,
            'message' => 'Gagal menghapus payroll.'
        ];
    }
}


