<?php

namespace App\DataTables;

use App\Models\Tax;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;
use Illuminate\Support\Facades\Auth;

class TaxReportDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', function ($tax) {
                return '
                    <div class="btn-group" role="group">
                        <a href="' . route('taxes.show', $tax->id) . '" class="btn btn-sm btn-info">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="' . route('taxes.edit', $tax->id) . '" class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i>
                        </a>
                    </div>
                ';
            })
            ->addColumn('employee_info', function ($tax) {
                return '<strong>' . ($tax->employee->name ?? '-') . '</strong><br>' .
                       '<small class="text-muted">' . ($tax->employee->employee_id ?? '-') . '</small>';
            })
            ->addColumn('tax_period_formatted', function ($tax) {
                return $tax->tax_period_formatted ?? '-';
            })
            ->addColumn('taxable_income_formatted', function ($tax) {
                return $tax->taxable_income_formatted ?? 'Rp 0';
            })
            ->addColumn('tax_amount_formatted', function ($tax) {
                return $tax->tax_amount_formatted ?? 'Rp 0';
            })
            ->addColumn('status_badge', function ($tax) {
                return $tax->status_badge ?? '<span class="badge badge-secondary">-</span>';
            })
            ->addColumn('created_at_formatted', function ($tax) {
                return $tax->created_at_formatted ?? '-';
            })
            ->rawColumns(['action', 'employee_info', 'tax_period_formatted', 'taxable_income_formatted', 'tax_amount_formatted', 'status_badge', 'created_at_formatted'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Tax $model): QueryBuilder
    {
        return $model->currentCompany()
            ->with(['employee', 'payroll'])
            ->select([
                'id',
                'employee_id',
                'company_id',
                'tax_period',
                'taxable_income',
                'ptkp_amount',
                'taxable_base',
                'tax_amount',
                'status',
                'created_at',
                'updated_at'
            ]);
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('tax-report-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->dom('Bfrtip')
                    ->orderBy(6, 'desc')
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
            Column::computed('employee_info')->title('Karyawan')->width(200)->responsivePriority(1),
            Column::computed('tax_period_formatted')->title('Periode Pajak')->width(150)->responsivePriority(2),
            Column::computed('taxable_income_formatted')->title('Pendapatan Kena Pajak')->width(180)->responsivePriority(2),
            Column::computed('tax_amount_formatted')->title('Jumlah Pajak')->width(150)->responsivePriority(2),
            Column::computed('status_badge')->title('Status')->width(100)->responsivePriority(1),
            Column::computed('created_at_formatted')->title('Tanggal Dibuat')->width(130)->responsivePriority(2),
            Column::computed('action')
                  ->exportable(false)
                  ->printable(false)
                  ->width(120)
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
        return 'TaxReport_' . date('YmdHis');
    }
}
