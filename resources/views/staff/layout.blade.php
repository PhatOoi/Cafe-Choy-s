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

        .btn-audio-reminder {
            border: 1px solid #e7c79a;
            background: #fff8ef;
            color: #9a5d1a;
            padding: 7px 14px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all .18s;
        }
        .btn-audio-reminder:hover {
            background: #ffe9cd;
            border-color: #d9a86a;
        }
        .btn-audio-reminder.active {
            background: #edfbf3;
            border-color: #9dd3ae;
            color: #1c7c45;
        }

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

        .global-back-btn {
            position: fixed;
            left: 22px;
            bottom: 22px;
            width: 46px;
            height: 46px;
            border: none;
            border-radius: 999px;
            background: linear-gradient(135deg, #d4813a, #b8692a);
            color: #fff;
            box-shadow: 0 10px 24px rgba(212, 129, 58, .35);
            font-size: 18px;
            z-index: 1200;
            cursor: pointer;
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .global-back-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 28px rgba(212, 129, 58, .42);
        }

        .global-back-btn:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(212, 129, 58, .25), 0 10px 24px rgba(212, 129, 58, .35);
        }

        @media (max-width: 768px) {
            .staff-sidebar { transform: translateX(-100%); }
            .staff-sidebar.open { transform: translateX(0); }
            .staff-main { margin-left: 0; }
            .sidebar-toggle { display: block; }
            .global-back-btn { left: 14px; bottom: 14px; width: 42px; height: 42px; }
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
        <a href="{{ route('staff.work-schedules.index') }}"
           class="sidebar-link {{ request()->routeIs('staff.work-schedules.*') ? 'active' : '' }}">
            <i class="fas fa-calendar-alt"></i> Đăng ký giờ làm
        </a>

        <div class="nav-section-label">Đơn hàng</div>
        <a href="{{ route('staff.orders') }}"
           class="sidebar-link {{ request()->routeIs('staff.orders') ? 'active' : '' }}">
            <i class="fas fa-clipboard-list"></i> Danh sách đơn
        </a>
        <a href="{{ route('staff.create-order') }}"
           class="sidebar-link {{ request()->routeIs('staff.create-order') ? 'active' : '' }}">
            <i class="fas fa-plus-circle"></i> Tạo đơn tại quán
        </a>
        <a href="{{ route('staff.order-history') }}"
           class="sidebar-link {{ request()->routeIs('staff.order-history') ? 'active' : '' }}">
            <i class="fas fa-history"></i> Lịch sử đơn
        </a>
        <a href="{{ route('staff.revenue.daily') }}"
           class="sidebar-link {{ request()->routeIs('staff.revenue.daily') ? 'active' : '' }}">
                <i class="fas fa-calendar-day"></i> Doanh thu ngày
        </a>

        <div class="nav-section-label">Hỗ trợ</div>
        <a href="{{ route('staff.support') }}"
           class="sidebar-link {{ request()->routeIs('staff.support') ? 'active' : '' }}"
           style="position:relative;">
            <i class="fas fa-headset"></i> Chat khách hàng
            <span id="sidebarUnread" style="display:none;background:#d4813a;color:#fff;border-radius:10px;
                padding:1px 7px;font-size:10px;font-weight:700;position:absolute;right:14px;"></span>
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
            <button type="button" class="btn-audio-reminder" id="enableReminderAudioButton">
                <i class="fas fa-volume-up"></i> Bật âm thanh nhắc
            </button>
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

<button type="button" class="global-back-btn" id="globalBackBtn" aria-label="Lên đầu trang" title="Lên đầu trang">
    <i class="fas fa-arrow-up"></i>
</button>

<script src="{{ asset('js/jquery.min.js') }}"></script>
<script src="{{ asset('js/popper.min.js') }}"></script>
<script src="{{ asset('js/bootstrap.min.js') }}"></script>
<script>
(() => {
    const backBtn = document.getElementById('globalBackBtn');
    if (!backBtn) return;

    backBtn.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
})();
</script>
<script>
(() => {
    const storageKey = 'staffOrderStatusReminders';
    const audioMutedStorageKey = 'staffOrderReminderAudioMuted';
    const reminderStatuses = ['pending', 'confirmed', 'processing', 'ready'];
    const reminderDelay = 7000;
    const confirmedIdsUrl = @json(route('staff.orders.confirmed-reminder-ids'));
    const fetchUrl = @json(route('staff.orders.reminder-statuses'));
    const soundUrl = @json(asset('audio/order-reminder.wav'));
    const flashedStartReminderId = @json(session('start_order_reminder_id'));
    const flashedClearReminderId = @json(session('clear_order_reminder_id'));
    let audioContext = null;
    let htmlAudio = null;
    let pollInFlight = false;
    let fallbackLoopTimer = null;
    let audioUnlocked = sessionStorage.getItem('staffReminderAudioUnlocked') === '1';
    let audioMuted = localStorage.getItem(audioMutedStorageKey) === '1';

    function readReminders() {
        try {
            const raw = localStorage.getItem(storageKey);
            if (!raw) {
                return {};
            }

            const parsed = JSON.parse(raw);
            return parsed && typeof parsed === 'object' ? parsed : {};
        } catch (error) {
            return {};
        }
    }

    function writeReminders(reminders) {
        localStorage.setItem(storageKey, JSON.stringify(reminders));
    }

    function markAudioUnlocked() {
        audioUnlocked = true;
        sessionStorage.setItem('staffReminderAudioUnlocked', '1');
        updateReminderAudioButton();
    }

    function setAudioMuted(nextMuted) {
        audioMuted = nextMuted;
        localStorage.setItem(audioMutedStorageKey, nextMuted ? '1' : '0');

        if (audioMuted) {
            stopReminderSound();
        }

        updateReminderAudioButton();
    }

    function updateReminderAudioButton() {
        const button = document.getElementById('enableReminderAudioButton');
        if (!button) {
            return;
        }

        if (audioMuted) {
            button.classList.remove('active');
            button.innerHTML = '<i class="fas fa-volume-mute"></i> Âm thanh: Tắt';
            return;
        }

        if (audioUnlocked) {
            button.classList.add('active');
            button.innerHTML = '<i class="fas fa-volume-up"></i> Âm thanh: Bật';
            return;
        }

        button.classList.remove('active');
        button.innerHTML = '<i class="fas fa-volume-up"></i> Bật âm thanh nhắc';
    }

    function clearReminder(orderId) {
        const reminders = readReminders();
        delete reminders[String(orderId)];
        writeReminders(reminders);
    }

    function setReminder(orderId) {
        const reminders = readReminders();
        reminders[String(orderId)] = {
            dueAt: Date.now() + reminderDelay,
            lastNotifiedAt: 0,
        };
        writeReminders(reminders);
    }

    function registerVisibleConfirmedOrders() {
        const reminders = readReminders();
        const forms = document.querySelectorAll('[data-status-reminder-form="true"]');

        forms.forEach((form) => {
            const orderId = form.dataset.orderId;
            if (!orderId || reminders[String(orderId)]) {
                return;
            }

            reminders[String(orderId)] = {
                dueAt: Date.now() + reminderDelay,
                lastNotifiedAt: 0,
            };
        });

        writeReminders(reminders);
    }

    async function syncConfirmedOrdersFromServer() {
        const response = await fetch(confirmedIdsUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        });

        if (!response.ok) {
            return readReminders();
        }

        const data = await response.json();
        const confirmedIds = Array.isArray(data.ids) ? data.ids.map((id) => String(id)) : [];
        const confirmedIdSet = new Set(confirmedIds);
        const reminders = readReminders();

        confirmedIds.forEach((id) => {
            if (!reminders[id]) {
                reminders[id] = {
                    dueAt: Date.now() + reminderDelay,
                    lastNotifiedAt: 0,
                };
            }
        });

        Object.keys(reminders).forEach((id) => {
            if (!confirmedIdSet.has(id)) {
                delete reminders[id];
            }
        });

        writeReminders(reminders);
        return reminders;
    }

    function acknowledgeReminders(orderIds) {
        const reminders = readReminders();
        orderIds.forEach((orderId) => {
            if (reminders[String(orderId)]) {
                reminders[String(orderId)].dueAt = Date.now() + reminderDelay;
                reminders[String(orderId)].lastNotifiedAt = 0;
            }
        });
        writeReminders(reminders);
        stopReminderSound();
        hideReminderBanner();
    }

    function getDueReminderOrderIds() {
        const reminders = readReminders();
        const nowAt = Date.now();

        return Object.keys(reminders).filter((id) => {
            const reminder = reminders[id];
            return reminder && nowAt >= reminder.dueAt;
        });
    }

    function getAudioContext() {
        const AudioCtor = window.AudioContext || window.webkitAudioContext;
        if (!AudioCtor) {
            return null;
        }

        if (!audioContext) {
            audioContext = new AudioCtor();
        }

        return audioContext;
    }

    function getHtmlAudio() {
        if (!htmlAudio) {
            htmlAudio = new Audio(soundUrl);
            htmlAudio.preload = 'auto';
        }

        return htmlAudio;
    }

    async function primeAudio() {
        const audio = getHtmlAudio();
        try {
            audio.muted = true;
            audio.currentTime = 0;
            await audio.play();
            audio.pause();
            audio.currentTime = 0;
            audio.muted = false;
            markAudioUnlocked();
        } catch (error) {
            audio.muted = false;
        }

        const context = getAudioContext();
        if (!context) {
            return;
        }

        try {
            if (context.state === 'suspended') {
                await context.resume();
            }
            markAudioUnlocked();
        } catch (error) {
            return;
        }
    }

    async function playReminderSound() {
        if (audioMuted) {
            return;
        }

        const audio = getHtmlAudio();
        try {
            audio.pause();
            audio.currentTime = 0;
            audio.loop = false;
            await audio.play();
            return;
        } catch (error) {
            // Fall back to generated beep if file playback is blocked.
        }

        const context = getAudioContext();
        if (!context) {
            return;
        }

        try {
            if (context.state === 'suspended') {
                await context.resume();
            }
        } catch (error) {
            return;
        }

        const startAt = context.currentTime;
        [0, 0.32].forEach((offset) => {
            const oscillator = context.createOscillator();
            const gain = context.createGain();

            oscillator.type = 'sine';
            oscillator.frequency.setValueAtTime(880, startAt + offset);

            gain.gain.setValueAtTime(0.0001, startAt + offset);
            gain.gain.exponentialRampToValueAtTime(0.18, startAt + offset + 0.02);
            gain.gain.exponentialRampToValueAtTime(0.0001, startAt + offset + 0.22);

            oscillator.connect(gain);
            gain.connect(context.destination);
            oscillator.start(startAt + offset);
            oscillator.stop(startAt + offset + 0.24);
        });
    }

    async function startReminderSoundLoop() {
        if (audioMuted) {
            stopReminderSound();
            return;
        }

        const audio = getHtmlAudio();
        try {
            audio.loop = true;
            if (audio.paused) {
                audio.currentTime = 0;
                await audio.play();
            }
            markAudioUnlocked();
            stopFallbackLoop();
            return;
        } catch (error) {
            startFallbackLoop();
        }
    }

    function stopReminderSound() {
        const audio = getHtmlAudio();
        audio.pause();
        audio.currentTime = 0;
        audio.loop = false;
        stopFallbackLoop();
    }

    function startFallbackLoop() {
        if (fallbackLoopTimer) {
            return;
        }

        playReminderSound();
        fallbackLoopTimer = window.setInterval(() => {
            playReminderSound();
        }, 1800);
    }

    function stopFallbackLoop() {
        if (!fallbackLoopTimer) {
            return;
        }

        window.clearInterval(fallbackLoopTimer);
        fallbackLoopTimer = null;
    }

    function buildReminderGroups(orderIds, statuses) {
        const groupedOrderIds = orderIds.reduce((groups, id) => {
            const statusInfo = statuses[id];
            const status = typeof statusInfo === 'string' ? statusInfo : statusInfo?.status;

            if (!reminderStatuses.includes(status)) {
                return groups;
            }

            if (!groups[status]) {
                groups[status] = [];
            }

            groups[status].push(id);
            return groups;
        }, {});

        return reminderStatuses
            .filter((status) => Array.isArray(groupedOrderIds[status]) && groupedOrderIds[status].length)
            .map((status) => ({
                status,
                orderIds: groupedOrderIds[status],
                ...getReminderContent(status, groupedOrderIds[status]),
            }));
    }

    function getReminderContent(status, orderIds) {
        const formattedIds = orderIds.join(', #');

        return {
            pending: {
                title: 'Nhắc xác nhận đơn hàng',
                body: 'Đơn #' + formattedIds + ' đang chờ xác nhận từ nhân viên.',
                note: 'Vui lòng kiểm tra và xác nhận đơn mới để tiếp tục xử lý.',
            },
            confirmed: {
                title: 'Nhắc bắt đầu chuẩn bị',
                body: 'Đơn #' + formattedIds + ' đã được xác nhận và cần chuyển sang Đang chuẩn bị.',
                note: 'Hãy cập nhật trạng thái khi bạn bắt đầu thực hiện đơn hàng.',
            },
            processing: {
                title: 'Nhắc chuyển sang Sẵn sàng',
                body: 'Đơn #' + formattedIds + ' đã sẵn sàng giao đến khách hàng.',
                note: 'Nếu đã chuẩn bị xong, vui lòng cập nhật trạng thái sang Sẵn sàng.',
            },
            ready: {
                title: 'Nhắc hoàn thành đơn hàng',
                body: 'Đơn #' + formattedIds + ' đang ở trạng thái Sẵn sàng.',
                note: 'Vui lòng xác nhận hoàn thành đơn hàng.',
            },
        }[status] || {
            title: 'Nhắc cập nhật đơn hàng',
            body: 'Đơn #' + formattedIds + ' cần được cập nhật trạng thái.',
            note: 'Vui lòng kiểm tra và xử lý đơn hàng.',
        };
    }

    function showBrowserNotification(reminderGroups) {
        if (!('Notification' in window) || Notification.permission !== 'granted') {
            return;
        }

        const message = reminderGroups.length === 1
            ? reminderGroups[0].body
            : 'Có ' + reminderGroups.reduce((sum, group) => sum + group.orderIds.length, 0) + ' đơn hàng đang cần cập nhật trạng thái.';

        const notification = new Notification('Nhắc chuẩn bị đơn', {
            body: message,
            tag: 'staff-order-reminder',
            renotify: true,
            requireInteraction: true,
        });

        notification.onclick = () => {
            window.focus();
            notification.close();
        };
    }

    function showReminderBanner(reminderGroups) {
        const allOrderIds = reminderGroups.flatMap((group) => group.orderIds);
        let banner = document.getElementById('staffReminderBanner');

        if (!banner) {
            banner = document.createElement('div');
            banner.id = 'staffReminderBanner';
            banner.style.position = 'fixed';
            banner.style.right = '24px';
            banner.style.bottom = '24px';
            banner.style.zIndex = '9999';
            banner.style.width = 'min(360px, calc(100vw - 32px))';
            banner.style.background = 'linear-gradient(135deg, #fff2d9, #ffffff)';
            banner.style.color = '#6d4b00';
            banner.style.border = '1px solid #efc87a';
            banner.style.borderRadius = '16px';
            banner.style.padding = '16px 18px';
            banner.style.boxShadow = '0 14px 36px rgba(0,0,0,.16)';
            banner.style.fontSize = '13px';
            banner.style.fontWeight = '600';
            banner.style.display = 'flex';
            banner.style.gap = '12px';
            banner.style.alignItems = 'flex-start';
            banner.style.animation = 'staffReminderPulse 1s ease-in-out infinite alternate';
            document.body.appendChild(banner);

            if (!document.getElementById('staffReminderStyles')) {
                const style = document.createElement('style');
                style.id = 'staffReminderStyles';
                style.textContent = '@keyframes staffReminderPulse { from { transform: translateY(0); box-shadow: 0 14px 36px rgba(0,0,0,.16); } to { transform: translateY(-3px); box-shadow: 0 18px 42px rgba(212,129,58,.28); } }';
                document.head.appendChild(style);
            }
        }

        banner.innerHTML = '';

        const icon = document.createElement('div');
        icon.textContent = '🔔';
        icon.style.fontSize = '24px';
        icon.style.lineHeight = '1';
        icon.style.marginTop = '2px';

        const content = document.createElement('div');
        content.style.flex = '1';

        const title = document.createElement('div');
        title.textContent = 'Nhắc cập nhật trạng thái đơn';
        title.style.fontSize = '15px';
        title.style.fontWeight = '700';
        title.style.marginBottom = '8px';

        const messageList = document.createElement('div');
        messageList.style.display = 'flex';
        messageList.style.flexDirection = 'column';
        messageList.style.gap = '10px';

        reminderGroups.forEach((group, index) => {
            const messageItem = document.createElement('div');
            if (index < reminderGroups.length - 1) {
                messageItem.style.paddingBottom = '10px';
                messageItem.style.borderBottom = '1px dashed rgba(155, 109, 0, 0.25)';
            }

            const messageTitle = document.createElement('div');
            messageTitle.textContent = group.title;
            messageTitle.style.fontSize = '13px';
            messageTitle.style.fontWeight = '700';

            const messageBody = document.createElement('div');
            messageBody.textContent = group.body;
            messageBody.style.marginTop = '4px';
            messageBody.style.lineHeight = '1.45';

            const messageNote = document.createElement('div');
            messageNote.textContent = group.note;
            messageNote.style.marginTop = '6px';
            messageNote.style.fontSize = '12px';
            messageNote.style.color = '#9b6d00';

            messageItem.appendChild(messageTitle);
            messageItem.appendChild(messageBody);
            messageItem.appendChild(messageNote);
            messageList.appendChild(messageItem);
        });

        const actions = document.createElement('div');
        actions.style.marginTop = '12px';
        actions.style.display = 'flex';
        actions.style.justifyContent = 'flex-end';
        actions.style.gap = '8px';

        if (audioMuted || !audioUnlocked) {
            const enableAudioButton = document.createElement('button');
            enableAudioButton.type = 'button';
            enableAudioButton.textContent = audioMuted ? 'Bật lại âm thanh' : 'Bật âm thanh nhắc';
            enableAudioButton.style.border = '1px solid #d9b067';
            enableAudioButton.style.borderRadius = '10px';
            enableAudioButton.style.padding = '8px 14px';
            enableAudioButton.style.background = '#fff';
            enableAudioButton.style.color = '#8a5a00';
            enableAudioButton.style.fontWeight = '700';
            enableAudioButton.style.cursor = 'pointer';
            enableAudioButton.addEventListener('click', async () => {
                setAudioMuted(false);
                await primeAudio();
                await startReminderSoundLoop();
                showReminderBanner(reminderGroups);
            });
            actions.appendChild(enableAudioButton);
        }

        const acknowledgeButton = document.createElement('button');
        acknowledgeButton.type = 'button';
        acknowledgeButton.textContent = 'Đã biết';
        acknowledgeButton.style.border = 'none';
        acknowledgeButton.style.borderRadius = '10px';
        acknowledgeButton.style.padding = '8px 14px';
        acknowledgeButton.style.background = '#d4813a';
        acknowledgeButton.style.color = '#fff';
        acknowledgeButton.style.fontWeight = '700';
        acknowledgeButton.style.cursor = 'pointer';
        acknowledgeButton.addEventListener('click', () => acknowledgeReminders(allOrderIds));

        actions.appendChild(acknowledgeButton);

        content.appendChild(title);
        content.appendChild(messageList);
        content.appendChild(actions);
        banner.appendChild(icon);
        banner.appendChild(content);
    }

    function hideReminderBanner() {
        const banner = document.getElementById('staffReminderBanner');
        if (banner) {
            banner.remove();
        }
    }

    async function checkReminders() {
        if (pollInFlight) {
            return;
        }

        pollInFlight = true;

        try {
            const reminders = await syncConfirmedOrdersFromServer();
            const ids = Object.keys(reminders);

            if (!ids.length) {
                hideReminderBanner();
                stopReminderSound();
                return;
            }

            const params = new URLSearchParams();
            ids.forEach((id) => params.append('ids[]', id));

            const response = await fetch(fetchUrl + '?' + params.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });

            if (!response.ok) {
                return;
            }

            const data = await response.json();
            const statuses = data.orders || {};
            const updatedReminders = readReminders();
            const dueOrderIds = [];
            const nowAt = Date.now();
            let shouldNotify = false;

            Object.keys(updatedReminders).forEach((id) => {
                const statusInfo = statuses[id];
                const status = typeof statusInfo === 'string' ? statusInfo : statusInfo?.status;
                if (!reminderStatuses.includes(status)) {
                    delete updatedReminders[id];
                    return;
                }

                const reminder = updatedReminders[id];
                if (nowAt >= reminder.dueAt) {
                    dueOrderIds.push(id);
                    if (!reminder.lastNotifiedAt) {
                        reminder.lastNotifiedAt = nowAt;
                        shouldNotify = true;
                    }
                }
            });

            writeReminders(updatedReminders);

            if (dueOrderIds.length) {
                const reminderGroups = buildReminderGroups(dueOrderIds, statuses);
                if (!reminderGroups.length) {
                    hideReminderBanner();
                    stopReminderSound();
                    return;
                }

                showReminderBanner(reminderGroups);
                startReminderSoundLoop();
                if (shouldNotify) {
                    showBrowserNotification(reminderGroups);
                }
            } else {
                hideReminderBanner();
                stopReminderSound();
            }
        } catch (error) {
            // Keep silent on polling errors to avoid interrupting staff workflow.
        } finally {
            pollInFlight = false;
        }
    }

    document.addEventListener('submit', (event) => {
        const form = event.target.closest('[data-status-reminder-form="true"]');
        if (!form) {
            return;
        }

        const orderId = form.dataset.orderId;
        const nextStatus = form.dataset.nextStatus;

        if (!orderId || !nextStatus) {
            return;
        }

        if (['confirmed', 'processing', 'ready'].includes(nextStatus)) {
            setReminder(orderId);
            return;
        }

        if (['delivered', 'cancelled', 'failed'].includes(nextStatus)) {
            clearReminder(orderId);
        }
    });

    const enableReminderAudioButton = document.getElementById('enableReminderAudioButton');
    if (enableReminderAudioButton) {
        enableReminderAudioButton.addEventListener('click', async () => {
            if (!audioMuted) {
                setAudioMuted(true);
                return;
            }

            setAudioMuted(false);
            await primeAudio();
            if (getDueReminderOrderIds().length) {
                await startReminderSoundLoop();
            }
            updateReminderAudioButton();
        });
    }

    document.addEventListener('pointerdown', primeAudio, { passive: true });
    document.addEventListener('keydown', primeAudio);

    if ('Notification' in window && Notification.permission === 'default') {
        document.addEventListener('pointerdown', () => Notification.requestPermission(), { once: true, passive: true });
    }

    if (flashedClearReminderId) {
        clearReminder(flashedClearReminderId);
    }

    if (flashedStartReminderId) {
        setReminder(flashedStartReminderId);
    }

    registerVisibleConfirmedOrders();
    updateReminderAudioButton();
    checkReminders();
    window.setInterval(checkReminders, 1000);
})();
</script>
@yield('scripts')

<script>
// Badge số tin nhắn chưa đọc trên sidebar
(function pollUnread() {
    fetch('{{ route("staff.chat.unread") }}', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    .then(r => r.json())
    .then(function(data) {
        var badge = document.getElementById('sidebarUnread');
        if (!badge) return;
        if (data.count > 0) {
            badge.style.display = 'inline-flex';
            badge.textContent = data.count;
        } else {
            badge.style.display = 'none';
        }
    })
    .catch(function(){});
    setTimeout(pollUnread, 6000);
})();
</script>
</body>
</html>
