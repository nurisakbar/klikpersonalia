<?php

namespace App\Http\Controllers;

use App\DataTables\EmployeeDataTable;
use App\Models\Employee;
use App\Http\Requests\EmployeeRequest;
use App\Http\Resources\EmployeeResource;
use App\Services\EmployeeService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Facades\DataTables;

class EmployeeController extends Controller
{
    public function __construct(
        private EmployeeService $employeeService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(EmployeeDataTable $dataTable)
    {
        return $dataTable->render('employees.index');
    }

    /**
     * Get employees data for DataTables.
     */
    public function data(): JsonResponse
    {
        $employees = $this->employeeService->getEmployeesForDataTables();

        return DataTables::of($employees)
            ->addColumn('action', function ($employee) {
                return '
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-info view-btn" data-id="' . $employee->id . '" title="Detail">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-warning edit-btn" data-id="' . $employee->id . '" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-danger delete-btn" data-id="' . $employee->id . '" data-name="' . htmlspecialchars($employee->name) . '" title="Hapus">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                ';
            })
            ->addColumn('status_badge', function ($employee) {
                $statusClass = [
                    'active' => 'badge badge-success',
                    'inactive' => 'badge badge-warning',
                    'terminated' => 'badge badge-danger'
                ];
                
                $statusText = [
                    'active' => 'Aktif',
                    'inactive' => 'Tidak Aktif',
                    'terminated' => 'Berhenti'
                ];
                
                return '<span class="' . $statusClass[$employee->status] . '">' . $statusText[$employee->status] . '</span>';
            })
            ->addColumn('salary_formatted', function ($employee) {
                return 'Rp ' . number_format($employee->basic_salary, 0, ',', '.');
            })
            ->addColumn('join_date_formatted', function ($employee) {
                return date('d/m/Y', strtotime($employee->join_date));
            })
            ->rawColumns(['action', 'status_badge', 'salary_formatted', 'join_date_formatted'])
            ->make(true);
    }

    /**
     * Search employees for AJAX select (name/email/employee_id)
     */
    public function search(Request $request): JsonResponse
    {
        $query = trim((string)$request->get('q', ''));
        $results = $query === ''
            ? $this->employeeService->getDefaultEmployeesForSelect(20)
            : $this->employeeService->searchEmployees($query)->take(20);

        return response()->json([
            'success' => true,
            'data' => $results->map(function ($emp) {
                return [
                    'id' => $emp->id,
                    'text' => $emp->employee_id . ' - ' . $emp->name . ' (' . $emp->department . ')',
                    'basic_salary' => $emp->basic_salary,
                ];
            }),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $formData = $this->employeeService->getFormData();
        
        return view('employees.create', [
            'departments' => $formData['departments'],
            'positions' => $formData['positions'],
            'banks' => $formData['banks']
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(EmployeeRequest $request)
    {
        $result = $this->employeeService->createEmployee($request->validated());

        if ($request->expectsJson()) {
            return response()->json($result);
        }

        if ($result['success']) {
            return redirect()->route('employees.index')->with('success', $result['message']);
        } else {
            return redirect()->back()->withInput()->with('error', $result['message']);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $employee = $this->employeeService->findEmployee($id);
        
        if (!$employee) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Karyawan tidak ditemukan.'
                ], 404);
            }
            
            return redirect()->route('employees.index')->with('error', 'Karyawan tidak ditemukan.');
        }

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => new EmployeeResource($employee)
            ]);
        }

        return view('employees.show', compact('employee'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $employee = $this->employeeService->findEmployee($id);
        
        if (!$employee) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Karyawan tidak ditemukan.'
                ], 404);
            }
            
            return redirect()->route('employees.index')->with('error', 'Karyawan tidak ditemukan.');
        }

        $formData = $this->employeeService->getFormData();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'employee' => new EmployeeResource($employee),
                    'form_options' => $formData
                ]
            ]);
        }

        return view('employees.edit', [
            'employee' => $employee,
            'departments' => $formData['departments'],
            'positions' => $formData['positions'],
            'statuses' => $formData['statuses'],
            'banks' => $formData['banks']
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(EmployeeRequest $request, string $id)
    {
        $result = $this->employeeService->updateEmployee($id, $request->validated());

        if ($request->expectsJson()) {
            return response()->json($result);
        }

        if ($result['success']) {
            return redirect()->route('employees.index')->with('success', $result['message']);
        } else {
            return redirect()->back()->withInput()->with('error', $result['message']);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $result = $this->employeeService->deleteEmployee($id);
        
        $statusCode = $result['success'] ? 200 : 422;
        return response()->json($result, $statusCode);
    }
}
