<?php

namespace App\Services;

use App\Models\Bpjs;
use App\Models\Employee;
use App\Repositories\BpjsRepository;
use App\Http\Resources\BpjsResource;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class BpjsService
{
    public function __construct(
        private BpjsRepository $bpjsRepository
    ) {}

    /**
     * Get all BPJS records for current company.
     */
    public function getAllBpjsRecords(): Collection
    {
        return $this->bpjsRepository->getAllForCurrentCompany();
    }

    /**
     * Get BPJS records with pagination.
     */
    public function getPaginatedBpjsRecords(int $perPage = 15): LengthAwarePaginator
    {
        return $this->bpjsRepository->getPaginated($perPage);
    }

    /**
     * Find BPJS record by ID.
     */
    public function findBpjsRecord(string $id): ?Bpjs
    {
        return $this->bpjsRepository->findById($id);
    }

    /**
     * Create new BPJS record.
     */
    public function createBpjsRecord(array $data): array
    {
        try {
            DB::beginTransaction();

            // Check if BPJS record already exists
            if ($this->bpjsRepository->existsForEmployeePeriodType(
                $data['employee_id'], 
                $data['bpjs_period'], 
                $data['bpjs_type']
            )) {
                return [
                    'success' => false,
                    'message' => 'Data BPJS sudah ada untuk karyawan, periode, dan jenis ini.',
                    'data' => null
                ];
            }

            $employee = Employee::findOrFail($data['employee_id']);

            // Calculate BPJS contribution
            if ($data['bpjs_type'] === 'kesehatan') {
                $calculation = Bpjs::calculateKesehatan($employee, $data['base_salary'], $data['bpjs_period']);
            } else {
                $calculation = Bpjs::calculateKetenagakerjaan($employee, $data['base_salary'], $data['bpjs_period']);
            }

            $bpjsData = array_merge($data, [
                'employee_contribution' => $calculation['employee_contribution'],
                'company_contribution' => $calculation['company_contribution'],
                'total_contribution' => $calculation['total_contribution'],
                'base_salary' => $calculation['base_salary'],
                'contribution_rate_employee' => $calculation['contribution_rate_employee'],
                'contribution_rate_company' => $calculation['contribution_rate_company'],
                'status' => Bpjs::STATUS_CALCULATED,
            ]);

            $bpjs = $this->bpjsRepository->create($bpjsData);

            DB::commit();

            Log::info('BPJS record created successfully', [
                'bpjs_id' => $bpjs->id,
                'employee_id' => $bpjs->employee_id,
                'period' => $bpjs->bpjs_period,
                'type' => $bpjs->bpjs_type,
                'created_by' => auth()->id()
            ]);

            return [
                'success' => true,
                'message' => 'Data BPJS berhasil dibuat.',
                'data' => $bpjs
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to create BPJS record', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);

            return [
                'success' => false,
                'message' => 'Gagal membuat data BPJS: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Update BPJS record.
     */
    public function updateBpjsRecord(Bpjs $bpjs, array $data): array
    {
        try {
            DB::beginTransaction();

            $employee = Employee::findOrFail($data['employee_id']);

            // Calculate BPJS contribution
            if ($data['bpjs_type'] === 'kesehatan') {
                $calculation = Bpjs::calculateKesehatan($employee, $data['base_salary'], $data['bpjs_period']);
            } else {
                $calculation = Bpjs::calculateKetenagakerjaan($employee, $data['base_salary'], $data['bpjs_period']);
            }

            $updateData = array_merge($data, [
                'employee_contribution' => $calculation['employee_contribution'],
                'company_contribution' => $calculation['company_contribution'],
                'total_contribution' => $calculation['total_contribution'],
                'base_salary' => $calculation['base_salary'],
                'contribution_rate_employee' => $calculation['contribution_rate_employee'],
                'contribution_rate_company' => $calculation['contribution_rate_company'],
            ]);

            $this->bpjsRepository->update($bpjs, $updateData);

            DB::commit();

            Log::info('BPJS record updated successfully', [
                'bpjs_id' => $bpjs->id,
                'updated_by' => auth()->id()
            ]);

            return [
                'success' => true,
                'message' => 'Data BPJS berhasil diperbarui.',
                'data' => $bpjs->fresh()
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to update BPJS record', [
                'bpjs_id' => $bpjs->id,
                'error' => $e->getMessage(),
                'data' => $data
            ]);

            return [
                'success' => false,
                'message' => 'Gagal memperbarui data BPJS: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Delete BPJS record.
     */
    public function deleteBpjsRecord(Bpjs $bpjs): array
    {
        try {
            $this->bpjsRepository->delete($bpjs);

            Log::info('BPJS record deleted successfully', [
                'bpjs_id' => $bpjs->id,
                'deleted_by' => auth()->id()
            ]);

            return [
                'success' => true,
                'message' => 'Data BPJS berhasil dihapus.'
            ];

        } catch (Exception $e) {
            Log::error('Failed to delete BPJS record', [
                'bpjs_id' => $bpjs->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Gagal menghapus data BPJS: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get BPJS records with filters.
     */
    public function getBpjsRecordsWithFilters(array $filters): Collection
    {
        return $this->bpjsRepository->getWithFilters($filters);
    }

    /**
     * Get BPJS periods.
     */
    public function getBpjsPeriods(): \Illuminate\Support\Collection
    {
        return $this->bpjsRepository->getPeriods();
    }

    /**
     * Get BPJS summary statistics.
     */
    public function getBpjsSummary(string $period = null): array
    {
        return $this->bpjsRepository->getSummary($period);
    }

    /**
     * Calculate BPJS for all employees.
     */
    public function calculateBpjsForAllEmployees(string $period, string $type): array
    {
        try {
            $result = $this->bpjsRepository->calculateForAllEmployees($period, $type);

            if ($result['success']) {
                Log::info('BPJS calculation completed successfully', [
                    'period' => $period,
                    'type' => $type,
                    'created_count' => $result['created_count'],
                    'errors' => $result['errors'],
                    'calculated_by' => auth()->id()
                ]);
            }

            return $result;

        } catch (Exception $e) {
            Log::error('Failed to calculate BPJS for all employees', [
                'period' => $period,
                'type' => $type,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to calculate BPJS: ' . $e->getMessage(),
                'created_count' => 0,
                'errors' => [$e->getMessage()]
            ];
        }
    }

    /**
     * Get BPJS records by period.
     */
    public function getBpjsRecordsByPeriod(string $period): Collection
    {
        return $this->bpjsRepository->getByPeriod($period);
    }

    /**
     * Get BPJS records by type.
     */
    public function getBpjsRecordsByType(string $type): Collection
    {
        return $this->bpjsRepository->getByType($type);
    }

    /**
     * Get BPJS records by status.
     */
    public function getBpjsRecordsByStatus(string $status): Collection
    {
        return $this->bpjsRepository->getByStatus($status);
    }

    /**
     * Get BPJS records by employee.
     */
    public function getBpjsRecordsByEmployee(string $employeeId): Collection
    {
        return $this->bpjsRepository->getByEmployee($employeeId);
    }
}
