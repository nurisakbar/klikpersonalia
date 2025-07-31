<?php

namespace App\Http\Controllers;

use App\DataTables\PayrollDataTable;
use App\Models\Payroll;
use App\Models\Employee;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PayrollController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(PayrollDataTable $dataTable)
    {
        return $dataTable->render('payroll.index');
    }

    /**
     * Get payroll data for DataTables.
     */
    public function getData()
    {
        $payrolls = Payroll::with('employee')->select([
            'id',
            'employee_id',
            'period',
            'basic_salary',
            'allowance',
            'overtime',
            'bonus',
            'deduction',
            'tax_amount',
            'bpjs_amount',
            'total_salary',
            'status',
            'payment_date'
        ]);

        return DataTables::of($payrolls)
            ->addColumn('action', function ($payroll) {
                return '
                    <div class="btn-group" role="group">
                        <a href="' . route('payroll.show', $payroll->id) . '" class="btn btn-sm btn-info" title="Detail">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="' . route('payroll.edit', $payroll->id) . '" class="btn btn-sm btn-warning" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button type="button" class="btn btn-sm btn-danger delete-btn" data-id="' . $payroll->id . '" data-name="' . $payroll->employee->name . ' - ' . $payroll->period . '" title="Hapus">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                ';
            })
            ->addColumn('employee_name', function ($payroll) {
                return $payroll->employee->name;
            })
            ->addColumn('employee_department', function ($payroll) {
                return $payroll->employee->department;
            })
            ->addColumn('status_badge', function ($payroll) {
                $statusClass = [
                    'draft' => 'badge badge-secondary',
                    'approved' => 'badge badge-warning',
                    'paid' => 'badge badge-success'
                ];
                
                $statusText = [
                    'draft' => 'Draft',
                    'approved' => 'Disetujui',
                    'paid' => 'Dibayar'
                ];
                
                return '<span class="' . $statusClass[$payroll->status] . '">' . $statusText[$payroll->status] . '</span>';
            })
            ->addColumn('salary_formatted', function ($payroll) {
                return 'Rp ' . number_format($payroll->basic_salary, 0, ',', '.');
            })
            ->addColumn('total_formatted', function ($payroll) {
                return 'Rp ' . number_format($payroll->total_salary, 0, ',', '.');
            })
            ->addColumn('payment_date_formatted', function ($payroll) {
                return $payroll->payment_date ? date('d/m/Y', strtotime($payroll->payment_date)) : '-';
            })
            ->rawColumns(['action', 'status_badge', 'salary_formatted', 'total_formatted', 'payment_date_formatted'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $employees = Employee::active()->get();
        
        $periods = [
            'Januari 2024',
            'Februari 2024',
            'Maret 2024',
            'April 2024',
            'Mei 2024',
            'Juni 2024',
            'Juli 2024',
            'Agustus 2024',
            'September 2024',
            'Oktober 2024',
            'November 2024',
            'Desember 2024'
        ];

        return view('payroll.create', compact('employees', 'periods'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'period' => 'required|string',
            'basic_salary' => 'required|numeric|min:0',
            'allowance' => 'required|numeric|min:0',
            'overtime' => 'required|numeric|min:0',
            'bonus' => 'required|numeric|min:0',
            'deduction' => 'required|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'bpjs_amount' => 'nullable|numeric|min:0',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        Payroll::create([
            'employee_id' => $request->employee_id,
            'period' => $request->period,
            'basic_salary' => $request->basic_salary,
            'allowance' => $request->allowance,
            'overtime' => $request->overtime,
            'bonus' => $request->bonus,
            'deduction' => $request->deduction,
            'tax_amount' => $request->tax_amount ?? 0,
            'bpjs_amount' => $request->bpjs_amount ?? 0,
            'status' => 'draft',
            'payment_date' => $request->payment_date,
            'notes' => $request->notes,
        ]);

        return redirect()->route('payroll.index')->with('success', 'Data payroll berhasil ditambahkan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $payroll = Payroll::with('employee')->findOrFail($id);
        return view('payroll.show', compact('payroll'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $payroll = Payroll::findOrFail($id);
        $employees = Employee::active()->get();
        
        $periods = [
            'Januari 2024',
            'Februari 2024',
            'Maret 2024',
            'April 2024',
            'Mei 2024',
            'Juni 2024',
            'Juli 2024',
            'Agustus 2024',
            'September 2024',
            'Oktober 2024',
            'November 2024',
            'Desember 2024'
        ];

        $statuses = ['draft', 'approved', 'paid'];

        return view('payroll.edit', compact('payroll', 'employees', 'periods', 'statuses'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'period' => 'required|string',
            'basic_salary' => 'required|numeric|min:0',
            'allowance' => 'required|numeric|min:0',
            'overtime' => 'required|numeric|min:0',
            'bonus' => 'required|numeric|min:0',
            'deduction' => 'required|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'bpjs_amount' => 'nullable|numeric|min:0',
            'payment_date' => 'required|date',
            'status' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $payroll = Payroll::findOrFail($id);
        $payroll->update([
            'employee_id' => $request->employee_id,
            'period' => $request->period,
            'basic_salary' => $request->basic_salary,
            'allowance' => $request->allowance,
            'overtime' => $request->overtime,
            'bonus' => $request->bonus,
            'deduction' => $request->deduction,
            'tax_amount' => $request->tax_amount ?? 0,
            'bpjs_amount' => $request->bpjs_amount ?? 0,
            'status' => $request->status,
            'payment_date' => $request->payment_date,
            'notes' => $request->notes,
        ]);

        return redirect()->route('payroll.index')->with('success', 'Data payroll berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $payroll = Payroll::findOrFail($id);
            $payroll->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Data payroll berhasil dihapus!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data payroll: ' . $e->getMessage()
            ], 500);
        }
    }
}
