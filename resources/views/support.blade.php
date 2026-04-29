<!DOCTYPE html>
<html lang="vi">

<head>
    <title>Hỗ Trợ Khách Hàng - Choy's Cafe</title>
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
                    <li class="nav-item active"><a href="{{ route('support') }}" class="nav-link">Hỗ trợ</a></li>
                    <li class="nav-item flex-spacer"></li>
                    <li class="nav-item cart">
                        <a href="/cart" class="nav-link">
                            <span class="icon icon-shopping_cart"></span>
                            <span class="bag"><small id="cart-count">0</small></span>
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
                                                @if(Auth::user()->isAdmin())
                                                    <span class="badge-admin">Admin</span>
                                                @else
                                                    <span class="badge-customer">Khách hàng</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                    <div class="dropdown-divider"></div>
                                    <a href="/profile" class="dropdown-link">Hồ sơ</a>
                                    <div class="dropdown-divider"></div>
                                    <form id="logout-form" action="{{ url('/logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-link logout-btn">Đăng xuất</button>
                                    </form>
                                </div>
                            </div>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>

    <section class="support-section">
        <div class="container support-container">

            <div class="support-hero text-center">
                <span class="support-kicker">Chúng tôi luôn ở đây</span>
                <h1>Hỗ Trợ Khách Hàng</h1>
                <p>Mọi thắc mắc về đơn hàng, thanh toán hay sản phẩm, đội ngũ Choy's Cafe sẵn sàng hỗ trợ bạn 7 ngày trong tuần.</p>
            </div>

            <!-- CONTACT CARDS -->
            <div class="support-contact-grid">
                <div class="support-contact-card">
                    <div class="contact-icon-wrap"><i class="fas fa-phone-alt"></i></div>
                    <h4>Hotline</h4>
                    <p>Gọi trực tiếp cho chúng tôi trong giờ làm việc</p>
                    <a href="tel:+84346901474" class="contact-action">+84 346901474</a>
                    <span class="contact-note">8:00 – 24:00 hàng ngày</span>
                </div>

                <div class="support-contact-card">
                    <div class="contact-icon-wrap"><i class="fas fa-envelope"></i></div>
                    <h4>Email</h4>
                    <p>Gửi câu hỏi và chúng tôi phản hồi trong vòng 24 giờ</p>
                    <a href="mailto:support@choy.cafe" class="contact-action">support@choy.cafe</a>
                    <span class="contact-note">Phản hồi trong 24 giờ</span>
                </div>

                <div class="support-contact-card">
                    <div class="contact-icon-wrap"><i class="fab fa-facebook-messenger"></i></div>
                    <h4>Facebook</h4>
                    <p>Nhắn tin trực tiếp qua trang Facebook của chúng tôi</p>
                    <a href="https://www.facebook.com/share/1CvQdbW463/?mibextid=wwXIfr" target="_blank" rel="noopener noreferrer" class="contact-action">Choy's Cafe</a>
                    <span class="contact-note">Trả lời trong 1-2 giờ</span>
                </div>

                <div class="support-contact-card" style="border-color: rgba(200,162,107,.35); background: rgba(200,162,107,.06);">
                    <div class="contact-icon-wrap" style="background: rgba(200,162,107,.25);"><i class="fas fa-robot"></i></div>
                    <h4 style="color: #c8a26b;">Choy AI</h4>
                    <p>Trợ lý AI trả lời tức thì về menu, đặt hàng và mọi thắc mắc về quán</p>
                    <a href="{{ route('ai-chat.index') }}" class="contact-action">Chat với AI ngay</a>
                    <span class="contact-note">Phản hồi trong vài giây</span>
                </div>
            </div>

            <!-- FAQ + CHAT side by side -->
            <div class="faq-chat-grid">
                <!-- FAQ -->
                <div class="faq-section">
                    <div class="faq-heading text-center">
                        <span class="support-kicker">Câu hỏi thường gặp</span>
                        <h2>FAQ</h2>
                    </div>

                    <div class="faq-list">
                        <div class="faq-item">
                            <button class="faq-question" type="button">
                                <span>Tôi có thể hủy đơn hàng sau khi đặt không?</span>
                                <i class="fas fa-chevron-down faq-icon"></i>
                            </button>
                            <div class="faq-answer">
                                <p>Bạn có thể hủy đơn trong vòng <strong>5 phút</strong> sau khi đặt hàng, miễn là đơn chưa được xác nhận bởi nhân viên. Vào mục <a href="{{ route('orders.history') }}">Lịch sử đơn hàng</a> để thực hiện hủy.</p>
                            </div>
                        </div>

                        <div class="faq-item">
                            <button class="faq-question" type="button">
                                <span>Các phương thức thanh toán nào được hỗ trợ?</span>
                                <i class="fas fa-chevron-down faq-icon"></i>
                            </button>
                            <div class="faq-answer">
                                <p>Chúng tôi hỗ trợ thanh toán <strong>tiền mặt</strong> khi nhận hàng và <strong>chuyển khoản QR</strong> qua ứng dụng ngân hàng.</p>
                            </div>
                        </div>

                        <div class="faq-item">
                            <button class="faq-question" type="button">
                                <span>Thời gian giao hàng mất bao lâu?</span>
                                <i class="fas fa-chevron-down faq-icon"></i>
                            </button>
                            <div class="faq-answer">
                                <p>Thông thường đơn hàng sẽ được chuẩn bị và giao trong khoảng <strong>20–40 phút</strong> tùy thuộc vào khu vực và thời điểm đặt hàng.</p>
                            </div>
                        </div>

                        <div class="faq-item">
                            <button class="faq-question" type="button">
                                <span>Tôi quên mật khẩu, phải làm gì?</span>
                                <i class="fas fa-chevron-down faq-icon"></i>
                            </button>
                            <div class="faq-answer">
                                <p>Vào trang <a href="{{ route('forgot-password.email-form') }}">Quên mật khẩu</a>, nhập email đã đăng ký. Chúng tôi sẽ gửi mã xác minh để đặt lại mật khẩu.</p>
                            </div>
                        </div>

                        <div class="faq-item">
                            <button class="faq-question" type="button">
                                <span>Làm sao để cập nhật thông tin tài khoản?</span>
                                <i class="fas fa-chevron-down faq-icon"></i>
                            </button>
                            <div class="faq-answer">
                                <p>Đăng nhập và truy cập <a href="/profile">Hồ sơ cá nhân</a> để cập nhật ảnh đại diện, số điện thoại và đổi mật khẩu.</p>
                            </div>
                        </div>

                        <div class="faq-item">
                            <button class="faq-question" type="button">
                                <span>Sản phẩm có thể tùy chỉnh như thế nào?</span>
                                <i class="fas fa-chevron-down faq-link"></i>
                            </button>
                            <div class="faq-answer">
                                <p>Khi thêm sản phẩm vào giỏ hàng, bạn có thể chọn <strong>kích cỡ, topping, đường và đá</strong> theo sở thích. Một số sản phẩm còn hỗ trợ thêm note đặc biệt.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CHAT -->
                <div class="support-chat-section">
                    <div class="faq-heading text-center">
                        <span class="support-kicker">Hỗ trợ trực tiếp</span>
                        <h2>Chat Với Nhân Viên</h2>
                    </div>

                    @auth
                        <div class="support-chat-shell">
                            {{-- Header khung chat để đồng bộ với card style của toàn trang hỗ trợ. --}}
                            <div class="support-chat-header">
                                <div class="support-chat-header-icon">
                                    <i class="fas fa-headset"></i>
                                </div>
                                <div class="support-chat-header-copy">
                                    <h3>Hỗ trợ trực tiếp từ Choy's Cafe</h3>
                                    <p>Nhắn tin với nhân viên để được hỗ trợ đơn hàng, thanh toán hoặc sản phẩm.</p>
                                </div>
                            </div>

                            <div id="supportChatMessages" class="support-chat-messages">
                                <div class="support-chat-empty">Bắt đầu hội thoại. Nhân viên sẽ phản hồi sớm nhất có thể.</div>
                            </div>
                            <div class="support-chat-input-row">
                                <input id="supportChatInput" type="text" placeholder="Nhập tin nhắn..." onkeydown="if(event.key==='Enter')sendSupportMessage()">
                                <button type="button" id="supportChatSendBtn" onclick="sendSupportMessage()">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                        </div>
                    @else
                        <div class="support-chat-login-box">
                            <div class="support-chat-login-icon">
                                <i class="fas fa-comments"></i>
                            </div>
                            <h3>Đăng nhập để nhận hỗ trợ</h3>
                            <p>Vui lòng đăng nhập để chat trực tiếp với nhân viên hỗ trợ.</p>
                            <a href="{{ url('/login') }}" class="support-chat-login-btn">Đăng nhập ngay</a>
                        </div>
                    @endauth
                </div>
            </div>

        </div>
    </section>

    <style>
        /* Đảm bảo navbar luôn ở trên cùng */
        .ftco_navbar {
            position: sticky !important;
            top: 0;
            z-index: 1000 !important;
        }

        /* User avatar & dropdown */
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
        .user-avatar:hover { transform: scale(1.1); }
        .user-avatar-btn {
            margin-left: 8px;
            padding: 0;
            border: none;
            background: transparent;
            box-shadow: none;
            appearance: none;
            border-radius: 999px;
            overflow: hidden;
            cursor: pointer;
        }
        .user-dropdown-wrapper { list-style: none; }
        .user-dropdown-container { position: relative; }
        .user-dropdown-menu {
            display: none;
            position: absolute;
            right: 0;
            top: calc(100% + 8px);
            background: #1a1a1a;
            border-radius: 12px;
            min-width: 230px;
            padding: 10px;
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.4);
            z-index: 9999;
        }
        .user-dropdown-menu.active { display: block; animation: dropdownFadeIn 0.2s ease; }
        @keyframes dropdownFadeIn {
            from { opacity: 0; transform: translateY(8px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .dropdown-header-info {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px;
        }
        .dropdown-avatar {
            width: 48px;
            height: 48px;
            border-radius: 999px;
            object-fit: cover;
            flex-shrink: 0;
        }
        .user-details .user-name { margin: 0; font-size: 14px; font-weight: 600; color: #fff; }
        .user-details .user-role { margin: 0; font-size: 12px; color: #aaa; }
        .badge-admin, .badge-customer {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 11px;
        }
        .badge-admin { background: #c8a26b; color: #1a1a1a; }
        .badge-customer { background: #333; color: #ccc; }
        .dropdown-divider { border-top: 1px solid rgba(255,255,255,0.1); margin: 6px 0; }
        .dropdown-link {
            display: block;
            padding: 9px 14px;
            color: #fff;
            text-decoration: none;
            border-radius: 8px;
            font-size: 14px;
            transition: background 0.2s;
            background: none;
            border: none;
            width: 100%;
            text-align: left;
            cursor: pointer;
        }
        .dropdown-link:hover { background: rgba(200,162,107,0.15); color: #c8a26b; }
        .logout-btn { color: #f87171; }
        .logout-btn:hover { background: rgba(248,113,113,0.1); color: #f87171; }

        .support-section {
            padding: 110px 0 90px;
            background: #1a1009;
            min-height: 100vh;
        }
        .support-container {
            max-width: 1200px;
        }
        .support-hero {
            max-width: 700px;
            margin: 0 auto 56px;
        }
        .support-kicker {
            display: inline-block;
            margin-bottom: 14px;
            color: #c8a26b;
            letter-spacing: 0.24em;
            text-transform: uppercase;
            font-size: 12px;
            font-weight: 600;
        }
        .support-hero h1 {
            font-size: 48px;
            color: #fff;
            margin-bottom: 16px;
            font-weight: 700;
        }
        .support-hero p {
            color: rgba(255,255,255,.7);
            font-size: 16px;
            line-height: 1.8;
        }
        /* Contact cards */
        .support-contact-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 64px;
        }
        @media (max-width: 900px) {
            .support-contact-grid { grid-template-columns: repeat(2, 1fr); }
            .support-hero h1 { font-size: 32px; }
        }
        @media (max-width: 576px) {
            .support-contact-grid { grid-template-columns: 1fr; }
        }
        .support-contact-card {
            background: rgba(255,255,255,.05);
            border: 1px solid rgba(200,162,107,.18);
            border-radius: 14px;
            padding: 32px 24px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            transition: border-color .25s, background .25s;
        }
        .support-contact-card:hover {
            border-color: rgba(200,162,107,.5);
            background: rgba(200,162,107,.07);
        }
        .contact-icon-wrap {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: rgba(200,162,107,.15);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 8px;
        }
        .contact-icon-wrap i {
            font-size: 22px;
            color: #c8a26b;
        }
        .support-contact-card h4 {
            color: #fff;
            font-size: 17px;
            font-weight: 600;
            margin: 0;
        }
        .support-contact-card p {
            color: rgba(255,255,255,.55);
            font-size: 13px;
            margin: 0;
            line-height: 1.6;
        }
        .contact-action {
            color: #c8a26b;
            font-weight: 600;
            font-size: 15px;
            text-decoration: none;
        }
        .contact-action:hover { color: #e0bc85; text-decoration: underline; }
        .contact-note {
            color: rgba(255,255,255,.4);
            font-size: 12px;
        }
        /* FAQ + Chat grid */
        .faq-chat-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            align-items: stretch;
            margin-top: 16px;
        }
        @media (max-width: 900px) {
            .faq-chat-grid { grid-template-columns: 1fr; }
        }
        /* FAQ */
        .faq-section {
            margin-top: 0;
            display: flex;
            flex-direction: column;
        }
        .faq-section .faq-list {
            flex: 1;
        }
        .faq-heading {
            margin-bottom: 36px;
        }
        .faq-heading h2 {
            font-size: 34px;
            color: #fff;
            font-weight: 700;
            margin: 0;
        }
        .faq-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .faq-item {
            background: rgba(255,255,255,.04);
            border: 1px solid rgba(200,162,107,.15);
            border-radius: 10px;
            overflow: hidden;
        }
        .faq-question {
            width: 100%;
            background: none;
            border: none;
            padding: 20px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            color: #fff;
            font-size: 15px;
            font-weight: 500;
            text-align: left;
            gap: 16px;
        }
        .faq-question:hover { color: #c8a26b; }
        .faq-icon {
            color: #c8a26b;
            font-size: 13px;
            transition: transform .3s;
            flex-shrink: 0;
        }
        .faq-item.open .faq-icon {
            transform: rotate(180deg);
        }
        .faq-answer {
            display: none;
            padding: 0 24px 20px;
            color: rgba(255,255,255,.7);
            font-size: 14px;
            line-height: 1.8;
        }
        .faq-answer a { color: #c8a26b; }
        .faq-item.open .faq-answer { display: block; }

        .support-chat-section {
            margin-top: 0;
            display: flex;
            flex-direction: column;
        }

        .support-chat-shell {
            flex: 1;
            border: 1px solid rgba(200,162,107,.18);
            border-radius: 18px;
            background: linear-gradient(180deg, rgba(255,255,255,.06), rgba(255,255,255,.03));
            overflow: hidden;
            display: flex;
            flex-direction: column;
            box-shadow: 0 22px 45px rgba(8, 4, 1, .24);
        }

        .support-chat-header {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 18px 20px;
            border-bottom: 1px solid rgba(255,255,255,.08);
            background: rgba(255,255,255,.03);
        }

        .support-chat-header-icon,
        .support-chat-login-icon {
            width: 52px;
            height: 52px;
            border-radius: 16px;
            background: rgba(200,162,107,.16);
            color: #c8a26b;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .support-chat-header-copy h3,
        .support-chat-login-box h3 {
            margin: 0 0 4px;
            color: #fff;
            font-size: 18px;
            font-weight: 600;
        }

        .support-chat-header-copy p {
            margin: 0;
            color: rgba(255,255,255,.62);
            font-size: 13px;
            line-height: 1.6;
        }

        .support-chat-messages {
            flex: 1;
            min-height: 260px;
            overflow-y: auto;
            padding: 18px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .support-chat-empty {
            color: rgba(255,255,255,.65);
            text-align: center;
            margin: auto 0;
            font-size: 13px;
            line-height: 1.7;
            padding: 18px 16px;
            border: 1px dashed rgba(200,162,107,.28);
            border-radius: 14px;
            background: rgba(200,162,107,.05);
        }

        .support-chat-row {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .support-chat-row.customer {
            align-items: flex-end;
        }

        .support-chat-row.staff {
            align-items: flex-start;
        }

        .support-chat-bubble {
            max-width: 78%;
            padding: 10px 14px;
            border-radius: 14px;
            line-height: 1.55;
            font-size: 13px;
            word-break: break-word;
            box-shadow: 0 10px 20px rgba(0,0,0,.12);
        }

        .support-chat-row.customer .support-chat-bubble {
            background: #c8a26b;
            color: #fff;
            border-bottom-right-radius: 3px;
        }

        .support-chat-row.staff .support-chat-bubble {
            background: rgba(255,255,255,.13);
            color: #fff;
            border-bottom-left-radius: 3px;
        }

        .support-chat-time {
            font-size: 10px;
            color: rgba(255,255,255,.38);
        }

        .support-chat-input-row {
            border-top: 1px solid rgba(255,255,255,.08);
            padding: 14px;
            display: flex;
            gap: 8px;
            background: rgba(255,255,255,.025);
        }

        .support-chat-input-row input {
            flex: 1;
            background: rgba(255,255,255,.08);
            border: 1px solid rgba(200,162,107,.26);
            border-radius: 12px;
            color: #fff;
            padding: 12px 14px;
            font-size: 13px;
            outline: none;
            transition: border-color .2s ease, box-shadow .2s ease, background .2s ease;
        }

        .support-chat-input-row input:focus {
            border-color: rgba(200,162,107,.6);
            box-shadow: 0 0 0 3px rgba(200,162,107,.14);
            background: rgba(255,255,255,.1);
        }

        .support-chat-input-row button {
            border: none;
            border-radius: 12px;
            width: 48px;
            background: linear-gradient(135deg, #c8a26b, #b88e54);
            color: #fff;
            cursor: pointer;
            box-shadow: 0 12px 24px rgba(200,162,107,.2);
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .support-chat-input-row button:hover {
            transform: translateY(-1px);
            box-shadow: 0 16px 26px rgba(200,162,107,.26);
        }

        .support-chat-login-box {
            text-align: center;
            background: linear-gradient(180deg, rgba(255,255,255,.06), rgba(255,255,255,.03));
            border: 1px solid rgba(200,162,107,.18);
            border-radius: 18px;
            padding: 30px 24px;
            box-shadow: 0 22px 45px rgba(8, 4, 1, .24);
        }

        .support-chat-login-icon {
            margin: 0 auto 16px;
        }

        .support-chat-login-box p {
            color: rgba(255,255,255,.72);
            margin: 0 0 18px;
            line-height: 1.7;
        }

        .support-chat-login-btn {
            display: inline-block;
            background: linear-gradient(135deg, #c8a26b, #b88e54);
            color: #fff;
            text-decoration: none;
            padding: 11px 18px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            box-shadow: 0 12px 24px rgba(200,162,107,.2);
        }

        .support-chat-login-btn:hover {
            color: #fff;
            background: #b78f56;
        }
    </style>

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
    <script src="{{ asset('js/main.js') }}"></script>
    <script>
        // FAQ accordion
        document.querySelectorAll('.faq-question').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var item = this.closest('.faq-item');
                var isOpen = item.classList.contains('open');
                document.querySelectorAll('.faq-item').forEach(function (i) { i.classList.remove('open'); });
                if (!isOpen) item.classList.add('open');
            });
        });

        // User dropdown
        var btn = document.getElementById('userMenuBtn');
        var menu = document.getElementById('userDropdownMenu');
        if (btn && menu) {
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                menu.classList.toggle('active');
            });
            document.addEventListener('click', function () { menu.classList.remove('active'); });
        }

        @auth
        var supportChatLastId = 0;
        var supportChatPoller = null;

        function renderSupportChatEmpty() {
            return '<div class="support-chat-empty">Bắt đầu hội thoại. Nhân viên sẽ phản hồi sớm nhất có thể.</div>';
        }

        function resetSupportChatView() {
            var container = document.getElementById('supportChatMessages');
            if (!container) return;

            supportChatLastId = 0;
            container.innerHTML = renderSupportChatEmpty();
        }

        function appendSupportMessage(msg) {
            var container = document.getElementById('supportChatMessages');
            if (!container) return;

            var empty = container.querySelector('.support-chat-empty');
            if (empty) {
                empty.remove();
            }

            var row = document.createElement('div');
            row.className = 'support-chat-row ' + msg.sender;

            var bubble = document.createElement('div');
            bubble.className = 'support-chat-bubble';
            bubble.textContent = msg.message;

            var time = document.createElement('div');
            time.className = 'support-chat-time';
            time.textContent = msg.created_at ? msg.created_at.substring(11, 16) : '';

            row.appendChild(bubble);
            row.appendChild(time);
            container.appendChild(row);
            container.scrollTop = container.scrollHeight;
        }

        function loadSupportMessages() {
            fetch('{{ route("chat.messages") }}?after=' + supportChatLastId, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function (res) { return res.json(); })
            .then(function (payload) {
                // Tương thích ngược nếu endpoint cũ vẫn trả mảng thuần.
                if (Array.isArray(payload)) {
                    payload = { reset: false, messages: payload };
                }

                if (payload.reset) {
                    resetSupportChatView();
                    return;
                }

                (payload.messages || []).forEach(function (msg) {
                    appendSupportMessage(msg);
                    if (msg.id > supportChatLastId) supportChatLastId = msg.id;
                });
            });
        }

        function sendSupportMessage() {
            var input = document.getElementById('supportChatInput');
            if (!input) return;

            var message = input.value.trim();
            if (!message) return;

            input.value = '';

            fetch('{{ route("chat.send") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ message: message })
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                appendSupportMessage({
                    message: message,
                    sender: 'customer',
                    created_at: data.created_at
                });
                if (data.id > supportChatLastId) supportChatLastId = data.id;
            });
        }

        loadSupportMessages();
        supportChatPoller = setInterval(loadSupportMessages, 4000);
        @endauth
    </script>
@include('components.ai-bot-widget')
</body>
</html>
