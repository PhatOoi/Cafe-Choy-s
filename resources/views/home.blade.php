<!DOCTYPE html>
<html lang="en">

<head>
    <title>Choy's Cafe</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Josefin+Sans:400,700" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Great+Vibes" rel="stylesheet">

    <link rel="stylesheet" href="css/open-iconic-bootstrap.min.css">
    <link rel="stylesheet" href="css/animate.css">

    <link rel="stylesheet" href="css/owl.carousel.min.css">
    <link rel="stylesheet" href="css/owl.theme.default.min.css">
    <link rel="stylesheet" href="css/magnific-popup.css">

    <link rel="stylesheet" href="css/aos.css">

    <link rel="stylesheet" href="css/ionicons.min.css">

    <link rel="stylesheet" href="css/bootstrap-datepicker.css">
    <link rel="stylesheet" href="css/jquery.timepicker.css">


    <link rel="stylesheet" href="css/flaticon.css">
    <link rel="stylesheet" href="css/icomoon.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark ftco_navbar bg-dark ftco-navbar-light" id="ftco-navbar">
        <div class="container">

            <!-- LOGO -->
            <a class="navbar-brand mr-3" href="{{ url('/') }}">
                <img src="/images/logo.png" style="height:72px;width:auto;object-fit:contain;">
            </a>

            @include('components.search-bar')

            <!-- MOBILE BUTTON -->
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#ftco-nav">
                <span class="oi oi-menu"></span> Menu
            </button>

            <div class="collapse navbar-collapse" id="ftco-nav">
                <ul class="navbar-nav ml-auto">

                    <!-- MENU ITEMS -->
                    <li class="nav-item active"><a href="{{ url('/') }}" class="nav-link">Trang chủ</a></li>
                    @if(!(auth()->check() && auth()->user()->isStaff()))
                        <li class="nav-item"><a href="{{ url('/menu') }}" class="nav-link">Menu</a></li>
                    @endif
                    @auth
                        @if(!auth()->user()->isStaff())
                            <li class="nav-item"><a href="{{ route('orders.history') }}" class="nav-link">Lịch sử đơn hàng</a></li>
                        @endif
                    @endauth
                    @guest
                        <li class="nav-item">
                            <a href="{{ url('/login') }}" class="nav-link">Đăng nhập</a>
                        </li>
                    @endguest
                    <li class="nav-item"><a href="{{ route('support') }}" class="nav-link">Hỗ trợ</a></li>
                    <!-- SPACER -->
                    <li class="nav-item flex-spacer"></li>

                    <!-- CART -->
                    <li class="nav-item cart">
                        <a href="/cart" class="nav-link">
                            <span class="icon icon-shopping_cart"></span>
                            <span class="bag">
                                <small id="cart-count">{{ $cartCount ?? 0 }}</small>
                            </span>
                        </a>
                    </li>

                    <!-- USER DROPDOWN -->
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
                                                    <img src="{{ Auth::user()->avatar_url
                                                        ? asset('storage/' . Auth::user()->avatar_url)
                                                        : asset('images/user.jpg') }}"
                                                        class="dropdown-avatar">

                                                    <div class="user-details">
                                                        <p class="user-name">{{ Auth::user()->name }}</p>
                                                        <p class="user-role">
                                                            @if(Auth::user()->role_id == 1)
                                                            <a href="/admin" class="dropdown-link">
                                                                <i class="fas fa-cog"></i><span>Quản trị</span>
                                                            </a>
                                                            @endif

                                                            @if(in_array(Auth::user()->role_id, [1, 2]))
                                                            <a href="{{ route('staff.dashboard') }}" class="dropdown-link">
                                                                <i class="fas fa-clipboard-list"></i><span>Trang nhân viên</span>
                                                            </a>
                                                            @endif
                                                        </p>
                                                    </div>
                                                </div>

                                                <div class="dropdown-divider"></div>

                                                <a href="/profile" class="dropdown-link">Hồ sơ</a>

                                                @if(Auth::user()->role_id == 1)
                                                    <a href="/admin" class="dropdown-link">Quản trị</a>
                                                @endif

                                                <div class="dropdown-divider"></div>

                                                <form id="logout-form" action="{{ url('/logout') }}" method="POST">
                                                    @csrf
                                                </form>

                                                <a href="#" class="dropdown-link logout-link"
                                                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
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
    <!-- END nav -->

    {{-- ── Hero slider: 3 slide cà phê banner với nền nh chất lượng cao ── --}}
    <section class="home-slider owl-carousel">
        <div class="slider-item" style="background-image: url(images/bg_1.jpg);">
            <div class="overlay"></div>
            <div class="container">
                <div class="row slider-text justify-content-center align-items-center" data-scrollax-parent="true">

                    <div class="col-md-8 col-sm-12 text-center ftco-animate">
                        <span class="subheading">Chào mừng bạn</span>
                        <h1 class="mb-4">Trải nghiệm cà phê tuyệt vời nhất</h1>
                        <p class="mb-4 mb-md-5">Một dòng sông nhỏ tên Duden chảy ngang qua quán, mang đến nguồn cảm hứng
                            và sự tươi mới cho từng ly nước.</p>
                        <p><a href="/menu" class="btn btn-white btn-outline-white p-3 px-xl-4 py-xl-3">Xem thực đơn</a>
                        </p>
                    </div>

                </div>
            </div>
        </div>

        <div class="slider-item" style="background-image: url(images/bg_2.jpg);">
            <div class="overlay"></div>
            <div class="container">
                <div class="row slider-text justify-content-center align-items-center" data-scrollax-parent="true">

                    <div class="col-md-8 col-sm-12 text-center ftco-animate">
                        <span class="subheading">Chào mừng bạn</span>
                        <h1 class="mb-4">Hương vị tuyệt vời &amp; không gian đẹp</h1>
                        <p class="mb-4 mb-md-5">Một dòng sông nhỏ tên Duden chảy ngang qua quán, mang đến nguồn cảm hứng
                            và sự tươi mới cho từng ly nước.</p>
                        <p><a href="/menu" class="btn btn-white btn-outline-white p-3 px-xl-4 py-xl-3">Xem thực đơn</a>
                        </p>
                    </div>

                </div>
            </div>
        </div>

        <div class="slider-item" style="background-image: url(images/bg_3.jpg);">
            <div class="overlay"></div>
            <div class="container">
                <div class="row slider-text justify-content-center align-items-center" data-scrollax-parent="true">

                    <div class="col-md-8 col-sm-12 text-center ftco-animate">
                        <span class="subheading">Chào mừng bạn</span>
                        <h1 class="mb-4">Nóng hổi, thơm ngậy, sẵn sàng phục vụ</h1>
                        <p class="mb-4 mb-md-5">Một dòng sông nhỏ tên Duden chảy ngang qua quán, mang đến nguồn cảm hứng
                            và sự tươi mới cho từng ly nước.</p>
                        <p><a href="/menu" class="btn btn-white btn-outline-white p-3 px-xl-4 py-xl-3">Xem thực đơn</a>
                        </p>
                    </div>

                </div>
            </div>
        </div>
    </section>

    

    {{-- ── Giới thiệu quán: ảnh nền + mô tả Choy's Cafe ── --}}
    <section class="ftco-about d-md-flex">
        <div class="one-half img" style="background-image: url(images/about.jpg);"></div>
        <div class="one-half ftco-animate">
            <div class="overlap">
                <div class="heading-section ftco-animate ">
                    <span class="subheading">Choy's Cafe</span>
                    <br>
                    <h2 class="mb-4">Niềm tự hào của chúng tôi</h2>
                </div>
                <div>
                    <p>Niềm tự hào của quán chúng tôi không chỉ nằm ở cà phê, mà còn ở sự đa dạng trong từng loại thức
                        uống. Từ cà phê đậm đà, trà thanh mát đến các loại nước trái cây tươi ngon – tất cả đều được pha
                        chế kỹ lưỡng từ nguyên liệu chất lượng cao.Chúng tôi luôn không ngừng sáng tạo để mang đến cho
                        khách hàng nhiều lựa chọn phong phú, phù hợp với mọi sở thích và nhu cầu. Mỗi ly nước không chỉ
                        là một thức uống giải khát, mà còn là sự kết hợp của hương vị, cảm xúc và trải nghiệm.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ── 3 ứng dụng chọn: đặt hàng dễ, giao nhanh, phục vụ tốt ── --}}
    <section class="ftco-section ftco-services">
        <div class="container">
            <div class="row">
                <div class="col-md-4 ftco-animate">
                    <div class="media d-block text-center block-6 services">
                        <div class="icon d-flex justify-content-center align-items-center mb-5">
                            <span class="flaticon-choices"></span>
                        </div>
                        <div class="media-body">
                            <h3 class="heading">Dễ Dàng Đặt Hàng</h3>
                            <p>Mang đến trải nghiệm đặt hàng nhanh chóng và tiện lợi, giúp bạn dễ dàng chọn món yêu
                                thích chỉ trong vài bước đơn giản.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 ftco-animate">
                    <div class="media d-block text-center block-6 services">
                        <div class="icon d-flex justify-content-center align-items-center mb-5">
                            <span class="flaticon-coffee-cup"></span>
                        </div>
                        <div class="media-body">
                            <h3 class="heading">Phục Vụ Nhanh Tại Quán</h3>
                            <p>Đội ngũ luôn chuẩn bị món nhanh chóng và đúng vị để bạn nhận đồ uống tại quầy trong thời gian ngắn nhất.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 ftco-animate">
                    <div class="media d-block text-center block-6 services">
                        <div class="icon d-flex justify-content-center align-items-center mb-5">
                            <span class="flaticon-coffee-bean"></span>
                        </div>
                        <div class="media-body">
                            <h3 class="heading">Chất Lượng Sản Phẩm</h3>
                            <p>Cung cấp những sản phẩm chất lượng cao, được chọn lọc kỹ lưỡng từ nguyên liệu tốt nhất.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="ftco-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 pr-md-5">
                    <div class="heading-section text-md-right ftco-animate">
                        <span class="subheading">Khám Phá</span>
                        <br>
                        <h2 class="mb-4">Thực Đơn</h2>
                        <p class="mb-4">Mỗi món nước đều được pha chế tỉ mỉ, kết hợp hương vị độc đáo nhằm mang đến cho
                            bạn trải nghiệm mới mẻ và đầy cảm hứng. Hãy để mỗi lần ghé quán là một hành trình khám phá
                            hương vị thú vị.</p>
                        <p><a href="/menu" class="btn btn-primary btn-outline-primary px-4 py-3">Xem toàn bộ thực
                                đơn</a></p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="menu-entry">
                                <a href="/menu" class="img" style="background-image: url(images/menu-1.jpg);"></a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="menu-entry mt-lg-4">
                                <a href="/menu" class="img" style="background-image: url(images/menu-2.jpg);"></a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="menu-entry">
                                <a href="/menu" class="img" style="background-image: url(images/menu-3.jpg);"></a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="menu-entry mt-lg-4">
                                <a href="/menu" class="img" style="background-image: url(images/drink-5.jpg);"></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="ftco-counter ftco-bg-dark img" id="section-counter" style="background-image: url(images/bg_2.jpg);"
        data-stellar-background-ratio="0.5">
        <div class="overlay"></div>
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-10">
                    <div class="row">
                        <div class="col-md-6 col-lg-3 d-flex justify-content-center counter-wrap ftco-animate">
                            <div class="block-18 text-center">
                                <div class="text">
                                    <div class="icon"><span class="flaticon-coffee-cup"></span></div>
                                    <strong class="number" data-number="56">0</strong>
                                    <span>Số Chi Nhánh Quán</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3 d-flex justify-content-center counter-wrap ftco-animate">
                            <div class="block-18 text-center">
                                <div class="text">
                                    <div class="icon"><span class="flaticon-coffee-cup"></span></div>
                                    <strong class="number" data-number="21">0</strong>
                                    <span>Giải Thưởng</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3 d-flex justify-content-center counter-wrap ftco-animate">
                            <div class="block-18 text-center">
                                <div class="text">
                                    <div class="icon"><span class="flaticon-coffee-cup"></span></div>
                                    <strong class="number" data-number="10567">0</strong>
                                    <span>Khách hàng hạnh phúc</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3 d-flex justify-content-center counter-wrap ftco-animate">
                            <div class="block-18 text-center">
                                <div class="text">
                                    <div class="icon"><span class="flaticon-coffee-cup"></span></div>
                                    <strong class="number" data-number="254">0</strong>
                                    <span>Nhân Viên</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    {{-- gg map --}}
    <section class="ftco-appointment">
        <div class="overlay"></div>
        <div class="container-wrap">
            <div class="row no-gutters d-md-flex align-items-stretch">

                <!-- MAP -->
                <div class="col-md-6 d-flex">
                    <iframe
                        src="https://www.google.com/maps?q=Cao%20Đẳng%20Kỹ%20Thuật%20Du%20Lịch%20Sài%20Gòn&hl=vi&z=16&output=embed"
                        width="100%" height="100%" style="border:0; min-height:400px;" allowfullscreen=""
                        loading="lazy">
                    </iframe>
                </div>

                <!-- QUẢNG CÁO -->
                <div class="col-md-6 d-flex align-items-center intro-ad">
                    <div class="ad-content" style="text-align:center;">
                        <span style="display:block;font-family:'Great Vibes',cursive;font-size:clamp(3rem,8vw,5.5rem);color:#c9a96e;font-weight:400;line-height:1.1;">Choy's Cafe</span>
                        <div class="heading-section ftco-animate ">
                            <div  class="mb-4" style="font-size: 40px; font-weight: 900;">
                            LỰA CHỌN TỐT NHẤT</div>
                        </div>
                        <div style="font-family:'Inter',sans-serif;font-size:0.98rem;color:#bdbdbd;opacity:0.65;max-width:600px;margin:0 auto 0;">Không chỉ là nơi thưởng thức cà phê, chúng tôi mang đến không gian thư giãn, hương vị tuyệt hảo và trải nghiệm đáng nhớ cho mọi khách hàng.</div>
                    </div>
                </div>

            </div>
        </div>
    </section>
    <footer class="coffee-footer">
        <!-- Newsletter Section -->
        <div class="newsletter-section">
            <div class="container">
                <div class="newsletter-content">
                    <h3>Nhận ưu đãi đặc biệt</h3>
                    <p>Đăng ký để nhận thông tin về cà phê mới và ưu đãi độc quyền</p>
                    <div class="newsletter-form">
                        <input type="text" placeholder="Nhập tên người dùng" id="emailInput">
                        <button onclick="subscribeNewsletter()">Đăng ký</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Footer -->
        <div class="main-footer">
            <div class="container">
                <div class="footer-grid">
                    <!-- Brand -->
                    <div class="footer-brand">
                        <div class="brand-header"></div>
                        <h2 class="footer-logo">
                            <img src="/images/logo.png" alt="logo">Choy's Cafe
                        </h2>
                        <p> Hân hạnh đồng hành cùng quý khách!.</p>
                        <div class="social-links">
                            <a href="https://www.facebook.com/share/1CvQdbW463/?mibextid=wwXIfr" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                            <a href="#" aria-label="TikTok"><i class="fab fa-tiktok"></i></a>
                        </div>
                    </div>

                    <!-- Quick Links -->
                    <div class="footer-links">
                        <h4>Khám phá</h4>
                        <ul>
                            <li><a href="#">Menu</a></li>
                            <li><a href="#">Đặt hàng online</a></li>
                            <li><a href="#">Tuyển dụng</a></li>
                        </ul>
                    </div>

                    <!-- Services -->
                    <div class="footer-links">
                        <h4>Dịch vụ</h4>
                        <ul>
                            <li><a href="#">Ship tận nơi</a></li>
                            <li><a href="#">Catering</a></li>
                            <li><a href="#">Thẻ thành viên</a></li>
                        </ul>
                    </div>

                    <!-- Contact -->
                    <div class="footer-contact">
                        <h4>Liên hệ</h4>
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <span>+84 346901474</span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-clock"></i>
                            <span>8:00 - 24:00</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Copyright -->
        <div class="copyright">
            <div class="container">
                <p>&copy; 2026 Choy's Cafe. Tất cả quyền được bảo lưu.</p>
            </div>
        </div>
    </footer>

    <style>
        /* GRID ĐỀU HƠN */
        .footer-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 40px;
            align-items: flex-start;
        }

        /* BRAND HEADER */
        .brand-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
        }

        .brand-header img {
            width: 45px;
            height: 45px;
            object-fit: contain;
        }

        .footer-brand h2 {
            margin: 0;
            font-size: 1.8rem;
        }

        /* CĂN ĐỀU CÁC CỘT */
        .footer-links,
        .footer-contact {
            padding-top: 10px;
        }

        /* LINE HEIGHT ĐẸP HƠN */
        .footer-links ul li {
            line-height: 1.8;
        }

        /* SOCIAL ICON CÂN */
        .social-links {
            margin-top: 10px;
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .footer-grid {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .brand-header {
                justify-content: center;
            }
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

        .intro-ad {
            background: linear-gradient(135deg, #1a1a1a, #000);
            color: #fff;
            padding: 40px;
        }

        .ad-content {
            max-width: 500px;
            margin: auto;
        }

        .ad-content h2 {
            font-size: 2.2rem;
            color: #e8a271;
            font-weight: 700;
        }

        .ad-content h4 {
            margin-bottom: 15px;
            opacity: 0.9;
        }

        .ad-content p {
            font-size: 1.1rem;
            opacity: 0.8;
        }

        /* AVATAR */
        /* FIX NAVBAR ALIGN */

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

        /* DROPDOWN */
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

        /* SHOW HOVER */
        .user-dropdown:hover .dropdown-menu {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        /* USER INFO */
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

        /* ITEM */
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

        /* ANIMATION */
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

        /* === COFFEE FOOTER STYLES === */
        .coffee-footer {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            color: #ffffff;
            background: linear-gradient(135deg, #1a1a1a 0%, #0d0d0d 100%);
            line-height: 1.6;
            margin-top: 50px;
            /* Khoảng cách với nội dung chính */
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Newsletter */
        .newsletter-section {
            background: rgba(255, 107, 0, 0.1);
            padding: 60px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .newsletter-content {
            text-align: center;
            max-width: 600px;
            margin: 0 auto;
        }

        .newsletter-content h3 {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 16px;
            background: linear-gradient(45deg, #ffffff, #f1af80);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .newsletter-content p {
            font-size: 1.1rem;
            opacity: 0.9;
            margin-bottom: 32px;
        }

        .newsletter-form {
            display: flex;
            max-width: 400px;
            margin: 0 auto;
            gap: 12px;
        }

        .newsletter-form input {
            flex: 1;
            padding: 16px 20px;
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 50px;
            background: rgba(255, 255, 255, 0.05);
            color: #ffffff;
            font-size: 1rem;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }

        .newsletter-form input::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .newsletter-form input:focus {
            outline: none;
            border-color: #e5ab81;
            box-shadow: 0 0 0 4px rgba(255, 107, 0, 0.1);
        }

        .newsletter-form button {
            padding: 16px 28px;
            background: linear-gradient(135deg, #a56a49, #c18a68);
            border: none;
            border-radius: 50px;
            color: #fffaf5;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 6px 20px rgba(165, 106, 73, 0.22);
        }

        .newsletter-form button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 24px rgba(165, 106, 73, 0.28);
            background: linear-gradient(135deg, #965d40, #b97b58);
        }

        /* Main Footer */
        .main-footer {
            padding: 60px 0 40px;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 40px;
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
            background: #d9b69e;
            transform: translateY(-3px);
        }

        /* Links */
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

        .footer-links ul {
            list-style: none;
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

        /* Copyright */
        .copyright {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding: 20px 0;
            text-align: center;
        }

        .copyright p {
            opacity: 0.7;
            font-size: 0.9rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .newsletter-form {
                flex-direction: column;
            }

            .footer-grid {
                grid-template-columns: 1fr;
                gap: 30px;
                text-align: center;
            }

            .newsletter-content h3 {
                font-size: 1.8rem;
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

    <script>
        function subscribeNewsletter() {
    const emailInput = document.getElementById('emailInput');
    if (!emailInput) return;

    const email = emailInput.value.trim();
    const button = emailInput.nextElementSibling;

    if (!email || email.length < 3) {
        button.style.background = '#ef4444';
        setTimeout(() => button.style.background = '', 500);
        return;
    }

    fetch('/subscribe', {
        method: 'POST',
        credentials: 'include', // 🔥 QUAN TRỌNG
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ email: email })
    })
    .then(res => {
        if (!res.ok) throw new Error('Server error');
        return res.json();
    })
    .then(data => {
        if (!data.success) throw new Error(data.error || 'Fail');

        button.textContent = 'Đã đăng ký!';
        button.style.background = '#10b981';
        emailInput.value = '';

        setTimeout(() => {
            button.textContent = 'Đăng ký';
            button.style.background = 'linear-gradient(45deg, #ff6b00, #ff8c42)';
        }, 2000);
    })
    .catch(err => {
        console.error(err);

        button.textContent = 'Lỗi!';
        button.style.background = '#ef4444';

        setTimeout(() => {
            button.textContent = 'Đăng ký';
            button.style.background = 'linear-gradient(45deg, #ff6b00, #ff8c42)';
        }, 2000);
    });
}

        // Enter key
        document.getElementById('emailInput').addEventListener('keypress', function (e) {
            if (e.key === 'Enter') subscribeNewsletter();
        });

        // ===== USER DROPDOWN MENU =====
        document.addEventListener('DOMContentLoaded', function () {
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
                backToTopBtn.addEventListener('click', function () {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                });
                window.addEventListener('scroll', toggleBackToTopButton, { passive: true });
            }

            if (userMenuBtn && userDropdownMenu) {
                // Show dropdown on click
                userMenuBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    userDropdownMenu.classList.toggle('active');
                    userMenuBtn.classList.toggle('active');
                });

                // Keep dropdown open when hovering
                dropdownContainer.addEventListener('mouseenter', function () {
                    userDropdownMenu.classList.add('active');
                    userMenuBtn.classList.add('active');
                });

                dropdownContainer.addEventListener('mouseleave', function () {
                    userDropdownMenu.classList.remove('active');
                    userMenuBtn.classList.remove('active');
                });

                // Close dropdown when clicking outside
                document.addEventListener('click', function (e) {
                    if (!dropdownContainer.contains(e.target)) {
                        userDropdownMenu.classList.remove('active');
                        userMenuBtn.classList.remove('active');
                    }
                });

                // Close dropdown when clicking on a link
                const links = userDropdownMenu.querySelectorAll('.dropdown-link:not(.logout-link)');
                links.forEach(link => {
                    link.addEventListener('click', function () {
                        userDropdownMenu.classList.remove('active');
                        userMenuBtn.classList.remove('active');
                    });
                });
            }
        });
    </script>


    <!-- loader -->
    <div id="ftco-loader" class="show fullscreen"><svg class="circular" width="48px" height="48px">
            <circle class="path-bg" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke="#eeeeee" />
            <circle class="path" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke-miterlimit="10"
                stroke="#F96D00" />
        </svg></div>


    <script src="js/footer.js"></script>
    <script src="js/jquery.min.js"></script>
    <script src="js/jquery-migrate-3.0.1.min.js"></script>
    <script src="js/popper.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/jquery.easing.1.3.js"></script>
    <script src="js/jquery.waypoints.min.js"></script>
    <script src="js/jquery.stellar.min.js"></script>
    <script src="js/owl.carousel.min.js"></script>
    <script src="js/jquery.magnific-popup.min.js"></script>
    <script src="js/aos.js"></script>
    <script src="js/jquery.animateNumber.min.js"></script>
    <script src="js/bootstrap-datepicker.js"></script>
    <script src="js/jquery.timepicker.min.js"></script>
    <script src="js/scrollax.min.js"></script>
    <script
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBVWaKrjvy3MaE7SQ74_uJiULgl1JY0H2s&sensor=false"></script>
    <script src="js/google-map.js"></script>
    <script src="js/main.js"></script>

@include('components.ai-bot-widget')
</body>

</html>