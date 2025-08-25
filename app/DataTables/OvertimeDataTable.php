<?php

namespace App\DataTables;

use App\Models\Overtime;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class OvertimeDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', function ($overtime) {
                $buttons = '<div class="btn-group" role="group">';
                $buttons .= '<button type="button" class="btn btn-sm btn-info view-btn" data-id="' . $overtime->id . '" title="Detail"><i class="fas fa-eye"></i></button>';
                
                if ($overtime->status === 'pending') {
                    $buttons .= '<button type="button" class="btn btn-sm btn-warning edit-btn" data-id="' . $overtime->id . '" title="Edit"><i class="fas fa-edit"></i></button>';
                    $buttons .= '<button type="button" class="btn btn-sm btn-danger delete-btn" data-id="' . $overtime->id . '" data-name="' . htmlspecialchars($overtime->overtime_type . ' overtime on ' . $overtime->formatted_date) . '" title="Cancel"><i class="fas fa-trash"></i></button>';
                }
                
                $buttons .= '</div>';
                return $buttons;
            })
            ->addColumn('type_badge', function ($overtime) {
                $typeClass = [
                    'regular' => 'badge badge-info',
                    'holiday' => 'badge badge-danger',
                    'weekend' => 'badge badge-warning',
                    'emergency' => 'badge badge-dark'
                ];
                
                $typeText = [
                    'regular' => 'Regular',
                    'holiday' => 'Hari Libur',
                    'weekend' => 'Akhir Pekan',
                    'emergency' => 'Darurat'
                ];
                
                return '<span class="' . $typeClass[$overtime->overtime_type] . '">' . $typeText[$overtime->overtime_type] . '</span>';
            })
            ->addColumn('status_badge', function ($overtime) {
                $statusClass = [
                    'pending' => 'badge badge-warning',
                    'approved' => 'badge badge-success',
                    'rejected' => 'badge badge-danger',
                    'cancelled' => 'badge badge-secondary'
                ];
                
                $statusText = [
                    'pending' => 'Menunggu',
                    'approved' => 'Disetujui',
                    'rejected' => 'Ditolak',
                    'cancelled' => 'Dibatalkan'
                ];
                
                return '<span class="' . $statusClass[$overtime->status] . '">' . $statusText[$overtime->status] . '</span>';
            })
            ->addColumn('total_hours_formatted', function ($overtime) {
                return $overtime->total_hours . ' jam';
            })
            ->addColumn('created_at_formatted', function ($overtime) {
                return $overtime->created_at->format('d/m/Y H:i');
            })
            ->rawColumns(['action', 'type_badge', 'status_badge', 'total_hours_formatted', 'created_at_formatted'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Overtime $model): QueryBuilder
    {
        return $model->with(['employee'])
            ->where('employee_id', auth()->user()->employee->id)
            ->select([
                'id',
                'employee_id',
                'overtime_type',
                'date',
                'start_time',
                'end_time',
                'total_hours',
                'reason',
                'status',
                'created_at'
            ]);
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('overtimes-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->dom('Bfrtip')
                    ->orderBy(5, 'desc')
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
            Column::make('id')->title('No')->width(50),
            Column::make('type_badge')->title('Jenis Lembur')->width(120),
            Column::make('date')->title('Tanggal')->width(120),
            Column::make('start_time')->title('Waktu Mulai')->width(100),
            Column::make('end_time')->title('Waktu Selesai')->width(100),
            Column::make('total_hours_formatted')->title('Total Jam')->width(100),
            Column::make('status_badge')->title('Status')->width(100),
            Column::make('created_at_formatted')->title('Dibuat')->width(130),
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
        return 'Overtimes_' . date('YmdHis');
    }
}
