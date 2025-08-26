<?php

namespace App\Repositories;

use App\Models\Tax;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Collection;

class TaxRepository
{
    protected $model;

    public function __construct(Tax $model)
    {
        $this->model = $model;
    }

    /**
     * Get all taxes for the current company with pagination
     */
    public function getAllWithPagination(array $filters = [], int $perPage = 15)
    {
        $user = Auth::user();
        
        $query = $this->model->with(['employee'])
            ->where('company_id', $user->company_id);

        // Apply filters
        if (!empty($filters['period'])) {
            $query->where('tax_period', $filters['period']);
        }

        if (!empty($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Get all taxes for DataTable
     */
    public function getAllForDataTable(array $filters = [])
    {
        $user = Auth::user();
        
        $query = $this->model->with(['employee'])
            ->where('company_id', $user->company_id);

        // Apply filters
        if (!empty($filters['month'])) {
            $month = str_pad($filters['month'], 2, '0', STR_PAD_LEFT);
            $query->where('tax_period', 'LIKE', '%-' . $month);
        }

        if (!empty($filters['year'])) {
            $query->where('tax_period', 'LIKE', $filters['year'] . '-%');
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query;
    }

    /**
     * Find tax by ID
     */
    public function findById(string $id)
    {
        $user = Auth::user();
        
        return $this->model->with(['employee', 'payroll'])
            ->where('company_id', $user->company_id)
            ->find($id);
    }

    /**
     * Create new tax
     */
    public function create(array $data)
    {
        $user = Auth::user();
        $data['company_id'] = $user->company_id;
        
        return $this->model->create($data);
    }

    /**
     * Update tax
     */
    public function update(string $id, array $data)
    {
        $user = Auth::user();
        
        $tax = $this->model->where('company_id', $user->company_id)
            ->findOrFail($id);
            
        $tax->update($data);
        
        return $tax;
    }

    /**
     * Delete tax
     */
    public function delete(string $id)
    {
        $user = Auth::user();
        
        $tax = $this->model->where('company_id', $user->company_id)
            ->findOrFail($id);
            
        return $tax->delete();
    }

    /**
     * Get taxes by employee
     */
    public function getByEmployee(int $employeeId, array $filters = [])
    {
        $user = Auth::user();
        
        $query = $this->model->with(['employee', 'payroll'])
            ->where('company_id', $user->company_id)
            ->where('employee_id', $employeeId);

        // Apply filters
        if (!empty($filters['period'])) {
            $query->where('tax_period', $filters['period']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('tax_period', 'desc')->get();
    }

    /**
     * Get taxes by period
     */
    public function getByPeriod(string $period, array $filters = [])
    {
        $user = Auth::user();
        
        $query = $this->model->with(['employee', 'payroll'])
            ->where('company_id', $user->company_id)
            ->where('tax_period', $period);

        // Apply additional filters
        if (!empty($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get tax summary for reporting
     */
    public function getTaxSummary(array $filters = [])
    {
        $user = Auth::user();
        
        $query = $this->model->with(['employee'])
            ->where('company_id', $user->company_id);

        // Apply filters
        if (!empty($filters['period'])) {
            $query->where('tax_period', $filters['period']);
        }

        if (!empty($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $taxes = $query->get();

        return [
            'total_employees' => $taxes->count(),
            'total_taxable_income' => $taxes->sum('taxable_income'),
            'total_tax_amount' => $taxes->sum('tax_amount'),
            'average_tax_rate' => $taxes->avg('tax_rate'),
            'status_distribution' => $taxes->groupBy('status')->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'total_tax' => $group->sum('tax_amount'),
                ];
            }),
        ];
    }

    /**
     * Check if tax calculation exists for employee and period
     */
    public function existsForEmployeeAndPeriod(string $employeeId, string $period)
    {
        $user = Auth::user();
        
        return $this->model->where('company_id', $user->company_id)
            ->where('employee_id', $employeeId)
            ->where('tax_period', $period)
            ->exists();
    }

    /**
     * Get employees for tax calculation
     */
    public function getEmployeesForTaxCalculation()
    {
        $user = Auth::user();
        
        return Employee::where('company_id', $user->company_id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
    }

    /**
     * Bulk calculate taxes for payroll period
     */
    public function calculateTaxesForPayrollPeriod(int $month, int $year)
    {
        $user = Auth::user();
        $period = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT);
        
        $employees = Employee::where('company_id', $user->company_id)
            ->where('status', 'active')
            ->get();
        
        $calculatedCount = 0;
        $errors = [];

        foreach ($employees as $employee) {
            try {
                // Check if tax calculation already exists
                if ($this->existsForEmployeeAndPeriod($employee->id, $period)) {
                    continue;
                }

                // Calculate tax for employee
                $taxCalculation = Tax::calculatePPh21($employee, $employee->basic_salary);

                // Create tax record
                $this->create([
                    'employee_id' => $employee->id,
                    'tax_period' => $period,
                    'taxable_income' => $employee->basic_salary,
                    'ptkp_status' => $employee->ptkp_status ?? 'TK/0',
                    'ptkp_amount' => $taxCalculation['ptkp_amount'],
                    'taxable_base' => $taxCalculation['taxable_base'],
                    'tax_amount' => $taxCalculation['tax_amount'],
                    'tax_bracket' => $taxCalculation['tax_bracket'],
                    'tax_rate' => $taxCalculation['tax_rate'],
                    'status' => Tax::STATUS_CALCULATED,
                ]);

                $calculatedCount++;
            } catch (\Exception $e) {
                $errors[] = "Error calculating tax for {$employee->name}: " . $e->getMessage();
            }
        }

        return [
            'calculated_count' => $calculatedCount,
            'errors' => $errors,
        ];
    }

    /**
     * Search taxes by employee name or tax period
     */
    public function search(string $query)
    {
        $user = Auth::user();
        
        return $this->model->with(['employee'])
            ->where('company_id', $user->company_id)
            ->whereHas('employee', function ($q) use ($query) {
                $q->where('name', 'like', '%' . $query . '%')
                  ->orWhere('employee_id', 'like', '%' . $query . '%');
            })
            ->orWhere('tax_period', 'like', '%' . $query . '%')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get taxes for select dropdown
     */
    public function getForSelect(int $limit = 20)
    {
        $user = Auth::user();
        
        return $this->model->with(['employee'])
            ->where('company_id', $user->company_id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
