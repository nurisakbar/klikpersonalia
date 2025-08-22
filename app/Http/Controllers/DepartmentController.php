<?php

namespace App\Http\Controllers;

use App\DataTables\DepartmentDataTable;
use App\Services\DepartmentService;
use App\Http\Requests\DepartmentRequest;
use App\Http\Resources\DepartmentResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Facades\DataTables;

class DepartmentController extends Controller
{
    public function __construct(
        private DepartmentService $departmentService
    ) {}

    public function index(DepartmentDataTable $dataTable)
    {
        return $dataTable->render('departments.index');
    }

    public function data()
    {
        $departments = $this->departmentService->getAllDepartments();

        return DataTables::of($departments)
            ->addColumn('action', function ($department) {
                return '<div class="btn-group" role="group">
                    <a href="' . route('departments.show', $department->id) . '" class="btn btn-sm btn-info" title="View">
                        <i class="fas fa-eye"></i>
                    </a>
                    <a href="' . route('departments.edit', $department->id) . '" class="btn btn-sm btn-warning" title="Edit">
                        <i class="fas fa-edit"></i>
                    </a>
                    <button type="button" class="btn btn-sm btn-danger delete-btn" 
                            data-id="' . $department->id . '" 
                            data-name="' . $department->name . '"
                            title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>';
            })
            ->addColumn('status', function ($department) {
                return $department->status 
                    ? '<span class="badge badge-success">Aktif</span>'
                    : '<span class="badge badge-danger">Tidak Aktif</span>';
            })
            ->addColumn('employee_count', function ($department) {
                return $department->employees()->count();
            })
            ->rawColumns(['action', 'status'])
            ->make(true);
    }

    public function create()
    {
        return view('departments.create');
    }

    public function store(DepartmentRequest $request)
    {
        try {
            $result = $this->departmentService->createDepartment($request->validated());

            if ($request->ajax()) {
                return response()->json($result);
            }

            return redirect()->route('departments.index')
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
            $department = $this->departmentService->getDepartmentById($id);
            $department->load('employees');
            
            return view('departments.show', compact('department'));
        } catch (\Exception $e) {
            return redirect()->route('departments.index')
                ->with('error', 'Department tidak ditemukan');
        }
    }

    public function edit($id)
    {
        try {
            $department = $this->departmentService->getDepartmentById($id);
            return view('departments.edit', compact('department'));
        } catch (\Exception $e) {
            return redirect()->route('departments.index')
                ->with('error', 'Department tidak ditemukan');
        }
    }

    public function update(DepartmentRequest $request, $id)
    {
        try {
            $result = $this->departmentService->updateDepartment($id, $request->validated());

            if ($request->ajax()) {
                return response()->json($result);
            }

            return redirect()->route('departments.index')
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
            $result = $this->departmentService->deleteDepartment($id);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
}
