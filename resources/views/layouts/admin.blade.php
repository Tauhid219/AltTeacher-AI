<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'AltTeacher-AI') }} - @yield('title', 'Dashboard')</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/plugins/fontawesome-free/css/all.min.css') }}">
    <!-- overlayScrollbars -->
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/plugins/overlayScrollbars/css/OverlayScrollbars.min.css') }}">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">
    
    @yield('styles')

    <!-- Theme Initialization Script to Prevent Flashing -->
    <script>
        (function() {
            const theme = localStorage.getItem('theme') || 'system';
            const isDark = theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
            if (isDark) {
                document.documentElement.classList.add('dark-mode');
                document.addEventListener('DOMContentLoaded', function() {
                    document.body.classList.add('dark-mode');
                });
            } else {
                document.documentElement.classList.remove('dark-mode');
            }
        })();
    </script>
    <style>
        /* Custom dark-mode overrides for AdminLTE */
        .dark-mode body, body.dark-mode {
            background-color: #454d55 !important;
            color: #fff;
        }
        .dark-mode .main-header {
            background-color: #343a40 !important;
            border-color: #4b545c !important;
        }
        .dark-mode .main-header .nav-link {
            color: #c2c7d0 !important;
        }
        .dark-mode .main-header .nav-link:hover {
            color: #fff !important;
        }
        .dark-mode .content-wrapper {
            background-color: #454d55 !important;
        }
        .dark-mode .card {
            background-color: #343a40 !important;
            color: #fff;
        }
        .dark-mode .card-header {
            border-bottom: 1px solid #4b545c;
        }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light" id="main-navbar">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="{{ route('dashboard') }}" class="nav-link">Home</a>
            </li>
        </ul>

        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto">
            <!-- Theme Selector Dropdown -->
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                    <i class="fas" id="current-theme-icon"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right p-2" style="min-width: 150px;">
                    <a href="#" class="dropdown-item d-flex align-items-center justify-content-between theme-opt" data-theme="light">
                        <span><i class="fas fa-sun mr-2"></i> Light</span>
                        <i class="fas fa-check check-mark d-none" data-theme="light"></i>
                    </a>
                    <a href="#" class="dropdown-item d-flex align-items-center justify-content-between theme-opt" data-theme="dark">
                        <span><i class="fas fa-moon mr-2"></i> Dark</span>
                        <i class="fas fa-check check-mark d-none" data-theme="dark"></i>
                    </a>
                    <a href="#" class="dropdown-item d-flex align-items-center justify-content-between theme-opt" data-theme="system">
                        <span><i class="fas fa-desktop mr-2"></i> System</span>
                        <i class="fas fa-check check-mark d-none" data-theme="system"></i>
                    </a>
                </div>
            </li>

            <!-- User Info and Logout -->
            <li class="nav-item dropdown">
                <a class="nav-link d-flex align-items-center" data-toggle="dropdown" href="#">
                    <span class="mr-2 d-none d-md-inline">{{ Auth::user()->name }}</span>
                    <i class="fas fa-user-circle fa-lg"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right">
                    <span class="dropdown-item dropdown-header text-bold">{{ ucfirst(str_replace('_', ' ', Auth::user()->roles->first()->name ?? 'User')) }}</span>
                    <div class="dropdown-divider"></div>
                    <a href="{{ route('profile.edit') }}" class="dropdown-item">
                        <i class="fas fa-cog mr-2"></i> Profile Settings
                    </a>
                    <div class="dropdown-divider"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <a href="{{ route('logout') }}" class="dropdown-item text-danger"
                           onclick="event.preventDefault(); this.closest('form').submit();">
                            <i class="fas fa-sign-out-alt mr-2"></i> Logout
                        </a>
                    </form>
                </div>
            </li>
        </ul>
    </nav>
    <!-- /.navbar -->

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar elevation-4 sidebar-dark-primary" id="main-sidebar">
        <!-- Brand Logo -->
        <a href="{{ route('dashboard') }}" class="brand-link">
            <span class="brand-text font-weight-light">AltTeacher-AI</span>
        </a>

        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Sidebar Menu -->
            <nav class="mt-3">
                <ul class="nav nav-pills nav-sidebar flex-column text-sm" data-widget="treeview" role="menu" data-accordion="false">
                    @yield('sidebar-menu')
                </ul>
            </nav>
            <!-- /.sidebar-menu -->
        </div>
        <!-- /.sidebar -->
    </aside>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>@yield('page-title', 'Dashboard')</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right text-sm">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                            @yield('breadcrumb')
                        </ol>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="icon fas fa-check mr-2"></i> {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="icon fas fa-ban mr-2"></i> {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
                
                @yield('content')
            </div>
        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

    <footer class="main-footer text-xs">
        <div class="float-right d-none d-sm-block">
            <b>Version</b> 1.0.0
        </div>
        <strong>Copyright &copy; 2026 AltTeacher-AI.</strong> All rights reserved.
    </footer>
</div>
<!-- ./wrapper -->

<!-- jQuery -->
<script src="{{ asset('vendor/adminlte/plugins/jquery/jquery.min.js') }}"></script>
<!-- Bootstrap 4 -->
<script src="{{ asset('vendor/adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<!-- overlayScrollbars -->
<script src="{{ asset('vendor/adminlte/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
<!-- AdminLTE App -->
<script src="{{ asset('vendor/adminlte/dist/js/adminlte.min.js') }}"></script>

<!-- Theme Control Script -->
<script>
    $(document).ready(function() {
        const body = $('body');
        const navbar = $('#main-navbar');
        const sidebar = $('#main-sidebar');

        function applyTheme(theme) {
            let isDark = false;
            if (theme === 'dark') {
                isDark = true;
            } else if (theme === 'system') {
                isDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            }

            // Apply Body and HTML Class
            if (isDark) {
                body.addClass('dark-mode');
                $('html').addClass('dark-mode');
                navbar.removeClass('navbar-white navbar-light').addClass('navbar-dark bg-dark');
                sidebar.removeClass('sidebar-light-primary').addClass('sidebar-dark-primary');
            } else {
                body.removeClass('dark-mode');
                $('html').removeClass('dark-mode');
                navbar.removeClass('navbar-dark bg-dark').addClass('navbar-white navbar-light');
                sidebar.removeClass('sidebar-dark-primary').addClass('sidebar-light-primary');
            }

            // Update Icon
            const icon = $('#current-theme-icon');
            icon.removeClass('fa-sun fa-moon fa-desktop');
            if (theme === 'light') {
                icon.addClass('fa-sun');
            } else if (theme === 'dark') {
                icon.addClass('fa-moon');
            } else {
                icon.addClass('fa-desktop');
            }

            // Update check marks
            $('.check-mark').addClass('d-none');
            $(`.check-mark[data-theme="${theme}"]`).removeClass('d-none');
        }

        // Initialize Theme
        const storedTheme = localStorage.getItem('theme') || 'system';
        applyTheme(storedTheme);

        // Theme Choice Clicks
        $('.theme-opt').on('click', function(e) {
            e.preventDefault();
            const theme = $(this).data('theme');
            localStorage.setItem('theme', theme);
            applyTheme(theme);
        });

        // Listen for system theme changes if set to system
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
            if (localStorage.getItem('theme') === 'system') {
                applyTheme('system');
            }
        });
    });
</script>

@yield('scripts')
</body>
</html>
