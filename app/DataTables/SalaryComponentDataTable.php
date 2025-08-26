<?php

namespace App\DataTables;

use App\Models\SalaryComponent;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;
use Illuminate\Support\Facades\Auth;

class SalaryComponentDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', function ($component) {
                return view('salary-components.partials.actions', compact('component'))->render();
            })
            ->addColumn('status_badge', function ($component) {
                return view('salary-components.partials.status-badge', compact('component'))->render();
            })
            ->addColumn('type_badge', function ($component) {
                return view('salary-components.partials.type-badge', compact('component'))->render();
            })
            ->addColumn('formatted_value', function ($component) {
                return 'Rp ' . number_format($component->default_value, 0, ',', '.');
            })
            ->addColumn('checkbox', function ($component) {
                return view('salary-components.partials.checkbox', compact('component'))->render();
            })
            ->rawColumns(['action', 'status_badge', 'type_badge', 'checkbox'])
            ->setRowId('id')
            ->orderColumn('sort_order', 'sort_order $1')
            ->orderColumn('name', 'name $1')
            ->orderColumn('default_value', 'default_value $1');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(SalaryComponent $model): QueryBuilder
    {
        return $model->newQuery()
            ->where('company_id', Auth::user()->company_id)
            ->orderBy('sort_order')
            ->orderBy('name');
    }

    /**
     * Optional method if you want to use html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('salary-components-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom('Bfrtip')
            ->orderBy(1, 'asc')
            ->selectStyleSingle()
            ->buttons([
                Button::make('create')
                    ->text('<i class="fas fa-plus"></i> Tambah Komponen')
                    ->className('btn btn-primary btn-sm'),
                Button::make('excel')
                    ->text('<i class="fas fa-file-excel"></i> Excel')
                    ->className('btn btn-success btn-sm'),
                Button::make('pdf')
                    ->text('<i class="fas fa-file-pdf"></i> PDF')
                    ->className('btn btn-danger btn-sm'),
                Button::make('print')
                    ->text('<i class="fas fa-print"></i> Print')
                    ->className('btn btn-info btn-sm'),
                Button::make('reload')
                    ->text('<i class="fas fa-sync-alt"></i> Reload')
                    ->className('btn btn-secondary btn-sm'),
            ])
            ->parameters([
                'scrollX' => true,
                'responsive' => true,
                'autoWidth' => false,
                'language' => [
                    'url' => '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json',
                ],
                'initComplete' => "function() {
                    this.api().columns().every(function () {
                        var column = this;
                        var input = document.createElement(\"input\");
                        $(input).appendTo($(column.footer()).empty())
                        .on('change', function () {
                            column.search($(this).val(), false, false, true).draw();
                        });
                    });
                }",
            ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('checkbox')
                ->title('<input type="checkbox" id="select-all">')
                ->width(30)
                ->searchable(false)
                ->orderable(false)
                ->printable(false)
                ->exportable(false),
            Column::make('sort_order')
                ->title('Urutan')
                ->width(80)
                ->searchable(false),
            Column::make('name')
                ->title('Nama Komponen')
                ->width(200),
            Column::make('type_badge')
                ->title('Tipe')
                ->width(100)
                ->searchable(false)
                ->orderable(false),
            Column::make('formatted_value')
                ->title('Nilai Default')
                ->width(150)
                ->searchable(false),
            Column::make('description')
                ->title('Deskripsi')
                ->width(250),
            Column::make('status_badge')
                ->title('Status')
                ->width(100)
                ->searchable(false)
                ->orderable(false),
            Column::make('is_taxable')
                ->title('Pajak')
                ->width(80)
                ->render('function(data, type, row) {
                    return data ? "<span class=\"badge badge-success\">Ya</span>" : "<span class=\"badge badge-secondary\">Tidak</span>";
                }')
                ->searchable(false)
                ->orderable(false),
            Column::make('is_bpjs_calculated')
                ->title('BPJS')
                ->width(80)
                ->render('function(data, type, row) {
                    return data ? "<span class=\"badge badge-info\">Ya</span>" : "<span class=\"badge badge-secondary\">Tidak</span>";
                }')
                ->searchable(false)
                ->orderable(false),
            Column::make('created_at')
                ->title('Dibuat')
                ->width(120)
                ->render('function(data, type, row) {
                    if (type === "display") {
                        return moment(data).format("DD/MM/YYYY");
                    }
                    return data;
                }')
                ->searchable(false),
            Column::computed('action')
                ->title('Aksi')
                ->width(150)
                ->searchable(false)
                ->orderable(false)
                ->printable(false)
                ->exportable(false),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'SalaryComponents_' . date('YmdHis');
    }
}
