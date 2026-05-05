@extends('staff.layout')

@section('title', 'Danh sách món')
@section('page-title', 'Danh sách món')

@section('content')

<style>
    .products-filter-bar {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: center;
        margin-bottom: 20px;
    }
    .products-filter-bar input,
    .products-filter-bar select {
        border: 1px solid #e0e0e8;
        border-radius: 8px;
        padding: 8px 12px;
        font-size: 13px;
        font-family: inherit;
        background: #fff;
        color: #333;
        outline: none;
    }
    .products-filter-bar input:focus,
    .products-filter-bar select:focus {
        border-color: var(--primary);
    }
    .btn-filter {
        background: var(--primary);
        color: #fff;
        border: none;
        border-radius: 8px;
        padding: 8px 18px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        font-family: inherit;
        transition: background .2s;
    }
    .btn-filter:hover { background: var(--primary-dark); }
    .btn-clear-filter {
        background: transparent;
        color: #888;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 8px 14px;
        font-size: 13px;
        cursor: pointer;
        font-family: inherit;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    .btn-clear-filter:hover { background: #f5f5f5; color: #555; }

    .products-table-wrap {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 2px 12px rgba(0,0,0,.06);
        overflow: hidden;
    }
    .products-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13.5px;
    }
    .products-table thead tr {
        background: #f7f8fc;
        border-bottom: 2px solid #eee;
    }
    .products-table th {
        padding: 13px 16px;
        text-align: left;
        font-weight: 600;
        color: #555;
        white-space: nowrap;
    }
    .products-table tbody tr {
        border-bottom: 1px solid #f0f0f4;
        transition: background .15s;
    }
    .products-table tbody tr:hover { background: #fafbff; }
    .products-table tbody tr.row-unavailable { opacity: .65; }
    .products-table td { padding: 11px 16px; vertical-align: middle; }

    .prod-img {
        width: 46px; height: 46px;
        border-radius: 10px;
        object-fit: cover;
        border: 1px solid #eee;
    }
    .prod-img-placeholder {
        width: 46px; height: 46px;
        border-radius: 10px;
        background: #fde9d6;
        display: flex; align-items: center; justify-content: center;
        font-size: 22px;
    }
    .prod-name { font-weight: 600; font-size: 13.5px; }
    .prod-desc { font-size: 11.5px; color: #999; max-width: 220px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

    .badge-available {
        background: #d4f4e2; color: #1a7a45;
        padding: 3px 10px; border-radius: 20px;
        font-size: 12px; font-weight: 600;
        display: inline-flex; align-items: center; gap: 5px;
    }
    .badge-unavailable {
        background: #f0f0f4; color: #888;
        padding: 3px 10px; border-radius: 20px;
        font-size: 12px; font-weight: 600;
        display: inline-flex; align-items: center; gap: 5px;
    }

    .btn-toggle-on {
        background: #fff3ea;
        color: var(--primary);
        border: 1.5px solid var(--primary);
        border-radius: 8px;
        padding: 6px 14px;
        font-size: 12.5px;
        font-weight: 600;
        cursor: pointer;
        font-family: inherit;
        transition: background .2s, color .2s;
        display: inline-flex; align-items: center; gap: 6px;
    }
    .btn-toggle-on:hover { background: var(--primary); color: #fff; }

    .btn-toggle-off {
        background: #f0f0f4;
        color: #888;
        border: 1.5px solid #ddd;
        border-radius: 8px;
        padding: 6px 14px;
        font-size: 12.5px;
        font-weight: 600;
        cursor: pointer;
        font-family: inherit;
        transition: background .2s, color .2s;
        display: inline-flex; align-items: center; gap: 6px;
    }
    .btn-toggle-off:hover { background: #e0e0e6; color: #555; }

    .summary-bar {
        display: flex;
        gap: 18px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }
    .summary-card {
        background: #fff;
        border-radius: 12px;
        padding: 14px 22px;
        box-shadow: 0 2px 8px rgba(0,0,0,.05);
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 150px;
    }
    .summary-icon { font-size: 22px; }
    .summary-label { font-size: 12px; color: #999; }
    .summary-value { font-size: 20px; font-weight: 700; color: #333; line-height: 1; }

    .confirm-modal {
        position: fixed;
        inset: 0;
        background: rgba(18, 23, 35, 0.45);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 1200;
        padding: 18px;
    }
    .confirm-modal.is-open {
        display: flex;
        animation: modalFadeIn .2s ease;
    }
    .confirm-modal-box {
        width: 100%;
        max-width: 520px;
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 18px 45px rgba(15, 25, 40, 0.25);
        border: 1px solid #f0f0f4;
        overflow: hidden;
        animation: modalSlideUp .24s ease;
    }
    .confirm-modal-body {
        padding: 24px 26px 14px;
    }
    .confirm-modal-title {
        margin: 0 0 9px;
        font-size: 21px;
        font-weight: 700;
        color: #1f2433;
        line-height: 1.25;
    }
    .confirm-modal-message {
        margin: 0;
        color: #4c5468;
        font-size: 14px;
        line-height: 1.6;
        white-space: pre-line;
    }
    .confirm-modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding: 14px 22px 22px;
    }
    .confirm-btn {
        border: 1px solid transparent;
        border-radius: 999px;
        height: 44px;
        min-width: 96px;
        padding: 0 18px;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        transition: transform .12s ease, box-shadow .2s ease, background .2s ease, color .2s ease, border-color .2s ease;
        font-family: inherit;
    }
    .confirm-btn:focus-visible {
        outline: 3px solid rgba(212, 129, 58, 0.3);
        outline-offset: 2px;
    }
    .confirm-btn-ok {
        background: #2fa866;
        color: #fff;
        box-shadow: 0 7px 14px rgba(47, 168, 102, 0.28);
    }
    .confirm-btn-ok:hover {
        background: #278b54;
        transform: translateY(-1px);
    }
    .confirm-btn-cancel {
        background: #eef1e9;
        color: #3d4c3e;
        border-color: #d7dfd2;
    }
    .confirm-btn-cancel:hover {
        background: #e6ebdf;
    }

    @keyframes modalFadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    @keyframes modalSlideUp {
        from {
            opacity: 0;
            transform: translateY(14px) scale(.98);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    @media (max-width: 640px) {
        .confirm-modal-body {
            padding: 20px 18px 10px;
        }
        .confirm-modal-title {
            font-size: 19px;
        }
        .confirm-modal-actions {
            padding: 12px 16px 16px;
            justify-content: stretch;
        }
        .confirm-btn {
            flex: 1;
        }
    }
</style>

{{-- Tóm tắt --}}
@php
    $totalCount     = $products->count();
    $availableCount = $products->where('status', 'available')->count();
    $lockedCount    = $totalCount - $availableCount;
@endphp
<div class="summary-bar">
    <div class="summary-card">
        <div>
            <div class="summary-label">Tổng số món</div>
            <div class="summary-value">{{ $totalCount }}</div>
        </div>
    </div>
    <div class="summary-card">
        <div>
            <div class="summary-label">Đang bán</div>
            <div class="summary-value" style="color:#1a7a45;">{{ $availableCount }}</div>
        </div>
    </div>
    <div class="summary-card">
        <div>
            <div class="summary-label">Đã khóa</div>
            <div class="summary-value" style="color:#888;">{{ $lockedCount }}</div>
        </div>
    </div>
</div>

{{-- Bộ lọc --}}
<form method="GET" action="{{ route('staff.products') }}">
    <div class="products-filter-bar">
        <input type="text" name="search" placeholder="🔍 Tìm tên món..." value="{{ request('search') }}" style="min-width:200px;">

        <select name="category">
            <option value="">Tất cả danh mục</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
            @endforeach
        </select>

        <select name="status">
            <option value="">Tất cả trạng thái</option>
            <option value="available"   {{ request('status') === 'available'   ? 'selected' : '' }}>Đang bán</option>
            <option value="unavailable" {{ request('status') === 'unavailable' ? 'selected' : '' }}>Đã khóa</option>
        </select>

        <button type="submit" class="btn-filter"><i class="fas fa-filter"></i> Lọc</button>

        @if(request()->hasAny(['search','category','status']))
            <a href="{{ route('staff.products') }}" class="btn-clear-filter">
                <i class="fas fa-times"></i> Xóa lọc
            </a>
        @endif
    </div>
</form>

{{-- Bảng sản phẩm --}}
<div class="products-table-wrap">
    <table class="products-table">
        <thead>
            <tr>
                <th>Món</th>
                <th>Danh mục</th>
                <th>Giá</th>
                <th>Trạng thái</th>
                <th style="text-align:center;">Hành động</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $product)
            <tr class="{{ $product->status !== 'available' ? 'row-unavailable' : '' }}">
                <td>
                    <div style="display:flex;align-items:center;gap:12px;">
                        @if($product->image_url)
                            @php
                                $imgSrc = str_starts_with($product->image_url, 'http')
                                    ? $product->image_url
                                    : asset('images/' . $product->image_url);
                            @endphp
                            <img src="{{ $imgSrc }}"
                                 onerror="this.src='https://via.placeholder.com/46x46/fde9d6/d4813a?text=☕'"
                                 alt="{{ $product->name }}" class="prod-img">
                        @else
                            <div class="prod-img-placeholder">☕</div>
                        @endif
                        <div>
                            <div class="prod-name">{{ $product->name }}</div>
                            @if($product->description)
                                <div class="prod-desc">{{ $product->description }}</div>
                            @endif
                        </div>
                    </div>
                </td>
                <td style="color:#666;">{{ $product->category->name ?? '—' }}</td>
                <td style="font-weight:600;color:var(--primary);">{{ number_format($product->price) }}đ</td>
                <td>
                    @if($product->status === 'available')
                        <span class="badge-available"><i class="fas fa-circle" style="font-size:8px;"></i> Đang bán</span>
                    @else
                        <span class="badge-unavailable"><i class="fas fa-lock" style="font-size:10px;"></i> Đang cập nhật</span>
                    @endif
                </td>
                <td style="text-align:center;">
                    <form method="POST" action="{{ route('staff.products.toggle', $product->id) }}" style="display:inline;">
                        @csrf
                        @method('PATCH')
                        @if($product->status === 'available')
                            <button type="submit" class="btn-toggle-off"
                                    data-confirm-title="Khóa món {{ $product->name }}?"
                                    data-confirm-message="Món sẽ không thể đặt hàng cho đến khi mở lại."
                                    data-confirm-ok="Khóa"
                                    data-confirm-cancel="Hủy"
                                    onclick="return openProductConfirm(event, this)">
                                <i class="fas fa-lock"></i> Khóa món
                            </button>
                        @else
                            <button type="submit" class="btn-toggle-on"
                                    data-confirm-title="Mở bán lại món {{ $product->name }}?"
                                    data-confirm-message="Món sẽ hiển thị trở lại và khách có thể đặt hàng."
                                    data-confirm-ok="Mở bán"
                                    data-confirm-cancel="Hủy"
                                    onclick="return openProductConfirm(event, this)">
                                <i class="fas fa-lock-open"></i> Mở bán
                            </button>
                        @endif
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align:center;padding:40px;color:#aaa;">
                    <i class="fas fa-search" style="font-size:28px;margin-bottom:10px;display:block;"></i>
                    Không tìm thấy sản phẩm nào.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="confirm-modal" id="product-confirm-modal" aria-hidden="true">
    <div class="confirm-modal-box" role="dialog" aria-modal="true" aria-labelledby="product-confirm-title" aria-describedby="product-confirm-message">
        <div class="confirm-modal-body">
            <h3 class="confirm-modal-title" id="product-confirm-title">Xác nhận thao tác</h3>
            <p class="confirm-modal-message" id="product-confirm-message">Bạn có chắc chắn muốn tiếp tục?</p>
        </div>
        <div class="confirm-modal-actions">
            <button type="button" class="confirm-btn confirm-btn-ok" id="product-confirm-ok">OK</button>
            <button type="button" class="confirm-btn confirm-btn-cancel" id="product-confirm-cancel">Hủy</button>
        </div>
    </div>
</div>

<script>
    (function () {
        var modal = document.getElementById('product-confirm-modal');
        var titleEl = document.getElementById('product-confirm-title');
        var messageEl = document.getElementById('product-confirm-message');
        var okBtn = document.getElementById('product-confirm-ok');
        var cancelBtn = document.getElementById('product-confirm-cancel');
        var pendingForm = null;
        var lastTrigger = null;

        function closeModal() {
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
            pendingForm = null;
            if (lastTrigger) {
                lastTrigger.focus();
            }
        }

        okBtn.addEventListener('click', function () {
            if (!pendingForm) {
                closeModal();
                return;
            }

            var form = pendingForm;
            closeModal();
            form.submit();
        });

        cancelBtn.addEventListener('click', closeModal);

        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                closeModal();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && modal.classList.contains('is-open')) {
                closeModal();
            }
        });

        window.openProductConfirm = function (event, trigger) {
            event.preventDefault();
            pendingForm = trigger.closest('form');
            if (!pendingForm) {
                return false;
            }

            lastTrigger = trigger;
            titleEl.textContent = trigger.getAttribute('data-confirm-title') || 'Xác nhận thao tác';
            messageEl.textContent = trigger.getAttribute('data-confirm-message') || 'Bạn có chắc chắn muốn tiếp tục?';
            okBtn.textContent = trigger.getAttribute('data-confirm-ok') || 'OK';
            cancelBtn.textContent = trigger.getAttribute('data-confirm-cancel') || 'Hủy';

            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
            okBtn.focus();

            return false;
        };
    })();
</script>

@endsection
