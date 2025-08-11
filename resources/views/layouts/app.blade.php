<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Aplikasi Payroll KlikMedis')</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">
    <!-- overlayScrollbars -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/overlayscrollbars/2.4.0/css/OverlayScrollbars.min.css">
    
    @stack('css')
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
                    <a class="nav-link" data-toggle="dropdown" href="#">
                        <i class="fas fa-user"></i>
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
                <!-- Sidebar user panel (optional) -->
                <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                    <div class="image">
                        <img src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/img/user2-160x160.jpg" class="img-circle elevation-2" alt="User Image">
                    </div>
                    <div class="info">
                        <a href="#" class="d-block">{{ Auth::user()->name }}</a>
                    </div>
                </div>

                <!-- Sidebar Menu -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                        <!-- Add icons to the links using the .nav-icon class
                             with font-awesome or any other icon font library -->
                        <li class="nav-item">
                            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('employees.index') }}" class="nav-link {{ request()->routeIs('employees.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-users"></i>
                                <p>Karyawan</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('payroll.index') }}" class="nav-link {{ request()->routeIs('payroll.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-money-bill-wave"></i>
                                <p>Payroll</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('attendance.index') }}" class="nav-link {{ request()->routeIs('attendance.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-clock"></i>
                                <p>Absensi</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('attendance.check-in-out') }}" class="nav-link {{ request()->routeIs('attendance.check-in-out') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-sign-in-alt"></i>
                                <p>Check In/Out</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('attendance.calendar') }}" class="nav-link {{ request()->routeIs('attendance.calendar*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Attendance Calendar</p>
                            </a>
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
                                @if(in_array(auth()->user()->role, ['admin', 'hr']))
                                <li class="nav-item">
                                    <a href="{{ route('settings.payroll-policy') }}" class="nav-link {{ request()->routeIs('settings.payroll-policy') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Payroll Policy</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('settings.leave-policy') }}" class="nav-link {{ request()->routeIs('settings.leave-policy') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Leave Policy</p>
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
                                @if(in_array(auth()->user()->role, ['admin']))
                                <li class="nav-item">
                                    <a href="{{ route('settings.users') }}" class="nav-link {{ request()->routeIs('settings.users') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>User Management</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('settings.system') }}" class="nav-link {{ request()->routeIs('settings.system') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>System Settings</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('settings.backup') }}" class="nav-link {{ request()->routeIs('settings.backup') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Backup & Restore</p>
                                    </a>
                                </li>
                                @endif
                            </ul>
                                </li>
        
        <!-- Export Functionality -->
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
                <li class="nav-item">
                    <a href="{{ route('exports.attendance') }}?format=xlsx" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Export Attendance</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('exports.taxes') }}?format=xlsx" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Export Taxes</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('exports.bpjs') }}?format=xlsx" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Export BPJS</p>
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
                    <a href="{{ route('bank-accounts.create') }}" class="nav-link {{ request()->routeIs('bank-accounts.create') ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Add Bank Account</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('salary-transfers.index') }}" class="nav-link {{ request()->routeIs('salary-transfers.*') ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Salary Transfers</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('salary-transfers.create') }}" class="nav-link {{ request()->routeIs('salary-transfers.create') ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>New Transfer</p>
                    </a>
                </li>
            </ul>
        </li>

        <!-- External System Integration -->
        <li class="nav-item {{ request()->routeIs('integrations.*') ? 'menu-open' : '' }}">
            <a href="#" class="nav-link {{ request()->routeIs('integrations.*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-plug"></i>
                <p>
                    External Integration
                    <i class="right fas fa-angle-left"></i>
                </p>
            </a>
            <ul class="nav nav-treeview">
                <li class="nav-item">
                    <a href="{{ route('integrations.index') }}" class="nav-link {{ request()->routeIs('integrations.index') ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Integrations</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('integrations.create') }}" class="nav-link {{ request()->routeIs('integrations.create') ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Add Integration</p>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Data Import/Export -->
        <li class="nav-item {{ request()->routeIs('import.*') ? 'menu-open' : '' }}">
            <a href="#" class="nav-link {{ request()->routeIs('import.*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-upload"></i>
                <p>
                    Data Import
                    <i class="right fas fa-angle-left"></i>
                </p>
            </a>
            <ul class="nav nav-treeview">
                <li class="nav-item">
                    <a href="{{ route('import.index') }}" class="nav-link {{ request()->routeIs('import.index') ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Import Dashboard</p>
                    </a>
                </li>
            </ul>
        </li>
                        <p>Add Bank Account</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('salary-transfers.index') }}" class="nav-link {{ request()->routeIs('salary-transfers.*') ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Salary Transfers</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('salary-transfers.create') }}" class="nav-link {{ request()->routeIs('salary-transfers.create') ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>New Transfer</p>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Performance Management -->
        <li class="nav-item {{ request()->routeIs('performance.*') ? 'menu-open' : '' }}">
            <a href="#" class="nav-link {{ request()->routeIs('performance.*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-chart-line"></i>
                <p>
                    Performance Management
                    <i class="right fas fa-angle-left"></i>
                </p>
            </a>
            <ul class="nav nav-treeview">
                <li class="nav-item">
                    <a href="{{ route('performance.index') }}" class="nav-link {{ request()->routeIs('performance.index') ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Performance Dashboard</p>
                    </a>
                </li>
                @if(in_array(auth()->user()->role, ['admin', 'hr']))
                <li class="nav-item">
                    <a href="{{ route('performance.kpi') }}" class="nav-link {{ request()->routeIs('performance.kpi') ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>KPI Management</p>
                    </a>
                </li>
                @endif
                @if(in_array(auth()->user()->role, ['admin', 'hr', 'manager']))
                <li class="nav-item">
                    <a href="{{ route('performance.appraisal') }}" class="nav-link {{ request()->routeIs('performance.appraisal') ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Performance Appraisal</p>
                    </a>
                </li>
                @endif
                @if(in_array(auth()->user()->role, ['admin', 'hr']))
                <li class="nav-item">
                    <a href="{{ route('performance.bonus') }}" class="nav-link {{ request()->routeIs('performance.bonus') ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Performance Bonus</p>
                    </a>
                </li>
                @endif
                <li class="nav-item">
                    <a href="{{ route('performance.goals') }}" class="nav-link {{ request()->routeIs('performance.goals') ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Goal Setting</p>
                    </a>
                </li>
                @if(in_array(auth()->user()->role, ['admin', 'hr']))
                <li class="nav-item">
                    <a href="{{ route('performance.reports') }}" class="nav-link {{ request()->routeIs('performance.reports') ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Performance Reports</p>
                    </a>
                </li>
                @endif
            </ul>
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
                                <li class="nav-item">
                                    <a href="{{ route('payrolls.create') }}" class="nav-link {{ request()->routeIs('payrolls.create') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Generate Payroll</p>
                                    </a>
                                </li>
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
                                    <a href="{{ route('taxes.create') }}" class="nav-link {{ request()->routeIs('taxes.create') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>New Tax Calculation</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('taxes.report') }}" class="nav-link {{ request()->routeIs('taxes.report') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Tax Reports</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('taxes.monthly-report') }}" class="nav-link {{ request()->routeIs('taxes.monthly-report') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Monthly Report</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('taxes.annual-summary') }}" class="nav-link {{ request()->routeIs('taxes.annual-summary') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Annual Summary</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('taxes.payment-report') }}" class="nav-link {{ request()->routeIs('taxes.payment-report') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Payment Report</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('taxes.certificate-report') }}" class="nav-link {{ request()->routeIs('taxes.certificate-report') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Certificate Report</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('taxes.compliance-report') }}" class="nav-link {{ request()->routeIs('taxes.compliance-report') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Compliance Report</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('taxes.audit-trail') }}" class="nav-link {{ request()->routeIs('taxes.audit-trail') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Audit Trail</p>
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
                                    <a href="{{ route('bpjs.create') }}" class="nav-link {{ request()->routeIs('bpjs.create') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>New BPJS Calculation</p>
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
                                <li class="nav-item">
                                    <a href="{{ route('overtimes.statistics') }}" class="nav-link {{ request()->routeIs('overtimes.statistics') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Overtime Statistics</p>
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
                        <li class="nav-item">
                            <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-chart-bar"></i>
                                <p>Laporan</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('settings.index') }}" class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-cog"></i>
                                <p>Pengaturan</p>
                            </a>
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
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                            <h5><i class="icon fas fa-check"></i> Success!</h5>
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                            <h5><i class="icon fas fa-ban"></i> Error!</h5>
                            {{ session('error') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                            <h5><i class="icon fas fa-ban"></i> Error!</h5>
                            <ul>
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

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
    
    @stack('js')
</body>
</html>
