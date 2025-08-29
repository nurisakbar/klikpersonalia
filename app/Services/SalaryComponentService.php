<?php

namespace App\Services;

use App\Models\SalaryComponent;
use App\Repositories\SalaryComponentRepository;
use App\Http\Resources\SalaryComponentResource;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class SalaryComponentService
{
    public function __construct(
        private SalaryComponentRepository $salaryComponentRepository
    ) {}

    /**
     * Get all salary components for current company.
     */
    public function getAllComponents(): Collection
    {
        return $this->salaryComponentRepository->getAllForCurrentCompany();
    }

    /**
     * Get paginated salary components.
     */
    public function getPaginatedComponents(int $perPage = 15): LengthAwarePaginator
    {
        return $this->salaryComponentRepository->getPaginated($perPage);
    }

    /**
     * Get all active salary components.
     */
    public function getActiveComponents(): Collection
    {
        return $this->salaryComponentRepository->getActiveComponents();
    }

    /**
     * Get earning components.
     */
    public function getEarningComponents(): Collection
    {
        return $this->salaryComponentRepository->getEarningComponents();
    }

    /**
     * Get deduction components.
     */
    public function getDeductionComponents(): Collection
    {
        return $this->salaryComponentRepository->getDeductionComponents();
    }

    /**
     * Get taxable components.
     */
    public function getTaxableComponents(): Collection
    {
        return $this->salaryComponentRepository->getTaxableComponents();
    }

    /**
     * Get BPJS calculated components.
     */
    public function getBpjsCalculatedComponents(): Collection
    {
        return $this->salaryComponentRepository->getBpjsCalculatedComponents();
    }

    /**
     * Get component details with usage statistics.
     */
    public function getComponentDetails(string $id): array
    {
        try {
            $component = $this->salaryComponentRepository->findById($id);
            
            if (!$component) {
                return [
                    'success' => false,
                    'message' => 'Komponen gaji tidak ditemukan.',
                    'data' => null,
                    'usage_stats' => []
                ];
            }

            // Get usage statistics
            $usageStats = $this->getComponentUsageStats($component);

            return [
                'success' => true,
                'data' => new SalaryComponentResource($component),
                'usage_stats' => $usageStats
            ];

        } catch (Exception $e) {
            Log::error('Failed to get component details', [
                'component_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return [
                'success' => false,
                'message' => 'Gagal mengambil detail komponen: ' . $e->getMessage(),
                'data' => null,
                'usage_stats' => []
            ];
        }
    }

    /**
     * Get component usage statistics.
     */
    protected function getComponentUsageStats(SalaryComponent $component): array
    {
        try {
            // Get employee count using this component
            $employeeCount = $component->employeeComponents()->count();

            // Get total usage in payrolls (placeholder for future implementation)
            $totalUsage = 0;

            // Get last usage period (placeholder for future implementation)
            $lastPeriod = '-';

            // Get average value (placeholder for future implementation)
            $averageValue = 0;

            return [
                'total_usage' => $totalUsage,
                'employee_count' => $employeeCount,
                'last_period' => $lastPeriod,
                'average_value' => $averageValue
            ];

        } catch (Exception $e) {
            Log::error('Failed to get component usage stats', [
                'component_id' => $component->id,
                'error' => $e->getMessage()
            ]);

            return [
                'total_usage' => 0,
                'employee_count' => 0,
                'last_period' => '-',
                'average_value' => 0
            ];
        }
    }

    /**
     * Create a new salary component.
     */
    public function createComponent(array $data): array
    {
        try {
            DB::beginTransaction();

            // Set default sort order if not provided
            if (!isset($data['sort_order'])) {
                $data['sort_order'] = $this->getNextSortOrder();
            }

            $component = $this->salaryComponentRepository->create($data);

            DB::commit();

            Log::info('Salary component created successfully', [
                'component_id' => $component->id,
                'name' => $component->name,
                'created_by' => auth()->id()
            ]);

            return [
                'success' => true,
                'message' => 'Komponen gaji berhasil ditambahkan!',
                'data' => new SalaryComponentResource($component)
            ];

        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to create salary component', [
                'error' => $e->getMessage(),
                'data' => $data,
                'user_id' => auth()->id()
            ]);

            return [
                'success' => false,
                'message' => 'Gagal menambahkan komponen gaji: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Update a salary component.
     */
    public function updateComponent(string $id, array $data): array
    {
        try {
            DB::beginTransaction();

            $component = $this->salaryComponentRepository->findById($id);
            
            if (!$component) {
                return [
                    'success' => false,
                    'message' => 'Komponen gaji tidak ditemukan.',
                    'data' => null
                ];
            }

            $this->salaryComponentRepository->update($component, $data);

            DB::commit();

            Log::info('Salary component updated successfully', [
                'component_id' => $component->id,
                'name' => $component->name,
                'updated_by' => auth()->id()
            ]);

            return [
                'success' => true,
                'message' => 'Komponen gaji berhasil diperbarui!',
                'data' => new SalaryComponentResource($component->fresh())
            ];

        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to update salary component', [
                'component_id' => $id,
                'error' => $e->getMessage(),
                'data' => $data,
                'user_id' => auth()->id()
            ]);

            return [
                'success' => false,
                'message' => 'Gagal memperbarui komponen gaji: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Delete a salary component.
     */
    public function deleteComponent(string $id): array
    {
        try {
            DB::beginTransaction();

            $component = $this->salaryComponentRepository->findById($id);
            
            if (!$component) {
                return [
                    'success' => false,
                    'message' => 'Komponen gaji tidak ditemukan.',
                    'data' => null
                ];
            }

            // Check if component can be deleted
            if ($this->isComponentInUse($component)) {
                return [
                    'success' => false,
                    'message' => 'Komponen gaji tidak dapat dihapus karena masih digunakan dalam penggajian.',
                    'data' => null
                ];
            }

            $componentName = $component->name;
            $this->salaryComponentRepository->delete($component);

            DB::commit();

            Log::info('Salary component deleted successfully', [
                'component_id' => $id,
                'name' => $componentName,
                'deleted_by' => auth()->id()
            ]);

            return [
                'success' => true,
                'message' => "Komponen gaji {$componentName} berhasil dihapus!",
                'data' => null
            ];

        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to delete salary component', [
                'component_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return [
                'success' => false,
                'message' => 'Gagal menghapus komponen gaji: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Toggle component status.
     */
    public function toggleComponentStatus(SalaryComponent $component): bool
    {
        try {
            $toggled = $this->salaryComponentRepository->toggleStatus($component);

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

            $updated = $this->salaryComponentRepository->updateSortOrder($components, $companyId);

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
        return $this->salaryComponentRepository->getComponentsByTypeAndStatus($companyId, $type, $isActive);
    }

    /**
     * Search components by name.
     */
    public function searchComponentsByName(string $companyId, string $searchTerm): Collection
    {
        return $this->salaryComponentRepository->searchByName($companyId, $searchTerm);
    }

    /**
     * Get next sort order for current company.
     */
    protected function getNextSortOrder(): int
    {
        $maxOrder = SalaryComponent::currentCompany()
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
            ->where('type', SalaryComponent::TYPE_EARNING)
            ->where('is_active', true)
            ->count();
        $deductionComponents = SalaryComponent::where('company_id', $companyId)
            ->where('type', SalaryComponent::TYPE_DEDUCTION)
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
