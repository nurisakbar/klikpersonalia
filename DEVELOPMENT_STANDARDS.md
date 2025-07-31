# 📋 STANDAR PENGEMBANGAN APLIKASI PAYROLL KLIKMEDIS

## 🎯 **OVERVIEW**
Dokumen ini berisi standar pengembangan yang wajib diikuti untuk memastikan konsistensi, kualitas, dan maintainability kode dalam aplikasi payroll.

---

## 🏗️ **ARCHITECTURE STANDARDS**

### **Technology Stack**
```
Backend: Laravel 12 (PHP 8.2+)
Frontend: AdminLTE 3 + Bootstrap 5
Database: MySQL/PostgreSQL
DataTables: Yajra Laravel DataTables
Notifications: SweetAlert2
Authentication: Laravel Breeze
```

### **Project Structure**
```
app/
├── DataTables/           # DataTable classes
├── Http/Controllers/     # Controllers
├── Models/              # Eloquent models
├── Services/            # Business logic services
└── Traits/              # Reusable traits

resources/views/
├── layouts/             # Layout templates
├── components/          # Reusable components
└── [module]/           # Module-specific views
    ├── index.blade.php  # DataTables listing
    ├── create.blade.php # Create form
    ├── edit.blade.php   # Edit form
    └── show.blade.php   # Detail view
```

---

## 📊 **DATATABLES STANDARDS (WAJIB)**

### **1. DataTable Class Structure**
```php
<?php

namespace App\DataTables;

use App\Models\YourModel;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class YourModelDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', function ($item) {
                return '
                    <div class="btn-group" role="group">
                        <a href="' . route('your-model.show', $item->id) . '" class="btn btn-sm btn-info" title="Detail">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="' . route('your-model.edit', $item->id) . '" class="btn btn-sm btn-warning" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button type="button" class="btn btn-sm btn-danger delete-btn" data-id="' . $item->id . '" data-name="' . $item->name . '" title="Hapus">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                ';
            })
            ->addColumn('status_badge', function ($item) {
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
                
                return '<span class="' . $statusClass[$item->status] . '">' . $statusText[$item->status] . '</span>';
            })
            ->rawColumns(['action', 'status_badge'])
            ->setRowId('id');
    }

    public function query(YourModel $model): QueryBuilder
    {
        return $model->select([
            'id',
            'name',
            'email',
            'status',
            // ... other columns
        ]);
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('your-model-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->dom('Bfrtip')
                    ->orderBy(1)
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

    public function getColumns(): array
    {
        return [
            Column::make('id')->title('ID')->width(50),
            Column::make('name')->title('Nama')->width(200),
            Column::make('email')->title('Email')->width(200),
            Column::make('status_badge')->title('Status')->width(100),
            Column::computed('action')
                  ->exportable(false)
                  ->printable(false)
                  ->width(150)
                  ->addClass('text-center')
                  ->title('Aksi'),
        ];
    }

    protected function filename(): string
    {
        return 'YourModel_' . date('YmdHis');
    }
}
```

### **2. Controller Implementation**
```php
<?php

namespace App\Http\Controllers;

use App\DataTables\YourModelDataTable;
use App\Models\YourModel;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class YourModelController extends Controller
{
    public function index(YourModelDataTable $dataTable)
    {
        return $dataTable->render('your-model.index');
    }

    public function getData()
    {
        $items = YourModel::select([
            'id',
            'name',
            'email',
            'status'
        ]);

        return DataTables::of($items)
            ->addColumn('action', function ($item) {
                return '
                    <div class="btn-group" role="group">
                        <a href="' . route('your-model.show', $item->id) . '" class="btn btn-sm btn-info" title="Detail">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="' . route('your-model.edit', $item->id) . '" class="btn btn-sm btn-warning" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button type="button" class="btn btn-sm btn-danger delete-btn" data-id="' . $item->id . '" data-name="' . $item->name . '" title="Hapus">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                ';
            })
            ->addColumn('status_badge', function ($item) {
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
                
                return '<span class="' . $statusClass[$item->status] . '">' . $statusText[$item->status] . '</span>';
            })
            ->rawColumns(['action', 'status_badge'])
            ->make(true);
    }

    public function destroy(string $id)
    {
        try {
            $item = YourModel::findOrFail($id);
            $item->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dihapus!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ], 500);
        }
    }
}
```

### **3. Routes**
```php
// In routes/web.php
Route::resource('your-model', YourModelController::class);
Route::get('/your-model-data', [YourModelController::class, 'getData'])->name('your-model.data');
```

---

## 🎨 **SWEETALERT STANDARDS (WAJIB)**

### **1. View Template Structure**
```php
@extends('layouts.app')

@section('title', 'Daftar [Module] - Aplikasi Payroll KlikMedis')
@section('page-title', 'Daftar [Module]')

@section('breadcrumb')
<li class="breadcrumb-item active">[Module]</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Data [Module]</h3>
                <div class="card-tools">
                    <a href="{{ route('your-model.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Tambah [Module]
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="your-model-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CSRF Token for AJAX -->
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@push('css')
<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
@endpush

@push('js')
<!-- DataTables & Plugins -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(function () {
    // Setup CSRF token for AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Initialize DataTable with server-side processing
    var table = $('#your-model-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("your-model.data") }}',
            type: 'GET'
        },
        columns: [
            {data: 'id', name: 'id', width: '50px'},
            {data: 'name', name: 'name', width: '200px'},
            {data: 'email', name: 'email', width: '200px'},
            {data: 'status_badge', name: 'status', width: '100px'},
            {data: 'action', name: 'action', orderable: false, searchable: false, width: '150px'}
        ],
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel"></i> Excel',
                className: 'btn btn-success btn-sm',
                exportOptions: {
                    columns: [0, 1, 2, 3]
                }
            },
            {
                extend: 'pdf',
                text: '<i class="fas fa-file-pdf"></i> PDF',
                className: 'btn btn-danger btn-sm',
                exportOptions: {
                    columns: [0, 1, 2, 3]
                }
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print"></i> Print',
                className: 'btn btn-info btn-sm',
                exportOptions: {
                    columns: [0, 1, 2, 3]
                }
            }
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
        },
        responsive: true,
        order: [[1, 'asc']]
    });

    // Handle delete button click
    $(document).on('click', '.delete-btn', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        
        Swal.fire({
            title: 'Konfirmasi Hapus',
            text: 'Apakah Anda yakin ingin menghapus "' + name + '" ?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'Menghapus...',
                    text: 'Mohon tunggu sebentar',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Send delete request
                $.ajax({
                    url: '/your-model/' + id,
                    type: 'DELETE',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire(
                                'Berhasil!',
                                response.message,
                                'success'
                            ).then(() => {
                                // Reload DataTable
                                table.ajax.reload();
                            });
                        } else {
                            Swal.fire(
                                'Gagal!',
                                response.message,
                                'error'
                            );
                        }
                    },
                    error: function(xhr) {
                        var message = 'Terjadi kesalahan saat menghapus data';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        
                        Swal.fire(
                            'Error!',
                            message,
                            'error'
                        );
                    }
                });
            }
        });
    });

    // Success message from session
    @if(session('success'))
        Swal.fire({
            title: 'Berhasil!',
            text: '{{ session("success") }}',
            icon: 'success',
            timer: 3000,
            showConfirmButton: false
        });
    @endif

    // Error message from session
    @if(session('error'))
        Swal.fire({
            title: 'Error!',
            text: '{{ session("error") }}',
            icon: 'error',
            timer: 3000,
            showConfirmButton: false
        });
    @endif
});
</script>
@endpush
```

---

## 📝 **CODING STANDARDS**

### **1. Naming Conventions**
```php
// Controllers
class EmployeeController extends Controller
class PayrollController extends Controller

// Models
class Employee extends Model
class Payroll extends Model

// DataTables
class EmployeeDataTable extends DataTable
class PayrollDataTable extends DataTable

// Routes
Route::resource('employees', EmployeeController::class);
Route::get('/employees-data', [EmployeeController::class, 'getData'])->name('employees.data');
```

### **2. Database Standards**
```php
// Migration naming
create_employees_table
create_payrolls_table
create_attendances_table

// Column naming
id, name, email, phone, status, created_at, updated_at
employee_id, basic_salary, join_date, department, position

// Status enums
enum('active', 'inactive', 'terminated')
enum('draft', 'approved', 'paid')
```

### **3. Validation Rules**
```php
// Standard validation
'name' => 'required|string|max:255',
'email' => 'required|email|unique:table,email',
'phone' => 'required|string|max:20',
'status' => 'required|in:active,inactive,terminated',
'amount' => 'required|numeric|min:0',
'date' => 'required|date',
```

---

## 🔧 **IMPLEMENTATION CHECKLIST**

### **Untuk Setiap Modul Baru:**

#### **1. Database Layer**
- [ ] Buat migration dengan struktur yang benar
- [ ] Buat model dengan fillable, casts, dan relationships
- [ ] Buat seeder dengan sample data
- [ ] Update DatabaseSeeder

#### **2. Controller Layer**
- [ ] Buat controller dengan resource methods
- [ ] Implementasi DataTables method (getData)
- [ ] Implementasi destroy method dengan JSON response
- [ ] Tambah validation rules
- [ ] Implementasi proper error handling

#### **3. DataTable Layer**
- [ ] Buat DataTable class
- [ ] Implementasi query method
- [ ] Implementasi dataTable method dengan action buttons
- [ ] Implementasi html method dengan buttons
- [ ] Implementasi getColumns method

#### **4. View Layer**
- [ ] Buat index.blade.php dengan DataTables
- [ ] Implementasi SweetAlert untuk delete
- [ ] Buat create.blade.php dengan proper validation
- [ ] Buat edit.blade.php dengan pre-filled data
- [ ] Buat show.blade.php dengan detail view

#### **5. Routes**
- [ ] Tambah resource route
- [ ] Tambah data route untuk DataTables

#### **6. Testing**
- [ ] Test CRUD operations
- [ ] Test DataTables functionality
- [ ] Test SweetAlert delete confirmation
- [ ] Test export functionality
- [ ] Test responsive design

---

## 🚨 **MANDATORY REQUIREMENTS**

### **WAJIB DIIMPLEMENTASI:**
1. ✅ **Yajra DataTables** - Semua listing pages
2. ✅ **SweetAlert2** - Semua delete operations
3. ✅ **Server-side processing** - Untuk performa optimal
4. ✅ **Export functionality** - Excel, PDF, Print
5. ✅ **Responsive design** - Mobile-friendly
6. ✅ **Indonesian language** - Interface dalam bahasa Indonesia
7. ✅ **Proper error handling** - Try-catch dengan JSON response
8. ✅ **CSRF protection** - Untuk semua AJAX requests
9. ✅ **Validation** - Client dan server-side validation
10. ✅ **Consistent styling** - Menggunakan AdminLTE components

### **DILARANG:**
- ❌ Client-side DataTables (tanpa server-side processing)
- ❌ Basic confirm() untuk delete (harus SweetAlert)
- ❌ Hardcoded data dalam controller
- ❌ Inconsistent naming conventions
- ❌ Missing error handling
- ❌ No validation rules

---

## 📚 **RESOURCES & REFERENCES**

### **Documentation:**
- [Yajra DataTables Documentation](https://yajrabox.com/docs/laravel-datatables)
- [SweetAlert2 Documentation](https://sweetalert2.github.io/)
- [AdminLTE 3 Documentation](https://adminlte.io/docs/3.0/)
- [Laravel Documentation](https://laravel.com/docs)

### **CDN Links:**
```html
<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
```

---

## 🔄 **CODE REVIEW CHECKLIST**

### **Sebelum Submit Pull Request:**
- [ ] Mengikuti naming conventions
- [ ] Implementasi DataTables dengan server-side processing
- [ ] Menggunakan SweetAlert untuk delete confirmation
- [ ] Proper error handling dan validation
- [ ] Responsive design
- [ ] Export functionality working
- [ ] No console errors
- [ ] Mobile-friendly
- [ ] Performance optimized
- [ ] Documentation updated

---

**Last Updated:** July 31, 2025  
**Version:** 1.0  
**Status:** Active Standards 