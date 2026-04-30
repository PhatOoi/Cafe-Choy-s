@extends('admin.layout')

@section('title', 'Kho nguyên liệu')
@section('page-title', 'Kho nguyên liệu')
@section('breadcrumb', 'Admin / Kho nguyên liệu')

@section('styles')
<style>
    .inventory-field {
        width: 100%;
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 10px 12px;
        font-size: 13px;
        background: #fff;
        font-family: 'Poppins', sans-serif;
    }

    .inventory-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
    }

    .inventory-status.low {
        background: #fee2e2;
        color: #991b1b;
    }

    .inventory-status.ok {
        background: #dcfce7;
        color: #166534;
    }

    .btn-soft-save {
        border: 1px solid #93c5fd;
        background: linear-gradient(180deg, #eff6ff 0%, #dbeafe 100%);
        color: #1e3a8a;
        padding: 8px 12px;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        font-family: 'Poppins', sans-serif;
    }

    .btn-soft-danger {
        border: 1px solid #fecdd3;
        background: #fff1f2;
        color: #be123c;
        padding: 8px 12px;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        font-family: 'Poppins', sans-serif;
    }

    .confirm-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.45);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        padding: 16px;
    }

    .confirm-overlay.open {
        display: flex;
    }

    .confirm-modal {
        width: min(460px, 100%);
        background: #fff;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 20px 45px rgba(15, 23, 42, 0.25);
        padding: 18px;
    }

    .confirm-title {
        font-size: 18px;
        font-weight: 700;
        color: #0f172a;
        margin: 0 0 8px;
    }

    .confirm-text {
        font-size: 14px;
        color: #475569;
        margin: 0 0 14px;
    }

    .confirm-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    .btn-confirm-cancel {
        border: 1px solid #cbd5e1;
        background: #fff;
        color: #334155;
        padding: 9px 14px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        font-family: 'Poppins', sans-serif;
    }

    .btn-confirm-delete {
        border: 1px solid #fecdd3;
        background: #e11d48;
        color: #fff;
        padding: 9px 14px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        font-family: 'Poppins', sans-serif;
    }
</style>
@endsection

@section('content')
<div class="card" style="margin-bottom:20px;">
    <div class="card-header">
        <div>
            <div class="card-header-title"><i class="fas fa-boxes-stacked" style="color:#16a34a;"></i> Thêm nguyên liệu</div>
            <p class="schedule-table-note">Quản lý tồn kho nguyên liệu để theo dõi nhập/xuất nội bộ.</p>
        </div>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.ingredients.store') }}" style="display:flex;flex-direction:column;gap:10px;">
            @csrf
            {{-- Hàng 1: Tên, Thương hiệu, Đơn vị, Số lượng, Đơn giá, Số lô, Tổng tiền --}}
            <div style="display:grid;grid-template-columns:2fr 1.2fr 1.2fr 1fr 1fr 1.2fr 1.2fr;gap:10px;">
                <div>
                    <div style="font-size:12px;font-weight:600;color:#64748b;margin-bottom:6px;">Tên nguyên liệu</div>
                    <input type="text" name="name" class="inventory-field" placeholder="Tên nguyên liệu" value="{{ old('name') }}" required>
                </div>
                <div>
                    <div style="font-size:12px;font-weight:600;color:#64748b;margin-bottom:6px;">Thương hiệu</div>
                    <input type="text" name="brand" class="inventory-field" placeholder="Thương hiệu" value="{{ old('brand') }}" required>
                </div>
                <div>
                    <div style="font-size:12px;font-weight:600;color:#64748b;margin-bottom:6px;">Đơn vị</div>
                    <input type="text" name="unit" class="inventory-field" placeholder="Đơn vị" value="{{ old('unit', 'số lượng') }}" required>
                </div>
                <div>
                    <div style="font-size:12px;font-weight:600;color:#64748b;margin-bottom:6px;">Số lượng</div>
                    <input type="number" step="0.01" min="0" name="stock_quantity" class="inventory-field inventory-quantity" placeholder="Số lượng" value="{{ old('stock_quantity', 0) }}" required>
                </div>
                <div>
                    <div style="font-size:12px;font-weight:600;color:#64748b;margin-bottom:6px;">Đơn giá</div>
                    <input type="number" step="0.01" min="0" name="unit_price" class="inventory-field inventory-price" placeholder="Đơn giá" value="{{ old('unit_price', 0) }}" required>
                </div>
                <div>
                    <div style="font-size:12px;font-weight:600;color:#64748b;margin-bottom:6px;">Số lô</div>
                    <input type="text" name="lot_number" class="inventory-field" placeholder="Số lô" value="{{ old('lot_number') }}" required>
                </div>
                <div>
                    <div style="font-size:12px;font-weight:600;color:#64748b;margin-bottom:6px;">Tổng tiền</div>
                    <div class="inventory-field" id="totalAmountDisplay" style="padding:8px;background:#f1f5f9;border-radius:6px;font-weight:600;color:#334155;display:flex;align-items:center;">0 ₫</div>
                </div>
            </div>
            {{-- Hàng 2: Ngày nhập hàng, Ngày sản xuất, Hạn sử dụng, Ghi chú, Thêm --}}
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr 3fr auto;gap:10px;align-items:end;">
                <div>
                    <div style="font-size:12px;font-weight:600;color:#64748b;margin-bottom:6px;">Ngày nhập hàng</div>
                    <input type="date" name="received_date" class="inventory-field" value="{{ old('received_date') }}" required>
                </div>
                <div>
                    <div style="font-size:12px;font-weight:600;color:#64748b;margin-bottom:6px;">Ngày sản xuất</div>
                    <input type="date" name="manufacture_date" class="inventory-field" value="{{ old('manufacture_date') }}" required>
                </div>
                <div>
                    <div style="font-size:12px;font-weight:600;color:#64748b;margin-bottom:6px;">Hạn sử dụng</div>
                    <input type="date" name="expiry_date" class="inventory-field" value="{{ old('expiry_date') }}" required>
                </div>
                <textarea name="note" class="inventory-field" placeholder="Ghi chú" style="min-height:42px;resize:vertical;">{{ old('note') }}</textarea>
                <button type="submit" class="btn-primary-admin" style="align-self:stretch;"><i class="fas fa-plus"></i> Thêm</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div>
            <div class="card-header-title"><i class="fas fa-warehouse" style="color:#0369a1;"></i> Danh sách kho nguyên liệu</div>
        </div>
    </div>
    <div class="card-body" style="padding:0;">
        @if($ingredients->isNotEmpty())
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Nguyên liệu</th>
                        <th>Thương hiệu</th>
                        <th>Số lượng</th>
                        <th>Đơn vị</th>
                        <th>Đơn giá</th>
                        <th>Tổng tiền</th>
                        <th>Ngày nhập hàng</th>
                        <th>Ngày sản xuất</th>
                        <th>Hạn sử dụng</th>
                        <th>Số lô</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ingredients as $ingredient)
                        <tr>
                            <td>
                                <div class="staff-cell-name">{{ $ingredient->name }}</div>
                                <div class="staff-cell-sub">{{ $ingredient->note ?: 'Không có ghi chú' }}</div>
                            </td>
                            <td>{{ $ingredient->brand ?: '—' }}</td>
                            <td>{{ rtrim(rtrim(number_format($ingredient->stock_quantity, 2, ',', '.'), '0'), ',') }}</td>
                            <td>{{ $ingredient->unit ?: 'số lượng' }}</td>
                            <td>{{ number_format($ingredient->unit_price, 0, ',', '.') }} ₫</td>
                            <td>{{ number_format($ingredient->total_amount, 0, ',', '.') }} ₫</td>
                            <td>{{ optional($ingredient->received_date)?->format('d/m/Y') ?: '—' }}</td>
                            <td>{{ optional($ingredient->manufacture_date)?->format('d/m/Y') ?: '—' }}</td>
                            <td>{{ optional($ingredient->expiry_date)?->format('d/m/Y') ?: '—' }}</td>
                            <td>{{ $ingredient->lot_number ?: '—' }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.ingredients.destroy', $ingredient->id) }}" style="margin:0;" class="js-ingredient-delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-soft-danger">Xóa</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="schedule-empty">Chưa có nguyên liệu nào trong kho.</div>
        @endif
    </div>
</div>

<div id="ingredientDeleteConfirm" class="confirm-overlay" aria-hidden="true">
    <div class="confirm-modal" role="dialog" aria-modal="true" aria-labelledby="confirmDeleteTitle">
        <h3 id="confirmDeleteTitle" class="confirm-title">Xác nhận xóa nguyên liệu</h3>
        <p class="confirm-text">Bạn chắc chắn muốn xóa nguyên liệu này? Hành động này không thể hoàn tác.</p>
        <div class="confirm-actions">
            <button type="button" class="btn-confirm-cancel" id="cancelIngredientDeleteBtn">Hủy</button>
            <button type="button" class="btn-confirm-delete" id="confirmIngredientDeleteBtn">Xóa ngay</button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
(() => {
    const overlay = document.getElementById('ingredientDeleteConfirm');
    const confirmBtn = document.getElementById('confirmIngredientDeleteBtn');
    const cancelBtn = document.getElementById('cancelIngredientDeleteBtn');
    const forms = document.querySelectorAll('.js-ingredient-delete-form');
    let pendingForm = null;

    if (!overlay || !confirmBtn || !cancelBtn || !forms.length) {
        return;
    }

    const closeModal = () => {
        overlay.classList.remove('open');
        overlay.setAttribute('aria-hidden', 'true');
        pendingForm = null;
    };

    forms.forEach((form) => {
        form.addEventListener('submit', (event) => {
            event.preventDefault();
            pendingForm = form;
            overlay.classList.add('open');
            overlay.setAttribute('aria-hidden', 'false');
        });
    });

    cancelBtn.addEventListener('click', closeModal);

    overlay.addEventListener('click', (event) => {
        if (event.target === overlay) {
            closeModal();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && overlay.classList.contains('open')) {
            closeModal();
        }
    });

    confirmBtn.addEventListener('click', () => {
        if (!pendingForm) {
            return;
        }

        const formToSubmit = pendingForm;
        closeModal();
        formToSubmit.submit();
    });
})();

// Auto-calculate total amount
(() => {
    const quantityInput = document.querySelector('.inventory-quantity');
    const priceInput = document.querySelector('.inventory-price');
    const totalDisplay = document.getElementById('totalAmountDisplay');

    if (!quantityInput || !priceInput || !totalDisplay) {
        return;
    }

    const updateTotal = () => {
        const quantity = parseFloat(quantityInput.value) || 0;
        const price = parseFloat(priceInput.value) || 0;
        const total = quantity * price;
        
        totalDisplay.textContent = new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(total);
    };

    quantityInput.addEventListener('input', updateTotal);
    priceInput.addEventListener('input', updateTotal);

    // Initial calculation
    updateTotal();
})();
</script>
@endsection
