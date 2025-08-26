<?php

namespace App\Http\Controllers;

use App\DataTables\TaxReportDataTable;
use App\Models\Tax;
use App\Models\Employee;
use App\Http\Requests\TaxReportRequest;
use App\Http\Resources\TaxReportResource;
use App\Services\TaxReportService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Facades\DataTables;

class TaxReportController extends Controller
{
    public function __construct(
        private TaxReportService $taxReportService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Get filter parameters
        $status = $request->get('status', '');
        $period = $request->get('period', date('Y-m'));
        
        return view('tax-reports.index', compact('status', 'period'));
    }



    /**
     * Generate tax report
     */
    public function generate(TaxReportRequest $request): JsonResponse
    {
        try {
            $filters = $request->validated();
            $report = $this->taxReportService->generateReport($filters);
            
            return response()->json([
                'success' => true,
                'message' => 'Laporan pajak berhasil dibuat',
                'data' => $report
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat laporan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export to PDF
     */
    public function exportPdf(TaxReportRequest $request): JsonResponse
    {
        try {
            $filters = $request->validated();
            $result = $this->taxReportService->exportToPdf($filters);
            
            return response()->json([
                'success' => true,
                'message' => 'Export PDF berhasil',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal export PDF: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export to Excel
     */
    public function exportExcel(TaxReportRequest $request): JsonResponse
    {
        try {
            $filters = $request->validated();
            $result = $this->taxReportService->exportToExcel($filters);
            
            return response()->json([
                'success' => true,
                'message' => 'Export Excel berhasil',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal export Excel: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get employees for filter dropdown
     */
    public function getEmployees(): JsonResponse
    {
        try {
            $employees = Employee::currentCompany()
                ->select('id', 'name', 'employee_id')
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $employees
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data karyawan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get tax periods for filter dropdown
     */
    public function getTaxPeriods(): JsonResponse
    {
        try {
            $periods = Tax::currentCompany()
                ->select('tax_period')
                ->distinct()
                ->orderBy('tax_period', 'desc')
                ->pluck('tax_period');

            return response()->json([
                'success' => true,
                'data' => $periods
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat periode pajak: ' . $e->getMessage()
            ], 500);
        }
    }
}
