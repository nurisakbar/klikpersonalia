<?php

namespace App\DataTables;

use App\Models\BankAccount;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class BankAccountDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', function ($bankAccount) {
                return '
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-info view-btn" data-id="' . $bankAccount->id . '">
                            <i class="fas fa-eye"></i>
                        </button>
                        <a href="' . route('bank-accounts.edit', $bankAccount->id) . '" class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button type="button" class="btn btn-sm btn-danger delete-btn" data-id="' . $bankAccount->id . '" data-name="' . $bankAccount->bank_name . ' - ' . $bankAccount->account_holder_name . '">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                ';
            })
            ->addColumn('status_badge', function ($bankAccount) {
                if ($bankAccount->is_active) {
                    return '<span class="badge badge-success">Aktif</span>';
                } else {
                    return '<span class="badge badge-warning">Tidak Aktif</span>';
                }
            })
            ->addColumn('primary_badge', function ($bankAccount) {
                if ($bankAccount->is_primary) {
                    return '<span class="badge badge-primary">Utama</span>';
                } else {
                    return '<span class="badge badge-secondary">-</span>';
                }
            })
            ->addColumn('account_number_masked', function ($bankAccount) {
                return $bankAccount->formatted_account_number;
            })
            ->addColumn('account_type_label', function ($bankAccount) {
                $types = [
                    'savings' => 'Tabungan',
                    'current' => 'Giro',
                    'salary' => 'Gaji'
                ];
                return $types[$bankAccount->account_type] ?? $bankAccount->account_type;
            })
            ->rawColumns(['action', 'status_badge', 'primary_badge', 'account_number_masked', 'account_type_label'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(BankAccount $model): QueryBuilder
    {
        $user = auth()->user();
        
        return $model->with(['employee'])
            ->where('bank_accounts.company_id', $user->company_id)
            ->select([
                'bank_accounts.id',
                'bank_accounts.employee_id',
                'bank_accounts.company_id',
                'bank_accounts.bank_name',
                'bank_accounts.account_number',
                'bank_accounts.account_holder_name',
                'bank_accounts.branch_code',
                'bank_accounts.swift_code',
                'bank_accounts.account_type',
                'bank_accounts.is_active',
                'bank_accounts.is_primary',
                'bank_accounts.notes',
                'bank_accounts.created_at'
            ]);
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('bank-accounts-table')
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
            Column::make('employee.name')->title('Karyawan')->width(150),
            Column::make('bank_name')->title('Nama Bank')->width(120),
            Column::make('account_holder_name')->title('Pemilik Rekening')->width(150),
            Column::make('account_number_masked')->title('Nomor Rekening')->width(130),
            Column::make('account_type_label')->title('Jenis Rekening')->width(100),
            Column::make('branch_code')->title('Kode Cabang')->width(100),
            Column::make('status_badge')->title('Status')->width(80),
            Column::make('primary_badge')->title('Utama')->width(80),
            Column::computed('action')
                  ->exportable(false)
                  ->printable(false)
                  ->width(120)
                  ->addClass('text-center')
                  ->title('Aksi'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'BankAccounts_' . date('YmdHis');
    }
}
