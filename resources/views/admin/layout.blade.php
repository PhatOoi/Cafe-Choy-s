<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Quản trị') — Choy's Cafe Admin</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">

    <style>
        :root {
            --primary:      #d4813a;
            --primary-dark: #b8692a;
            --primary-light:#fff4ec;
            --admin-gold:   #f0a500;
            --sidebar-w:    260px;
            --sidebar-bg:   #0f0f1a;
            --sidebar-text: #b0b4cc;
            --sidebar-active: #d4813a;
            --bg:           #f2f4f8;
            --card-bg:      #ffffff;
            --border:       #e8ecf2;
            --text-dark:    #111827;
            --text-muted:   #6b7280;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg);
            display: flex;
            min-height: 100vh;
            color: var(--text-dark);
        }

        /* ═══════════════════════════════════
           SIDEBAR
        ═══════════════════════════════════ */
        .admin-sidebar {
            width: var(--sidebar-w);
            background: var(--sidebar-bg);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0; left: 0;
            height: 100vh;
            z-index: 200;
            transition: transform .25s ease;
            overflow: hidden;
        }

        /* decorative top line */
        .admin-sidebar::before {
            content: '';
            display: block;
            height: 3px;
            background: linear-gradient(90deg, var(--primary), var(--admin-gold), var(--primary));
            flex-shrink: 0;
        }

        .sidebar-brand {
            padding: 20px 22px 18px;
            border-bottom: 1px solid rgba(255,255,255,.06);
            flex-shrink: 0;
        }
        .brand-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }
        .brand-icon {
            width: 38px; height: 38px;
            background: linear-gradient(135deg, var(--primary), var(--admin-gold));
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px;
        }
        .brand-text-name {
            font-size: 16px;
            font-weight: 700;
            color: #fff;
            line-height: 1.1;
        }
        .brand-text-sub {
            font-size: 10px;
            color: var(--admin-gold);
            font-weight: 600;
            letter-spacing: 1.2px;
            text-transform: uppercase;
        }

        .sidebar-nav {
            flex: 1;
            padding: 10px 0 10px;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,.1) transparent;
        }

        .nav-label {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1.8px;
            text-transform: uppercase;
            color: rgba(255,255,255,.25);
            padding: 16px 22px 6px;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 10px 22px;
            color: var(--sidebar-text);
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 400;
            border-left: 3px solid transparent;
            transition: all .16s;
            position: relative;
        }
        .sidebar-link:hover {
            background: rgba(255,255,255,.05);
            color: #fff;
            text-decoration: none;
        }
        .sidebar-link.active {
            background: rgba(212,129,58,.14);
            color: var(--primary);
            border-left-color: var(--primary);
            font-weight: 600;
        }
        .sidebar-link i {
            width: 18px;
            text-align: center;
            font-size: 14px;
            flex-shrink: 0;
        }
        .nav-badge {
            margin-left: auto;
            background: var(--primary);
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 20px;
            line-height: 1.4;
        }

        .sidebar-footer {
            padding: 14px 18px;
            border-top: 1px solid rgba(255,255,255,.06);
            flex-shrink: 0;
        }
        .admin-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .admin-avatar {
            width: 38px; height: 38px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--admin-gold);
        }
        .admin-name { font-size: 13px; color: #fff; font-weight: 600; line-height: 1.3; }
        .admin-role-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 10px;
            background: linear-gradient(90deg, var(--primary), var(--admin-gold));
            color: #fff;
            padding: 2px 8px;
            border-radius: 10px;
            font-weight: 700;
            letter-spacing: .5px;
        }

        /* ═══════════════════════════════════
           MAIN AREA
        ═══════════════════════════════════ */
        .admin-main {
            margin-left: var(--sidebar-w);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            min-width: 0;
        }

        .admin-topbar {
            background: var(--card-bg);
            border-bottom: 1px solid var(--border);
            padding: 0 28px;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .topbar-left { display: flex; align-items: center; gap: 16px; }
        .topbar-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-dark);
        }
        .topbar-breadcrumb {
            font-size: 12px;
            color: var(--text-muted);
        }
        .topbar-right { display: flex; align-items: center; gap: 12px; }

        .topbar-icon-btn {
            width: 36px; height: 36px;
            border-radius: 10px;
            border: 1px solid var(--border);
            background: transparent;
            display: flex; align-items: center; justify-content: center;
            color: var(--text-muted);
            cursor: pointer;
            transition: all .16s;
            text-decoration: none;
        }
        .topbar-icon-btn:hover { background: var(--bg); color: var(--primary); border-color: var(--primary); }

        .topbar-date {
            font-size: 12px;
            color: var(--text-muted);
            background: var(--bg);
            padding: 6px 12px;
            border-radius: 8px;
        }

        .btn-logout-topbar {
            display: flex;
            align-items: center;
            gap: 6px;
            background: transparent;
            border: 1px solid var(--border);
            padding: 7px 14px;
            border-radius: 10px;
            font-size: 13px;
            color: var(--text-muted);
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            transition: all .16s;
        }
        .btn-logout-topbar:hover { background: #fff0f0; border-color: #fca5a5; color: #dc2626; }

        .sidebar-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 20px;
            color: var(--text-dark);
            cursor: pointer;
            padding: 4px;
        }

        /* ═══════════════════════════════════
           CONTENT
        ═══════════════════════════════════ */
        .admin-content { padding: 28px; flex: 1; }

        /* Page header */
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 12px;
        }
        .page-header-title { font-size: 22px; font-weight: 700; color: var(--text-dark); }
        .page-header-sub { font-size: 13px; color: var(--text-muted); margin-top: 2px; }

        /* Cards */
        .card {
            background: var(--card-bg);
            border-radius: 14px;
            border: 1px solid var(--border);
            box-shadow: 0 1px 6px rgba(0,0,0,.04);
        }
        .card-header {
            padding: 16px 22px;
            border-bottom: 1px solid var(--border);
            font-weight: 600;
            font-size: 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-radius: 14px 14px 0 0;
        }
        .card-header-title { display: flex; align-items: center; gap: 8px; }
        .card-body { padding: 22px; }

        /* Stat cards */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }
        @media (max-width: 1100px) { .stat-grid { grid-template-columns: repeat(2,1fr); } }
        @media (max-width: 600px)  { .stat-grid { grid-template-columns: 1fr; } }

        .stat-card {
            background: var(--card-bg);
            border-radius: 14px;
            border: 1px solid var(--border);
            padding: 20px;
            display: flex;
            align-items: flex-start;
            gap: 16px;
            transition: box-shadow .18s, transform .18s;
        }
        .stat-card:hover { box-shadow: 0 6px 20px rgba(0,0,0,.08); transform: translateY(-2px); }

        .stat-icon {
            width: 50px; height: 50px;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }
        .si-orange { background: linear-gradient(135deg,#fff0e4,#ffe0c4); color: var(--primary); }
        .si-blue   { background: linear-gradient(135deg,#eff6ff,#dbeafe); color: #2563eb; }
        .si-green  { background: linear-gradient(135deg,#f0fdf4,#dcfce7); color: #16a34a; }
        .si-purple { background: linear-gradient(135deg,#faf5ff,#ede9fe); color: #7c3aed; }
        .si-gold   { background: linear-gradient(135deg,#fffbeb,#fef3c7); color: #d97706; }
        .si-red    { background: linear-gradient(135deg,#fff1f2,#ffe4e6); color: #e11d48; }

        .stat-value { font-size: 26px; font-weight: 800; color: var(--text-dark); line-height: 1; }
        .stat-label { font-size: 12.5px; color: var(--text-muted); margin-top: 4px; }
        .stat-change {
            font-size: 11px;
            font-weight: 600;
            margin-top: 6px;
            display: flex;
            align-items: center;
            gap: 3px;
        }
        .stat-change.up   { color: #16a34a; }
        .stat-change.down { color: #dc2626; }

        /* Table */
        .admin-table { width: 100%; border-collapse: collapse; }
        .admin-table th {
            background: #f8f9fc;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .6px;
            text-transform: uppercase;
            color: var(--text-muted);
            padding: 12px 16px;
            border-bottom: 1px solid var(--border);
            text-align: left;
            white-space: nowrap;
        }
        .admin-table td {
            padding: 14px 16px;
            border-bottom: 1px solid #f3f4f8;
            font-size: 13.5px;
            vertical-align: middle;
        }
        .admin-table tr:last-child td { border-bottom: none; }
        .admin-table tr:hover td { background: #fafbfd; }

        /* Badges */
        .badge {
            font-size: 11px;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 20px;
            display: inline-block;
            white-space: nowrap;
        }
        .badge-admin    { background: #fef3c7; color: #92400e; }
        .badge-staff    { background: #dbeafe; color: #1d4ed8; }
        .badge-customer { background: #dcfce7; color: #166534; }
        .badge-active   { background: #dcfce7; color: #166534; }
        .badge-inactive { background: #f1f5f9; color: #64748b; }

        .badge-pending    { background: #fff8e5; color: #b45309; }
        .badge-confirmed  { background: #e0f2fe; color: #0369a1; }
        .badge-processing { background: #ede9fe; color: #6d28d9; }
        .badge-ready      { background: #d1fae5; color: #065f46; }
        .badge-delivering { background: #eff6ff; color: #2563eb; }
        .badge-delivered  { background: #dcfce7; color: #166534; }
        .badge-cancelled  { background: #f1f5f9; color: #64748b; }
        .badge-failed     { background: #ffe4e6; color: #be123c; }

        /* Buttons */
        .btn-primary-admin {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: #fff;
            border: none;
            padding: 9px 20px;
            border-radius: 10px;
            font-size: 13.5px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            transition: all .18s;
            font-family: 'Poppins', sans-serif;
        }
        .btn-primary-admin:hover {
            box-shadow: 0 4px 14px rgba(212,129,58,.4);
            color: #fff;
            text-decoration: none;
            transform: translateY(-1px);
        }

        .btn-outline-admin {
            background: transparent;
            color: var(--primary);
            border: 1.5px solid var(--primary);
            padding: 8px 18px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            transition: all .18s;
            font-family: 'Poppins', sans-serif;
        }
        .btn-outline-admin:hover { background: var(--primary); color: #fff; text-decoration: none; }

        .btn-sm {
            padding: 5px 12px;
            font-size: 12px;
            border-radius: 8px;
            gap: 5px;
        }

        .btn-danger {
            background: transparent;
            color: #dc2626;
            border: 1px solid #fca5a5;
            padding: 5px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all .16s;
            font-family: 'Poppins', sans-serif;
        }
        .btn-danger:hover { background: #dc2626; color: #fff; border-color: #dc2626; }

        .btn-warning {
            background: transparent;
            color: #d97706;
            border: 1px solid #fcd34d;
            padding: 5px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all .16s;
            font-family: 'Poppins', sans-serif;
            text-decoration: none;
        }
        .btn-warning:hover { background: #d97706; color: #fff; border-color: #d97706; text-decoration: none; }

        .btn-edit {
            background: transparent;
            color: #2563eb;
            border: 1px solid #bfdbfe;
            padding: 5px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all .16s;
            font-family: 'Poppins', sans-serif;
            text-decoration: none;
        }
        .btn-edit:hover { background: #2563eb; color: #fff; border-color: #2563eb; text-decoration: none; }

        /* Alerts */
        .alert {
            padding: 13px 18px;
            border-radius: 12px;
            font-size: 13.5px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .alert-success { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
        .alert-error   { background: #fff1f2; color: #be123c; border: 1px solid #fecdd3; }
        .alert-info    { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }

        /* Form inputs */
        .form-control, .form-select {
            border: 1.5px solid var(--border);
            border-radius: 10px;
            padding: 9px 14px;
            font-size: 13.5px;
            font-family: 'Poppins', sans-serif;
            transition: border-color .16s, box-shadow .16s;
            width: 100%;
        }
        .form-control:focus, .form-select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(212,129,58,.12);
        }
        .form-label {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 6px;
            display: block;
        }
        .form-text { font-size: 11.5px; color: var(--text-muted); margin-top: 4px; }
        .form-group { margin-bottom: 18px; }

        /* Filter bar */
        .filter-bar {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
            padding: 16px 22px;
            border-bottom: 1px solid var(--border);
            background: #fafbfc;
            border-radius: 14px 14px 0 0;
        }
        .filter-bar .form-control,
        .filter-bar .form-select { width: auto; min-width: 150px; }

        /* Avatar */
        .user-avatar {
            width: 36px; height: 36px;
            border-radius: 50%;
            object-fit: cover;
        }
        .user-cell {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .user-cell-name { font-weight: 600; font-size: 13.5px; }
        .user-cell-email { font-size: 11.5px; color: var(--text-muted); }

        /* Pagination */
        .pagination { gap: 4px; }
        .page-link {
            border-radius: 8px !important;
            border: 1px solid var(--border);
            color: var(--text-dark);
            font-size: 13px;
            padding: 6px 12px;
        }
        .page-item.active .page-link {
            background: var(--primary);
            border-color: var(--primary);
        }
        .page-link:hover { border-color: var(--primary); color: var(--primary); }

        /* Responsive */
        @media (max-width: 768px) {
            .admin-sidebar { transform: translateX(-100%); }
            .admin-sidebar.open { transform: translateX(0); }
            .admin-main { margin-left: 0; }
            .sidebar-toggle { display: block; }
            .admin-content { padding: 16px; }
        }
        .sidebar-brand {
            padding: 24px 20px 20px;
            border-bottom: 1px solid rgba(255,255,255,.08);
        }
        .sidebar-brand img { height: 42px; }
        .sidebar-brand p {
            color: var(--primary);
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin: 6px 0 0;
        }

        .sidebar-nav { flex: 1; padding: 16px 0; overflow-y: auto; }
    </style>

    @yield('styles')
</head>
<body>

{{-- ════════ SIDEBAR ════════ --}}
<aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-brand">
        <a href="{{ route('admin.dashboard') }}" class="brand-logo">
           <img src="{{ asset('images/logo.png') }}" alt="Logo" class="sidebar-brand">
            <div>
                <div class="brand-text-name">Choy's Cafe</div>
                <div class="brand-text-sub">Admin Panel</div>
            </div>
        </a>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-label">Tổng quan</div>
        <a href="{{ route('admin.dashboard') }}"
        class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="fas fa-chart-pie"></i> Dashboard
        </a>

        <div class="nav-label">Doanh thu</div>
        <a href="{{ route('admin.reports', ['period' => 'day']) }}"
        class="sidebar-link {{ request()->routeIs('admin.reports') && request('period','month') === 'day' ? 'active' : '' }}">
            <i class="fas fa-calendar-day"></i> Theo ngày
        </a>
        <a href="{{ route('admin.reports', ['period' => 'month']) }}"
        class="sidebar-link {{ request()->routeIs('admin.reports') && request('period','month') === 'month' ? 'active' : '' }}">
            <i class="fas fa-calendar-alt"></i> Theo tháng
        </a>
        <a href="{{ route('admin.reports', ['period' => 'year']) }}"
        class="sidebar-link {{ request()->routeIs('admin.reports') && request('period','month') === 'year' ? 'active' : '' }}">
            <i class="fas fa-calendar"></i> Theo năm
        </a>
        <a href="{{ route('admin.work-schedules.index') }}"
        class="sidebar-link {{ request()->routeIs('admin.work-schedules.*') ? 'active' : '' }}">
            <i class="fas fa-calendar-week"></i> Đăng ký giờ làm
        </a>
        <a href="{{ route('admin.payroll') }}"
        class="sidebar-link {{ request()->routeIs('admin.payroll') ? 'active' : '' }}">
            <i class="fas fa-wallet"></i> Bảng lương
        </a>
        <div class="nav-label">Sản phẩm</div>
        <a href="{{ route('admin.products') }}"
           class="sidebar-link {{ request()->routeIs('admin.products*') ? 'active' : '' }}">
            <i class="fas fa-coffee"></i> Danh sách sản phẩm
        </a>
        <a href="{{ route('admin.products.create') }}"
           class="sidebar-link {{ request()->routeIs('admin.products.create') ? 'active' : '' }}">
            <i class="fas fa-plus-circle"></i> Thêm sản phẩm
        </a>
        <a href="{{ route('admin.categories') }}"
           class="sidebar-link {{ request()->routeIs('admin.categories*') ? 'active' : '' }}">
            <i class="fas fa-tags"></i> Danh mục
        </a>

        <div class="nav-label">Vận hành</div>
        <a href="{{ route('admin.orders') }}"
           class="sidebar-link {{ request()->routeIs('admin.orders*') ? 'active' : '' }}">
            <i class="fas fa-receipt"></i> Quản lý đơn hàng
        </a>

        <div class="nav-label">Người dùng</div>
        <a href="{{ route('admin.users') }}"
           class="sidebar-link {{ request()->routeIs('admin.users*') && !request()->routeIs('admin.users.create') ? 'active' : '' }}">
            <i class="fas fa-users"></i> Danh sách tài khoản
        </a>
        <a href="{{ route('admin.users.create') }}"
           class="sidebar-link {{ request()->routeIs('admin.users.create') ? 'active' : '' }}">
            <i class="fas fa-user-plus"></i> Thêm nhân viên
        </a>

        <div class="nav-label">Hệ thống</div>
        <a href="{{ route('staff.dashboard') }}" class="sidebar-link">
            <i class="fas fa-exchange-alt"></i> Xem giao diện Staff
        </a>
        <a href="{{ url('/') }}" class="sidebar-link">
            <i class="fas fa-store"></i> Về trang cửa hàng
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="admin-info">
            <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=d4813a&color=fff&size=80"
                 alt="{{ Auth::user()->name }}" class="admin-avatar">
            <div style="min-width:0;">
                <div class="admin-name" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                    {{ Auth::user()->name }}
                </div>
                <span class="admin-role-badge"><i class="fas fa-crown" style="font-size:9px;"></i> Admin</span>
            </div>
        </div>
    </div>
</aside>

{{-- ════════ MAIN ════════ --}}
<div class="admin-main">

    {{-- Topbar --}}
    <div class="admin-topbar">
        <div class="topbar-left">
            <button class="sidebar-toggle"
                    onclick="document.getElementById('adminSidebar').classList.toggle('open')">
                <i class="fas fa-bars"></i>
            </button>
            <div>
                <div class="topbar-title">@yield('page-title', 'Dashboard')</div>
                @hasSection('breadcrumb')
                <div class="topbar-breadcrumb">@yield('breadcrumb')</div>
                @endif
            </div>
        </div>

        <div class="topbar-right">
            <div class="topbar-date">
                <i class="fas fa-calendar-alt" style="color:var(--primary);margin-right:5px;"></i>
                {{ now()->locale('vi')->translatedFormat('l, d/m/Y') }}
            </div>

            <a href="{{ url('/') }}" class="topbar-icon-btn" title="Xem trang cửa hàng" target="_blank">
                <i class="fas fa-external-link-alt" style="font-size:13px;"></i>
            </a>

            <form action="{{ url('/logout') }}" method="POST" style="margin:0;">
                @csrf
                <button type="submit" class="btn-logout-topbar">
                    <i class="fas fa-sign-out-alt"></i> Đăng xuất
                </button>
            </form>
        </div>
    </div>

    {{-- Content --}}
    <div class="admin-content">

        @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
        @endif
        @if(session('error'))
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        </div>
        @endif

        @yield('content')
    </div>
</div>

<script src="{{ asset('js/jquery.min.js') }}"></script>
<script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>

<script>
// Đóng sidebar khi click bên ngoài (mobile)
document.addEventListener('click', function(e) {
    const sidebar = document.getElementById('adminSidebar');
    const toggle  = document.querySelector('.sidebar-toggle');
    if (sidebar.classList.contains('open') && !sidebar.contains(e.target) && e.target !== toggle) {
        sidebar.classList.remove('open');
    }
});

// Confirm xóa
function confirmDelete(formId, msg) {
    if (confirm(msg || 'Bạn có chắc muốn xóa không?')) {
        document.getElementById(formId).submit();
    }
}
</script>

@yield('scripts')
</body>
</html>
