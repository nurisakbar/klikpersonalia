<?php

namespace App\Http\Controllers;

use App\DataTables\AttendanceDataTable;
use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(AttendanceDataTable $dataTable)
    {
        return $dataTable->render('attendance.index');
    }

    /**
     * Display check-in/out interface.
     */
    public function checkInOut()
    {
        return view('attendance.check-in-out');
    }

    /**
     * Get current attendance status for the logged-in user.
     */
    public function current()
    {
        $user = auth()->user();
        $today = Carbon::today();

        // Find employee associated with the user
        $employee = Employee::where('user_id', $user->id)->first();
        
        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not found for this user.'
            ]);
        }

        $attendance = Attendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->first();

        return response()->json([
            'success' => true,
            'employee_id' => $employee->id,
            'attendance' => $attendance ? [
                'check_in' => $attendance->check_in ? Carbon::parse($attendance->check_in)->format('H:i') : null,
                'check_out' => $attendance->check_out ? Carbon::parse($attendance->check_out)->format('H:i') : null,
                'total_hours' => $attendance->total_hours ? number_format($attendance->total_hours, 2) : null,
                'overtime_hours' => $attendance->overtime_hours ? number_format($attendance->overtime_hours, 2) : null,
                'status' => $attendance->status
            ] : null
        ]);
    }

    /**
     * Get attendance history for the logged-in user.
     */
    public function history()
    {
        $user = auth()->user();
        
        // Find employee associated with the user
        $employee = Employee::where('user_id', $user->id)->first();
        
        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not found for this user.'
            ]);
        }
        
        $attendance = Attendance::where('employee_id', $employee->id)
            ->orderBy('date', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($attendance) {
                return [
                    'date' => $attendance->date->format('d/m/Y'),
                    'check_in' => $attendance->check_in ? Carbon::parse($attendance->check_in)->format('H:i') : null,
                    'check_out' => $attendance->check_out ? Carbon::parse($attendance->check_out)->format('H:i') : null,
                    'total_hours' => $attendance->total_hours ? number_format($attendance->total_hours, 2) : null,
                    'overtime_hours' => $attendance->overtime_hours ? number_format($attendance->overtime_hours, 2) : null,
                    'status' => $attendance->status
                ];
            });

        return response()->json([
            'success' => true,
            'attendance' => $attendance
        ]);
    }

    /**
     * Get attendance data for DataTables.
     */
    public function getData()
    {
        $attendances = Attendance::with('employee')->select([
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

        return DataTables::of($attendances)
            ->addColumn('action', function ($attendance) {
                return '
                    <div class="btn-group" role="group">
                        <a href="' . route('attendance.show', $attendance->id) . '" class="btn btn-sm btn-info" title="Detail">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="' . route('attendance.edit', $attendance->id) . '" class="btn btn-sm btn-warning" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button type="button" class="btn btn-sm btn-danger delete-btn" data-id="' . $attendance->id . '" data-name="' . $attendance->employee->name . ' - ' . $attendance->date->format('d/m/Y') . '" title="Hapus">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                ';
            })
            ->addColumn('employee_name', function ($attendance) {
                return $attendance->employee->name;
            })
            ->addColumn('employee_department', function ($attendance) {
                return $attendance->employee->department;
            })
            ->addColumn('date_formatted', function ($attendance) {
                return $attendance->date->format('d/m/Y');
            })
            ->addColumn('check_in_formatted', function ($attendance) {
                return $attendance->check_in ? Carbon::parse($attendance->check_in)->format('H:i') : '-';
            })
            ->addColumn('check_out_formatted', function ($attendance) {
                return $attendance->check_out ? Carbon::parse($attendance->check_out)->format('H:i') : '-';
            })
            ->addColumn('total_hours_formatted', function ($attendance) {
                return $attendance->total_hours ? $attendance->total_hours . ' jam' : '-';
            })
            ->addColumn('overtime_hours_formatted', function ($attendance) {
                return $attendance->overtime_hours ? $attendance->overtime_hours . ' jam' : '-';
            })
            ->addColumn('status_badge', function ($attendance) {
                $statusClass = [
                    'present' => 'badge badge-success',
                    'absent' => 'badge badge-danger',
                    'late' => 'badge badge-warning',
                    'half_day' => 'badge badge-info',
                    'leave' => 'badge badge-secondary',
                    'holiday' => 'badge badge-primary'
                ];
                
                $statusText = [
                    'present' => 'Hadir',
                    'absent' => 'Tidak Hadir',
                    'late' => 'Terlambat',
                    'half_day' => 'Setengah Hari',
                    'leave' => 'Cuti',
                    'holiday' => 'Libur'
                ];
                
                return '<span class="' . $statusClass[$attendance->status] . '">' . $statusText[$attendance->status] . '</span>';
            })
            ->rawColumns(['action', 'status_badge'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $employees = Employee::active()->get();
        return view('attendance.create', compact('employees'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'check_in' => 'nullable|date_format:H:i',
            'check_out' => 'nullable|date_format:H:i|after:check_in',
            'status' => 'required|in:present,absent,late,half_day,leave,holiday',
            'notes' => 'nullable|string|max:500',
        ]);

        // Check if attendance already exists for this employee and date
        $existingAttendance = Attendance::where('employee_id', $request->employee_id)
            ->where('date', $request->date)
            ->first();

        if ($existingAttendance) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['date' => 'Absensi untuk karyawan ini pada tanggal tersebut sudah ada.']);
        }

        $attendance = Attendance::create([
            'employee_id' => $request->employee_id,
            'date' => $request->date,
            'check_in' => $request->check_in,
            'check_out' => $request->check_out,
            'status' => $request->status,
            'notes' => $request->notes,
            'check_in_location' => $request->check_in_location,
            'check_out_location' => $request->check_out_location,
            'check_in_ip' => $request->ip(),
            'check_out_ip' => $request->ip(),
            'check_in_device' => $request->user_agent(),
            'check_out_device' => $request->user_agent(),
        ]);

        // Calculate total hours if check-in and check-out are provided
        if ($attendance->check_in && $attendance->check_out) {
            $attendance->calculateTotalHours();
        }

        return redirect()->route('attendance.index')
            ->with('success', 'Data absensi berhasil ditambahkan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $attendance = Attendance::with('employee')->findOrFail($id);
        return view('attendance.show', compact('attendance'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $attendance = Attendance::findOrFail($id);
        $employees = Employee::active()->get();
        return view('attendance.edit', compact('attendance', 'employees'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'check_in' => 'nullable|date_format:H:i',
            'check_out' => 'nullable|date_format:H:i|after:check_in',
            'status' => 'required|in:present,absent,late,half_day,leave,holiday',
            'notes' => 'nullable|string|max:500',
        ]);

        $attendance = Attendance::findOrFail($id);

        // Check if attendance already exists for this employee and date (excluding current record)
        $existingAttendance = Attendance::where('employee_id', $request->employee_id)
            ->where('date', $request->date)
            ->where('id', '!=', $id)
            ->first();

        if ($existingAttendance) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['date' => 'Absensi untuk karyawan ini pada tanggal tersebut sudah ada.']);
        }

        $attendance->update([
            'employee_id' => $request->employee_id,
            'date' => $request->date,
            'check_in' => $request->check_in,
            'check_out' => $request->check_out,
            'status' => $request->status,
            'notes' => $request->notes,
        ]);

        // Calculate total hours if check-in and check-out are provided
        if ($attendance->check_in && $attendance->check_out) {
            $attendance->calculateTotalHours();
        }

        return redirect()->route('attendance.index')
            ->with('success', 'Data absensi berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $attendance = Attendance::findOrFail($id);
            $attendance->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Data absensi berhasil dihapus!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data absensi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check-in employee.
     */
    public function checkIn(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
        ]);

        $user = auth()->user();
        
        // Find employee associated with the user
        $employee = Employee::where('user_id', $user->id)->first();
        
        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not found for this user.'
            ]);
        }

        // Verify that the employee_id matches the logged-in user's employee record
        if ($employee->id != $request->employee_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access.'
            ]);
        }

        $today = Carbon::today();

        // Check if already checked in today
        $existingAttendance = Attendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->first();

        if ($existingAttendance && $existingAttendance->check_in) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan check-in hari ini.'
            ]);
        }

        $checkInTime = Carbon::now();
        $startTime = Carbon::parse('08:00');
        $status = $checkInTime->gt($startTime) ? 'late' : 'present';

        if ($existingAttendance) {
            $existingAttendance->update([
                'check_in' => $checkInTime,
                'status' => $status,
                'check_in_location' => $request->location ?? 'Location not available',
                'check_in_ip' => $request->ip(),
                'check_in_device' => $request->user_agent(),
            ]);
        } else {
            Attendance::create([
                'employee_id' => $employee->id,
                'company_id' => $employee->company_id,
                'date' => $today,
                'check_in' => $checkInTime,
                'status' => $status,
                'check_in_location' => $request->location ?? 'Location not available',
                'check_in_ip' => $request->ip(),
                'check_in_device' => $request->user_agent(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Check-in berhasil! Waktu: ' . $checkInTime->format('H:i:s')
        ]);
    }

    /**
     * Check-out employee.
     */
    public function checkOut(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
        ]);

        $user = auth()->user();
        
        // Find employee associated with the user
        $employee = Employee::where('user_id', $user->id)->first();
        
        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not found for this user.'
            ]);
        }

        // Verify that the employee_id matches the logged-in user's employee record
        if ($employee->id != $request->employee_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access.'
            ]);
        }

        $today = Carbon::today();

        $attendance = Attendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->first();

        if (!$attendance || !$attendance->check_in) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum melakukan check-in hari ini.'
            ]);
        }

        if ($attendance->check_out) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan check-out hari ini.'
            ]);
        }

        $checkOutTime = Carbon::now();
        
        $attendance->update([
            'check_out' => $checkOutTime,
            'check_out_location' => $request->location ?? 'Location not available',
            'check_out_ip' => $request->ip(),
            'check_out_device' => $request->user_agent(),
        ]);

        // Calculate total hours
        $attendance->calculateTotalHours();

        return response()->json([
            'success' => true,
            'message' => 'Check-out berhasil! Waktu: ' . $checkOutTime->format('H:i:s')
        ]);
    }
}
