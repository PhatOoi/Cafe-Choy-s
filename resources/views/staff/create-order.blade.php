@extends('staff.layout')

@section('title', 'Tạo đơn tại quán')
@section('page-title', 'Tạo đơn tại quán')

@section('styles')
<style>
    .create-grid {
        display: grid;
        grid-template-columns: 1fr 360px;
        gap: 20px;
        align-items: flex-start;
    }
    @media (max-width: 960px) { .create-grid { grid-template-columns: 1fr; } }

    /* ── Category tabs ── */
    .cat-tabs {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 16px;
    }
    .cat-tab {
        padding: 6px 16px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        border: 1px solid #e0e4ee;
        background: #fff;
        color: #666;
        transition: all .15s;
    }
    .cat-tab:hover, .cat-tab.active { background: var(--primary); color: #fff; border-color: var(--primary); }

    /* ── Product grid ── */
    .product-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 12px;
    }

    .product-card {
        background: #fff;
        border: 1.5px solid #eef0f4;
        border-radius: 12px;
        padding: 14px 12px;
        cursor: pointer;
        transition: all .18s;
        text-align: center;
        position: relative;
        user-select: none;
    }
    .product-card:hover { border-color: var(--primary); box-shadow: 0 4px 16px rgba(212,129,58,.15); }
    .product-card.selected { border-color: var(--primary); background: #fffaf6; }

    .product-card .product-img {
        width: 60px; height: 60px;
        border-radius: 10px;
        object-fit: cover;
        margin: 0 auto 8px;
        display: block;
        background: #f4f6fb;
    }
    .product-card .product-name { font-size: 13px; font-weight: 600; color: #1a1a2e; }
    .product-card .product-price { font-size: 13px; color: var(--primary); font-weight: 600; margin-top: 3px; }

    .product-card .in-cart-badge {
        position: absolute;
        top: -8px; right: -8px;
        background: var(--primary);
        color: #fff;
        border-radius: 50%;
        width: 22px; height: 22px;
        font-size: 11px;
        font-weight: 700;
        display: flex; align-items: center; justify-content: center;
        display: none;
    }
    .product-card.selected .in-cart-badge { display: flex; }

    /* ── Cart (right panel) ── */
    .cart-panel {
        background: #fff;
        border-radius: 12px;
        border: 1px solid #eef0f4;
        position: sticky;
        top: 80px;
    }
    .cart-header {
        padding: 16px 20px;
        border-bottom: 1px solid #f0f2f5;
        font-size: 15px;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .cart-items { padding: 12px 20px; max-height: 400px; overflow-y: auto; }

    .cart-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 0;
        border-bottom: 1px solid #f3f4f8;
    }
    .cart-item:last-child { border-bottom: none; }
    .cart-item-name { font-size: 13px; font-weight: 500; flex: 1; }
    .cart-item-price { font-size: 13px; font-weight: 600; color: var(--primary); }

    .qty-control {
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .qty-btn {
        width: 26px; height: 26px;
        border-radius: 7px;
        border: 1px solid #e0e4ee;
        background: #f8f9fc;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        transition: all .15s;
        line-height: 1;
        padding: 0;
    }
    .qty-btn:hover { background: var(--primary); color: #fff; border-color: var(--primary); }
    .qty-value { font-size: 14px; font-weight: 600; min-width: 20px; text-align: center; }

    .cart-footer { padding: 16px 20px; border-top: 1px solid #f0f2f5; }
    .total-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 14px;
    }
    .total-label { font-size: 13px; color: #8a8fa8; }
    .total-amount { font-size: 22px; font-weight: 700; color: var(--primary); }

    .payment-select {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #e0e4ee;
        border-radius: 8px;
        font-size: 14px;
        font-family: 'Poppins', sans-serif;
        margin-bottom: 12px;
        outline: none;
        background: #f8f9fc;
    }
    .payment-select:focus { border-color: var(--primary); }

    .loyalty-lookup-wrap {
        background: #fffbf2;
        border: 1px solid #fde68a;
        border-radius: 10px;
        padding: 12px 14px;
        margin-bottom: 12px;
    }
    .loyalty-lookup-label {
        font-size: 12px;
        font-weight: 700;
        color: #92400e;
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: .04em;
    }
    .loyalty-lookup-row {
        display: flex;
        gap: 8px;
        align-items: center;
    }
    .customer-found-card {
        background: #f0fdf4;
        border: 1px solid #86efac;
        border-radius: 8px;
        padding: 8px 12px;
        font-size: 13px;
    }
    .customer-found-name { font-weight: 700; color: #15803d; }
    .customer-found-pts  { color: #6b7280; font-size: 12px; margin-top: 2px; }
    .customer-clear-btn  { font-size: 11px; color: #ef4444; cursor: pointer; text-decoration: underline; margin-top: 4px; display: inline-block; }
    .customer-not-found  { font-size: 13px; color: #ef4444; }

    .btn-submit-order {
        width: 100%;
        padding: 14px;
        background: var(--primary);
        color: #fff;
        border: none;
        border-radius: 10px;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        font-family: 'Poppins', sans-serif;
        transition: background .18s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    .btn-submit-order:hover { background: var(--primary-dark); }
    .btn-submit-order:disabled { background: #ccc; cursor: not-allowed; }

    .empty-cart {
        text-align: center;
        padding: 30px;
        color: #ccc;
    }
    .empty-cart i { font-size: 36px; margin-bottom: 8px; display: block; }

    .search-product {
        padding: 9px 14px;
        border: 1px solid #e0e4ee;
        border-radius: 8px;
        font-size: 13px;
        font-family: 'Poppins', sans-serif;
        width: 100%;
        margin-bottom: 14px;
        outline: none;
        background: #f8f9fc;
    }
    .search-product:focus { border-color: var(--primary); background: #fff; }

    .note-input {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #e0e4ee;
        border-radius: 8px;
        font-size: 13px;
        font-family: 'Poppins', sans-serif;
        resize: none;
        outline: none;
        background: #f8f9fc;
        margin-bottom: 12px;
    }
    .note-input:focus { border-color: var(--primary); background: #fff; }

    .cart-item {
        align-items: flex-start;
    }
    .cart-item-main {
        flex: 1;
        min-width: 0;
    }
    .cart-item-meta {
        font-size: 11px;
        color: #8a8fa8;
        line-height: 1.5;
        margin-top: 4px;
        white-space: normal;
    }

    .staff-option-modal {
        position: fixed;
        inset: 0;
        z-index: 9999;
        background: rgba(15, 23, 42, 0.45);
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    .staff-option-modal.open { display: flex; }
    .staff-option-sheet {
        width: min(720px, 100%);
        max-height: 88vh;
        overflow-y: auto;
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.18);
        border: 1px solid #eef0f4;
    }
    .staff-option-head {
        padding: 18px 20px 14px;
        border-bottom: 1px solid #f0f2f5;
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: flex-start;
    }
    .staff-option-title {
        margin: 0;
        font-size: 22px;
        font-weight: 700;
        color: #1a1a2e;
    }
    .staff-option-price {
        margin-top: 6px;
        font-size: 14px;
        color: var(--primary);
        font-weight: 600;
    }
    .staff-option-close {
        border: none;
        background: #f7f8fb;
        color: #8a8fa8;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        cursor: pointer;
        font-size: 18px;
    }
    .staff-option-body {
        padding: 18px 20px 20px;
    }
    .staff-option-section {
        margin-bottom: 16px;
        padding: 14px;
        border: 1px solid #eef0f4;
        border-radius: 14px;
        background: #fcfcfe;
    }
    .staff-option-label {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #6b7280;
        margin-bottom: 10px;
    }
    .staff-option-row {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }
    .staff-chip {
        border: 1px solid #dfe5f0;
        background: #fff;
        color: #495066;
        padding: 9px 14px;
        border-radius: 999px;
        font-size: 12px;
        cursor: pointer;
        transition: all .16s;
    }
    .staff-chip.active {
        background: #fff6ef;
        color: var(--primary);
        border-color: rgba(212,129,58,.45);
        box-shadow: 0 6px 16px rgba(212,129,58,.12);
    }
    .staff-topping-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 10px;
    }
    .staff-topping-item {
        border: 1px solid #dfe5f0;
        background: #fff;
        border-radius: 12px;
        padding: 11px 12px;
        cursor: pointer;
        transition: all .16s;
    }
    .staff-topping-item.active {
        border-color: rgba(212,129,58,.45);
        background: #fff8f1;
        box-shadow: 0 8px 18px rgba(212,129,58,.12);
    }
    .staff-topping-name {
        font-size: 13px;
        font-weight: 600;
        color: #1a1a2e;
    }
    .staff-topping-price {
        font-size: 12px;
        color: var(--primary);
        margin-top: 3px;
    }
    .staff-textarea {
        width: 100%;
        min-height: 88px;
        border-radius: 12px;
        border: 1px solid #dfe5f0;
        background: #fff;
        padding: 12px 14px;
        font-size: 13px;
        outline: none;
        resize: vertical;
    }
    .staff-option-actions {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: center;
        margin-top: 18px;
    }
    .staff-option-total {
        font-size: 20px;
        font-weight: 700;
        color: #1a1a2e;
    }
    .staff-option-buttons {
        display: flex;
        gap: 10px;
    }
    .btn-option-secondary,
    .btn-option-primary {
        border: none;
        border-radius: 10px;
        padding: 11px 16px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        font-family: 'Poppins', sans-serif;
    }
    .btn-option-secondary {
        background: #eef1f7;
        color: #52607a;
    }
    .btn-option-primary {
        background: var(--primary);
        color: #fff;
    }

    .staff-qr-modal {
        position: fixed;
        inset: 0;
        z-index: 10000;
        background: rgba(15, 23, 42, 0.62);
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    .staff-qr-modal.open { display: flex; }
    .staff-qr-dialog {
        width: min(1040px, 100%);
        background: #fff;
        border-radius: 18px;
        border: 1px solid #e8ebf2;
        box-shadow: 0 28px 80px rgba(15, 23, 42, 0.3);
        overflow: hidden;
    }
    .staff-qr-header {
        padding: 18px 24px;
        border-bottom: 1px solid #eef0f4;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }
    .staff-qr-title {
        margin: 0;
        font-size: 24px;
        font-weight: 700;
        color: #1a1a2e;
    }
    .staff-qr-close {
        border: none;
        background: transparent;
        color: #b4bac9;
        font-size: 30px;
        line-height: 1;
        cursor: pointer;
    }
    .staff-qr-body {
        display: grid;
        grid-template-columns: 1fr 1fr;
    }
    @media (max-width: 860px) {
        .staff-qr-body { grid-template-columns: 1fr; }
    }
    .staff-qr-col {
        padding: 26px 22px 24px;
    }
    .staff-qr-col + .staff-qr-col {
        border-left: 1px solid #eef0f4;
    }
    @media (max-width: 860px) {
        .staff-qr-col + .staff-qr-col { border-left: none; border-top: 1px solid #eef0f4; }
    }
    .staff-qr-col-title {
        margin: 0 0 18px;
        font-size: 18px;
        font-weight: 700;
        color: #666;
        line-height: 1.45;
    }
    .staff-qr-frame {
        width: 274px;
        max-width: 100%;
        margin: 0 auto;
        padding: 12px;
        background: #fff;
        border: 2px solid #edf1f5;
    }
    .staff-qr-frame img {
        width: 100%;
        display: block;
    }
    .staff-bank-card {
        padding: 18px 20px;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        background: #fff;
    }
    .staff-bank-name {
        font-size: 22px;
        font-weight: 700;
        color: #16a34a;
        margin-bottom: 14px;
    }
    .staff-bank-line {
        font-size: 15px;
        color: #666;
        margin-bottom: 8px;
        line-height: 1.5;
    }
    .staff-bank-line strong {
        font-weight: 700;
        color: #666;
    }
    .staff-bank-line span {
        color: #16a34a;
    }
    .staff-bank-line code {
        font-family: 'Poppins', sans-serif;
        font-size: 15px;
        font-weight: 700;
        color: #374151;
        background: transparent;
        padding: 0;
    }
    .staff-qr-warning {
        margin-top: 16px;
        padding: 14px 16px;
        border-radius: 10px;
        background: #fff5d9;
        border: 1px solid #fde68a;
        color: #a16207;
        font-size: 13px;
        line-height: 1.5;
    }
    .staff-qr-actions {
        margin-top: 20px;
        display: flex;
        gap: 12px;
        justify-content: flex-start;
        flex-wrap: wrap;
    }
    .staff-qr-btn,
    .staff-qr-btn-secondary {
        border: none;
        border-radius: 10px;
        padding: 12px 18px;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        font-family: 'Poppins', sans-serif;
    }
    .staff-qr-btn {
        background: #28a745;
        color: #fff;
    }
    .staff-qr-btn-secondary {
        background: #eef1f7;
        color: #52607a;
    }
    .staff-bill-modal {
        position: fixed;
        inset: 0;
        z-index: 10000;
        background: rgba(15, 23, 42, 0.62);
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    .staff-bill-modal.open { display: flex; }
    .staff-bill-dialog {
        width: min(390px, 100%);
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.28);
        overflow: hidden;
    }
    .staff-bill-body {
        padding: 16px 16px 18px;
        color: #111;
        font-family: Arial, sans-serif;
    }
    .staff-bill-logo {
        text-align: center;
        margin-bottom: 4px;
    }
    .staff-bill-logo img {
        height: 90px;
        object-fit: contain;
    }
    .staff-bill-center {
        text-align: center;
        font-size: 13px;
        color: #111;
    }
    .staff-bill-title {
        text-align: center;
        margin: 10px 0 8px;
        font-size: 16px;
        font-weight: 700;
        color: #111;
    }
    .staff-bill-row {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        font-size: 13px;
        color: #111;
        margin-bottom: 4px;
    }
    .staff-bill-table-head {
        display: flex;
        font-size: 13px;
        font-weight: 700;
        margin: 8px 0 0;
        text-align: left;
        color: #111;
        border-bottom: 1px dashed #ddd;
        padding-bottom: 6px;
    }
    .staff-bill-col-name { width: 46%; padding-right: 8px; }
    .staff-bill-col-price { width: 18%; text-align: right; }
    .staff-bill-col-qty { width: 10%; text-align: center; }
    .staff-bill-col-total { width: 26%; text-align: right; }
    .staff-bill-items { color: #111; }
    .staff-bill-item {
        padding: 6px 0 8px;
        border-bottom: 1px dashed #efefef;
    }
    .staff-bill-item-row {
        display: flex;
        align-items: flex-start;
        font-size: 12px;
        color: #111;
    }
    .staff-bill-item-name {
        font-size: 12px;
        font-weight: 600;
        line-height: 1.35;
        word-break: break-word;
    }
    .staff-bill-item-meta {
        font-size: 10px;
        color: #888;
        font-style: italic;
        margin-top: 2px;
        line-height: 1.4;
    }
    .staff-bill-total-row {
        display: flex;
        justify-content: space-between;
        font-size: 13px;
        color: #111;
        margin-top: 6px;
    }
    .staff-bill-pay-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin: 4px 0;
        font-size: 16px;
        font-weight: 700;
        color: #111;
    }
    .staff-bill-pay-row strong:last-child { font-size: 20px; }
    .staff-bill-actions {
        text-align: center;
        margin-top: 12px;
    }
    .staff-bill-btn,
    .staff-bill-btn-secondary {
        border: none;
        border-radius: 8px;
        padding: 9px 16px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        font-family: Arial, sans-serif;
        margin: 0 4px;
    }
    .staff-bill-btn {
        background: #28a745;
        color: #fff;
    }
    .staff-bill-btn-secondary {
        background: #eef1f7;
        color: #52607a;
    }
</style>
@endsection

@section('content')

<form id="orderForm" action="{{ route('staff.store-order') }}" method="POST">
@csrf
<div class="create-grid">

    {{-- ── LEFT: Product picker ── --}}
    <div>
        <div class="card">
            <div class="card-header"><i class="fas fa-coffee" style="color:var(--primary);margin-right:8px;"></i>Chọn sản phẩm</div>
            <div class="card-body">
                <input type="text" class="search-product" id="searchProduct" placeholder="🔍 Tìm tên sản phẩm...">

                {{-- Category tabs --}}
                <div class="cat-tabs">
                    <button type="button" class="cat-tab active" data-cat="all">Tất cả</button>
                    @foreach($products as $catName => $items)
                    <button type="button" class="cat-tab" data-cat="{{ $catName }}">{{ $catName }}</button>
                    @endforeach
                </div>

                {{-- Product grid --}}
                <div class="product-grid" id="productGrid">
                    @foreach($products as $catName => $items)
                        @foreach($items as $product)
                        <div class="product-card"
                             data-id="{{ $product->id }}"
                             data-name="{{ $product->name }}"
                             data-price="{{ $product->price }}"
                             data-cat="{{ $catName }}"
                             data-category-slug="{{ \Illuminate\Support\Str::slug($product->category->name ?? $catName) }}"
                             onclick="openOptionModal({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $product->price }}, '{{ \Illuminate\Support\Str::slug($product->category->name ?? $catName) }}')">
                            <span class="in-cart-badge" id="badge-{{ $product->id }}">1</span>
                           <img src="{{ 
                                $product->image_url 
                                    ? (Str::startsWith($product->image_url, ['http://', 'https://']) 
                                        ? $product->image_url 
                                        : asset('images/' . $product->image_url)) 
                                    : asset('images/logo.png') 
                            }}" class="product-img"
                            onerror="this.onerror=null;this.src='{{ asset('images/logo.png') }}';">
                            <div class="product-name">{{ $product->name }}</div>
                            <div class="product-price">{{ number_format($product->price, 0, ',', '.') }}đ</div>
                        </div>
                        @endforeach
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- ── RIGHT: Cart ── --}}
    <div class="cart-panel">
        <div class="cart-header">
            <span><i class="fas fa-shopping-basket" style="color:var(--primary);margin-right:8px;"></i>Đơn hàng</span>
            <span id="cartCount" style="font-size:13px;color:#8a8fa8;font-weight:400;">0 món</span>
        </div>

        <div class="cart-items" id="cartItems">
            <div class="empty-cart" id="emptyCart">
                <i class="fas fa-coffee"></i>
                <p style="font-size:13px;">Chưa có sản phẩm nào</p>
            </div>
        </div>

        <div class="cart-footer">
            <div class="total-row">
                <span class="total-label">Tổng cộng</span>
                <span class="total-amount" id="totalDisplay">0đ</span>
            </div>

            <select class="payment-select" name="payment_method" id="paymentMethodSelect">
                <option value="cash">💵 Tiền mặt</option>
                <option value="momo">📱 MoMo</option>
                <option value="bank_transfer">🏦 Chuyển khoản</option>
                <option value="vnpay">💳 VNPay</option>
            </select>

            {{-- Tích điểm khách hàng --}}
            <div class="loyalty-lookup-wrap">
                <div class="loyalty-lookup-label"><i class="fas fa-star" style="color:#f59e0b;margin-right:5px;"></i>Tích điểm khách hàng</div>
                <div class="loyalty-lookup-row">
                    <input type="text" id="customerPhoneInput" placeholder="Nhập SĐT khách..." inputmode="tel" maxlength="15"
                           style="flex:1;border:1px solid #e2e6ef;border-radius:8px;padding:8px 12px;font-size:13px;outline:none;">
                    <button type="button" id="lookupBtn" onclick="lookupCustomer()"
                            style="padding:8px 14px;background:var(--primary);color:#fff;border:none;border-radius:8px;cursor:pointer;font-size:13px;white-space:nowrap;">
                        Tra cứu
                    </button>
                </div>
                <div id="customerResult" style="margin-top:8px;display:none;"></div>
                <input type="hidden" name="customer_phone" id="customerPhoneHidden">
                <input type="hidden" name="use_points" id="usePointsHidden" value="0">
            </div>

            <textarea class="note-input" name="note" rows="2" placeholder="Ghi chú đơn hàng (tuỳ chọn)..."></textarea>
            <input type="hidden" name="qr_note" id="staffQrNoteInput" value="">

            {{-- Hidden inputs sẽ được JS tạo --}}
            <div id="hiddenInputs"></div>

            <button type="submit" class="btn-submit-order" id="submitBtn" disabled>
                <i class="fas fa-check-circle"></i> Tạo đơn & thanh toán
            </button>
        </div>
    </div>
</div>
</form>

<div class="staff-option-modal" id="staffOptionModal" onclick="closeOptionModal(event)">
    <div class="staff-option-sheet">
        <div class="staff-option-head">
            <div>
                <h3 class="staff-option-title" id="optionProductName">Tùy chỉnh sản phẩm</h3>
                <div class="staff-option-price" id="optionBasePrice">0đ</div>
            </div>
            <button type="button" class="staff-option-close" onclick="closeOptionModal()">×</button>
        </div>
        <div class="staff-option-body">
            <div class="staff-option-section" id="optionSizeSection">
                <div class="staff-option-label">Kích cỡ</div>
                <div class="staff-option-row" id="optionSizeRow"></div>
            </div>
            <div class="staff-option-section" id="optionToppingSection">
                <div class="staff-option-label">Topping</div>
                <div class="staff-topping-grid" id="optionToppingGrid"></div>
            </div>
            <div class="staff-option-section" id="optionSugarSection">
                <div class="staff-option-label">Đường & sữa</div>
                <div class="staff-option-row" id="optionSugarRow"></div>
            </div>
            <div class="staff-option-section" id="optionIceSection">
                <div class="staff-option-label">Đá</div>
                <div class="staff-option-row" id="optionIceRow"></div>
            </div>
            <div class="staff-option-section">
                <div class="staff-option-label">Ghi chú</div>
                <textarea class="staff-textarea" id="optionNoteInput" placeholder="Ví dụ: ít ngọt, thêm siro, không đá..."></textarea>
            </div>
            <div class="staff-option-actions">
                <div class="staff-option-total" id="optionTotalPrice">0đ</div>
                <div class="staff-option-buttons">
                    <button type="button" class="btn-option-secondary" onclick="closeOptionModal()">Hủy</button>
                    <button type="button" class="btn-option-primary" onclick="applyOptionSelection()">Thêm vào đơn</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="staff-qr-modal" id="staffQrModal" onclick="closeStaffQrModal(event)">
    <div class="staff-qr-dialog">
        <div class="staff-qr-header">
            <h3 class="staff-qr-title">Thanh toán qua chuyển khoản ngân hàng</h3>
            <button type="button" class="staff-qr-close" onclick="closeStaffQrModal()">×</button>
        </div>
        <div class="staff-qr-body">
            <div class="staff-qr-col">
                <div class="staff-qr-col-title">Cách 1: Mở app ngân hàng / Ví và quét mã QR</div>
                <div class="staff-qr-frame">
                    <img id="staffQrImage" src="" alt="QR chuyển khoản">
                </div>
            </div>
            <div class="staff-qr-col">
                <div class="staff-qr-col-title">Cách 2: Chuyển khoản thủ công theo thông tin</div>
                <div class="staff-bank-card">
                    <div class="staff-bank-name" id="staffQrBankName">Vietcombank</div>
                    <div class="staff-bank-line"><strong>Ngân hàng:</strong> <span id="staffQrBankLabel">Vietcombank</span></div>
                    <div class="staff-bank-line"><strong>Thụ hưởng:</strong> <code id="staffQrAccountName">TRAN QUOC LONG</code></div>
                    <div class="staff-bank-line"><strong>Số tài khoản:</strong> <code id="staffQrAccountNumber">1042131375</code></div>
                    <div class="staff-bank-line"><strong>Số tiền:</strong> <code id="staffQrAmount">0 đ</code></div>
                    <div class="staff-bank-line"><strong>Nội dung CK:</strong> <code id="staffQrReference">DH0000</code></div>

                    <div class="staff-qr-warning">
                        <strong>Lưu ý:</strong> Vui lòng giữ nguyên nội dung chuyển khoản <strong id="staffQrWarningReference">DH0000</strong> để dễ đối soát thanh toán.
                    </div>

                    <div class="staff-qr-actions">
                        <button type="button" class="staff-qr-btn" onclick="submitBankTransferOrder()">Đã nhận chuyển khoản, tạo đơn</button>
                        <button type="button" class="staff-qr-btn-secondary" onclick="closeStaffQrModal()">Đóng</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="staff-bill-modal" id="staffCashBillModal" onclick="closeStaffCashBillModal(event)">
    <div class="staff-bill-dialog">
        <div class="staff-bill-body">
            <div class="staff-bill-logo">
                <img src="/images/logo.png" alt="Choy's Cafe Logo">
            </div>
            <div class="staff-bill-center">Tòa JOVE, Trung Mỹ Tây, Quận 12<br>0904xxxxxx</div>
            <div class="staff-bill-title">HÓA ĐƠN <span id="staffBillCode"></span></div>
            <div class="staff-bill-row">
                <span>Thời gian</span>
                <span><span id="staffBillTime"></span> <span id="staffBillDate"></span></span>
            </div>
            <div class="staff-bill-table-head">
                <div class="staff-bill-col-name">Tên sản phẩm</div>
                <div class="staff-bill-col-price">Giá tiền</div>
                <div class="staff-bill-col-qty">SL</div>
                <div class="staff-bill-col-total">Tổng cộng</div>
            </div>
            <div class="staff-bill-items" id="staffBillItems"></div>
            <div class="staff-bill-total-row">
                <span>Tổng dịch vụ</span>
                <span id="staffBillSubtotal">0</span>
            </div>
            <div class="staff-bill-pay-row">
                <strong>Thanh toán</strong>
                <strong id="staffBillTotal">0</strong>
            </div>
            <div class="staff-bill-row">
                <span>Mã hóa đơn</span>
                <span id="staffBillCode2"></span>
            </div>
            <div class="staff-bill-row">
                <span>Nhân viên trực ca</span>
                <span>{{ Auth::user()->name ?? '-' }}</span>
            </div>
            <div class="staff-bill-center" style="margin-top:8px;">
                Quý khách vui lòng kiểm tra lại hóa đơn trước khi thanh toán<br>
                Xin cảm ơn quý khách.<br>
                Hẹn gặp lại quý khách lần sau
            </div>
            <div class="staff-bill-actions">
                <button type="button" class="staff-bill-btn" onclick="submitCashOrder()">Xác nhận thanh toán</button>
                <button type="button" class="staff-bill-btn-secondary" onclick="closeStaffCashBillModal()">Đóng</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
const sizeOptions = @json($sizes->map(fn($size) => ['name' => $size->name, 'extra_price' => (float) $size->extra_price])->values());
const toppingOptions = @json($toppings->map(fn($extra) => ['id' => $extra->id, 'name' => $extra->name, 'price' => (float) $extra->price])->values());
const sugarOptions = @json($sugars->pluck('name')->values());
const iceOptions = @json($ices->pluck('name')->values());
const nextOrderNumber = @json($nextOrderNumber);
const staffBankInfo = {
    bankSlug: 'vietcombank',
    bankName: 'Vietcombank',
    accountName: 'TRAN QUOC LONG',
    accountNumber: '1042131375',
};

const cart = {};
let optionState = null;
let staffQrReference = '';
let staffBillCode = '';

function formatPrice(value) {
    return Number(value || 0).toLocaleString('vi-VN') + 'đ';
}

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function escapeJsSingleQuote(value) {
    return String(value ?? '').replace(/\\/g, '\\\\').replace(/'/g, "\\'");
}

function createFingerprint(config) {
    return [
        config.productId,
        config.size,
        config.sugar,
        config.ice,
        (config.toppings || []).slice().sort().join(','),
        (config.note || '').trim()
    ].join('||');
}

function sumProductQty(productId) {
    return Object.values(cart).reduce((total, item) => {
        return total + (Number(item.productId) === Number(productId) ? item.quantity : 0);
    }, 0);
}

function buildChipButton(label, value, group, isActive) {
    return `<button type="button" class="staff-chip${isActive ? ' active' : ''}" data-group="${escapeHtml(group)}" data-value="${escapeHtml(value)}" onclick="selectOptionChip('${escapeJsSingleQuote(group)}', '${escapeJsSingleQuote(value)}')">${escapeHtml(label)}</button>`;
}

function optionRuleState(categorySlug, productName) {
    const lowerName = (productName || '').toLowerCase();

    const state = {
        size: true,
        topping: true,
        sugar: true,
        ice: true,
    };

    if (categorySlug === 'tra-sua') {
        state.sugar = false;
    } else if (categorySlug === 'da-xay') {
        state.topping = false;
        state.sugar = false;
        state.ice = false;
    } else if (categorySlug === 'nuoc-ep' || categorySlug === 'nuoc-ep-sinh-to') {
        state.topping = false;
        state.sugar = false;
        if (lowerName.includes('sinh tố')) {
            state.ice = false;
        }
    } else if (categorySlug === 'ca-phe') {
        state.topping = false;
    } else if (categorySlug === 'tra-va-thuc-uong-theo-mua') {
        state.topping = false;
        state.sugar = false;
    } else if (categorySlug === 'banh-snack') {
        state.size = false;
        state.topping = false;
        state.sugar = false;
        state.ice = false;
    }

    return state;
}

function renderOptionSections() {
    if (!optionState) return;

    document.getElementById('optionProductName').textContent = optionState.name;
    document.getElementById('optionBasePrice').textContent = 'Giá gốc: ' + formatPrice(optionState.basePrice);
    document.getElementById('optionNoteInput').value = optionState.note || '';

    document.getElementById('optionSizeRow').innerHTML = sizeOptions.map((size) => {
        const label = size.extra_price > 0
            ? `${size.name} (+${formatPrice(size.extra_price)})`
            : `${size.name} (mặc định)`;
        return buildChipButton(label, size.name, 'size', optionState.size === size.name);
    }).join('');

    document.getElementById('optionToppingGrid').innerHTML = toppingOptions.map((topping) => {
        const active = optionState.toppings.includes(topping.name);
        return `
            <button type="button" class="staff-topping-item${active ? ' active' : ''}" onclick="toggleTopping('${escapeJsSingleQuote(topping.name)}')">
                <div class="staff-topping-name">${escapeHtml(topping.name)}</div>
                <div class="staff-topping-price">+${formatPrice(topping.price)}</div>
            </button>
        `;
    }).join('');

    document.getElementById('optionSugarRow').innerHTML = sugarOptions.map((sugar, index) => {
        return buildChipButton(sugar, sugar, 'sugar', optionState.sugar === sugar || (!optionState.sugar && index === sugarOptions.length - 1));
    }).join('');

    document.getElementById('optionIceRow').innerHTML = iceOptions.map((ice, index) => {
        return buildChipButton(ice, ice, 'ice', optionState.ice === ice || (!optionState.ice && index === iceOptions.length - 1));
    }).join('');

    const rules = optionRuleState(optionState.categorySlug, optionState.name);
    document.getElementById('optionSizeSection').style.display = rules.size ? '' : 'none';
    document.getElementById('optionToppingSection').style.display = rules.topping ? '' : 'none';
    document.getElementById('optionSugarSection').style.display = rules.sugar ? '' : 'none';
    document.getElementById('optionIceSection').style.display = rules.ice ? '' : 'none';

    updateOptionTotal();
}

function updateOptionTotal() {
    if (!optionState) return;

    const sizeExtra = Number((sizeOptions.find((size) => size.name === optionState.size) || {}).extra_price || 0);
    const toppingExtra = toppingOptions
        .filter((topping) => optionState.toppings.includes(topping.name))
        .reduce((sum, topping) => sum + Number(topping.price || 0), 0);

    optionState.totalPrice = optionState.basePrice + sizeExtra + toppingExtra;
    document.getElementById('optionTotalPrice').textContent = formatPrice(optionState.totalPrice);
}

function openOptionModal(id, name, price, categorySlug) {
    optionState = {
        productId: id,
        name,
        basePrice: Number(price),
        totalPrice: Number(price),
        categorySlug,
        size: 'S',
        sugar: sugarOptions[sugarOptions.length - 1] || '',
        ice: iceOptions[iceOptions.length - 1] || '',
        toppings: [],
        note: '',
    };

    renderOptionSections();
    document.getElementById('staffOptionModal').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeOptionModal(event) {
    if (event && event.target && event.target.id !== 'staffOptionModal') {
        return;
    }

    document.getElementById('staffOptionModal').classList.remove('open');
    document.body.style.overflow = '';
    optionState = null;
}

function selectOptionChip(group, value) {
    if (!optionState) return;
    optionState[group] = value;
    renderOptionSections();
}

function toggleTopping(name) {
    if (!optionState) return;

    if (optionState.toppings.includes(name)) {
        optionState.toppings = optionState.toppings.filter((item) => item !== name);
    } else {
        optionState.toppings.push(name);
    }

    renderOptionSections();
}

function applyOptionSelection() {
    if (!optionState) return;

    optionState.note = document.getElementById('optionNoteInput').value.trim();
    updateOptionTotal();

    const payload = {
        productId: optionState.productId,
        name: optionState.name,
        basePrice: optionState.basePrice,
        price: optionState.totalPrice,
        quantity: 1,
        size: optionState.size,
        sugar: optionState.sugar,
        ice: optionState.ice,
        toppings: [...optionState.toppings],
        note: optionState.note,
    };

    const fingerprint = createFingerprint(payload);

    if (cart[fingerprint]) {
        cart[fingerprint].quantity += 1;
    } else {
        cart[fingerprint] = payload;
    }

    closeOptionModal();
    renderCart();
}

function changeQty(key, delta) {
    if (!cart[key]) return;
    cart[key].quantity += delta;
    if (cart[key].quantity <= 0) {
        delete cart[key];
    }
    renderCart();
}

function renderCart() {
    const cartItems = document.getElementById('cartItems');
    const emptyCart = document.getElementById('emptyCart');
    const hiddenInputs = document.getElementById('hiddenInputs');
    const totalDisplay = document.getElementById('totalDisplay');
    const cartCount = document.getElementById('cartCount');
    const submitBtn = document.getElementById('submitBtn');

    const ids = Object.keys(cart);
    const totalItems = ids.reduce((s, id) => s + cart[id].quantity, 0);
    const total = ids.reduce((s, id) => s + cart[id].price * cart[id].quantity, 0);

    // Update count badge on product cards
    document.querySelectorAll('.product-card').forEach(card => {
        const id = card.dataset.id;
        const badge = document.getElementById('badge-' + id);
        const productQty = sumProductQty(id);
        if (productQty > 0) {
            card.classList.add('selected');
            badge.textContent = productQty;
            badge.style.display = 'flex';
        } else {
            card.classList.remove('selected');
            badge.style.display = 'none';
        }
    });

    cartCount.textContent = totalItems + ' món';
    totalDisplay.textContent = total.toLocaleString('vi-VN') + 'đ';
    submitBtn.disabled = ids.length === 0;
    resetStaffQrReference();
    resetStaffBillCode();
    // Áp dụng giảm điểm nếu đang dùng
    if (typeof applyPointsDiscount === 'function') applyPointsDiscount();

    if (ids.length === 0) {
        emptyCart.style.display = 'block';
        // Remove all dynamic cart item rows
        cartItems.querySelectorAll('.cart-item').forEach(el => el.remove());
        hiddenInputs.innerHTML = '';
        return;
    }

    emptyCart.style.display = 'none';

    // Rebuild cart item rows
    cartItems.querySelectorAll('.cart-item').forEach(el => el.remove());
    hiddenInputs.innerHTML = '';

    ids.forEach((id, idx) => {
        const item = cart[id];
        const metaParts = [
            `Size: ${item.size || 'S'}`,
            `Đường & sữa: ${item.sugar || '-'}`,
            `Đá: ${item.ice || '-'}`,
        ];

        if (item.toppings && item.toppings.length) {
            metaParts.push(`Topping: ${item.toppings.map((topping) => escapeHtml(topping)).join(', ')}`);
        }

        if (item.note) {
            metaParts.push(`Ghi chú: ${escapeHtml(item.note)}`);
        }

        // Cart row
        const row = document.createElement('div');
        row.className = 'cart-item';
        row.innerHTML = `
            <div class="cart-item-main">
                <div class="cart-item-name">${escapeHtml(item.name)}</div>
                <div class="cart-item-meta">${metaParts.join('<br>')}</div>
            </div>
            <div class="qty-control">
                <button type="button" class="qty-btn" onclick="changeQty('${id}', -1)">−</button>
                <span class="qty-value">${item.quantity}</span>
                <button type="button" class="qty-btn" onclick="changeQty('${id}', 1)">+</button>
            </div>
            <div class="cart-item-price">${(item.price * item.quantity).toLocaleString('vi-VN')}đ</div>
        `;
        cartItems.insertBefore(row, emptyCart);

        // Hidden inputs for form submission
        hiddenInputs.innerHTML += `
            <input type="hidden" name="items[${idx}][product_id]" value="${item.productId}">
            <input type="hidden" name="items[${idx}][quantity]" value="${item.quantity}">
            <input type="hidden" name="items[${idx}][size]" value="${escapeHtml(item.size || '')}">
            <input type="hidden" name="items[${idx}][sugar]" value="${escapeHtml(item.sugar || '')}">
            <input type="hidden" name="items[${idx}][ice]" value="${escapeHtml(item.ice || '')}">
            <input type="hidden" name="items[${idx}][note]" value="${escapeHtml(item.note || '')}">
        `;

        (item.toppings || []).forEach((topping, toppingIndex) => {
            hiddenInputs.innerHTML += `<input type="hidden" name="items[${idx}][toppings][${toppingIndex}]" value="${escapeHtml(topping)}">`;
        });
    });
}

function buildStaffQrReference() {
    return `STT${nextOrderNumber}-DH${nextOrderNumber}`;
}

function getCartTotal() {
    return Object.keys(cart).reduce((sum, id) => sum + cart[id].price * cart[id].quantity, 0);
}

function ensureStaffQrReference() {
    if (!staffQrReference) {
        staffQrReference = buildStaffQrReference();
    }

    document.getElementById('staffQrNoteInput').value = staffQrReference;
    return staffQrReference;
}

function resetStaffQrReference() {
    staffQrReference = '';
    document.getElementById('staffQrNoteInput').value = '';
}

function openStaffQrModal() {
    const total = getCartTotal();
    const reference = ensureStaffQrReference();

    document.getElementById('staffQrBankName').textContent = staffBankInfo.bankName;
    document.getElementById('staffQrBankLabel').textContent = staffBankInfo.bankName;
    document.getElementById('staffQrAccountName').textContent = staffBankInfo.accountName;
    document.getElementById('staffQrAccountNumber').textContent = staffBankInfo.accountNumber;
    document.getElementById('staffQrAmount').textContent = Number(total).toLocaleString('vi-VN') + ' đ';
    document.getElementById('staffQrReference').textContent = reference;
    document.getElementById('staffQrWarningReference').textContent = reference;

    const qrUrl = `https://img.vietqr.io/image/${staffBankInfo.bankSlug}-${staffBankInfo.accountNumber}-print.png?amount=${encodeURIComponent(total)}&addInfo=${encodeURIComponent(reference)}&accountName=${encodeURIComponent(staffBankInfo.accountName)}`;
    document.getElementById('staffQrImage').src = qrUrl;

    document.getElementById('staffQrModal').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeStaffQrModal(event) {
    if (event && event.target && event.target.id !== 'staffQrModal') {
        return;
    }

    document.getElementById('staffQrModal').classList.remove('open');
    document.body.style.overflow = '';
}

function submitBankTransferOrder() {
    const form = document.getElementById('orderForm');
    closeStaffQrModal();
    form.submit();
}

function ensureStaffBillCode() {
    if (!staffBillCode) {
        staffBillCode = String(nextOrderNumber).padStart(6, '0');
    }

    return staffBillCode;
}

function resetStaffBillCode() {
    staffBillCode = '';
}

function renderStaffCashBill() {
    const now = new Date();
    const code = ensureStaffBillCode();
    const total = getCartTotal();
    const itemsContainer = document.getElementById('staffBillItems');

    document.getElementById('staffBillDate').textContent = now.toLocaleDateString('vi-VN');
    document.getElementById('staffBillTime').textContent = now.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
    document.getElementById('staffBillCode').textContent = code;
    document.getElementById('staffBillCode2').textContent = code;
    document.getElementById('staffBillSubtotal').textContent = Number(total).toLocaleString('vi-VN');
    document.getElementById('staffBillTotal').textContent = Number(total).toLocaleString('vi-VN');

    itemsContainer.innerHTML = Object.keys(cart).map((key) => {
        const item = cart[key];
        const metaLines = [
            `- Size: ${escapeHtml(item.size || '-')}`,
            `- Đường: ${escapeHtml(item.sugar || '-')}`,
            `- Đá: ${escapeHtml(item.ice || '-')}`,
        ];

        if (item.toppings && item.toppings.length) {
            metaLines.push(`- Topping: ${item.toppings.map((topping) => escapeHtml(topping)).join(', ')}`);
        }

        return `
            <div class="staff-bill-item">
                <div class="staff-bill-item-row">
                    <div class="staff-bill-col-name">
                        <div class="staff-bill-item-name">${escapeHtml(item.name)}</div>
                        <div class="staff-bill-item-meta">${metaLines.join('<br>')}</div>
                    </div>
                    <div class="staff-bill-col-price">${Number(item.price).toLocaleString('vi-VN')}</div>
                    <div class="staff-bill-col-qty">${item.quantity}</div>
                    <div class="staff-bill-col-total">${Number(item.price * item.quantity).toLocaleString('vi-VN')}</div>
                </div>
            </div>
        `;
    }).join('');
}

function openStaffCashBillModal() {
    renderStaffCashBill();
    document.getElementById('staffCashBillModal').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeStaffCashBillModal(event) {
    if (event && event.target && event.target.id !== 'staffCashBillModal') {
        return;
    }

    document.getElementById('staffCashBillModal').classList.remove('open');
    document.body.style.overflow = '';
}

function submitCashOrder() {
    const form = document.getElementById('orderForm');
    closeStaffCashBillModal();
    form.submit();
}

// ── Category filter ──
document.querySelectorAll('.cat-tab').forEach(tab => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('.cat-tab').forEach(t => t.classList.remove('active'));
        tab.classList.add('active');

        const cat = tab.dataset.cat;
        document.querySelectorAll('.product-card').forEach(card => {
            card.style.display = (cat === 'all' || card.dataset.cat === cat) ? '' : 'none';
        });
    });
});

// ── Search filter ──
document.getElementById('searchProduct').addEventListener('input', function () {
    const q = this.value.toLowerCase().trim();
    document.querySelectorAll('.product-card').forEach(card => {
        card.style.display = card.dataset.name.toLowerCase().includes(q) ? '' : 'none';
    });
    // Reset active category tab
    if (q) {
        document.querySelectorAll('.cat-tab').forEach(t => t.classList.remove('active'));
    }
});

// ── Confirm before submit ──
document.getElementById('orderForm').addEventListener('submit', function (e) {
    const ids = Object.keys(cart);
    if (ids.length === 0) { e.preventDefault(); return; }

    const total = ids.reduce((s, id) => s + cart[id].price * cart[id].quantity, 0);
    const paymentMethod = document.getElementById('paymentMethodSelect').value;

    if (paymentMethod === 'bank_transfer') {
        e.preventDefault();
        openStaffQrModal();
        return;
    }

    if (paymentMethod === 'cash') {
        e.preventDefault();
        openStaffCashBillModal();
        return;
    }

    resetStaffQrReference();
    const ok = confirm(`Xác nhận tạo đơn tại quán?\nTổng tiền: ${total.toLocaleString('vi-VN')}đ`);
    if (!ok) e.preventDefault();
});

// ── Tra cứu khách hàng theo SĐT ──────────────────────────────────────────
const lookupUrl = '{{ route('staff.orders.lookup-customer') }}';
let linkedCustomer = null;
let currentPointsDiscount = 0;

function getCartBaseTotal() {
    return Object.keys(cart).reduce((s, id) => s + cart[id].price * cart[id].quantity, 0);
}

function applyPointsDiscount() {
    const useEl = document.getElementById('usePointsHidden');
    const totalDisplay = document.getElementById('totalDisplay');
    const base = getCartBaseTotal();

    if (linkedCustomer && currentPointsDiscount > 0 && useEl.value === '1') {
        totalDisplay.textContent = Math.max(0, base - currentPointsDiscount).toLocaleString('vi-VN') + 'đ';
    } else {
        totalDisplay.textContent = base.toLocaleString('vi-VN') + 'đ';
    }
}

function toggleUsePoints(checked) {
    document.getElementById('usePointsHidden').value = checked ? '1' : '0';
    applyPointsDiscount();
}

function renderCustomerCard() {
    const d = linkedCustomer;
    const resultBox = document.getElementById('customerResult');
    const hasPoints = d.loyalty_points > 0 && d.max_discount > 0;
    resultBox.innerHTML = `
        <div class="customer-found-card">
            <div class="customer-found-name">✅ ${d.name}</div>
            <div class="customer-found-pts">SĐT: ${d.phone} · Điểm hiện tại: <strong>${d.loyalty_points.toLocaleString('vi-VN')}</strong></div>
            ${hasPoints ? `
            <label class="use-points-toggle" style="display:flex;align-items:center;gap:8px;margin-top:8px;cursor:pointer;font-size:13px;color:#15803d;">
                <input type="checkbox" id="usePointsCheck" onchange="toggleUsePoints(this.checked)"
                       style="width:16px;height:16px;accent-color:#15803d;cursor:pointer;">
                <span>Dùng điểm giảm <strong>${d.max_discount.toLocaleString('vi-VN')}đ</strong> (${d.max_discount} điểm)</span>
            </label>` : '<div style="font-size:12px;color:#9ca3af;margin-top:6px;">Không đủ điểm để giảm giá đơn này</div>'}
            <span class="customer-clear-btn" onclick="clearCustomer()">Xóa liên kết</span>
        </div>`;
    resultBox.style.display = 'block';
}

function lookupCustomer() {
    const phone = document.getElementById('customerPhoneInput').value.trim();
    const resultBox = document.getElementById('customerResult');
    if (!phone) return;

    // Tính total hiện tại để gửi lên server tính max_discount đúng.
    const base = getCartBaseTotal();

    resultBox.style.display = 'block';
    resultBox.innerHTML = '<span style="color:#9ca3af;font-size:13px;">Đang tìm...</span>';

    fetch(lookupUrl + '?phone=' + encodeURIComponent(phone) + '&total=' + base)
        .then(r => r.json())
        .then(data => {
            if (data.found) {
                linkedCustomer = data;
                currentPointsDiscount = data.max_discount ?? 0;
                document.getElementById('customerPhoneHidden').value = data.phone;
                document.getElementById('usePointsHidden').value = '0';
                renderCustomerCard();
            } else {
                linkedCustomer = null;
                currentPointsDiscount = 0;
                document.getElementById('customerPhoneHidden').value = '';
                document.getElementById('usePointsHidden').value = '0';
                resultBox.innerHTML = '<div class="customer-not-found">❌ Không tìm thấy tài khoản khách với SĐT này.</div>';
            }
        })
        .catch(() => {
            resultBox.innerHTML = '<div class="customer-not-found">Lỗi kết nối, vui lòng thử lại.</div>';
        });
}

function clearCustomer() {
    linkedCustomer = null;
    currentPointsDiscount = 0;
    document.getElementById('customerPhoneInput').value = '';
    document.getElementById('customerPhoneHidden').value = '';
    document.getElementById('usePointsHidden').value = '0';
    document.getElementById('customerResult').style.display = 'none';
    applyPointsDiscount();
}

// Cho phép nhấn Enter trong ô SĐT để tra cứu.
document.getElementById('customerPhoneInput').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') { e.preventDefault(); lookupCustomer(); }
});

// Khi cart thay đổi, cập nhật lại hiển thị tổng tiền (có trừ điểm nếu đang dùng).
const _origRenderCart = renderCart;
window.addEventListener('cartUpdated', applyPointsDiscount);
</script>
@endsection
