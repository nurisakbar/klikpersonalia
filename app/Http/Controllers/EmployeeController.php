<?php

namespace App\Http\Controllers;

use App\DataTables\EmployeeDataTable;
use App\Models\Employee;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class EmployeeController extends Controller
{
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
    public function getEmployees()
    {
        $employees = Employee::currentCompany()->select([
            'id',
            'employee_id',
            'name',
            'email',
            'phone',
            'department',
            'position',
            'join_date',
            'basic_salary',
            'status'
        ]);

        return DataTables::of($employees)
            ->addColumn('action', function ($employee) {
                return '
                    <div class="btn-group" role="group">
                        <a href="' . route('employees.show', $employee->id) . '" class="btn btn-sm btn-info" title="Detail">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="' . route('employees.edit', $employee->id) . '" class="btn btn-sm btn-warning" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button type="button" class="btn btn-sm btn-danger delete-btn" data-id="' . $employee->id . '" data-name="' . $employee->name . '" title="Hapus">
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
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $departments = ['IT', 'HR', 'Finance', 'Marketing', 'Sales', 'Operations'];
        $positions = ['Staff', 'Senior Staff', 'Supervisor', 'Manager', 'Senior Manager', 'Director'];
        
        return view('employees.create', compact('departments', 'positions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email',
            'phone' => 'required|string|max:20',
            'department' => 'required|string',
            'position' => 'required|string',
            'join_date' => 'required|date',
            'basic_salary' => 'required|numeric|min:0',
            'address' => 'nullable|string',
            'emergency_contact' => 'nullable|string',
            'bank_name' => 'nullable|string',
            'bank_account' => 'nullable|string',
        ]);

        // Generate employee ID
        $lastEmployee = Employee::orderBy('id', 'desc')->first();
        $lastId = $lastEmployee ? intval(substr($lastEmployee->employee_id, 3)) : 0;
        $newId = $lastId + 1;
        $employeeId = 'EMP' . str_pad($newId, 3, '0', STR_PAD_LEFT);

        Employee::create([
            'employee_id' => $employeeId,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'join_date' => $request->join_date,
            'department' => $request->department,
            'position' => $request->position,
            'basic_salary' => $request->basic_salary,
            'status' => 'active',
            'emergency_contact' => $request->emergency_contact,
            'bank_name' => $request->bank_name,
            'bank_account' => $request->bank_account,
        ]);

        return redirect()->route('employees.index')->with('success', 'Karyawan berhasil ditambahkan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $employee = Employee::findOrFail($id);
        return view('employees.show', compact('employee'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $employee = Employee::findOrFail($id);
        $departments = ['IT', 'HR', 'Finance', 'Marketing', 'Sales', 'Operations'];
        $positions = ['Staff', 'Senior Staff', 'Supervisor', 'Manager', 'Senior Manager', 'Director'];
        $statuses = ['active', 'inactive', 'terminated'];

        return view('employees.edit', compact('employee', 'departments', 'positions', 'statuses'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email,' . $id,
            'phone' => 'required|string|max:20',
            'department' => 'required|string',
            'position' => 'required|string',
            'join_date' => 'required|date',
            'basic_salary' => 'required|numeric|min:0',
            'status' => 'required|string',
            'address' => 'nullable|string',
            'emergency_contact' => 'nullable|string',
            'bank_name' => 'nullable|string',
            'bank_account' => 'nullable|string',
        ]);

        $employee = Employee::findOrFail($id);
        $employee->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'join_date' => $request->join_date,
            'department' => $request->department,
            'position' => $request->position,
            'basic_salary' => $request->basic_salary,
            'status' => $request->status,
            'emergency_contact' => $request->emergency_contact,
            'bank_name' => $request->bank_name,
            'bank_account' => $request->bank_account,
            'company_id' => auth()->user()->company_id,
        ]);

        return redirect()->route('employees.index')->with('success', 'Data karyawan berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $employee = Employee::findOrFail($id);
            $employee->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Karyawan berhasil dihapus!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus karyawan: ' . $e->getMessage()
            ], 500);
        }
    }
}
