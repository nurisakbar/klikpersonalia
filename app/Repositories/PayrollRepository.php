<?php

namespace App\Repositories;

use App\Models\Payroll;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class PayrollRepository
{
    public function queryForCompany(string $companyId)
    {
        return Payroll::where('company_id', $companyId);
    }

    public function findByIdForCompany(string $id, string $companyId): ?Payroll
    {
        return Payroll::where('id', $id)
            ->where('company_id', $companyId)
            ->first();
    }

    public function findById(string $id): ?Payroll
    {
        $user = Auth::user();
        return Payroll::where('id', $id)
            ->where('company_id', $user->company_id)
            ->first();
    }

    public function findByIdWithRelations(string $id): ?Payroll
    {
        $user = Auth::user();
        return Payroll::with(['employee', 'generatedBy'])
            ->where('id', $id)
            ->where('company_id', $user->company_id)
            ->first();
    }

    public function existsForEmployeeAndPeriod(string $employeeId, string $period, string $companyId): bool
    {
        return Payroll::where('employee_id', $employeeId)
            ->where('period', $period)
            ->where('company_id', $companyId)
            ->exists();
    }

    public function create(array $data): Payroll
    {
        return Payroll::create($data);
    }

    public function update(Payroll $payroll, array $data): bool
    {
        return $payroll->update($data);
    }

    public function delete(Payroll $payroll): bool
    {
        return $payroll->delete();
    }

    public function getByPeriod(string $companyId, ?string $period = null): Collection
    {
        $query = $this->queryForCompany($companyId);
        if ($period) {
            $query->where('period', 'like', $period . '%');
        }
        return $query->with('employee')->orderBy('created_at', 'desc')->get();
    }

    public function getPaginatedWithFilters(?string $period = null, ?string $status = null, int $perPage = 15): LengthAwarePaginator
    {
        $user = Auth::user();
        $query = Payroll::with(['employee', 'generatedBy'])
            ->where('company_id', $user->company_id);
        
        if ($period) {
            $query->where('period', 'LIKE', $period . '%');
        }
        
        if ($status) {
            $query->where('status', $status);
        }
        
        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function getSummaryStatistics(?string $period = null): array
    {
        $user = Auth::user();
        $query = Payroll::where('company_id', $user->company_id);
        
        if ($period) {
            $query->where('period', 'LIKE', $period . '%');
        }
        
        $payrolls = $query->get();

        return [
            'total_payrolls' => $payrolls->count(),
            'draft_payrolls' => $payrolls->where('status', 'draft')->count(),
            'approved_payrolls' => $payrolls->where('status', 'approved')->count(),
            'paid_payrolls' => $payrolls->where('status', 'paid')->count(),
            'total_salary' => $payrolls->sum('total_salary'),
            'total_overtime' => $payrolls->sum('overtime'),
            'total_bonus' => $payrolls->sum('bonus'),
            'total_deductions' => $payrolls->sum('deduction'),
        ];
    }
}


