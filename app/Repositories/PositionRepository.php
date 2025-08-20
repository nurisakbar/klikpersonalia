<?php

namespace App\Repositories;

use App\Models\Position;
use Illuminate\Support\Facades\Auth;

class PositionRepository
{
    protected $model;

    public function __construct(Position $model)
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
        $position = $this->findById($id);
        $position->update($data);
        return $position;
    }

    public function delete($id)
    {
        $position = $this->findById($id);
        return $position->delete();
    }

    public function hasEmployees($id)
    {
        $position = $this->findById($id);
        return $position->employees()->count() > 0;
    }

    public function getActivePositions($companyId = null)
    {
        $companyId = $companyId ?? Auth::user()->company_id;
        return $this->model->byCompany($companyId)->active()->get();
    }


}
