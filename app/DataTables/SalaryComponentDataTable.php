<?php

namespace App\DataTables;

use App\Models\SalaryComponent;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

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
                return '
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-info view-btn" data-id="' . $component->id . '" title="View">
                            <i class="fas fa-eye"></i>
                        </button>
                        <a href="' . route('salary-components.edit', $component->id) . '" class="btn btn-sm btn-warning" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button type="button" class="btn btn-sm btn-danger delete-btn" data-id="' . $component->id . '" data-name="' . $component->name . '" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                ';
            })
            ->addColumn('type_text', function ($component) {
                return $component->type === 'earning' ? 'Pendapatan' : 'Potongan';
            })
            ->addColumn('status_text', function ($component) {
                return $component->is_active ? 'Aktif' : 'Nonaktif';
            })
            ->addColumn('formatted_value', function ($component) {
                return 'Rp ' . number_format($component->default_value, 0, ',', '.');
            })
            ->addColumn('is_taxable_badge', function ($component) {
                if ($component->is_taxable) {
                    return '<span class="badge badge-success">Ya</span>';
                } else {
                    return '<span class="badge badge-secondary">Tidak</span>';
                }
            })
            ->addColumn('is_bpjs_calculated_badge', function ($component) {
                if ($component->is_bpjs_calculated) {
                    return '<span class="badge badge-info">Ya</span>';
                } else {
                    return '<span class="badge badge-secondary">Tidak</span>';
                }
            })
            ->addColumn('created_at_formatted', function ($component) {
                return $component->created_at ? $component->created_at->format('d/m/Y') : 'N/A';
            })
            ->rawColumns(['action', 'is_taxable_badge', 'is_bpjs_calculated_badge'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(SalaryComponent $model): QueryBuilder
    {
        return $model->currentCompany()->select([
            'id',
            'name',
            'description',
            'default_value',
            'type',
            'is_active',
            'is_taxable',
            'is_bpjs_calculated',
            'sort_order',
            'created_at'
        ]);
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('salary-components-table')
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
                        Button::make('reload')
                    ])
                    ->language([
                        'sProcessing' => 'Memproses...',
                        'sLengthMenu' => 'Tampilkan _MENU_ entri',
                        'sZeroRecords' => 'Tidak ditemukan data yang sesuai',
                        'sInfo' => 'Menampilkan _START_ sampai _END_ dari _TOTAL_ entri',
                        'sInfoEmpty' => 'Menampilkan 0 sampai 0 dari 0 entri',
                        'sInfoFiltered' => '(disaring dari _MAX_ entri keseluruhan)',
                        'sSearch' => 'Cari:',
                        'oPaginate' => [
                            'sFirst' => 'Pertama',
                            'sPrevious' => 'Sebelumnya',
                            'sNext' => 'Selanjutnya',
                            'sLast' => 'Terakhir'
                        ]
                    ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('sort_order')->title('Urutan')->width(80),
            Column::make('name')->title('Nama Komponen')->width(200),
            Column::make('type_text')->title('Tipe')->width(100),
            Column::make('formatted_value')->title('Nilai Default')->width(150),
            Column::make('description')->title('Deskripsi')->width(250),
            Column::make('status_text')->title('Status')->width(100),
            Column::make('is_taxable_badge')->title('Pajak')->width(80),
            Column::make('is_bpjs_calculated_badge')->title('BPJS')->width(80),
            Column::make('created_at_formatted')->title('Dibuat')->width(120),
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
        return 'SalaryComponents_' . date('YmdHis');
    }
}
