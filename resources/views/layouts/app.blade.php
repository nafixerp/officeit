<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') - {{ config('app.name', 'OfficeIT ERP') }}</title>

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    {{-- Bootstrap 5 CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Font Awesome 6 --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    @yield('styles')

    <style>
        :root {
            --sidebar-width: 260px;
            --sidebar-bg: #1e293b;
            --sidebar-hover: #334155;
            --sidebar-active: #3b82f6;
            --sidebar-text: #94a3b8;
            --sidebar-text-active: #ffffff;
            --navbar-height: 60px;
            --content-bg: #f1f5f9;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--content-bg);
            overflow-x: hidden;
        }

        /* ===== Sidebar ===== */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--sidebar-bg);
            z-index: 1040;
            transition: transform 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .sidebar-brand {
            height: var(--navbar-height);
            display: flex;
            align-items: center;
            padding: 0 1.25rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            flex-shrink: 0;
        }

        .sidebar-brand h4 {
            color: #fff;
            font-weight: 700;
            font-size: 1.15rem;
            margin: 0;
            white-space: nowrap;
        }

        .sidebar-brand .brand-icon {
            width: 34px;
            height: 34px;
            background: var(--sidebar-active);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1rem;
            margin-right: 0.75rem;
            flex-shrink: 0;
        }

        .sidebar-nav {
            flex: 1;
            overflow-y: auto;
            padding: 0.75rem 0;
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,0.15) transparent;
        }

        .sidebar-nav::-webkit-scrollbar {
            width: 5px;
        }

        .sidebar-nav::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar-nav::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 10px;
        }

        .nav-section-title {
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #64748b;
            padding: 1rem 1.25rem 0.4rem;
            font-weight: 600;
        }

        .sidebar-nav .nav-link {
            color: var(--sidebar-text);
            padding: 0.5rem 1.25rem;
            font-size: 0.835rem;
            font-weight: 400;
            display: flex;
            align-items: center;
            transition: all 0.15s ease;
            border-radius: 0;
            white-space: nowrap;
        }

        .sidebar-nav .nav-link:hover {
            color: var(--sidebar-text-active);
            background: var(--sidebar-hover);
        }

        .sidebar-nav .nav-link.active,
        .sidebar-nav .nav-link[aria-expanded="true"] {
            color: var(--sidebar-text-active);
            background: var(--sidebar-hover);
        }

        .sidebar-nav .nav-link .nav-icon {
            width: 20px;
            text-align: center;
            margin-right: 0.75rem;
            font-size: 0.9rem;
            flex-shrink: 0;
        }

        .sidebar-nav .nav-link .nav-arrow {
            margin-left: auto;
            font-size: 0.65rem;
            transition: transform 0.2s ease;
        }

        .sidebar-nav .nav-link[aria-expanded="true"] .nav-arrow {
            transform: rotate(90deg);
        }

        .sidebar-nav .sub-menu {
            background: rgba(0, 0, 0, 0.15);
        }

        .sidebar-nav .sub-menu .nav-link {
            padding: 0.4rem 1.25rem 0.4rem 3.25rem;
            font-size: 0.8rem;
        }

        .sidebar-nav .sub-menu .nav-link::before {
            content: '';
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: var(--sidebar-text);
            margin-right: 0.75rem;
            flex-shrink: 0;
            transition: background 0.15s ease;
        }

        .sidebar-nav .sub-menu .nav-link:hover::before,
        .sidebar-nav .sub-menu .nav-link.active::before {
            background: var(--sidebar-active);
        }

        .sidebar-nav .sub-menu .nav-link.active {
            color: var(--sidebar-active);
            background: rgba(59, 130, 246, 0.08);
        }

        /* ===== Main Content ===== */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        /* ===== Top Navbar ===== */
        .top-navbar {
            height: var(--navbar-height);
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
            position: sticky;
            top: 0;
            z-index: 1030;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        }

        .top-navbar .navbar-toggler {
            border: none;
            padding: 0.25rem 0.5rem;
            font-size: 1.2rem;
            color: #475569;
            display: none;
            background: none;
        }

        .top-navbar .navbar-toggler:focus {
            box-shadow: none;
        }

        .top-navbar .navbar-right {
            display: flex;
            align-items: center;
            margin-left: auto;
            gap: 0.5rem;
        }

        .top-navbar .nav-icon-btn {
            width: 38px;
            height: 38px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #475569;
            background: transparent;
            border: none;
            transition: background 0.15s ease;
            position: relative;
        }

        .top-navbar .nav-icon-btn:hover {
            background: #f1f5f9;
        }

        .notification-badge {
            position: absolute;
            top: 4px;
            right: 4px;
            width: 8px;
            height: 8px;
            background: #ef4444;
            border-radius: 50%;
            border: 2px solid #fff;
        }

        .user-dropdown .dropdown-toggle {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            border: none;
            background: transparent;
            padding: 0.25rem 0.5rem;
            border-radius: 8px;
            transition: background 0.15s ease;
        }

        .user-dropdown .dropdown-toggle:hover {
            background: #f1f5f9;
        }

        .user-dropdown .dropdown-toggle::after {
            display: none;
        }

        .user-avatar {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            background: var(--sidebar-active);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.8rem;
        }

        .user-info {
            text-align: left;
            line-height: 1.2;
        }

        .user-info .user-name {
            font-size: 0.835rem;
            font-weight: 600;
            color: #1e293b;
        }

        .user-info .user-role {
            font-size: 0.7rem;
            color: #64748b;
        }

        /* ===== Page Content ===== */
        .page-header {
            padding: 1.25rem 1.5rem;
            background: transparent;
        }

        .page-header .page-title {
            font-size: 1.35rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
        }

        .page-header .breadcrumb {
            margin: 0;
            padding: 0;
            background: transparent;
            font-size: 0.8rem;
        }

        .page-header .breadcrumb-item a {
            color: #64748b;
            text-decoration: none;
        }

        .page-header .breadcrumb-item.active {
            color: #94a3b8;
        }

        .page-body {
            padding: 0 1.5rem 2rem;
        }

        /* ===== Toast Area ===== */
        .toast-container {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 1080;
        }

        /* ===== Sidebar Overlay (mobile) ===== */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1035;
        }

        /* ===== Responsive ===== */
        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .sidebar-overlay.show {
                display: block;
            }

            .main-content {
                margin-left: 0;
            }

            .top-navbar .navbar-toggler {
                display: flex;
            }
        }

        /* ===== Utility ===== */
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
        }

        .card-header {
            background: transparent;
            border-bottom: 1px solid #f1f5f9;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .table th {
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            color: #64748b;
            font-weight: 600;
            border-bottom-width: 1px;
        }

        .table td {
            font-size: 0.85rem;
            vertical-align: middle;
        }

        .btn {
            font-size: 0.835rem;
            font-weight: 500;
            border-radius: 7px;
            padding: 0.45rem 1rem;
        }
    </style>
</head>
<body>
    {{-- Sidebar Overlay --}}
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    {{-- Sidebar --}}
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="brand-icon">
                <i class="fas fa-building"></i>
            </div>
            <h4>OfficeIT ERP</h4>
        </div>

        <nav class="sidebar-nav">
            <ul class="nav flex-column">
                {{-- Dashboard --}}
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('dashboard') }}" data-page="dashboard">
                        <span class="nav-icon"><i class="fas fa-th-large"></i></span>
                        Dashboard
                    </a>
                </li>

                {{-- Masters --}}
                <div class="nav-section-title">Masters</div>
                <li class="nav-item">
                    <a class="nav-link" href="#mastersMenu" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="mastersMenu">
                        <span class="nav-icon"><i class="fas fa-database"></i></span>
                        Masters
                        <span class="nav-arrow"><i class="fas fa-chevron-right"></i></span>
                    </a>
                    <div class="collapse sub-menu" id="mastersMenu">
                        <ul class="nav flex-column">
                            <li class="nav-item"><a class="nav-link" href="{{ url('/companies') }}">Company</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ url('/countries') }}">Country</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ url('/currencies') }}">Currency</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ url('/branches') }}">Branch</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ url('/departments') }}">Department</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ url('/customers') }}">Customer</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ url('/suppliers') }}">Supplier</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ url('/items') }}">Items</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ url('/services') }}">Services</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ url('/chart-of-accounts') }}">Chart of Accounts</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ url('/bank-accounts') }}">Bank Accounts</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ url('/warehouses') }}">Warehouses</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ url('/tax-profiles') }}">Tax Profiles</a></li>
                        </ul>
                    </div>
                </li>

                {{-- Sales --}}
                <div class="nav-section-title">Transactions</div>
                <li class="nav-item">
                    <a class="nav-link" href="#salesMenu" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="salesMenu">
                        <span class="nav-icon"><i class="fas fa-file-invoice-dollar"></i></span>
                        Sales
                        <span class="nav-arrow"><i class="fas fa-chevron-right"></i></span>
                    </a>
                    <div class="collapse sub-menu" id="salesMenu">
                        <ul class="nav flex-column">
                            <li class="nav-item"><a class="nav-link" href="{{ url('/quotations') }}">Quotations</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ url('/sales-orders') }}">Sales Orders</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ url('/sales-invoices') }}">Sales Invoices</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ url('/sales-returns') }}">Sales Returns</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ url('/credit-notes') }}">Credit Notes</a></li>
                        </ul>
                    </div>
                </li>

                {{-- Purchase --}}
                <li class="nav-item">
                    <a class="nav-link" href="#purchaseMenu" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="purchaseMenu">
                        <span class="nav-icon"><i class="fas fa-shopping-cart"></i></span>
                        Purchase
                        <span class="nav-arrow"><i class="fas fa-chevron-right"></i></span>
                    </a>
                    <div class="collapse sub-menu" id="purchaseMenu">
                        <ul class="nav flex-column">
                            <li class="nav-item"><a class="nav-link" href="{{ url('/purchase-orders') }}">Purchase Orders</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ url('/purchase-invoices') }}">Purchase Invoices</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ url('/purchase-returns') }}">Purchase Returns</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ url('/debit-notes') }}">Debit Notes</a></li>
                        </ul>
                    </div>
                </li>

                {{-- Finance --}}
                <li class="nav-item">
                    <a class="nav-link" href="#financeMenu" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="financeMenu">
                        <span class="nav-icon"><i class="fas fa-coins"></i></span>
                        Finance
                        <span class="nav-arrow"><i class="fas fa-chevron-right"></i></span>
                    </a>
                    <div class="collapse sub-menu" id="financeMenu">
                        <ul class="nav flex-column">
                            <li class="nav-item"><a class="nav-link" href="{{ url('/receipts') }}">Receipts</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ url('/payments') }}">Payments</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ url('/journal-entries') }}">Journal Entries</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ url('/contra-entries') }}">Contra Entries</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ url('/expenses') }}">Expenses</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ url('/bank-reconciliation') }}">Bank Reconciliation</a></li>
                        </ul>
                    </div>
                </li>

                {{-- Inventory --}}
                <li class="nav-item">
                    <a class="nav-link" href="#inventoryMenu" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="inventoryMenu">
                        <span class="nav-icon"><i class="fas fa-boxes-stacked"></i></span>
                        Inventory
                        <span class="nav-arrow"><i class="fas fa-chevron-right"></i></span>
                    </a>
                    <div class="collapse sub-menu" id="inventoryMenu">
                        <ul class="nav flex-column">
                            <li class="nav-item"><a class="nav-link" href="{{ url('/stock-transactions') }}">Stock Transactions</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ url('/stock-report') }}">Stock Report</a></li>
                        </ul>
                    </div>
                </li>

                {{-- HR --}}
                <div class="nav-section-title">Human Resources</div>
                <li class="nav-item">
                    <a class="nav-link" href="#hrMenu" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="hrMenu">
                        <span class="nav-icon"><i class="fas fa-users"></i></span>
                        HR
                        <span class="nav-arrow"><i class="fas fa-chevron-right"></i></span>
                    </a>
                    <div class="collapse sub-menu" id="hrMenu">
                        <ul class="nav flex-column">
                            <li class="nav-item"><a class="nav-link" href="{{ url('/employees') }}">Employees</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ url('/recruitment') }}">Recruitment</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ url('/candidates') }}">Candidates</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ url('/hr-letters') }}">HR Letters</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ url('/leave-types') }}">Leave Types</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ url('/leave-requests') }}">Leave Requests</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ url('/attendance') }}">Attendance</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ url('/payroll') }}">Payroll</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ url('/employee-loans') }}">Employee Loans</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ url('/assets') }}">Assets</a></li>
                        </ul>
                    </div>
                </li>

                {{-- Reports --}}
                <div class="nav-section-title">Analytics</div>
                <li class="nav-item">
                    <a class="nav-link" href="#reportsMenu" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="reportsMenu">
                        <span class="nav-icon"><i class="fas fa-chart-bar"></i></span>
                        Reports
                        <span class="nav-arrow"><i class="fas fa-chevron-right"></i></span>
                    </a>
                    <div class="collapse sub-menu" id="reportsMenu">
                        <ul class="nav flex-column">
                            <li class="nav-item"><a class="nav-link" href="{{ url('/reports/sales') }}">Sales Reports</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ url('/reports/purchase') }}">Purchase Reports</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ url('/reports/financial') }}">Financial Reports</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ url('/reports/tax') }}">Tax Reports</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ url('/reports/hr') }}">HR Reports</a></li>
                        </ul>
                    </div>
                </li>

                {{-- Settings --}}
                <div class="nav-section-title">System</div>
                <li class="nav-item">
                    <a class="nav-link" href="#settingsMenu" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="settingsMenu">
                        <span class="nav-icon"><i class="fas fa-cog"></i></span>
                        Settings
                        <span class="nav-arrow"><i class="fas fa-chevron-right"></i></span>
                    </a>
                    <div class="collapse sub-menu" id="settingsMenu">
                        <ul class="nav flex-column">
                            <li class="nav-item"><a class="nav-link" href="{{ url('/users') }}">Users</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ url('/roles') }}">Roles & Permissions</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ url('/company-settings') }}">Company Settings</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ url('/activity-log') }}">Activity Log</a></li>
                        </ul>
                    </div>
                </li>
            </ul>
        </nav>
    </aside>

    {{-- Main Content --}}
    <div class="main-content">
        {{-- Top Navbar --}}
        <header class="top-navbar">
            <button class="navbar-toggler" id="sidebarToggle" type="button">
                <i class="fas fa-bars"></i>
            </button>

            <div class="navbar-right">
                {{-- Notifications --}}
                <button class="nav-icon-btn" type="button" title="Notifications">
                    <i class="far fa-bell"></i>
                    <span class="notification-badge"></span>
                </button>

                {{-- Fullscreen --}}
                <button class="nav-icon-btn d-none d-md-flex" type="button" id="fullscreenToggle" title="Fullscreen">
                    <i class="fas fa-expand"></i>
                </button>

                {{-- User Dropdown --}}
                <div class="dropdown user-dropdown">
                    <button class="dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="user-avatar">
                            {{ substr(Auth::user()->name ?? 'U', 0, 1) }}
                        </div>
                        <div class="user-info d-none d-sm-block">
                            <div class="user-name">{{ Auth::user()->name ?? 'User' }}</div>
                            <div class="user-role">Administrator</div>
                        </div>
                        <i class="fas fa-chevron-down ms-1" style="font-size: 0.6rem; color: #94a3b8;"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" style="min-width: 200px;">
                        <li>
                            <a class="dropdown-item py-2" href="{{ url('/profile') }}">
                                <i class="fas fa-user me-2 text-muted"></i> My Profile
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item py-2" href="{{ url('/company-settings') }}">
                                <i class="fas fa-cog me-2 text-muted"></i> Settings
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item py-2 text-danger">
                                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        {{-- Page Header --}}
        <div class="page-header">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <h1 class="page-title">@yield('title', 'Dashboard')</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 mt-1">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                            @yield('breadcrumb')
                        </ol>
                    </nav>
                </div>
                <div class="page-actions d-flex gap-2">
                    @yield('actions')
                </div>
            </div>
        </div>

        {{-- Flash Messages --}}
        @include('partials.flash-messages')

        {{-- Page Body --}}
        <div class="page-body">
            @yield('content')
        </div>
    </div>

    {{-- Toast Container --}}
    <div class="toast-container" id="toastContainer"></div>

    {{-- Modals --}}
    @stack('modals')

    {{-- Bootstrap JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var sidebar = document.getElementById('sidebar');
            var overlay = document.getElementById('sidebarOverlay');
            var toggler = document.getElementById('sidebarToggle');

            // Sidebar toggle (mobile)
            if (toggler) {
                toggler.addEventListener('click', function () {
                    sidebar.classList.toggle('show');
                    overlay.classList.toggle('show');
                });
            }

            if (overlay) {
                overlay.addEventListener('click', function () {
                    sidebar.classList.remove('show');
                    overlay.classList.remove('show');
                });
            }

            // Active menu highlighting based on current URL
            var currentUrl = window.location.href;
            var sidebarLinks = document.querySelectorAll('.sidebar-nav .nav-link[href]');

            sidebarLinks.forEach(function (link) {
                if (link.getAttribute('href') === '#') return;
                if (link.getAttribute('data-bs-toggle') === 'collapse') return;

                if (currentUrl.indexOf(link.getAttribute('href')) !== -1) {
                    link.classList.add('active');

                    // Expand parent collapse
                    var parentCollapse = link.closest('.collapse');
                    if (parentCollapse) {
                        parentCollapse.classList.add('show');
                        var trigger = document.querySelector('[href="#' + parentCollapse.id + '"], [data-bs-target="#' + parentCollapse.id + '"]');
                        if (trigger) {
                            trigger.setAttribute('aria-expanded', 'true');
                        }
                    }
                }
            });

            // Fullscreen toggle
            var fsBtn = document.getElementById('fullscreenToggle');
            if (fsBtn) {
                fsBtn.addEventListener('click', function () {
                    if (!document.fullscreenElement) {
                        document.documentElement.requestFullscreen();
                        fsBtn.innerHTML = '<i class="fas fa-compress"></i>';
                    } else {
                        document.exitFullscreen();
                        fsBtn.innerHTML = '<i class="fas fa-expand"></i>';
                    }
                });
            }

            // Auto-dismiss toasts
            var toastEls = document.querySelectorAll('.toast');
            toastEls.forEach(function (el) {
                new bootstrap.Toast(el, { delay: 4000 }).show();
            });
        });

        // Global toast function
        function showToast(message, type) {
            type = type || 'info';
            var icons = { success: 'check-circle', error: 'times-circle', warning: 'exclamation-triangle', info: 'info-circle' };
            var container = document.getElementById('toastContainer');
            var toast = document.createElement('div');
            toast.className = 'toast align-items-center text-bg-' + (type === 'error' ? 'danger' : type) + ' border-0';
            toast.setAttribute('role', 'alert');
            toast.innerHTML =
                '<div class="d-flex">' +
                    '<div class="toast-body"><i class="fas fa-' + (icons[type] || 'info-circle') + ' me-2"></i>' + message + '</div>' +
                    '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>' +
                '</div>';
            container.appendChild(toast);
            new bootstrap.Toast(toast, { delay: 4000 }).show();
            toast.addEventListener('hidden.bs.toast', function () { toast.remove(); });
        }
    </script>

    @yield('scripts')
</body>
</html>
