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

        if ($this->payrollRepository->existsForEmployeeAndPeriod($data['employee_id'], $data['period'], $user->company_id)) {
            return [
                'success' => false,
                'message' => 'Payroll already exists for this employee and period.'
            ];
        }

        $employee = Employee::findOrFail($data['employee_id']);

        $totalSalary = ($data['basic_salary'] ?? 0)
            + ($data['allowance'] ?? 0)
            + ($data['overtime'] ?? 0)
            + ($data['bonus'] ?? 0)
            - ($data['deduction'] ?? 0)
            - ($data['tax_amount'] ?? 0)
            - ($data['bpjs_amount'] ?? 0);

        $payload = [
            'employee_id' => $data['employee_id'],
            'company_id' => $user->company_id,
            'period' => $data['period'],
            'basic_salary' => $data['basic_salary'],
            'allowance' => $data['allowance'] ?? 0,
            'overtime' => $data['overtime'] ?? 0,
            'bonus' => $data['bonus'] ?? 0,
            'deduction' => $data['deduction'] ?? 0,
            'tax_amount' => $data['tax_amount'] ?? 0,
            'bpjs_amount' => $data['bpjs_amount'] ?? 0,
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
            'message' => 'Payroll generated successfully for ' . $employee->name,
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
    public function getPayrollSummary(string $period = null): array
    {
        return $this->payrollRepository->getSummaryStatistics($period);
    }

    /**
     * Get active employees for current company
     */
    public function getActiveEmployees()
    {
        return $this->employeeRepository->getActiveEmployees();
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
        $payroll = $this->payrollRepository->findById($id);
        
        if (!$payroll) {
            return [
                'success' => false,
                'message' => 'Payroll not found.'
            ];
        }

        $result = $this->payrollRepository->update($payroll, $data);

        if ($result) {
            return [
                'success' => true,
                'message' => 'Payroll updated successfully.'
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to update payroll.'
        ];
    }

    /**
     * Delete payroll
     */
    public function deletePayroll(string $id): array
    {
        $payroll = $this->payrollRepository->findById($id);
        
        if (!$payroll) {
            return [
                'success' => false,
                'message' => 'Payroll not found.'
            ];
        }

        $result = $this->payrollRepository->delete($payroll);

        if ($result) {
            return [
                'success' => true,
                'message' => 'Payroll deleted successfully.'
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to delete payroll.'
        ];
    }
}


