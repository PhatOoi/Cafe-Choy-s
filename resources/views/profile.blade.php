<!DOCTYPE html>
<html lang="vi">

<head>
    <title>Hồ Sơ Khách Hàng - Choy's Cafe</title>
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
                            <li class="nav-item"><a href="{{ route('orders.history') }}" class="nav-link">Lịch sử đơn hàng</a></li>
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
                                    <a href="/profile" class="dropdown-link active-link">Hồ sơ</a>

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

    <section class="ftco-section profile-section">
        <div class="container profile-container">
            <div class="profile-hero text-center">
                <span class="profile-kicker">Khu vực cá nhân</span>
                <h1>Hồ sơ khách hàng</h1>
                <p>Quản lý nhanh thông tin cá nhân, theo dõi trạng thái tài khoản và truy cập lại các khu vực bạn dùng thường xuyên trên Choy's Cafe.</p>
            </div>

            <div class="profile-shell">
                <aside class="profile-side-card">
                    <div class="profile-side-top">
                        <div class="avatar-frame" style="position:relative;">
                            @if($user->avatar_url)
                                <img src="{{ asset('storage/' . $user->avatar_url) }}" alt="{{ $user->name }}" class="profile-avatar" id="avatarPreview">
                            @else
                                <img src="{{ asset('images/user.jpg') }}" alt="{{ $user->name }}" class="profile-avatar" id="avatarPreview">
                            @endif
                            <form action="{{ route('profile.avatar') }}" method="POST" enctype="multipart/form-data" id="avatarForm">
                                @csrf
                                <label for="avatarInput" title="Thay đổi ảnh đại diện" style="position:absolute;bottom:6px;right:6px;background:#c8a26b;border-radius:50%;width:30px;height:30px;display:flex;align-items:center;justify-content:center;cursor:pointer;box-shadow:0 2px 6px rgba(0,0,0,.3);">
                                    <i class="fas fa-camera" style="color:#fff;font-size:13px;"></i>
                                </label>
                                <input type="file" id="avatarInput" name="avatar" accept="image/*" style="display:none;" onchange="document.getElementById('avatarForm').submit();">
                            </form>
                        </div>
                        <p class="profile-role-label">{{ $user->role->name ?? 'Khách hàng' }}</p>
                        <h2 class="profile-user-name">{{ $user->name }}</h2>
                        <p class="profile-user-email">{{ $user->email }}</p>
                    </div>

                    <div class="profile-quick-list">
                        <div class="quick-item">
                            <span class="quick-label">Đơn đã lưu</span>
                            <strong>{{ $orders->count() }}</strong>
                        </div>
                        <div class="quick-item">
                            <span class="quick-label">Tham gia từ</span>
                            <strong>{{ $user->created_at->format('m/Y') }}</strong>
                        </div>
                        <div class="quick-item">
                            <span class="quick-label">Số điện thoại</span>
                            <strong>{{ $user->phone ?? 'Chưa cập nhật' }}</strong>
                        </div>
                    </div>

                    {{-- ── Nút hành động nhanh: đến menu và lịch sử đơn ── --}}
                    <div class="profile-side-actions">
                        <a href="{{ url('/menu') }}" class="profile-action-btn primary-btn">
                            <i class="fas fa-mug-hot"></i>
                            Quay lại menu
                        </a>
                        @if(!auth()->user()->isStaff())
                            <a href="{{ route('orders.history') }}" class="profile-action-btn secondary-btn">
                                <i class="fas fa-receipt"></i>
                                Lịch sử đơn hàng
                            </a>
                        @endif
                    </div>
                </aside>

                <div class="profile-main-grid">
                    @if(session('success'))
                        <div class="profile-alert success-alert" id="successAlert">{{ session('success') }}</div>
                        <script>
                            setTimeout(function () {
                                var el = document.getElementById('successAlert');
                                if (el) {
                                    el.style.transition = 'opacity 0.5s ease';
                                    el.style.opacity = '0';
                                    setTimeout(function () { el.remove(); }, 500);
                                }
                            }, 3000);
                        </script>
                    @endif

                    @if($errors->any())
                        <div class="profile-alert error-alert">
                            <strong>Không thể cập nhật mật khẩu.</strong>
                            <ul class="profile-alert-list">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- ── Panel thông tin cơ bản: email, phone, role ── --}}
                    <section class="profile-panel panel-wide">
                        <div class="panel-heading">
                            <span class="panel-kicker">Thông tin chính</span>
                            <h3>Thông tin khách hàng</h3>
                        </div>
                        <div class="profile-info-grid">
                            <article class="info-card">
                                <div class="info-icon-wrap"><i class="fas fa-envelope"></i></div>
                                <div>
                                    <p class="info-title">Email</p>
                                    <p class="info-value">{{ $user->email }}</p>
                                </div>
                            </article>

                            <article class="info-card">
                                <div class="info-icon-wrap"><i class="fas fa-phone"></i></div>
                                <div>
                                    <p class="info-title">Số điện thoại</p>
                                    <p class="info-value {{ $user->phone ? '' : 'is-empty' }}">{{ $user->phone ?? 'Chưa cập nhật' }}</p>
                                </div>
                            </article>

                            <article class="info-card">
                                <div class="info-icon-wrap"><i class="fas fa-user-tag"></i></div>
                                <div>
                                    <p class="info-title">Vai trò</p>
                                    <p class="info-value">{{ $user->role->name ?? 'Khách hàng' }}</p>
                                </div>
                            </article>

                            <article class="info-card">
                                <div class="info-icon-wrap"><i class="fas fa-calendar-alt"></i></div>
                                <div>
                                    <p class="info-title">Tham gia từ</p>
                                    <p class="info-value">{{ $user->created_at->format('m/Y') }}</p>
                                </div>
                            </article>
                        </div>
                    </section>

                    <section class="profile-panel">
                        <div class="panel-heading">
                            <span class="panel-kicker">Tài khoản</span>
                            <h3>Trạng thái hiện tại</h3>
                        </div>
                        <div class="status-stack">
                            <div class="status-card">
                                <span class="status-dot"></span>
                                <div>
                                    <p class="status-title">Tài khoản đang hoạt động</p>
                                    <p class="status-copy">Bạn có thể tiếp tục đặt món, xem lịch sử đơn hàng và cập nhật thông tin khi cần.</p>
                                </div>
                            </div>
                            <div class="status-card muted-card">
                                <div>
                                    <p class="status-title">Gợi ý tiếp theo</p>
                                    <p class="status-copy">Sử dụng mục Lịch sử đơn hàng trên thanh menu để xem lại các đơn đã thanh toán và quay lại đặt món nhanh hơn.</p>
                                </div>
                            </div>
                        </div>
                    </section>

                    {{-- ── Panel đổi mật khẩu: validate mật khẩu mạnh + xác nhận ── --}}
                    <section class="profile-panel panel-wide" id="password-panel">
                        <div class="panel-heading">
                            <span class="panel-kicker">Bảo mật</span>
                            <h3>Đổi mật khẩu</h3>
                        </div>
                        <form action="{{ route('profile.update') }}" method="POST" class="password-form">
                            @csrf
                            @method('PUT')

                            <div class="password-form-grid">
                                <div class="form-field">
                                    <label for="current_password">Mật khẩu hiện tại</label>
                                    <input type="password" id="current_password" name="current_password" placeholder="Nhập mật khẩu hiện tại" required autocomplete="current-password">
                                </div>

                                <div class="form-field">
                                    <label for="password">Mật khẩu mới</label>
                                    <input type="password" id="password" name="password" placeholder="Tạo mật khẩu mới" required autocomplete="new-password">
                                </div>

                                <div class="form-field form-field-full">
                                    <label for="password_confirmation">Xác nhận mật khẩu mới</label>
                                    <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Nhập lại mật khẩu mới" required autocomplete="new-password">
                                </div>
                            </div>

                            <div class="password-form-actions">
                                <button type="submit" class="profile-action-btn primary-btn">Cập nhật mật khẩu</button>
                            </div>
                        </form>
                    </section>
                </div>
            </div>
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
        .profile-section {
            padding: 110px 0 90px;
        }

        .profile-container {
            max-width: 1220px;
        }

        .profile-hero {
            max-width: 780px;
            margin: 0 auto 46px;
        }

        .profile-kicker {
            display: inline-block;
            margin-bottom: 14px;
            color: #c49b63;
            letter-spacing: 0.24em;
            text-transform: uppercase;
            font-size: 12px;
            font-weight: 600;
        }

        .profile-hero h1 {
            font-size: 52px;
            color: #fff;
            margin-bottom: 16px;
            font-weight: 700;
        }

        .profile-hero p {
            color: rgba(255, 255, 255, 0.72);
            font-size: 16px;
            line-height: 1.8;
            margin: 0;
        }

        .profile-shell {
            display: grid;
            grid-template-columns: 340px minmax(0, 1fr);
            gap: 24px;
            align-items: start;
        }

        .profile-side-card,
        .profile-panel {
            background: rgba(24, 18, 15, 0.76);
            border: 1px solid rgba(196, 155, 99, 0.16);
            box-shadow: 0 18px 45px rgba(0, 0, 0, 0.22);
            backdrop-filter: blur(8px);
        }

        .profile-side-card {
            border-radius: 24px;
            padding: 28px;
            position: sticky;
            top: 110px;
        }

        .profile-side-top {
            text-align: center;
            padding-bottom: 26px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .avatar-frame {
            width: 126px;
            height: 126px;
            margin: 10px auto 24px;
                padding: 0;
            border-radius: 50%;
                background: transparent;
                overflow: hidden;
                box-shadow: 0 12px 28px rgba(0, 0, 0, 0.22);
        }

        .profile-avatar {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
                border: none;
                box-shadow: none;
                display: block;
        }

        .profile-role-label {
            margin: 0 0 10px;
            color: #c49b63;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            font-size: 12px;
            font-weight: 700;
        }

        .profile-user-name {
            margin: 0 0 10px;
            color: #fff;
            font-size: 28px;
            font-weight: 700;
        }

        .profile-user-email {
            margin: 0;
            color: rgba(255, 255, 255, 0.68);
            font-size: 15px;
            word-break: break-word;
        }

        .profile-quick-list {
            display: grid;
            gap: 12px;
            margin-top: 22px;
        }

        .quick-item {
            padding: 14px 16px;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.06);
        }

        .quick-label {
            display: block;
            color: rgba(255, 255, 255, 0.54);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            margin-bottom: 6px;
        }

        .quick-item strong {
            color: #fff;
            font-size: 16px;
            font-weight: 600;
            line-height: 1.6;
            word-break: break-word;
        }

        .profile-side-actions {
            display: grid;
            gap: 12px;
            margin-top: 22px;
        }

        .profile-action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
            padding: 0.95rem 1.1rem;
            border-radius: 999px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.25s ease;
        }

        .primary-btn {
            background: #c49b63;
            border: 1px solid #c49b63;
            color: #1a140f;
        }

        .primary-btn:hover,
        .primary-btn:focus {
            background: #b6894f;
            border-color: #b6894f;
            color: #1a140f;
            text-decoration: none;
            transform: translateY(-2px);
        }

        .secondary-btn {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            color: #fff;
        }

        .secondary-btn:hover,
        .secondary-btn:focus {
            background: rgba(196, 155, 99, 0.12);
            border-color: rgba(196, 155, 99, 0.22);
            color: #fff;
            text-decoration: none;
            transform: translateY(-2px);
        }

        .profile-main-grid {
            display: grid;
            gap: 24px;
        }

        .profile-alert {
            border-radius: 18px;
            padding: 16px 18px;
            border: 1px solid transparent;
        }

        .success-alert {
            background: rgba(83, 181, 118, 0.14);
            border-color: rgba(83, 181, 118, 0.24);
            color: #d2ffd8;
        }

        .error-alert {
            background: rgba(220, 53, 69, 0.14);
            border-color: rgba(220, 53, 69, 0.24);
            color: #ffd9dd;
        }

        .profile-alert-list {
            margin: 10px 0 0;
            padding-left: 18px;
        }

        .profile-panel {
            border-radius: 24px;
            padding: 28px;
        }

        .password-form {
            display: grid;
            gap: 18px;
        }

        .password-form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        .form-field {
            display: grid;
            gap: 8px;
        }

        .form-field-full {
            grid-column: 1 / -1;
        }

        .form-field label {
            margin: 0;
            color: #f7efe5;
            font-size: 14px;
            font-weight: 600;
        }

        .form-field input {
            width: 100%;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.04);
            color: #fff;
            padding: 14px 16px;
            outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .form-field input:focus {
            border-color: rgba(196, 155, 99, 0.7);
            box-shadow: 0 0 0 3px rgba(196, 155, 99, 0.16);
        }

        .password-form-actions {
            display: flex;
            justify-content: flex-end;
        }

        .panel-wide {
            min-height: 100%;
        }

        .panel-heading {
            margin-bottom: 20px;
        }

        .panel-kicker {
            display: inline-block;
            color: #c49b63;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.16em;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .panel-heading h3 {
            margin: 0;
            color: #fff;
            font-size: 28px;
            font-weight: 700;
        }

        .profile-info-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        .info-card {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            padding: 18px;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.06);
        }

        .info-icon-wrap {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            background: rgba(196, 155, 99, 0.14);
            color: #e8c48c;
            font-size: 18px;
        }

        .info-title {
            margin: 0 0 6px;
            color: rgba(255, 255, 255, 0.56);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.12em;
        }

        .info-value {
            margin: 0;
            color: #fff;
            font-size: 17px;
            font-weight: 600;
            line-height: 1.6;
            word-break: break-word;
        }

        .info-value.is-empty {
            color: rgba(255, 255, 255, 0.46);
            font-style: italic;
        }

        .status-stack {
            display: grid;
            gap: 16px;
        }

        .status-card {
            display: flex;
            gap: 14px;
            padding: 18px;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.06);
        }

        .muted-card {
            background: rgba(196, 155, 99, 0.05);
            border-color: rgba(196, 155, 99, 0.12);
        }

        .status-dot {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: #8ed699;
            box-shadow: 0 0 0 6px rgba(142, 214, 153, 0.14);
            margin-top: 6px;
            flex-shrink: 0;
        }

        .status-title {
            margin: 0 0 8px;
            color: #fff;
            font-size: 18px;
            font-weight: 600;
        }

        .status-copy {
            margin: 0;
            color: rgba(255, 255, 255, 0.66);
            line-height: 1.75;
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
            margin-left: 10px;
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

        .active-link {
            color: #c49b63 !important;
        }

        @media (max-width: 991.98px) {
            .profile-shell {
                grid-template-columns: 1fr;
            }

            .profile-side-card {
                position: static;
            }

            .profile-hero h1 {
                font-size: 42px;
            }
        }

        @media (max-width: 767.98px) {
            .profile-hero h1 {
                font-size: 34px;
            }

            .profile-info-grid,
            .footer-grid,
            .password-form-grid {
                grid-template-columns: 1fr;
            }

            .profile-side-card,
            .profile-panel {
                padding: 22px;
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
</body>

</html>