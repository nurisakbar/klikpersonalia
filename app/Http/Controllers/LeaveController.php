<?php

namespace App\Http\Controllers;

use App\Models\Leave;
use App\Models\Employee;
use App\Models\User;
use App\Services\LeaveService;
use App\Http\Requests\LeaveRequest;
use App\Http\Requests\LeaveApprovalRequest;
use App\Http\Resources\LeaveResource;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\JsonResponse;

class LeaveController extends Controller
{
    protected $leaveService;

    public function __construct(LeaveService $leaveService)
    {
        $this->leaveService = $leaveService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            return view('leaves.index');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Get leaves data for DataTables.
     */
    public function data(): JsonResponse
    {
        $leaves = $this->leaveService->getLeavesForDataTables();

        return DataTables::of($leaves)
            ->addColumn('action', function ($leave) {
                $buttons = '<div class="btn-group" role="group">';
                $buttons .= '<button type="button" class="btn btn-sm btn-info view-btn" data-id="' . $leave->id . '" title="Detail"><i class="fas fa-eye"></i></button>';
                
                if ($leave->status === 'pending') {
                    $buttons .= '<button type="button" class="btn btn-sm btn-warning edit-btn" data-id="' . $leave->id . '" title="Edit"><i class="fas fa-edit"></i></button>';
                    $buttons .= '<button type="button" class="btn btn-sm btn-danger delete-btn" data-id="' . $leave->id . '" data-name="' . htmlspecialchars($leave->leave_type . ' cuti dari ' . $leave->formatted_start_date . ' sampai ' . $leave->formatted_end_date) . '" title="Batalkan"><i class="fas fa-times"></i></button>';
                }
                
                $buttons .= '</div>';
                return $buttons;
            })
            ->addColumn('type_badge', function ($leave) {
                return $leave->type_badge;
            })
            ->addColumn('status_badge', function ($leave) {
                return $leave->status_badge;
            })
            ->addColumn('start_date', function ($leave) {
                return $leave->formatted_start_date;
            })
            ->addColumn('end_date', function ($leave) {
                return $leave->formatted_end_date;
            })
            ->addColumn('total_days_formatted', function ($leave) {
                return $leave->total_days . ' hari';
            })
            ->addColumn('created_at_formatted', function ($leave) {
                return $leave->created_at->format('d/m/Y H:i');
            })
            ->rawColumns(['action', 'type_badge', 'status_badge', 'total_days_formatted', 'created_at_formatted'])
            ->make(true);
    }

    /**
     * Display leave requests for approval (for managers/HR).
     */
    public function approval()
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return redirect()->route('login')->with('error', 'Please login first.');
            }
            
            if (!in_array($user->role, ['admin', 'hr', 'manager'])) {
                return redirect()->back()->with('error', 'You do not have permission to approve leave requests.');
            }

            return view('leaves.approval');
        } catch (\Exception $e) {
            // Log error for debugging
            \Log::error('Leave approval error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error loading leave approval: ' . $e->getMessage());
        }
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
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not found for this user.'
                ], 404);
            }
            return redirect()->back()->with('error', 'Employee not found for this user.');
        }

        $leave = Leave::with(['employee', 'approver'])
            ->where('id', $id)
            ->where('employee_id', $employee->id)
            ->first();

        if (!$leave) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Leave request not found.'
                ], 404);
            }
            abort(404);
        }

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $leave->id,
                    'leave_type' => $leave->leave_type,
                    'type_badge' => $leave->type_badge,
                    'start_date' => $leave->start_date,
                    'formatted_start_date' => $leave->formatted_start_date,
                    'end_date' => $leave->end_date,
                    'formatted_end_date' => $leave->formatted_end_date,
                    'total_days' => $leave->total_days,
                    'reason' => $leave->reason,
                    'status' => $leave->status,
                    'status_badge' => $leave->status_badge,
                    'attachment_path' => $leave->attachment,
                    'created_at' => $leave->created_at,
                    'created_at_formatted' => $leave->created_at->format('d/m/Y H:i'),
                    'approver_name' => $leave->approver ? $leave->approver->name : null,
                    'approved_at' => $leave->approved_at,
                    'approved_at_formatted' => $leave->approved_at ? $leave->approved_at->format('d/m/Y H:i') : null,
                    'approval_notes' => $leave->approval_notes,
                ]
            ]);
        }

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
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not found for this user.'
                ], 404);
            }
            return redirect()->back()->with('error', 'Employee not found for this user.');
        }

        $leave = Leave::where('id', $id)
            ->where('employee_id', $employee->id)
            ->where('status', 'pending')
            ->first();

        if (!$leave) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Leave request not found or cannot be cancelled.'
                ], 404);
            }
            return redirect()->back()->with('error', 'Leave request not found or cannot be cancelled.');
        }

        // Update status to cancelled instead of deleting
        $leave->update([
            'status' => 'cancelled',
            'approved_by' => $user->id,
            'approved_at' => now(),
            'approval_notes' => 'Dibatalkan oleh pemohon'
        ]);

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Permintaan cuti berhasil dibatalkan!'
            ]);
        }

        return redirect()->route('leaves.index')
            ->with('success', 'Permintaan cuti berhasil dibatalkan!');
    }

    /**
     * Approve leave request.
     */
    public function approve(LeaveApprovalRequest $request, string $id)
    {
        try {
            $this->leaveService->approveLeave($id, $request->approval_notes);
            
            return response()->json([
                'success' => true,
                'message' => 'Leave request approved successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Reject leave request.
     */
    public function reject(LeaveApprovalRequest $request, string $id)
    {
        try {
            $this->leaveService->rejectLeave($id, $request->approval_notes);
            
            return response()->json([
                'success' => true,
                'message' => 'Leave request rejected successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Get leave balance for employee.
     */
    public function balance()
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
            }
            
            $employee = Employee::where('user_id', $user->id)->first();
            
            if (!$employee) {
                return redirect()->back()->with('error', 'Data karyawan tidak ditemukan.');
            }

            // Get leave balance
            $leaveBalance = $this->getLeaveBalance($employee->id);
            
            // Get leave history
            $leaveHistory = Leave::where('employee_id', $employee->id)
                ->orderBy('created_at', 'desc')
                ->paginate(10);
            
            return view('leaves.balance', compact('leaveBalance', 'leaveHistory'));
        } catch (\Exception $e) {
            \Log::error('Leave balance error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan. Silakan coba lagi.');
        }
    }

    /**
     * Get leave balance for employee (fallback method).
     */
    public function getLeaveBalance(string $employeeId)
    {
        try {
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
        } catch (\Exception $e) {
            \Log::error('Error in getLeaveBalance: ' . $e->getMessage());
            \Log::error('Employee ID: ' . $employeeId);
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            // Return default values if there's an error
            return [
                'annual_total' => 12,
                'annual_used' => 0,
                'annual_remaining' => 12,
                'sick_total' => 12,
                'sick_used' => 0,
                'sick_remaining' => 12,
                'maternity_total' => 90,
                'maternity_used' => 0,
                'maternity_remaining' => 90,
                'paternity_total' => 2,
                'paternity_used' => 0,
                'paternity_remaining' => 2,
                'other_total' => 5,
                'other_used' => 0,
                'other_remaining' => 5,
            ];
        }
    }

    /**
     * API: Get leaves for current user
     */
    public function apiIndex()
    {
        try {
            $leaves = $this->leaveService->getLeavesForCurrentUser(10);
            return LeaveResource::collection($leaves);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }



    /**
     * Get approval data for DataTables
     */
    public function approvalData(): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                \Log::error('User not authenticated in approvalData');
                return response()->json(['error' => 'User not authenticated'], 401);
            }
            
            $company = $user->company;
            
            if (!$company) {
                \Log::error('Company not found for user: ' . $user->id);
                return response()->json(['error' => 'Company not found'], 404);
            }

            // Debug: Log the query
            \Log::info('Leave approval data request for company: ' . $company->id);

            $leaves = Leave::with('employee')
                ->where('company_id', $company->id)
                ->where('status', 'pending');

            // Debug: Log the count
            $count = $leaves->count();
            \Log::info('Found ' . $count . ' pending leaves for company: ' . $company->id);

            // If no data found, return empty DataTable response
            if ($count === 0) {
                \Log::info('No pending leaves found, returning empty response');
                return DataTables::of(collect([]))
                    ->addColumn('employee_info', function ($leave) {
                        return 'No data';
                    })
                    ->addColumn('leave_type_badge', function ($leave) {
                        return 'No data';
                    })
                    ->addColumn('start_date_formatted', function ($leave) {
                        return 'No data';
                    })
                    ->addColumn('end_date_formatted', function ($leave) {
                        return 'No data';
                    })
                    ->addColumn('created_at_formatted', function ($leave) {
                        return 'No data';
                    })
                    ->addColumn('action', function ($leave) {
                        return 'No data';
                    })
                    ->rawColumns(['leave_type_badge', 'action'])
                    ->make(true);
            }

            // Get the actual data
            $leavesData = $leaves->get();
            \Log::info('Retrieved ' . $leavesData->count() . ' leaves from database');

            // Transform data for client-side processing
            $transformedData = $leavesData->map(function ($leave) {
                try {
                    $badges = [
                        'annual' => '<span class="badge badge-primary">Cuti Tahunan</span>',
                        'sick' => '<span class="badge badge-danger">Cuti Sakit</span>',
                        'maternity' => '<span class="badge badge-success">Cuti Melahirkan</span>',
                        'paternity' => '<span class="badge badge-secondary">Cuti Melahirkan (Suami)</span>',
                        'other' => '<span class="badge badge-warning">Cuti Lainnya</span>'
                    ];

                    return [
                        'employee_info' => $leave->employee->name . ' (' . $leave->employee->employee_id . ')',
                        'leave_type_badge' => $badges[$leave->leave_type] ?? '<span class="badge badge-secondary">Unknown</span>',
                        'start_date_formatted' => date('d/m/Y', strtotime($leave->start_date)),
                        'end_date_formatted' => date('d/m/Y', strtotime($leave->end_date)),
                        'total_days' => $leave->total_days,
                        'reason' => $leave->reason,
                        'created_at_formatted' => date('d/m/Y H:i', strtotime($leave->created_at)),
                        'action' => '
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-success approve-btn" 
                                        data-id="' . $leave->id . '" 
                                        data-employee="' . htmlspecialchars($leave->employee->name) . '"
                                        data-type="' . $leave->leave_type . '"
                                        data-days="' . $leave->total_days . '"
                                        title="Approve">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger reject-btn" 
                                        data-id="' . $leave->id . '" 
                                        data-employee="' . htmlspecialchars($leave->employee->name) . '"
                                        data-type="' . $leave->leave_type . '"
                                        data-days="' . $leave->total_days . '"
                                        title="Reject">
                                    <i class="fas fa-times"></i>
                                </button>
                                <a href="' . route('leaves.show', $leave->id) . '" class="btn btn-sm btn-info" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        '
                    ];
                } catch (\Exception $e) {
                    \Log::error('Error transforming leave data for ID ' . $leave->id . ': ' . $e->getMessage());
                    return [
                        'employee_info' => 'Error loading employee',
                        'leave_type_badge' => '<span class="badge badge-secondary">Error</span>',
                        'start_date_formatted' => 'Error',
                        'end_date_formatted' => 'Error',
                        'total_days' => 0,
                        'reason' => 'Error loading data',
                        'created_at_formatted' => 'Error',
                        'action' => 'Error generating actions'
                    ];
                }
            });

            \Log::info('Data transformed successfully, returning ' . $transformedData->count() . ' records');
            
            return response()->json([
                'data' => $transformedData
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in approvalData: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'error' => 'Internal server error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Get specific leave
     */
    public function apiShow($id)
    {
        try {
            $leave = $this->leaveService->findLeaveById($id);
            return new LeaveResource($leave);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * API: Store new leave
     */
    public function apiStore(LeaveRequest $request)
    {
        try {
            $leave = $this->leaveService->createLeave($request->validated());
            return response()->json([
                'success' => true,
                'message' => 'Leave request submitted successfully!',
                'data' => new LeaveResource($leave)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * API: Update leave
     */
    public function apiUpdate(LeaveRequest $request, $id)
    {
        try {
            $this->leaveService->updateLeave($id, $request->validated());
            return response()->json([
                'success' => true,
                'message' => 'Leave request updated successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * API: Delete leave
     */
    public function apiDestroy($id)
    {
        try {
            $this->leaveService->deleteLeave($id);
            return response()->json([
                'success' => true,
                'message' => 'Leave request cancelled successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
} 