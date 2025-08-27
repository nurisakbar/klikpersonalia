<?php

namespace App\Repositories;

use App\Models\Bpjs;
use App\Models\Employee;
use App\Models\Payroll;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class BpjsRepository
{
    /**
     * Get all BPJS records for current company.
     */
    public function getAllForCurrentCompany(): Collection
    {
        return Bpjs::with(['employee'])
            ->forCompany(auth()->user()->company_id)
            ->orderBy('bpjs_period', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get BPJS records with pagination.
     */
    public function getPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return Bpjs::with(['employee'])
            ->forCompany(auth()->user()->company_id)
            ->orderBy('bpjs_period', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Find BPJS record by ID.
     */
    public function findById(string $id): ?Bpjs
    {
        return Bpjs::with(['employee', 'payroll'])
            ->forCompany(auth()->user()->company_id)
            ->find($id);
    }

    /**
     * Create new BPJS record.
     */
    public function create(array $data): Bpjs
    {
        // Add company_id to data
        if (!isset($data['company_id'])) {
            $data['company_id'] = auth()->user()->company_id;
        }
        
        return Bpjs::create($data);
    }

    /**
     * Update BPJS record.
     */
    public function update(Bpjs $bpjs, array $data): bool
    {
        return $bpjs->update($data);
    }

    /**
     * Delete BPJS record.
     */
    public function delete(Bpjs $bpjs): bool
    {
        return $bpjs->delete();
    }

    /**
     * Get BPJS records by period.
     */
    public function getByPeriod(string $period): Collection
    {
        return Bpjs::with(['employee'])
            ->forCompany(auth()->user()->company_id)
            ->forPeriod($period)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get BPJS records by type.
     */
    public function getByType(string $type): Collection
    {
        return Bpjs::with(['employee'])
            ->forCompany(auth()->user()->company_id)
            ->forType($type)
            ->orderBy('bpjs_period', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get BPJS records by status.
     */
    public function getByStatus(string $status): Collection
    {
        return Bpjs::with(['employee'])
            ->forCompany(auth()->user()->company_id)
            ->where('status', $status)
            ->orderBy('bpjs_period', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get BPJS records by employee.
     */
    public function getByEmployee(string $employeeId): Collection
    {
        return Bpjs::with(['employee'])
            ->forCompany(auth()->user()->company_id)
            ->where('employee_id', $employeeId)
            ->orderBy('bpjs_period', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get BPJS records with filters.
     */
    public function getWithFilters(array $filters): Collection
    {
        $query = Bpjs::with(['employee'])
            ->forCompany(auth()->user()->company_id);

        if (isset($filters['period'])) {
            $query->forPeriod($filters['period']);
        }

        if (isset($filters['type'])) {
            $query->forType($filters['type']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }

        return $query->orderBy('bpjs_period', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->get();
    }

    /**
     * Get BPJS periods for current company.
     */
    public function getPeriods(): \Illuminate\Support\Collection
    {
        return Bpjs::forCompany(auth()->user()->company_id)
            ->distinct()
            ->pluck('bpjs_period')
            ->sort()
            ->reverse();
    }

    /**
     * Get BPJS summary statistics.
     */
    public function getSummary(string $period = null): array
    {
        $query = Bpjs::forCompany(auth()->user()->company_id);

        if ($period) {
            $query->forPeriod($period);
        }

        $records = $query->get();

        return [
            'total_records' => $records->count(),
            'total_employee_contribution' => $records->sum('employee_contribution'),
            'total_company_contribution' => $records->sum('company_contribution'),
            'total_contribution' => $records->sum('total_contribution'),
            'kesehatan_count' => $records->where('bpjs_type', 'kesehatan')->count(),
            'ketenagakerjaan_count' => $records->where('bpjs_type', 'ketenagakerjaan')->count(),
            'pending_count' => $records->where('status', 'pending')->count(),
            'calculated_count' => $records->where('status', 'calculated')->count(),
            'paid_count' => $records->where('status', 'paid')->count(),
            'verified_count' => $records->where('status', 'verified')->count(),
        ];
    }

    /**
     * Check if BPJS record exists for employee, period, and type.
     */
    public function existsForEmployeePeriodType(string $employeeId, string $period, string $type): bool
    {
        return Bpjs::forCompany(auth()->user()->company_id)
            ->where('employee_id', $employeeId)
            ->where('bpjs_period', $period)
            ->where('bpjs_type', $type)
            ->exists();
    }

    /**
     * Calculate BPJS for all employees in a period.
     */
    public function calculateForAllEmployees(string $period, string $type): array
    {
        $companyId = auth()->user()->company_id;
        $employees = Employee::forCompany($companyId)->get();
        $payrolls = Payroll::forCompany($companyId)
            ->forPeriod($period)
            ->get();

        $createdCount = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($employees as $employee) {
                // Check if employee is active for BPJS
                if ($type === 'kesehatan' && !$employee->bpjs_kesehatan_active) {
                    continue;
                }
                if ($type === 'ketenagakerjaan' && !$employee->bpjs_ketenagakerjaan_active) {
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
                if ($type === 'kesehatan' || $type === 'both') {
                    if ($employee->bpjs_kesehatan_active) {
                        $this->createBpjsRecord($companyId, $employee, $payroll, $baseSalary, 'kesehatan', $period);
                        $createdCount++;
                    }
                }

                if ($type === 'ketenagakerjaan' || $type === 'both') {
                    if ($employee->bpjs_ketenagakerjaan_active) {
                        $this->createBpjsRecord($companyId, $employee, $payroll, $baseSalary, 'ketenagakerjaan', $period);
                        $createdCount++;
                    }
                }
            }

            DB::commit();

            return [
                'success' => true,
                'created_count' => $createdCount,
                'errors' => $errors
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Failed to create BPJS records: ' . $e->getMessage(),
                'created_count' => 0,
                'errors' => [$e->getMessage()]
            ];
        }
    }

    /**
     * Create BPJS record helper method.
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
}
