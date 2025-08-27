<?php

namespace App\Http\Controllers;

use App\DataTables\BpjsDataTable;
use App\Models\Bpjs;
use App\Models\Employee;
use App\Services\BpjsService;
use App\Http\Requests\BpjsRequest;
use App\Http\Resources\BpjsResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class BpjsController extends Controller
{
    public function __construct(
        private BpjsService $bpjsService
    ) {}

    /**
     * Display a listing of BPJS records
     */
    public function index(BpjsDataTable $dataTable)
    {
        $companyId = Auth::user()->company_id;
        $employees = Employee::forCompany($companyId)->get();
        $periods = $this->bpjsService->getBpjsPeriods();
        $summary = $this->bpjsService->getBpjsSummary();

        return view('bpjs.index', compact('employees', 'periods', 'summary'));
    }

    /**
     * Get BPJS data for DataTables.
     */
    public function data(): JsonResponse
    {
        $companyId = Auth::user()->company_id;
        $query = Bpjs::with(['employee'])
            ->forCompany($companyId);

        return DataTables::of($query)
            ->addColumn('employee_name', function ($bpjs) {
                return $bpjs->employee->name;
            })
            ->addColumn('employee_id', function ($bpjs) {
                return $bpjs->employee->employee_id;
            })
            ->addColumn('bpjs_type_badge', function ($bpjs) {
                if ($bpjs->bpjs_type === 'kesehatan') {
                    return '<span class="badge badge-info"><i class="fas fa-heartbeat"></i> Kesehatan</span>';
                } else {
                    return '<span class="badge badge-success"><i class="fas fa-briefcase"></i> Ketenagakerjaan</span>';
                }
            })
            ->addColumn('period_formatted', function ($bpjs) {
                return \Carbon\Carbon::parse($bpjs->bpjs_period)->format('F Y');
            })
            ->addColumn('base_salary_formatted', function ($bpjs) {
                return 'Rp ' . number_format($bpjs->base_salary, 0, ',', '.');
            })
            ->addColumn('employee_contribution_formatted', function ($bpjs) {
                return 'Rp ' . number_format($bpjs->employee_contribution, 0, ',', '.');
            })
            ->addColumn('company_contribution_formatted', function ($bpjs) {
                return 'Rp ' . number_format($bpjs->company_contribution, 0, ',', '.');
            })
            ->addColumn('total_contribution_formatted', function ($bpjs) {
                return 'Rp ' . number_format($bpjs->total_contribution, 0, ',', '.');
            })
            ->addColumn('status_badge', function ($bpjs) {
                $statusClass = [
                    'pending' => 'badge badge-warning',
                    'calculated' => 'badge badge-info',
                    'paid' => 'badge badge-success',
                    'verified' => 'badge badge-primary'
                ];
                
                $statusText = [
                    'pending' => 'Menunggu',
                    'calculated' => 'Dihitung',
                    'paid' => 'Dibayar',
                    'verified' => 'Diverifikasi'
                ];
                
                return '<span class="' . $statusClass[$bpjs->status] . '">' . $statusText[$bpjs->status] . '</span>';
            })
            ->addColumn('action', function ($bpjs) {
                return '
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-info view-btn" data-id="' . $bpjs->id . '">
                            <i class="fas fa-eye"></i>
                        </button>
                        <a href="' . route('bpjs.edit', $bpjs->id) . '" class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button type="button" class="btn btn-sm btn-danger delete-btn" data-id="' . $bpjs->id . '" data-name="' . htmlspecialchars($bpjs->employee->name) . '">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                ';
            })
            ->rawColumns(['action', 'bpjs_type_badge', 'status_badge'])
            ->make(true);
    }

    /**
     * Show the form for creating a new BPJS record
     */
    public function create()
    {
        $companyId = Auth::user()->company_id;
        $employees = Employee::forCompany($companyId)
            ->where('bpjs_kesehatan_active', true)
            ->orWhere('bpjs_ketenagakerjaan_active', true)
            ->get();

        return view('bpjs.create', compact('employees'));
    }

    /**
     * Store a newly created BPJS record
     */
    public function store(BpjsRequest $request)
    {
        $result = $this->bpjsService->createBpjsRecord($request->validated());

        if ($request->ajax()) {
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'data' => new BpjsResource($result['data']),
                    'redirect' => route('bpjs.index')
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result['message']
            ], 422);
        }

        if ($result['success']) {
            return redirect()->route('bpjs.index')
                ->with('success', $result['message']);
        }

        return back()->withErrors(['error' => $result['message']]);
    }

    /**
     * Display the specified BPJS record
     */
    public function show($id)
    {
        $bpjs = Bpjs::findOrFail($id);
        $bpjs->load(['employee', 'payroll']);
        
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'data' => new BpjsResource($bpjs)
            ]);
        }
        
        return view('bpjs.show', compact('bpjs'));
    }

    /**
     * Show the form for editing the specified BPJS record
     */
    public function edit($id)
    {
        $bpjs = Bpjs::findOrFail($id);
        $companyId = Auth::user()->company_id;
        $employees = Employee::forCompany($companyId)->get();
        
        return view('bpjs.edit', compact('bpjs', 'employees'));
    }

    /**
     * Update the specified BPJS record
     */
    public function update(BpjsRequest $request, $id)
    {
        $bpjs = Bpjs::findOrFail($id);
        $result = $this->bpjsService->updateBpjsRecord($bpjs, $request->validated());

        if ($request->ajax()) {
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'data' => new BpjsResource($result['data']),
                    'redirect' => route('bpjs.index')
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result['message']
            ], 422);
        }

        if ($result['success']) {
            return redirect()->route('bpjs.index')
                ->with('success', $result['message']);
        }

        return back()->withErrors(['error' => $result['message']]);
    }

    /**
     * Remove the specified BPJS record
     */
    public function destroy($id)
    {
        $bpjs = Bpjs::findOrFail($id);
        $result = $this->bpjsService->deleteBpjsRecord($bpjs);
        
        if ($result['success']) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message']
                ]);
            }
            
            return redirect()->route('bpjs.index')
                ->with('success', $result['message']);
        }
        
        if (request()->ajax()) {
            return response()->json([
                'success' => false,
                'message' => $result['message']
            ], 400);
        }
        
        return back()->withErrors(['error' => $result['message']]);
    }

    /**
     * Calculate BPJS for all employees in a payroll period
     */
    public function calculateForPayroll(Request $request)
    {
        $request->validate([
            'payroll_period' => 'required|date_format:Y-m',
            'bpjs_type' => ['required', \Illuminate\Validation\Rule::in(['kesehatan', 'ketenagakerjaan', 'both'])],
        ]);

        $result = $this->bpjsService->calculateBpjsForAllEmployees($request->payroll_period, $request->bpjs_type);

        if ($result['success']) {
            $message = "Successfully created {$result['created_count']} BPJS records.";
            if (!empty($result['errors'])) {
                $message .= " Errors: " . implode(', ', $result['errors']);
            }

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message
                ]);
            }

            return redirect()->route('bpjs.index')
                ->with('success', $message);
        }

        if (request()->ajax()) {
            return response()->json([
                'success' => false,
                'message' => $result['message']
            ], 400);
        }

        return back()->withErrors(['error' => $result['message']]);
    }



    /**
     * Display BPJS reports
     */
    public function report(Request $request)
    {
        $companyId = Auth::user()->company_id;

        $period = $request->get('period', date('Y-m'));
        $type = $request->get('type', 'both');

        $query = Bpjs::with(['employee'])
            ->forCompany($companyId)
            ->forPeriod($period);

        if ($type !== 'both') {
            $query->forType($type);
        }

        $bpjsRecords = $query->get();

        // Calculate summary
        $summary = [
            'total_employee_contribution' => $bpjsRecords->sum('employee_contribution'),
            'total_company_contribution' => $bpjsRecords->sum('company_contribution'),
            'total_contribution' => $bpjsRecords->sum('total_contribution'),
            'kesehatan_count' => $bpjsRecords->where('bpjs_type', 'kesehatan')->count(),
            'ketenagakerjaan_count' => $bpjsRecords->where('bpjs_type', 'ketenagakerjaan')->count(),
        ];

        $periods = Bpjs::forCompany($companyId)
            ->distinct()
            ->pluck('bpjs_period')
            ->sort()
            ->reverse();

        return view('bpjs.report', compact('bpjsRecords', 'summary', 'periods', 'period', 'type'));
    }

    /**
     * Export BPJS data
     */
    public function export(Request $request)
    {
        $companyId = Auth::user()->company_id;

        $period = $request->get('period', date('Y-m'));
        $type = $request->get('type', 'both');

        $query = Bpjs::with(['employee'])
            ->forCompany($companyId)
            ->forPeriod($period);

        if ($type !== 'both') {
            $query->forType($type);
        }

        $bpjsRecords = $query->get();

        // Generate CSV
        $filename = "bpjs_report_{$period}_{$type}.csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($bpjsRecords) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'Employee ID',
                'Employee Name',
                'BPJS Type',
                'Period',
                'Base Salary',
                'Employee Contribution',
                'Company Contribution',
                'Total Contribution',
                'Status',
                'Payment Date',
                'Notes'
            ]);

            // CSV data
            foreach ($bpjsRecords as $record) {
                fputcsv($file, [
                    $record->employee->employee_id,
                    $record->employee->name,
                    $record->bpjs_type,
                    $record->bpjs_period,
                    $record->base_salary,
                    $record->employee_contribution,
                    $record->company_contribution,
                    $record->total_contribution,
                    $record->status,
                    $record->payment_date,
                    $record->notes
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
} 