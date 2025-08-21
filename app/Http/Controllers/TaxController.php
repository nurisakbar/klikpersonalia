<?php

namespace App\Http\Controllers;

use App\Models\Tax;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\Company;
use App\Services\TaxService;
use App\DataTables\TaxDataTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class TaxController extends Controller
{
    protected $taxService;

    public function __construct(TaxService $taxService)
    {
        $this->taxService = $taxService;
    }

    /**
     * Display a listing of taxes.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $employees = Employee::where('company_id', $user->company_id)->get();

        return view('taxes.index', compact('employees'));
    }

    /**
     * Get taxes data for DataTable
     */
    public function data(Request $request)
    {
        try {
            $user = Auth::user();
            $taxes = Tax::with(['employee'])
                ->where('company_id', $user->company_id)
                ->select([
                    'taxes.id',
                    'taxes.employee_id',
                    'taxes.tax_period',
                    'taxes.taxable_income',
                    'taxes.ptkp_amount',
                    'taxes.tax_amount',
                    'taxes.tax_rate',
                    'taxes.status'
                ]);

            // Apply filters
            if ($request->filled('period_filter')) {
                $taxes->where('tax_period', $request->period_filter);
            }

            if ($request->filled('employee_filter')) {
                $taxes->where('employee_id', $request->employee_filter);
            }

            if ($request->filled('status_filter')) {
                $taxes->where('status', $request->status_filter);
            }

            return DataTables::of($taxes)
                ->addColumn('action', function ($tax) {
                    return '
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-info view-btn" data-id="' . $tax->id . '" title="Detail">
                                <i class="fas fa-eye"></i>
                            </button>
                            <a href="' . route('taxes.edit', $tax->id) . '" class="btn btn-sm btn-warning" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-danger delete-btn" data-id="' . $tax->id . '" data-name="Tax #' . $tax->id . '" title="Hapus">
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
                        return date('M Y', strtotime($tax->tax_period . '-01'));
                    } catch (\Exception $e) {
                        return $tax->tax_period;
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
            \Log::error('TaxDataTable error: ' . $e->getMessage());
            return response()->json([
                'draw' => intval($request->get('draw')),
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
        $user = Auth::user();
        $employees = Employee::where('company_id', $user->company_id)->get();
        $ptkpStatuses = Tax::PTKP_STATUSES;

        return view('taxes.create', compact('employees', 'ptkpStatuses'));
    }

    /**
     * Store a newly created tax calculation.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'tax_period' => 'required|date_format:Y-m',
            'taxable_income' => 'required|numeric|min:0',
            'ptkp_status' => 'required|in:' . implode(',', array_keys(Tax::PTKP_STATUSES)),
            'notes' => 'nullable|string',
        ]);

        $employee = Employee::findOrFail($request->employee_id);
        
        // Check if employee belongs to user's company
        if ($employee->company_id !== $user->company_id) {
            return redirect()->back()->withErrors(['employee_id' => 'Employee not found.']);
        }

        // Check if tax calculation already exists for this period
        $existingTax = Tax::where('company_id', $user->company_id)
            ->where('employee_id', $request->employee_id)
            ->where('tax_period', $request->tax_period)
            ->first();

        if ($existingTax) {
            return redirect()->back()->withErrors(['tax_period' => 'Tax calculation already exists for this period.']);
        }

        // Calculate tax
        $taxCalculation = Tax::calculatePPh21($employee, $request->taxable_income);

        // Create tax record
        $tax = Tax::create([
            'company_id' => $user->company_id,
            'employee_id' => $request->employee_id,
            'tax_period' => $request->tax_period,
            'taxable_income' => $request->taxable_income,
            'ptkp_status' => $request->ptkp_status,
            'ptkp_amount' => $taxCalculation['ptkp_amount'],
            'taxable_base' => $taxCalculation['taxable_base'],
            'tax_amount' => $taxCalculation['tax_amount'],
            'tax_bracket' => $taxCalculation['tax_bracket'],
            'tax_rate' => $taxCalculation['tax_rate'],
            'status' => Tax::STATUS_CALCULATED,
            'notes' => $request->notes,
        ]);

        return redirect()->route('taxes.show', $tax)->with('success', 'Tax calculation created successfully.');
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
                    'data' => [
                        'id' => $tax->id,
                        'employee_name' => $tax->employee->name ?? '-',
                        'employee_id' => $tax->employee->employee_id ?? '-',
                        'tax_period' => $tax->tax_period ? date('M Y', strtotime($tax->tax_period . '-01')) : '-',
                        'taxable_income' => 'Rp ' . number_format($tax->taxable_income, 0, ',', '.'),
                        'ptkp_status' => $tax->ptkp_status,
                        'ptkp_amount' => 'Rp ' . number_format($tax->ptkp_amount, 0, ',', '.'),
                        'taxable_base' => 'Rp ' . number_format($tax->taxable_base, 0, ',', '.'),
                        'tax_amount' => 'Rp ' . number_format($tax->tax_amount, 0, ',', '.'),
                        'tax_rate' => number_format($tax->tax_rate * 100, 1) . '%',
                        'status' => $tax->status,
                        'notes' => $tax->notes ?? '-',
                    ]
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
        $user = Auth::user();
        
        // Check if tax belongs to user's company
        if ($tax->company_id !== $user->company_id) {
            abort(404);
        }

        $ptkpStatuses = Tax::PTKP_STATUSES;
        
        return view('taxes.edit', compact('tax', 'ptkpStatuses'));
    }

    /**
     * Update the specified tax calculation.
     */
    public function update(Request $request, Tax $tax)
    {
        $user = Auth::user();
        
        // Check if tax belongs to user's company
        if ($tax->company_id !== $user->company_id) {
            abort(404);
        }

        $request->validate([
            'taxable_income' => 'required|numeric|min:0',
            'ptkp_status' => 'required|in:' . implode(',', array_keys(Tax::PTKP_STATUSES)),
            'status' => 'required|in:' . implode(',', [Tax::STATUS_PENDING, Tax::STATUS_CALCULATED, Tax::STATUS_PAID, Tax::STATUS_VERIFIED]),
            'notes' => 'nullable|string',
        ]);

        $employee = $tax->employee;
        
        // Recalculate tax
        $taxCalculation = Tax::calculatePPh21($employee, $request->taxable_income);

        $tax->update([
            'taxable_income' => $request->taxable_income,
            'ptkp_status' => $request->ptkp_status,
            'ptkp_amount' => $taxCalculation['ptkp_amount'],
            'taxable_base' => $taxCalculation['taxable_base'],
            'tax_amount' => $taxCalculation['tax_amount'],
            'tax_bracket' => $taxCalculation['tax_bracket'],
            'tax_rate' => $taxCalculation['tax_rate'],
            'status' => $request->status,
            'notes' => $request->notes,
        ]);

        return redirect()->route('taxes.show', $tax)->with('success', 'Tax calculation updated successfully.');
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
                ], 500);
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

        $period = $request->year . '-' . str_pad($request->month, 2, '0', STR_PAD_LEFT);
        
        // Get all employees for the company
        $employees = Employee::where('company_id', $user->company_id)->get();
        
        $calculatedCount = 0;
        $errors = [];

        foreach ($employees as $employee) {
            try {
                // Check if tax calculation already exists
                $existingTax = Tax::where('company_id', $user->company_id)
                    ->where('employee_id', $employee->id)
                    ->where('tax_period', $period)
                    ->first();

                if ($existingTax) {
                    continue; // Skip if already calculated
                }

                // Get payroll for this period
                $payroll = Payroll::where('company_id', $user->company_id)
                    ->where('employee_id', $employee->id)
                    ->where('month', $request->month)
                    ->where('year', $request->year)
                    ->first();

                if (!$payroll) {
                    $errors[] = "No payroll found for {$employee->name} in {$period}";
                    continue;
                }

                // Calculate taxable income (basic salary + allowances + overtime + bonus)
                $taxableIncome = $payroll->basic_salary + 
                               $payroll->allowances + 
                               $payroll->overtime + 
                               $payroll->bonus;

                // Calculate tax
                $taxCalculation = Tax::calculatePPh21($employee, $taxableIncome);

                // Create tax record
                Tax::create([
                    'company_id' => $user->company_id,
                    'employee_id' => $employee->id,
                    'payroll_id' => $payroll->id,
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
                $errors[] = "Error calculating tax for {$employee->name}: " . $e->getMessage();
            }
        }

        $message = "Successfully calculated tax for {$calculatedCount} employees.";
        if (!empty($errors)) {
            $message .= " Errors: " . implode(', ', $errors);
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