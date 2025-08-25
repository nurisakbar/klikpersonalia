<?php

namespace App\Repositories;

use App\Models\Overtime;
use App\Models\Employee;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

class OvertimeRepository
{
    protected $model;

    public function __construct(Overtime $model)
    {
        $this->model = $model;
    }

    /**
     * Get all overtimes for employee
     */
    public function getOvertimesForEmployee(string $employeeId, int $perPage = 10): LengthAwarePaginator
    {
        return $this->model
            ->where('employee_id', $employeeId)
            ->with(['employee', 'approver'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get overtimes for DataTables
     */
    public function getOvertimesForEmployeeDataTables(string $employeeId, $startDate = null, $endDate = null, $statusFilter = null)
    {
        $query = $this->model
            ->where('employee_id', $employeeId)
            ->with(['employee', 'approver']);

        // Apply date filters
        if ($startDate) {
            $query->where('date', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('date', '<=', $endDate);
        }

        // Apply status filter
        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }

        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Get all overtimes for company DataTables (for admin/HR/manager)
     */
    public function getOvertimesForCompanyDataTables(string $companyId, $startDate = null, $endDate = null, $statusFilter = null)
    {
        $query = $this->model
            ->where('company_id', $companyId)
            ->with(['employee', 'approver']);

        // Apply date filters
        if ($startDate) {
            $query->where('date', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('date', '<=', $endDate);
        }

        // Apply status filter
        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }

        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Get pending overtimes for approval
     */
    public function getPendingOvertimes(string $companyId, int $perPage = 10): LengthAwarePaginator
    {
        return $this->model
            ->with(['employee', 'approver'])
            ->where('status', 'pending')
            ->where('company_id', $companyId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get overtime by ID
     */
    public function findById(string $id): ?Overtime
    {
        return $this->model
            ->with(['employee', 'approver'])
            ->find($id);
    }

    /**
     * Create new overtime
     */
    public function create(array $data): Overtime
    {
        return $this->model->create($data);
    }

    /**
     * Update overtime
     */
    public function update(Overtime $overtime, array $data): bool
    {
        return $overtime->update($data);
    }

    /**
     * Delete overtime
     */
    public function delete(Overtime $overtime): bool
    {
        return $overtime->delete();
    }

    /**
     * Approve overtime
     */
    public function approve(Overtime $overtime, string $approverId, ?string $notes = null): bool
    {
        return $overtime->update([
            'status' => 'approved',
            'approved_by' => $approverId,
            'approved_at' => now(),
            'approval_notes' => $notes,
        ]);
    }

    /**
     * Reject overtime
     */
    public function reject(Overtime $overtime, string $rejecterId, string $notes): bool
    {
        return $overtime->update([
            'status' => 'rejected',
            'approved_by' => $rejecterId,
            'approved_at' => now(),
            'approval_notes' => $notes,
        ]);
    }

    /**
     * Get pending overtimes for company approval
     */
    public function getPendingOvertimesForCompany(string $companyId, $startDate = null, $endDate = null)
    {
        $query = $this->model
            ->with('employee')
            ->where('company_id', $companyId)
            ->where('status', 'pending');

        // Apply date filters
        if ($startDate) {
            $query->where('date', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('date', '<=', $endDate);
        }

        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Get overtime statistics for employee
     */
    public function getOvertimeStatistics(string $employeeId, int $year = null): array
    {
        $year = $year ?? Carbon::now()->year;
        
        // Get approved overtimes for the year
        $approvedOvertimes = $this->model
            ->where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->whereYear('date', $year)
            ->get();

        // Calculate statistics by type
        $totalHours = $approvedOvertimes->sum('total_hours');
        $totalRequests = $approvedOvertimes->count();
        $averageHours = $totalRequests > 0 ? round($totalHours / $totalRequests, 2) : 0;
        
        // Calculate monthly average
        $currentMonth = Carbon::now()->month;
        $monthlyOvertimes = $approvedOvertimes->where('date', '>=', Carbon::now()->startOfYear()->addMonths($currentMonth - 1));
        $monthlyHours = $monthlyOvertimes->sum('total_hours');
        $monthlyAverage = $currentMonth > 0 ? round($monthlyHours / $currentMonth, 2) : 0;

        // Calculate by type
        $regularHours = $approvedOvertimes->where('overtime_type', 'regular')->sum('total_hours');
        $holidayHours = $approvedOvertimes->where('overtime_type', 'holiday')->sum('total_hours');
        $weekendHours = $approvedOvertimes->where('overtime_type', 'weekend')->sum('total_hours');
        $emergencyHours = $approvedOvertimes->where('overtime_type', 'emergency')->sum('total_hours');

        return [
            'total_hours' => $totalHours,
            'total_requests' => $totalRequests,
            'average_hours' => $averageHours,
            'monthly_average' => $monthlyAverage,
            'regular_hours' => $regularHours,
            'holiday_hours' => $holidayHours,
            'weekend_hours' => $weekendHours,
            'emergency_hours' => $emergencyHours,
        ];
    }

    /**
     * Get overtime statistics for company
     */
    public function getCompanyOvertimeStatistics(string $companyId, int $year = null): array
    {
        $year = $year ?? Carbon::now()->year;
        
        $overtimes = $this->model
            ->where('company_id', $companyId)
            ->whereYear('created_at', $year)
            ->get();

        return [
            'total_overtimes' => $overtimes->count(),
            'pending_overtimes' => $overtimes->where('status', 'pending')->count(),
            'approved_overtimes' => $overtimes->where('status', 'approved')->count(),
            'rejected_overtimes' => $overtimes->where('status', 'rejected')->count(),
            'total_hours' => $overtimes->sum('total_hours'),
            'approved_hours' => $overtimes->where('status', 'approved')->sum('total_hours'),
        ];
    }

    /**
     * Check if employee has overlapping overtimes
     */
    public function hasOverlappingOvertimes(string $employeeId, string $date, ?string $excludeId = null): bool
    {
        $query = $this->model
            ->where('employee_id', $employeeId)
            ->whereIn('status', ['pending', 'approved']) // Only check pending and approved overtimes
            ->where('date', $date);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}
