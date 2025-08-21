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

class TaxDataTable extends DataTable
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
                        <a href="javascript:void(0)" class="btn btn-sm btn-info view-btn" data-id="' . $tax->id . '">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="' . route('taxes.edit', $tax->id) . '" class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button type="button" class="btn btn-sm btn-danger delete-btn" data-id="' . $tax->id . '" data-name="Tax #' . $tax->id . '">
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
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Tax $model): QueryBuilder
    {
        $user = Auth::user();
        
        return $model->with(['employee'])
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
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('taxes-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->dom('Bfrtip')
                    ->orderBy(1)
                    ->selectStyleSingle()
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
            Column::make('id')->title('ID')->width(50),
            Column::make('employee_name')->title('Nama Karyawan')->width(200),
            Column::make('employee_id_display')->title('ID Karyawan')->width(120),
            Column::make('tax_period_formatted')->title('Periode Pajak')->width(120),
            Column::make('taxable_income_formatted')->title('Pendapatan Kena Pajak')->width(180),
            Column::make('ptkp_amount_formatted')->title('PTKP')->width(120),
            Column::make('tax_amount_formatted')->title('Jumlah Pajak')->width(150),
            Column::make('tax_rate_formatted')->title('Tarif Pajak')->width(100),
            Column::make('status_badge')->title('Status')->width(100),
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
        return 'Taxes_' . date('YmdHis');
    }
}
