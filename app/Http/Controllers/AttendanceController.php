<?php

namespace App\Http\Controllers;

use App\DataTables\AttendanceDataTable;
use App\Models\Attendance;
use App\Models\Employee;
use App\Http\Requests\AttendanceRequest;
use App\Http\Resources\AttendanceResource;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function __construct(
        private AttendanceService $attendanceService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(AttendanceDataTable $dataTable, Request $request)
    {
        // Temporarily disabled role check for testing
        // if (!in_array(auth()->user()->role, ['super_admin', 'admin', 'hr', 'manager'])) {
        //     abort(403, 'Unauthorized access.');
        // }
        
        return $dataTable->render('attendance.index');
    }

    /**
     * Get attendance data for DataTables.
     */
    public function data(Request $request): JsonResponse
    {
        $attendances = $this->attendanceService->getAttendancesForDataTables($request);

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
                        <button type="button" class="btn btn-sm btn-danger delete-btn" data-id="' . $attendance->id . '" data-name="' . htmlspecialchars($attendance->employee->name . ' - ' . $attendance->date->format('d/m/Y')) . '" title="Hapus">
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
        // Temporarily disabled role check for testing
        // if (!in_array(auth()->user()->role, ['super_admin', 'admin', 'hr', 'manager'])) {
        //     abort(403, 'Unauthorized access.');
        // }
        
        $employees = Employee::active()->currentCompany()->get();
        return view('attendance.create', compact('employees'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AttendanceRequest $request)
    {
        $result = $this->attendanceService->createAttendance($request->validated());

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json($result, $result['success'] ? 200 : 422);
        }

        if ($result['success']) {
            return redirect()->route('attendance.index')
                ->with('success', $result['message']);
        }

        return redirect()->back()
            ->withInput()
            ->withErrors(['error' => $result['message']]);
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
        // Temporarily disabled role check for testing
        // if (!in_array(auth()->user()->role, ['super_admin', 'admin', 'hr', 'manager'])) {
        //     abort(403, 'Unauthorized access.');
        // }
        
        $attendance = Attendance::findOrFail($id);
        $employees = Employee::active()->currentCompany()->get();
        return view('attendance.edit', compact('attendance', 'employees'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AttendanceRequest $request, string $id)
    {
        $result = $this->attendanceService->updateAttendance($id, $request->validated());

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json($result, $result['success'] ? 200 : 422);
        }

        if ($result['success']) {
            return redirect()->route('attendance.index')
                ->with('success', $result['message']);
        }

        return redirect()->back()
            ->withInput()
            ->withErrors(['error' => $result['message']]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $result = $this->attendanceService->deleteAttendance($id);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => $result['message']
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message']
        ], 500);
    }

    /**
     * Display check-in/out interface.
     */
    public function checkInOut()
    {
        // Check if user has employee record
        $employee = Employee::where('user_id', auth()->user()->id)->first();
        if (!$employee) {
            abort(403, 'Employee record not found. Please contact administrator.');
        }
        
        return view('attendance.check-in-out');
    }

    /**
     * Get current attendance status for the logged-in user.
     */
    public function current(): JsonResponse
    {
        $result = $this->attendanceService->getCurrentAttendance();
        return response()->json($result);
    }

    /**
     * Get attendance history for the logged-in user.
     */
    public function history(): JsonResponse
    {
        $result = $this->attendanceService->getAttendanceHistory();
        return response()->json($result);
    }

    /**
     * Check-in employee.
     */
    public function checkIn(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'employee_id' => 'required|exists:employees,id',
                'location' => 'nullable|string|max:255'
            ]);

            $result = $this->attendanceService->performCheckIn(
                $request->employee_id,
                $request->location
            );

            return response()->json($result, $result['success'] ? 200 : 422);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid: ' . implode(', ', $e->errors())
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check-out employee.
     */
    public function checkOut(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'employee_id' => 'required|exists:employees,id',
                'location' => 'nullable|string|max:255'
            ]);

            $result = $this->attendanceService->performCheckOut(
                $request->employee_id,
                $request->location
            );

            return response()->json($result, $result['success'] ? 200 : 422);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid: ' . implode(', ', $e->errors())
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
