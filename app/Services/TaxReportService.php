<?php

namespace App\Services;

use App\Repositories\TaxReportRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TaxReportService
{
    protected $taxReportRepository;

    public function __construct(TaxReportRepository $taxReportRepository)
    {
        $this->taxReportRepository = $taxReportRepository;
    }

    /**
     * Get tax report data for DataTable
     */
    public function getTaxReportData($filters = [])
    {
        try {
            $user = Auth::user();
            $filters['company_id'] = $user->company_id;
            
            return $this->taxReportRepository->getTaxReportData($filters);
        } catch (\Exception $e) {
            Log::error('Error getting tax report data: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate tax report
     */
    public function generateReport($filters = [])
    {
        try {
            $user = Auth::user();
            $filters['company_id'] = $user->company_id;
            
            $data = $this->taxReportRepository->getTaxReportData($filters);
            
            // Calculate summary
            $summary = $this->calculateSummary($data['data']);
            
            return [
                'data' => $data['data'],
                'summary' => $summary,
                'filters' => $filters
            ];
        } catch (\Exception $e) {
            Log::error('Error generating tax report: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Calculate summary from tax data
     */
    private function calculateSummary($taxData)
    {
        $summary = [
            'total_records' => count($taxData),
            'total_taxable_income' => 0,
            'total_ptkp_amount' => 0,
            'total_taxable_base' => 0,
            'total_tax_amount' => 0,
            'status_counts' => [
                'pending' => 0,
                'calculated' => 0,
                'paid' => 0,
                'verified' => 0
            ]
        ];

        foreach ($taxData as $tax) {
            $summary['total_taxable_income'] += $tax->taxable_income ?? 0;
            $summary['total_ptkp_amount'] += $tax->ptkp_amount ?? 0;
            $summary['total_taxable_base'] += $tax->taxable_base ?? 0;
            $summary['total_tax_amount'] += $tax->tax_amount ?? 0;
            
            $status = $tax->status ?? 'pending';
            if (isset($summary['status_counts'][$status])) {
                $summary['status_counts'][$status]++;
            }
        }

        return $summary;
    }

    /**
     * Export to PDF
     */
    public function exportToPdf($filters = [])
    {
        try {
            $report = $this->generateReport($filters);
            
            // For now, return a simple response
            // In real implementation, you would use a PDF library like DomPDF
            return response()->json([
                'message' => 'PDF export functionality will be implemented',
                'data' => $report
            ]);
        } catch (\Exception $e) {
            Log::error('Error exporting tax report to PDF: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Export to Excel
     */
    public function exportToExcel($filters = [])
    {
        try {
            $report = $this->generateReport($filters);
            
            // For now, return a simple response
            // In real implementation, you would use a Excel library like Maatwebsite Excel
            return response()->json([
                'message' => 'Excel export functionality will be implemented',
                'data' => $report
            ]);
        } catch (\Exception $e) {
            Log::error('Error exporting tax report to Excel: ' . $e->getMessage());
            throw $e;
        }
    }
}
