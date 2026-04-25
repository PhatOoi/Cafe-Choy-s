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
                    <li class="nav-item"><a href="{{ url('/') }}" class="nav-link">Trang chủ</a></li>
                    @if(!(auth()->check() && auth()->user()->isStaff()))
                        <li class="nav-item active"><a href="{{ url('/menu') }}" class="nav-link">Menu</a></li>
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
                                    @php
                                        $menuUserAvatar = Auth::user()->avatar_url
                                            ? asset('storage/' . Auth::user()->avatar_url)
                                            : asset('images/user.jpg');
                                    @endphp
                                    <li class="nav-item user-dropdown-wrapper">
                                        <div class="user-dropdown-container">

                                            <button class="user-avatar-btn" type="button" id="userMenuBtn" aria-label="Mở menu người dùng">
                                                <img src="{{ $menuUserAvatar }}" alt="{{ Auth::user()->name }}"
                                                    class="user-avatar"
                                                    onerror="this.onerror=null;this.src='{{ asset('images/user.jpg') }}';">
                                            </button>

                                            <div class="user-dropdown-menu" id="userDropdownMenu">
                                                <div class="dropdown-header-info">
                                                    <img src="{{ $menuUserAvatar }}" alt="{{ Auth::user()->name }}"
                                                        class="dropdown-avatar"
                                                        onerror="this.onerror=null;this.src='{{ asset('images/user.jpg') }}';">

                                                    <div class="user-details">
                                                        <p class="user-name">{{ Auth::user()->name }}</p>
                                                        <p class="user-role">
                                                            @if(Auth::user()->role_id === 1)
                                                                <span class="badge-admin">Admin</span>
                                                            @else
                                                                <span class="badge-customer">Khách hàng</span>
                                                            @endif
                                                        </p>
                                                    </div>
                                                </div>

                                                <div class="dropdown-divider"></div>

                                                <a href="/profile" class="dropdown-link">Hồ sơ</a>

                                                @if(Auth::user()->role_id === 1)
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

    @php
        $menuDirectory = [
            ['label' => 'Cà phê', 'slug' => 'ca-phe', 'aliases' => ['cà phê', 'ca phe', 'coffee']],
            ['label' => 'Trà sữa', 'slug' => 'tra-sua', 'aliases' => ['trà sữa', 'tra sua', 'milk tea']],
            ['label' => 'Nước ép và sinh tố', 'slug' => 'nuoc-ep-sinh-to', 'aliases' => ['nước ép', 'nuoc ep', 'sinh tố', 'sinh to', 'juice', 'smoothie']],
            ['label' => 'Đá xay', 'slug' => 'da-xay', 'aliases' => ['đá xay', 'da xay', 'frappe']],
            ['label' => 'Trà và thức uống theo mùa', 'slug' => 'tra-va-thuc-uong-theo-mua', 'aliases' => ['trà và thức uống theo mùa', 'tra va thuc uong theo mua', 'theo mùa', 'theo mua', 'seasonal']],
            ['label' => 'Bánh', 'slug' => 'banh-snack', 'aliases' => ['bánh', 'banh', 'cake', 'pastry']],
        ];
    @endphp

    <section class="menu-directory-section">
        <div class="menu-directory-shell">
            <p class="menu-directory-kicker">Mục lục thực đơn</p>
            <div class="menu-directory-links">
                @foreach($menuDirectory as $directoryItem)
                    @php
                        $targetCategory = $categories->first(function ($category) use ($directoryItem) {
                            return \Illuminate\Support\Str::slug($category->name) === $directoryItem['slug'];
                        });

                        if (!$targetCategory) {
                            $targetCategory = $categories->first(function ($category) use ($directoryItem) {
                                $categoryName = \Illuminate\Support\Str::lower(trim($category->name));
                                $aliases = collect($directoryItem['aliases']);

                                if ($aliases->contains($categoryName)) {
                                    return true;
                                }

                                return $aliases->contains(function ($alias) use ($categoryName) {
                                    return str_contains($categoryName, $alias);
                                });
                            });
                        }
                    @endphp

                    @if($targetCategory)
                        @php
                            $directoryImage = optional($targetCategory->products->first())->image_url;
                        @endphp
                        <a href="#menu-cat-{{ \Illuminate\Support\Str::slug($targetCategory->name) }}" class="menu-directory-link">
                            <span class="menu-directory-avatar-wrap">
                                <img
                                    src="{{ $directoryImage ? asset('images/' . $directoryImage) : 'https://via.placeholder.com/180x180/c8b8a8/ffffff?text=Menu' }}"
                                    alt="{{ $directoryItem['label'] }}"
                                    class="menu-directory-avatar"
                                    onerror="this.src='https://via.placeholder.com/180x180/c8b8a8/ffffff?text=Menu'">
                            </span>
                            <span class="menu-directory-label">{{ $directoryItem['label'] }}</span>
                        </a>
                    @endif
                @endforeach
            </div>
        </div>
    </section>

    {{-- ===== GRID SẢN PHẨM THEO PHÂN LOẠI ===== --}}
    <section class="menu-section">
        @foreach($categories as $category)
            <div class="menu-category-block" id="menu-cat-{{ \Illuminate\Support\Str::slug($category->name) }}">
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
                @php
                    $categorySlug = \Illuminate\Support\Str::slug($category->name);
                    $productGroups = collect([
                        [
                            'label' => null,
                            'products' => $category->products,
                        ],
                    ]);

                    if ($categorySlug === 'tra-va-thuc-uong-theo-mua') {
                        $coldNames = ['Peach Tea', 'Trà Dâu', 'Trà Trái Cây Nhiệt Đới'];
                        $hotNames = ['Trà Lài', 'Trà Olong thiết quan âm', 'Trà Dưỡng Nhan'];

                        $coldProducts = collect($coldNames)
                            ->map(fn ($name) => $category->products->firstWhere('name', $name))
                            ->filter();

                        $hotProducts = collect($hotNames)
                            ->map(fn ($name) => $category->products->firstWhere('name', $name))
                            ->filter();

                        $productGroups = collect([
                            ['label' => 'Uống lạnh', 'products' => $coldProducts],
                            ['label' => 'Uống nóng', 'products' => $hotProducts],
                        ])->filter(fn ($group) => $group['products']->isNotEmpty());
                    }
                @endphp

                @foreach($productGroups as $group)
                    @if($group['label'])
                        <div class="menu-subcategory-label-wrap">
                            <h3 class="menu-subcategory-label">{{ $group['label'] }}</h3>
                        </div>
                    @endif

                    <div class="menu-grid">
                        @foreach($group['products'] as $product)
                            <div class="product-card">
                                <div class="card-image-wrap">
                                    @php
                                        // Nếu image_url là URL (bắt đầu bằng http), dùng trực tiếp; nếu không, thêm đường dẫn local
                                        $imageUrl = str_starts_with($product->image_url, 'http') 
                                            ? $product->image_url 
                                            : asset('images/' . $product->image_url);
                                    @endphp
                                    <img src="{{ $imageUrl }}"
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
                                    <h3 class="card-name">{{ $product->name }}</h3>
                                    @if($product->description ?? false)
                                        <p class="card-desc">{{ Str::limit($product->description, 65) }}</p>
                                    @endif
                                    <div class="card-footer">
                                        <span class="card-price">
                                            {{ number_format($product->price) }}<span class="price-unit">đ</span>
                                        </span>
                                        @auth
                                            @php
                                                $buttonImageUrl = str_starts_with($product->image_url, 'http') 
                                                    ? $product->image_url 
                                                    : asset('images/' . $product->image_url);
                                            @endphp
                                            <button class="btn-add-cart" onclick='openModal(
                                                            {{ $product->id }},
                                                            @json($product->name),
                                                            {{ $product->price }},
                                                            @json($category->name),
                                                            @json($buttonImageUrl),
                                                            @json(\Illuminate\Support\Str::slug($category->name)),
                                                            @json($product->description ?? "")
                                                        )'>
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
                @endforeach
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
            <div class="sheet-scroll">

                <div class="sheet-handle"></div>

                {{-- Ảnh sản phẩm --}}
                <div class="sheet-img-wrap" id="sheetImgWrap">
                    <img id="sheetImg" src="" alt="">
                    <div class="sheet-img-overlay">
                        <div class="sheet-hero-copy">
                            <p class="sheet-cat" id="sheetCat"></p>
                            <h2 class="sheet-name" id="sheetName"></h2>
                            <p class="sheet-desc" id="sheetDesc"></p>
                        </div>
                        <div class="sheet-hero-price">
                            <span>Giá gốc</span>
                            <strong id="priceBaseHero">0đ</strong>
                        </div>
                    </div>
                    <button class="sheet-close" onclick="closeModal()">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                            stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18" />
                            <line x1="6" y1="6" x2="18" y2="18" />
                        </svg>
                    </button>
                </div>

                <div class="sheet-body">
                <div class="price-summary">
                    <div class="price-main">
                        <p class="price-label">Tổng cộng</p>
                        <p class="price-total" id="priceTotal">0đ</p>
                        <p class="price-hint">Giá sẽ cập nhật theo kích cỡ và lựa chọn thêm.</p>
                    </div>
                </div>

                <div class="sheet-section sheet-section-size">
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
                </div>

                <div class="sheet-section sheet-section-topping">
                    <p class="section-label">
                        Topping
                        <span class="optional-badge">Tùy chọn</span>
                    </p>

                    <div class="topping-grid">
                        @foreach($toppings as $tp)
                            <div class="topping-item" data-price="{{ $tp->price }}" data-id="{{ $tp->id }}"
                                onclick="toggleTopping(this)">
                                <div class="tp-check">
                                    <svg viewBox="0 0 12 12" aria-hidden="true" focusable="false">
                                        <polyline points="2 6.5 5 9 10 3" />
                                    </svg>
                                </div>
                                <div class="tp-info">
                                    <span class="tp-name">{{ $tp->name }}</span>
                                    <span class="tp-price">+{{ number_format($tp->price) }}đ</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="sheet-section sheet-section-sugar">
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
                </div>

                <div class="sheet-section sheet-section-ice">
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
                </div>

                <div class="sheet-section sheet-section-note">
                    <p class="section-label">Ghi chú</p>
                    <textarea class="note-input" id="noteInput" placeholder="Vd: ít ngọt, thêm siro, không đường..."
                        rows="3"></textarea>
                </div>

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

    {{-- Dialog cảnh báo khi vượt quá số lượng tối đa và cần chuyển sang trang hỗ trợ. --}}
    <div class="limit-dialog-backdrop" id="limitDialogBackdrop" onclick="closeLimitDialog(event)">
        <div class="limit-dialog-card" role="dialog" aria-modal="true" aria-labelledby="limitDialogTitle">
            <div class="limit-dialog-icon">
                <i class="fas fa-headset"></i>
            </div>
            <p class="limit-dialog-kicker">Hỗ trợ đơn hàng</p>
            <h3 class="limit-dialog-title" id="limitDialogTitle">Vượt quá số lượng cho phép</h3>
            <p class="limit-dialog-copy" id="limitDialogMessage">Bạn đã vượt quá số lượng cho phép cho sản phẩm này.</p>
            <div class="limit-dialog-actions">
                <button type="button" class="limit-dialog-btn limit-dialog-btn-muted" onclick="hideLimitDialog()">Đã hiểu</button>
                <button type="button" class="limit-dialog-btn limit-dialog-btn-primary" id="limitDialogSupportBtn">Đến trang hỗ trợ</button>
            </div>
        </div>
    </div>

    <button type="button" class="back-to-top-btn" id="backToTopBtn" aria-label="Trở về đầu trang">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6"
            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M12 19V5" />
            <path d="m5 12 7-7 7 7" />
        </svg>
    </button>

    <footer class="coffee-footer">
        <!-- Newsletter Section -->

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
        /* GRID ĐỀU HƠN */
        .footer-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 40px;
            align-items: flex-start;
        }

        
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

        
        .footer-links,
        .footer-contact {
            padding-top: 10px;
        }

       
        .footer-links ul li {
            line-height: 1.8;
        }

        
        .social-links {
            margin-top: 10px;
        }

      
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

        .user-avatar {
            width: 44px;
            height: 44px;
            border-radius: 999px;
            object-fit: cover;
            border: 2px solid rgba(201, 169, 110, 0.42);
            background: #f6ede3;
            box-shadow: 0 8px 20px rgba(26, 17, 13, 0.14);
            display: block;
            transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease;
        }

        .user-avatar-btn {
            width: 48px;
            height: 48px;
            margin-left: 8px;
            padding: 0;
            border: none;
            background: transparent;
            box-shadow: none;
            appearance: none;
            border-radius: 999px;
            overflow: visible;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .user-avatar-btn:hover .user-avatar,
        .user-avatar-btn:focus-visible .user-avatar {
            transform: translateY(-1px) scale(1.03);
            border-color: rgba(201, 169, 110, 0.82);
            box-shadow: 0 12px 24px rgba(26, 17, 13, 0.18);
        }

        .user-avatar-btn:focus-visible {
            outline: none;
        }

        .dropdown-avatar {
            width: 56px;
            height: 56px;
            border-radius: 999px;
            object-fit: cover;
            border: 2px solid rgba(201, 169, 110, 0.42);
            background: #f6ede3;
            box-shadow: 0 10px 24px rgba(26, 17, 13, 0.12);
            display: block;
            flex-shrink: 0;
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

        .menu-directory-section {
            background: linear-gradient(180deg, #1a110d 0%, #f5f0eb 100%);
            padding: 0 24px 24px;
        }

        .menu-directory-shell {
            max-width: 1200px;
            margin: 0 auto;
            padding: 22px 24px;
            border-radius: 24px;
            background: rgba(255, 248, 240, 0.92);
            border: 1px solid rgba(201, 169, 110, .28);
            box-shadow: 0 16px 34px rgba(26, 17, 13, .08);
        }

        .menu-directory-kicker {
            margin: 0 0 14px;
            font-family: 'DM Sans', sans-serif;
            font-size: .78rem;
            letter-spacing: .22em;
            text-transform: uppercase;
            color: #8b7060;
        }

        .menu-directory-links {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(132px, 1fr));
            gap: 16px;
        }

        .menu-directory-link {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            gap: 10px;
            padding: 16px 12px 14px;
            border-radius: 22px;
            background: #fff;
            border: 1px solid rgba(201, 169, 110, .28);
            color: #3b2a20;
            font-family: 'DM Sans', sans-serif;
            text-decoration: none;
            transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease, color .2s ease;
        }

        .menu-directory-avatar-wrap {
            width: 82px;
            height: 82px;
            padding: 4px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(201, 169, 110, .32), rgba(255, 255, 255, .85));
            box-shadow: 0 10px 22px rgba(26, 17, 13, .12);
            flex-shrink: 0;
        }

        .menu-directory-avatar {
            width: 100%;
            height: 100%;
            display: block;
            border-radius: 50%;
            object-fit: cover;
        }

        .menu-directory-label {
            display: block;
            text-align: center;
            color: #3b2a20;
            font-size: .92rem;
            font-weight: 600;
            line-height: 1.4;
            min-height: 2.6em;
        }

        .menu-directory-link:hover {
            color: #8a5b2f;
            border-color: rgba(138, 91, 47, .45);
            box-shadow: 0 10px 20px rgba(26, 17, 13, .08);
            transform: translateY(-2px);
            text-decoration: none;
        }

        .menu-directory-link:hover .menu-directory-label {
            color: #8a5b2f;
        }

        /* ===== MENU SECTION ===== */
        .menu-section {
            background: #f5f0eb;
            padding: 56px 24px 80px;
            min-height: 50vh;
        }

        .menu-category-block {
            scroll-margin-top: 120px;
        }

        .menu-grid {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 28px;
        }

        .menu-subcategory-label-wrap {
            max-width: 1200px;
            margin: 0 auto 18px;
        }

        .menu-subcategory-label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin: 8px 0 0;
            padding: 8px 14px;
            border-radius: 999px;
            background: rgba(201, 169, 110, .12);
            color: #6f4d33;
            font-family: 'DM Sans', sans-serif;
            font-size: .86rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
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
            font-size: 1.5rem;
            font-weight: 500;
            color: #1a110d;
            margin: 0 0 6px;
            line-height: 1.3;
        }

        .card-desc {
            font-family: 'DM Sans', sans-serif;
            font-size: 1.1rem;
            color: #7d6348;
            margin: 0 0 12px;
            line-height: 1.5;
        }

        .card-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding-top: 12px;
            border-top: 1px solid rgba(134, 107, 58, 0.2);
        }

        .card-price {
            font-family: 'Playfair Display', serif;
            font-size: 1.1rem;
            font-weight: 500;
            color: #111111;
        }

        .price-unit {
            font-size: 1.1rem;
            font-weight: 900;
            margin-left: 1px;
            color: #111110;
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
            background: radial-gradient(circle at top, rgba(201, 169, 110, .16), transparent 28%), rgba(15, 8, 5, .7);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            pointer-events: none;
            transition: opacity .3s ease;
            padding: 24px;
        }

        .modal-backdrop.open {
            opacity: 1;
            pointer-events: all;
        }

        /* ===== MODAL SHEET ===== */
        .modal-sheet {
            background: linear-gradient(180deg, #fffdfa 0%, #fff 100%);
            border-radius: 30px;
            width: 100%;
            max-width: 640px;
            max-height: min(90vh, 800px);
            overflow: hidden;
            transform: translateY(24px);
            transition: transform .32s cubic-bezier(.25, .8, .25, 1);
            box-shadow: 0 30px 90px rgba(15, 8, 5, .28);
            border: 1px solid rgba(201, 169, 110, .18);
            text-rendering: geometricPrecision;
        }

        .sheet-scroll {
            width: 100%;
            max-height: min(90vh, 800px);
            overflow-y: auto;
            overflow-x: hidden;
            scrollbar-width: thin;
            scrollbar-color: #dcc7af transparent;
            scrollbar-gutter: stable;
            padding-right: 4px;
        }

        .sheet-scroll::-webkit-scrollbar {
            width: 8px;
        }

        .sheet-scroll::-webkit-scrollbar-track {
            background: transparent;
        }

        .sheet-scroll::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, #dbc2a1, #c89b63);
            border-radius: 999px;
            border: 2px solid transparent;
            background-clip: padding-box;
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
            height: 280px;
            background: #d4b896;
            overflow: hidden;
            margin: 10px 10px 0;
            border-radius: 24px 24px 18px 18px;
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
            background: linear-gradient(180deg, rgba(9, 7, 5, .08) 0%, rgba(9, 7, 5, .16) 30%, rgba(9, 7, 5, .78) 100%);
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 18px;
            padding: 22px;
        }

        .sheet-hero-copy {
            max-width: 72%;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .sheet-close {
            position: absolute;
            top: 12px;
            right: 12px;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .94);
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1a110d;
            transition: background .2s, transform .2s;
            z-index: 2;
            box-shadow: 0 10px 24px rgba(15, 8, 5, .18);
        }

        .sheet-close:hover {
            background: #fff;
            transform: rotate(90deg);
        }

        .sheet-body {
            padding: 22px 20px 24px;
            font-family: 'DM Sans', 'Segoe UI', sans-serif;
        }

        .sheet-cat {
            font-family: 'DM Sans', sans-serif;
            font-size: 11px;
            letter-spacing: .26em;
            text-transform: uppercase;
            color: rgba(255, 243, 224, .82);
            margin: 0;
        }

        .sheet-name {
            font-family: 'DM Sans', 'Segoe UI', sans-serif;
            font-size: clamp(1.8rem, 4vw, 2.35rem);
            font-weight: 800;
            letter-spacing: -.03em;
            color: #fffaf2;
            margin: 0;
            line-height: 1.05;
        }

        .sheet-desc {
            font-family: 'DM Sans', sans-serif;
            font-size: 13px;
            line-height: 1.65;
            color: rgba(255, 243, 224, .72);
            margin: 0;
            max-width: 44ch;
        }

        .sheet-hero-price {
            display: flex;
            flex-direction: column;
            gap: 4px;
            padding: 12px 14px;
            border-radius: 18px;
            background: rgba(255, 248, 238, .14);
            border: 1px solid rgba(255, 255, 255, .16);
            backdrop-filter: blur(10px);
            color: #fff5e7;
            text-align: right;
            align-self: flex-end;
            min-width: 120px;
        }

        .sheet-hero-price span {
            font-family: 'DM Sans', sans-serif;
            font-size: 10px;
            letter-spacing: .2em;
            text-transform: uppercase;
            opacity: .72;
        }

        .sheet-hero-price strong {
            font-family: 'DM Sans', 'Segoe UI', sans-serif;
            font-size: 1.22rem;
            font-weight: 800;
            letter-spacing: -.02em;
        }

        .price-summary {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 8px;
            padding: 18px 20px;
            border-radius: 22px;
            background: linear-gradient(135deg, #fff 0%, #fbf3e8 100%);
            border: 1px solid rgba(201, 169, 110, .22);
            box-shadow: 0 12px 26px rgba(26, 17, 13, .06);
        }

        .price-main {
            min-width: 0;
        }

        .price-label {
            font-family: 'DM Sans', sans-serif;
            font-size: 11px;
            color: #b8a090;
            margin: 0 0 2px;
            letter-spacing: .18em;
            text-transform: uppercase;
        }

        .price-total {
            font-family: 'DM Sans', 'Segoe UI', sans-serif;
            font-size: 2rem;
            font-weight: 800;
            letter-spacing: -.03em;
            color: #1a110d;
            margin: 0;
        }

        .price-hint {
            margin: 6px 0 0;
            font-family: 'DM Sans', sans-serif;
            font-size: 12px;
            color: #8b7060;
        }

        .sheet-section {
            margin-top: 18px;
            padding: 18px 18px 16px;
            border-radius: 20px;
            background: #fff;
            border: 1px solid rgba(201, 169, 110, .16);
            box-shadow: 0 8px 22px rgba(26, 17, 13, .04);
        }

        .section-label {
            font-family: 'DM Sans', sans-serif;
            font-size: 11px;
            font-weight: 700;
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
            background: #f8f0e6;
            color: #b08f71;
            font-size: 9px;
            padding: 4px 8px;
            border-radius: 10px;
            letter-spacing: .1em;
            font-weight: 600;
        }

        /* SIZE */
        .size-row {
            display: flex;
            gap: 12px;
        }

        .size-btn {
            flex: 1;
            border: 1px solid #e7d8c8;
            border-radius: 18px;
            padding: 16px 10px 14px;
            text-align: center;
            cursor: pointer;
            transition: all .2s ease, transform .2s ease;
            background: linear-gradient(180deg, #fff 0%, #fffaf5 100%);
            box-shadow: inset 0 0 0 1px rgba(255,255,255,.4);
        }

        .size-btn.active {
            border-color: #1a110d;
            background: linear-gradient(180deg, #2a1a13 0%, #160d09 100%);
            transform: translateY(-2px);
            box-shadow: 0 16px 24px rgba(26, 17, 13, .16);
        }

        .size-letter {
            display: block;
            font-family: 'DM Sans', 'Segoe UI', sans-serif;
            font-size: 1.35rem;
            font-weight: 800;
            letter-spacing: -.02em;
            color: #1a110d;
        }

        .size-btn.active .size-letter {
            color: #f0e6d0;
        }

        .size-price {
            display: block;
            font-family: 'DM Sans', sans-serif;
            font-size: 11px;
            color: #b8a090;
            margin-top: 6px;
        }

        .size-btn.active .size-price {
            color: #c9a96e;
        }

        /* OPTIONS (ĐƯỜNG, ĐÁ) */
        .option-row {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .option-chip {
            border: 1px solid #e6d8c8;
            border-radius: 999px;
            padding: 10px 16px;
            font-family: 'DM Sans', sans-serif;
            font-size: 12px;
            color: #6b5a4a;
            cursor: pointer;
            transition: all .2s;
            background: linear-gradient(180deg, #fff 0%, #fffaf5 100%);
        }

        .option-chip.active {
            border-color: #c9a96e;
            background: linear-gradient(135deg, #d7b27a, #c69858);
            color: #1a110d;
            font-weight: 700;
            box-shadow: 0 10px 18px rgba(201, 169, 110, .22);
        }

        /* TOPPING */
        .topping-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .topping-item {
            display: flex;
            align-items: center;
            gap: 10px;
            border: 1px solid #e6d8c8;
            border-radius: 16px;
            padding: 12px 14px;
            cursor: pointer;
            transition: all .2s;
            background: linear-gradient(180deg, #fff 0%, #fffaf5 100%);
        }

        .topping-item.active {
            border-color: #c9a96e;
            background: linear-gradient(135deg, #fff6ea 0%, #f8ecdd 100%);
            box-shadow: 0 10px 20px rgba(201, 169, 110, .16);
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
            width: 12px;
            height: 12px;
            display: block;
            fill: none;
            stroke: #fff;
            stroke-width: 2.2;
            stroke-linecap: round;
            stroke-linejoin: round;
            opacity: 0;
            transform: scale(.7);
            transition: opacity .18s ease, transform .18s ease;
        }

        .topping-item.active .tp-check {
            background: #c9a96e;
            border-color: #c9a96e;
        }

        .topping-item.active .tp-check svg {
            opacity: 1;
            transform: scale(1);
        }

        .tp-name {
            display: block;
            font-family: 'DM Sans', sans-serif;
            font-size: 13px;
            font-weight: 700;
            color: #1a110d;
        }

        .tp-price {
            display: block;
            font-family: 'DM Sans', sans-serif;
            font-size: 11px;
            color: #b8a090;
            margin-top: 2px;
        }

        /* NOTE */
        .note-input {
            width: 100%;
            border: 1px solid #e6d8c8;
            border-radius: 16px;
            padding: 14px 16px;
            font-family: 'DM Sans', sans-serif;
            font-size: 13px;
            color: #1a110d;
            resize: none;
            outline: none;
            transition: border-color .2s;
            background: linear-gradient(180deg, #fff 0%, #fffaf5 100%);
            min-height: 92px;
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
            position: sticky;
            bottom: 0;
            padding-top: 14px;
            background: linear-gradient(180deg, rgba(255, 253, 250, 0) 0%, rgba(255, 253, 250, .96) 24%, #fffdfa 100%);
        }

        .qty-wrap {
            display: flex;
            align-items: center;
            border: 1px solid #e6d8c8;
            border-radius: 16px;
            overflow: hidden;
            flex-shrink: 0;
            background: #fff;
            box-shadow: 0 8px 18px rgba(26, 17, 13, .05);
        }

        .qty-btn {
            width: 44px;
            height: 48px;
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
            width: 42px;
            text-align: center;
            font-family: 'DM Sans', 'Segoe UI', sans-serif;
            font-size: 16px;
            font-weight: 800;
            color: #1a110d;
            border-left: 1px solid #e0d4c8;
            border-right: 1px solid #e0d4c8;
            height: 48px;
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
            background: linear-gradient(135deg, #1f140f 0%, #120a07 100%);
            color: #f0e6d0;
            border: none;
            border-radius: 16px;
            height: 52px;
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: background .22s, transform .15s;
            box-shadow: 0 18px 30px rgba(26, 17, 13, .18);
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

        .limit-dialog-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(11, 6, 3, .58);
            backdrop-filter: blur(8px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            opacity: 0;
            visibility: hidden;
            transition: opacity .24s ease, visibility .24s ease;
            z-index: 100000;
        }

        .limit-dialog-backdrop.show {
            opacity: 1;
            visibility: visible;
        }

        .limit-dialog-card {
            width: min(100%, 420px);
            padding: 28px 24px 22px;
            border-radius: 26px;
            background: linear-gradient(180deg, #fff8f1 0%, #f8ebdb 100%);
            border: 1px solid rgba(201, 169, 110, .36);
            box-shadow: 0 28px 54px rgba(26, 17, 13, .28);
            text-align: center;
            font-family: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            transform: translateY(16px) scale(.98);
            transition: transform .24s ease;
        }

        .limit-dialog-backdrop.show .limit-dialog-card {
            transform: translateY(0) scale(1);
        }

        .limit-dialog-icon {
            width: 58px;
            height: 58px;
            margin: 0 auto 14px;
            border-radius: 18px;
            background: linear-gradient(135deg, #c9a96e, #b8844f);
            color: #fff7ed;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            box-shadow: 0 16px 28px rgba(201, 169, 110, .26);
        }

        .limit-dialog-kicker {
            margin: 0 0 8px;
            font-family: inherit;
            font-weight: 500;
            font-size: 11px;
            letter-spacing: .18em;
            text-transform: uppercase;
            color: #9a7b61;
        }

        .limit-dialog-title {
            margin: 0 0 10px;
            font-family: inherit;
            font-size: 1.45rem;
            font-weight: 700;
            color: #1a110d;
        }

        .limit-dialog-copy {
            margin: 0;
            font-family: inherit;
            font-weight: 400;
            color: #755d4f;
            font-size: 14px;
            line-height: 1.7;
        }

        .limit-dialog-actions {
            display: flex;
            gap: 10px;
            margin-top: 22px;
        }

        .limit-dialog-btn {
            flex: 1;
            border: none;
            border-radius: 16px;
            padding: 12px 16px;
            font-family: inherit;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .limit-dialog-btn:hover {
            transform: translateY(-1px);
        }

        .limit-dialog-btn-muted {
            background: #efe2d4;
            color: #6f584a;
        }

        .limit-dialog-btn-primary {
            background: linear-gradient(135deg, #1a110d, #38231a);
            color: #f7ebd7;
            box-shadow: 0 14px 24px rgba(26, 17, 13, .18);
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

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .limit-dialog-actions {
                flex-direction: column;
            }

            .modal-backdrop {
                align-items: flex-end;
                padding: 0;
            }

            .modal-sheet {
                max-width: 100%;
                max-height: 92vh;
                border-radius: 28px 28px 0 0;
                transform: translateY(36px);
            }

            .sheet-scroll {
                max-height: 92vh;
                padding-right: 0;
            }

            .sheet-img-wrap {
                height: 228px;
                margin: 10px 10px 0;
                border-radius: 22px 22px 16px 16px;
            }

            .sheet-img-overlay {
                padding: 16px;
                flex-direction: column;
                align-items: flex-start;
                justify-content: flex-end;
            }

            .sheet-hero-copy {
                max-width: 100%;
            }

            .sheet-hero-price {
                min-width: 0;
                align-self: flex-start;
                text-align: left;
            }

            .price-summary {
                flex-direction: column;
                align-items: flex-start;
            }

            .menu-directory-section {
                padding: 0 14px 18px;
            }

            .menu-directory-shell {
                padding: 18px 16px;
                border-radius: 20px;
            }

            .menu-directory-links {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 10px;
            }

            .menu-directory-link {
                width: 100%;
                border-radius: 18px;
                padding: 14px 10px 12px;
            }

            .menu-directory-avatar-wrap {
                width: 72px;
                height: 72px;
            }

            .menu-directory-label {
                font-size: .86rem;
            }

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
                padding: 16px 14px 24px;
            }

            .sheet-section {
                padding: 16px 14px 14px;
            }

            .size-row {
                gap: 10px;
            }

            .confirm-row {
                flex-direction: column;
                align-items: stretch;
            }

            .qty-wrap {
                width: 100%;
                justify-content: space-between;
            }

            .qty-btn,
            .qty-num {
                flex: 1;
                width: auto;
            }

            .back-to-top-btn {
                right: 16px;
                bottom: 18px;
                width: 48px;
                height: 48px;
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

        function openModal(id, name, price, cat, imgUrl, categorySlug, description) {

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
            document.getElementById('sheetDesc').textContent = description || 'Tùy chỉnh món theo khẩu vị của bạn với kích cỡ và lựa chọn thêm phù hợp.';
            document.getElementById('priceBaseHero').textContent = fmtPrice(price);
            document.getElementById('sheetImg').src = imgUrl;
            document.getElementById('sheetImg').alt = name;
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

            var toppingSection = document.querySelector('.sheet-section-topping');
            var sugarSection = document.querySelector('.sheet-section-sugar');
            var iceSection = document.querySelector('.sheet-section-ice');
            var sizeSection = document.querySelector('.sheet-section-size');

            // ===== RESET HIỆN TẤT CẢ =====
            [toppingSection, sugarSection, iceSection, sizeSection].forEach(el => {
                if (el) el.style.display = '';
            });

            // ===== LOGIC MỚI =====
            var productName = document.getElementById('sheetName').textContent || '';
            if (categorySlug === 'tra-sua') {
                // Trà sữa: hiện topping và đá, ẩn đường & sữa
                sugarSection.style.display = 'none';
            } else if (categorySlug === 'da-xay') {
                // Đá xay: ẩn topping, đường, sữa và đá
                toppingSection.style.display = 'none';
                sugarSection.style.display = 'none';
                iceSection.style.display = 'none';
            } else if (categorySlug === 'nuoc-ep-sinh-to' || categorySlug === 'nuoc-ep') {
                // Sinh tố/nước ép: ẩn topping
                toppingSection.style.display = 'none';
                if (productName.toLowerCase().includes('sinh tố')) {
                    // Sinh tố: ẩn luôn đá, đường & sữa
                    sugarSection.style.display = 'none';
                    iceSection.style.display = 'none';
                } else {
                    // Nước ép: ẩn đường & sữa
                    sugarSection.style.display = 'none';
                }
            } else if (categorySlug === 'ca-phe') {
                // Cà phê: ẩn topping
                toppingSection.style.display = 'none';
            } else if (categorySlug === 'tra-va-thuc-uong-theo-mua') {
                // Thức uống theo mùa: ẩn topping, đường & sữa
                toppingSection.style.display = 'none';
                sugarSection.style.display = 'none';
            } else if (categorySlug === 'banh-snack') {
                // Bánh/snack: ẩn tất cả option
                toppingSection.style.display = 'none';
                sugarSection.style.display = 'none';
                iceSection.style.display = 'none';
                sizeSection.style.display = 'none';
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
                        // Nếu vượt quá giới hạn số lượng
                        if (data.is_limit_exceeded) {
                            showLimitDialog(data.message || 'Số lượng sản phẩm đã vượt quá mức cho phép.', data.support_url);
                        } else {
                            showToast('❌ ' + (data.message || 'Lỗi server'));
                        }
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

        // Dùng dialog tùy biến để đồng bộ giao diện thay cho confirm() mặc định.
        function showLimitDialog(message, supportUrl) {
            var backdrop = document.getElementById('limitDialogBackdrop');
            var messageNode = document.getElementById('limitDialogMessage');
            var supportBtn = document.getElementById('limitDialogSupportBtn');

            if (!backdrop || !messageNode || !supportBtn) {
                return;
            }

            messageNode.textContent = message + ' Bạn có muốn truy cập trang hỗ trợ để liên hệ nhân viên không?';
            supportBtn.onclick = function () {
                hideLimitDialog();
                if (supportUrl) {
                    clearCartAndGoSupport(supportUrl);
                }
            };

            backdrop.classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function hideLimitDialog() {
            var backdrop = document.getElementById('limitDialogBackdrop');
            if (!backdrop) {
                return;
            }

            backdrop.classList.remove('show');
            document.body.style.overflow = '';
        }

        // Khi khách chọn sang hỗ trợ, reset session cart trước rồi mới chuyển trang.
        function clearCartAndGoSupport(supportUrl) {
            fetch('{{ route("cart.clear-for-support") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ reason: 'quantity_limit_support_redirect' })
            })
            .catch(function () {
                // Dù clear cart lỗi vẫn ưu tiên điều hướng sang trang hỗ trợ.
            })
            .finally(function () {
                window.location.href = supportUrl;
            });
        }

        function closeLimitDialog(event) {
            if (event.target.id === 'limitDialogBackdrop') {
                hideLimitDialog();
            }
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

@include('components.ai-bot-widget')
</body>

</html>