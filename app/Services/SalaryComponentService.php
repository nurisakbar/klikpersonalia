<?php

namespace App\Services;

use App\Models\SalaryComponent;
use App\Repositories\SalaryComponentRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SalaryComponentService
{
    protected $repository;

    public function __construct(SalaryComponentRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get paginated salary components.
     */
    public function getPaginatedComponents(string $companyId, int $perPage = 10): LengthAwarePaginator
    {
        return $this->repository->getPaginatedComponents($companyId, $perPage);
    }

    /**
     * Get all active salary components.
     */
    public function getActiveComponents(string $companyId): Collection
    {
        return $this->repository->getActiveComponents($companyId);
    }

    /**
     * Get earning components.
     */
    public function getEarningComponents(string $companyId): Collection
    {
        return $this->repository->getEarningComponents($companyId);
    }

    /**
     * Get deduction components.
     */
    public function getDeductionComponents(string $companyId): Collection
    {
        return $this->repository->getDeductionComponents($companyId);
    }

    /**
     * Get taxable components.
     */
    public function getTaxableComponents(string $companyId): Collection
    {
        return $this->repository->getTaxableComponents($companyId);
    }

    /**
     * Get BPJS calculated components.
     */
    public function getBpjsCalculatedComponents(string $companyId): Collection
    {
        return $this->repository->getBpjsCalculatedComponents($companyId);
    }

    /**
     * Create a new salary component.
     */
    public function createComponent(array $data): SalaryComponent
    {
        try {
            DB::beginTransaction();

            // Set default sort order if not provided
            if (!isset($data['sort_order'])) {
                $data['sort_order'] = $this->getNextSortOrder($data['company_id']);
            }

            $component = $this->repository->create($data);

            DB::commit();
            Log::info('Salary component created successfully', ['component_id' => $component->id]);

            return $component;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create salary component', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Update a salary component.
     */
    public function updateComponent(SalaryComponent $component, array $data): bool
    {
        try {
            DB::beginTransaction();

            $updated = $this->repository->update($component, $data);

            DB::commit();
            Log::info('Salary component updated successfully', ['component_id' => $component->id]);

            return $updated;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update salary component', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Delete a salary component.
     */
    public function deleteComponent(SalaryComponent $component): bool
    {
        try {
            DB::beginTransaction();

            // Check if component can be deleted
            if ($this->isComponentInUse($component)) {
                throw new \Exception('Komponen gaji tidak dapat dihapus karena masih digunakan.');
            }

            $deleted = $this->repository->delete($component);

            DB::commit();
            Log::info('Salary component deleted successfully', ['component_id' => $component->id]);

            return $deleted;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete salary component', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Toggle component status.
     */
    public function toggleComponentStatus(SalaryComponent $component): bool
    {
        try {
            $toggled = $this->repository->toggleStatus($component);

            Log::info('Salary component status toggled', [
                'component_id' => $component->id,
                'new_status' => $component->is_active
            ]);

            return $toggled;
        } catch (\Exception $e) {
            Log::error('Failed to toggle salary component status', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Update components sort order.
     */
    public function updateComponentsSortOrder(array $components, string $companyId): bool
    {
        try {
            DB::beginTransaction();

            $updated = $this->repository->updateSortOrder($components, $companyId);

            DB::commit();
            Log::info('Salary components sort order updated', ['company_id' => $companyId]);

            return $updated;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update salary components sort order', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Get components by type and status.
     */
    public function getComponentsByTypeAndStatus(string $companyId, string $type, bool $isActive = true): Collection
    {
        return $this->repository->getComponentsByTypeAndStatus($companyId, $type, $isActive);
    }

    /**
     * Search components by name.
     */
    public function searchComponentsByName(string $companyId, string $searchTerm): Collection
    {
        return $this->repository->searchByName($companyId, $searchTerm);
    }

    /**
     * Get next sort order for a company.
     */
    protected function getNextSortOrder(string $companyId): int
    {
        $maxOrder = SalaryComponent::where('company_id', $companyId)
            ->max('sort_order');

        return ($maxOrder ?? 0) + 1;
    }

    /**
     * Check if component is being used in payrolls.
     */
    protected function isComponentInUse(SalaryComponent $component): bool
    {
        // This method should be implemented based on your payroll structure
        // For now, we'll return false as a placeholder
        return false;
    }

    /**
     * Get component statistics for a company.
     */
    public function getComponentStatistics(string $companyId): array
    {
        $totalComponents = SalaryComponent::where('company_id', $companyId)->count();
        $activeComponents = SalaryComponent::where('company_id', $companyId)
            ->where('is_active', true)
            ->count();
        $earningComponents = SalaryComponent::where('company_id', $companyId)
            ->where('type', 'earning')
            ->where('is_active', true)
            ->count();
        $deductionComponents = SalaryComponent::where('company_id', $companyId)
            ->where('type', 'deduction')
            ->where('is_active', true)
            ->count();

        return [
            'total' => $totalComponents,
            'active' => $activeComponents,
            'inactive' => $totalComponents - $activeComponents,
            'earnings' => $earningComponents,
            'deductions' => $deductionComponents
        ];
    }
}
