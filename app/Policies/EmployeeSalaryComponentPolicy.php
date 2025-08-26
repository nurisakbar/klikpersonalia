<?php

namespace App\Policies;

use App\Models\User;
use App\Models\EmployeeSalaryComponent;
use Illuminate\Auth\Access\HandlesAuthorization;

class EmployeeSalaryComponentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view employee salary components');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, EmployeeSalaryComponent $employeeSalaryComponent): bool
    {
        return $user->company_id === $employeeSalaryComponent->company_id &&
               $user->hasPermissionTo('view employee salary components');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create employee salary components');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, EmployeeSalaryComponent $employeeSalaryComponent): bool
    {
        return $user->company_id === $employeeSalaryComponent->company_id &&
               $user->hasPermissionTo('update employee salary components');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, EmployeeSalaryComponent $employeeSalaryComponent): bool
    {
        return $user->company_id === $employeeSalaryComponent->company_id &&
               $user->hasPermissionTo('delete employee salary components');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, EmployeeSalaryComponent $employeeSalaryComponent): bool
    {
        return $user->company_id === $employeeSalaryComponent->company_id &&
               $user->hasPermissionTo('restore employee salary components');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, EmployeeSalaryComponent $employeeSalaryComponent): bool
    {
        return $user->company_id === $employeeSalaryComponent->company_id &&
               $user->hasPermissionTo('force delete employee salary components');
    }

    /**
     * Determine whether the user can toggle status of the model.
     */
    public function toggleStatus(User $user, EmployeeSalaryComponent $employeeSalaryComponent): bool
    {
        return $user->company_id === $employeeSalaryComponent->company_id &&
               $user->hasPermissionTo('update employee salary components');
    }

    /**
     * Determine whether the user can bulk assign components.
     */
    public function bulkAssign(User $user): bool
    {
        return $user->hasPermissionTo('create employee salary components');
    }
}
