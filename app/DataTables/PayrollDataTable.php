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
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Payroll $model): QueryBuilder
    {
        return $model->with('employee')->select([
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
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('payroll-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->dom('Bfrtip')
                    ->orderBy(1)
                    ->selectStyleSingle()
                    ->buttons([
                        Button::make('excel'),
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
            Column::make('id')->title('ID')->width(50),
            Column::make('employee_name')->title('Nama Karyawan')->width(200),
            Column::make('employee_department')->title('Departemen')->width(150),
            Column::make('period')->title('Periode')->width(100),
            Column::make('salary_formatted')->title('Gaji Pokok')->width(130),
            Column::make('total_formatted')->title('Total Gaji')->width(130),
            Column::make('status_badge')->title('Status')->width(100),
            Column::make('payment_date_formatted')->title('Tanggal Bayar')->width(120),
            Column::computed('action')
                  ->exportable(false)
                  ->printable(false)
                  ->width(150)
                  ->addClass('text-center')
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