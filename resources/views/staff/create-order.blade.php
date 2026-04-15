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
                             onclick="addToCart({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $product->price }})">
                            <span class="in-cart-badge" id="badge-{{ $product->id }}">1</span>
                           <img src="{{ 
                                $product->image_url 
                                    ? (Str::startsWith($product->image_url, ['http://', 'https://']) 
                                        ? $product->image_url 
                                        : asset('images/' . $product->image_url)) 
                                    : 'https://via.placeholder.com/60x60?text=☕' 
                            }}" class="product-img">
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

            <select class="payment-select" name="payment_method">
                <option value="cash">💵 Tiền mặt</option>
                <option value="momo">📱 MoMo</option>
                <option value="bank_transfer">🏦 Chuyển khoản</option>
                <option value="vnpay">💳 VNPay</option>
            </select>

            <textarea class="note-input" name="note" rows="2" placeholder="Ghi chú đơn hàng (tuỳ chọn)..."></textarea>

            {{-- Hidden inputs sẽ được JS tạo --}}
            <div id="hiddenInputs"></div>

            <button type="submit" class="btn-submit-order" id="submitBtn" disabled>
                <i class="fas fa-check-circle"></i> Tạo đơn & thanh toán
            </button>
        </div>
    </div>
</div>
</form>
@endsection

@section('scripts')
<script>
// ── Cart state ──
const cart = {}; // { productId: { name, price, quantity } }

function addToCart(id, name, price) {
    if (cart[id]) {
        cart[id].quantity++;
    } else {
        cart[id] = { name, price, quantity: 1 };
    }
    renderCart();
}

function changeQty(id, delta) {
    if (!cart[id]) return;
    cart[id].quantity += delta;
    if (cart[id].quantity <= 0) {
        delete cart[id];
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
        if (cart[id]) {
            card.classList.add('selected');
            badge.textContent = cart[id].quantity;
            badge.style.display = 'flex';
        } else {
            card.classList.remove('selected');
            badge.style.display = 'none';
        }
    });

    cartCount.textContent = totalItems + ' món';
    totalDisplay.textContent = total.toLocaleString('vi-VN') + 'đ';
    submitBtn.disabled = ids.length === 0;

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

        // Cart row
        const row = document.createElement('div');
        row.className = 'cart-item';
        row.innerHTML = `
            <div class="cart-item-name">${item.name}</div>
            <div class="qty-control">
                <button type="button" class="qty-btn" onclick="changeQty(${id}, -1)">−</button>
                <span class="qty-value">${item.quantity}</span>
                <button type="button" class="qty-btn" onclick="changeQty(${id}, 1)">+</button>
            </div>
            <div class="cart-item-price">${(item.price * item.quantity).toLocaleString('vi-VN')}đ</div>
        `;
        cartItems.insertBefore(row, emptyCart);

        // Hidden inputs for form submission
        hiddenInputs.innerHTML += `
            <input type="hidden" name="items[${idx}][product_id]" value="${id}">
            <input type="hidden" name="items[${idx}][quantity]" value="${item.quantity}">
        `;
    });
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
    const ok = confirm(`Xác nhận tạo đơn tại quán?\nTổng tiền: ${total.toLocaleString('vi-VN')}đ`);
    if (!ok) e.preventDefault();
});
</script>
@endsection
