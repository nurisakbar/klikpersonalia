<?php

namespace App\Http\Controllers;

use App\Services\PositionService;
use App\Http\Requests\PositionRequest;
use App\Http\Resources\PositionResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Facades\DataTables;

class PositionController extends Controller
{
    public function __construct(
        private PositionService $positionService
    ) {}

    public function index()
    {
        return view('positions.index');
    }

    public function data()
    {
        $positions = $this->positionService->getAllPositions();

        return DataTables::of($positions)
            ->addColumn('action', function ($position) {
                return '<div class="btn-group" role="group">
                    <a href="' . route('positions.show', $position->id) . '" class="btn btn-sm btn-info" title="View">
                        <i class="fas fa-eye"></i>
                    </a>
                    <a href="' . route('positions.edit', $position->id) . '" class="btn btn-sm btn-warning" title="Edit">
                        <i class="fas fa-edit"></i>
                    </a>
                    <button type="button" class="btn btn-sm btn-danger delete-btn" 
                            data-id="' . $position->id . '" 
                            data-name="' . $position->name . '"
                            title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>';
            })
            ->addColumn('status', function ($position) {
                return $position->status 
                    ? '<span class="badge badge-success">Aktif</span>'
                    : '<span class="badge badge-danger">Tidak Aktif</span>';
            })
            ->addColumn('employee_count', function ($position) {
                return $position->employees()->count();
            })
            ->rawColumns(['action', 'status'])
            ->make(true);
    }

    public function create()
    {
        return view('positions.create');
    }

    public function store(PositionRequest $request)
    {
        try {
            $result = $this->positionService->createPosition($request->validated());

            if ($request->ajax()) {
                return response()->json($result);
            }

            return redirect()->route('positions.index')
                ->with('success', $result['message']);

        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function show($id)
    {
        try {
            $position = $this->positionService->getPositionById($id);
            $position->load('employees');
            
            return view('positions.show', compact('position'));
        } catch (\Exception $e) {
            return redirect()->route('positions.index')
                ->with('error', 'Jabatan tidak ditemukan');
        }
    }

    public function edit($id)
    {
        try {
            $position = $this->positionService->getPositionById($id);
            return view('positions.edit', compact('position'));
        } catch (\Exception $e) {
            return redirect()->route('positions.index')
                ->with('error', 'Jabatan tidak ditemukan');
        }
    }

    public function update(PositionRequest $request, $id)
    {
        try {
            $result = $this->positionService->updatePosition($id, $request->validated());

            if ($request->ajax()) {
                return response()->json($result);
            }

            return redirect()->route('positions.index')
                ->with('success', $result['message']);

        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        try {
            $result = $this->positionService->deletePosition($id);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
}
