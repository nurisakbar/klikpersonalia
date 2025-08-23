<?php

namespace App\Repositories;

use App\Models\SalaryComponent;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class SalaryComponentRepository
{
    protected $model;

    public function __construct(SalaryComponent $model)
    {
        $this->model = $model;
    }

    /**
     * Get all salary components for a company with pagination.
     */
    public function getPaginatedComponents(string $companyId, int $perPage = 10): LengthAwarePaginator
    {
        return $this->model
            ->where('company_id', $companyId)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Get all active salary components for a company.
     */
    public function getActiveComponents(string $companyId): Collection
    {
        return $this->model
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get earning components for a company.
     */
    public function getEarningComponents(string $companyId): Collection
    {
        return $this->model
            ->where('company_id', $companyId)
            ->where('type', 'earning')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get deduction components for a company.
     */
    public function getDeductionComponents(string $companyId): Collection
    {
        return $this->model
            ->where('company_id', $companyId)
            ->where('type', 'deduction')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get taxable components for a company.
     */
    public function getTaxableComponents(string $companyId): Collection
    {
        return $this->model
            ->where('company_id', $companyId)
            ->where('is_taxable', true)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get BPJS calculated components for a company.
     */
    public function getBpjsCalculatedComponents(string $companyId): Collection
    {
        return $this->model
            ->where('company_id', $companyId)
            ->where('is_bpjs_calculated', true)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /**
     * Find a salary component by ID and company.
     */
    public function findByIdAndCompany(string $id, string $companyId): ?SalaryComponent
    {
        return $this->model
            ->where('id', $id)
            ->where('company_id', $companyId)
            ->first();
    }

    /**
     * Create a new salary component.
     */
    public function create(array $data): SalaryComponent
    {
        return $this->model->create($data);
    }

    /**
     * Update a salary component.
     */
    public function update(SalaryComponent $component, array $data): bool
    {
        return $component->update($data);
    }

    /**
     * Delete a salary component.
     */
    public function delete(SalaryComponent $component): bool
    {
        return $component->delete();
    }

    /**
     * Toggle active status of a component.
     */
    public function toggleStatus(SalaryComponent $component): bool
    {
        return $component->update([
            'is_active' => !$component->is_active
        ]);
    }

    /**
     * Update sort order of components.
     */
    public function updateSortOrder(array $components, string $companyId): bool
    {
        foreach ($components as $component) {
            $this->model
                ->where('id', $component['id'])
                ->where('company_id', $companyId)
                ->update(['sort_order' => $component['sort_order']]);
        }

        return true;
    }

    /**
     * Get components by type and status.
     */
    public function getComponentsByTypeAndStatus(string $companyId, string $type, bool $isActive = true): Collection
    {
        return $this->model
            ->where('company_id', $companyId)
            ->where('type', $type)
            ->where('is_active', $isActive)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /**
     * Search components by name.
     */
    public function searchByName(string $companyId, string $searchTerm): Collection
    {
        return $this->model
            ->where('company_id', $companyId)
            ->where('name', 'like', "%{$searchTerm}%")
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }
}
