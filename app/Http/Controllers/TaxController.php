<?php

namespace App\Http\Controllers;

use App\Models\Tax;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\Company;
use App\Services\TaxService;
use App\Http\Resources\DetailTaxResource;
use App\Http\Requests\TaxRequest;
use App\DataTables\TaxDataTable;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class TaxController extends Controller
{
    public function __construct(
        private TaxService $taxService
    ) {}

    /**
     * Display a listing of taxes.
     */
    public function index()
    {
        return view('taxes.index');
    }

    /**
     * Search taxes for AJAX select
     */
    public function search(Request $request): JsonResponse
    {
        $query = trim((string)$request->get('q', ''));
        $results = $query === ''
            ? $this->taxService->getDefaultTaxesForSelect(20)
            : $this->taxService->searchTaxes($query)->take(20);

        return response()->json([
            'success' => true,
            'data' => $results->map(function ($tax) {
                return [
                    'id' => $tax->id,
                    'text' => $tax->employee->name . ' - ' . $tax->tax_period . ' (Rp ' . number_format($tax->tax_amount, 0, ',', '.') . ')',
                    'tax_amount' => $tax->tax_amount,
                ];
            }),
        ]);
    }

    /**
     * Get taxes data for DataTable
     */
    public function data(): JsonResponse
    {
        try {
            $filters = [
                'month' => request('filter_month'),
                'year' => request('filter_year'),
                'status' => request('status_filter'),
            ];

            $taxes = $this->taxService->getTaxesForDataTable($filters);

            return DataTables::of($taxes)
                ->addColumn('action', function ($tax) {
                    return '
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-info view-btn" data-id="' . $tax->id . '" title="Detail">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-warning edit-btn" data-id="' . $tax->id . '" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger delete-btn" data-id="' . $tax->id . '" data-name="' . htmlspecialchars($tax->employee ? $tax->employee->name : 'Unknown') . '" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    ';
                })
                ->addColumn('employee_name', function ($tax) {
                    return $tax->employee ? $tax->employee->name : '-';
                })
                ->addColumn('employee_id_display', function ($tax) {
                    return $tax->employee ? $tax->employee->employee_id : '-';
                })
                ->addColumn('tax_period_formatted', function ($tax) {
                    if (!$tax->tax_period) return '-';
                    try {
                        $date = \Carbon\Carbon::createFromFormat('Y-m', $tax->tax_period);
                        return $date->format('M Y');
                    } catch (\Exception $e) {
                        return $tax->tax_period ?? '-';
                    }
                })
                ->addColumn('taxable_income_formatted', function ($tax) {
                    return 'Rp ' . number_format($tax->taxable_income ?? 0, 0, ',', '.');
                })
                ->addColumn('ptkp_amount_formatted', function ($tax) {
                    return 'Rp ' . number_format($tax->ptkp_amount ?? 0, 0, ',', '.');
                })
                ->addColumn('tax_amount_formatted', function ($tax) {
                    return 'Rp ' . number_format($tax->tax_amount ?? 0, 0, ',', '.');
                })
                ->addColumn('tax_rate_formatted', function ($tax) {
                    return number_format(($tax->tax_rate ?? 0) * 100, 1) . '%';
                })
                ->addColumn('status_badge', function ($tax) {
                    $status = $tax->status ?? 'pending';
                    $statusClass = [
                        'pending' => 'badge badge-secondary',
                        'calculated' => 'badge badge-info',
                        'paid' => 'badge badge-success',
                        'verified' => 'badge badge-primary'
                    ];
                    
                    $statusText = [
                        'pending' => 'Menunggu',
                        'calculated' => 'Dihitung',
                        'paid' => 'Dibayar',
                        'verified' => 'Terverifikasi'
                    ];
                    
                    $class = $statusClass[$status] ?? 'badge badge-secondary';
                    $text = $statusText[$status] ?? ucfirst($status);
                    
                    return '<span class="' . $class . '">' . $text . '</span>';
                })
                ->rawColumns(['action', 'status_badge'])
                ->make(true);
                
        } catch (\Exception $e) {
            \Log::error('TaxDataTable error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'draw' => intval(request('draw')),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Gagal memuat data pajak: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for creating a new tax calculation.
     */
    public function create()
    {
        $formData = $this->taxService->getFormData();
        
        return view('taxes.create', [
            'employees' => $formData['employees'],
            'ptkpStatuses' => $formData['ptkpStatuses']
        ]);
    }

    /**
     * Store a newly created tax calculation.
     */
    public function store(TaxRequest $request)
    {
        try {
            $result = $this->taxService->createTax($request->validated());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data pajak berhasil dibuat',
                    'data' => new DetailTaxResource($result)
                ]);
            }

            return redirect()->route('taxes.show', $result)->with('success', 'Data pajak berhasil dibuat.');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }

            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified tax calculation.
     */
    public function show(Request $request, $id)
    {
        try {
            $tax = $this->taxService->getTaxById($id);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => new DetailTaxResource($tax)
                ]);
            }
            
            return view('taxes.show', compact('tax'));
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 404);
            }
            
            return redirect()->route('taxes.index')->with('error', $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified tax calculation.
     */
    public function edit(Tax $tax)
    {
        try {
            $tax = $this->taxService->getTaxById($tax->id);
            $formData = $this->taxService->getFormData();
            
            return view('taxes.edit', [
                'tax' => $tax,
                'ptkpStatuses' => $formData['ptkpStatuses']
            ]);
        } catch (\Exception $e) {
            abort(404);
        }
    }

    /**
     * Update the specified tax calculation.
     */
    public function update(TaxRequest $request, Tax $tax)
    {
        try {
            $result = $this->taxService->updateTax($tax->id, $request->validated());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data pajak berhasil diperbarui',
                    'data' => new DetailTaxResource($result)
                ]);
            }

            return redirect()->route('taxes.show', $result)->with('success', 'Data pajak berhasil diperbarui.');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }

            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Remove the specified tax calculation.
     */
    public function destroy(Request $request, $id)
    {
        try {
            $this->taxService->deleteTax($id);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data pajak berhasil dihapus'
                ]);
            }
            
            return redirect()->route('taxes.index')->with('success', 'Data pajak berhasil dihapus');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 404);
            }
            
            return redirect()->route('taxes.index')->with('error', $e->getMessage());
        }
    }

    /**
     * Calculate tax for payroll period.
     */
    public function calculateForPayroll(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2020',
        ]);

        // Format period to match payroll format (e.g., "Januari 2024")
        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $period = $monthNames[$request->month] . ' ' . $request->year;
        
        // Get all employees for the company
        $employees = Employee::where('company_id', $user->company_id)->get();
        
        $calculatedCount = 0;
        $skippedCount = 0;
        $payrollCount = 0;
        $basicSalaryCount = 0;
        $errorCount = 0;

        foreach ($employees as $employee) {
            try {
                // Check if tax calculation already exists
                $existingTax = Tax::where('company_id', $user->company_id)
                    ->where('employee_id', $employee->id)
                    ->where('tax_period', $period)
                    ->first();

                if ($existingTax) {
                    $skippedCount++;
                    continue; // Skip if already calculated
                }

                // Try to get payroll for this period first
                $payroll = Payroll::where('company_id', $user->company_id)
                    ->where('employee_id', $employee->id)
                    ->where('period', $period)
                    ->first();

                $taxableIncome = 0;
                $payrollId = null;

                if ($payroll) {
                    // Use payroll data if available
                    $taxableIncome = $payroll->basic_salary + 
                                   $payroll->allowances + 
                                   $payroll->overtime + 
                                   $payroll->bonus;
                    $payrollId = $payroll->id;
                    $payrollCount++;
                } else {
                    // Use basic salary as fallback
                    $taxableIncome = $employee->basic_salary;
                    $basicSalaryCount++;
                }

                // Calculate tax
                $taxCalculation = Tax::calculatePPh21($employee, $taxableIncome);

                // Create tax record
                Tax::create([
                    'company_id' => $user->company_id,
                    'employee_id' => $employee->id,
                    'payroll_id' => $payrollId,
                    'tax_period' => $period,
                    'taxable_income' => $taxableIncome,
                    'ptkp_status' => $employee->ptkp_status ?? 'TK/0',
                    'ptkp_amount' => $taxCalculation['ptkp_amount'],
                    'taxable_base' => $taxCalculation['taxable_base'],
                    'tax_amount' => $taxCalculation['tax_amount'],
                    'tax_bracket' => $taxCalculation['tax_bracket'],
                    'tax_rate' => $taxCalculation['tax_rate'],
                    'status' => Tax::STATUS_CALCULATED,
                ]);

                $calculatedCount++;
            } catch (\Exception $e) {
                $errorCount++;
            }
        }

        // Build professional message
        $message = "✅ Berhasil menghitung pajak untuk {$calculatedCount} karyawan";
        
        if ($skippedCount > 0) {
            $message .= "\n⏭️ {$skippedCount} karyawan sudah memiliki perhitungan pajak";
        }
        
        if ($payrollCount > 0) {
            $message .= "\n📊 {$payrollCount} karyawan menggunakan data payroll";
        }
        
        if ($basicSalaryCount > 0) {
            $message .= "\n💰 {$basicSalaryCount} karyawan menggunakan gaji pokok (tidak ada payroll)";
        }
        
        if ($errorCount > 0) {
            $message .= "\n❌ {$errorCount} karyawan gagal dihitung";
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'calculated_count' => $calculatedCount,
                    'skipped_count' => $skippedCount,
                    'payroll_count' => $payrollCount,
                    'basic_salary_count' => $basicSalaryCount,
                    'error_count' => $errorCount
                ]
            ]);
        }

        return redirect()->route('taxes.index')->with('success', $message);
    }

    /**
     * Generate tax report.
     */
    public function report(Request $request)
    {
        $user = Auth::user();
        
        $query = Tax::with(['employee'])
            ->where('company_id', $user->company_id);

        // Filter by period
        if ($request->filled('period')) {
            $query->where('tax_period', $request->period);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $taxes = $query->orderBy('tax_period', 'desc')->get();

        // Calculate summary
        $summary = [
            'total_employees' => $taxes->count(),
            'total_taxable_income' => $taxes->sum('taxable_income'),
            'total_tax_amount' => $taxes->sum('tax_amount'),
            'average_tax_rate' => $taxes->avg('tax_rate'),
        ];

        // Calculate tax bracket distribution
        $bracketDistribution = $taxes->groupBy('tax_bracket')
            ->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'total_tax' => $group->sum('tax_amount'),
                    'avg_tax' => $group->avg('tax_amount'),
                ];
            });

        // Calculate status distribution
        $statusDistribution = $taxes->groupBy('status')
            ->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'total_tax' => $group->sum('tax_amount'),
                ];
            });

        return view('taxes.report', compact('taxes', 'summary', 'bracketDistribution', 'statusDistribution'));
    }

    /**
     * Monthly tax report.
     */
    public function monthlyTaxReport(Request $request)
    {
        $user = Auth::user();
        $month = $request->get('month', date('Y-m'));
        
        $taxes = Tax::with(['employee'])
            ->where('company_id', $user->company_id)
            ->where('tax_period', 'LIKE', $month . '%')
            ->orderBy('tax_period', 'desc')
            ->get();

        $summary = [
            'month' => $month,
            'total_employees' => $taxes->count(),
            'total_taxable_income' => $taxes->sum('taxable_income'),
            'total_tax_amount' => $taxes->sum('tax_amount'),
            'average_tax_rate' => $taxes->avg('tax_rate'),
            'paid_tax' => $taxes->where('status', 'paid')->sum('tax_amount'),
            'pending_tax' => $taxes->where('status', 'calculated')->sum('tax_amount'),
        ];

        return view('taxes.monthly-report', compact('taxes', 'summary'));
    }

    /**
     * Annual tax summary.
     */
    public function annualTaxSummary(Request $request)
    {
        $user = Auth::user();
        $year = $request->get('year', date('Y'));
        
        $taxes = Tax::with(['employee'])
            ->where('company_id', $user->company_id)
            ->where('tax_period', 'LIKE', $year . '%')
            ->orderBy('tax_period', 'desc')
            ->get();

        // Group by month
        $monthlyData = $taxes->groupBy(function ($tax) {
            return substr($tax->tax_period, 0, 7); // YYYY-MM
        })->map(function ($monthTaxes) {
            return [
                'total_employees' => $monthTaxes->count(),
                'total_taxable_income' => $monthTaxes->sum('taxable_income'),
                'total_tax_amount' => $monthTaxes->sum('tax_amount'),
                'average_tax_rate' => $monthTaxes->avg('tax_rate'),
            ];
        });

        $summary = [
            'year' => $year,
            'total_employees' => $taxes->count(),
            'total_taxable_income' => $taxes->sum('taxable_income'),
            'total_tax_amount' => $taxes->sum('tax_amount'),
            'average_tax_rate' => $taxes->avg('tax_rate'),
            'monthly_data' => $monthlyData,
        ];

        return view('taxes.annual-summary', compact('taxes', 'summary'));
    }

    /**
     * Tax payment report.
     */
    public function taxPaymentReport(Request $request)
    {
        $user = Auth::user();
        
        $query = Tax::with(['employee'])
            ->where('company_id', $user->company_id)
            ->where('status', 'paid');

        if ($request->filled('period')) {
            $query->where('tax_period', $request->period);
        }

        if ($request->filled('payment_date')) {
            $query->where('payment_date', $request->payment_date);
        }

        $taxes = $query->orderBy('payment_date', 'desc')->get();

        $summary = [
            'total_payments' => $taxes->count(),
            'total_paid_amount' => $taxes->sum('tax_amount'),
            'average_payment' => $taxes->avg('tax_amount'),
            'payment_by_month' => $taxes->groupBy(function ($tax) {
                return substr($tax->payment_date, 0, 7);
            })->map(function ($group) {
                return $group->sum('tax_amount');
            }),
        ];

        return view('taxes.payment-report', compact('taxes', 'summary'));
    }

    /**
     * Tax certificate report.
     */
    public function taxCertificateReport(Request $request)
    {
        $user = Auth::user();
        
        $query = Tax::with(['employee'])
            ->where('company_id', $user->company_id)
            ->where('status', 'verified');

        if ($request->filled('period')) {
            $query->where('tax_period', $request->period);
        }

        $taxes = $query->orderBy('tax_period', 'desc')->get();

        $summary = [
            'total_certificates' => $taxes->count(),
            'total_verified_amount' => $taxes->sum('tax_amount'),
            'certificates_by_type' => [
                'A1' => $taxes->where('certificate_type', 'A1')->count(),
                'A2' => $taxes->where('certificate_type', 'A2')->count(),
                '1721' => $taxes->where('certificate_type', '1721')->count(),
            ],
        ];

        return view('taxes.certificate-report', compact('taxes', 'summary'));
    }

    /**
     * Tax compliance report.
     */
    public function taxComplianceReport(Request $request)
    {
        $user = Auth::user();
        $year = $request->get('year', date('Y'));
        
        $taxes = Tax::with(['employee'])
            ->where('company_id', $user->company_id)
            ->where('tax_period', 'LIKE', $year . '%')
            ->get();

        $employees = Employee::where('company_id', $user->company_id)->get();
        
        $complianceData = [];
        foreach ($employees as $employee) {
            $employeeTaxes = $taxes->where('employee_id', $employee->id);
            $complianceData[] = [
                'employee' => $employee,
                'total_months' => 12,
                'calculated_months' => $employeeTaxes->count(),
                'compliance_rate' => ($employeeTaxes->count() / 12) * 100,
                'total_tax_amount' => $employeeTaxes->sum('tax_amount'),
                'paid_amount' => $employeeTaxes->where('status', 'paid')->sum('tax_amount'),
                'pending_amount' => $employeeTaxes->where('status', 'calculated')->sum('tax_amount'),
            ];
        }

        $summary = [
            'year' => $year,
            'total_employees' => $employees->count(),
            'average_compliance_rate' => collect($complianceData)->avg('compliance_rate'),
            'fully_compliant' => collect($complianceData)->where('compliance_rate', 100)->count(),
            'partially_compliant' => collect($complianceData)->whereBetween('compliance_rate', [1, 99])->count(),
            'non_compliant' => collect($complianceData)->where('compliance_rate', 0)->count(),
        ];

        return view('taxes.compliance-report', compact('complianceData', 'summary'));
    }

    /**
     * Tax audit trail.
     */
    public function taxAuditTrail(Request $request)
    {
        $user = Auth::user();
        
        $query = Tax::with(['employee'])
            ->where('company_id', $user->company_id);

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('period')) {
            $query->where('tax_period', $request->period);
        }

        if ($request->filled('action')) {
            $query->where('status', $request->action);
        }

        $taxes = $query->orderBy('updated_at', 'desc')->get();

        // Group by employee and period for audit trail
        $auditTrail = $taxes->groupBy('employee_id')
            ->map(function ($employeeTaxes) {
                return $employeeTaxes->groupBy('tax_period')
                    ->map(function ($periodTaxes) {
                        return [
                            'period' => $periodTaxes->first()->tax_period,
                            'calculations' => $periodTaxes->count(),
                            'latest_calculation' => $periodTaxes->sortByDesc('updated_at')->first(),
                            'status_changes' => $periodTaxes->pluck('status')->unique()->values(),
                            'total_tax_amount' => $periodTaxes->sum('tax_amount'),
                        ];
                    });
            });

        return view('taxes.audit-trail', compact('auditTrail'));
    }

    /**
     * Export tax report to Excel.
     */
    public function export(Request $request)
    {
        $user = Auth::user();
        
        $query = Tax::with(['employee'])
            ->where('company_id', $user->company_id);

        if ($request->filled('period')) {
            $query->where('tax_period', $request->period);
        }

        $taxes = $query->orderBy('tax_period', 'desc')->get();

        // Generate Excel file
        $filename = 'tax_report_' . date('Y-m-d_H-i-s') . '.xlsx';
        
        // For now, return CSV format
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($taxes) {
            $file = fopen('php://output', 'w');
            
            // Add headers
            fputcsv($file, [
                'Employee Name',
                'Tax Period',
                'Taxable Income',
                'PTKP Status',
                'PTKP Amount',
                'Taxable Base',
                'Tax Amount',
                'Tax Rate',
                'Status'
            ]);

            // Add data
            foreach ($taxes as $tax) {
                fputcsv($file, [
                    $tax->employee->name,
                    $tax->tax_period,
                    number_format($tax->taxable_income, 2),
                    $tax->ptkp_status,
                    number_format($tax->ptkp_amount, 2),
                    number_format($tax->taxable_base, 2),
                    number_format($tax->tax_amount, 2),
                    number_format($tax->tax_rate * 100, 2) . '%',
                    $tax->status
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
} 