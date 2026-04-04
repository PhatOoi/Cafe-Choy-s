<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Menu — Coffee Choy's</title>

    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600&family=DM+Sans:wght@300;400;500&family=Great+Vibes&display=swap" rel="stylesheet">

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
            <a class="navbar-brand" href="{{ url('/login') }}">Coffee<small>Choy's</small></a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#ftco-nav"
                aria-controls="ftco-nav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="oi oi-menu"></span> Menu
            </button>
            <div class="collapse navbar-collapse" id="ftco-nav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item active"><a href="{{ url('/') }}" class="nav-link">Trang chủ</a></li>
                    <li class="nav-item"><a href="{{ url('/menu') }}" class="nav-link">Menu</a></li>
                    <li class="nav-item"><a href="contact.html" class="nav-link">Liên hệ</a></li>
                    <li class="nav-item"><a href="{{ url('/login') }}" class="nav-link">Đăng nhập</a></li>
                    <li class="nav-item cart">
                        <a href="cart" class="nav-link">
                            <span class="icon icon-shopping_cart"></span>
                            <span class="bag d-flex justify-content-center align-items-center">
                                <small id="cart-count">0</small>
                            </span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</div>

{{-- ===== HERO ===== --}}
<section class="menu-hero">
    <div class="menu-hero-inner">
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

{{-- ===== GRID SẢN PHẨM ===== --}}
<section class="menu-section">
    <div class="menu-grid">
        @foreach($products as $product)
        <div class="product-card">
            <div class="card-image-wrap">
                <img
                    src="{{ asset('images/' . $product->image_url) }}"
                    onerror="this.src='https://via.placeholder.com/400x280/c8b8a8/ffffff?text=Coffee'"
                    alt="{{ $product->name }}"
                    class="card-img">
                <div class="card-shine"></div>
                @if($product->is_new ?? false)
                    <span class="card-badge badge-new">Mới</span>
                @endif
                @if($product->is_hot ?? false)
                    <span class="card-badge badge-hot">Bán chạy</span>
                @endif
            </div>
            <div class="card-body">
                <p class="card-cat">{{ $product->category->name ?? 'Cà Phê' }}</p>
                <h3 class="card-name">{{ $product->name }}</h3>
                @if($product->description ?? false)
                <p class="card-desc">{{ Str::limit($product->description, 65) }}</p>
                @endif
                <div class="card-footer">
                    <span class="card-price">
                        {{ number_format($product->price) }}<span class="price-unit">đ</span>
                    </span>
                    <button
                        class="btn-add-cart"
                        onclick="openModal(
                            {{ $product->id }},
                            '{{ addslashes($product->name) }}',
                            {{ $product->price }},
                            '{{ $product->category->name ?? 'Cà Phê' }}',
                            '{{ asset('images/' . $product->image_url) }}'
                        )"
                    >
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                        </svg>
                        <span>Thêm vào giỏ</span>
                    </button>
                </div>
            </div>
        </div>
        @endforeach
    </div>
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
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
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
                <div class="size-btn active" data-extra="0" onclick="selectSize(this)">
                    <span class="size-letter">S</span>
                    <span class="size-price">Mặc định</span>
                </div>
                <div class="size-btn" data-extra="5000" onclick="selectSize(this)">
                    <span class="size-letter">M</span>
                    <span class="size-price">+5.000đ</span>
                </div>
                <div class="size-btn" data-extra="10000" onclick="selectSize(this)">
                    <span class="size-letter">L</span>
                    <span class="size-price">+10.000đ</span>
                </div>
            </div>

            <div class="sheet-divider"></div>

            {{-- Đường --}}
            <p class="section-label">Lượng đường</p>
            <div class="option-row" id="sugarRow">
                @foreach(['0%', '30%', '50%', '70%', '100%'] as $s)
                    <div class="option-chip {{ $loop->last ? 'active' : '' }}"
                         data-group="sugar" data-val="{{ $s }}"
                         onclick="selectOption(this, 'sugar')">{{ $s }}</div>
                @endforeach
            </div>

            <div class="sheet-divider"></div>

            {{-- Đá --}}
            <p class="section-label">Lượng đá</p>
            <div class="option-row" id="iceRow">
                @foreach(['Không đá', 'Ít đá', 'Bình thường', 'Nhiều đá'] as $ice)
                    <div class="option-chip {{ $loop->last ? 'active' : '' }}"
                         data-group="ice" data-val="{{ $ice }}"
                         onclick="selectOption(this, 'ice')">{{ $ice }}</div>
                @endforeach
            </div>

            <div class="sheet-divider"></div>

            {{-- Topping --}}
            <p class="section-label">
                Topping
                <span class="optional-badge">Tùy chọn</span>
            </p>

            @php
            $toppingList = [
                ['name' => 'Trân châu đen',    'price' => 5000],
                ['name' => 'Trân châu trắng',  'price' => 5000],
                ['name' => 'Thạch cà phê',     'price' => 5000],
                ['name' => 'Kem cheese',        'price' => 10000],
                ['name' => 'Trứng muối',        'price' => 10000],
                ['name' => 'Pudding',           'price' => 8000],
                ['name' => 'Nata de coco',      'price' => 5000],
                ['name' => 'Kem tươi',          'price' => 8000],
            ];
            @endphp

            <div class="topping-grid">
                @foreach($toppingList as $tp)
                <div class="topping-item" data-price="{{ $tp['price'] }}" onclick="toggleTopping(this)">
                    <div class="tp-check">
                        <svg width="10" height="10" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="2 6 5 9 10 3"/>
                        </svg>
                    </div>
                    <div class="tp-info">
                        <span class="tp-name">{{ $tp['name'] }}</span>
                        <span class="tp-price">+{{ number_format($tp['price']) }}đ</span>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="sheet-divider"></div>

            {{-- Ghi chú --}}
            <p class="section-label">Ghi chú</p>
            <textarea class="note-input" id="noteInput"
                placeholder="Vd: ít ngọt, thêm siro, không đường..." rows="2"></textarea>

            {{-- Số lượng + Thêm giỏ --}}
            <div class="confirm-row">
                <div class="qty-wrap">
                    <button class="qty-btn" onclick="changeQty(-1)">−</button>
                    <span class="qty-num" id="qtyNum">1</span>
                    <button class="qty-btn" onclick="changeQty(1)">+</button>
                </div>
                <button class="btn-confirm" onclick="confirmAddToCart()">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
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
        <svg width="10" height="10" viewBox="0 0 12 12" fill="none" stroke="#1a110d" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="2 6 5 9 10 3"/>
        </svg>
    </div>
    <span id="toastMsg"></span>
</div>

<style>
/* ===== BASE ===== */
*, *::before, *::after { box-sizing: border-box; }

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
    background: radial-gradient(ellipse 70% 50% at 50% -10%, rgba(201,169,110,.18), transparent 70%);
    pointer-events: none;
}
.menu-hero-inner { position: relative; z-index: 1; }
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
.divider-line { width: 80px; height: 1px; background: linear-gradient(to right, transparent, #c9a96e); }
.divider-line:last-child { background: linear-gradient(to left, transparent, #c9a96e); }
.divider-dot { color: #c9a96e; font-size: .65rem; opacity: .7; }
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
    border: 1px solid rgba(201,169,110,.15);
    box-shadow: 0 2px 14px rgba(26,17,13,.06);
    transition: transform .35s cubic-bezier(.25,.8,.25,1), box-shadow .35s cubic-bezier(.25,.8,.25,1);
}
.product-card:hover {
    transform: translateY(-7px);
    box-shadow: 0 18px 40px rgba(26,17,13,.13);
}
.card-image-wrap {
    position: relative;
    aspect-ratio: 4/3;
    overflow: hidden;
    background: #e8ddd4;
}
.card-img {
    width: 100%; height: 100%;
    object-fit: cover;
    display: block;
    transition: transform .55s cubic-bezier(.25,.8,.25,1);
}
.product-card:hover .card-img { transform: scale(1.07); }
.card-shine {
    position: absolute; inset: 0;
    background: linear-gradient(135deg, rgba(255,255,255,.08), transparent 60%);
    pointer-events: none;
}
.card-badge {
    position: absolute;
    top: 12px; left: 12px;
    font-family: 'DM Sans', sans-serif;
    font-size: 9px; font-weight: 500;
    letter-spacing: .14em;
    text-transform: uppercase;
    padding: 4px 10px;
    border-radius: 20px;
}
.badge-new { background: #1a110d; color: #f0e6d0; }
.badge-hot { background: #c0392b; color: #fff; }
.card-body { padding: 16px 18px 18px; }
.card-cat {
    font-family: 'DM Sans', sans-serif;
    font-size: 10px; letter-spacing: .18em;
    text-transform: uppercase; color: #b8a090; margin: 0 0 4px;
}
.card-name {
    font-family: 'Playfair Display', serif;
    font-size: 1.1rem; font-weight: 500; color: #1a110d;
    margin: 0 0 6px; line-height: 1.3;
}
.card-desc {
    font-family: 'DM Sans', sans-serif;
    font-size: .8rem; color: #a09080;
    margin: 0 0 12px; line-height: 1.5;
}
.card-footer {
    display: flex; align-items: center;
    justify-content: space-between; gap: 10px;
    padding-top: 12px;
    border-top: 1px solid rgba(201,169,110,.2);
}
.card-price {
    font-family: 'Playfair Display', serif;
    font-size: 1.05rem; font-weight: 500; color: #6b3a2a;
}
.price-unit { font-size: .8rem; font-weight: 400; margin-left: 1px; }
.btn-add-cart {
    display: flex; align-items: center; gap: 6px;
    background: #1a110d; color: #f0e6d0;
    border: none; border-radius: 10px;
    padding: 9px 15px;
    font-family: 'DM Sans', sans-serif;
    font-size: 11px; font-weight: 500;
    cursor: pointer;
    transition: background .22s, transform .18s;
    white-space: nowrap; flex-shrink: 0;
}
.btn-add-cart:hover { background: #c9a96e; color: #1a110d; }
.btn-add-cart:active { transform: scale(.96); }

/* ===== MODAL BACKDROP ===== */
.modal-backdrop {
    position: fixed; inset: 0;
    background: rgba(15,8,5,.6);
    z-index: 9999;
    display: flex; align-items: flex-end; justify-content: center;
    opacity: 0; pointer-events: none;
    transition: opacity .3s ease;
}
.modal-backdrop.open { opacity: 1; pointer-events: all; }

/* ===== MODAL SHEET ===== */
.modal-sheet {
    background: #fff;
    border-radius: 24px 24px 0 0;
    width: 100%; max-width: 560px;
    max-height: 92vh;
    overflow-y: auto;
    transform: translateY(50px);
    transition: transform .38s cubic-bezier(.25,.8,.25,1);
    scrollbar-width: thin;
    scrollbar-color: #e0d4c8 transparent;
}
.modal-backdrop.open .modal-sheet { transform: translateY(0); }
.sheet-handle {
    width: 44px; height: 4px;
    background: #e0d8d0; border-radius: 2px;
    margin: 12px auto 0;
}
.sheet-img-wrap {
    position: relative; height: 200px;
    background: #d4b896; overflow: hidden; margin-top: 10px;
}
.sheet-img-wrap img {
    width: 100%; height: 100%;
    object-fit: cover; display: block;
}
.sheet-img-overlay {
    position: absolute; inset: 0;
    background: linear-gradient(to top, rgba(26,17,13,.3), transparent 60%);
}
.sheet-close {
    position: absolute; top: 12px; right: 12px;
    width: 32px; height: 32px; border-radius: 50%;
    background: rgba(255,255,255,.92);
    border: none; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    color: #1a110d; transition: background .2s; z-index: 2;
}
.sheet-close:hover { background: #fff; }
.sheet-body { padding: 20px 22px 32px; }
.sheet-cat {
    font-family: 'DM Sans', sans-serif;
    font-size: 10px; letter-spacing: .2em;
    text-transform: uppercase; color: #b8a090; margin: 0 0 4px;
}
.sheet-name {
    font-family: 'Playfair Display', serif;
    font-size: 1.4rem; font-weight: 500; color: #1a110d; margin: 0 0 16px;
}
.price-summary {
    display: flex; align-items: flex-end;
    justify-content: space-between; margin-bottom: 16px;
}
.price-label {
    font-family: 'DM Sans', sans-serif;
    font-size: 11px; color: #b8a090; margin: 0 0 2px;
}
.price-total {
    font-family: 'Playfair Display', serif;
    font-size: 1.4rem; font-weight: 500; color: #1a110d; margin: 0;
}
.price-base {
    font-family: 'Playfair Display', serif;
    font-size: 1rem; color: #c9a96e; margin: 0;
}
.sheet-divider {
    border: none; border-top: 1px solid #f0e8e0; margin: 16px 0;
}
.section-label {
    font-family: 'DM Sans', sans-serif;
    font-size: 11px; font-weight: 500;
    letter-spacing: .18em; text-transform: uppercase;
    color: #8b7060; margin: 0 0 12px;
    display: flex; align-items: center; gap: 8px;
}
.required { color: #c0392b; font-size: 13px; letter-spacing: 0; }
.optional-badge {
    background: #f5efe8; color: #b8a090;
    font-size: 9px; padding: 2px 8px;
    border-radius: 10px; letter-spacing: .1em;
    font-weight: 400;
}

/* SIZE */
.size-row { display: flex; gap: 10px; margin-bottom: 4px; }
.size-btn {
    flex: 1; border: 1.5px solid #e0d4c8;
    border-radius: 12px; padding: 10px 6px;
    text-align: center; cursor: pointer;
    transition: all .2s; background: #fff;
}
.size-btn.active { border-color: #1a110d; background: #1a110d; }
.size-letter {
    display: block;
    font-family: 'Playfair Display', serif;
    font-size: 1.1rem; font-weight: 500; color: #1a110d;
}
.size-btn.active .size-letter { color: #f0e6d0; }
.size-price {
    display: block;
    font-family: 'DM Sans', sans-serif;
    font-size: 10px; color: #b8a090; margin-top: 2px;
}
.size-btn.active .size-price { color: #c9a96e; }

/* OPTIONS (ĐƯỜNG, ĐÁ) */
.option-row { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 4px; }
.option-chip {
    border: 1.5px solid #e0d4c8; border-radius: 20px;
    padding: 6px 14px;
    font-family: 'DM Sans', sans-serif;
    font-size: 12px; color: #6b5a4a;
    cursor: pointer; transition: all .2s; background: #fff;
}
.option-chip.active {
    border-color: #c9a96e; background: #c9a96e;
    color: #1a110d; font-weight: 500;
}

/* TOPPING */
.topping-grid {
    display: grid; grid-template-columns: 1fr 1fr;
    gap: 8px; margin-bottom: 4px;
}
.topping-item {
    display: flex; align-items: center; gap: 10px;
    border: 1.5px solid #e0d4c8; border-radius: 12px;
    padding: 10px 12px; cursor: pointer;
    transition: all .2s; background: #fff;
}
.topping-item.active { border-color: #c9a96e; background: #fdf8f2; }
.tp-check {
    width: 20px; height: 20px; border-radius: 6px;
    border: 1.5px solid #d0c4b4; background: #fff;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; transition: all .2s;
}
.tp-check svg { display: none; stroke: #fff; }
.topping-item.active .tp-check { background: #c9a96e; border-color: #c9a96e; }
.topping-item.active .tp-check svg { display: block; }
.tp-name {
    display: block;
    font-family: 'DM Sans', sans-serif;
    font-size: 12px; font-weight: 500; color: #1a110d;
}
.tp-price {
    display: block;
    font-family: 'DM Sans', sans-serif;
    font-size: 11px; color: #b8a090;
}

/* NOTE */
.note-input {
    width: 100%; border: 1.5px solid #e0d4c8;
    border-radius: 12px; padding: 10px 14px;
    font-family: 'DM Sans', sans-serif;
    font-size: 13px; color: #1a110d;
    resize: none; outline: none;
    transition: border-color .2s; background: #fff;
}
.note-input:focus { border-color: #c9a96e; }
.note-input::placeholder { color: #c0b0a0; }

/* CONFIRM ROW */
.confirm-row {
    display: flex; align-items: center; gap: 12px; margin-top: 20px;
}
.qty-wrap {
    display: flex; align-items: center;
    border: 1.5px solid #e0d4c8; border-radius: 12px; overflow: hidden;
    flex-shrink: 0;
}
.qty-btn {
    width: 38px; height: 42px; border: none; background: #fff;
    color: #1a110d; font-size: 20px; cursor: pointer;
    transition: background .18s;
    display: flex; align-items: center; justify-content: center;
    font-family: 'DM Sans', sans-serif;
}
.qty-btn:hover { background: #f5efe8; }
.qty-num {
    width: 34px; text-align: center;
    font-family: 'Playfair Display', serif;
    font-size: 15px; font-weight: 500; color: #1a110d;
    border-left: 1px solid #e0d4c8; border-right: 1px solid #e0d4c8;
    height: 42px; display: flex; align-items: center; justify-content: center;
}
.btn-confirm {
    flex: 1;
    display: flex; align-items: center; justify-content: center; gap: 8px;
    background: #1a110d; color: #f0e6d0;
    border: none; border-radius: 12px; height: 42px;
    font-family: 'DM Sans', sans-serif;
    font-size: 13px; font-weight: 500; cursor: pointer;
    transition: background .22s, transform .15s;
}
.btn-confirm:hover { background: #c9a96e; color: #1a110d; }
.btn-confirm:active { transform: scale(.97); }

/* TOAST */
.toast-wrap {
    position: fixed; top: 24px; left: 50%;
    transform: translateX(-50%) translateY(-90px);
    background: #1a110d; color: #f0e6d0;
    padding: 10px 20px; border-radius: 30px;
    display: flex; align-items: center; gap: 8px;
    font-family: 'DM Sans', sans-serif;
    font-size: 13px; font-weight: 500;
    white-space: nowrap; z-index: 99999;
    transition: transform .38s cubic-bezier(.25,.8,.25,1);
    pointer-events: none;
}
.toast-wrap.show { transform: translateX(-50%) translateY(0); }
.toast-icon {
    width: 20px; height: 20px;
    background: #c9a96e; border-radius: 50%;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}

/* RESPONSIVE */
@media (max-width: 768px) {
    .menu-section { padding: 36px 14px 60px; }
    .menu-grid { gap: 16px; }
    .topping-grid { grid-template-columns: 1fr; }
    .sheet-body { padding: 16px 16px 28px; }
}
@media (max-width: 480px) {
    .menu-grid { grid-template-columns: 1fr; }
    .btn-add-cart span { display: none; }
    .btn-add-cart { padding: 9px 12px; }
}
</style>

<script>
var modalState = { productId: null, basePrice: 0, sizeExtra: 0, toppingTotal: 0, qty: 1 };
var cartTotal = 0;

function openModal(id, name, price, cat, imgUrl) {
    modalState = { productId: id, basePrice: price, sizeExtra: 0, toppingTotal: 0, qty: 1 };

    document.getElementById('sheetCat').textContent = cat;
    document.getElementById('sheetName').textContent = name;
    document.getElementById('priceBase').textContent = fmtPrice(price);
    document.getElementById('sheetImg').src = imgUrl;
    document.getElementById('qtyNum').textContent = '1';
    document.getElementById('noteInput').value = '';

    // reset size
    var sizeBtns = document.querySelectorAll('.size-btn');
    sizeBtns.forEach(function(b, i) { b.classList.toggle('active', i === 0); });
    modalState.sizeExtra = 0;

    // reset options
    document.querySelectorAll('.option-chip[data-group="sugar"]').forEach(function(b, i, arr) {
        b.classList.toggle('active', i === arr.length - 1);
    });
    document.querySelectorAll('.option-chip[data-group="ice"]').forEach(function(b, i, arr) {
        b.classList.toggle('active', i === arr.length - 1);
    });

    // reset toppings
    document.querySelectorAll('.topping-item').forEach(function(t) { t.classList.remove('active'); });
    modalState.toppingTotal = 0;

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
    document.querySelectorAll('.size-btn').forEach(function(b) { b.classList.remove('active'); });
    el.classList.add('active');
    modalState.sizeExtra = parseInt(el.dataset.extra) || 0;
    updateTotal();
}

function selectOption(el, group) {
    document.querySelectorAll('.option-chip[data-group="' + group + '"]').forEach(function(b) {
        b.classList.remove('active');
    });
    el.classList.add('active');
}

function toggleTopping(el) {
    el.classList.toggle('active');
    var total = 0;
    document.querySelectorAll('.topping-item.active').forEach(function(item) {
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
    var toppings = Array.from(document.querySelectorAll('.topping-item.active')).map(function(el) {
        return el.querySelector('.tp-name').textContent;
    });
    var note = document.getElementById('noteInput').value.trim();
    var name = document.getElementById('sheetName').textContent;

    fetch('{{ url("/cart/add") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
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
    .then(function(res) { return res.json(); })
    .then(function(data) {
        if (data.success !== false) {
            cartTotal = data.cart_count !== undefined ? data.cart_count : cartTotal + modalState.qty;
            document.getElementById('cart-count').textContent = cartTotal;
        }
    })
    .catch(function() {});

    closeModal();

    var topStr = toppings.length ? ' + ' + toppings.length + ' topping' : '';
    showToast('Đã thêm: ' + name + ' (' + size + ')' + topStr + ' x' + modalState.qty);
}

function showToast(msg) {
    document.getElementById('toastMsg').textContent = msg;
    var t = document.getElementById('toastWrap');
    t.classList.add('show');
    setTimeout(function() { t.classList.remove('show'); }, 2800);
}

function fmtPrice(n) {
    return n.toLocaleString('vi-VN') + 'đ';
}
</script>

</body>
</html>