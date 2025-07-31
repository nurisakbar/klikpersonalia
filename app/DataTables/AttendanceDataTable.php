<?php

namespace App\DataTables;

use App\Models\Attendance;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class AttendanceDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', function ($attendance) {
                return '
                    <div class="btn-group" role="group">
                        <a href="' . route('attendance.show', $attendance->id) . '" class="btn btn-sm btn-info" title="Detail">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="' . route('attendance.edit', $attendance->id) . '" class="btn btn-sm btn-warning" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button type="button" class="btn btn-sm btn-danger delete-btn" data-id="' . $attendance->id . '" data-name="' . $attendance->employee->name . ' - ' . $attendance->formatted_date . '" title="Hapus">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                ';
            })
            ->addColumn('employee_name', function ($attendance) {
                return $attendance->employee->name;
            })
            ->addColumn('employee_department', function ($attendance) {
                return $attendance->employee->department;
            })
            ->addColumn('date_formatted', function ($attendance) {
                return $attendance->date->format('d/m/Y');
            })
            ->addColumn('check_in_formatted', function ($attendance) {
                return $attendance->check_in ? $attendance->check_in->format('H:i') : '-';
            })
            ->addColumn('check_out_formatted', function ($attendance) {
                return $attendance->check_out ? $attendance->check_out->format('H:i') : '-';
            })
            ->addColumn('total_hours_formatted', function ($attendance) {
                return $attendance->total_hours ? $attendance->total_hours . ' jam' : '-';
            })
            ->addColumn('overtime_hours_formatted', function ($attendance) {
                return $attendance->overtime_hours ? $attendance->overtime_hours . ' jam' : '-';
            })
            ->addColumn('status_badge', function ($attendance) {
                $statusClass = [
                    'present' => 'badge badge-success',
                    'absent' => 'badge badge-danger',
                    'late' => 'badge badge-warning',
                    'half_day' => 'badge badge-info',
                    'leave' => 'badge badge-secondary',
                    'holiday' => 'badge badge-primary'
                ];
                
                $statusText = [
                    'present' => 'Hadir',
                    'absent' => 'Tidak Hadir',
                    'late' => 'Terlambat',
                    'half_day' => 'Setengah Hari',
                    'leave' => 'Cuti',
                    'holiday' => 'Libur'
                ];
                
                return '<span class="' . $statusClass[$attendance->status] . '">' . $statusText[$attendance->status] . '</span>';
            })
            ->rawColumns(['action', 'status_badge'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Attendance $model): QueryBuilder
    {
        return $model->with('employee')->select([
            'id',
            'employee_id',
            'date',
            'check_in',
            'check_out',
            'total_hours',
            'overtime_hours',
            'status',
            'notes'
        ]);
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('attendance-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->dom('Bfrtip')
                    ->orderBy(2, 'desc') // Order by date descending
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
            Column::make('date_formatted')->title('Tanggal')->width(100),
            Column::make('check_in_formatted')->title('Check In')->width(100),
            Column::make('check_out_formatted')->title('Check Out')->width(100),
            Column::make('total_hours_formatted')->title('Total Jam')->width(100),
            Column::make('overtime_hours_formatted')->title('Lembur')->width(100),
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
        return 'Attendance_' . date('YmdHis');
    }
} 