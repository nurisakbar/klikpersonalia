<?php

namespace App\Repositories;

use App\Models\Department;
use Illuminate\Support\Facades\Auth;

class DepartmentRepository
{
    protected $model;

    public function __construct(Department $model)
    {
        $this->model = $model;
    }

    public function getAllByCompany($companyId = null)
    {
        $companyId = $companyId ?? Auth::user()->company_id;
        return $this->model->byCompany($companyId);
    }

    public function findById($id)
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data)
    {
        $data['company_id'] = Auth::user()->company_id;
        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $department = $this->findById($id);
        $department->update($data);
        return $department;
    }

    public function delete($id)
    {
        $department = $this->findById($id);
        return $department->delete();
    }

    public function hasEmployees($id)
    {
        $department = $this->findById($id);
        return $department->employees()->count() > 0;
    }

    public function getActiveDepartments($companyId = null)
    {
        $companyId = $companyId ?? Auth::user()->company_id;
        return $this->model->byCompany($companyId)->active()->get();
    }


}
