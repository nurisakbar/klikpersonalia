<?php

namespace App\Http\Controllers;

use App\Models\Overtime;
use App\Models\Employee;
use App\Models\User;
use App\Services\OvertimeService;
use App\Http\Requests\OvertimeRequest;
use App\Http\Requests\OvertimeApprovalRequest;
use App\Http\Resources\OvertimeResource;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\JsonResponse;

class OvertimeController extends Controller
{
    protected $overtimeService;

    public function __construct(OvertimeService $overtimeService)
    {
        $this->overtimeService = $overtimeService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            return view('overtimes.index');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Get overtimes data for DataTables.
     */
    public function data(): JsonResponse
    {
        $startDate = request('start_date');
        $endDate = request('end_date');
        $statusFilter = request('status_filter');
        
        $overtimes = $this->overtimeService->getOvertimesForDataTables($startDate, $endDate, $statusFilter);
        $user = Auth::user();
        $isAdmin = in_array($user->role, ['admin', 'hr', 'manager']);



        return DataTables::of($overtimes)
            ->addColumn('action', function ($overtime) use ($isAdmin) {
                $buttons = '<div class="btn-group" role="group">';
                $buttons .= '<button type="button" class="btn btn-sm btn-info view-btn" data-id="' . $overtime->id . '" title="Detail"><i class="fas fa-eye"></i></button>';
                
                if ($overtime->status === 'pending') {
                    if ($isAdmin) {
                        // Admin can edit and delete their own overtimes or view others
                        $buttons .= '<button type="button" class="btn btn-sm btn-warning edit-btn" data-id="' . $overtime->id . '" title="Edit"><i class="fas fa-edit"></i></button>';
                        $buttons .= '<button type="button" class="btn btn-sm btn-danger delete-btn" data-id="' . $overtime->id . '" data-name="' . htmlspecialchars($overtime->overtime_type . ' lembur pada ' . $overtime->formatted_date) . '" title="Batalkan"><i class="fas fa-times"></i></button>';
                    } else {
                        $buttons .= '<button type="button" class="btn btn-sm btn-warning edit-btn" data-id="' . $overtime->id . '" title="Edit"><i class="fas fa-edit"></i></button>';
                        $buttons .= '<button type="button" class="btn btn-sm btn-danger delete-btn" data-id="' . $overtime->id . '" data-name="' . htmlspecialchars($overtime->overtime_type . ' lembur pada ' . $overtime->formatted_date) . '" title="Batalkan"><i class="fas fa-times"></i></button>';
                    }
                }
                

                
                $buttons .= '</div>';
                return $buttons;
            })
            ->addColumn('employee_name', function ($overtime) {
                return $overtime->employee->name . ' (' . $overtime->employee->employee_id . ')';
            })
            ->addColumn('type_badge', function ($overtime) {
                return $overtime->type_badge;
            })
            ->addColumn('status_badge', function ($overtime) {
                return $overtime->status_badge;
            })
            ->addColumn('formatted_date', function ($overtime) {
                return $overtime->formatted_date;
            })
            ->addColumn('time_range', function ($overtime) {
                return $overtime->start_time . ' - ' . $overtime->end_time;
            })
            ->addColumn('total_hours_formatted', function ($overtime) {
                return $overtime->total_hours . ' jam';
            })
            ->addColumn('created_at_formatted', function ($overtime) {
                return $overtime->created_at->format('d/m/Y H:i');
            })
            ->rawColumns(['action', 'type_badge', 'status_badge', 'total_hours_formatted', 'created_at_formatted'])
            ->make(true);
    }

    /**
     * Display overtime requests for approval (for managers/HR).
     */
    public function approval()
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return redirect()->route('login')->with('error', 'Please login first.');
            }
            
            if (!in_array($user->role, ['admin', 'hr', 'manager'])) {
                return redirect()->back()->with('error', 'You do not have permission to approve overtime requests.');
            }

            return view('overtimes.approval');
        } catch (\Exception $e) {
            // Log error for debugging
            \Log::error('Overtime approval error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error loading overtime approval: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            $user = Auth::user();
            $employee = Employee::where('user_id', $user->id)->first();
            
            if (!$employee) {
                return redirect()->back()->with('error', 'Employee not found for this user.');
            }

            // Get overtime statistics
            $overtimeStats = $this->overtimeService->getOvertimeStatisticsForCurrentUser();

            return view('overtimes.create', compact('overtimeStats'));
        } catch (\Exception $e) {
            \Log::error('Overtime create error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error loading overtime create form: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(OvertimeRequest $request)
    {
        try {
            $overtime = $this->overtimeService->createOvertime($request->validated());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Permintaan lembur berhasil diajukan!',
                    'data' => new OvertimeResource($overtime)
                ]);
            }

            return redirect()->route('overtimes.index')
                ->with('success', 'Permintaan lembur berhasil diajukan!');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating overtime request: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = Auth::user();
        
        // If user is admin/HR/manager, they can view any overtime in their company
        if (in_array($user->role, ['admin', 'hr', 'manager'])) {
            $overtime = Overtime::with(['employee', 'approver'])
                ->where('id', $id)
                ->where('company_id', $user->company_id)
                ->first();
        } else {
            // If user is employee, they can only view their own overtimes
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

            $overtime = Overtime::with(['employee', 'approver'])
                ->where('id', $id)
                ->where('employee_id', $employee->id)
                ->first();
        }

        if (!$overtime) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Overtime request not found.'
                ], 404);
            }
            abort(404);
        }

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $overtime->id,
                    'employee_name' => $overtime->employee->name . ' (' . $overtime->employee->employee_id . ')',
                    'overtime_type' => $overtime->overtime_type,
                    'type_badge' => $overtime->type_badge,
                    'date' => $overtime->date,
                    'formatted_date' => $overtime->formatted_date,
                    'start_time' => $overtime->start_time,
                    'end_time' => $overtime->end_time,
                    'total_hours' => $overtime->total_hours,
                    'reason' => $overtime->reason,
                    'status' => $overtime->status,
                    'status_badge' => $overtime->status_badge,
                    'attachment_path' => $overtime->attachment,
                    'created_at' => $overtime->created_at,
                    'created_at_formatted' => $overtime->created_at->format('d/m/Y H:i'),
                    'approver_name' => $overtime->approver ? $overtime->approver->name : null,
                    'approved_at' => $overtime->approved_at,
                    'approved_at_formatted' => $overtime->approved_at ? $overtime->approved_at->format('d/m/Y H:i') : null,
                    'approval_notes' => $overtime->approval_notes,
                ]
            ]);
        }

        return view('overtimes.show', compact('overtime'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $overtime = $this->overtimeService->findOvertimeById($id);

            // Check if overtime can be edited (only pending overtimes can be edited)
            if ($overtime->status !== 'pending') {
                return redirect()->back()->with('error', 'Only pending overtime requests can be edited.');
            }

            return view('overtimes.edit', compact('overtime'));
        } catch (\Exception $e) {
            \Log::error('Overtime edit error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error loading overtime edit form: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(OvertimeRequest $request, string $id)
    {
        try {
            // Log data yang diterima untuk debugging
            \Log::info('Overtime update request data', [
                'id' => $id,
                'all_data' => $request->all(),
                'validated_data' => $request->validated(),
                'start_time' => $request->input('start_time'),
                'end_time' => $request->input('end_time'),
                'date' => $request->input('date')
            ]);

            $overtime = $this->overtimeService->updateOvertime($id, $request->validated());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Permintaan lembur berhasil diperbarui!',
                    'data' => new OvertimeResource($overtime)
                ]);
            }

            return redirect()->route('overtimes.index')
                ->with('success', 'Permintaan lembur berhasil diperbarui!');
        } catch (\Exception $e) {
            \Log::error('Overtime update error', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating overtime request: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $user = Auth::user();
            $isAdmin = in_array($user->role, ['admin', 'hr', 'manager']);
            
            // Log for debugging
            \Log::info('Overtime destroy attempt', [
                'user_id' => $user->id,
                'user_role' => $user->role,
                'overtime_id' => $id,
                'is_admin' => $isAdmin,
                'company_id' => $user->company_id ?? 'null'
            ]);
            
            // Find overtime based on user role
            if ($isAdmin) {
                // Admin/HR/Manager can cancel any overtime in their company
                $overtime = Overtime::where('id', $id)
                    ->where('company_id', $user->company_id)
                    ->where('status', 'pending')
                    ->first();
                    
                \Log::info('Admin overtime search result', [
                    'overtime_found' => $overtime ? true : false,
                    'overtime_status' => $overtime ? $overtime->status : 'not_found'
                ]);
            } else {
                // Employee can only cancel their own overtime
                $employee = Employee::where('user_id', $user->id)->first();
                
                if (!$employee) {
                    \Log::warning('Employee not found for user', ['user_id' => $user->id]);
                    if (request()->expectsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Employee not found for this user.'
                        ], 404);
                    }
                    return redirect()->back()->with('error', 'Employee not found for this user.');
                }

                $overtime = Overtime::where('id', $id)
                    ->where('employee_id', $employee->id)
                    ->where('status', 'pending')
                    ->first();
                    
                \Log::info('Employee overtime search result', [
                    'employee_id' => $employee->id,
                    'overtime_found' => $overtime ? true : false,
                    'overtime_status' => $overtime ? $overtime->status : 'not_found'
                ]);
            }

            if (!$overtime) {
                \Log::warning('Overtime not found or cannot be cancelled', [
                    'overtime_id' => $id,
                    'user_role' => $user->role
                ]);
                if (request()->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Overtime request not found or cannot be cancelled.'
                    ], 404);
                }
                return redirect()->back()->with('error', 'Overtime request not found or cannot be cancelled.');
            }

            // Update status to cancelled instead of deleting
            $overtime->update([
                'status' => 'cancelled',
                'approved_by' => $user->id,
                'approved_at' => now(),
                'approval_notes' => $isAdmin ? 'Dibatalkan oleh ' . $user->name : 'Dibatalkan oleh pemohon'
            ]);

            \Log::info('Overtime successfully cancelled', [
                'overtime_id' => $id,
                'cancelled_by' => $user->id
            ]);

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Permintaan lembur berhasil dibatalkan!'
                ]);
            }

            return redirect()->route('overtimes.index')
                ->with('success', 'Permintaan lembur berhasil dibatalkan!');
        } catch (\Exception $e) {
            \Log::error('Error in overtime destroy', [
                'overtime_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat membatalkan permintaan lembur: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Terjadi kesalahan saat membatalkan permintaan lembur: ' . $e->getMessage());
        }
    }

    /**
     * Approve overtime request.
     */
    public function approve(OvertimeApprovalRequest $request, string $id)
    {
        try {
            $this->overtimeService->approveOvertime($id, $request->approval_notes);
            
            return response()->json([
                'success' => true,
                'message' => 'Permintaan lembur berhasil disetujui!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Reject overtime request.
     */
    public function reject(OvertimeApprovalRequest $request, string $id)
    {
        try {
            $this->overtimeService->rejectOvertime($id, $request->approval_notes);
            
            return response()->json([
                'success' => true,
                'message' => 'Permintaan lembur berhasil ditolak!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Get overtime data for approval DataTables.
     */
    public function approvalData(): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }
            
            if (!in_array($user->role, ['admin', 'hr', 'manager'])) {
                return response()->json(['error' => 'You do not have permission to approve overtime requests.'], 403);
            }

            $startDate = request('start_date');
            $endDate = request('end_date');

            // Get pending overtimes using service
            $overtimes = $this->overtimeService->getPendingOvertimesForApproval($startDate, $endDate);
            
            return DataTables::of($overtimes)
                ->addColumn('employee_info', function ($overtime) {
                    return $overtime->employee->name . ' (' . $overtime->employee->employee_id . ')';
                })
                ->addColumn('overtime_type_badge', function ($overtime) {
                    return $overtime->type_badge;
                })
                ->addColumn('date_formatted', function ($overtime) {
                    return $overtime->formatted_date;
                })
                ->addColumn('time_range', function ($overtime) {
                    return $overtime->start_time . ' - ' . $overtime->end_time;
                })
                ->addColumn('total_hours', function ($overtime) {
                    return $overtime->total_hours . ' jam';
                })
                ->addColumn('created_at_formatted', function ($overtime) {
                    return $overtime->created_at->format('d/m/Y H:i');
                })
                ->addColumn('action', function ($overtime) {
                    return '
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-success approve-btn" 
                                    data-id="' . $overtime->id . '" 
                                    data-employee="' . htmlspecialchars($overtime->employee->name) . '"
                                    data-type="' . $overtime->overtime_type . '"
                                    data-hours="' . $overtime->total_hours . '"
                                    title="Setujui">
                                <i class="fas fa-check"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger reject-btn" 
                                    data-id="' . $overtime->id . '" 
                                    data-employee="' . htmlspecialchars($overtime->employee->name) . '"
                                    data-type="' . $overtime->overtime_type . '"
                                    data-hours="' . $overtime->total_hours . '"
                                    title="Tolak">
                                <i class="fas fa-times"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-info view-btn" 
                                    data-id="' . $overtime->id . '"
                                    title="Detail">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    ';
                })
                ->rawColumns(['overtime_type_badge', 'action'])
                ->make(true);

        } catch (\Exception $e) {
            \Log::error('Error in overtime approvalData: ' . $e->getMessage());
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Get overtime statistics for employee.
     */
    public function statistics()
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

            // Get overtime statistics using service
            $overtimeStats = $this->overtimeService->getOvertimeStatisticsForCurrentUser();

            return view('overtimes.statistics', compact('overtimeStats'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error loading overtime statistics: ' . $e->getMessage());
        }
    }


} 