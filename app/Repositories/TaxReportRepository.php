<?php

namespace App\Repositories;

use App\Models\Tax;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TaxReportRepository
{
    /**
     * Get tax report data with filters
     */
    public function getTaxReportData($filters = [])
    {
        $user = Auth::user();
        
        $query = Tax::with(['employee', 'payroll'])
            ->where('company_id', $user->company_id);

        // Apply filters
        if (!empty($filters['start_date'])) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }

        if (!empty($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['tax_period'])) {
            $query->where('tax_period', $filters['tax_period']);
        }

        // Get total count for pagination
        $totalRecords = $query->count();
        $filteredRecords = $totalRecords;

        // Apply search if provided
        if (!empty($filters['search']['value'])) {
            $searchValue = $filters['search']['value'];
            $query->where(function($q) use ($searchValue) {
                $q->whereHas('employee', function($subQ) use ($searchValue) {
                    $subQ->where('name', 'like', "%{$searchValue}%")
                         ->orWhere('employee_id', 'like', "%{$searchValue}%");
                })
                ->orWhere('tax_period', 'like', "%{$searchValue}%")
                ->orWhere('status', 'like', "%{$searchValue}%");
            });
            $filteredRecords = $query->count();
        }

        // Apply ordering
        if (!empty($filters['order'])) {
            foreach ($filters['order'] as $order) {
                $columnIndex = $order['column'];
                $columnName = $this->getColumnName($columnIndex);
                $direction = $order['dir'];
                
                if ($columnName) {
                    $query->orderBy($columnName, $direction);
                }
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Apply pagination
        if (isset($filters['start']) && isset($filters['length'])) {
            $query->skip($filters['start'])->take($filters['length']);
        }

        $data = $query->get();

        // Log for debugging
        \Log::info('TaxReportRepository: Query executed', [
            'totalRecords' => $totalRecords,
            'filteredRecords' => $filteredRecords,
            'dataCount' => count($data),
            'filters' => $filters
        ]);

        return [
            'data' => $data,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'draw' => isset($filters['draw']) ? intval($filters['draw']) : 1
        ];
    }

    /**
     * Get column name by index for DataTable ordering
     */
    private function getColumnName($columnIndex)
    {
        $columns = [
            0 => 'created_at',
            1 => 'employee_id',
            2 => 'tax_period',
            3 => 'taxable_income',
            4 => 'tax_amount',
            5 => 'status',
            6 => 'created_at'
        ];

        return $columns[$columnIndex] ?? null;
    }

    /**
     * Get employees for filter dropdown
     */
    public function getEmployeesForFilter()
    {
        $user = Auth::user();
        
        return Employee::where('company_id', $user->company_id)
            ->select('id', 'name', 'employee_id')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get tax periods for filter dropdown
     */
    public function getTaxPeriodsForFilter()
    {
        $user = Auth::user();
        
        return Tax::where('company_id', $user->company_id)
            ->select('tax_period')
            ->distinct()
            ->orderBy('tax_period', 'desc')
            ->get()
            ->pluck('tax_period');
    }

    /**
     * Get summary statistics
     */
    public function getSummaryStatistics($filters = [])
    {
        $user = Auth::user();
        
        $query = Tax::where('company_id', $user->company_id);

        // Apply filters
        if (!empty($filters['start_date'])) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }

        if (!empty($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['tax_period'])) {
            $query->where('tax_period', $filters['tax_period']);
        }

        return [
            'total_records' => $query->count(),
            'total_taxable_income' => $query->sum('taxable_income'),
            'total_ptkp_amount' => $query->sum('ptkp_amount'),
            'total_taxable_base' => $query->sum('taxable_base'),
            'total_tax_amount' => $query->sum('tax_amount'),
            'status_counts' => $query->selectRaw('status, count(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray()
        ];
    }
}
