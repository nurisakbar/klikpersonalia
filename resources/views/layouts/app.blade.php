<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Aplikasi Payroll KlikMedis')</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">
    <!-- overlayScrollbars -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/overlayscrollbars/2.4.0/css/OverlayScrollbars.min.css">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <!-- Select2 (for AJAX employee select) -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.6.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />
    
    @stack('css')
    <style>
		/* Global DataTables layout adjustments */
		.dataTables_wrapper .dataTables_info {
			margin-top: 1.5rem !important;
		}
		.dataTables_wrapper .dataTables_paginate {
			margin-top: -2.5rem !important;
		}



		/* Header user dropdown styling */
		.navbar .nav-link.d-flex {
			color: #6c757d;
			text-decoration: none;
		}

		.navbar .nav-link.d-flex:hover {
			color: #495057;
		}

		.navbar .nav-link.d-flex img {
			border: 2px solid #e9ecef;
		}

		.navbar .nav-link.d-flex:hover img {
			border-color: #adb5bd;
		}

		/* Sub menu indentation */
		.sidebar .nav-treeview {
			margin-left: 1rem;
		}

		.sidebar .nav-treeview .nav-link {
			padding-left: 1rem;
		}

		.sidebar .nav-treeview .nav-icon {
			margin-left: 0.5rem;
		}
	</style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">

        <!-- Preloader -->
        <div class="preloader flex-column justify-content-center align-items-center">
            <img class="animation__shake" src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/img/AdminLTELogo.png" alt="AdminLTELogo" height="60" width="60">
        </div>

        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                </li>
            </ul>

            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
                <!-- Notifications Dropdown Menu -->
                <li class="nav-item dropdown">
                    <a class="nav-link" data-toggle="dropdown" href="#">
                        <i class="far fa-bell"></i>
                        <span class="badge badge-warning navbar-badge">15</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                        <span class="dropdown-item dropdown-header">15 Notifications</span>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item">
                            <i class="fas fa-envelope mr-2"></i> 4 new messages
                            <span class="float-right text-muted text-sm">3 mins</span>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item">
                            <i class="fas fa-users mr-2"></i> 8 friend requests
                            <span class="float-right text-muted text-sm">12 hours</span>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item">
                            <i class="fas fa-file mr-2"></i> 3 new reports
                            <span class="float-right text-muted text-sm">2 days</span>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item dropdown-footer">See All Notifications</a>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                        <i class="fas fa-expand-arrows-alt"></i>
                    </a>
                </li>
                <!-- User Dropdown Menu -->
                <li class="nav-item dropdown">
                    <a class="nav-link d-flex align-items-center" data-toggle="dropdown" href="#" style="padding: 0.5rem 1rem;">
                        <img src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/img/user2-160x160.jpg" class="img-circle elevation-2 mr-2" alt="User Image" style="width: 32px; height: 32px;">
                        <span class="d-none d-md-inline">{{ Auth::user()->name }}</span>
                        <i class="fas fa-chevron-down ml-1"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <span class="dropdown-item dropdown-header">{{ Auth::user()->name }}</span>
                        <div class="dropdown-divider"></div>
                        <a href="{{ route('profile.edit') }}" class="dropdown-item">
                            <i class="fas fa-user mr-2"></i> Profile
                        </a>
                        <a href="{{ route('settings.index') }}" class="dropdown-item">
                            <i class="fas fa-cog mr-2"></i> Settings
                        </a>
                        <div class="dropdown-divider"></div>
                        <form method="POST" action="{{ route('logout') }}" class="dropdown-item">
                            @csrf
                            <button type="submit" class="btn btn-link p-0 m-0" style="text-decoration: none;">
                                <i class="fas fa-sign-out-alt mr-2"></i> Logout
                            </button>
                        </form>
                    </div>
                </li>
            </ul>
        </nav>
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <!-- Brand Logo -->
            <a href="{{ route('dashboard') }}" class="brand-link">
                <img src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
                <span class="brand-text font-weight-light">Payroll KlikMedis</span>
            </a>

            <!-- Sidebar -->
            <div class="sidebar">

                <!-- Sidebar Menu -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                        <!-- Dashboard -->
                        <li class="nav-item">
                            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>

                        <!-- Employee Management -->
                        <li class="nav-item">
                            <a href="{{ route('employees.index') }}" class="nav-link {{ request()->routeIs('employees.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-users"></i>
                                <p>Karyawan</p>
                            </a>
                        </li>

                        <!-- Payroll Management -->
                        <li class="nav-item {{ request()->routeIs('payrolls.*') ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ request()->routeIs('payrolls.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-money-bill-wave"></i>
                                <p>
                                    Payroll Management
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('payrolls.index') }}" class="nav-link {{ request()->routeIs('payrolls.index') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>All Payrolls</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- Attendance Management -->
                        <li class="nav-item {{ request()->routeIs('attendance.*') ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ request()->routeIs('attendance.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-clock"></i>
                                <p>
                                    Attendance Management
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('attendance.index') }}" class="nav-link {{ request()->routeIs('attendance.index') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Attendance Records</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('attendance.check-in-out') }}" class="nav-link {{ request()->routeIs('attendance.check-in-out') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Check In/Out</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('attendance.calendar') }}" class="nav-link {{ request()->routeIs('attendance.calendar*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Attendance Calendar</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- Leave Management -->
                        <li class="nav-item {{ request()->routeIs('leaves.*') ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ request()->routeIs('leaves.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-calendar-alt"></i>
                                <p>
                                    Leave Management
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('leaves.index') }}" class="nav-link {{ request()->routeIs('leaves.index') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>My Leave Requests</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('leaves.create') }}" class="nav-link {{ request()->routeIs('leaves.create') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Submit Leave Request</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('leaves.balance') }}" class="nav-link {{ request()->routeIs('leaves.balance') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Leave Balance</p>
                                    </a>
                                </li>
                                @if(in_array(auth()->user()->role, ['admin', 'hr', 'manager']))
                                <li class="nav-item">
                                    <a href="{{ route('leaves.approval') }}" class="nav-link {{ request()->routeIs('leaves.approval') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Leave Approval</p>
                                    </a>
                                </li>
                                @endif
                            </ul>
                        </li>

                        <!-- Overtime Management -->
                        <li class="nav-item {{ request()->routeIs('overtimes.*') ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ request()->routeIs('overtimes.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-clock"></i>
                                <p>
                                    Overtime Management
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('overtimes.index') }}" class="nav-link {{ request()->routeIs('overtimes.index') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>My Overtime Requests</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('overtimes.create') }}" class="nav-link {{ request()->routeIs('overtimes.create') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Submit Overtime Request</p>
                                    </a>
                                </li>
                                @if(in_array(auth()->user()->role, ['admin', 'hr', 'manager']))
                                <li class="nav-item">
                                    <a href="{{ route('overtimes.approval') }}" class="nav-link {{ request()->routeIs('overtimes.approval') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Overtime Approval</p>
                                    </a>
                                </li>
                                @endif
                            </ul>
                        </li>

                        <!-- Tax Management -->
                        <li class="nav-item {{ request()->routeIs('taxes.*') ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ request()->routeIs('taxes.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-calculator"></i>
                                <p>
                                    Tax Management
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('taxes.index') }}" class="nav-link {{ request()->routeIs('taxes.index') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Tax Calculations</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('taxes.report') }}" class="nav-link {{ request()->routeIs('taxes.report') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Tax Reports</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- BPJS Management -->
                        <li class="nav-item {{ request()->routeIs('bpjs.*') ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ request()->routeIs('bpjs.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-heartbeat"></i>
                                <p>
                                    BPJS Management
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('bpjs.index') }}" class="nav-link {{ request()->routeIs('bpjs.index') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>BPJS Calculations</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('bpjs.report') }}" class="nav-link {{ request()->routeIs('bpjs.report') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>BPJS Reports</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- Bank Integration -->
                        <li class="nav-item {{ request()->routeIs('bank-accounts.*') || request()->routeIs('salary-transfers.*') ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ request()->routeIs('bank-accounts.*') || request()->routeIs('salary-transfers.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-university"></i>
                                <p>
                                    Bank Integration
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('bank-accounts.index') }}" class="nav-link {{ request()->routeIs('bank-accounts.*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Bank Accounts</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('salary-transfers.index') }}" class="nav-link {{ request()->routeIs('salary-transfers.*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Salary Transfers</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- Reports -->
                        <li class="nav-item">
                            <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-chart-bar"></i>
                                <p>Reports</p>
                            </a>
                        </li>

                        <!-- Export Data -->
                        <li class="nav-item {{ request()->routeIs('exports.*') ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ request()->routeIs('exports.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-download"></i>
                                <p>
                                    Export Data
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('exports.index') }}" class="nav-link {{ request()->routeIs('exports.index') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Export Dashboard</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('exports.employees') }}?format=xlsx" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Export Employees</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('exports.payrolls') }}?format=xlsx" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Export Payrolls</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- Settings -->
                        <li class="nav-item {{ request()->routeIs('settings.*') ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-cogs"></i>
                                <p>
                                    Settings
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('settings.index') }}" class="nav-link {{ request()->routeIs('settings.index') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Settings Dashboard</p>
                                    </a>
                                </li>
                                @if(in_array(auth()->user()->role, ['admin']))
                                <li class="nav-item">
                                    <a href="{{ route('settings.company') }}" class="nav-link {{ request()->routeIs('settings.company') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Company Settings</p>
                                    </a>
                                </li>
                                @endif
                                <li class="nav-item">
                                    <a href="{{ route('settings.profile') }}" class="nav-link {{ request()->routeIs('settings.profile') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>User Profile</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('settings.password') }}" class="nav-link {{ request()->routeIs('settings.password') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Change Password</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </nav>
                <!-- /.sidebar-menu -->
            </div>
            <!-- /.sidebar -->
        </aside>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">@yield('page-title', 'Dashboard')</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                                @yield('breadcrumb')
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.content-header -->

            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    @yield('content')
                </div>
            </section>
            <!-- /.content -->
        </div>
        <!-- /.content-wrapper -->
        <footer class="main-footer">
            <strong>Copyright &copy; 2024 <a href="#">KlikMedis</a>.</strong>
            All rights reserved.
            <div class="float-right d-none d-sm-inline-block">
                <b>Version</b> 1.0.0
            </div>
        </footer>

        <!-- Control Sidebar -->
        <aside class="control-sidebar control-sidebar-dark">
            <!-- Control sidebar content goes here -->
        </aside>
        <!-- /.control-sidebar -->
    </div>
    <!-- ./wrapper -->

    <!-- jQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <!-- jQuery UI 1.11.4 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/jquery-ui.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/js/bootstrap.bundle.min.js"></script>
    <!-- overlayScrollbars -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/overlayscrollbars/2.4.0/js/OverlayScrollbars.min.js"></script>
    <!-- AdminLTE App -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
    <!-- Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <!-- Global SweetAlert Helper -->
    <script>
        // Global SweetAlert Helper Functions
        window.SwalHelper = {
            // Success Alert
            success: function(title, text = '', timer = 3000) {
                return Swal.fire({
                    title: title,
                    text: text,
                    icon: 'success',
                    timer: timer,
                    showConfirmButton: false,
                    toast: false,
                    position: 'center'
                });
            },
            
            // Error Alert
            error: function(title, text = '', showConfirmButton = true) {
                return Swal.fire({
                    title: title,
                    text: text,
                    icon: 'error',
                    showConfirmButton: showConfirmButton,
                    toast: false,
                    position: 'center'
                });
            },
            
            // Warning Alert
            warning: function(title, text = '', showConfirmButton = true) {
                return Swal.fire({
                    title: title,
                    text: text,
                    icon: 'warning',
                    showConfirmButton: showConfirmButton,
                    toast: false,
                    position: 'center'
                });
            },
            
            // Info Alert
            info: function(title, text = '', showConfirmButton = true) {
                return Swal.fire({
                    title: title,
                    text: text,
                    icon: 'info',
                    showConfirmButton: showConfirmButton,
                    toast: false,
                    position: 'center'
                });
            },
            
            // Confirmation Dialog
            confirm: function(title, text = '', confirmButtonText = 'Ya, Hapus!', cancelButtonText = 'Batal') {
                return Swal.fire({
                    title: title,
                    text: text,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: confirmButtonText,
                    cancelButtonText: cancelButtonText
                });
            },
            
            // Toast Success
            toastSuccess: function(title) {
                return Swal.fire({
                    title: title,
                    icon: 'success',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
            },
            
            // Toast Error
            toastError: function(title) {
                return Swal.fire({
                    title: title,
                    icon: 'error',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
            },
            
            // Loading Alert
            loading: function(title = 'Memproses...') {
                return Swal.fire({
                    title: title,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    allowEnterKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            },
            
            // Close Loading
            close: function() {
                Swal.close();
            }
        };

        // Auto-show alerts from Laravel session
        @if(session('success'))
            SwalHelper.success('Berhasil!', '{{ session("success") }}');
        @endif

        @if(session('error'))
            SwalHelper.error('Error!', '{{ session("error") }}');
        @endif

        @if($errors->any())
            SwalHelper.error('Error!', '{!! implode("\\n", $errors->all()) !!}');
        @endif
    </script>

    <!-- DataTables Buttons global fix: remove unintended btn-secondary so color classes apply -->
    <script>
        (function ($) {
            // On every DataTable init/draw, ensure dt-buttons don't keep btn-secondary
            $(document).on('init.dt', function (e, settings) {
                var $wrapper = $(settings.nTableWrapper);
                $wrapper.find('.dt-buttons .dt-button').removeClass('btn-secondary');
            });
            $(document).on('draw.dt', function (e, settings) {
                var $wrapper = $(settings.nTableWrapper);
                $wrapper.find('.dt-buttons .dt-button').removeClass('btn-secondary');
            });
        })(jQuery);
    </script>


    
    @stack('js')
</body>
</html>
