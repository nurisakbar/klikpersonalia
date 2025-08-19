<?php

namespace App\Services;

use App\Repositories\PositionRepository;
use App\Http\Resources\PositionResource;
use Illuminate\Support\Facades\DB;
use Exception;

class PositionService
{
    protected $positionRepository;

    public function __construct(PositionRepository $positionRepository)
    {
        $this->positionRepository = $positionRepository;
    }

    public function getAllPositions()
    {
        return $this->positionRepository->getAllByCompany();
    }

    public function getPositionById($id)
    {
        return $this->positionRepository->findById($id);
    }

    public function createPosition(array $data)
    {
        try {
            DB::beginTransaction();

            $position = $this->positionRepository->create($data);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Jabatan berhasil ditambahkan!',
                'data' => new PositionResource($position)
            ];

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updatePosition($id, array $data)
    {
        try {
            DB::beginTransaction();

            $position = $this->positionRepository->update($id, $data);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Jabatan berhasil diperbarui!',
                'data' => new PositionResource($position)
            ];

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deletePosition($id)
    {
        try {
            DB::beginTransaction();

            $this->positionRepository->delete($id);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Jabatan berhasil dihapus!'
            ];

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getActivePositions()
    {
        return $this->positionRepository->getActivePositions();
    }

    public function getPositionForSelect()
    {
        $positions = $this->getActivePositions();
        return $positions->map(function ($position) {
            return [
                'id' => $position->id,
                'text' => $position->name
            ];
        });
    }
}
