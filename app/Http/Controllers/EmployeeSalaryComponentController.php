<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\SalaryComponent;
use App\Models\EmployeeSalaryComponent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class EmployeeSalaryComponentController extends Controller
{
    /**
     * Display a listing of employee salary components.
     * This method is no longer used as components are now embedded in employee detail.
     */
    public function index()
    {
        return redirect()->route('employees.index');
    }

    /**
     * Show the form for creating a new employee salary component.
     * This method is no longer used as components are now embedded in employee detail.
     */
    public function create()
    {
        return redirect()->route('employees.index');
    }

    /**
     * Store a newly created employee salary component.
     */
    public function store(Request $request, Employee $employee)
    {
        $request->validate([
            'salary_component_id' => 'required|uuid|exists:salary_components,id',
            'amount' => 'required|numeric|min:0',
            'calculation_type' => ['required', Rule::in(['fixed', 'percentage'])],
            'percentage_value' => 'nullable|numeric|min:0|max:100',
            'reference_type' => 'nullable|in:basic_salary,gross_salary,net_salary',
            'is_active' => 'boolean',
            'effective_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after:effective_date',
            'notes' => 'nullable|string|max:1000'
        ]);

        // Check if component is already assigned to this employee
        $existing = EmployeeSalaryComponent::where('company_id', Auth::user()->company_id)
            ->where('employee_id', $employee->id)
            ->where('salary_component_id', $request->salary_component_id)
            ->first();

        if ($existing) {
            return back()->withErrors(['salary_component_id' => 'Komponen gaji ini sudah diassign ke karyawan tersebut.']);
        }

        // Validate percentage calculation
        if ($request->calculation_type === 'percentage') {
            if (!$request->percentage_value) {
                return back()->withErrors(['percentage_value' => 'Nilai persentase harus diisi untuk perhitungan berbasis persentase.']);
            }
            if (!$request->reference_type) {
                return back()->withErrors(['reference_type' => 'Tipe referensi harus dipilih untuk perhitungan berbasis persentase.']);
            }
        }

        $component = EmployeeSalaryComponent::create([
            'company_id' => Auth::user()->company_id,
            'employee_id' => $employee->id,
            'salary_component_id' => $request->salary_component_id,
            'amount' => $request->amount,
            'calculation_type' => $request->calculation_type,
            'percentage_value' => $request->percentage_value,
            'reference_type' => $request->reference_type,
            'is_active' => $request->boolean('is_active'),
            'effective_date' => $request->effective_date,
            'expiry_date' => $request->expiry_date,
            'notes' => $request->notes
        ]);

        return redirect()->route('employees.show', $employee->id)
            ->with('success', 'Komponen gaji berhasil diassign ke karyawan.');
    }

    /**
     * Display the specified employee salary component.
     */
    public function show(Employee $employee, EmployeeSalaryComponent $employeeSalaryComponent)
    {
        $this->authorize('view', $employeeSalaryComponent);
        return view('employee-salary-components.show', compact('employeeSalaryComponent'));
    }

    /**
     * Show the form for editing the specified employee salary component.
     */
    public function edit(Employee $employee, EmployeeSalaryComponent $employeeSalaryComponent)
    {
        $this->authorize('update', $employeeSalaryComponent);

        $salaryComponents = SalaryComponent::where('company_id', Auth::user()->company_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('employee-salary-components.edit', compact('employeeSalaryComponent', 'salaryComponents'));
    }

    /**
     * Update the specified employee salary component.
     */
    public function update(Request $request, Employee $employee, EmployeeSalaryComponent $employeeSalaryComponent)
    {
        $this->authorize('update', $employeeSalaryComponent);

        $request->validate([
            'amount' => 'required|numeric|min:0',
            'calculation_type' => ['required', Rule::in(['fixed', 'percentage'])],
            'percentage_value' => 'nullable|numeric|min:0|max:100',
            'reference_type' => 'nullable|in:basic_salary,gross_salary,net_salary',
            'is_active' => 'boolean',
            'effective_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after:effective_date',
            'notes' => 'nullable|string|max:1000'
        ]);

        // Validate percentage calculation
        if ($request->calculation_type === 'percentage') {
            if (!$request->percentage_value) {
                return back()->withErrors(['percentage_value' => 'Nilai persentase harus diisi untuk perhitungan berbasis persentase.']);
            }
            if (!$request->reference_type) {
                return back()->withErrors(['reference_type' => 'Tipe referensi harus dipilih untuk perhitungan berbasis persentase.']);
            }
        }

        $employeeSalaryComponent->update([
            'amount' => $request->amount,
            'calculation_type' => $request->calculation_type,
            'percentage_value' => $request->percentage_value,
            'reference_type' => $request->reference_type,
            'is_active' => $request->boolean('is_active'),
            'effective_date' => $request->effective_date,
            'expiry_date' => $request->expiry_date,
            'notes' => $request->notes
        ]);

        return redirect()->route('employees.show', $employee->id)
            ->with('success', 'Komponen gaji karyawan berhasil diperbarui.');
    }

    /**
     * Remove the specified employee salary component.
     */
    public function destroy(Employee $employee, EmployeeSalaryComponent $employeeSalaryComponent)
    {
        $this->authorize('delete', $employeeSalaryComponent);

        $employeeSalaryComponent->delete();

        return redirect()->route('employees.show', $employee->id)
            ->with('success', 'Komponen gaji karyawan berhasil dihapus.');
    }

    /**
     * Toggle the status of an employee salary component.
     */
    public function toggleStatus(Employee $employee, EmployeeSalaryComponent $employeeSalaryComponent)
    {
        $this->authorize('update', $employeeSalaryComponent);

        $employeeSalaryComponent->update([
            'is_active' => !$employeeSalaryComponent->is_active
        ]);

        $status = $employeeSalaryComponent->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return response()->json([
            'success' => true,
            'message' => "Komponen gaji berhasil {$status}."
        ]);
    }

    /**
     * Get employee salary components for DataTable.
     */
    public function data()
    {
        $components = EmployeeSalaryComponent::where('company_id', Auth::user()->company_id)
            ->with(['employee', 'salaryComponent'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $components->map(function ($component) {
                return [
                    'id' => $component->id,
                    'employee_name' => $component->employee->name,
                    'component_name' => $component->salaryComponent->name,
                    'component_type' => $component->salaryComponent->type,
                    'amount' => $component->formatted_amount,
                    'calculation_type' => $component->calculation_type_text,
                    'reference_type' => $component->reference_type_text,
                    'is_active' => $component->is_active,
                    'effective_date' => $component->effective_date ? $component->effective_date->format('d/m/Y') : '-',
                    'expiry_date' => $component->expiry_date ? $component->expiry_date->format('d/m/Y') : '-',
                    'created_at' => $component->created_at->format('d/m/Y H:i'),
                    'action' => view('employee-salary-components.partials.actions', compact('component'))->render()
                ];
            })
        ]);
    }

    /**
     * Get components for a specific employee.
     */
    public function employeeComponents($employeeId)
    {
        $employee = Employee::where('company_id', Auth::user()->company_id)
            ->findOrFail($employeeId);

        $components = $employee->salaryComponents()
            ->with('salaryComponent')
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'employee' => [
                'id' => $employee->id,
                'name' => $employee->name,
                'basic_salary' => $employee->basic_salary
            ],
            'components' => $components->map(function ($component) {
                return [
                    'id' => $component->id,
                    'component_name' => $component->salaryComponent->name,
                    'component_type' => $component->salaryComponent->type,
                    'amount' => $component->formatted_amount,
                    'calculation_type' => $component->calculation_type_text,
                    'reference_type' => $component->reference_type_text,
                    'is_active' => $component->is_active,
                    'effective_date' => $component->effective_date ? $component->effective_date->format('d/m/Y') : '-',
                    'expiry_date' => $component->expiry_date ? $component->expiry_date->format('d/m/Y') : '-',
                    'notes' => $component->notes
                ];
            })
        ]);
    }

    /**
     * Bulk assign components to employees.
     */
    public function bulkAssign(Request $request)
    {
        $request->validate([
            'employee_ids' => 'required|array',
            'employee_ids.*' => 'required|uuid|exists:employees,id',
            'salary_component_id' => 'required|uuid|exists:salary_components,id',
            'amount' => 'required|numeric|min:0',
            'calculation_type' => ['required', Rule::in(['fixed', 'percentage'])],
            'percentage_value' => 'nullable|numeric|min:0|max:100',
            'reference_type' => 'nullable|in:basic_salary,gross_salary,net_salary',
            'is_active' => 'boolean',
            'effective_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after:effective_date'
        ]);

        $assigned = 0;
        $skipped = 0;

        foreach ($request->employee_ids as $employeeId) {
            // Check if already assigned
            $existing = EmployeeSalaryComponent::where('company_id', Auth::user()->company_id)
                ->where('employee_id', $employeeId)
                ->where('salary_component_id', $request->salary_component_id)
                ->first();

            if ($existing) {
                $skipped++;
                continue;
            }

            EmployeeSalaryComponent::create([
                'company_id' => Auth::user()->company_id,
                'employee_id' => $employeeId,
                'salary_component_id' => $request->salary_component_id,
                'amount' => $request->amount,
                'calculation_type' => $request->calculation_type,
                'percentage_value' => $request->percentage_value,
            'reference_type' => $request->reference_type,
                'is_active' => $request->boolean('is_active'),
                'effective_date' => $request->effective_date,
                'expiry_date' => $request->expiry_date
            ]);

            $assigned++;
        }

        $message = "{$assigned} komponen gaji berhasil diassign.";
        if ($skipped > 0) {
            $message .= " {$skipped} karyawan sudah memiliki komponen tersebut.";
        }

        return redirect()->route('employees.index')
            ->with('success', $message);
    }
}
