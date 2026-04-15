@extends('admin.layout')

@section('title', 'Chi tiết đơn #' . $order->id)
@section('page-title', 'Chi tiết đơn #' . $order->id)
@section('breadcrumb', 'Admin / Đơn hàng / #' . $order->id)

@section('content')

<div class="page-header">
    <div>
        <div class="page-header-title">Đơn hàng #{{ $order->id }}</div>
        <div class="page-header-sub">{{ $order->created_at->format('H:i — d/m/Y') }}</div>
    </div>
    <div style="display:flex;gap:10px;">
        <a href="{{ route('admin.orders') }}" class="btn-outline-admin">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 320px;gap:20px;align-items:start;">

    {{-- Left --}}
    <div style="display:flex;flex-direction:column;gap:18px;">

        {{-- Items --}}
        <div class="card">
            <div class="card-header">
                <div class="card-header-title">
                    <i class="fas fa-shopping-bag" style="color:var(--primary);"></i>
                    Sản phẩm đặt ({{ $order->items->count() }} món)
                </div>
            </div>
            <div style="overflow-x:auto;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th style="text-align:center;">SL</th>
                            <th style="text-align:right;">Đơn giá</th>
                            <th style="text-align:right;">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                        <tr>
                            <td>
                                <div style="font-weight:600;">{{ $item->product->name ?? 'Sản phẩm đã xóa' }}</div>
                                @if($item->note)
                                <div style="font-size:11.5px;color:var(--text-muted);">📝 {{ $item->note }}</div>
                                @endif
                            </td>
                            <td style="text-align:center;">{{ $item->quantity }}</td>
                            <td style="text-align:right;">{{ number_format($item->unit_price, 0, ',', '.') }}đ</td>
                            <td style="text-align:right;font-weight:700;">{{ number_format($item->unit_price * $item->quantity, 0, ',', '.') }}đ</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="padding:16px 22px;border-top:1px solid var(--border);">
                <div style="display:flex;flex-direction:column;gap:8px;max-width:280px;margin-left:auto;">
                    <div style="display:flex;justify-content:space-between;font-size:13.5px;">
                        <span style="color:var(--text-muted);">Tạm tính</span>
                        <span>{{ number_format($order->total_price, 0, ',', '.') }}đ</span>
                    </div>
                    @if($order->discount_amount > 0)
                    <div style="display:flex;justify-content:space-between;font-size:13.5px;">
                        <span style="color:var(--text-muted);">Giảm giá</span>
                        <span style="color:#16a34a;">- {{ number_format($order->discount_amount, 0, ',', '.') }}đ</span>
                    </div>
                    @endif
                    @if($order->shipping_fee > 0)
                    <div style="display:flex;justify-content:space-between;font-size:13.5px;">
                        <span style="color:var(--text-muted);">Phí giao hàng</span>
                        <span>{{ number_format($order->shipping_fee, 0, ',', '.') }}đ</span>
                    </div>
                    @endif
                    <div style="display:flex;justify-content:space-between;font-size:16px;font-weight:800;border-top:1px solid var(--border);padding-top:10px;">
                        <span>Tổng cộng</span>
                        <span style="color:var(--primary);">{{ number_format($order->final_price, 0, ',', '.') }}đ</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Note --}}
        @if($order->note)
        <div class="card">
            <div class="card-header">
                <div class="card-header-title"><i class="fas fa-sticky-note" style="color:var(--primary);"></i> Ghi chú đơn</div>
            </div>
            <div class="card-body">
                <p style="margin:0;color:var(--text-muted);">{{ $order->note }}</p>
            </div>
        </div>
        @endif
    </div>

    {{-- Right --}}
    <div style="display:flex;flex-direction:column;gap:18px;position:sticky;top:80px;">

        {{-- Status --}}
        <div class="card">
            <div class="card-header"><div class="card-header-title"><i class="fas fa-info-circle" style="color:var(--primary);"></i> Trạng thái đơn</div></div>
            <div class="card-body">
                <div style="text-align:center;margin-bottom:16px;">
                    <span class="badge badge-{{ $order->status }}" style="font-size:14px;padding:7px 18px;">
                        {{ $order->status_label ?? $order->status }}
                    </span>
                </div>
                <div style="font-size:13px;color:var(--text-muted);">
                    <div style="margin-bottom:8px;">
                        <strong>Loại đơn:</strong> {{ $order->order_type === 'delivery' ? '🛵 Giao hàng' : '🏠 Tại quán' }}
                    </div>
                    <div style="margin-bottom:8px;">
                        <strong>Tạo lúc:</strong> {{ $order->created_at->format('H:i d/m/Y') }}
                    </div>
                    <div>
                        <strong>Nhân viên:</strong> {{ $order->staff->name ?? '—' }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Customer --}}
        <div class="card">
            <div class="card-header"><div class="card-header-title"><i class="fas fa-user" style="color:var(--primary);"></i> Khách hàng</div></div>
            <div class="card-body">
                @if($order->user)
                <div class="user-cell" style="margin-bottom:12px;">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($order->user->name) }}&background=d4813a&color=fff&size=72"
                         class="user-avatar" alt="">
                    <div>
                        <div class="user-cell-name">{{ $order->user->name }}</div>
                        <div class="user-cell-email">{{ $order->user->email }}</div>
                    </div>
                </div>
                <div style="font-size:13px;color:var(--text-muted);">
                    📞 {{ $order->user->phone ?? '—' }}
                </div>
                @else
                <p style="color:var(--text-muted);font-size:13px;">Khách vãng lai</p>
                @endif

                @if($order->address)
                <div style="margin-top:12px;padding-top:12px;border-top:1px solid var(--border);font-size:13px;">
                    <strong>📍 Địa chỉ giao:</strong><br>
                    <span style="color:var(--text-muted);">{{ $order->address->full_address ?? '—' }}</span>
                </div>
                @endif
            </div>
        </div>

        {{-- Payment --}}
        <div class="card">
            <div class="card-header"><div class="card-header-title"><i class="fas fa-credit-card" style="color:var(--primary);"></i> Thanh toán</div></div>
            <div class="card-body">
                @if($order->payment)
                <div style="font-size:13.5px;display:flex;flex-direction:column;gap:8px;">
                    <div style="display:flex;justify-content:space-between;">
                        <span style="color:var(--text-muted);">Phương thức</span>
                        <span style="font-weight:600;">{{ strtoupper($order->payment->method) }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;">
                        <span style="color:var(--text-muted);">Trạng thái</span>
                        <span class="badge {{ $order->payment->status === 'paid' ? 'badge-active' : 'badge-pending' }}">
                            {{ $order->payment->status === 'paid' ? '✅ Đã thanh toán' : '⏳ Chờ thanh toán' }}
                        </span>
                    </div>
                    @if($order->payment->paid_at)
                    <div style="display:flex;justify-content:space-between;">
                        <span style="color:var(--text-muted);">Lúc</span>
                        <span>{{ \Carbon\Carbon::parse($order->payment->paid_at)->format('H:i d/m/Y') }}</span>
                    </div>
                    @endif
                    <div style="display:flex;justify-content:space-between;font-weight:800;font-size:16px;border-top:1px solid var(--border);padding-top:10px;">
                        <span>Số tiền</span>
                        <span style="color:var(--primary);">{{ number_format($order->payment->amount, 0, ',', '.') }}đ</span>
                    </div>
                </div>
                @else
                <p style="color:var(--text-muted);font-size:13px;">Chưa có thông tin thanh toán</p>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
