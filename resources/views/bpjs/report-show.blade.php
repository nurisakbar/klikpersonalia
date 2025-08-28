@extends('layouts.app')

@section('title', 'Rincian BPJS Record')
@section('page-title', 'Rincian BPJS Record')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('bpjs-report.index') }}">Laporan BPJS</a></li>
<li class="breadcrumb-item active">Rincian BPJS Record</li>
@endsection

@section('content')
            <!-- Loading State -->
            <div id="loading-state" class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <p class="mt-3">Memuat data BPJS...</p>
            </div>

            <!-- Error State -->
            <div id="error-state" class="text-center py-5" style="display: none;">
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span id="error-message">Terjadi kesalahan saat memuat data</span>
                </div>
                <a href="{{ route('bpjs-report.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali ke Laporan BPJS
                </a>
            </div>

            <!-- Content State -->
            <div id="content-state" style="display: none;">
                <div class="row">
                    <div class="col-md-8">
                        <!-- BPJS Rincians Card -->
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-file-alt"></i> Informasi BPJS Record
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <table class="table table-borderless">
                                            <tr>
                                                <td><strong>Jenis BPJS:</strong></td>
                                                <td id="bpjs-type"></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Periode:</strong></td>
                                                <td id="bpjs-period"></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Status:</strong></td>
                                                <td id="bpjs-status"></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Tanggal Dibuat:</strong></td>
                                                <td id="bpjs-created"></td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <table class="table table-borderless">
                                            <tr>
                                                <td><strong>Gaji Pokok:</strong></td>
                                                <td id="base-salary"></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Rate Karyawan:</strong></td>
                                                <td id="employee-rate"></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Rate Perusahaan:</strong></td>
                                                <td id="company-rate"></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Tanggal Update:</strong></td>
                                                <td id="bpjs-updated"></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>

                                <div id="notes-section" style="display: none;">
                                    <div class="row mt-3">
                                        <div class="col-12">
                                            <h6><i class="fas fa-sticky-note"></i> Catatan:</h6>
                                            <p class="text-muted" id="bpjs-notes"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Contribution Rincians Card -->
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-calculator"></i> Rincian Kontribusi
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="info-box bg-info">
                                            <span class="info-box-icon"><i class="fas fa-user"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Kontribusi Karyawan</span>
                                                <span class="info-box-number" id="employee-contribution"></span>
                                                <div class="progress">
                                                    <div class="progress-bar" style="width: 100%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="info-box bg-success">
                                            <span class="info-box-icon"><i class="fas fa-building"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Kontribusi Perusahaan</span>
                                                <span class="info-box-number" id="company-contribution"></span>
                                                <div class="progress">
                                                    <div class="progress-bar" style="width: 100%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="info-box bg-primary">
                                            <span class="info-box-icon"><i class="fas fa-calculator"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Total Kontribusi</span>
                                                <span class="info-box-number" id="total-contribution"></span>
                                                <div class="progress">
                                                    <div class="progress-bar" style="width: 100%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- BPJS Ketenagakerjaan Breakdown -->
                                <div id="ketenagakerjaan-breakdown" style="display: none;">
                                    <div class="row mt-3">
                                        <div class="col-12">
                                            <h6><i class="fas fa-list"></i> Breakdown BPJS Ketenagakerjaan:</h6>
                                            <div class="table-responsive">
                                                <table class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>Komponen</th>
                                                            <th>Karyawan</th>
                                                            <th>Perusahaan</th>
                                                            <th>Total</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="breakdown-table">
                                                        <!-- Will be populated by JavaScript -->
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <!-- Employee Information Card -->
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-user"></i> Informasi Karyawan
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="text-center mb-3">
                                    <img src="https://via.placeholder.com/100x100?text=?" 
                                         class="img-circle" alt="Employee Photo" id="employee-photo">
                                </div>
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Nama:</strong></td>
                                        <td id="employee-name"></td>
                                    </tr>
                                    <tr>
                                        <td><strong>ID Karyawan:</strong></td>
                                        <td id="employee-id"></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Posisi:</strong></td>
                                        <td id="employee-position"></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Departemen:</strong></td>
                                        <td id="employee-department"></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Email:</strong></td>
                                        <td id="employee-email"></td>
                                    </tr>
                                </table>

                                <hr>

                                <h6>Status BPJS:</h6>
                                <div class="row">
                                    <div class="col-6">
                                        <small>BPJS Kesehatan:</small><br>
                                        <span class="badge badge-secondary" id="bpjs-kesehatan-status">Unknown</span>
                                    </div>
                                    <div class="col-6">
                                        <small>BPJS Ketenagakerjaan:</small><br>
                                        <span class="badge badge-secondary" id="bpjs-ketenagakerjaan-status">Unknown</span>
                                    </div>
                                </div>

                                <div id="bpjs-numbers" style="display: none;">
                                    <div class="mt-2">
                                        <small>Nomor BPJS Kesehatan:</small><br>
                                        <strong id="bpjs-kesehatan-number"></strong>
                                    </div>
                                    <div class="mt-2">
                                        <small>Nomor BPJS Ketenagakerjaan:</small><br>
                                        <strong id="bpjs-ketenagakerjaan-number"></strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Information Card -->
                        <div class="card" id="payment-card" style="display: none;">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-credit-card"></i> Informasi Pembayaran
                                </h3>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Tanggal Pembayaran:</strong></td>
                                        <td id="payment-date"></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Status:</strong></td>
                                        <td id="payment-status"></td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <!-- Related Payroll Card -->
                        <div class="card" id="payroll-card" style="display: none;">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-file-invoice-dollar"></i> Payroll Terkait
                                </h3>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Periode Payroll:</strong></td>
                                        <td id="payroll-period"></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Gaji Pokok:</strong></td>
                                        <td id="payroll-basic-salary"></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Total Gaji:</strong></td>
                                        <td id="payroll-total-salary"></td>
                                    </tr>
                                </table>
                                <a href="#" class="btn btn-info btn-sm btn-block" id="payroll-link">
                                    <i class="fas fa-eye"></i> Lihat Rincian Payroll
                                </a>
                            </div>
                        </div>
                    </div>
                                 </div>
             </div>
         </div>
     </section>
 @endsection

@push('js')
<script>
$(document).ready(function() {
    // Get BPJS ID from URL
    var bpjsId = window.location.pathname.split('/').pop();
    
    // Load BPJS data
    loadBpjsData(bpjsId);
    
    // Handle edit button click
    $('#edit-btn').click(function() {
        var editUrl = '/bpjs/' + bpjsId + '/edit';
        window.location.href = editUrl;
    });
});

function loadBpjsData(bpjsId) {
    // Show loading state
    $('#loading-state').show();
    $('#error-state').hide();
    $('#content-state').hide();
    
    // Fetch BPJS data
    $.ajax({
        url: '/bpjs-report/' + bpjsId,
        type: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(response) {
            if (response.success) {
                var data = response.data;
                populateBpjsData(data);
                
                // Hide loading, show content
                $('#loading-state').hide();
                $('#content-state').show();
            } else {
                showError('Gagal memuat data BPJS');
            }
        },
        error: function(xhr) {
            var message = 'Terjadi kesalahan saat memuat data';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }
            showError(message);
        }
    });
}

function populateBpjsData(data) {
    // BPJS Information
    $('#bpjs-type').html(data.bpjs_type_text);
    $('#bpjs-period').text(data.period_formatted);
    $('#bpjs-status').html(data.status_badge);
    $('#bpjs-created').text(data.created_at_formatted);
    $('#bpjs-updated').text(data.updated_at_formatted);
    $('#base-salary').text(data.base_salary_formatted);
    $('#employee-rate').text((data.contribution_rate_employee * 100).toFixed(2) + '%');
    $('#company-rate').text((data.contribution_rate_company * 100).toFixed(2) + '%');
    
    // Contribution amounts
    $('#employee-contribution').text(data.employee_contribution_formatted);
    $('#company-contribution').text(data.company_contribution_formatted);
    $('#total-contribution').text(data.total_contribution_formatted);
    
    // Notes
    if (data.notes) {
        $('#bpjs-notes').text(data.notes);
        $('#notes-section').show();
    }
    
         // Employee Information
     if (data.employee) {
         var employee = data.employee;
         $('#employee-name').text(employee.name);
         $('#employee-id').text(employee.employee_id);
         $('#employee-position').text(employee.position);
         $('#employee-department').text(employee.department);
         $('#employee-email').text(employee.email);
         
         // Update employee photo
         var firstLetter = employee.name ? employee.name.charAt(0).toUpperCase() : '?';
         
         // Check if employee has photo
         if (employee.photo && employee.photo !== '') {
             // Use Storage::url for employee photo
             $('#employee-photo').attr('src', '/storage/' + employee.photo);
         } else {
             // Use default avatar
             $('#employee-photo').attr('src', '/images/default-avatar.svg');
         }
         
         // Update BPJS Status
         if (employee.bpjs_kesehatan_active) {
             $('#bpjs-kesehatan-status').removeClass('badge-secondary').addClass('badge-success').text('Active');
         } else {
             $('#bpjs-kesehatan-status').removeClass('badge-secondary').addClass('badge-danger').text('Inactive');
         }
         
         if (employee.bpjs_ketenagakerjaan_active) {
             $('#bpjs-ketenagakerjaan-status').removeClass('badge-secondary').addClass('badge-success').text('Active');
         } else {
             $('#bpjs-ketenagakerjaan-status').removeClass('badge-secondary').addClass('badge-danger').text('Inactive');
         }
         
         // Show BPJS numbers if available
         if (employee.bpjs_kesehatan_number || employee.bpjs_ketenagakerjaan_number) {
             if (employee.bpjs_kesehatan_number) {
                 $('#bpjs-kesehatan-number').text(employee.bpjs_kesehatan_number);
             }
             if (employee.bpjs_ketenagakerjaan_number) {
                 $('#bpjs-ketenagakerjaan-number').text(employee.bpjs_ketenagakerjaan_number);
             }
             $('#bpjs-numbers').show();
         }
     }
    
    // Payment Information
    if (data.payment_date) {
        $('#payment-date').text(data.payment_date_formatted);
        $('#payment-status').html(data.status_badge);
        $('#payment-card').show();
    }
    
    // Payroll Information
    if (data.payroll) {
        var payroll = data.payroll;
        $('#payroll-period').text(new Date(payroll.payroll_period).toLocaleDateString('id-ID', { year: 'numeric', month: 'long' }));
        $('#payroll-basic-salary').text('Rp ' + new Intl.NumberFormat('id-ID').format(payroll.basic_salary));
        $('#payroll-total-salary').text('Rp ' + new Intl.NumberFormat('id-ID').format(payroll.net_salary));
        $('#payroll-link').attr('href', '/payrolls/' + payroll.id);
        $('#payroll-card').show();
    }
    
    // BPJS Ketenagakerjaan Breakdown
    if (data.bpjs_type === 'ketenagakerjaan') {
        generateKetenagakerjaanBreakdown(data);
        $('#ketenagakerjaan-breakdown').show();
    }
}

function generateKetenagakerjaanBreakdown(data) {
    var baseSalary = data.base_salary;
    var breakdown = [
        {
            name: 'JHT (Jaminan Hari Tua)',
            employee: baseSalary * 0.02,
            company: baseSalary * 0.037
        },
        {
            name: 'JKK (Jaminan Kecelakaan Kerja)',
            employee: 0,
            company: baseSalary * 0.0024
        },
        {
            name: 'JKM (Jaminan Kematian)',
            employee: 0,
            company: baseSalary * 0.003
        },
        {
            name: 'JP (Jaminan Pensiun)',
            employee: baseSalary * 0.01,
            company: baseSalary * 0.02
        }
    ];
    
    var html = '';
    breakdown.forEach(function(item) {
        var total = item.employee + item.company;
        html += '<tr>';
        html += '<td><strong>' + item.name + '</strong></td>';
        html += '<td>' + (item.employee > 0 ? 'Rp ' + new Intl.NumberFormat('id-ID').format(item.employee) : '-') + '</td>';
        html += '<td>Rp ' + new Intl.NumberFormat('id-ID').format(item.company) + '</td>';
        html += '<td>Rp ' + new Intl.NumberFormat('id-ID').format(total) + '</td>';
        html += '</tr>';
    });
    
    // Add total row
    html += '<tr class="table-active">';
    html += '<td><strong>TOTAL</strong></td>';
    html += '<td><strong>' + data.employee_contribution_formatted + '</strong></td>';
    html += '<td><strong>' + data.company_contribution_formatted + '</strong></td>';
    html += '<td><strong>' + data.total_contribution_formatted + '</strong></td>';
    html += '</tr>';
    
    $('#breakdown-table').html(html);
}

function showError(message) {
    $('#error-message').text(message);
    $('#loading-state').hide();
    $('#error-state').show();
}
</script>
@endpush
