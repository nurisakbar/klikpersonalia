<?php

namespace App\DataTables;

use App\Models\Bpjs;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;
use Illuminate\Support\Facades\Auth;

class BpjsDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', function ($bpjs) {
                return '
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-info view-btn" data-id="' . $bpjs->id . '">
                            <i class="fas fa-eye"></i>
                        </button>
                        <a href="' . route('bpjs.edit', $bpjs->id) . '" class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button type="button" class="btn btn-sm btn-danger delete-btn" data-id="' . $bpjs->id . '" data-name="' . $bpjs->employee->name . '">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                ';
            })
            ->addColumn('employee_name', function ($bpjs) {
                return $bpjs->employee->name;
            })
            ->addColumn('employee_id', function ($bpjs) {
                return $bpjs->employee->employee_id;
            })
            ->addColumn('bpjs_type_badge', function ($bpjs) {
                if ($bpjs->bpjs_type === 'kesehatan') {
                    return '<span class="badge badge-info"><i class="fas fa-heartbeat"></i> Kesehatan</span>';
                } else {
                    return '<span class="badge badge-success"><i class="fas fa-briefcase"></i> Ketenagakerjaan</span>';
                }
            })
            ->addColumn('period_formatted', function ($bpjs) {
                return \Carbon\Carbon::parse($bpjs->bpjs_period)->format('F Y');
            })
            ->addColumn('base_salary_formatted', function ($bpjs) {
                return 'Rp ' . number_format($bpjs->base_salary, 0, ',', '.');
            })
            ->addColumn('employee_contribution_formatted', function ($bpjs) {
                return 'Rp ' . number_format($bpjs->employee_contribution, 0, ',', '.');
            })
            ->addColumn('company_contribution_formatted', function ($bpjs) {
                return 'Rp ' . number_format($bpjs->company_contribution, 0, ',', '.');
            })
            ->addColumn('total_contribution_formatted', function ($bpjs) {
                return 'Rp ' . number_format($bpjs->total_contribution, 0, ',', '.');
            })
            ->addColumn('status_badge', function ($bpjs) {
                $statusClass = [
                    'pending' => 'badge badge-warning',
                    'calculated' => 'badge badge-info',
                    'paid' => 'badge badge-success',
                    'verified' => 'badge badge-primary'
                ];
                
                $statusText = [
                    'pending' => 'Menunggu',
                    'calculated' => 'Dihitung',
                    'paid' => 'Dibayar',
                    'verified' => 'Diverifikasi'
                ];
                
                return '<span class="' . $statusClass[$bpjs->status] . '">' . $statusText[$bpjs->status] . '</span>';
            })
            ->rawColumns(['action', 'bpjs_type_badge', 'status_badge'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Bpjs $model): QueryBuilder
    {
        $companyId = Auth::user()->company_id;
        
        return $model->with(['employee'])
            ->forCompany($companyId)
            ->select([
                'id',
                'employee_id',
                'bpjs_type',
                'bpjs_period',
                'base_salary',
                'employee_contribution',
                'company_contribution',
                'total_contribution',
                'status',
                'created_at'
            ])
            ->orderBy('bpjs_period', 'desc')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('bpjs-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->dom('Bfrtip')
                    ->orderBy(1)
                    ->selectStyleSingle()
                    ->buttons([
                        Button::make('add')
                            ->text('<i class="fas fa-plus"></i> Tambah')
                            ->className('btn btn-primary btn-sm mr-2')
                            ->action('window.location.href = "' . route('bpjs.create') . '"'),
                        Button::make('excel')
                            ->text('<i class="fas fa-file-excel"></i> Excel')
                            ->className('btn btn-success btn-sm'),
                        Button::make('pdf')
                            ->text('<i class="fas fa-file-pdf"></i> PDF')
                            ->className('btn btn-danger btn-sm'),
                        Button::make('print')
                            ->text('<i class="fas fa-print"></i> Print')
                            ->className('btn btn-info btn-sm')
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
            Column::make('employee_name')->title('Nama Karyawan')->width(200),
            Column::make('employee_id')->title('ID Karyawan')->width(120),
            Column::make('bpjs_type_badge')->title('Jenis BPJS')->width(150),
            Column::make('period_formatted')->title('Periode')->width(120),
            Column::make('base_salary_formatted')->title('Gaji Pokok')->width(130),
            Column::make('employee_contribution_formatted')->title('Kontribusi Karyawan')->width(150),
            Column::make('company_contribution_formatted')->title('Kontribusi Perusahaan')->width(150),
            Column::make('total_contribution_formatted')->title('Total Kontribusi')->width(130),
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
        return 'BPJS_' . date('YmdHis');
    }
}
