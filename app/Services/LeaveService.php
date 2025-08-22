<?php

namespace App\Services;

use App\Repositories\LeaveRepository;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class LeaveService
{
    protected $leaveRepository;

    public function __construct(LeaveRepository $leaveRepository)
    {
        $this->leaveRepository = $leaveRepository;
    }

    /**
     * Get leaves for current user
     */
    public function getLeavesForCurrentUser(int $perPage = 10)
    {
        $user = auth()->user();
        $employee = Employee::where('user_id', $user->id)->first();
        
        if (!$employee) {
            throw new Exception('Employee not found for this user.');
        }

        return $this->leaveRepository->getLeavesForEmployee($employee->id, $perPage);
    }

    /**
     * Get leaves for DataTables
     */
    public function getLeavesForDataTables()
    {
        $user = auth()->user();
        $employee = Employee::where('user_id', $user->id)->first();
        
        if (!$employee) {
            throw new Exception('Employee not found for this user.');
        }

        return $this->leaveRepository->getLeavesForEmployeeDataTables($employee->id);
    }

    /**
     * Get pending leaves for approval
     */
    public function getPendingLeaves(int $perPage = 10)
    {
        $user = auth()->user();
        
        if (!in_array($user->role, ['admin', 'hr', 'manager'])) {
            throw new Exception('You do not have permission to view pending leaves.');
        }

        return $this->leaveRepository->getPendingLeaves($user->company_id, $perPage);
    }

    /**
     * Create new leave request
     */
    public function createLeave(array $data)
    {
        $user = auth()->user();
        $employee = Employee::where('user_id', $user->id)->first();
        
        if (!$employee) {
            throw new Exception('Employee not found for this user.');
        }

        // Check for overlapping leaves
        if ($this->leaveRepository->hasOverlappingLeaves($employee->id, $data['start_date'], $data['end_date'])) {
            throw new Exception('Anda memiliki permintaan cuti yang tumpang tindih untuk tanggal yang dipilih.');
        }

        // Calculate total days
        $totalDays = $this->calculateWorkingDays($data['start_date'], $data['end_date']);

        // Check leave balance for annual leave
        if ($data['leave_type'] === 'annual') {
            $leaveBalance = $this->leaveRepository->getLeaveBalance($employee->id);
            if ($totalDays > $leaveBalance['annual_remaining']) {
                throw new Exception('Sisa cuti tahunan tidak mencukupi. Anda memiliki ' . $leaveBalance['annual_remaining'] . ' hari tersisa.');
            }
        }

        // Handle file upload
        $attachmentPath = null;
        if (isset($data['attachment']) && $data['attachment']) {
            $attachmentPath = $data['attachment']->store('leaves/attachments', 'public');
        }

        $leaveData = [
            'employee_id' => $employee->id,
            'company_id' => $user->company_id,
            'leave_type' => $data['leave_type'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'total_days' => $totalDays,
            'reason' => $data['reason'],
            'attachment' => $attachmentPath,
            'status' => 'pending',
        ];

        return $this->leaveRepository->create($leaveData);
    }

    /**
     * Update leave request
     */
    public function updateLeave(string $leaveId, array $data)
    {
        $user = auth()->user();
        $employee = Employee::where('user_id', $user->id)->first();
        
        if (!$employee) {
            throw new Exception('Employee not found for this user.');
        }

        $leave = $this->leaveRepository->findById($leaveId);
        
        if (!$leave) {
            throw new Exception('Leave request not found.');
        }

        if ($leave->employee_id !== $employee->id) {
            throw new Exception('You can only edit your own leave requests.');
        }

        if ($leave->status !== 'pending') {
            throw new Exception('You can only edit pending leave requests.');
        }

        // Check for overlapping leaves (excluding current leave)
        if ($this->leaveRepository->hasOverlappingLeaves($employee->id, $data['start_date'], $data['end_date'], $leaveId)) {
            throw new Exception('Anda memiliki permintaan cuti yang tumpang tindih untuk tanggal yang dipilih.');
        }

        // Calculate total days
        $totalDays = $this->calculateWorkingDays($data['start_date'], $data['end_date']);

        // Check leave balance for annual leave
        if ($data['leave_type'] === 'annual') {
            $leaveBalance = $this->leaveRepository->getLeaveBalance($employee->id);
            $currentLeaveDays = $leave->leave_type === 'annual' ? $leave->total_days : 0;
            if (($totalDays - $currentLeaveDays) > $leaveBalance['annual_remaining']) {
                throw new Exception('Sisa cuti tahunan tidak mencukupi. Anda memiliki ' . $leaveBalance['annual_remaining'] . ' hari tersisa.');
            }
        }

        // Handle file upload
        $attachmentPath = $leave->attachment;
        if (isset($data['attachment']) && $data['attachment']) {
            // Delete old file if exists
            if ($attachmentPath && Storage::disk('public')->exists($attachmentPath)) {
                Storage::disk('public')->delete($attachmentPath);
            }
            $attachmentPath = $data['attachment']->store('leaves/attachments', 'public');
        }

        $updateData = [
            'leave_type' => $data['leave_type'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'total_days' => $totalDays,
            'reason' => $data['reason'],
            'attachment' => $attachmentPath,
        ];

        return $this->leaveRepository->update($leave, $updateData);
    }

    /**
     * Delete leave request
     */
    public function deleteLeave(string $leaveId)
    {
        $user = auth()->user();
        $employee = Employee::where('user_id', $user->id)->first();
        
        if (!$employee) {
            throw new Exception('Employee not found for this user.');
        }

        $leave = $this->leaveRepository->findById($leaveId);
        
        if (!$leave) {
            throw new Exception('Leave request not found.');
        }

        if ($leave->employee_id !== $employee->id) {
            throw new Exception('You can only delete your own leave requests.');
        }

        if ($leave->status !== 'pending') {
            throw new Exception('You can only delete pending leave requests.');
        }

        // Delete attachment file if exists
        if ($leave->attachment && Storage::disk('public')->exists($leave->attachment)) {
            Storage::disk('public')->delete($leave->attachment);
        }

        return $this->leaveRepository->delete($leave);
    }

    /**
     * Approve leave request
     */
    public function approveLeave(string $leaveId, ?string $notes = null)
    {
        $user = auth()->user();
        
        if (!in_array($user->role, ['admin', 'hr', 'manager'])) {
            throw new Exception('You do not have permission to approve leave requests.');
        }

        $leave = $this->leaveRepository->findById($leaveId);
        
        if (!$leave) {
            throw new Exception('Leave request not found.');
        }

        if ($leave->company_id !== $user->company_id) {
            throw new Exception('You can only approve leaves from your company.');
        }

        if ($leave->status !== 'pending') {
            throw new Exception('You can only approve pending leave requests.');
        }

        return $this->leaveRepository->approve($leave, $user->id, $notes);
    }

    /**
     * Reject leave request
     */
    public function rejectLeave(string $leaveId, string $notes)
    {
        $user = auth()->user();
        
        if (!in_array($user->role, ['admin', 'hr', 'manager'])) {
            throw new Exception('You do not have permission to reject leave requests.');
        }

        $leave = $this->leaveRepository->findById($leaveId);
        
        if (!$leave) {
            throw new Exception('Leave request not found.');
        }

        if ($leave->company_id !== $user->company_id) {
            throw new Exception('You can only reject leaves from your company.');
        }

        if ($leave->status !== 'pending') {
            throw new Exception('You can only reject pending leave requests.');
        }

        return $this->leaveRepository->reject($leave, $user->id, $notes);
    }

    /**
     * Find leave by ID
     */
    public function findLeaveById(string $id)
    {
        $user = auth()->user();
        $employee = Employee::where('user_id', $user->id)->first();
        
        if (!$employee) {
            throw new Exception('Employee not found for this user.');
        }

        $leave = $this->leaveRepository->findById($id);
        
        if (!$leave) {
            throw new Exception('Leave request not found.');
        }

        // Check if user can access this leave
        if ($leave->employee_id !== $employee->id && !in_array($user->role, ['admin', 'hr', 'manager'])) {
            throw new Exception('You can only view your own leave requests.');
        }

        return $leave;
    }

    /**
     * Get leave balance for current user
     */
    public function getLeaveBalanceForCurrentUser(int $year = null)
    {
        $user = auth()->user();
        $employee = Employee::where('user_id', $user->id)->first();
        
        if (!$employee) {
            throw new Exception('Employee not found for this user.');
        }

        return $this->leaveRepository->getLeaveBalance($employee->id, $year);
    }

    /**
     * Get leave statistics for company
     */
    public function getLeaveStatistics(int $year = null)
    {
        $user = auth()->user();
        
        if (!in_array($user->role, ['admin', 'hr', 'manager'])) {
            throw new Exception('You do not have permission to view leave statistics.');
        }

        return $this->leaveRepository->getLeaveStatistics($user->company_id, $year);
    }

    /**
     * Calculate working days between two dates
     */
    private function calculateWorkingDays(string $startDate, string $endDate): int
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $totalDays = 0;
        
        while ($start->lte($end)) {
            if (!$start->isWeekend()) {
                $totalDays++;
            }
            $start->addDay();
        }
        
        return $totalDays;
    }
}
