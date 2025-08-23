<?php

namespace App\Policies;

use App\Models\SalaryComponent;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SalaryComponentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view salary components') || 
               $user->hasRole(['admin', 'hr_manager', 'payroll_manager']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SalaryComponent $salaryComponent): bool
    {
        // Check if user belongs to the same company
        if ($user->company_id !== $salaryComponent->company_id) {
            return false;
        }

        return $user->hasPermissionTo('view salary components') || 
               $user->hasRole(['admin', 'hr_manager', 'payroll_manager']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create salary components') || 
               $user->hasRole(['admin', 'hr_manager']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SalaryComponent $salaryComponent): bool
    {
        // Check if user belongs to the same company
        if ($user->company_id !== $salaryComponent->company_id) {
            return false;
        }

        return $user->hasPermissionTo('update salary components') || 
               $user->hasRole(['admin', 'hr_manager']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SalaryComponent $salaryComponent): bool
    {
        // Check if user belongs to the same company
        if ($user->company_id !== $salaryComponent->company_id) {
            return false;
        }

        // Check if component is being used in payrolls
        if ($salaryComponent->isUsedInPayrolls()) {
            return false;
        }

        return $user->hasPermissionTo('delete salary components') || 
               $user->hasRole(['admin', 'hr_manager']);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, SalaryComponent $salaryComponent): bool
    {
        // Check if user belongs to the same company
        if ($user->company_id !== $salaryComponent->company_id) {
            return false;
        }

        return $user->hasPermissionTo('restore salary components') || 
               $user->hasRole(['admin']);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, SalaryComponent $salaryComponent): bool
    {
        // Check if user belongs to the same company
        if ($user->company_id !== $salaryComponent->company_id) {
            return false;
        }

        return $user->hasPermissionTo('force delete salary components') || 
               $user->hasRole(['admin']);
    }

    /**
     * Determine whether the user can toggle the status of the component.
     */
    public function toggleStatus(User $user, SalaryComponent $salaryComponent): bool
    {
        // Check if user belongs to the same company
        if ($user->company_id !== $salaryComponent->company_id) {
            return false;
        }

        return $user->hasPermissionTo('update salary components') || 
               $user->hasRole(['admin', 'hr_manager']);
    }

    /**
     * Determine whether the user can update the sort order of components.
     */
    public function updateSortOrder(User $user): bool
    {
        return $user->hasPermissionTo('update salary components') || 
               $user->hasRole(['admin', 'hr_manager']);
    }

    /**
     * Determine whether the user can bulk update components.
     */
    public function bulkUpdate(User $user): bool
    {
        return $user->hasPermissionTo('update salary components') || 
               $user->hasRole(['admin', 'hr_manager']);
    }

    /**
     * Determine whether the user can bulk delete components.
     */
    public function bulkDelete(User $user): bool
    {
        return $user->hasPermissionTo('delete salary components') || 
               $user->hasRole(['admin', 'hr_manager']);
    }
}
