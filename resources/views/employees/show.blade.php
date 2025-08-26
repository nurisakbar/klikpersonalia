@extends('layouts.app')

@section('title', 'Detail Karyawan - Aplikasi Payroll KlikMedis')
@section('page-title', 'Detail Karyawan')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('employees.index') }}">Karyawan</a></li>
<li class="breadcrumb-item active">Detail</li>
@endsection

@push('js')
<script>
$(document).ready(function() {
    console.log('Employee show page loaded');
    console.log('Employee ID: {{ $employee->id }}');
    console.log('Salary components count: {{ $employee->salaryComponents->count() }}');
    console.log('Available components: {{ $salaryComponents->count() }}');
    
    // Handle calculation type change
    $('#calculation_type').change(function() {
        var type = $(this).val();
        if (type === 'percentage') {
            $('#percentage_value').prop('disabled', false).prop('required', true);
            $('#reference_type').prop('disabled', false).prop('required', true);
            $('#amount').prop('disabled', true).prop('required', false);
        } else {
            $('#percentage_value').prop('disabled', true).prop('required', false);
            $('#reference_type').prop('disabled', true).prop('required', false);
            $('#amount').prop('disabled', false).prop('required', true);
        }
    });

    // Handle salary component selection change
    $('#salary_component_id').change(function() {
        var selectedOption = $(this).find('option:selected');
        var defaultValue = selectedOption.data('default-value');
        var type = selectedOption.data('type');
        
        if (defaultValue && $('#amount').val() === '') {
            $('#amount').val(defaultValue);
        }
        
        // Update calculation type based on component type
        if (type === 'earning') {
            $('#calculation_type').val('fixed').trigger('change');
        }
    });

    // Handle delete component button
    $(document).on('click', '.delete-component-btn', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        
        Swal.fire({
            title: 'Konfirmasi Hapus',
            text: 'Apakah Anda yakin ingin menghapus komponen gaji "' + name + '" dari karyawan ini?',
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
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Send delete request
                $.ajax({
                    url: '/employees/{{ $employee->id }}/salary-components/' + id,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: response.message || 'Komponen gaji berhasil dihapus',
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        var message = 'Terjadi kesalahan saat menghapus komponen gaji';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        
                        Swal.fire('Error!', message, 'error');
                    }
                });
            }
        });
    });

    // Handle form submission
    $('#addComponentForm').submit(function(e) {
        e.preventDefault();
        
        var form = $(this);
        var submitBtn = form.find('button[type="submit"]');
        var originalText = submitBtn.text();
        
        // Disable submit button and show loading
        submitBtn.prop('disabled', true).text('Menyimpan...');
        
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                Swal.fire({
                    title: 'Berhasil!',
                    text: response.message || 'Komponen gaji berhasil ditambahkan',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            },
            error: function(xhr) {
                var message = 'Terjadi kesalahan saat menyimpan komponen gaji';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    var errors = xhr.responseJSON.errors;
                    message = Object.values(errors).flat().join('\n');
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                
                Swal.fire('Error!', message, 'error');
            },
            complete: function() {
                // Re-enable submit button
                submitBtn.prop('disabled', false).text(originalText);
            }
        });
    });
});
</script>
@endpush

<!-- CSRF Token for AJAX -->
<meta name="csrf-token" content="{{ csrf_token() }}">

@section('content')
<div class="row">
    <div class="col-md-4">
        <!-- Profile Image -->
        <div class="card card-primary card-outline">
            <div class="card-body box-profile">
                <div class="text-center">
                    <img class="profile-user-img img-fluid img-circle" src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/img/user4-128x128.jpg" alt="User profile picture">
                </div>

                <h3 class="profile-username text-center">{{ $employee->name }}</h3>

                <p class="text-muted text-center">{{ $employee->position }}</p>

                <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item">
                        <b>ID Karyawan</b> <a class="float-right">{{ $employee->employee_id }}</a>
                    </li>
                    <li class="list-group-item">
                        <b>Departemen</b> <a class="float-right">{{ $employee->department }}</a>
                    </li>
                    <li class="list-group-item">
                        <b>Status</b> <a class="float-right">
                            @if($employee->status == 'active')
                                <span class="badge badge-success">Aktif</span>
                            @elseif($employee->status == 'inactive')
                                <span class="badge badge-warning">Tidak Aktif</span>
                            @else
                                <span class="badge badge-danger">Berhenti</span>
                            @endif
                        </a>
                    </li>
                </ul>

                <a href="{{ route('employees.edit', $employee->id) }}" class="btn btn-primary btn-block">
                    <i class="fas fa-edit"></i> Edit Karyawan
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header p-2">
                <ul class="nav nav-pills">
                    <li class="nav-item"><a class="nav-link active" href="#info" data-toggle="tab">Informasi</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contact" data-toggle="tab">Kontak</a></li>
                    <li class="nav-item"><a class="nav-link" href="#bank" data-toggle="tab">Bank</a></li>
                    <li class="nav-item"><a class="nav-link" href="#salary-components" data-toggle="tab" style="background-color: #28a745; color: white; font-weight: bold;">
                        <i class="fas fa-money-bill"></i> Komponen Gaji 
                        <span class="badge badge-light">{{ $employee->salaryComponents->count() }}</span>
                    </a></li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <div class="active tab-pane" id="info">
                        <div class="row">
                            <div class="col-md-6">
                                <strong><i class="fas fa-user mr-1"></i> Nama Lengkap</strong>
                                <p class="text-muted">{{ $employee->name }}</p>
                            </div>
                            <div class="col-md-6">
                                <strong><i class="fas fa-envelope mr-1"></i> Email</strong>
                                <p class="text-muted">{{ $employee->email }}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <strong><i class="fas fa-briefcase mr-1"></i> Jabatan</strong>
                                <p class="text-muted">{{ $employee->position }}</p>
                            </div>
                            <div class="col-md-6">
                                <strong><i class="fas fa-building mr-1"></i> Departemen</strong>
                                <p class="text-muted">{{ $employee->department }}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <strong><i class="fas fa-calendar mr-1"></i> Tanggal Bergabung</strong>
                                <p class="text-muted">{{ date('d/m/Y', strtotime($employee->join_date)) }}</p>
                            </div>
                            <div class="col-md-6">
                                <strong><i class="fas fa-money-bill mr-1"></i> Gaji Pokok</strong>
                                <p class="text-muted">Rp {{ number_format($employee->basic_salary, 0, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane" id="contact">
                        <div class="row">
                            <div class="col-md-6">
                                <strong><i class="fas fa-phone mr-1"></i> Nomor Telepon</strong>
                                <p class="text-muted">{{ $employee->phone }}</p>
                            </div>
                            <div class="col-md-6">
                                <strong><i class="fas fa-map-marker-alt mr-1"></i> Alamat</strong>
                                <p class="text-muted">{{ $employee->address ?? 'Belum diisi' }}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <strong><i class="fas fa-phone-alt mr-1"></i> Kontak Darurat</strong>
                                <p class="text-muted">{{ $employee->emergency_contact ?? 'Belum diisi' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane" id="bank">
                        <div class="row">
                            <div class="col-md-6">
                                <strong><i class="fas fa-university mr-1"></i> Nama Bank</strong>
                                <p class="text-muted">{{ $employee->bank_name ?? 'Belum diisi' }}</p>
                            </div>
                            <div class="col-md-6">
                                <strong><i class="fas fa-credit-card mr-1"></i> Nomor Rekening</strong>
                                <p class="text-muted">{{ $employee->bank_account ?? 'Belum diisi' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane" id="salary-components">
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addComponentModal">
                                    <i class="fas fa-plus"></i> Tambah Komponen Gaji
                                </button>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <h5><i class="fas fa-info-circle"></i> Tab Komponen Gaji</h5>
                                    <p>Tab ini berfungsi untuk mengelola komponen gaji karyawan.</p>
                                    <p><strong>Debug Info:</strong></p>
                                    <ul>
                                        <li>Employee ID: {{ $employee->id }}</li>
                                        <li>Salary Components Count: {{ $employee->salaryComponents->count() }}</li>
                                        <li>Available Components: {{ $salaryComponents->count() }}</li>
                                    </ul>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped" id="salary-components-table">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Komponen</th>
                                                <th>Tipe</th>
                                                <th>Nilai</th>
                                                <th>Perhitungan</th>
                                                <th>Status</th>
                                                <th>Tanggal Efektif</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($employee->salaryComponents as $index => $component)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>
                                                        <strong>{{ $component->salaryComponent->name }}</strong>
                                                        @if($component->notes)
                                                            <br><small class="text-muted">{{ $component->notes }}</small>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($component->salaryComponent->type == 'earning')
                                                            <span class="badge badge-success">Penambah</span>
                                                        @else
                                                            <span class="badge badge-warning">Pengurang</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($component->calculation_type == 'fixed')
                                                            Rp {{ number_format($component->amount, 0, ',', '.') }}
                                                        @else
                                                            {{ $component->percentage_value }}% dari {{ $component->reference_type_text }}
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($component->calculation_type == 'fixed')
                                                            <span class="badge badge-info">Tetap</span>
                                                        @else
                                                            <span class="badge badge-warning">Persentase</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($component->is_active)
                                                            <span class="badge badge-success">Aktif</span>
                                                        @else
                                                            <span class="badge badge-danger">Tidak Aktif</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($component->effective_date)
                                                            {{ date('d/m/Y', strtotime($component->effective_date)) }}
                                                        @else
                                                            {{ $component->effective_date ?? '-' }}
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="btn-group" role="group">
                                                            <a href="{{ route('employee-salary-components.show', ['employee' => $employee->id, 'employeeSalaryComponent' => $component->id]) }}" class="btn btn-sm btn-info">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            <a href="{{ route('employee-salary-components.edit', ['employee' => $employee->id, 'employeeSalaryComponent' => $component->id]) }}" class="btn btn-sm btn-warning">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <button type="button" class="btn btn-sm btn-danger delete-component-btn" data-id="{{ $component->id }}" data-name="{{ $component->salaryComponent->name }}">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="text-center text-muted">
                                                        <i class="fas fa-info-circle"></i> Belum ada komponen gaji yang diassign
                                                        <br><small>Klik tombol "Tambah Komponen Gaji" di atas untuk menambahkan komponen pertama.</small>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Komponen Gaji -->
<div class="modal fade" id="addComponentModal" tabindex="-1" role="dialog" aria-labelledby="addComponentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="card">
            <div class="card-header">
                <h5 class="modal-title" id="addComponentModalLabel">Tambah Komponen Gaji</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('employee-salary-components.store', $employee->id) }}" method="POST" id="addComponentForm">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="salary_component_id">Komponen Gaji <span class="text-danger">*</span></label>
                                <select name="salary_component_id" id="salary_component_id" class="form-control" required>
                                    <option value="">Pilih Komponen</option>
                                    @foreach($salaryComponents ?? [] as $component)
                                        <option value="{{ $component->id }}" data-type="{{ $component->type }}" data-default-value="{{ $component->default_value }}">
                                            {{ $component->name }} ({{ $component->type_text }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="calculation_type">Tipe Perhitungan <span class="text-danger">*</span></label>
                                <select name="calculation_type" id="calculation_type" class="form-control" required>
                                    <option value="fixed">Nilai Tetap</option>
                                    <option value="percentage">Persentase</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="amount">Nilai <span class="text-danger">*</span></label>
                                <input type="number" name="amount" id="amount" class="form-control" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="percentage_value">Nilai Persentase (%)</label>
                                <input type="number" name="percentage_value" id="percentage_value" class="form-control" step="0.01" min="0" max="100" disabled>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="reference_type">Tipe Referensi</label>
                                <select name="reference_type" id="reference_type" class="form-control" disabled>
                                    <option value="">Pilih Referensi</option>
                                    <option value="basic_salary">Gaji Pokok</option>
                                    <option value="gross_salary">Gaji Kotor</option>
                                    <option value="net_salary">Gaji Bersih</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="effective_date">Tanggal Efektif</label>
                                <input type="date" name="effective_date" id="effective_date" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="notes">Catatan</label>
                        <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="Catatan tambahan (opsional)"></textarea>
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" checked>
                            <label class="custom-control-label" for="is_active">Aktif</label>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection 