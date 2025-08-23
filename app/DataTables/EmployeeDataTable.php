<?php

namespace App\DataTables;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class EmployeeDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', function ($employee) {
                return '
                    <div class="btn-group" role="group">
                        <a href="' . route('employees.show', $employee->id) . '" class="btn btn-sm btn-info">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="' . route('employees.edit', $employee->id) . '" class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button type="button" class="btn btn-sm btn-danger delete-btn" data-id="' . $employee->id . '" data-name="' . $employee->name . '">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                ';
            })
            ->addColumn('status_badge', function ($employee) {
                $statusClass = [
                    'active' => 'badge badge-success',
                    'inactive' => 'badge badge-warning',
                    'terminated' => 'badge badge-danger'
                ];
                
                $statusText = [
                    'active' => 'Aktif',
                    'inactive' => 'Tidak Aktif',
                    'terminated' => 'Berhenti'
                ];
                
                return '<span class="' . $statusClass[$employee->status] . '">' . $statusText[$employee->status] . '</span>';
            })
            ->addColumn('salary_formatted', function ($employee) {
                return 'Rp ' . number_format($employee->basic_salary, 0, ',', '.');
            })
            ->addColumn('join_date_formatted', function ($employee) {
                return date('d/m/Y', strtotime($employee->join_date));
            })
            ->rawColumns(['action', 'status_badge', 'salary_formatted', 'join_date_formatted'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Employee $model): QueryBuilder
    {
        return $model->currentCompany()->select([
            'id',
            'employee_id',
            'name',
            'email',
            'phone',
            'department',
            'position',
            'join_date',
            'basic_salary',
            'status'
        ]);
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('employees-table')
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
            Column::make('id')->title('ID')->width(50),
            Column::make('employee_id')->title('ID Karyawan')->width(120),
            Column::make('name')->title('Nama')->width(200),
            Column::make('email')->title('Email')->width(200),
            Column::make('phone')->title('Telepon')->width(120),
            Column::make('department')->title('Departemen')->width(150),
            Column::make('position')->title('Jabatan')->width(150),
            Column::make('join_date_formatted')->title('Tanggal Bergabung')->width(130),
            Column::make('salary_formatted')->title('Gaji Pokok')->width(130),
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
        return 'Employees_' . date('YmdHis');
    }
} 