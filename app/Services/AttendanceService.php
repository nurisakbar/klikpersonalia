<?php

namespace App\Services;

use App\Repositories\AttendanceRepository;
use App\Models\Employee;
use App\Models\Attendance;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class AttendanceService
{
    public function __construct(
        private AttendanceRepository $attendanceRepository
    ) {}

    /**
     * Get attendances for DataTables
     */
    public function getAttendancesForDataTables()
    {
        return $this->attendanceRepository->getForDataTables();
    }

    /**
     * Create new attendance
     */
    public function createAttendance(array $data): array
    {
        try {
            DB::beginTransaction();

            // Check if attendance already exists
            if ($this->attendanceRepository->existsForEmployeeAndDate($data['employee_id'], $data['date'])) {
                throw new Exception('Absensi untuk karyawan ini pada tanggal tersebut sudah ada.');
            }

            // Ensure company_id is set based on selected employee
            $employee = Employee::find($data['employee_id']);
            if (!$employee) {
                throw new Exception('Karyawan tidak ditemukan.');
            }
            $data['company_id'] = $employee->company_id;

            $attendance = $this->attendanceRepository->create($data);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Data absensi berhasil ditambahkan!',
                'data' => $attendance
            ];
        } catch (Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Update attendance
     */
    public function updateAttendance(string $id, array $data): array
    {
        try {
            DB::beginTransaction();

            $attendance = $this->attendanceRepository->findById($id);
            if (!$attendance) {
                throw new Exception('Data absensi tidak ditemukan.');
            }

            // Check if attendance already exists (excluding current record)
            if ($this->attendanceRepository->existsForEmployeeAndDate($data['employee_id'], $data['date'], $id)) {
                throw new Exception('Absensi untuk karyawan ini pada tanggal tersebut sudah ada.');
            }
            // Sync company_id if employee changed
            if (!empty($data['employee_id'])) {
                $employee = Employee::find($data['employee_id']);
                if (!$employee) {
                    throw new Exception('Karyawan tidak ditemukan.');
                }
                $data['company_id'] = $employee->company_id;
            }

            $this->attendanceRepository->update($attendance, $data);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Data absensi berhasil diperbarui!',
                'data' => $attendance->fresh()
            ];
        } catch (Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Delete attendance
     */
    public function deleteAttendance(string $id): array
    {
        try {
            DB::beginTransaction();

            $attendance = $this->attendanceRepository->findById($id);
            if (!$attendance) {
                throw new Exception('Data absensi tidak ditemukan.');
            }

            $this->attendanceRepository->delete($attendance);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Data absensi berhasil dihapus!'
            ];
        } catch (Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get current attendance for logged-in user
     */
    public function getCurrentAttendance(): array
    {
        try {
            $user = auth()->user();
            $employee = Employee::where('user_id', $user->id)->first();

            if (!$employee) {
                return [
                    'success' => false,
                    'message' => 'Employee not found for this user.',
                    'employee_id' => null,
                    'attendance' => null
                ];
            }

            $attendance = $this->attendanceRepository->getCurrentAttendance($employee->id);

            return [
                'success' => true,
                'employee_id' => $employee->id,
                'attendance' => $attendance ? [
                    'check_in' => $attendance->check_in ? Carbon::parse($attendance->check_in)->format('H:i') : null,
                    'check_out' => $attendance->check_out ? Carbon::parse($attendance->check_out)->format('H:i') : null,
                    'total_hours' => $attendance->total_hours ? number_format($attendance->total_hours, 2) . ' jam' : null,
                    'overtime_hours' => $attendance->overtime_hours ? number_format($attendance->overtime_hours, 2) . ' jam' : null,
                    'status' => $attendance->status,
                    'date' => $attendance->date->format('d/m/Y')
                ] : null
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'employee_id' => null,
                'attendance' => null
            ];
        }
    }

    /**
     * Get attendance history for logged-in user
     */
    public function getAttendanceHistory(): array
    {
        try {
            $user = auth()->user();
            $employee = Employee::where('user_id', $user->id)->first();

            if (!$employee) {
                return [
                    'success' => false,
                    'message' => 'Employee not found for this user.',
                    'attendance' => []
                ];
            }

            $attendances = $this->attendanceRepository->getAttendanceHistory($employee->id);

            $formattedAttendances = $attendances->map(function ($attendance) {
                return [
                    'date' => $attendance->date->format('d/m/Y'),
                    'check_in' => $attendance->check_in ? Carbon::parse($attendance->check_in)->format('H:i') : '--:--',
                    'check_out' => $attendance->check_out ? Carbon::parse($attendance->check_out)->format('H:i') : '--:--',
                    'total_hours' => $attendance->total_hours ? number_format($attendance->total_hours, 2) . ' jam' : '--:--',
                    'overtime_hours' => $attendance->overtime_hours ? number_format($attendance->overtime_hours, 2) . ' jam' : '--:--',
                    'status' => $attendance->status
                ];
            });

            return [
                'success' => true,
                'attendance' => $formattedAttendances
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'attendance' => []
            ];
        }
    }

    /**
     * Perform check-in
     */
    public function performCheckIn(string $employeeId, string $location = null): array
    {
        try {
            DB::beginTransaction();

            $user = auth()->user();
            $employee = Employee::where('user_id', $user->id)->first();

            if (!$employee || $employee->id != $employeeId) {
                throw new Exception('Unauthorized access.');
            }

            $today = Carbon::today();
            $existingAttendance = $this->attendanceRepository->getCurrentAttendance($employeeId);

            if ($existingAttendance && $existingAttendance->check_in) {
                throw new Exception('Anda sudah melakukan check-in hari ini.');
            }

            $checkInTime = Carbon::now();
            $startTime = Carbon::parse('08:00');
            $status = $checkInTime->gt($startTime) ? 'late' : 'present';

            $data = [
                'check_in' => $checkInTime,
                'status' => $status,
                'check_in_location' => $location ?? 'Location not available',
                'check_in_ip' => request()->ip(),
                'check_in_device' => request()->header('User-Agent'),
            ];

            if ($existingAttendance) {
                $this->attendanceRepository->update($existingAttendance, $data);
            } else {
                $data['employee_id'] = $employeeId;
                $data['company_id'] = $employee->company_id;
                $data['date'] = $today;
                $this->attendanceRepository->create($data);
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Check-in berhasil! Waktu: ' . $checkInTime->format('H:i:s')
            ];
        } catch (Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Perform check-out
     */
    public function performCheckOut(string $employeeId, string $location = null): array
    {
        try {
            DB::beginTransaction();

            $user = auth()->user();
            $employee = Employee::where('user_id', $user->id)->first();

            if (!$employee || $employee->id != $employeeId) {
                throw new Exception('Unauthorized access.');
            }

            $attendance = $this->attendanceRepository->getCurrentAttendance($employeeId);

            if (!$attendance || !$attendance->check_in) {
                throw new Exception('Anda belum melakukan check-in hari ini.');
            }

            if ($attendance->check_out) {
                throw new Exception('Anda sudah melakukan check-out hari ini.');
            }

            $checkOutTime = Carbon::now();
            $checkInTime = Carbon::parse($attendance->check_in);
            
            // Calculate total hours and overtime
            $totalHours = $checkInTime->diffInMinutes($checkOutTime) / 60;
            $overtimeHours = max(0, $totalHours - 8); // 8 hours standard work day

            $this->attendanceRepository->update($attendance, [
                'check_out' => $checkOutTime,
                'check_out_location' => $location ?? 'Location not available',
                'check_out_ip' => request()->ip(),
                'check_out_device' => request()->header('User-Agent'),
                'total_hours' => round($totalHours, 2),
                'overtime_hours' => round($overtimeHours, 2)
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Check-out berhasil! Waktu: ' . $checkOutTime->format('H:i:s') . ' | Total Jam: ' . round($totalHours, 2) . ' jam'
            ];
        } catch (Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
