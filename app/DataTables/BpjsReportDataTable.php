<?php

namespace App\DataTables;

use App\Models\Bpjs;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;
use Carbon\Carbon;

class BpjsReportDataTable extends DataTable
{
    protected $period;
    protected $type;

    public function __construct($period = null, $type = null)
    {
        $this->period = $period ?? request()->get('period', now()->format('Y-m'));
        $this->type = $type ?? request()->get('type', 'both');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Bpjs $model): QueryBuilder
    {
        $query = $model->with(['employee.department', 'employee.position'])
            ->whereYear('created_at', Carbon::parse($this->period)->year)
            ->whereMonth('created_at', Carbon::parse($this->period)->month);

        if ($this->type !== 'both') {
            $query->where('bpjs_type', $this->type);
        }

        return $query;
    }

    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('employee_name', function ($item) {
                return $item->employee->name ?? 'N/A';
            })
            ->addColumn('employee_id', function ($item) {
                return $item->employee->employee_id ?? 'N/A';
            })
            ->addColumn('department', function ($item) {
                return $item->employee->department->name ?? 'N/A';
            })
            ->addColumn('bpjs_type_badge', function ($item) {
                if ($item->bpjs_type === 'kesehatan') {
                    return '<span class="badge badge-info"><i class="fas fa-heartbeat"></i> Kesehatan</span>';
                } else {
                    return '<span class="badge badge-success"><i class="fas fa-briefcase"></i> Ketenagakerjaan</span>';
                }
            })
            ->addColumn('base_salary_formatted', function ($item) {
                return 'Rp ' . number_format($item->base_salary, 0, ',', '.');
            })
            ->addColumn('employee_contribution_formatted', function ($item) {
                return 'Rp ' . number_format($item->employee_contribution, 0, ',', '.');
            })
            ->addColumn('company_contribution_formatted', function ($item) {
                return 'Rp ' . number_format($item->company_contribution, 0, ',', '.');
            })
            ->addColumn('total_contribution_formatted', function ($item) {
                return '<strong>Rp ' . number_format($item->total_contribution, 0, ',', '.') . '</strong>';
            })
            ->addColumn('status_badge', function ($item) {
                switch ($item->status) {
                    case 'pending':
                        return '<span class="badge badge-warning">Pending</span>';
                    case 'calculated':
                        return '<span class="badge badge-info">Calculated</span>';
                    case 'paid':
                        return '<span class="badge badge-success">Paid</span>';
                    case 'verified':
                        return '<span class="badge badge-primary">Verified</span>';
                    default:
                        return '<span class="badge badge-secondary">Unknown</span>';
                }
            })
            ->addColumn('actions', function ($item) {
                $actions = '<a href="' . route('bpjs.show', $item->id) . '" class="btn btn-sm btn-info" title="View"><i class="fas fa-eye"></i></a>';
                
                // Add delete button if user has permission
                if (auth()->user()->can('delete', $item)) {
                    $actions .= ' <button type="button" class="btn btn-sm btn-danger delete-btn" data-id="' . $item->id . '" data-name="' . ($item->employee->name ?? 'N/A') . '" title="Delete"><i class="fas fa-trash"></i></button>';
                }
                
                return $actions;
            })
            ->addColumn('created_at_formatted', function ($item) {
                return $item->created_at ? $item->created_at->format('d/m/Y H:i') : 'N/A';
            })
            ->rawColumns(['bpjs_type_badge', 'total_contribution_formatted', 'status_badge', 'actions'])
            ->setRowId('id');
    }

    /**
     * Optional method if you want to use html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('bpjs-report-table')
            ->addTableClass('data-table')
            ->columns($this->getColumns())
            ->minifiedAjax(route('bpjs-report.data', ['period' => $this->period, 'type' => $this->type]))
            ->dom('Bfrtip')
            ->orderBy(1, 'asc')
            ->selectStyleSingle()
            ->buttons([
                Button::make('excel')->text('<i class="fas fa-file-excel"></i> Excel'),
                Button::make('csv')->text('<i class="fas fa-file-csv"></i> CSV'),
                Button::make('pdf')->text('<i class="fas fa-file-pdf"></i> PDF'),
                Button::make('print')->text('<i class="fas fa-print"></i> Cetak'),
                Button::make('reset')->text('<i class="fas fa-undo"></i> Reset'),
                Button::make('reload')->text('<i class="fas fa-sync"></i> Muat Ulang'),
            ])
            ->parameters([
                'scrollX' => true,
                'responsive' => true,
                'autoWidth' => false,
                'pageLength' => 25,
                'lengthMenu' => [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                'language' => [
                    'search' => 'Cari:',
                    'lengthMenu' => 'Tampilkan _MENU_ data per halaman',
                    'zeroRecords' => 'Tidak ada data yang ditemukan',
                    'info' => 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                    'infoEmpty' => 'Menampilkan 0 sampai 0 dari 0 data',
                    'infoFiltered' => '(difilter dari _MAX_ total data)',
                    'paginate' => [
                        'first' => 'Pertama',
                        'last' => 'Terakhir',
                        'next' => 'Selanjutnya',
                        'previous' => 'Sebelumnya'
                    ],
                ],
            ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('employee_name')->title('Nama Karyawan')->width(150),
            Column::make('employee_id')->title('ID Karyawan')->width(120),
            Column::make('department')->title('Departemen')->width(120),
            Column::make('bpjs_type_badge')->title('Jenis BPJS')->width(120),
            Column::make('base_salary_formatted')->title('Gaji Pokok')->width(120),
            Column::make('employee_contribution_formatted')->title('Kontribusi Karyawan')->width(150),
            Column::make('company_contribution_formatted')->title('Kontribusi Perusahaan')->width(150),
            Column::make('total_contribution_formatted')->title('Total Kontribusi')->width(130),
            Column::make('status_badge')->title('Status')->width(100),
            Column::make('created_at_formatted')->title('Tanggal Dibuat')->width(120),
            Column::computed('actions')
                  ->exportable(false)
                  ->printable(false)
                  ->width(100)
                  ->addClass('text-center')
                  ->title('Aksi'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Laporan_BPJS_' . $this->period . '_' . $this->type . '_' . date('Y-m-d_H-i-s');
    }
}
