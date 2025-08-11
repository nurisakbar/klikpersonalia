<?php

namespace App\Http\Controllers;

use App\Services\ExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExportController extends Controller
{
    protected $exportService;

    public function __construct(ExportService $exportService)
    {
        $this->exportService = $exportService;
    }

    /**
     * Export employees data
     */
    public function exportEmployees(Request $request)
    {
        $request->validate([
            'format' => 'required|in:xlsx,pdf'
        ]);

        try {
            return $this->exportService->exportEmployees($request->format);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Export failed: ' . $e->getMessage());
        }
    }

    /**
     * Export payroll data
     */
    public function exportPayrolls(Request $request)
    {
        $request->validate([
            'format' => 'required|in:xlsx,pdf',
            'period' => 'nullable|string'
        ]);

        try {
            return $this->exportService->exportPayrolls($request->period, $request->format);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Export failed: ' . $e->getMessage());
        }
    }

    /**
     * Export attendance data
     */
    public function exportAttendance(Request $request)
    {
        $request->validate([
            'format' => 'required|in:xlsx,pdf',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date'
        ]);

        try {
            return $this->exportService->exportAttendance(
                $request->start_date,
                $request->end_date,
                $request->format
            );
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Export failed: ' . $e->getMessage());
        }
    }

    /**
     * Export leave data
     */
    public function exportLeaves(Request $request)
    {
        $request->validate([
            'format' => 'required|in:xlsx,pdf',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date'
        ]);

        try {
            return $this->exportService->exportLeaves(
                $request->start_date,
                $request->end_date,
                $request->format
            );
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Export failed: ' . $e->getMessage());
        }
    }

    /**
     * Export tax data
     */
    public function exportTaxes(Request $request)
    {
        $request->validate([
            'format' => 'required|in:xlsx,pdf',
            'period' => 'nullable|string'
        ]);

        try {
            return $this->exportService->exportTaxes($request->period, $request->format);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Export failed: ' . $e->getMessage());
        }
    }

    /**
     * Export BPJS data
     */
    public function exportBpjs(Request $request)
    {
        $request->validate([
            'format' => 'required|in:xlsx,pdf',
            'period' => 'nullable|string'
        ]);

        try {
            return $this->exportService->exportBpjs($request->period, $request->format);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Export failed: ' . $e->getMessage());
        }
    }

    /**
     * Show export options page
     */
    public function index()
    {
        $user = Auth::user();
        $company = $user->company;

        return view('exports.index', compact('company'));
    }

    /**
     * Export all data (bulk export)
     */
    public function exportAll(Request $request)
    {
        $request->validate([
            'format' => 'required|in:xlsx,pdf',
            'data_types' => 'required|array',
            'data_types.*' => 'in:employees,payrolls,attendance,leaves,taxes,bpjs'
        ]);

        try {
            $exports = [];
            $format = $request->format;

            foreach ($request->data_types as $dataType) {
                switch ($dataType) {
                    case 'employees':
                        $exports[] = $this->exportService->exportEmployees($format);
                        break;
                    case 'payrolls':
                        $exports[] = $this->exportService->exportPayrolls(null, $format);
                        break;
                    case 'attendance':
                        $exports[] = $this->exportService->exportAttendance(null, null, $format);
                        break;
                    case 'leaves':
                        $exports[] = $this->exportService->exportLeaves(null, null, $format);
                        break;
                    case 'taxes':
                        $exports[] = $this->exportService->exportTaxes(null, $format);
                        break;
                    case 'bpjs':
                        $exports[] = $this->exportService->exportBpjs(null, $format);
                        break;
                }
            }

            return redirect()->back()->with('success', 'Export completed successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Bulk export failed: ' . $e->getMessage());
        }
    }
} 