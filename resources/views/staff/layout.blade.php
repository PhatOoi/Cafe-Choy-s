<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Nhân viên') — Choy's Cafe</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">

    <style>
        :root {
            --primary: #d4813a;
            --primary-dark: #b8692a;
            --sidebar-w: 240px;
            --sidebar-bg: #1a1a2e;
            --sidebar-text: #c8c8d8;
            --sidebar-active: #d4813a;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f4f6fb;
            margin: 0;
            display: flex;
            min-height: 100vh;
        }

        /* ── Sidebar ── */
        .staff-sidebar {
            width: var(--sidebar-w);
            background: var(--sidebar-bg);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0; left: 0;
            height: 100vh;
            z-index: 100;
            transition: transform .25s ease;
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

        .nav-section-label {
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: rgba(255,255,255,.3);
            padding: 14px 20px 6px;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 20px;
            color: var(--sidebar-text);
            text-decoration: none;
            font-size: 14px;
            font-weight: 400;
            border-left: 3px solid transparent;
            transition: all .18s;
        }
        .sidebar-link:hover {
            background: rgba(255,255,255,.06);
            color: #fff;
            text-decoration: none;
        }
        .sidebar-link.active {
            background: rgba(212,129,58,.15);
            color: var(--primary);
            border-left-color: var(--primary);
            font-weight: 500;
        }
        .sidebar-link i { width: 18px; text-align: center; font-size: 15px; }

        .sidebar-footer {
            padding: 16px 20px;
            border-top: 1px solid rgba(255,255,255,.08);
        }
        .staff-info { display: flex; align-items: center; gap: 10px; }
        .staff-avatar {
            width: 38px; height: 38px; border-radius: 50%;
            object-fit: cover; border: 2px solid var(--primary);
        }
        .staff-name { font-size: 13px; color: #fff; font-weight: 500; }
        .staff-badge {
            font-size: 10px;
            background: var(--primary);
            color: #fff;
            padding: 1px 7px;
            border-radius: 10px;
            font-weight: 600;
        }

        /* ── Main content ── */
        .staff-main {
            margin-left: var(--sidebar-w);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .staff-topbar {
            background: #fff;
            border-bottom: 1px solid #e8ecf0;
            padding: 14px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .topbar-title { font-size: 18px; font-weight: 600; color: #1a1a2e; margin: 0; }
        .topbar-right { display: flex; align-items: center; gap: 14px; }

        .btn-logout {
            background: transparent;
            border: 1px solid #e0e0e0;
            padding: 6px 14px;
            border-radius: 8px;
            font-size: 13px;
            color: #666;
            cursor: pointer;
            transition: all .18s;
        }
        .btn-logout:hover { background: #fff0f0; border-color: #f55; color: #f55; }

        .staff-content { padding: 28px; flex: 1; }

        /* ── Cards ── */
        .card {
            background: #fff;
            border-radius: 12px;
            border: 1px solid #eef0f4;
            box-shadow: 0 1px 4px rgba(0,0,0,.05);
        }
        .card-header {
            padding: 16px 20px;
            border-bottom: 1px solid #f0f2f5;
            font-weight: 600;
            font-size: 15px;
            background: #fff;
            border-radius: 12px 12px 0 0;
        }
        .card-body { padding: 20px; }

        /* ── Stat cards ── */
        .stat-card {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            border: 1px solid #eef0f4;
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .stat-icon {
            width: 52px; height: 52px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px;
        }
        .stat-icon.orange { background: #fff4ec; color: var(--primary); }
        .stat-icon.blue   { background: #eef4ff; color: #4d7cfe; }
        .stat-icon.green  { background: #edfbf3; color: #27ae60; }
        .stat-icon.purple { background: #f3effe; color: #7c5cbf; }

        .stat-value { font-size: 28px; font-weight: 700; color: #1a1a2e; line-height: 1; }
        .stat-label { font-size: 13px; color: #8a8fa8; margin-top: 4px; }

        /* ── Status badges ── */
        .badge-status {
            font-size: 11px;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 20px;
            display: inline-block;
        }
        .badge-pending    { background: #fff8e5; color: #c09000; }
        .badge-confirmed  { background: #e5f6ff; color: #0077b6; }
        .badge-processing { background: #eef4ff; color: #4d7cfe; }
        .badge-ready      { background: #edfbf3; color: #27ae60; }
        .badge-delivering { background: #f0f0ff; color: #6366f1; }
        .badge-delivered  { background: #e5f6e5; color: #1a8a1a; }
        .badge-cancelled  { background: #f5f5f5; color: #888; }
        .badge-failed     { background: #fff0f0; color: #e53e3e; }

        /* ── Table ── */
        .staff-table { width: 100%; border-collapse: collapse; }
        .staff-table th {
            background: #f8f9fc;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: .5px;
            text-transform: uppercase;
            color: #8a8fa8;
            padding: 12px 16px;
            border-bottom: 1px solid #eef0f4;
            text-align: left;
        }
        .staff-table td {
            padding: 14px 16px;
            border-bottom: 1px solid #f3f4f8;
            font-size: 14px;
            vertical-align: middle;
        }
        .staff-table tr:last-child td { border-bottom: none; }
        .staff-table tr:hover td { background: #fafbfc; }

        /* ── Buttons ── */
        .btn-primary-staff {
            background: var(--primary);
            color: #fff;
            border: none;
            padding: 8px 18px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: background .18s;
        }
        .btn-primary-staff:hover { background: var(--primary-dark); color: #fff; text-decoration: none; }

        .btn-outline-staff {
            background: transparent;
            color: var(--primary);
            border: 1px solid var(--primary);
            padding: 7px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all .18s;
        }
        .btn-outline-staff:hover { background: var(--primary); color: #fff; text-decoration: none; }

        /* ── Alerts ── */
        .alert-staff {
            padding: 12px 18px;
            border-radius: 10px;
            font-size: 14px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .alert-success { background: #edfbf3; color: #1a6e35; border: 1px solid #b7eecb; }
        .alert-error   { background: #fff0f0; color: #b91c1c; border: 1px solid #fecaca; }

        /* ── Responsive sidebar toggle ── */
        .sidebar-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 22px;
            color: #1a1a2e;
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .staff-sidebar { transform: translateX(-100%); }
            .staff-sidebar.open { transform: translateX(0); }
            .staff-main { margin-left: 0; }
            .sidebar-toggle { display: block; }
        }
    </style>

    @yield('styles')
</head>
<body>

{{-- ───── Sidebar ───── --}}
<aside class="staff-sidebar" id="staffSidebar">
    <div class="sidebar-brand">
        <img src="{{ asset('images/logo.png') }}" alt="Choy's Cafe" onerror="this.style.display='none'">
        <p>Bảng điều khiển nhân viên</p>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section-label">Tổng quan</div>
        <a href="{{ route('staff.dashboard') }}"
           class="sidebar-link {{ request()->routeIs('staff.dashboard') ? 'active' : '' }}">
            <i class="fas fa-th-large"></i> Dashboard
        </a>

        <div class="nav-section-label">Đơn hàng</div>
        <a href="{{ route('staff.orders') }}"
           class="sidebar-link {{ request()->routeIs('staff.orders') ? 'active' : '' }}">
            <i class="fas fa-clipboard-list"></i> Danh sách đơn
        </a>
        <a href="{{ route('staff.orders', ['status' => 'pending']) }}"
           class="sidebar-link {{ request()->is('staff/orders') && request('status') === 'pending' ? 'active' : '' }}">
            <i class="fas fa-clock"></i> Chờ xác nhận
        </a>
        <a href="{{ route('staff.orders', ['status' => 'delivering']) }}"
           class="sidebar-link">
            <i class="fas fa-motorcycle"></i> Đang giao
        </a>
        <a href="{{ route('staff.create-order') }}"
           class="sidebar-link {{ request()->routeIs('staff.create-order') ? 'active' : '' }}">
            <i class="fas fa-plus-circle"></i> Tạo đơn tại quán
        </a>

        <div class="nav-section-label">Tài khoản</div>
        <a href="{{ url('/profile') }}" class="sidebar-link">
            <i class="fas fa-user"></i> Hồ sơ cá nhân
        </a>
        <a href="{{ url('/') }}" class="sidebar-link">
            <i class="fas fa-store"></i> Về trang chủ
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="staff-info">
            <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=d4813a&color=fff&size=80"
                 alt="{{ Auth::user()->name }}" class="staff-avatar">
            <div>
                <div class="staff-name">{{ Auth::user()->name }}</div>
                <span class="staff-badge">
                    {{ Auth::user()->role_id === 1 ? 'Admin' : 'Nhân viên' }}
                </span>
            </div>
        </div>
    </div>
</aside>

{{-- ───── Main ───── --}}
<div class="staff-main">
    <div class="staff-topbar">
        <div style="display:flex;align-items:center;gap:14px;">
            <button class="sidebar-toggle" onclick="document.getElementById('staffSidebar').classList.toggle('open')">
                <i class="fas fa-bars"></i>
            </button>
            <h1 class="topbar-title">@yield('page-title', 'Dashboard')</h1>
        </div>
        <div class="topbar-right">
            <span style="font-size:13px;color:#8a8fa8;">
                {{ now()->format('d/m/Y H:i') }}
            </span>
            <form action="{{ url('/logout') }}" method="POST" style="margin:0;">
                @csrf
                <button type="submit" class="btn-logout">
                    <i class="fas fa-sign-out-alt"></i> Đăng xuất
                </button>
            </form>
        </div>
    </div>

    <div class="staff-content">
        {{-- Flash messages --}}
        @if(session('success'))
            <div class="alert-staff alert-success">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert-staff alert-error">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </div>
</div>

<script src="{{ asset('js/jquery.min.js') }}"></script>
<script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
@yield('scripts')
</body>
</html>
