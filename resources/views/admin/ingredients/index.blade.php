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
{{-- Thẻ tổng tiền kho --}}
<div style="display:flex;gap:14px;margin-bottom:20px;flex-wrap:wrap;">
    <div class="card" style="flex:1;min-width:220px;padding:20px 24px;display:flex;align-items:center;gap:16px;">
        <div style="width:44px;height:44px;border-radius:12px;background:#dcfce7;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="fas fa-coins" style="color:#16a34a;font-size:20px;"></i>
        </div>
        <div>
            <div style="font-size:12px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.5px;">Tổng giá trị nhập kho</div>
            <div style="font-size:22px;font-weight:700;color:#15803d;margin-top:2px;">{{ number_format($totalInventoryValue, 0, ',', '.') }} ₫</div>
        </div>
    </div>
    <div class="card" style="flex:1;min-width:220px;padding:20px 24px;display:flex;align-items:center;gap:16px;">
        <div style="width:44px;height:44px;border-radius:12px;background:#dbeafe;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="fas fa-boxes-stacked" style="color:#1d4ed8;font-size:20px;"></i>
        </div>
        <div>
            <div style="font-size:12px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.5px;">Số loại nguyên liệu</div>
            <div style="font-size:22px;font-weight:700;color:#1d4ed8;margin-top:2px;">{{ $ingredients->count() }} loại</div>
        </div>
    </div>
    @php
        $lowStockCount = $ingredients->filter(fn($i) => $i->minimum_quantity && $i->stock_quantity <= $i->minimum_quantity)->count();
    @endphp
    @if($lowStockCount > 0)
    <div class="card" style="flex:1;min-width:220px;padding:20px 24px;display:flex;align-items:center;gap:16px;border:1.5px solid #fecdd3;">
        <div style="width:44px;height:44px;border-radius:12px;background:#fee2e2;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="fas fa-triangle-exclamation" style="color:#dc2626;font-size:20px;"></i>
        </div>
        <div>
            <div style="font-size:12px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.5px;">Sắp hết hàng</div>
            <div style="font-size:22px;font-weight:700;color:#dc2626;margin-top:2px;">{{ $lowStockCount }} loại</div>
        </div>
    </div>
    @endif
</div>

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
                    <input type="text" name="unit" class="inventory-field" placeholder="Đơn vị" value="{{ old('unit') }}" required>
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
                    <input type="text" name="lot_number" class="inventory-field{{ $errors->has('lot_number') ? ' border-red-500' : '' }}"
                           style="{{ $errors->has('lot_number') ? 'border-color:#dc2626;background:#fff5f5;' : '' }}"
                           placeholder="Số lô" value="{{ old('lot_number') }}" required>
                    @error('lot_number')
                        <div style="margin-top:4px;font-size:11px;color:#dc2626;font-weight:600;">
                            <i class="fas fa-circle-exclamation" style="margin-right:3px;"></i>{{ $message }}
                        </div>
                    @enderror
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

{{-- Wrapper tiêu đề + bộ lọc tháng --}}
<div class="card" style="margin-bottom:0;">
    <div class="card-header">
        <div>
            <div class="card-header-title"><i class="fas fa-file-import" style="color:#0369a1;"></i> Danh sách nhập kho</div>
            <p class="schedule-table-note">Mỗi ngày nhập là một bảng riêng. Lọc theo tháng để thu hẹp kết quả.</p>
        </div>
        <form method="GET" action="{{ route('admin.ingredients') }}" style="display:flex;align-items:center;gap:8px;">
            <input type="month" name="filter_month" class="inventory-field" style="width:160px;height:36px;"
                value="{{ $filterMonth ?? '' }}" max="{{ now()->format('Y-m') }}">
            <button type="submit" class="btn-soft-save" style="height:36px;white-space:nowrap;"><i class="fas fa-filter"></i> Lọc</button>
            @if($filterMonth)
                <a href="{{ route('admin.ingredients') }}" class="btn-soft-danger" style="height:36px;display:inline-flex;align-items:center;gap:5px;text-decoration:none;"><i class="fas fa-xmark"></i> Xóa lọc</a>
            @endif
        </form>
    </div>
</div>

@if($ingredients->isNotEmpty())
    @php
        // Nhóm theo received_date, mới nhất lên trước
        $grouped = $ingredients->groupBy(fn($i) => optional($i->received_date)?->format('Y-m-d') ?? 'unknown')
                               ->sortKeysDesc();
    @endphp

    @foreach($grouped as $dateKey => $group)
        @php
            $dateLabel   = $dateKey !== 'unknown'
                ? \Carbon\Carbon::parse($dateKey)->format('d/m/Y')
                : 'Chưa rõ ngày';
            $dayOfWeek   = $dateKey !== 'unknown'
                ? ['Chủ nhật','Thứ 2','Thứ 3','Thứ 4','Thứ 5','Thứ 6','Thứ 7'][\Carbon\Carbon::parse($dateKey)->dayOfWeek]
                : '';
            $groupTotal  = $group->sum('purchase_amount');
            $groupCount  = $group->count();
        @endphp
        <div class="card" style="margin-top:14px;">
            <div class="card-header" style="background:#f0f9ff;border-bottom:1px solid #bae6fd;">
                <div style="display:flex;align-items:center;gap:10px;">
                    <div style="width:40px;height:40px;border-radius:10px;background:#0ea5e9;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="fas fa-calendar-day" style="color:#fff;font-size:16px;"></i>
                    </div>
                    <div>
                        <div style="font-size:15px;font-weight:700;color:#0c4a6e;">
                            {{ $dayOfWeek }}{{ $dayOfWeek ? ', ' : '' }}{{ $dateLabel }}
                        </div>
                        <div style="font-size:12px;color:#0369a1;margin-top:1px;">
                            {{ $groupCount }} nguyên liệu &nbsp;·&nbsp;
                            Tổng nhập: <strong>{{ number_format($groupTotal, 0, ',', '.') }} ₫</strong>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body" style="padding:0;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Nguyên liệu</th>
                            <th>Thương hiệu</th>
                            <th>Số lượng nhập</th>
                            <th>Đơn vị</th>
                            <th>Đơn giá</th>
                            <th>Giá trị nhập</th>
                            <th>Ngày sản xuất</th>
                            <th>Hạn sử dụng</th>
                            <th>Số lô</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($group->sortBy('name') as $ingredient)
                            <tr>
                                <td>
                                    <div class="staff-cell-name">{{ $ingredient->name }}</div>
                                    @if(!empty($ingredient->note))
                                        <div class="staff-cell-sub">{{ $ingredient->note }}</div>
                                    @endif
                                </td>
                                <td>{{ $ingredient->brand ?: '—' }}</td>
                                <td>{{ rtrim(rtrim(number_format($ingredient->stock_quantity, 2, ',', '.'), '0'), ',') }}</td>
                                <td>{{ $ingredient->unit ?? '' }}</td>
                                <td>{{ number_format($ingredient->unit_price, 0, ',', '.') }} ₫</td>
                                <td style="font-weight:600;color:#15803d;">{{ number_format($ingredient->purchase_amount, 0, ',', '.') }} ₫</td>
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
                    <tfoot>
                        <tr style="background:#f0fdf4;font-weight:700;">
                            <td colspan="5" style="text-align:right;color:#64748b;padding:10px 14px;">Tổng ngày {{ $dateLabel }}:</td>
                            <td style="color:#15803d;">{{ number_format($groupTotal, 0, ',', '.') }} ₫</td>
                            <td colspan="4"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @endforeach

    @if($filterMonth)
        <div style="padding:10px 4px;font-size:12px;color:#64748b;margin-top:6px;">
            Hiển thị {{ $ingredients->count() }} mục nhập kho trong tháng {{ \Carbon\Carbon::parse($filterMonth)->format('m/Y') }}
            — {{ $grouped->count() }} ngày nhập khác nhau.
        </div>
    @endif
@else
    <div class="card" style="margin-top:14px;">
        <div class="card-body">
            <div class="schedule-empty">
                @if($filterMonth)
                    Không có dữ liệu nhập kho trong tháng {{ \Carbon\Carbon::parse($filterMonth)->format('m/Y') }}.
                @else
                    Chưa có nguyên liệu nào trong kho.
                @endif
            </div>
        </div>
    </div>
@endif

{{-- Bảng xuất kho nguyên liệu --}}
<div class="card" style="margin-top:20px;">
    <div class="card-header">
        <div>
            <div class="card-header-title"><i class="fas fa-arrow-up-from-bracket" style="color:#d97706;"></i> Xuất kho nguyên liệu</div>
            <p class="schedule-table-note">Tìm ngày liệu, nhập số lượng cần lấy ra và xác nhận. Hệ thống tự động trừ tồn kho.</p>
        </div>
    </div>
    <div class="card-body">
        {{-- Flash thành công xuất kho --}}
        @if(session('withdraw_success'))
            <div class="alert-success-admin" style="margin-bottom:14px;padding:10px 14px;background:#dcfce7;border:1px solid #86efac;border-radius:10px;color:#166534;font-size:13px;font-weight:600;">
                <i class="fas fa-circle-check" style="margin-right:6px;"></i>{{ session('withdraw_success') }}
            </div>
        @endif

        {{-- Bước 1: tìm kiếm --}}
        <div style="display:flex;gap:10px;align-items:center;margin-bottom:16px;flex-wrap:wrap;">
            <div style="position:relative;flex:1;min-width:220px;">
                <i class="fas fa-search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:13px;"></i>
                <input id="withdrawSearch" type="text" class="inventory-field" placeholder="Nhập tên nguyên liệu..." style="padding-left:34px;" autocomplete="off">
            </div>
            <div id="withdrawSearchHint" style="font-size:12px;color:#94a3b8;">Gõ tên để tìm</div>
        </div>

        {{-- Bảng kết quả tìm kiếm --}}
        <div id="withdrawResultWrap" style="display:none;">
            <table class="admin-table" id="withdrawResultTable">
                <thead>
                    <tr>
                        <th>Tên nguyên liệu</th>
                        <th>Tồn kho hiện tại</th>
                        <th>Đơn vị</th>
                        <th>Số lượng lấy ra</th>
                        <th>Tồn sau khi lấy</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody id="withdrawResultBody">
                    {{-- render bằng JS --}}
                </tbody>
            </table>
        </div>

        <div id="withdrawNoResult" style="display:none;padding:20px;text-align:center;color:#94a3b8;font-size:13px;">
            Không tìm thấy ngày liệu phù hợp.
        </div>
    </div>
</div>

{{-- Modal xác nhận xuất kho --}}
<div id="withdrawConfirmOverlay" class="confirm-overlay" aria-hidden="true">
    <div class="confirm-modal" role="dialog" aria-modal="true">
        <h3 class="confirm-title"><i class="fas fa-arrow-up-from-bracket" style="color:#d97706;margin-right:8px;"></i>Xác nhận xuất kho</h3>
        <p class="confirm-text" id="withdrawConfirmText">Bạn muốn lấy ra <strong id="wConfirmQty"></strong> <span id="wConfirmUnit"></span> — <strong id="wConfirmName"></strong>?<br>Tồn sau khi lấy: <strong id="wConfirmRemain"></strong> <span id="wConfirmUnit2"></span>.</p>
        <div class="confirm-actions">
            <button type="button" class="btn-confirm-cancel" id="cancelWithdrawBtn">Hủy</button>
            <button type="button" class="btn-confirm-delete" id="confirmWithdrawBtn" style="background:#d97706;border-color:#b45309;">Xuất kho</button>
        </div>
    </div>
</div>

{{-- Form ẩn để submit xuất kho --}}
<form id="withdrawSubmitForm" method="POST" action="" style="display:none;">
    @csrf
    <input type="hidden" name="withdraw_quantity" id="withdrawSubmitQty">
</form>

{{-- Lịch sử xuất kho --}}
<div class="card" style="margin-top:20px;">
    <div class="card-header">
        <div>
            <div class="card-header-title"><i class="fas fa-clock-rotate-left" style="color:#7c3aed;"></i> Lịch sử xuất kho</div>
            <p class="schedule-table-note">100 lần xuất kho gần nhất.</p>
        </div>
        <div style="display:flex;align-items:center;gap:8px;">
            <input id="logSearch" type="text" class="inventory-field" placeholder="Lọc theo tên..." style="width:200px;height:36px;" autocomplete="off">
        </div>
    </div>
    <div class="card-body" style="padding:0;">
        @if($withdrawalLogs->isNotEmpty())
            <table class="admin-table" id="withdrawLogTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nguyên liệu</th>
                        <th>SL lấy ra</th>
                        <th>Đơn vị</th>
                        <th>Tồn trước</th>
                        <th>Tồn sau</th>
                        <th>Người xuất</th>
                        <th>Thời gian</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($withdrawalLogs as $log)
                        @php $low = $log->stock_after <= 5 && $log->stock_after > 0; @endphp
                        <tr class="log-row" data-name="{{ strtolower($log->ingredient_name) }}">
                            <td style="color:#94a3b8;font-size:12px;">{{ $loop->iteration }}</td>
                            <td><div class="staff-cell-name">{{ $log->ingredient_name }}</div></td>
                            <td style="font-weight:700;color:#d97706;">{{ rtrim(rtrim(number_format($log->quantity, 2, ',', '.'), '0'), ',') }}</td>
                            <td>{{ $log->unit }}</td>
                            <td>{{ rtrim(rtrim(number_format($log->stock_before, 2, ',', '.'), '0'), ',') }}</td>
                            <td>
                                <span style="{{ $low ? 'color:#dc2626;font-weight:700;' : 'color:#16a34a;font-weight:600;' }}">
                                    {{ rtrim(rtrim(number_format($log->stock_after, 2, ',', '.'), '0'), ',') }}
                                </span>
                                @if($low)
                                    <span style="background:#fee2e2;color:#991b1b;font-size:10px;font-weight:700;padding:2px 7px;border-radius:999px;margin-left:4px;">Sắp hết</span>
                                @endif
                            </td>
                            <td>{{ $log->creator?->name ?? '—' }}</td>
                            <td style="color:#64748b;font-size:12px;">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="schedule-empty" id="logEmpty">Chưa có lịch sử xuất kho nào.</div>
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
@php
    $ingredientsJson = $ingredients->map(fn($i) => [
        'id'               => $i->id,
        'name'             => $i->name,
        'stock_quantity'   => (float) $i->stock_quantity,
        'unit'             => $i->unit,
        'minimum_quantity' => (float) ($i->minimum_quantity ?? 0),
        'withdraw_url'     => route('admin.ingredients.withdraw', $i->id),
    ])->values();
@endphp
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

// ── Xuất kho: search + preview + confirm ─────────────────────────────────────
(() => {
    // Dữ liệu nguyên liệu từ server (JSON)
    const ingredients = {!! json_encode($ingredientsJson) !!};

    const searchInput   = document.getElementById('withdrawSearch');
    const searchHint    = document.getElementById('withdrawSearchHint');
    const resultWrap    = document.getElementById('withdrawResultWrap');
    const resultBody    = document.getElementById('withdrawResultBody');
    const noResult      = document.getElementById('withdrawNoResult');
    const overlay       = document.getElementById('withdrawConfirmOverlay');
    const cancelBtn     = document.getElementById('cancelWithdrawBtn');
    const confirmBtn    = document.getElementById('confirmWithdrawBtn');
    const submitForm    = document.getElementById('withdrawSubmitForm');
    const submitQty     = document.getElementById('withdrawSubmitQty');

    if (!searchInput) return;

    const fmt = (n) => new Intl.NumberFormat('vi-VN').format(n);

    // ── render bảng kết quả
    function renderRows(list) {
        if (!list.length) {
            resultWrap.style.display = 'none';
            noResult.style.display   = 'block';
            return;
        }
        noResult.style.display   = 'none';
        resultWrap.style.display = 'block';

        resultBody.innerHTML = list.map(item => {
            const low      = item.minimum_quantity > 0 && item.stock_quantity <= item.minimum_quantity;
            const stockCls = low ? 'style="color:#dc2626;font-weight:700;"' : '';
            return `
            <tr data-id="${item.id}">
                <td><div class="staff-cell-name">${item.name}</div></td>
                <td><span ${stockCls}>${fmt(item.stock_quantity)}</span>${low ? ' <span style="background:#fee2e2;color:#991b1b;font-size:10px;font-weight:700;padding:2px 7px;border-radius:999px;margin-left:4px;">Sắp hết</span>' : ''}</td>
                <td>${item.unit}</td>
                <td>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <input type="number" step="0.01" min="0.01" max="${item.stock_quantity}"
                               class="inventory-field withdraw-qty-input"
                               style="width:110px;"
                               placeholder="Số lượng"
                               data-max="${item.stock_quantity}"
                               data-id="${item.id}">
                        <span class="withdraw-qty-err" data-id="${item.id}" style="color:#dc2626;font-size:11px;display:none;"></span>
                    </div>
                </td>
                <td class="withdraw-remain" data-id="${item.id}" style="font-weight:600;">—</td>
                <td>
                    <button type="button" class="btn-soft-save withdraw-confirm-btn" data-id="${item.id}" data-name="${item.name}" data-unit="${item.unit}" data-stock="${item.stock_quantity}">
                        <i class="fas fa-arrow-up-from-bracket"></i> Xuất kho
                    </button>
                </td>
            </tr>`;
        }).join('');

        // live preview tồn sau khi lấy
        resultBody.querySelectorAll('.withdraw-qty-input').forEach(inp => {
            inp.addEventListener('input', () => {
                const id      = inp.dataset.id;
                const max     = parseFloat(inp.dataset.max) || 0;
                const val     = parseFloat(inp.value) || 0;
                const remain  = max - val;
                const errEl   = resultBody.querySelector(`.withdraw-qty-err[data-id="${id}"]`);
                const remainEl= resultBody.querySelector(`.withdraw-remain[data-id="${id}"]`);
                const item    = ingredients.find(i => String(i.id) === id);

                if (val > max) {
                    errEl.textContent = `Tối đa ${fmt(max)} ${item ? item.unit : ''}`;
                    errEl.style.display = 'inline';
                    inp.style.borderColor = '#dc2626';
                } else {
                    errEl.style.display = 'none';
                    inp.style.borderColor = '';
                }

                if (val > 0 && val <= max) {
                    const low = item && item.minimum_quantity > 0 && remain <= item.minimum_quantity;
                    remainEl.innerHTML = `<span style="${low ? 'color:#dc2626;' : 'color:#16a34a;'}">${fmt(remain)}</span>${low ? ' <span style="background:#fee2e2;color:#991b1b;font-size:10px;font-weight:700;padding:2px 7px;border-radius:999px;margin-left:4px;">Sắp hết</span>' : ''}`;
                } else {
                    remainEl.textContent = '—';
                }
            });
        });

        // nút xuất kho từng dòng
        resultBody.querySelectorAll('.withdraw-confirm-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const id    = btn.dataset.id;
                const qtyEl = resultBody.querySelector(`.withdraw-qty-input[data-id="${id}"]`);
                const val   = parseFloat(qtyEl?.value) || 0;
                const max   = parseFloat(btn.dataset.stock) || 0;

                if (!val || val <= 0) { qtyEl.focus(); return; }
                if (val > max) { return; }

                const remain = max - val;
                const item   = ingredients.find(i => String(i.id) === id);

                document.getElementById('wConfirmQty').textContent    = fmt(val);
                document.getElementById('wConfirmUnit').textContent   = btn.dataset.unit;
                document.getElementById('wConfirmUnit2').textContent  = btn.dataset.unit;
                document.getElementById('wConfirmName').textContent   = btn.dataset.name;
                document.getElementById('wConfirmRemain').textContent = fmt(remain);

                submitForm.action  = item ? item.withdraw_url : '';
                submitQty.value    = val;

                overlay.classList.add('open');
                overlay.setAttribute('aria-hidden', 'false');
            });
        });
    }

    // ── tìm kiếm
    searchInput.addEventListener('input', () => {
        const q = searchInput.value.trim().toLowerCase();
        if (!q) {
            resultWrap.style.display = 'none';
            noResult.style.display   = 'none';
            searchHint.textContent   = 'Gõ tên để tìm';
            return;
        }
        searchHint.textContent = '';
        const found = ingredients.filter(i => i.name.toLowerCase().includes(q));
        renderRows(found);
    });

    // ── đóng modal
    const closeOverlay = () => {
        overlay.classList.remove('open');
        overlay.setAttribute('aria-hidden', 'true');
    };
    cancelBtn.addEventListener('click', closeOverlay);
    overlay.addEventListener('click', e => { if (e.target === overlay) closeOverlay(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeOverlay(); });

    // ── xác nhận → submit
    confirmBtn.addEventListener('click', () => {
        closeOverlay();
        submitForm.submit();
    });
})();

// ── Lọc bảng lịch sử xuất kho ─────────────────────────────────────────────
(() => {
    const logSearch = document.getElementById('logSearch');
    if (!logSearch) return;
    logSearch.addEventListener('input', () => {
        const q = logSearch.value.trim().toLowerCase();
        document.querySelectorAll('#withdrawLogTable .log-row').forEach(row => {
            row.style.display = !q || row.dataset.name.includes(q) ? '' : 'none';
        });
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
