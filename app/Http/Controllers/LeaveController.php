<?php

namespace App\Http\Controllers;

use App\Models\Leave;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class LeaveController extends Controller
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

        // Get leaves for current employee
        $leaves = Leave::where('employee_id', $employee->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('leaves.index', compact('leaves'));
    }

    /**
     * Display leave requests for approval (for managers/HR).
     */
    public function approval()
    {
        $user = Auth::user();
        
        // Check if user has approval rights
        if (!in_array($user->role, ['admin', 'hr', 'manager'])) {
            return redirect()->back()->with('error', 'You do not have permission to approve leave requests.');
        }

        // Get pending leave requests
        $pendingLeaves = Leave::with(['employee', 'approver'])
            ->where('status', 'pending')
            ->where('company_id', $user->company_id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('leaves.approval', compact('pendingLeaves'));
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

        // Get leave balance
        $leaveBalance = $this->getLeaveBalance($employee->id);

        return view('leaves.create', compact('leaveBalance'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'leave_type' => 'required|in:annual,sick,maternity,paternity,other',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:500',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();
        
        if (!$employee) {
            return redirect()->back()->with('error', 'Employee not found for this user.');
        }

        // Calculate total days
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $totalDays = $this->calculateWorkingDays($startDate, $endDate);

        // Check leave balance for annual leave
        if ($request->leave_type === 'annual') {
            $leaveBalance = $this->getLeaveBalance($employee->id);
            if ($totalDays > $leaveBalance['annual_remaining']) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['end_date' => 'Insufficient annual leave balance. You have ' . $leaveBalance['annual_remaining'] . ' days remaining.']);
            }
        }

        // Handle file upload
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('leave-attachments', 'public');
        }

        // Create leave request
        $leave = Leave::create([
            'employee_id' => $employee->id,
            'company_id' => $employee->company_id,
            'leave_type' => $request->leave_type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'total_days' => $totalDays,
            'reason' => $request->reason,
            'attachment' => $attachmentPath,
            'status' => 'pending',
        ]);

        return redirect()->route('leaves.index')
            ->with('success', 'Leave request submitted successfully! It will be reviewed by your manager.');
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

        $leave = Leave::with(['employee', 'approver'])
            ->where('id', $id)
            ->where('employee_id', $employee->id)
            ->firstOrFail();

        return view('leaves.show', compact('leave'));
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

        $leave = Leave::where('id', $id)
            ->where('employee_id', $employee->id)
            ->where('status', 'pending')
            ->firstOrFail();

        $leaveBalance = $this->getLeaveBalance($employee->id);

        return view('leaves.edit', compact('leave', 'leaveBalance'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'leave_type' => 'required|in:annual,sick,maternity,paternity,other',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:500',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();
        
        if (!$employee) {
            return redirect()->back()->with('error', 'Employee not found for this user.');
        }

        $leave = Leave::where('id', $id)
            ->where('employee_id', $employee->id)
            ->where('status', 'pending')
            ->firstOrFail();

        // Calculate total days
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $totalDays = $this->calculateWorkingDays($startDate, $endDate);

        // Check leave balance for annual leave
        if ($request->leave_type === 'annual') {
            $leaveBalance = $this->getLeaveBalance($employee->id);
            $currentLeaveDays = $leave->leave_type === 'annual' ? $leave->total_days : 0;
            if (($totalDays - $currentLeaveDays) > $leaveBalance['annual_remaining']) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['end_date' => 'Insufficient annual leave balance. You have ' . $leaveBalance['annual_remaining'] . ' days remaining.']);
            }
        }

        // Handle file upload
        $attachmentPath = $leave->attachment;
        if ($request->hasFile('attachment')) {
            // Delete old file if exists
            if ($attachmentPath) {
                \Storage::disk('public')->delete($attachmentPath);
            }
            $attachmentPath = $request->file('attachment')->store('leave-attachments', 'public');
        }

        // Update leave request
        $leave->update([
            'leave_type' => $request->leave_type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'total_days' => $totalDays,
            'reason' => $request->reason,
            'attachment' => $attachmentPath,
        ]);

        return redirect()->route('leaves.index')
            ->with('success', 'Leave request updated successfully!');
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

        $leave = Leave::where('id', $id)
            ->where('employee_id', $employee->id)
            ->where('status', 'pending')
            ->firstOrFail();

        // Delete attachment if exists
        if ($leave->attachment) {
            \Storage::disk('public')->delete($leave->attachment);
        }

        $leave->delete();

        return redirect()->route('leaves.index')
            ->with('success', 'Leave request cancelled successfully!');
    }

    /**
     * Approve leave request.
     */
    public function approve(Request $request, string $id)
    {
        $user = Auth::user();
        
        // Check if user has approval rights
        if (!in_array($user->role, ['admin', 'hr', 'manager'])) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to approve leave requests.'
            ]);
        }

        $leave = Leave::with('employee')
            ->where('id', $id)
            ->where('company_id', $user->company_id)
            ->where('status', 'pending')
            ->firstOrFail();

        $leave->approve($user->id, $request->approval_notes);

        return response()->json([
            'success' => true,
            'message' => 'Leave request approved successfully!'
        ]);
    }

    /**
     * Reject leave request.
     */
    public function reject(Request $request, string $id)
    {
        $user = Auth::user();
        
        // Check if user has approval rights
        if (!in_array($user->role, ['admin', 'hr', 'manager'])) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to reject leave requests.'
            ]);
        }

        $leave = Leave::with('employee')
            ->where('id', $id)
            ->where('company_id', $user->company_id)
            ->where('status', 'pending')
            ->firstOrFail();

        $leave->reject($user->id, $request->approval_notes);

        return response()->json([
            'success' => true,
            'message' => 'Leave request rejected successfully!'
        ]);
    }

    /**
     * Get leave balance for employee.
     */
    public function balance()
    {
        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();
        
        if (!$employee) {
            return redirect()->back()->with('error', 'Employee not found for this user.');
        }

        $leaveBalance = $this->getLeaveBalance($employee->id);

        return view('leaves.balance', compact('leaveBalance'));
    }

    /**
     * Calculate working days between two dates.
     */
    private function calculateWorkingDays($startDate, $endDate)
    {
        $totalDays = 0;
        $currentDate = $startDate->copy();
        
        while ($currentDate->lte($endDate)) {
            if (!$currentDate->isWeekend()) {
                $totalDays++;
            }
            $currentDate->addDay();
        }
        
        return $totalDays;
    }

    /**
     * Get leave balance for employee.
     */
    private function getLeaveBalance($employeeId)
    {
        $currentYear = Carbon::now()->year;
        
        // Get approved leaves for current year
        $approvedLeaves = Leave::where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->whereYear('start_date', $currentYear)
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
} 