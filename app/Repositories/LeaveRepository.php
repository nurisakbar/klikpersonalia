<?php

namespace App\Repositories;

use App\Models\Leave;
use App\Models\Employee;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

class LeaveRepository
{
    protected $model;

    public function __construct(Leave $model)
    {
        $this->model = $model;
    }

    /**
     * Get all leaves for employee
     */
    public function getLeavesForEmployee(string $employeeId, int $perPage = 10): LengthAwarePaginator
    {
        return $this->model
            ->where('employee_id', $employeeId)
            ->with(['employee', 'approver'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get leaves for DataTables
     */
    public function getLeavesForEmployeeDataTables(string $employeeId)
    {
        return $this->model
            ->where('employee_id', $employeeId)
            ->with(['employee', 'approver'])
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get pending leaves for approval
     */
    public function getPendingLeaves(string $companyId, int $perPage = 10): LengthAwarePaginator
    {
        return $this->model
            ->with(['employee', 'approver'])
            ->where('status', 'pending')
            ->where('company_id', $companyId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get leave by ID
     */
    public function findById(string $id): ?Leave
    {
        return $this->model
            ->with(['employee', 'approver'])
            ->find($id);
    }

    /**
     * Create new leave
     */
    public function create(array $data): Leave
    {
        return $this->model->create($data);
    }

    /**
     * Update leave
     */
    public function update(Leave $leave, array $data): bool
    {
        return $leave->update($data);
    }

    /**
     * Delete leave
     */
    public function delete(Leave $leave): bool
    {
        return $leave->delete();
    }

    /**
     * Approve leave
     */
    public function approve(Leave $leave, string $approverId, ?string $notes = null): bool
    {
        return $leave->update([
            'status' => 'approved',
            'approved_by' => $approverId,
            'approved_at' => now(),
            'approval_notes' => $notes,
        ]);
    }

    /**
     * Reject leave
     */
    public function reject(Leave $leave, string $rejecterId, string $notes): bool
    {
        return $leave->update([
            'status' => 'rejected',
            'approved_by' => $rejecterId,
            'approved_at' => now(),
            'approval_notes' => $notes,
        ]);
    }

    /**
     * Get leave balance for employee
     */
    public function getLeaveBalance(string $employeeId, int $year = null): array
    {
        $year = $year ?? Carbon::now()->year;
        
        // Get approved leaves for the year
        $approvedLeaves = $this->model
            ->where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->whereYear('start_date', $year)
            ->get();

        // Calculate used leaves by type
        $usedAnnual = $approvedLeaves->where('leave_type', 'annual')->sum('total_days');
        $usedSick = $approvedLeaves->where('leave_type', 'sick')->sum('total_days');
        $usedMaternity = $approvedLeaves->where('leave_type', 'maternity')->sum('total_days');
        $usedPaternity = $approvedLeaves->where('leave_type', 'paternity')->sum('total_days');
        $usedOther = $approvedLeaves->where('leave_type', 'other')->sum('total_days');

        // Default leave quotas (can be configured per company)
        $annualQuota = 12;
        $sickQuota = 12;
        $maternityQuota = 90;
        $paternityQuota = 2;
        $otherQuota = 5;

        return [
            'annual_total' => $annualQuota,
            'annual_used' => $usedAnnual,
            'annual_remaining' => max(0, $annualQuota - $usedAnnual),
            'sick_total' => $sickQuota,
            'sick_used' => $usedSick,
            'sick_remaining' => max(0, $sickQuota - $usedSick),
            'maternity_total' => $maternityQuota,
            'maternity_used' => $usedMaternity,
            'maternity_remaining' => max(0, $maternityQuota - $usedMaternity),
            'paternity_total' => $paternityQuota,
            'paternity_used' => $usedPaternity,
            'paternity_remaining' => max(0, $paternityQuota - $usedPaternity),
            'other_total' => $otherQuota,
            'other_used' => $usedOther,
            'other_remaining' => max(0, $otherQuota - $usedOther),
        ];
    }

    /**
     * Get leave statistics
     */
    public function getLeaveStatistics(string $companyId, int $year = null): array
    {
        $year = $year ?? Carbon::now()->year;
        
        $leaves = $this->model
            ->where('company_id', $companyId)
            ->whereYear('created_at', $year)
            ->get();

        return [
            'total_leaves' => $leaves->count(),
            'pending_leaves' => $leaves->where('status', 'pending')->count(),
            'approved_leaves' => $leaves->where('status', 'approved')->count(),
            'rejected_leaves' => $leaves->where('status', 'rejected')->count(),
            'cancelled_leaves' => $leaves->where('status', 'cancelled')->count(),
            'total_days' => $leaves->sum('total_days'),
            'approved_days' => $leaves->where('status', 'approved')->sum('total_days'),
        ];
    }

    /**
     * Check if employee has overlapping leaves
     */
    public function hasOverlappingLeaves(string $employeeId, string $startDate, string $endDate, ?string $excludeId = null): bool
    {
        $query = $this->model
            ->where('employee_id', $employeeId)
            ->whereIn('status', ['pending', 'approved']) // Only check pending and approved leaves
            ->where(function ($q) use ($startDate, $endDate) {
                // Check if the new date range overlaps with existing leaves
                $q->where(function ($q2) use ($startDate, $endDate) {
                    // Case 1: New start date falls within existing leave period
                    $q2->where('start_date', '<=', $startDate)
                       ->where('end_date', '>=', $startDate);
                })->orWhere(function ($q2) use ($startDate, $endDate) {
                    // Case 2: New end date falls within existing leave period
                    $q2->where('start_date', '<=', $endDate)
                       ->where('end_date', '>=', $endDate);
                })->orWhere(function ($q2) use ($startDate, $endDate) {
                    // Case 3: New date range completely contains existing leave period
                    $q2->where('start_date', '>=', $startDate)
                       ->where('end_date', '<=', $endDate);
                });
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}
