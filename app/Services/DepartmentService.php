<?php

namespace App\Services;

use App\Repositories\DepartmentRepository;
use App\Http\Resources\DepartmentResource;
use Illuminate\Support\Facades\DB;
use Exception;

class DepartmentService
{
    protected $departmentRepository;

    public function __construct(DepartmentRepository $departmentRepository)
    {
        $this->departmentRepository = $departmentRepository;
    }

    public function getAllDepartments()
    {
        return $this->departmentRepository->getAllByCompany();
    }

    public function getDepartmentById($id)
    {
        return $this->departmentRepository->findById($id);
    }

    public function createDepartment(array $data)
    {
        try {
            DB::beginTransaction();

            $department = $this->departmentRepository->create($data);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Department berhasil ditambahkan!',
                'data' => new DepartmentResource($department)
            ];

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateDepartment($id, array $data)
    {
        try {
            DB::beginTransaction();

            $department = $this->departmentRepository->update($id, $data);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Department berhasil diperbarui!',
                'data' => new DepartmentResource($department)
            ];

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteDepartment($id)
    {
        try {
            DB::beginTransaction();

            $this->departmentRepository->delete($id);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Department berhasil dihapus!'
            ];

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getActiveDepartments()
    {
        return $this->departmentRepository->getActiveDepartments();
    }

    public function getDepartmentForSelect()
    {
        $departments = $this->getActiveDepartments();
        return $departments->map(function ($department) {
            return [
                'id' => $department->id,
                'text' => $department->name
            ];
        });
    }
}
