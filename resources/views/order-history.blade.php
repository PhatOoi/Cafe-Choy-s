<!DOCTYPE html>
<html lang="vi">

<head>
    <title>Lịch Sử Đơn Hàng - Choy's Cafe</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Josefin+Sans:400,700" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Great+Vibes" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/open-iconic-bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/animate.css') }}">
    <link rel="stylesheet" href="{{ asset('css/owl.carousel.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/owl.theme.default.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/magnific-popup.css') }}">
    <link rel="stylesheet" href="{{ asset('css/aos.css') }}">
    <link rel="stylesheet" href="{{ asset('css/ionicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/jquery.timepicker.css') }}">
    <link rel="stylesheet" href="{{ asset('css/flaticon.css') }}">
    <link rel="stylesheet" href="{{ asset('css/icomoon.css') }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark ftco_navbar bg-dark ftco-navbar-light" id="ftco-navbar">
        <div class="container">
            <a class="navbar-brand mr-3" href="{{ url('/') }}">
                <img src="/images/logo.png" style="height:72px;width:auto;object-fit:contain;">
            </a>

            @include('components.search-bar')

            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#ftco-nav">
                <span class="oi oi-menu"></span> Menu
            </button>

            <div class="collapse navbar-collapse" id="ftco-nav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item"><a href="{{ url('/') }}" class="nav-link">Trang chủ</a></li>
                    @if(!(auth()->check() && auth()->user()->isStaff()))
                        <li class="nav-item"><a href="{{ url('/menu') }}" class="nav-link">Menu</a></li>
                    @endif
                    @auth
                        @if(!auth()->user()->isStaff())
                            <li class="nav-item active"><a href="{{ route('orders.history') }}" class="nav-link">Lịch sử đơn hàng</a></li>
                        @endif
                    @endauth
                    @guest
                        <li class="nav-item"><a href="{{ url('/login') }}" class="nav-link">Đăng nhập</a></li>
                    @endguest
                    <li class="nav-item"><a href="{{ route('support') }}" class="nav-link">Hỗ trợ</a></li>
                    <li class="nav-item flex-spacer"></li>
                    <li class="nav-item cart">
                        <a href="/cart" class="nav-link">
                            <span class="icon icon-shopping_cart"></span>
                            <span class="bag"><small id="cart-count">{{ $cartCount ?? 0 }}</small></span>
                        </a>
                    </li>

                    @if(Auth::check())
                        <li class="nav-item user-dropdown-wrapper">
                            <div class="user-dropdown-container">
                                <button class="user-avatar-btn" type="button" id="userMenuBtn">
                                    @if(Auth::user()->avatar_url)
                                        <img src="{{ asset('storage/' . Auth::user()->avatar_url) }}" class="user-avatar">
                                    @else
                                        <img src="{{ asset('images/user.jpg') }}" class="user-avatar">
                                    @endif
                                </button>

                                <div class="user-dropdown-menu" id="userDropdownMenu">
                                    <div class="dropdown-header-info">
                                        <img src="{{ Auth::user()->avatar_url ? asset('storage/' . Auth::user()->avatar_url) : asset('images/user.jpg') }}" class="dropdown-avatar">

                                        <div class="user-details">
                                            <p class="user-name">{{ Auth::user()->name }}</p>
                                            <p class="user-role">
                                                @if(Auth::user()->role === 'admin')
                                                    <span class="badge-admin">Admin</span>
                                                @else
                                                    <span class="badge-customer">Khách hàng</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>

                                    <div class="dropdown-divider"></div>
                                    <a href="/profile" class="dropdown-link">Hồ sơ</a>

                                    @if(Auth::user()->role === 'admin')
                                        <a href="/admin" class="dropdown-link">Quản trị</a>
                                    @endif

                                    <div class="dropdown-divider"></div>

                                    <form id="logout-form" action="{{ url('/logout') }}" method="POST">
                                        @csrf
                                    </form>

                                    <a href="#" class="dropdown-link logout-link" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        Đăng xuất
                                    </a>
                                </div>
                            </div>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>

    <section class="ftco-section order-history-section">
        <div class="container order-history-container">
            <div class="history-hero text-center">
                <span class="history-kicker">Theo dõi đơn của bạn</span>
                <h1>Lịch sử đơn hàng</h1>
                <p>Mọi đơn đã thanh toán đều được lưu lại để bạn xem nhanh món đã đặt, topping và thời gian mua.</p>
            </div>

            @if(session('success'))
                <div class="history-alert history-alert-success">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="history-alert history-alert-error">{{ session('error') }}</div>
            @endif

            @php
                $statusLabels = [
                    'pending' => 'Chờ xử lý',
                    'confirmed' => 'Đã xác nhận',
                    'processing' => 'Đang chuẩn bị',
                    'ready' => 'Sẵn sàng',
                    'delivered' => 'Hoàn tất',
                    'failed' => 'Thất bại',
                    'cancelled' => 'Đã hủy',
                ];
            @endphp

            @if($orders->isEmpty())
                <div class="history-empty text-center">
                    <div class="history-empty-icon"><i class="fas fa-receipt"></i></div>
                    <h3>Chưa có đơn hàng nào</h3>
                    <p>Khi bạn thanh toán thành công, đơn hàng sẽ xuất hiện tại đây.</p>
                    <a href="{{ url('/menu') }}" class="btn btn-primary history-cta">Đặt món ngay</a>
                </div>
            @else
                <div class="history-summary-grid">
                    <div class="history-summary-card">
                        <span>Tổng số đơn</span>
                        <strong>{{ $orders->count() }}</strong>
                    </div>
                    <div class="history-summary-card">
                        <span>Đơn gần nhất</span>
                        <strong>{{ optional($orders->first()->created_at)->format('d/m/Y') }}</strong>
                    </div>
                    <div class="history-summary-card">
                        <span>Tổng chi tiêu</span>
                        <strong>{{ number_format((float) $orders->where('status', 'delivered')->sum('final_price'), 0, ',', '.') }} đ</strong>
                    </div>
                </div>

                <div class="order-history-list">
                    @foreach($orders as $order)
                        <article class="order-history-card">
                            <div class="order-history-top">
                                <div>
                                    <p class="order-code">Đơn #{{ str_pad((string) $order->id, 6, '0', STR_PAD_LEFT) }}</p>
                                    <p class="order-date">{{ optional($order->created_at)->format('d/m/Y H:i') }}</p>
                                </div>
                                <span class="order-status status-{{ $order->status }}">{{ $statusLabels[$order->status] ?? ucfirst($order->status) }}</span>
                            </div>

                            <div class="order-meta-grid">
                                <div class="order-meta-item">
                                    <span>Thanh toán</span>
                                    <strong>{{ number_format((float) $order->final_price, 0, ',', '.') }} đ</strong>
                                </div>
                                <div class="order-meta-item">
                                    <span>Hình thức</span>
                                    <strong>{{ $order->order_type === 'in_store' ? 'Tại quán' : 'Giao hàng' }}</strong>
                                </div>
                                <div class="order-meta-item">
                                    <span>Số món</span>
                                    <strong>{{ $order->items->sum('quantity') }}</strong>
                                </div>
                            </div>

                            <div class="order-item-list">
                                @foreach($order->items as $item)
                                    <div class="order-item-row">
                                        <div class="order-item-main">
                                            <p class="order-item-title">{{ optional($item->product)->name ?? 'Sản phẩm đã xóa' }}</p>
                                            <div class="order-item-notes">
                                                @if($item->note)
                                                    <p>{{ $item->note }}</p>
                                                @endif
                                                @if($item->extras->isNotEmpty())
                                                    <p>Topping: {{ $item->extras->pluck('pivot.extra_name')->implode(', ') }}</p>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="order-item-side">
                                            <span>{{ $item->quantity }} x {{ number_format((float) $item->unit_price, 0, ',', '.') }} đ</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            @if($order->canCustomerCancel())
                                <div class="order-action-row">
                                    <form action="{{ route('orders.cancel', $order->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn hủy đơn #{{ str_pad((string) $order->id, 6, '0', STR_PAD_LEFT) }}?')">
                                        @csrf
                                        <button type="submit" class="order-cancel-btn">
                                            <i class="fas fa-times-circle"></i> Hủy đơn hàng
                                        </button>
                                    </form>
                                    <span class="order-action-note">Bạn chỉ có thể hủy trước khi quán bắt đầu chuẩn bị đơn.</span>
                                </div>
                            @endif
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <footer class="coffee-footer">
        <div class="main-footer">
            <div class="container">
                <div class="footer-grid">
                    <div class="footer-brand">
                        <div class="brand-header"></div>
                        <h2 class="footer-logo">
                            <img src="/images/logo.png" alt="logo">Choy's Cafe
                        </h2>
                        <p> Hân hạnh đồng hành cùng quý khách!.</p>
                        <div class="social-links">
                            <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                            <a href="#" aria-label="TikTok"><i class="fab fa-tiktok"></i></a>
                        </div>
                    </div>

                    <div class="footer-links">
                        <h4>Khám phá</h4>
                        <ul>
                            <li><a href="#">Menu</a></li>
                            <li><a href="#">Cửa hàng</a></li>
                            <li><a href="#">Đặt hàng online</a></li>
                        </ul>
                    </div>

                    <div class="footer-links">
                        <h4>Dịch vụ</h4>
                        <ul>
                            <li><a href="#">Ship tận nơi</a></li>
                            <li><a href="#">Catering</a></li>
                            <li><a href="#">Thẻ thành viên</a></li>
                        </ul>
                    </div>

                    <div class="footer-contact">
                        <h4>Liên hệ</h4>
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <span>+190099</span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-clock"></i>
                            <span>8:00 - 21:00</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="copyright">
            <div class="container">
                <p>&copy; 2026 Choy's Cafe. Tất cả quyền được bảo lưu.</p>
            </div>
        </div>
    </footer>

    <style>
        .order-history-section {
            padding: 110px 0 90px;
        }

        .order-history-container {
            max-width: 1220px;
        }

        .history-hero {
            max-width: 760px;
            margin: 0 auto 48px;
        }

        .history-kicker {
            display: inline-block;
            margin-bottom: 14px;
            color: #c49b63;
            letter-spacing: 0.24em;
            text-transform: uppercase;
            font-size: 12px;
            font-weight: 600;
        }

        .history-hero h1 {
            font-size: 52px;
            color: #fff;
            margin-bottom: 16px;
            font-weight: 700;
        }

        .history-hero p {
            color: rgba(255, 255, 255, 0.72);
            font-size: 16px;
            line-height: 1.8;
            margin: 0;
        }

        .history-summary-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
            margin-bottom: 28px;
        }

        .history-alert {
            border-radius: 16px;
            padding: 14px 18px;
            margin-bottom: 22px;
            font-size: 14px;
            font-weight: 600;
        }

        .history-alert-success {
            background: rgba(32, 181, 102, 0.16);
            border: 1px solid rgba(32, 181, 102, 0.32);
            color: #8af0b8;
        }

        .history-alert-error {
            background: rgba(255, 99, 99, 0.16);
            border: 1px solid rgba(255, 99, 99, 0.3);
            color: #ffb4b4;
        }

        .history-summary-card,
        .order-history-card,
        .history-empty {
            background: rgba(24, 18, 15, 0.76);
            border: 1px solid rgba(196, 155, 99, 0.16);
            box-shadow: 0 18px 45px rgba(0, 0, 0, 0.22);
            backdrop-filter: blur(8px);
        }

        .history-summary-card {
            border-radius: 20px;
            padding: 22px 24px;
        }

        .history-summary-card span {
            display: block;
            color: rgba(255, 255, 255, 0.58);
            font-size: 13px;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.12em;
        }

        .history-summary-card strong {
            color: #fff;
            font-size: 28px;
            font-weight: 700;
        }

        .order-history-list {
            display: grid;
            gap: 22px;
        }

        .order-history-card {
            border-radius: 24px;
            padding: 26px 28px;
        }

        .order-history-top {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            align-items: flex-start;
            margin-bottom: 24px;
        }

        .order-code {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
            color: #fff;
        }

        .order-date {
            margin: 6px 0 0;
            color: rgba(255, 255, 255, 0.58);
            font-size: 14px;
        }

        .order-status {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 16px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .status-confirmed,
        .status-delivered,
        .status-ready {
            background: rgba(32, 181, 102, 0.16);
            color: #7dffb7;
        }

        .status-pending,
        .status-processing {
            background: rgba(255, 194, 84, 0.16);
            color: #ffd273;
        }

        .status-cancelled,
        .status-failed {
            background: rgba(255, 99, 99, 0.16);
            color: #ff9c9c;
        }

        .order-meta-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .order-meta-item {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 16px;
            padding: 16px 18px;
        }

        .order-meta-item span {
            display: block;
            color: rgba(255, 255, 255, 0.56);
            font-size: 13px;
            margin-bottom: 6px;
        }

        .order-meta-item strong {
            color: #fff;
            font-size: 18px;
            font-weight: 700;
        }

        .order-item-list {
            display: grid;
            gap: 14px;
        }

        .order-action-row {
            margin-top: 18px;
            padding-top: 18px;
            border-top: 1px dashed rgba(255, 255, 255, 0.12);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
        }

        .order-cancel-btn {
            border: none;
            border-radius: 999px;
            background: rgba(255, 99, 99, 0.16);
            color: #ffb4b4;
            padding: 10px 16px;
            font-size: 13px;
            font-weight: 700;
        }

        .order-cancel-btn:hover,
        .order-cancel-btn:focus {
            background: rgba(255, 99, 99, 0.24);
            color: #ffd0d0;
            outline: none;
        }

        .order-action-note {
            color: rgba(255, 255, 255, 0.56);
            font-size: 13px;
        }

        .order-item-row {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            padding-top: 14px;
            border-top: 1px dashed rgba(255, 255, 255, 0.12);
        }

        .order-item-row:first-child {
            padding-top: 0;
            border-top: none;
        }

        .order-item-title {
            margin: 0;
            color: #fff;
            font-size: 17px;
            font-weight: 600;
        }

        .order-item-notes {
            margin-top: 6px;
        }

        .order-item-notes p {
            margin: 0 0 4px;
            color: rgba(255, 255, 255, 0.62);
            font-size: 14px;
            line-height: 1.7;
        }

        .order-item-side {
            color: #c49b63;
            font-size: 15px;
            font-weight: 700;
            white-space: nowrap;
        }

        .history-empty {
            border-radius: 24px;
            padding: 54px 24px;
        }

        .history-empty-icon {
            width: 82px;
            height: 82px;
            border-radius: 50%;
            background: rgba(196, 155, 99, 0.14);
            color: #c49b63;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            margin-bottom: 18px;
        }

        .history-empty h3 {
            color: #fff;
            margin-bottom: 10px;
            font-size: 28px;
        }

        .history-empty p {
            color: rgba(255, 255, 255, 0.62);
            margin-bottom: 24px;
        }

        .history-cta {
            background: #c49b63;
            border-color: #c49b63;
            color: #111;
            font-weight: 700;
            padding: 12px 28px;
        }

        .history-cta:hover,
        .history-cta:focus {
            background: #b6894f;
            border-color: #b6894f;
            color: #111;
        }

        @media (max-width: 767px) {
            .order-action-row {
                align-items: stretch;
            }

            .order-cancel-btn {
                width: 100%;
            }
        }

        .coffee-footer {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            color: #ffffff;
            background: linear-gradient(135deg, #1a1a1a 0%, #0d0d0d 100%);
            line-height: 1.6;
            margin-top: 100px;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 40px;
            align-items: flex-start;
        }

        .footer-links ul {
            list-style: none;
            padding-left: 0;
        }

        .brand-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
        }

        .footer-logo {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .footer-logo img {
            height: 70px;
            width: auto;
            object-fit: contain;
        }

        .main-footer {
            padding: 60px 0 40px;
        }

        .footer-brand h2 {
            font-size: 2.2rem;
            font-weight: 800;
            margin-bottom: 16px;
            background: linear-gradient(45deg, #ffffff, #ff6b00);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .footer-brand p {
            opacity: 0.8;
            margin-bottom: 24px;
        }

        .social-links {
            display: flex;
            gap: 16px;
        }

        .social-links a {
            width: 44px;
            height: 44px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .social-links a:hover {
            background: #ff6b00;
            transform: translateY(-3px);
        }

        .footer-links h4,
        .footer-contact h4 {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 20px;
            position: relative;
        }

        .footer-links h4::after {
            content: '';
            position: absolute;
            bottom: -6px;
            left: 0;
            width: 30px;
            height: 2px;
            background: #ff6b00;
        }

        .footer-links li {
            margin-bottom: 12px;
        }

        .footer-links a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .footer-links a:hover {
            color: #ff6b00;
            padding-left: 6px;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
            opacity: 0.9;
        }

        .contact-item i {
            color: #ff6b00;
            width: 20px;
        }

        .copyright {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding: 20px 0;
            text-align: center;
        }

        .copyright p {
            opacity: 0.7;
            font-size: 0.9rem;
        }

        .user-avatar {
            width: 48px;
            height: 48px;
            border-radius: 999px;
            object-fit: cover;
                border: none;
                box-shadow: none;
                display: block;
            transition: 0.3s;
        }

        .user-avatar-btn {
            margin-left: 8px;
                padding: 0;
                border: none;
                background: transparent;
                box-shadow: none;
                appearance: none;
            border-radius: 999px;
            overflow: hidden;
        }

            .dropdown-avatar {
                width: 52px;
                height: 52px;
                border-radius: 999px;
                object-fit: cover;
                border: none;
                box-shadow: none;
                display: block;
                flex-shrink: 0;
            }

        .user-avatar:hover {
            transform: scale(1.1);
        }

        .user-dropdown {
            position: relative;
        }

        .user-dropdown .dropdown-menu {
            display: none;
            position: absolute;
            right: 0;
            top: 120%;
            background: #1a1a1a;
            border-radius: 12px;
            min-width: 230px;
            padding: 10px;
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .user-dropdown:hover .dropdown-menu {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
        }

        .user-info img {
            width: 45px;
            height: 45px;
            border-radius: 50%;
        }

        .user-info p {
            margin: 0;
            font-size: 12px;
            color: #aaa;
        }

        .back-to-top-btn {
            position: fixed;
            right: 24px;
            bottom: 24px;
            width: 52px;
            height: 52px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none;
            border-radius: 50%;
            background: linear-gradient(135deg, #c9a96e, #8a5b2f);
            color: #fff7ed;
            box-shadow: 0 14px 30px rgba(26, 17, 13, .22);
            cursor: pointer;
            opacity: 0;
            visibility: hidden;
            transform: translateY(16px);
            transition: opacity .25s ease, transform .25s ease, visibility .25s ease, box-shadow .25s ease;
            z-index: 9998;
        }

        .back-to-top-btn.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .back-to-top-btn:hover {
            box-shadow: 0 18px 34px rgba(26, 17, 13, .28);
            transform: translateY(-3px);
        }

        .back-to-top-btn:focus {
            outline: none;
            box-shadow: 0 0 0 4px rgba(201, 169, 110, .25), 0 14px 30px rgba(26, 17, 13, .22);
        }

        .dropdown-item {
            color: #fff;
            padding: 10px 12px;
            border-radius: 8px;
            transition: 0.3s;
        }

        .dropdown-item:hover {
            background: #ff6b00;
            color: #fff;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 991.98px) {
            .history-summary-grid,
            .order-meta-grid {
                grid-template-columns: 1fr;
            }

            .history-hero h1 {
                font-size: 42px;
            }
        }

        @media (max-width: 767.98px) {
            .order-history-top,
            .order-item-row,
            .footer-grid {
                flex-direction: column;
                grid-template-columns: 1fr;
            }

            .history-hero h1 {
                font-size: 34px;
            }

            .order-history-card,
            .history-summary-card,
            .history-empty {
                padding: 20px;
            }

            .social-links {
                justify-content: center;
            }

            .back-to-top-btn {
                right: 16px;
                bottom: 18px;
                width: 48px;
                height: 48px;
            }
        }
    </style>

    <button type="button" class="back-to-top-btn" id="backToTopBtn" aria-label="Trở về đầu trang">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6"
            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M12 19V5" />
            <path d="m5 12 7-7 7 7" />
        </svg>
    </button>

    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/jquery-migrate-3.0.1.min.js') }}"></script>
    <script src="{{ asset('js/popper.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('js/jquery.easing.1.3.js') }}"></script>
    <script src="{{ asset('js/jquery.waypoints.min.js') }}"></script>
    <script src="{{ asset('js/jquery.stellar.min.js') }}"></script>
    <script src="{{ asset('js/owl.carousel.min.js') }}"></script>
    <script src="{{ asset('js/jquery.magnific-popup.min.js') }}"></script>
    <script src="{{ asset('js/aos.js') }}"></script>
    <script src="{{ asset('js/jquery.animateNumber.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap-datepicker.js') }}"></script>
    <script src="{{ asset('js/jquery.timepicker.min.js') }}"></script>
    <script src="{{ asset('js/scrollax.min.js') }}"></script>
    <script src="{{ asset('js/main.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const userMenuBtn = document.getElementById('userMenuBtn');
            const userDropdownMenu = document.getElementById('userDropdownMenu');
            const dropdownContainer = document.querySelector('.user-dropdown-container');
            const backToTopBtn = document.getElementById('backToTopBtn');

            function toggleBackToTopButton() {
                if (!backToTopBtn) {
                    return;
                }

                if (window.scrollY > 320) {
                    backToTopBtn.classList.add('show');
                } else {
                    backToTopBtn.classList.remove('show');
                }
            }

            if (backToTopBtn) {
                toggleBackToTopButton();
                backToTopBtn.addEventListener('click', function() {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                });
                window.addEventListener('scroll', toggleBackToTopButton, { passive: true });
            }

            if (userMenuBtn && userDropdownMenu && dropdownContainer) {
                userMenuBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    userDropdownMenu.classList.toggle('active');
                    userMenuBtn.classList.toggle('active');
                });

                dropdownContainer.addEventListener('mouseenter', function() {
                    userDropdownMenu.classList.add('active');
                    userMenuBtn.classList.add('active');
                });

                dropdownContainer.addEventListener('mouseleave', function() {
                    userDropdownMenu.classList.remove('active');
                    userMenuBtn.classList.remove('active');
                });

                document.addEventListener('click', function(e) {
                    if (!dropdownContainer.contains(e.target)) {
                        userDropdownMenu.classList.remove('active');
                        userMenuBtn.classList.remove('active');
                    }
                });
            }
        });
    </script>
@include('components.ai-bot-widget')
</body>

</html>