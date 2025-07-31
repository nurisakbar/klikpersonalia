<?php

namespace App\Http\Controllers;

use App\Models\Overtime;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class OvertimeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get employee for current user
        $employee = Employee::where('user_id', $user->id)->first();
        
        if (!$employee) {
            return redirect()->back()->with('error', 'Employee not found for this user.');
        }

        // Get overtime requests for current employee
        $overtimes = Overtime::where('employee_id', $employee->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('overtimes.index', compact('overtimes'));
    }

    /**
     * Display overtime requests for approval (for managers/HR).
     */
    public function approval()
    {
        $user = Auth::user();
        
        // Check if user has approval rights
        if (!in_array($user->role, ['admin', 'hr', 'manager'])) {
            return redirect()->back()->with('error', 'You do not have permission to approve overtime requests.');
        }

        // Get pending overtime requests
        $pendingOvertimes = Overtime::with(['employee', 'approver'])
            ->where('status', 'pending')
            ->where('company_id', $user->company_id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('overtimes.approval', compact('pendingOvertimes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();
        
        if (!$employee) {
            return redirect()->back()->with('error', 'Employee not found for this user.');
        }

        // Get overtime statistics
        $overtimeStats = $this->getOvertimeStats($employee->id);

        return view('overtimes.create', compact('overtimeStats'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'overtime_type' => 'required|in:regular,holiday,weekend,emergency',
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'reason' => 'required|string|max:500',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();
        
        if (!$employee) {
            return redirect()->back()->with('error', 'Employee not found for this user.');
        }

        // Calculate total hours
        $startTime = Carbon::parse($request->date . ' ' . $request->start_time);
        $endTime = Carbon::parse($request->date . ' ' . $request->end_time);
        $totalHours = $endTime->diffInHours($startTime);

        // Check if overtime is within reasonable limits (max 8 hours per day)
        if ($totalHours > 8) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['end_time' => 'Overtime cannot exceed 8 hours per day.']);
        }

        // Check if overtime already exists for this date
        $existingOvertime = Overtime::where('employee_id', $employee->id)
            ->where('date', $request->date)
            ->where('status', '!=', 'rejected')
            ->first();

        if ($existingOvertime) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['date' => 'You already have an overtime request for this date.']);
        }

        // Handle file upload
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('overtime-attachments', 'public');
        }

        // Create overtime request
        $overtime = Overtime::create([
            'employee_id' => $employee->id,
            'company_id' => $employee->company_id,
            'overtime_type' => $request->overtime_type,
            'date' => $request->date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'total_hours' => $totalHours,
            'reason' => $request->reason,
            'attachment' => $attachmentPath,
            'status' => 'pending',
        ]);

        return redirect()->route('overtimes.index')
            ->with('success', 'Overtime request submitted successfully! It will be reviewed by your manager.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();
        
        if (!$employee) {
            return redirect()->back()->with('error', 'Employee not found for this user.');
        }

        $overtime = Overtime::with(['employee', 'approver'])
            ->where('id', $id)
            ->where('employee_id', $employee->id)
            ->firstOrFail();

        return view('overtimes.show', compact('overtime'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();
        
        if (!$employee) {
            return redirect()->back()->with('error', 'Employee not found for this user.');
        }

        $overtime = Overtime::where('id', $id)
            ->where('employee_id', $employee->id)
            ->where('status', 'pending')
            ->firstOrFail();

        $overtimeStats = $this->getOvertimeStats($employee->id);

        return view('overtimes.edit', compact('overtime', 'overtimeStats'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'overtime_type' => 'required|in:regular,holiday,weekend,emergency',
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'reason' => 'required|string|max:500',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();
        
        if (!$employee) {
            return redirect()->back()->with('error', 'Employee not found for this user.');
        }

        $overtime = Overtime::where('id', $id)
            ->where('employee_id', $employee->id)
            ->where('status', 'pending')
            ->firstOrFail();

        // Calculate total hours
        $startTime = Carbon::parse($request->date . ' ' . $request->start_time);
        $endTime = Carbon::parse($request->date . ' ' . $request->end_time);
        $totalHours = $endTime->diffInHours($startTime);

        // Check if overtime is within reasonable limits
        if ($totalHours > 8) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['end_time' => 'Overtime cannot exceed 8 hours per day.']);
        }

        // Check if overtime already exists for this date (excluding current record)
        $existingOvertime = Overtime::where('employee_id', $employee->id)
            ->where('date', $request->date)
            ->where('id', '!=', $id)
            ->where('status', '!=', 'rejected')
            ->first();

        if ($existingOvertime) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['date' => 'You already have an overtime request for this date.']);
        }

        // Handle file upload
        $attachmentPath = $overtime->attachment;
        if ($request->hasFile('attachment')) {
            // Delete old file if exists
            if ($attachmentPath) {
                \Storage::disk('public')->delete($attachmentPath);
            }
            $attachmentPath = $request->file('attachment')->store('overtime-attachments', 'public');
        }

        // Update overtime request
        $overtime->update([
            'overtime_type' => $request->overtime_type,
            'date' => $request->date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'total_hours' => $totalHours,
            'reason' => $request->reason,
            'attachment' => $attachmentPath,
        ]);

        return redirect()->route('overtimes.index')
            ->with('success', 'Overtime request updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();
        
        if (!$employee) {
            return redirect()->back()->with('error', 'Employee not found for this user.');
        }

        $overtime = Overtime::where('id', $id)
            ->where('employee_id', $employee->id)
            ->where('status', 'pending')
            ->firstOrFail();

        // Delete attachment if exists
        if ($overtime->attachment) {
            \Storage::disk('public')->delete($overtime->attachment);
        }

        $overtime->delete();

        return redirect()->route('overtimes.index')
            ->with('success', 'Overtime request cancelled successfully!');
    }

    /**
     * Approve overtime request.
     */
    public function approve(Request $request, string $id)
    {
        $user = Auth::user();
        
        // Check if user has approval rights
        if (!in_array($user->role, ['admin', 'hr', 'manager'])) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to approve overtime requests.'
            ]);
        }

        $overtime = Overtime::with('employee')
            ->where('id', $id)
            ->where('company_id', $user->company_id)
            ->where('status', 'pending')
            ->firstOrFail();

        $overtime->approve($user->id, $request->approval_notes);

        return response()->json([
            'success' => true,
            'message' => 'Overtime request approved successfully!'
        ]);
    }

    /**
     * Reject overtime request.
     */
    public function reject(Request $request, string $id)
    {
        $user = Auth::user();
        
        // Check if user has approval rights
        if (!in_array($user->role, ['admin', 'hr', 'manager'])) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to reject overtime requests.'
            ]);
        }

        $overtime = Overtime::with('employee')
            ->where('id', $id)
            ->where('company_id', $user->company_id)
            ->where('status', 'pending')
            ->firstOrFail();

        $overtime->reject($user->id, $request->approval_notes);

        return response()->json([
            'success' => true,
            'message' => 'Overtime request rejected successfully!'
        ]);
    }

    /**
     * Get overtime statistics for employee.
     */
    public function statistics()
    {
        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();
        
        if (!$employee) {
            return redirect()->back()->with('error', 'Employee not found for this user.');
        }

        $overtimeStats = $this->getOvertimeStats($employee->id);

        return view('overtimes.statistics', compact('overtimeStats'));
    }

    /**
     * Get overtime statistics for employee.
     */
    private function getOvertimeStats($employeeId)
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        
        // Get approved overtimes for current month
        $approvedOvertimes = Overtime::where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->get();

        // Calculate statistics
        $totalHours = $approvedOvertimes->sum('total_hours');
        $totalRequests = $approvedOvertimes->count();
        $averageHours = $totalRequests > 0 ? round($totalHours / $totalRequests, 2) : 0;

        // Calculate by type
        $regularHours = $approvedOvertimes->where('overtime_type', 'regular')->sum('total_hours');
        $holidayHours = $approvedOvertimes->where('overtime_type', 'holiday')->sum('total_hours');
        $weekendHours = $approvedOvertimes->where('overtime_type', 'weekend')->sum('total_hours');
        $emergencyHours = $approvedOvertimes->where('overtime_type', 'emergency')->sum('total_hours');

        return [
            'total_hours' => $totalHours,
            'total_requests' => $totalRequests,
            'average_hours' => $averageHours,
            'regular_hours' => $regularHours,
            'holiday_hours' => $holidayHours,
            'weekend_hours' => $weekendHours,
            'emergency_hours' => $emergencyHours,
        ];
    }
} 