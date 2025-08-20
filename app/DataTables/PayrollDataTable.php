<?php

namespace App\DataTables;

use App\Models\Payroll;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class PayrollDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', function ($payroll) {
                $buttons = '<div class="btn-group" role="group">';
                $buttons .= '<a href="' . route('payrolls.show', $payroll->id) . '" class="btn btn-sm btn-info" title="Detail"><i class="fas fa-eye"></i></a>';
                
                if ($payroll->status === 'draft') {
                    $buttons .= '<a href="' . route('payrolls.edit', $payroll->id) . '" class="btn btn-sm btn-warning" title="Edit"><i class="fas fa-edit"></i></a>';
                    $buttons .= '<button type="button" class="btn btn-sm btn-success approve-btn" data-id="' . $payroll->id . '" data-name="' . htmlspecialchars($payroll->employee->name) . '" title="Approve"><i class="fas fa-check"></i></button>';
                    $buttons .= '<button type="button" class="btn btn-sm btn-danger reject-btn" data-id="' . $payroll->id . '" data-name="' . htmlspecialchars($payroll->employee->name) . '" title="Reject"><i class="fas fa-times"></i></button>';
                    $buttons .= '<button type="button" class="btn btn-sm btn-danger delete-btn" data-id="' . $payroll->id . '" data-name="' . htmlspecialchars($payroll->employee->name . ' - ' . $payroll->period) . '" title="Delete"><i class="fas fa-trash"></i></button>';
                }
                
                $buttons .= '</div>';
                return $buttons;
            })
            ->addColumn('employee_name', function ($payroll) {
                return $payroll->employee->name;
            })
            ->addColumn('employee_department', function ($payroll) {
                return $payroll->employee->department;
            })
            ->addColumn('status_badge', function ($payroll) {
                $statusClass = [
                    'draft' => 'badge badge-warning',
                    'approved' => 'badge badge-success',
                    'paid' => 'badge badge-info',
                    'rejected' => 'badge badge-danger'
                ];
                
                $statusText = [
                    'draft' => 'Draft',
                    'approved' => 'Disetujui',
                    'paid' => 'Dibayar',
                    'rejected' => 'Ditolak'
                ];
                
                return '<span class="' . $statusClass[$payroll->status] . '">' . $statusText[$payroll->status] . '</span>';
            })
            ->addColumn('salary_formatted', function ($payroll) {
                return 'Rp ' . number_format($payroll->basic_salary, 0, ',', '.');
            })
            ->addColumn('overtime_formatted', function ($payroll) {
                return 'Rp ' . number_format($payroll->overtime ?? 0, 0, ',', '.');
            })
            ->addColumn('bonus_formatted', function ($payroll) {
                return 'Rp ' . number_format($payroll->bonus ?? 0, 0, ',', '.');
            })
            ->addColumn('deductions_formatted', function ($payroll) {
                $totalDeductions = ($payroll->deduction ?? 0) + ($payroll->tax_amount ?? 0) + ($payroll->bpjs_amount ?? 0);
                return 'Rp ' . number_format($totalDeductions, 0, ',', '.');
            })
            ->addColumn('total_formatted', function ($payroll) {
                return 'Rp ' . number_format($payroll->total_salary, 0, ',', '.');
            })
            ->addColumn('generated_info', function ($payroll) {
                $generatedAt = $payroll->generated_at ? $payroll->generated_at->format('d/m/Y H:i') : '-';
                $generatedBy = $payroll->generatedBy->name ?? '-';
                return $generatedAt . '<br><small class="text-muted">by ' . $generatedBy . '</small>';
            })
            ->rawColumns(['action', 'status_badge', 'salary_formatted', 'overtime_formatted', 'bonus_formatted', 'deductions_formatted', 'total_formatted', 'generated_info'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Payroll $model): QueryBuilder
    {
        // Use scopeCurrentCompany to filter by user's company
        return $model->currentCompany()
            ->with(['employee', 'generatedBy'])
            ->select([
                'id',
                'employee_id',
                'company_id',
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
                'payment_date',
                'notes',
                'generated_by',
                'generated_at'
            ]);
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('payrolls-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->dom('Bfrtip')
                    ->orderBy(1)
                    ->selectStyleSingle()
                    ->responsive(true)
                    ->autoWidth(false)
                    ->buttons([
                        Button::make('excel'),
                        Button::make('csv'),
                        Button::make('pdf'),
                        Button::make('print'),
                        Button::make('reset'),
                        Button::make('reload')
                    ])
                    ->language([
                        'url' => '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
                    ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('id')->title('ID')->width(50)->responsivePriority(1),
            Column::make('employee_name')->title('Nama Karyawan')->width(200)->responsivePriority(1),
            Column::make('employee_department')->title('Departemen')->width(150)->responsivePriority(2),
            Column::make('period')->title('Periode')->width(100)->responsivePriority(1),
            Column::make('salary_formatted')->title('Gaji Pokok')->width(130)->responsivePriority(2),
            Column::make('overtime_formatted')->title('Lembur')->width(120)->responsivePriority(3),
            Column::make('bonus_formatted')->title('Bonus')->width(120)->responsivePriority(3),
            Column::make('deductions_formatted')->title('Potongan')->width(130)->responsivePriority(3),
            Column::make('total_formatted')->title('Total Gaji')->width(130)->responsivePriority(1),
            Column::make('status_badge')->title('Status')->width(100)->responsivePriority(1),
            Column::make('generated_info')->title('Generated')->width(150)->responsivePriority(2),
            Column::computed('action')
                  ->exportable(false)
                  ->printable(false)
                  ->width(150)
                  ->addClass('text-center')
                  ->responsivePriority(1)
                  ->title('Aksi'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Payroll_' . date('YmdHis');
    }
} 