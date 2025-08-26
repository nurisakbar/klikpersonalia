<?php

namespace App\Services;

use App\Repositories\TaxRepository;
use App\Models\Tax;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;

class TaxService
{
    protected $taxRepository;

    public function __construct(TaxRepository $taxRepository)
    {
        $this->taxRepository = $taxRepository;
    }

    /**
     * Get all taxes with pagination
     */
    public function getAllTaxes(array $filters = [], int $perPage = 15)
    {
        try {
            return $this->taxRepository->getAllWithPagination($filters, $perPage);
        } catch (\Exception $e) {
            Log::error('Error getting taxes: ' . $e->getMessage());
            throw new \Exception('Gagal mengambil data pajak');
        }
    }

    /**
     * Get taxes for DataTable
     */
    public function getTaxesForDataTable(array $filters = [])
    {
        try {
            $result = $this->taxRepository->getAllForDataTable($filters);
            
            return $result;
        } catch (\Exception $e) {
            Log::error('Error getting taxes for DataTable: ' . $e->getMessage());
            throw new \Exception('Gagal mengambil data pajak untuk DataTable');
        }
    }

    /**
     * Get tax by ID
     */
    public function getTaxById(string $id)
    {
        try {
            $tax = $this->taxRepository->findById($id);
            
            if (!$tax) {
                throw new \Exception('Data pajak tidak ditemukan');
            }
            
            return $tax;
        } catch (\Exception $e) {
            Log::error('Error getting tax by ID: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create new tax
     */
    public function createTax(array $data)
    {
        try {
            DB::beginTransaction();
            
            // Validate employee belongs to user's company
            $employee = Employee::where('company_id', Auth::user()->company_id)
                ->find($data['employee_id']);
                
            if (!$employee) {
                throw new \Exception('Karyawan tidak ditemukan');
            }

            // Check if tax calculation already exists for this period
            if ($this->taxRepository->existsForEmployeeAndPeriod($data['employee_id'], $data['tax_period'])) {
                throw new \Exception('Perhitungan pajak untuk periode ini sudah ada');
            }

            // Calculate tax
            $taxCalculation = Tax::calculatePPh21($employee, $data['taxable_income']);

            // Prepare tax data
            $taxData = [
                'employee_id' => $data['employee_id'],
                'tax_period' => $data['tax_period'],
                'taxable_income' => $data['taxable_income'],
                'ptkp_status' => $data['ptkp_status'],
                'ptkp_amount' => $taxCalculation['ptkp_amount'],
                'taxable_base' => $taxCalculation['taxable_base'],
                'tax_amount' => $taxCalculation['tax_amount'],
                'tax_bracket' => $taxCalculation['tax_bracket'],
                'tax_rate' => $taxCalculation['tax_rate'],
                'status' => Tax::STATUS_CALCULATED,
                'notes' => $data['notes'] ?? null,
            ];

            $tax = $this->taxRepository->create($taxData);
            
            DB::commit();
            
            return $tax;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating tax: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update tax
     */
    public function updateTax(string $id, array $data)
    {
        try {
            DB::beginTransaction();
            
            $tax = $this->taxRepository->findById($id);
            
            if (!$tax) {
                throw new \Exception('Data pajak tidak ditemukan');
            }

            // Get employee for recalculation
            $employee = $tax->employee;
            
            // Recalculate tax
            $taxCalculation = Tax::calculatePPh21($employee, $data['taxable_income']);

            // Prepare update data
            $updateData = [
                'taxable_income' => $data['taxable_income'],
                'ptkp_status' => $data['ptkp_status'],
                'ptkp_amount' => $taxCalculation['ptkp_amount'],
                'taxable_base' => $taxCalculation['taxable_base'],
                'tax_amount' => $taxCalculation['tax_amount'],
                'tax_bracket' => $taxCalculation['tax_bracket'],
                'tax_rate' => $taxCalculation['tax_rate'],
                'status' => $data['status'],
                'notes' => $data['notes'] ?? null,
            ];

            $updatedTax = $this->taxRepository->update($id, $updateData);
            
            DB::commit();
            
            return $updatedTax;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating tax: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete tax
     */
    public function deleteTax(int $id)
    {
        try {
            DB::beginTransaction();
            
            $tax = $this->taxRepository->findById($id);
            
            if (!$tax) {
                throw new \Exception('Data pajak tidak ditemukan');
            }

            $this->taxRepository->delete($id);
            
            DB::commit();
            
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting tax: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get taxes by employee
     */
    public function getTaxesByEmployee(int $employeeId, array $filters = [])
    {
        try {
            return $this->taxRepository->getByEmployee($employeeId, $filters);
        } catch (\Exception $e) {
            Log::error('Error getting taxes by employee: ' . $e->getMessage());
            throw new \Exception('Gagal mengambil data pajak karyawan');
        }
    }

    /**
     * Get taxes by period
     */
    public function getTaxesByPeriod(string $period, array $filters = [])
    {
        try {
            return $this->taxRepository->getByPeriod($period, $filters);
        } catch (\Exception $e) {
            Log::error('Error getting taxes by period: ' . $e->getMessage());
            throw new \Exception('Gagal mengambil data pajak periode');
        }
    }

    /**
     * Get tax summary for reporting
     */
    public function getTaxSummary(array $filters = [])
    {
        try {
            return $this->taxRepository->getTaxSummary($filters);
        } catch (\Exception $e) {
            Log::error('Error getting tax summary: ' . $e->getMessage());
            throw new \Exception('Gagal mengambil ringkasan pajak');
        }
    }

    /**
     * Get employees for tax calculation
     */
    public function getEmployeesForTaxCalculation()
    {
        try {
            return $this->taxRepository->getEmployeesForTaxCalculation();
        } catch (\Exception $e) {
            Log::error('Error getting employees for tax calculation: ' . $e->getMessage());
            throw new \Exception('Gagal mengambil data karyawan untuk perhitungan pajak');
        }
    }

    /**
     * Bulk calculate taxes for payroll period
     */
    public function calculateTaxesForPayrollPeriod(int $month, int $year)
    {
        try {
            DB::beginTransaction();
            
            $result = $this->taxRepository->calculateTaxesForPayrollPeriod($month, $year);
            
            DB::commit();
            
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error calculating taxes for payroll period: ' . $e->getMessage());
            throw new \Exception('Gagal menghitung pajak untuk periode payroll');
        }
    }

    /**
     * Validate tax data
     */
    public function validateTaxData(array $data, bool $isUpdate = false)
    {
        $rules = [
            'employee_id' => 'required|exists:employees,id',
            'tax_period' => 'required|date_format:Y-m',
            'taxable_income' => 'required|numeric|min:0',
            'ptkp_status' => 'required|in:' . implode(',', array_keys(Tax::PTKP_STATUSES)),
            'notes' => 'nullable|string|max:1000',
        ];

        if ($isUpdate) {
            $rules['status'] = 'required|in:' . implode(',', [Tax::STATUS_PENDING, Tax::STATUS_CALCULATED, Tax::STATUS_PAID, Tax::STATUS_VERIFIED]);
        }

        $validator = \Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first());
        }

        return true;
    }

    /**
     * Export tax data
     */
    public function exportTaxData(array $filters = [])
    {
        try {
            $taxes = $this->taxRepository->getAllForDataTable($filters)->get();
            
            $exportData = [];
            
            foreach ($taxes as $tax) {
                $exportData[] = [
                    'Nama Karyawan' => $tax->employee->name ?? '-',
                    'ID Karyawan' => $tax->employee->employee_id ?? '-',
                    'Periode Pajak' => $tax->tax_period ? date('M Y', strtotime($tax->tax_period . '-01')) : '-',
                    'Pendapatan Kena Pajak' => number_format($tax->taxable_income, 0, ',', '.'),
                    'Status PTKP' => $tax->ptkp_status,
                    'Jumlah PTKP' => number_format($tax->ptkp_amount, 0, ',', '.'),
                    'Dasar Pengenaan Pajak' => number_format($tax->taxable_base, 0, ',', '.'),
                    'Jumlah Pajak' => number_format($tax->tax_amount, 0, ',', '.'),
                    'Tarif Pajak' => number_format($tax->tax_rate * 100, 1) . '%',
                    'Status' => $this->getStatusText($tax->status),
                    'Catatan' => $tax->notes ?? '-',
                ];
            }
            
            return $exportData;
        } catch (\Exception $e) {
            Log::error('Error exporting tax data: ' . $e->getMessage());
            throw new \Exception('Gagal mengekspor data pajak');
        }
    }

    /**
     * Get form data (employees, ptkp statuses, etc.).
     */
    public function getFormData(): array
    {
        return [
            'employees' => $this->taxRepository->getEmployeesForTaxCalculation(),
            'ptkpStatuses' => Tax::PTKP_STATUSES,
        ];
    }

    /**
     * Get employees for select dropdown
     */
    public function getEmployeesForSelect()
    {
        return $this->taxRepository->getEmployeesForTaxCalculation();
    }

    /**
     * Search taxes.
     */
    public function searchTaxes(string $query): Collection
    {
        return $this->taxRepository->search($query);
    }

    /**
     * Default list for select (no query), capped.
     */
    public function getDefaultTaxesForSelect(int $limit = 20): Collection
    {
        return $this->taxRepository->getForSelect($limit);
    }

    /**
     * Get status text
     */
    private function getStatusText(string $status): string
    {
        switch ($status) {
            case 'pending':
                return 'Menunggu';
            case 'calculated':
                return 'Dihitung';
            case 'paid':
                return 'Dibayar';
            case 'verified':
                return 'Terverifikasi';
            default:
                return ucfirst($status);
        }
    }
}
