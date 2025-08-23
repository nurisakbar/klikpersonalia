<?php

namespace App\Http\Controllers;

use App\Models\SalaryComponent;
use App\Models\Employee;
use App\Models\EmployeeSalaryComponent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class EmployeeSalaryComponentManagementController extends Controller
{
    public function index()
    {
        $salaryComponents = SalaryComponent::where('company_id', Auth::user()->company_id)
            ->orderBy('name')
            ->get();

        $employees = Employee::where('company_id', Auth::user()->company_id)
            ->with(['salaryComponents.salaryComponent'])
            ->orderBy('name')
            ->get();

        return view('employee-salary-component-management.index', compact('salaryComponents', 'employees'));
    }

    public function create()
    {
        $salaryComponents = SalaryComponent::where('company_id', Auth::user()->company_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $employees = Employee::where('company_id', Auth::user()->company_id)
            ->orderBy('name')
            ->get();

        return view('employee-salary-component-management.create', compact('salaryComponents', 'employees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'salary_component_id' => 'required|exists:salary_components,id',
            'calculation_type' => 'required|in:fixed,percentage',
            'amount' => 'required_if:calculation_type,fixed|numeric|min:0',
            'percentage_value' => 'required_if:calculation_type,percentage|numeric|min:0|max:100',
            'reference_type' => 'required_if:calculation_type,percentage|in:basic_salary,gross_salary,net_salary',
            'effective_date' => 'required|date',
            'notes' => 'nullable|string|max:500',
            'is_active' => 'boolean'
        ]);

        // Check if component already exists for this employee
        $existing = EmployeeSalaryComponent::where('employee_id', $request->employee_id)
            ->where('salary_component_id', $request->salary_component_id)
            ->first();

        if ($existing) {
            return back()->withErrors(['salary_component_id' => 'Komponen gaji ini sudah ada untuk karyawan tersebut.']);
        }

        EmployeeSalaryComponent::create([
            'employee_id' => $request->employee_id,
            'salary_component_id' => $request->salary_component_id,
            'calculation_type' => $request->calculation_type,
            'amount' => $request->calculation_type === 'fixed' ? $request->amount : null,
            'percentage_value' => $request->calculation_type === 'percentage' ? $request->percentage_value : null,
            'reference_type' => $request->calculation_type === 'percentage' ? $request->reference_type : null,
            'effective_date' => $request->effective_date,
            'notes' => $request->notes,
            'is_active' => $request->has('is_active'),
            'company_id' => Auth::user()->company_id
        ]);

        return redirect()->route('employee-salary-component-management.index')
            ->with('success', 'Komponen gaji berhasil ditambahkan untuk karyawan.');
    }

    public function show(EmployeeSalaryComponent $employeeSalaryComponent)
    {
        $this->authorize('view', $employeeSalaryComponent);
        
        return view('employee-salary-component-management.show', compact('employeeSalaryComponent'));
    }

    public function edit(EmployeeSalaryComponent $employeeSalaryComponent)
    {
        $this->authorize('update', $employeeSalaryComponent);
        
        $salaryComponents = SalaryComponent::where('company_id', Auth::user()->company_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('employee-salary-component-management.edit', compact('employeeSalaryComponent', 'salaryComponents'));
    }

    public function update(Request $request, EmployeeSalaryComponent $employeeSalaryComponent)
    {
        $this->authorize('update', $employeeSalaryComponent);

        $request->validate([
            'calculation_type' => 'required|in:fixed,percentage',
            'amount' => 'required_if:calculation_type,fixed|numeric|min:0',
            'percentage_value' => 'required_if:calculation_type,percentage|numeric|min:0|max:100',
            'reference_type' => 'required_if:calculation_type,percentage|in:basic_salary,gross_salary,net_salary',
            'effective_date' => 'required|date',
            'notes' => 'nullable|string|max:500',
            'is_active' => 'boolean'
        ]);

        $employeeSalaryComponent->update([
            'calculation_type' => $request->calculation_type,
            'amount' => $request->calculation_type === 'fixed' ? $request->amount : null,
            'percentage_value' => $request->calculation_type === 'percentage' ? $request->percentage_value : null,
            'reference_type' => $request->calculation_type === 'percentage' ? $request->reference_type : null,
            'effective_date' => $request->effective_date,
            'notes' => $request->notes,
            'is_active' => $request->has('is_active')
        ]);

        return redirect()->route('employee-salary-component-management.index')
            ->with('success', 'Komponen gaji berhasil diperbarui.');
    }

    public function destroy(EmployeeSalaryComponent $employeeSalaryComponent)
    {
        $this->authorize('delete', $employeeSalaryComponent);
        
        $employeeSalaryComponent->delete();

        return redirect()->route('employee-salary-component-management.index')
            ->with('success', 'Komponen gaji berhasil dihapus.');
    }

    public function toggleStatus(EmployeeSalaryComponent $employeeSalaryComponent)
    {
        $this->authorize('update', $employeeSalaryComponent);
        
        $employeeSalaryComponent->update([
            'is_active' => !$employeeSalaryComponent->is_active
        ]);

        $status = $employeeSalaryComponent->is_active ? 'diaktifkan' : 'dinonaktifkan';
        
        return redirect()->route('employee-salary-component-management.index')
            ->with('success', "Status komponen gaji berhasil {$status}.");
    }

    public function bulkAssign(Request $request)
    {
        $request->validate([
            'employee_ids' => 'required|array',
            'employee_ids.*' => 'exists:employees,id',
            'salary_component_id' => 'required|exists:salary_components,id',
            'calculation_type' => 'required|in:fixed,percentage',
            'amount' => 'required_if:calculation_type,fixed|numeric|min:0',
            'percentage_value' => 'required_if:calculation_type,percentage|numeric|min:0|max:100',
            'reference_type' => 'required_if:calculation_type,percentage|in:basic_salary,gross_salary,net_salary',
            'effective_date' => 'required|date',
            'notes' => 'nullable|string|max:500',
            'is_active' => 'boolean'
        ]);

        $assigned = 0;
        $skipped = 0;

        foreach ($request->employee_ids as $employeeId) {
            // Check if component already exists
            $existing = EmployeeSalaryComponent::where('employee_id', $employeeId)
                ->where('salary_component_id', $request->salary_component_id)
                ->first();

            if ($existing) {
                $skipped++;
                continue;
            }

            EmployeeSalaryComponent::create([
                'employee_id' => $employeeId,
                'salary_component_id' => $request->salary_component_id,
                'calculation_type' => $request->calculation_type,
                'amount' => $request->calculation_type === 'fixed' ? $request->amount : null,
                'percentage_value' => $request->calculation_type === 'percentage' ? $request->percentage_value : null,
                'reference_type' => $request->calculation_type === 'percentage' ? $request->reference_type : null,
                'effective_date' => $request->effective_date,
                'notes' => $request->notes,
                'is_active' => $request->has('is_active'),
                'company_id' => Auth::user()->company_id
            ]);

            $assigned++;
        }

        $message = "Berhasil menambahkan komponen gaji untuk {$assigned} karyawan.";
        if ($skipped > 0) {
            $message .= " {$skipped} karyawan dilewati karena komponen sudah ada.";
        }

        return redirect()->route('employee-salary-component-management.index')
            ->with('success', $message);
    }
}
