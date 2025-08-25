<?php

namespace App\Services;

use App\Repositories\OvertimeRepository;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class OvertimeService
{
    protected $overtimeRepository;

    public function __construct(OvertimeRepository $overtimeRepository)
    {
        $this->overtimeRepository = $overtimeRepository;
    }

    /**
     * Get overtimes for current user
     */
    public function getOvertimesForCurrentUser(int $perPage = 10)
    {
        $user = auth()->user();
        $employee = Employee::where('user_id', $user->id)->first();
        
        if (!$employee) {
            throw new Exception('Employee not found for this user.');
        }

        return $this->overtimeRepository->getOvertimesForEmployee($employee->id, $perPage);
    }

    /**
     * Get pending overtimes for approval
     */
    public function getPendingOvertimesForApproval($startDate = null, $endDate = null)
    {
        $user = auth()->user();
        
        if (!in_array($user->role, ['admin', 'hr', 'manager'])) {
            throw new Exception('You do not have permission to approve overtime requests.');
        }

        return $this->overtimeRepository->getPendingOvertimesForCompany($user->company_id, $startDate, $endDate);
    }

    /**
     * Get overtimes for DataTables
     */
    public function getOvertimesForDataTables($startDate = null, $endDate = null, $statusFilter = null)
    {
        $user = auth()->user();
        
        // If user is admin/HR/manager, show all overtimes for their company
        if (in_array($user->role, ['admin', 'hr', 'manager'])) {
            return $this->overtimeRepository->getOvertimesForCompanyDataTables($user->company_id, $startDate, $endDate, $statusFilter);
        }
        
        // If user is employee, show only their own overtimes
        $employee = Employee::where('user_id', $user->id)->first();
        
        if (!$employee) {
            throw new Exception('Employee not found for this user.');
        }

        return $this->overtimeRepository->getOvertimesForEmployeeDataTables($employee->id, $startDate, $endDate, $statusFilter);
    }

    /**
     * Get pending overtimes for approval
     */
    public function getPendingOvertimes(int $perPage = 10)
    {
        $user = auth()->user();
        
        if (!in_array($user->role, ['admin', 'hr', 'manager'])) {
            throw new Exception('You do not have permission to view pending overtimes.');
        }

        return $this->overtimeRepository->getPendingOvertimes($user->company_id, $perPage);
    }

    /**
     * Create new overtime request
     */
    public function createOvertime(array $data)
    {
        $user = auth()->user();
        $employee = Employee::where('user_id', $user->id)->first();
        
        if (!$employee) {
            throw new Exception('Employee not found for this user.');
        }

        // Check for overlapping overtimes
        if ($this->overtimeRepository->hasOverlappingOvertimes($employee->id, $data['date'])) {
            throw new Exception('Anda sudah memiliki permintaan lembur untuk tanggal yang dipilih.');
        }

        // Calculate total hours
        $totalHours = $this->calculateTotalHours($data['date'], $data['start_time'], $data['end_time']);

        // Check overtime limits (max 8 hours per day)
        if ($totalHours > 8) {
            throw new Exception('Lembur tidak boleh melebihi 8 jam per hari.');
        }

        // Handle file upload
        $attachmentPath = null;
        if (isset($data['attachment']) && $data['attachment']) {
            $attachmentPath = $data['attachment']->store('overtimes/attachments', 'public');
        }

        $overtimeData = [
            'employee_id' => $employee->id,
            'company_id' => $user->company_id,
            'overtime_type' => $data['overtime_type'],
            'date' => $data['date'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'total_hours' => $totalHours,
            'reason' => $data['reason'],
            'attachment' => $attachmentPath,
            'status' => 'pending',
        ];

        return $this->overtimeRepository->create($overtimeData);
    }

    /**
     * Update overtime request
     */
    public function updateOvertime(string $overtimeId, array $data)
    {
        $user = auth()->user();
        $overtime = $this->overtimeRepository->findById($overtimeId);
        
        if (!$overtime) {
            throw new Exception('Overtime request not found.');
        }

        // If user is admin/HR/manager, check if overtime is from their company
        if (in_array($user->role, ['admin', 'hr', 'manager'])) {
            if ($overtime->company_id !== $user->company_id) {
                throw new Exception('You can only edit overtimes from your company.');
            }
        } else {
            // If user is employee, check if it's their own overtime
            $employee = Employee::where('user_id', $user->id)->first();
            
            if (!$employee) {
                throw new Exception('Employee not found for this user.');
            }

            if ($overtime->employee_id !== $employee->id) {
                throw new Exception('You can only edit your own overtime requests.');
            }
        }

        if ($overtime->status !== 'pending') {
            throw new Exception('You can only edit pending overtime requests.');
        }

        // Check for overlapping overtimes (excluding current overtime)
        if ($this->overtimeRepository->hasOverlappingOvertimes($overtime->employee_id, $data['date'], $overtimeId)) {
            throw new Exception('Karyawan sudah memiliki permintaan lembur untuk tanggal yang dipilih.');
        }

        // Calculate total hours
        $totalHours = $this->calculateTotalHours($data['date'], $data['start_time'], $data['end_time']);

        // Check overtime limits
        if ($totalHours > 8) {
            throw new Exception('Lembur tidak boleh melebihi 8 jam per hari.');
        }

        // Handle file upload
        $attachmentPath = $overtime->attachment;
        if (isset($data['attachment']) && $data['attachment']) {
            // Delete old file if exists
            if ($attachmentPath && Storage::disk('public')->exists($attachmentPath)) {
                Storage::disk('public')->delete($attachmentPath);
            }
            $attachmentPath = $data['attachment']->store('overtimes/attachments', 'public');
        }

        $updateData = [
            'overtime_type' => $data['overtime_type'],
            'date' => $data['date'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'total_hours' => $totalHours,
            'reason' => $data['reason'],
            'attachment' => $attachmentPath,
        ];

        $this->overtimeRepository->update($overtime, $updateData);
        
        // Return the updated overtime
        return $this->overtimeRepository->findById($overtimeId);
    }

    /**
     * Delete overtime request
     */
    public function deleteOvertime(string $overtimeId)
    {
        $user = auth()->user();
        $employee = Employee::where('user_id', $user->id)->first();
        
        if (!$employee) {
            throw new Exception('Employee not found for this user.');
        }

        $overtime = $this->overtimeRepository->findById($overtimeId);
        
        if (!$overtime) {
            throw new Exception('Overtime request not found.');
        }

        if ($overtime->employee_id !== $employee->id) {
            throw new Exception('You can only delete your own overtime requests.');
        }

        if ($overtime->status !== 'pending') {
            throw new Exception('You can only delete pending overtime requests.');
        }

        // Delete attachment file if exists
        if ($overtime->attachment && Storage::disk('public')->exists($overtime->attachment)) {
            Storage::disk('public')->delete($overtime->attachment);
        }

        return $this->overtimeRepository->delete($overtime);
    }

    /**
     * Approve overtime request
     */
    public function approveOvertime(string $overtimeId, ?string $notes = null)
    {
        $user = auth()->user();
        
        if (!in_array($user->role, ['admin', 'hr', 'manager'])) {
            throw new Exception('You do not have permission to approve overtime requests.');
        }

        $overtime = $this->overtimeRepository->findById($overtimeId);
        
        if (!$overtime) {
            throw new Exception('Overtime request not found.');
        }

        if ($overtime->company_id !== $user->company_id) {
            throw new Exception('You can only approve overtimes from your company.');
        }

        if ($overtime->status !== 'pending') {
            throw new Exception('You can only approve pending overtime requests.');
        }

        return $this->overtimeRepository->approve($overtime, $user->id, $notes);
    }

    /**
     * Reject overtime request
     */
    public function rejectOvertime(string $overtimeId, string $notes)
    {
        $user = auth()->user();
        
        if (!in_array($user->role, ['admin', 'hr', 'manager'])) {
            throw new Exception('You do not have permission to reject overtime requests.');
        }

        $overtime = $this->overtimeRepository->findById($overtimeId);
        
        if (!$overtime) {
            throw new Exception('Overtime request not found.');
        }

        if ($overtime->company_id !== $user->company_id) {
            throw new Exception('You can only reject overtimes from your company.');
        }

        if ($overtime->status !== 'pending') {
            throw new Exception('You can only reject pending overtime requests.');
        }

        return $this->overtimeRepository->reject($overtime, $user->id, $notes);
    }

    /**
     * Find overtime by ID
     */
    public function findOvertimeById(string $id)
    {
        $user = auth()->user();
        $overtime = $this->overtimeRepository->findById($id);
        
        if (!$overtime) {
            throw new Exception('Overtime request not found.');
        }

        // If user is admin/HR/manager, check if overtime is from their company
        if (in_array($user->role, ['admin', 'hr', 'manager'])) {
            if ($overtime->company_id !== $user->company_id) {
                throw new Exception('You can only access overtimes from your company.');
            }
            return $overtime;
        }

        // If user is employee, check if it's their own overtime
        $employee = Employee::where('user_id', $user->id)->first();
        
        if (!$employee) {
            throw new Exception('Employee not found for this user.');
        }

        if ($overtime->employee_id !== $employee->id) {
            throw new Exception('You can only view your own overtime requests.');
        }

        return $overtime;
    }

    /**
     * Get overtime statistics for current user
     */
    public function getOvertimeStatisticsForCurrentUser(int $year = null)
    {
        $user = auth()->user();
        $employee = Employee::where('user_id', $user->id)->first();
        
        if (!$employee) {
            throw new Exception('Employee not found for this user.');
        }

        return $this->overtimeRepository->getOvertimeStatistics($employee->id, $year);
    }

    /**
     * Get overtime statistics for company
     */
    public function getOvertimeStatisticsForCompany(int $year = null)
    {
        $user = auth()->user();
        
        if (!in_array($user->role, ['admin', 'hr', 'manager'])) {
            throw new Exception('You do not have permission to view overtime statistics.');
        }

        return $this->overtimeRepository->getCompanyOvertimeStatistics($user->company_id, $year);
    }

    /**
     * Calculate total hours between start and end time
     */
    private function calculateTotalHours(string $date, string $startTime, string $endTime): int
    {
        $startDateTime = Carbon::parse($date . ' ' . $startTime);
        $endDateTime = Carbon::parse($date . ' ' . $endTime);
        
        // If end time is before start time, it means it's the next day
        if ($endDateTime < $startDateTime) {
            $endDateTime->addDay();
        }
        
        $diffInMinutes = $startDateTime->diffInMinutes($endDateTime);
        $totalHours = max(1, ceil($diffInMinutes / 60));
        
        return $totalHours;
    }
}
