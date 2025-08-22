<?php

namespace App\DataTables;

use App\Models\Leave;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class LeaveDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', function ($leave) {
                $buttons = '<div class="btn-group" role="group">';
                $buttons .= '<button type="button" class="btn btn-sm btn-info view-btn" data-id="' . $leave->id . '" title="Detail"><i class="fas fa-eye"></i></button>';
                
                if ($leave->status === 'pending') {
                    $buttons .= '<button type="button" class="btn btn-sm btn-warning edit-btn" data-id="' . $leave->id . '" title="Edit"><i class="fas fa-edit"></i></button>';
                    $buttons .= '<button type="button" class="btn btn-sm btn-danger delete-btn" data-id="' . $leave->id . '" data-name="' . htmlspecialchars($leave->leave_type . ' leave from ' . $leave->formatted_start_date . ' to ' . $leave->formatted_end_date) . '" title="Cancel"><i class="fas fa-trash"></i></button>';
                }
                
                $buttons .= '</div>';
                return $buttons;
            })
            ->addColumn('type_badge', function ($leave) {
                $typeClass = [
                    'annual' => 'badge badge-primary',
                    'sick' => 'badge badge-warning',
                    'maternity' => 'badge badge-info',
                    'paternity' => 'badge badge-secondary',
                    'other' => 'badge badge-dark'
                ];
                
                $typeText = [
                    'annual' => 'Tahunan',
                    'sick' => 'Sakit',
                    'maternity' => 'Melahirkan',
                    'paternity' => 'Melahirkan (Pria)',
                    'other' => 'Lainnya'
                ];
                
                return '<span class="' . $typeClass[$leave->leave_type] . '">' . $typeText[$leave->leave_type] . '</span>';
            })
            ->addColumn('status_badge', function ($leave) {
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
                
                return '<span class="' . $statusClass[$leave->status] . '">' . $statusText[$leave->status] . '</span>';
            })
            ->addColumn('total_days_formatted', function ($leave) {
                return $leave->total_days . ' hari';
            })
            ->addColumn('created_at_formatted', function ($leave) {
                return $leave->created_at->format('d/m/Y H:i');
            })
            ->rawColumns(['action', 'type_badge', 'status_badge', 'total_days_formatted', 'created_at_formatted'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Leave $model): QueryBuilder
    {
        return $model->with(['employee'])
            ->where('employee_id', auth()->user()->employee->id)
            ->select([
                'id',
                'employee_id',
                'leave_type',
                'start_date',
                'end_date',
                'total_days',
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
                    ->setTableId('leaves-table')
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
            Column::make('type_badge')->title('Jenis Cuti')->width(120),
            Column::make('start_date')->title('Tanggal Mulai')->width(120),
            Column::make('end_date')->title('Tanggal Selesai')->width(120),
            Column::make('total_days_formatted')->title('Total Hari')->width(100),
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
        return 'Leaves_' . date('YmdHis');
    }
}
