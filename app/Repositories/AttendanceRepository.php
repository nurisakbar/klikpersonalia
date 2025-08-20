<?php

namespace App\Repositories;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

class AttendanceRepository
{
    public function __construct(
        private Attendance $model
    ) {}

    /**
     * Get all attendances with pagination
     */
    public function getAllPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->currentCompany()
            ->with('employee')
            ->orderBy('date', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get attendances for DataTables
     */
    public function getForDataTables($request = null)
    {
        $query = $this->model
            ->currentCompany()
            ->with('employee')
            ->select([
                'id',
                'employee_id',
                'date',
                'check_in',
                'check_out',
                'total_hours',
                'overtime_hours',
                'status',
                'notes'
            ]);

        // Apply date filter
        if ($request && $request->get('date_filter')) {
            $dateFilter = $request->get('date_filter');
            if (preg_match('/^(\d{4})-(\d{2})$/', $dateFilter, $matches)) {
                $year = $matches[1];
                $month = $matches[2];
                $query->whereYear('date', $year)
                      ->whereMonth('date', $month);
            }
        }

        // Apply status filter
        if ($request && $request->get('status_filter')) {
            $query->where('status', $request->get('status_filter'));
        }

        // Apply global search
        if ($request && $request->get('search') && $request->get('search')['value']) {
            $searchValue = $request->get('search')['value'];
            $query->where(function($q) use ($searchValue) {
                $q->whereHas('employee', function($employeeQuery) use ($searchValue) {
                    $employeeQuery->where('name', 'like', '%' . $searchValue . '%')
                                 ->orWhere('employee_id', 'like', '%' . $searchValue . '%')
                                 ->orWhere('department', 'like', '%' . $searchValue . '%');
                });
            });
        }

        return $query;
    }

    /**
     * Find attendance by ID
     */
    public function findById(string $id): ?Attendance
    {
        return $this->model->with('employee')->find($id);
    }

    /**
     * Create new attendance
     */
    public function create(array $data): Attendance
    {
        $attendance = $this->model->create($data);

        // Calculate total hours if check-in and check-out are provided
        if ($attendance->check_in && $attendance->check_out) {
            $this->calculateTotalHours($attendance);
        }

        return $attendance;
    }

    /**
     * Update attendance
     */
    public function update(Attendance $attendance, array $data): bool
    {
        $updated = $attendance->update($data);

        // Calculate total hours if check-in and check-out are provided
        if ($attendance->check_in && $attendance->check_out) {
            $this->calculateTotalHours($attendance);
        }

        return $updated;
    }

    /**
     * Delete attendance
     */
    public function delete(Attendance $attendance): bool
    {
        return $attendance->delete();
    }

    /**
     * Check if attendance exists for employee and date
     */
    public function existsForEmployeeAndDate(string $employeeId, string $date, ?string $excludeId = null): bool
    {
        $query = $this->model
            ->where('employee_id', $employeeId)
            ->where('date', $date);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Get current attendance for employee
     */
    public function getCurrentAttendance(string $employeeId): ?Attendance
    {
        return $this->model
            ->where('employee_id', $employeeId)
            ->where('date', Carbon::today())
            ->first();
    }

    /**
     * Get attendance history for employee
     */
    public function getAttendanceHistory(string $employeeId, int $limit = 10): Collection
    {
        return $this->model
            ->where('employee_id', $employeeId)
            ->orderBy('date', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Calculate total hours and overtime for attendance
     */
    private function calculateTotalHours(Attendance $attendance): void
    {
        if (!$attendance->check_in || !$attendance->check_out) {
            return;
        }

        $checkIn = Carbon::parse($attendance->check_in);
        $checkOut = Carbon::parse($attendance->check_out);

        if ($checkOut <= $checkIn) {
            return;
        }

        $totalHours = $checkIn->diffInMinutes($checkOut) / 60;
        $overtimeHours = max(0, $totalHours - 8); // 8 hours standard work day

        $attendance->update([
            'total_hours' => round($totalHours, 2),
            'overtime_hours' => round($overtimeHours, 2)
        ]);
    }

    /**
     * Get attendance statistics
     */
    public function getStatistics(string $employeeId, string $startDate, string $endDate): array
    {
        $attendances = $this->model
            ->where('employee_id', $employeeId)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        return [
            'total_days' => $attendances->count(),
            'present_days' => $attendances->where('status', 'present')->count(),
            'absent_days' => $attendances->where('status', 'absent')->count(),
            'late_days' => $attendances->where('status', 'late')->count(),
            'leave_days' => $attendances->where('status', 'leave')->count(),
            'total_hours' => $attendances->sum('total_hours'),
            'overtime_hours' => $attendances->sum('overtime_hours'),
        ];
    }
}
