<?php

namespace App\Services;

use App\Models\Bpjs;
use App\Models\Employee;
use App\Repositories\BpjsRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BpjsReportService
{
    protected $bpjsRepository;

    public function __construct(BpjsRepository $bpjsRepository)
    {
        $this->bpjsRepository = $bpjsRepository;
    }

    /**
     * Get BPJS Report summary data
     */
    public function getSummary(string $period, string $type = 'both'): array
    {
        try {
            $parsedPeriod = Carbon::parse($period);
        } catch (\Exception $e) {
            // If period parsing fails, use current month
            $parsedPeriod = now();
            \Log::warning('Invalid period format provided: ' . $period . ', using current month instead');
        }

        $query = Bpjs::with('employee')
            ->currentCompany()
            ->whereYear('created_at', $parsedPeriod->year)
            ->whereMonth('created_at', $parsedPeriod->month);

        if ($type !== 'both') {
            $query->where('bpjs_type', $type);
        }

        $records = $query->get();

        $kesehatanCount = $records->where('bpjs_type', 'kesehatan')->count();
        $ketenagakerjaanCount = $records->where('bpjs_type', 'ketenagakerjaan')->count();
        
        $totalEmployeeContribution = $records->sum('employee_contribution');
        $totalCompanyContribution = $records->sum('company_contribution');
        $totalContribution = $totalEmployeeContribution + $totalCompanyContribution;

        return [
            'kesehatan_count' => $kesehatanCount,
            'ketenagakerjaan_count' => $ketenagakerjaanCount,
            'total_employee_contribution' => $totalEmployeeContribution,
            'total_company_contribution' => $totalCompanyContribution,
            'total_contribution' => $totalContribution,
            'total_records' => $records->count()
        ];
    }

    /**
     * Get BPJS Report data for DataTables
     */
    public function getReportData(string $period, string $type = 'both'): Collection
    {
        try {
            $parsedPeriod = Carbon::parse($period);
        } catch (\Exception $e) {
            // If period parsing fails, use current month
            $parsedPeriod = now();
            \Log::warning('Invalid period format provided: ' . $period . ', using current month instead');
        }

        $query = Bpjs::with('employee')
            ->currentCompany()
            ->whereYear('created_at', $parsedPeriod->year)
            ->whereMonth('created_at', $parsedPeriod->month);

        if ($type !== 'both') {
            $query->where('bpjs_type', $type);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get available periods for BPJS data
     */
    public function getAvailablePeriods(): array
    {
        $periods = Bpjs::currentCompany()
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as period')
            ->distinct()
            ->orderBy('period', 'desc')
            ->pluck('period')
            ->toArray();

        // If no periods found, add current month
        if (empty($periods)) {
            $periods[] = now()->format('Y-m');
        }

        return $periods;
    }

    /**
     * Get chart data for BPJS Report
     */
    public function getChartData(string $period, string $type = 'both'): array
    {
        $summary = $this->getSummary($period, $type);
        
        return [
            'contribution_distribution' => [
                'labels' => ['Employee Contribution', 'Company Contribution'],
                'data' => [
                    $summary['total_employee_contribution'],
                    $summary['total_company_contribution']
                ],
                'backgroundColor' => ['#17a2b8', '#28a745']
            ],
            'type_distribution' => [
                'labels' => ['BPJS Kesehatan', 'BPJS Ketenagakerjaan'],
                'data' => [
                    $summary['kesehatan_count'],
                    $summary['ketenagakerjaan_count']
                ],
                'backgroundColor' => ['#17a2b8', '#28a745']
            ]
        ];
    }

    /**
     * Export BPJS Report to CSV
     */
    public function exportToCsv(string $period, string $type = 'both'): StreamedResponse
    {
        $data = $this->getReportData($period, $type);
        $summary = $this->getSummary($period, $type);
        
        $filename = "laporan_bpjs_{$period}_{$type}_" . now()->format('Y-m-d_H-i-s') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($data, $summary) {
            $file = fopen('php://output', 'w');
            
            // Write summary section
            fputcsv($file, ['Ringkasan Laporan BPJS']);
            fputcsv($file, ['']);
            fputcsv($file, ['Total Data', $summary['total_records']]);
            fputcsv($file, ['Data BPJS Kesehatan', $summary['kesehatan_count']]);
            fputcsv($file, ['Data BPJS Ketenagakerjaan', $summary['ketenagakerjaan_count']]);
            fputcsv($file, ['Total Kontribusi Karyawan', 'Rp ' . number_format($summary['total_employee_contribution'], 0, ',', '.')]);
            fputcsv($file, ['Total Kontribusi Perusahaan', 'Rp ' . number_format($summary['total_company_contribution'], 0, ',', '.')]);
            fputcsv($file, ['Total Kontribusi', 'Rp ' . number_format($summary['total_contribution'], 0, ',', '.')]);
            fputcsv($file, ['']);
            
            // Write detailed data
            fputcsv($file, [
                'ID Karyawan',
                'Nama Karyawan',
                'Jenis BPJS',
                'Gaji Pokok',
                'Kontribusi Karyawan',
                'Kontribusi Perusahaan',
                'Total Kontribusi',
                'Status',
                'Tanggal Dibuat'
            ]);

            foreach ($data as $item) {
                fputcsv($file, [
                    $item->employee->employee_id ?? 'N/A',
                    $item->employee->name ?? 'N/A',
                    ucfirst($item->bpjs_type),
                    'Rp ' . number_format($item->base_salary, 0, ',', '.'),
                    'Rp ' . number_format($item->employee_contribution, 0, ',', '.'),
                    'Rp ' . number_format($item->company_contribution, 0, ',', '.'),
                    'Rp ' . number_format($item->total_contribution, 0, ',', '.'),
                    ucfirst($item->status),
                    $item->created_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get BPJS Report statistics by department
     */
    public function getStatisticsByDepartment(string $period, string $type = 'both'): array
    {
        $query = Bpjs::with('employee')
            ->currentCompany()
            ->whereYear('created_at', Carbon::parse($period)->year)
            ->whereMonth('created_at', Carbon::parse($period)->month);

        if ($type !== 'both') {
            $query->where('bpjs_type', $type);
        }

        $records = $query->get();

        $statistics = [];
        
        foreach ($records as $record) {
            $departmentName = $record->employee->department ?? 'Unknown Department';
            
            if (!isset($statistics[$departmentName])) {
                $statistics[$departmentName] = [
                    'total_employees' => 0,
                    'total_employee_contribution' => 0,
                    'total_company_contribution' => 0,
                    'kesehatan_count' => 0,
                    'ketenagakerjaan_count' => 0
                ];
            }

            $statistics[$departmentName]['total_employees']++;
            $statistics[$departmentName]['total_employee_contribution'] += $record->employee_contribution;
            $statistics[$departmentName]['total_company_contribution'] += $record->company_contribution;

            if ($record->bpjs_type === 'kesehatan') {
                $statistics[$departmentName]['kesehatan_count']++;
            } else {
                $statistics[$departmentName]['ketenagakerjaan_count']++;
            }
        }

        return $statistics;
    }

    /**
     * Get BPJS Report trend data for multiple periods
     */
    public function getTrendData(int $months = 12): array
    {
        $trends = [];
        
        for ($i = $months - 1; $i >= 0; $i--) {
            $period = now()->subMonths($i)->format('Y-m');
            $summary = $this->getSummary($period);
            
            $trends[] = [
                'period' => $period,
                'period_label' => Carbon::parse($period)->format('M Y'),
                'total_contribution' => $summary['total_contribution'],
                'employee_contribution' => $summary['total_employee_contribution'],
                'company_contribution' => $summary['total_company_contribution'],
                'total_records' => $summary['total_records']
            ];
        }

        return $trends;
    }

    /**
     * Get single BPJS record for show
     */
    public function getBpjsRecord(string $id): ?Bpjs
    {
        return Bpjs::with(['employee', 'payroll'])
            ->currentCompany()
            ->find($id);
    }
}
