<?php

namespace App\Http\Controllers;

use App\Services\BpjsReportService;
use App\Http\Resources\BpjsReportResource;
use App\Http\Resources\BpjsResource;
use App\DataTables\BpjsReportDataTable;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class BpjsReportController extends Controller
{
    protected $bpjsReportService;

    public function __construct(BpjsReportService $bpjsReportService)
    {
        $this->bpjsReportService = $bpjsReportService;
    }

    /**
     * Display BPJS Report page
     */
    public function index(Request $request): View
    {
        $period = $request->get('period', now()->format('Y-m'));
        $type = $request->get('type', 'both');
        
        $summary = $this->bpjsReportService->getSummary($period, $type);
        $periods = $this->bpjsReportService->getAvailablePeriods();
        
        return view('bpjs.report', compact('summary', 'periods', 'period', 'type'));
    }

    /**
     * Get BPJS Report data for DataTables
     */
    public function getData(Request $request): JsonResponse
    {
        $period = $request->get('period', now()->format('Y-m'));
        $type = $request->get('type');
        
        // Handle null or empty type values
        if (empty($type)) {
            $type = 'both';
        }
        
        \Log::info('BPJS Report DataTable request', [
            'period' => $period,
            'type' => $type,
            'request_params' => $request->all()
        ]);
        
        $data = $this->bpjsReportService->getReportData($period, $type);
        
        return DataTables::of($data)
            ->addColumn('employee_name', function ($item) {
                return $item->employee->name ?? 'N/A';
            })
            ->addColumn('employee_id', function ($item) {
                return $item->employee->employee_id ?? 'N/A';
            })
            ->addColumn('department', function ($item) {
                return $item->employee->department ?? 'N/A';
            })
            ->addColumn('bpjs_type_badge', function ($item) {
                if ($item->bpjs_type === 'kesehatan') {
                    return '<span class="badge badge-info"><i class="fas fa-heartbeat"></i> Kesehatan</span>';
                } else {
                    return '<span class="badge badge-success"><i class="fas fa-briefcase"></i> Ketenagakerjaan</span>';
                }
            })
            ->addColumn('base_salary_formatted', function ($item) {
                return 'Rp ' . number_format($item->base_salary, 0, ',', '.');
            })
            ->addColumn('employee_contribution_formatted', function ($item) {
                return 'Rp ' . number_format($item->employee_contribution, 0, ',', '.');
            })
            ->addColumn('company_contribution_formatted', function ($item) {
                return 'Rp ' . number_format($item->company_contribution, 0, ',', '.');
            })
            ->addColumn('total_contribution_formatted', function ($item) {
                return '<strong>Rp ' . number_format($item->total_contribution, 0, ',', '.') . '</strong>';
            })
            ->addColumn('status_badge', function ($item) {
                switch ($item->status) {
                    case 'pending':
                        return '<span class="badge badge-warning">Pending</span>';
                    case 'calculated':
                        return '<span class="badge badge-info">Calculated</span>';
                    case 'paid':
                        return '<span class="badge badge-success">Paid</span>';
                    case 'verified':
                        return '<span class="badge badge-primary">Verified</span>';
                    default:
                        return '<span class="badge badge-secondary">Unknown</span>';
                }
            })
            ->addColumn('actions', function ($item) {
                $actions = '<a href="' . route('bpjs-report.show', $item->id) . '" class="btn btn-sm btn-info" title="View"><i class="fas fa-eye"></i></a>';
                
                // Add delete button if user has permission
                if (auth()->user()->can('delete', $item)) {
                    $actions .= ' <button type="button" class="btn btn-sm btn-danger delete-btn" data-id="' . $item->id . '" data-name="' . ($item->employee->name ?? 'N/A') . '" title="Delete"><i class="fas fa-trash"></i></button>';
                }
                
                return $actions;
            })
            ->addColumn('created_at_formatted', function ($item) {
                return $item->created_at ? $item->created_at->format('d/m/Y H:i') : 'N/A';
            })
            ->rawColumns(['bpjs_type_badge', 'total_contribution_formatted', 'status_badge', 'actions'])
            ->make(true);
    }

    /**
     * Get BPJS Report summary data
     */
    public function getSummary(Request $request): JsonResponse
    {
        try {
            $period = $request->get('period', now()->format('Y-m'));
            $type = $request->get('type');
            
            // Handle null or empty type values
            if (empty($type)) {
                $type = 'both';
            }
            
            $summary = $this->bpjsReportService->getSummary($period, $type);
            
            return response()->json([
                'success' => true,
                'data' => $summary
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Terjadi kesalahan saat mengambil summary: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export BPJS Report to CSV
     */
    public function export(Request $request)
    {
        try {
            $period = $request->get('period', now()->format('Y-m'));
            $type = $request->get('type');
            
            // Handle null or empty type values
            if (empty($type)) {
                $type = 'both';
            }
            
            return $this->bpjsReportService->exportToCsv($period, $type);
            
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat export: ' . $e->getMessage());
        }
    }

    /**
     * Get chart data for BPJS Report
     */
    public function getChartData(Request $request): JsonResponse
    {
        try {
            $period = $request->get('period', now()->format('Y-m'));
            $type = $request->get('type');
            
            // Handle null or empty type values
            if (empty($type)) {
                $type = 'both';
            }
            
            $chartData = $this->bpjsReportService->getChartData($period, $type);
            
            return response()->json([
                'success' => true,
                'data' => $chartData
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Terjadi kesalahan saat mengambil data chart: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show BPJS record details
     */
    public function show(string $id)
    {
        try {
            $bpjs = $this->bpjsReportService->getBpjsRecord($id);
            
            if (!$bpjs) {
                if (request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Data BPJS tidak ditemukan'
                    ], 404);
                }
                
                return redirect()->route('bpjs-report.index')
                    ->with('error', 'Data BPJS tidak ditemukan');
            }
            
            // If AJAX request, return JSON
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => new BpjsResource($bpjs)
                ]);
            }
            
            // If regular request, return view
            return view('bpjs.report-show', compact('bpjs'));
            
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat mengambil data: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->route('bpjs-report.index')
                ->with('error', 'Terjadi kesalahan saat mengambil data: ' . $e->getMessage());
        }
    }
}
