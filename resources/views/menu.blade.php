<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        const isLoggedIn = {{ auth()->check() ? 'true' : 'false' }};
    </script>
    <title>Menu — Choy's Cafe</title>

    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600&family=DM+Sans:wght@300;400;500&family=Great+Vibes&display=swap"
        rel="stylesheet">

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
</head>

<body>

    <!-- END nav -->

    {{-- ===== NAVBAR GIỮ NGUYÊN ===== --}}
    <div>
        <nav class="navbar navbar-expand-lg navbar-dark ftco_navbar bg-dark ftco-navbar-light" id="ftco-navbar">
    <div class="container">

        <!-- LOGO -->
        <a class="navbar-brand mr-3" href="{{ url('/') }}">
            <img src="/images/logo.png"
                 style="height:72px;width:auto;object-fit:contain;">
        </a>

        @include('components.search-bar')

        <!-- MOBILE BUTTON -->
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#ftco-nav">
            <span class="oi oi-menu"></span> Menu
        </button>

        <div class="collapse navbar-collapse" id="ftco-nav">
            <ul class="navbar-nav ml-auto">

                <!-- MENU ITEMS -->
                <li class="nav-item"><a href="{{ url('/') }}" class="nav-link">Trang chủ</a></li>
                <li class="nav-item active"><a href="{{ url('/menu') }}" class="nav-link">Menu</a></li>
                
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
                        <!-- USER AVATAR BUTTON -->
                        <button class="user-avatar-btn" type="button" id="userMenuBtn">
                            @if(Auth::user()->avatar && file_exists(public_path('storage/' . Auth::user()->avatar)))
                                <img src="{{ asset('storage/' . Auth::user()->avatar) }}" 
                                     alt="{{ Auth::user()->name }}"
                                     class="user-avatar">
                            @else
                                <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=ff6b00&color=fff&size=48"
                                     alt="{{ Auth::user()->name }}"
                                     class="user-avatar">
                            @endif
                        </button>

                        <!-- USER DROPDOWN MENU -->
                        <div class="user-dropdown-menu" id="userDropdownMenu">
                            <!-- Header Info -->
                            <div class="dropdown-header-info">
                                @if(Auth::user()->avatar && file_exists(public_path('storage/' . Auth::user()->avatar)))
                                    <img src="{{ asset('storage/' . Auth::user()->avatar) }}" 
                                         alt="{{ Auth::user()->name }}"
                                         class="dropdown-avatar">
                                @else
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=ff6b00&color=fff&size=60"
                                         alt="{{ Auth::user()->name }}"
                                         class="dropdown-avatar">
                                @endif
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

                            <!-- Divider -->
                            <div class="dropdown-divider"></div>

                            <!-- Menu Items -->
                            <a href="/profile" class="dropdown-link">
                                <i class="fas fa-user"></i>
                                <span>Hồ sơ cá nhân</span>
                            </a>

                            @if(Auth::user()->role === 'admin')
                            <a href="/admin" class="dropdown-link">
                                <i class="fas fa-cog"></i>
                                <span>Quản trị</span>
                            </a>
                            @endif

                            <!-- Divider -->
                            <div class="dropdown-divider"></div>

                            <!-- Logout -->
                            <form id="logout-form" action="{{ url('/logout') }}" method="POST" style="display:none;">
                                @csrf
                            </form>
                            <a href="#" class="dropdown-link logout-link" 
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>Đăng xuất</span>
                            </a>
                        </div>
                    </div>
                </li>
				@else
                <li class="nav-item">
                    <a href="{{ url('/login') }}" class="nav-link btn-login">
                        <i class="fas fa-sign-in-alt mr-2"></i>Đăng nhập
                    </a>
                </li>
				@endif

            </ul>
        </div>
    </div>
</nav>
    </div>

    {{-- ===== HERO ===== --}}
    <section class="menu-hero">
        <div class="menu-hero-inner">
            <p><br></p>
            <p class="menu-tagline">Thức uống thủ công</p>
            <h1 class="menu-title">Thực đơn</h1>
            <div class="menu-divider">
                <span class="divider-line"></span>
                <span class="divider-dot">✦</span>
                <span class="divider-line"></span>
            </div>
            <p class="menu-sub">Mỗi ly được pha chế với tâm huyết &amp; nguyên liệu chọn lọc</p>
        </div>
    </section>

    {{-- ===== GRID SẢN PHẨM THEO PHÂN LOẠI ===== --}}
    <section class="menu-section">
        @foreach($categories as $category)
            <div class="menu-category-block">
                <h2 class="menu-category-title" style="
                            border-bottom: 2px solid #c8b8a8;
                            margin-top: 40px;
                            margin-bottom: 24px;
                            padding-bottom: 8px;
                            color: var(--category-title-color, #1a110d);
                            text-align: left;
                        ">
                    {{ $category->name }}
                </h2>
                <div class="menu-grid">
                    @foreach($category->products as $product)
                        <div class="product-card">
                            <div class="card-image-wrap">
                                <img src="{{ asset('images/' . $product->image_url) }}"
                                    onerror="this.src='https://via.placeholder.com/400x280/c8b8a8/ffffff?text=Coffee'"
                                    alt="{{ $product->name }}" class="card-img">
                                <div class="card-shine"></div>
                                @if($product->is_new ?? false)
                                    <span class="card-badge badge-new">Mới</span>
                                @endif
                                @if($product->is_hot ?? false)
                                    <span class="card-badge badge-hot">Bán chạy</span>
                                @endif
                            </div>
                            <div class="card-body">
                                <p class="card-cat">{{ $category->name }}</p>
                                <h3 class="card-name">{{ $product->name }}</h3>
                                @if($product->description ?? false)
                                    <p class="card-desc">{{ Str::limit($product->description, 65) }}</p>
                                @endif
                                <div class="card-footer">
                                    <span class="card-price">
                                        {{ number_format($product->price) }}<span class="price-unit">đ</span>
                                    </span>
                                    @auth
                                        <button class="btn-add-cart" onclick="openModal(
                                                        {{ $product->id }},
                                                        '{{ addslashes($product->name) }}',
                                                        {{ $product->price }},
                                                        '{{ $category->name }}',
                                                        '{{ asset('images/' . $product->image_url) }}',
                                                        {{ $category->id }}
                                                    )">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="9" cy="21" r="1" />
                                                <circle cx="20" cy="21" r="1" />
                                                <path d="M1 1h4l2.68 13.39..." />
                                            </svg>
                                            <span>Thêm vào giỏ</span>
                                        </button>
                                    @else
                                        <button class="btn-add-cart" onclick="redirectLogin()">
                                            <span>Đăng nhập để mua</span>
                                        </button>
                                    @endauth
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        <style>
            body[data-theme="dark"] .menu-category-title {
                color: #fff !important;
                border-bottom: 2px solid #fff !important;
            }

            body[data-theme="light"] .menu-category-title {
                color: #1a110d !important;
                border-bottom: 2px solid #c8b8a8 !important;
            }
        </style>
    </section>

    {{-- ===== MODAL TOPPING ===== --}}
    <div class="modal-backdrop" id="modalBackdrop" onclick="closeModalOutside(event)">
        <div class="modal-sheet" id="modalSheet">

            <div class="sheet-handle"></div>

            {{-- Ảnh sản phẩm --}}
            <div class="sheet-img-wrap" id="sheetImgWrap">
                <img id="sheetImg" src="" alt="">
                <div class="sheet-img-overlay"></div>
                <button class="sheet-close" onclick="closeModal()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                        stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18" />
                        <line x1="6" y1="6" x2="18" y2="18" />
                    </svg>
                </button>
            </div>

            <div class="sheet-body">

                {{-- Tên & giá --}}
                <p class="sheet-cat" id="sheetCat"></p>
                <h2 class="sheet-name" id="sheetName"></h2>

                <div class="price-summary">
                    <div>
                        <p class="price-label">Tổng cộng</p>
                        <p class="price-total" id="priceTotal">0đ</p>
                    </div>
                    <p class="price-base" id="priceBase">0đ</p>
                </div>

                <div class="sheet-divider"></div>

                {{-- Kích cỡ --}}
                <p class="section-label">
                    Kích cỡ <span class="required">*</span>
                </p>
                <div class="size-row">
                    @foreach($sizes as $index => $size)
                        <div class="size-btn {{ $index == 0 ? 'active' : '' }}" data-extra="{{ $size->extra_price }}"
                            onclick="selectSize(this)">
                            <span class="size-letter">{{ $size->name }}</span>
                            <span class="size-price">
                                {{ $size->extra_price > 0 ? '+' . number_format($size->extra_price) . 'đ' : 'Mặc định' }}
                            </span>
                        </div>
                    @endforeach
                </div>

                <div class="sheet-divider"></div>

                {{-- Topping --}}
                <p class="section-label">
                    Topping
                    <span class="optional-badge">Tùy chọn</span>
                </p>

                <div class="topping-grid">
                    @foreach($toppings as $tp)
                        <div class="topping-item" data-price="{{ $tp->price }}" data-id="{{ $tp->id }}"
                            onclick="toggleTopping(this)">
                            <div class="tp-check">
                                <svg width="10" height="10">
                                    <polyline points="2 6 5 9 10 3" />
                                </svg>
                            </div>
                            <div class="tp-info">
                                <span class="tp-name">{{ $tp->name }}</span>
                                <span class="tp-price">+{{ number_format($tp->price) }}đ</span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="sheet-divider"></div>

                {{-- Đường --}}
                <p class="section-label">
                    Đường & Sữa
                    <span class="optional-badge">Tùy chọn</span>
                </p>
                <div class="option-row">
                    @foreach($sugars as $sugar)
                        <div class="option-chip {{ $loop->last ? 'active' : '' }}" data-group="sugar"
                            data-val="{{ $sugar->name }}" onclick="selectOption(this, 'sugar')">
                            {{ $sugar->name }}
                        </div>
                    @endforeach
                </div>

                <div class="sheet-divider"></div>

                {{-- Đá --}}
                <p class="section-label">
                    Đá
                    <span class="optional-badge">Tùy chọn</span>
                </p>
                <div class="option-row">
                    @foreach($ices as $ice)
                        <div class="option-chip {{ $loop->last ? 'active' : '' }}" data-group="ice"
                            data-val="{{ $ice->name }}" onclick="selectOption(this, 'ice')">
                            {{ $ice->name }}
                        </div>
                    @endforeach
                </div>

                <div class="sheet-divider"></div>

                {{-- Ghi chú --}}
                <p class="section-label">Ghi chú</p>
                <textarea class="note-input" id="noteInput" placeholder="Vd: ít ngọt, thêm siro, không đường..."
                    rows="2"></textarea>

                {{-- Số lượng + Thêm giỏ --}}
                <div class="confirm-row">
                    <div class="qty-wrap">
                        <button class="qty-btn" onclick="changeQty(-1)">−</button>
                        <span class="qty-num" id="qtyNum">1</span>
                        <button class="qty-btn" onclick="changeQty(1)">+</button>
                    </div>
                    <button class="btn-confirm" onclick="confirmAddToCart()">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="9" cy="21" r="1" />
                            <circle cx="20" cy="21" r="1" />
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" />
                        </svg>
                        Thêm vào giỏ hàng
                    </button>
                </div>

            </div>
        </div>
    </div>

    {{-- TOAST --}}
    <div class="toast-wrap" id="toastWrap">
        <div class="toast-icon">
            <svg width="10" height="10" viewBox="0 0 12 12" fill="none" stroke="#1a110d" stroke-width="2.5"
                stroke-linecap="round" stroke-linejoin="round">
                <polyline points="2 6 5 9 10 3" />
            </svg>
        </div>
        <span id="toastMsg"></span>
    </div>
    <div class="main-footer">
        <div class="container">
            <div class="footer-grid">
                <!-- Brand -->
                <div class="footer-brand">
                    <h2>Choy's Cafe</h2>
                    <p>Hân hạnh đồng hành cùng quý khách!.</p>
                    <div class="social-links">
                        <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" aria-label="TikTok"><i class="fab fa-tiktok"></i></a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="footer-links">
                    <h4>Khám phá</h4>
                    <ul>
                        <li><a href="#">Menu</a></li>
                        <li><a href="#">Cửa hàng</a></li>
                        <li><a href="#">Đặt hàng online</a></li>
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

    <!-- Copyright -->
    <div class="copyright">
        <div class="container">
            <p>&copy; 2026 Choy's Cafe. Tất cả quyền được bảo lưu.</p>
        </div>
    </div>
    </footer>

    <style>
        .user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #ff6b00;
    transition: 0.3s;
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
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
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
            margin-top: 100px;
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
            background: linear-gradient(45deg, #ffffff, #ff6b00);
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
            border-color: #ff6b00;
            box-shadow: 0 0 0 4px rgba(255, 107, 0, 0.1);
        }

        .newsletter-form button {
            padding: 16px 28px;
            background: linear-gradient(45deg, #ff6b00, #ff8c42);
            border: none;
            border-radius: 50px;
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(255, 107, 0, 0.3);
        }

        .newsletter-form button:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(255, 107, 0, 0.4);
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
            background: #ff6b00;
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
        }
    </style>
    {{-- style menu --}}
    <style>
        /* ===== BASE ===== */
        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        /* ===== HERO ===== */
        .menu-hero {
            background: #1a110d;
            text-align: center;
            padding: 70px 20px 58px;
            position: relative;
            overflow: hidden;
        }

        .menu-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse 70% 50% at 50% -10%, rgba(201, 169, 110, .18), transparent 70%);
            pointer-events: none;
        }

        .menu-hero-inner {
            position: relative;
            z-index: 1;
        }

        .menu-tagline {
            font-family: 'DM Sans', sans-serif;
            font-size: .72rem;
            letter-spacing: .35em;
            text-transform: uppercase;
            color: #c9a96e;
            margin: 0 0 8px;
        }

        .menu-title {
            font-family: 'Great Vibes', cursive;
            font-size: clamp(3rem, 8vw, 5.5rem);
            color: #f0e6d0;
            margin: 0 0 18px;
            font-weight: 400;
            line-height: 1.1;
        }

        .menu-divider {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 14px;
            margin-bottom: 16px;
        }

        .divider-line {
            width: 80px;
            height: 1px;
            background: linear-gradient(to right, transparent, #c9a96e);
        }

        .divider-line:last-child {
            background: linear-gradient(to left, transparent, #c9a96e);
        }

        .divider-dot {
            color: #c9a96e;
            font-size: .65rem;
            opacity: .7;
        }

        .menu-sub {
            font-family: 'DM Sans', sans-serif;
            font-size: .72rem;
            letter-spacing: .15em;
            color: #8b7060;
            text-transform: uppercase;
            margin: 0;
        }

        /* ===== MENU SECTION ===== */
        .menu-section {
            background: #f5f0eb;
            padding: 56px 24px 80px;
            min-height: 50vh;
        }

        .menu-grid {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 28px;
        }

        /* ===== CARD ===== */
        .product-card {
            background: #fff;
            border-radius: 18px;
            overflow: hidden;
            border: 1px solid rgba(201, 169, 110, .15);
            box-shadow: 0 2px 14px rgba(26, 17, 13, .06);
            transition: transform .35s cubic-bezier(.25, .8, .25, 1), box-shadow .35s cubic-bezier(.25, .8, .25, 1);
        }

        .product-card:hover {
            transform: translateY(-7px);
            box-shadow: 0 18px 40px rgba(26, 17, 13, .13);
        }

        .card-image-wrap {
            position: relative;
            aspect-ratio: 4/3;
            overflow: hidden;
            background: #e8ddd4;
        }

        .card-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform .55s cubic-bezier(.25, .8, .25, 1);
        }

        .product-card:hover .card-img {
            transform: scale(1.07);
        }

        .card-shine {
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255, 255, 255, .08), transparent 60%);
            pointer-events: none;
        }

        .card-badge {
            position: absolute;
            top: 12px;
            left: 12px;
            font-family: 'DM Sans', sans-serif;
            font-size: 9px;
            font-weight: 500;
            letter-spacing: .14em;
            text-transform: uppercase;
            padding: 4px 10px;
            border-radius: 20px;
        }

        .badge-new {
            background: #1a110d;
            color: #f0e6d0;
        }

        .badge-hot {
            background: #c0392b;
            color: #fff;
        }

        .card-body {
            padding: 16px 18px 18px;
        }

        .card-cat {
            font-family: 'DM Sans', sans-serif;
            font-size: 10px;
            letter-spacing: .18em;
            text-transform: uppercase;
            color: #b8a090;
            margin: 0 0 4px;
        }

        .card-name {
            font-family: 'Playfair Display', serif;
            font-size: 1.1rem;
            font-weight: 500;
            color: #1a110d;
            margin: 0 0 6px;
            line-height: 1.3;
        }

        .card-desc {
            font-family: 'DM Sans', sans-serif;
            font-size: .8rem;
            color: #a09080;
            margin: 0 0 12px;
            line-height: 1.5;
        }

        .card-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding-top: 12px;
            border-top: 1px solid rgba(201, 169, 110, .2);
        }

        .card-price {
            font-family: 'Playfair Display', serif;
            font-size: 1.05rem;
            font-weight: 500;
            color: #6b3a2a;
        }

        .price-unit {
            font-size: .8rem;
            font-weight: 400;
            margin-left: 1px;
        }

        .btn-add-cart {
            display: flex;
            align-items: center;
            gap: 6px;
            background: #1a110d;
            color: #f0e6d0;
            border: none;
            border-radius: 10px;
            padding: 9px 15px;
            font-family: 'DM Sans', sans-serif;
            font-size: 11px;
            font-weight: 500;
            cursor: pointer;
            transition: background .22s, transform .18s;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .btn-add-cart:hover {
            background: #c9a96e;
            color: #1a110d;
        }

        .btn-add-cart:active {
            transform: scale(.96);
        }

        /* ===== MODAL BACKDROP ===== */
        .modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(15, 8, 5, .6);
            z-index: 9999;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            opacity: 0;
            pointer-events: none;
            transition: opacity .3s ease;
        }

        .modal-backdrop.open {
            opacity: 1;
            pointer-events: all;
        }

        /* ===== MODAL SHEET ===== */
        .modal-sheet {
            background: #fff;
            border-radius: 24px 24px 0 0;
            width: 100%;
            max-width: 560px;
            max-height: 92vh;
            overflow-y: auto;
            transform: translateY(50px);
            transition: transform .38s cubic-bezier(.25, .8, .25, 1);
            scrollbar-width: thin;
            scrollbar-color: #e0d4c8 transparent;
        }

        .modal-backdrop.open .modal-sheet {
            transform: translateY(0);
        }

        .sheet-handle {
            width: 44px;
            height: 4px;
            background: #e0d8d0;
            border-radius: 2px;
            margin: 12px auto 0;
        }

        .sheet-img-wrap {
            position: relative;
            height: 200px;
            background: #d4b896;
            overflow: hidden;
            margin-top: 10px;
        }

        .sheet-img-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .sheet-img-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(26, 17, 13, .3), transparent 60%);
        }

        .sheet-close {
            position: absolute;
            top: 12px;
            right: 12px;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .92);
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1a110d;
            transition: background .2s;
            z-index: 2;
        }

        .sheet-close:hover {
            background: #fff;
        }

        .sheet-body {
            padding: 20px 22px 32px;
        }

        .sheet-cat {
            font-family: 'DM Sans', sans-serif;
            font-size: 10px;
            letter-spacing: .2em;
            text-transform: uppercase;
            color: #b8a090;
            margin: 0 0 4px;
        }

        .sheet-name {
            font-family: 'Playfair Display', serif;
            font-size: 1.4rem;
            font-weight: 500;
            color: #1a110d;
            margin: 0 0 16px;
        }

        .price-summary {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            margin-bottom: 16px;
        }

        .price-label {
            font-family: 'DM Sans', sans-serif;
            font-size: 11px;
            color: #b8a090;
            margin: 0 0 2px;
        }

        .price-total {
            font-family: 'Playfair Display', serif;
            font-size: 1.4rem;
            font-weight: 500;
            color: #1a110d;
            margin: 0;
        }

        .price-base {
            font-family: 'Playfair Display', serif;
            font-size: 1rem;
            color: #c9a96e;
            margin: 0;
        }

        .sheet-divider {
            border: none;
            border-top: 1px solid #f0e8e0;
            margin: 16px 0;
        }

        .section-label {
            font-family: 'DM Sans', sans-serif;
            font-size: 11px;
            font-weight: 500;
            letter-spacing: .18em;
            text-transform: uppercase;
            color: #8b7060;
            margin: 0 0 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .required {
            color: #c0392b;
            font-size: 13px;
            letter-spacing: 0;
        }

        .optional-badge {
            background: #f5efe8;
            color: #b8a090;
            font-size: 9px;
            padding: 2px 8px;
            border-radius: 10px;
            letter-spacing: .1em;
            font-weight: 400;
        }

        /* SIZE */
        .size-row {
            display: flex;
            gap: 10px;
            margin-bottom: 4px;
        }

        .size-btn {
            flex: 1;
            border: 1.5px solid #e0d4c8;
            border-radius: 12px;
            padding: 10px 6px;
            text-align: center;
            cursor: pointer;
            transition: all .2s;
            background: #fff;
        }

        .size-btn.active {
            border-color: #1a110d;
            background: #1a110d;
        }

        .size-letter {
            display: block;
            font-family: 'Playfair Display', serif;
            font-size: 1.1rem;
            font-weight: 500;
            color: #1a110d;
        }

        .size-btn.active .size-letter {
            color: #f0e6d0;
        }

        .size-price {
            display: block;
            font-family: 'DM Sans', sans-serif;
            font-size: 10px;
            color: #b8a090;
            margin-top: 2px;
        }

        .size-btn.active .size-price {
            color: #c9a96e;
        }

        /* OPTIONS (ĐƯỜNG, ĐÁ) */
        .option-row {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 4px;
        }

        .option-chip {
            border: 1.5px solid #e0d4c8;
            border-radius: 20px;
            padding: 6px 14px;
            font-family: 'DM Sans', sans-serif;
            font-size: 12px;
            color: #6b5a4a;
            cursor: pointer;
            transition: all .2s;
            background: #fff;
        }

        .option-chip.active {
            border-color: #c9a96e;
            background: #c9a96e;
            color: #1a110d;
            font-weight: 500;
        }

        /* TOPPING */
        .topping-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-bottom: 4px;
        }

        .topping-item {
            display: flex;
            align-items: center;
            gap: 10px;
            border: 1.5px solid #e0d4c8;
            border-radius: 12px;
            padding: 10px 12px;
            cursor: pointer;
            transition: all .2s;
            background: #fff;
        }

        .topping-item.active {
            border-color: #c9a96e;
            background: #fdf8f2;
        }

        .tp-check {
            width: 20px;
            height: 20px;
            border-radius: 6px;
            border: 1.5px solid #d0c4b4;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: all .2s;
        }

        .tp-check svg {
            display: none;
            stroke: #fff;
        }

        .topping-item.active .tp-check {
            background: #c9a96e;
            border-color: #c9a96e;
        }

        .topping-item.active .tp-check svg {
            display: block;
        }

        .tp-name {
            display: block;
            font-family: 'DM Sans', sans-serif;
            font-size: 12px;
            font-weight: 500;
            color: #1a110d;
        }

        .tp-price {
            display: block;
            font-family: 'DM Sans', sans-serif;
            font-size: 11px;
            color: #b8a090;
        }

        /* NOTE */
        .note-input {
            width: 100%;
            border: 1.5px solid #e0d4c8;
            border-radius: 12px;
            padding: 10px 14px;
            font-family: 'DM Sans', sans-serif;
            font-size: 13px;
            color: #1a110d;
            resize: none;
            outline: none;
            transition: border-color .2s;
            background: #fff;
        }

        .note-input:focus {
            border-color: #c9a96e;
        }

        .note-input::placeholder {
            color: #c0b0a0;
        }

        /* CONFIRM ROW */
        .confirm-row {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: 20px;
        }

        .qty-wrap {
            display: flex;
            align-items: center;
            border: 1.5px solid #e0d4c8;
            border-radius: 12px;
            overflow: hidden;
            flex-shrink: 0;
        }

        .qty-btn {
            width: 38px;
            height: 42px;
            border: none;
            background: #fff;
            color: #1a110d;
            font-size: 20px;
            cursor: pointer;
            transition: background .18s;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'DM Sans', sans-serif;
        }

        .qty-btn:hover {
            background: #f5efe8;
        }

        .qty-num {
            width: 34px;
            text-align: center;
            font-family: 'Playfair Display', serif;
            font-size: 15px;
            font-weight: 500;
            color: #1a110d;
            border-left: 1px solid #e0d4c8;
            border-right: 1px solid #e0d4c8;
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-confirm {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: #1a110d;
            color: #f0e6d0;
            border: none;
            border-radius: 12px;
            height: 42px;
            font-family: 'DM Sans', sans-serif;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: background .22s, transform .15s;
        }

        .btn-confirm:hover {
            background: #c9a96e;
            color: #1a110d;
        }

        .btn-confirm:active {
            transform: scale(.97);
        }

        /* TOAST */
        .toast-wrap {
            position: fixed;
            top: 24px;
            left: 50%;
            transform: translateX(-50%) translateY(-90px);
            background: #1a110d;
            color: #f0e6d0;
            padding: 10px 20px;
            border-radius: 30px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-family: 'DM Sans', sans-serif;
            font-size: 13px;
            font-weight: 500;
            white-space: nowrap;
            z-index: 99999;
            transition: transform .38s cubic-bezier(.25, .8, .25, 1);
            pointer-events: none;
        }

        .toast-wrap.show {
            transform: translateX(-50%) translateY(0);
        }

        .toast-icon {
            width: 20px;
            height: 20px;
            background: #c9a96e;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .menu-section {
                padding: 36px 14px 60px;
            }

            .menu-grid {
                gap: 16px;
            }

            .topping-grid {
                grid-template-columns: 1fr;
            }

            .sheet-body {
                padding: 16px 16px 28px;
            }
        }

        @media (max-width: 480px) {
            .menu-grid {
                grid-template-columns: 1fr;
            }

            .btn-add-cart span {
                display: none;
            }

            .btn-add-cart {
                padding: 9px 12px;
            }
        }
    </style>

    <script>
        var modalState = { productId: null, basePrice: 0, sizeExtra: 0, toppingTotal: 0, qty: 1 };
        var cartTotal = 0;

        function openModal(id, name, price, cat, imgUrl, categoryId) {

            // 🚨 CHẶN CHƯA LOGIN
            if (!isLoggedIn) {
                showToast('⚠️ Vui lòng đăng nhập!');
                setTimeout(() => {
                    window.location.href = "{{ url('/login') }}";
                }, 1200);
                return;
            }

            modalState = { productId: id, basePrice: price, sizeExtra: 0, toppingTotal: 0, qty: 1 };

            document.getElementById('sheetCat').textContent = cat;
            document.getElementById('sheetName').textContent = name;
            document.getElementById('priceBase').textContent = fmtPrice(price);
            document.getElementById('sheetImg').src = imgUrl;
            document.getElementById('qtyNum').textContent = '1';
            document.getElementById('noteInput').value = '';

            var sizeBtns = document.querySelectorAll('.size-btn');
            sizeBtns.forEach((b, i) => b.classList.toggle('active', i === 0));
            modalState.sizeExtra = 0;

            document.querySelectorAll('.option-chip[data-group="sugar"]').forEach((b, i, arr) => {
                b.classList.toggle('active', i === arr.length - 1);
            });

            document.querySelectorAll('.option-chip[data-group="ice"]').forEach((b, i, arr) => {
                b.classList.toggle('active', i === arr.length - 1);
            });

            document.querySelectorAll('.topping-item').forEach(t => t.classList.remove('active'));
            modalState.toppingTotal = 0;

            // Ẩn/hiện topping theo category id
            var toppingGrid = document.querySelector('.topping-grid');
            var toppingLabel = toppingGrid.previousElementSibling;

            var sugarSection = document.querySelector('.option-chip[data-group="sugar"]').closest('.option-row').previousElementSibling;
            var sugarRow = document.querySelector('.option-chip[data-group="sugar"]').closest('.option-row');

            var iceSection = document.querySelector('.option-chip[data-group="ice"]').closest('.option-row').previousElementSibling;
            var iceRow = document.querySelector('.option-chip[data-group="ice"]').closest('.option-row');

            var sizeSection = document.querySelector('.size-row').previousElementSibling;
            var sizeRow = document.querySelector('.size-row');

            // ===== RESET HIỆN TẤT CẢ =====
            [toppingGrid, toppingLabel, sugarSection, sugarRow, iceSection, iceRow, sizeSection, sizeRow].forEach(el => {
                if (el) el.style.display = '';
            });

            // ===== LOGIC MỚI =====
            var productName = document.getElementById('sheetName').textContent || '';
            if (categoryId === 2) {
                // Trà sữa: chỉ hiện topping, ẩn đường & sữa
                sugarSection.style.display = 'none';
                sugarRow.style.display = 'none';
            } else if (categoryId === 3) {
                // Đá xay: ẩn topping, đường, sữa, đá
                toppingGrid.style.display = 'none';
                toppingLabel.style.display = 'none';
                sugarSection.style.display = 'none';
                sugarRow.style.display = 'none';
                iceSection.style.display = 'none';
                iceRow.style.display = 'none';
            } else if (categoryId === 4) {
                // Sinh tố/nước ép: ẩn topping
                toppingGrid.style.display = 'none';
                toppingLabel.style.display = 'none';
                if (productName.toLowerCase().includes('sinh tố')) {
                    // Sinh tố: ẩn luôn đá, đường & sữa
                    sugarSection.style.display = 'none';
                    sugarRow.style.display = 'none';
                    iceSection.style.display = 'none';
                    iceRow.style.display = 'none';
                } else {
                    // Nước ép: ẩn đường & sữa
                    sugarSection.style.display = 'none';
                    sugarRow.style.display = 'none';
                }
            } else if (categoryId === 1) {
                // Cà phê: ẩn topping
                toppingGrid.style.display = 'none';
                toppingLabel.style.display = 'none';
            } else if (categoryId === 5) {
                // Bánh/snack: ẩn tất cả option
                toppingGrid.style.display = 'none';
                toppingLabel.style.display = 'none';
                sugarSection.style.display = 'none';
                sugarRow.style.display = 'none';
                iceSection.style.display = 'none';
                iceRow.style.display = 'none';
                sizeSection.style.display = 'none';
                sizeRow.style.display = 'none';
            }
            updateTotal();

            document.getElementById('modalBackdrop').classList.add('open');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            document.getElementById('modalBackdrop').classList.remove('open');
            document.body.style.overflow = '';
        }

        function closeModalOutside(e) {
            if (e.target === document.getElementById('modalBackdrop')) closeModal();
        }

        function selectSize(el) {
            document.querySelectorAll('.size-btn').forEach(function (b) { b.classList.remove('active'); });
            el.classList.add('active');
            modalState.sizeExtra = parseInt(el.dataset.extra) || 0;
            updateTotal();
        }

        function selectOption(el, group) {
            document.querySelectorAll('.option-chip[data-group="' + group + '"]').forEach(function (b) {
                b.classList.remove('active');
            });
            el.classList.add('active');
        }

        function toggleTopping(el) {
            el.classList.toggle('active');
            var total = 0;
            document.querySelectorAll('.topping-item.active').forEach(function (item) {
                total += parseInt(item.dataset.price) || 0;
            });
            modalState.toppingTotal = total;
            updateTotal();
        }

        function changeQty(d) {
            modalState.qty = Math.max(1, modalState.qty + d);
            document.getElementById('qtyNum').textContent = modalState.qty;
            updateTotal();
        }

        function updateTotal() {
            var unit = modalState.basePrice + modalState.sizeExtra + modalState.toppingTotal;
            document.getElementById('priceTotal').textContent = fmtPrice(unit * modalState.qty);
        }

        function confirmAddToCart() {
            var size = (document.querySelector('.size-btn.active .size-letter') || {}).textContent || 'S';
            var sugar = (document.querySelector('.option-chip[data-group="sugar"].active') || {}).dataset.val || '100%';
            var ice = (document.querySelector('.option-chip[data-group="ice"].active') || {}).dataset.val || 'Bình thường';
            var toppings = Array.from(document.querySelectorAll('.topping-item.active')).map(function (el) {
                return el.querySelector('.tp-name').textContent;
            });
            var note = document.getElementById('noteInput').value.trim();
            var name = document.getElementById('sheetName').textContent;

            fetch('{{ url("/cart/add") }}', {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    product_id: modalState.productId,
                    size: size,
                    sugar: sugar,
                    ice: ice,
                    toppings: toppings,
                    note: note,
                    qty: modalState.qty
                })
            })
                .then(res => res.json())
                .then(data => {

                    if (!data.success) {
                        showToast('❌ ' + (data.message || 'Lỗi server'));
                        return;
                    }

                    closeModal();

                    // 🔥 sửa selector cho đúng
                    document.querySelector('.bag small').textContent = data.cart_count;

                    showToast('Đã thêm vào giỏ hàng!');
                })
        }

        function showToast(msg) {
            document.getElementById('toastMsg').textContent = msg;
            var t = document.getElementById('toastWrap');
            t.classList.add('show');
            setTimeout(function () { t.classList.remove('show'); }, 2800);
        }

        function fmtPrice(n) {
            return n.toLocaleString('vi-VN') + 'đ';
        }
        function redirectLogin() {
            showToast('Vui lòng đăng nhập!');
            setTimeout(() => {
                window.location.href = "{{ url('/login') }}";
            }, 1200);
        }
         document.addEventListener('DOMContentLoaded', function() {
        const userMenuBtn = document.getElementById('userMenuBtn');
        const userDropdownMenu = document.getElementById('userDropdownMenu');
        const dropdownContainer = document.querySelector('.user-dropdown-container');

        if (userMenuBtn && userDropdownMenu) {
            // Show dropdown on click
            userMenuBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                userDropdownMenu.classList.toggle('active');
                userMenuBtn.classList.toggle('active');
            });

            // Keep dropdown open when hovering
            dropdownContainer.addEventListener('mouseenter', function() {
                userDropdownMenu.classList.add('active');
                userMenuBtn.classList.add('active');
            });

            dropdownContainer.addEventListener('mouseleave', function() {
                userDropdownMenu.classList.remove('active');
                userMenuBtn.classList.remove('active');
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!dropdownContainer.contains(e.target)) {
                    userDropdownMenu.classList.remove('active');
                    userMenuBtn.classList.remove('active');
                }
            });

            // Close dropdown when clicking on a link
            const links = userDropdownMenu.querySelectorAll('.dropdown-link:not(.logout-link)');
            links.forEach(link => {
                link.addEventListener('click', function() {
                    userDropdownMenu.classList.remove('active');
                    userMenuBtn.classList.remove('active');
                });
            });
        }
    });
    </script>
    <script src="js/footer.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

</body>

</html>