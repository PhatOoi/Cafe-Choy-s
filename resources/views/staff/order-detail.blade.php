@extends('staff.layout')

@section('title', 'Đơn hàng #' . $order->id)
@section('page-title', 'Chi tiết đơn #' . $order->id)

@section('styles')
<style>
    .detail-grid {
        display: grid;
        grid-template-columns: 1fr 340px;
        gap: 20px;
    }
    @media (max-width: 900px) { .detail-grid { grid-template-columns: 1fr; } }

    .info-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        padding: 10px 0;
        border-bottom: 1px solid #f3f4f8;
        font-size: 14px;
    }
    .info-row:last-child { border-bottom: none; }
    .info-label { color: #8a8fa8; font-size: 13px; min-width: 120px; }
    .info-value { font-weight: 500; text-align: right; }

    .item-row {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 14px 0;
        border-bottom: 1px solid #f3f4f8;
    }
    .item-row:last-child { border-bottom: none; }

    .item-img {
        width: 52px; height: 52px;
        border-radius: 10px;
        object-fit: cover;
        background: #f4f6fb;
        flex-shrink: 0;
    }
    .item-name { font-size: 14px; font-weight: 600; color: #1a1a2e; }
    .item-extras { font-size: 12px; color: #8a8fa8; margin-top: 3px; }
    .item-note { font-size: 12px; color: #d4813a; font-style: italic; margin-top: 2px; }
    .item-qty { font-size: 13px; color: #555; margin-top: 2px; }
    .item-price { font-size: 14px; font-weight: 700; color: #1a1a2e; margin-left: auto; white-space: nowrap; }

    /* Status timeline */
    .status-timeline { padding: 10px 0; }
    .timeline-item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 8px 0;
    }
    .timeline-dot {
        width: 28px; height: 28px;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 12px;
        flex-shrink: 0;
    }
    .timeline-dot.done    { background: #edfbf3; color: #27ae60; }
    .timeline-dot.current { background: var(--primary); color: #fff; }
    .timeline-dot.pending { background: #f4f6fb; color: #ccc; }
    .timeline-label { font-size: 13px; font-weight: 500; padding-top: 4px; }
    .timeline-label.pending { color: #bbb; }

    /* Action buttons */
    .status-actions { display: flex; flex-direction: column; gap: 10px; margin-top: 16px; }
    .btn-action-lg {
        padding: 12px;
        border-radius: 10px;
        border: none;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all .18s;
        font-family: 'Poppins', sans-serif;
    }
    .btn-action-confirm  { background: #eef4ff; color: #4d7cfe; }
    .btn-action-confirm:hover  { background: #4d7cfe; color: #fff; }
    .btn-action-process  { background: #fff8e5; color: #c09000; }
    .btn-action-process:hover  { background: #c09000; color: #fff; }
    .btn-action-ready    { background: #edfbf3; color: #27ae60; }
    .btn-action-ready:hover    { background: #27ae60; color: #fff; }
    .btn-action-deliver  { background: #f0f0ff; color: #6366f1; }
    .btn-action-deliver:hover  { background: #6366f1; color: #fff; }
    .btn-action-done     { background: #edfbf3; color: #27ae60; }
    .btn-action-done:hover     { background: #27ae60; color: #fff; }
    .btn-action-cancel   { background: #fff0f0; color: #e53e3e; }
    .btn-action-cancel:hover   { background: #e53e3e; color: #fff; }

    .price-summary { padding: 12px 0; }
    .price-row {
        display: flex;
        justify-content: space-between;
        font-size: 13px;
        padding: 5px 0;
        color: #555;
    }
    .price-row.total {
        font-size: 16px;
        font-weight: 700;
        color: var(--primary);
        border-top: 1px solid #eee;
        margin-top: 8px;
        padding-top: 12px;
    }

    .address-box {
        background: #f8f9fc;
        border-radius: 10px;
        padding: 12px 16px;
        font-size: 13px;
        color: #555;
        margin-top: 4px;
    }
    .address-box i { color: var(--primary); margin-right: 6px; }

    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #8a8fa8;
        font-size: 13px;
        text-decoration: none;
        margin-bottom: 20px;
        transition: color .15s;
    }
    .back-link:hover { color: var(--primary); text-decoration: none; }
</style>
@endsection

@section('content')

<a href="{{ route('staff.orders') }}" class="back-link">
    <i class="fas fa-arrow-left"></i> Quay lại danh sách
</a>

<div class="detail-grid">

    {{-- ── LEFT: Order details ── --}}
    <div style="display:flex;flex-direction:column;gap:20px;">

        {{-- Order header --}}
        <div class="card">
            <div class="card-body">
                <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
                    <div>
                        <h2 style="font-size:20px;font-weight:700;margin:0;color:#1a1a2e;">Đơn hàng #{{ $order->id }}</h2>
                        <p style="font-size:13px;color:#8a8fa8;margin:4px 0 0;">
                            {{ \Carbon\Carbon::parse($order->created_at)->format('H:i — d/m/Y') }}
                            &nbsp;·&nbsp;
                            {{ $order->order_type === 'delivery' ? '🛵 Giao hàng' : '🏠 Tại quán' }}
                        </p>
                    </div>
                    <span class="badge-status badge-{{ $order->status }}" style="font-size:13px;padding:6px 16px;">
                        {{ $order->status_label }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Items --}}
        <div class="card">
            <div class="card-header"><i class="fas fa-coffee" style="color:var(--primary);margin-right:8px;"></i>Sản phẩm đặt</div>
            <div class="card-body">
                @foreach($order->items as $item)
                <div class="item-row">
                    <div style="position:relative;">
                        <img src="{{ $item->product->image_url ? asset('images/products/' . $item->product->image_url) : 'https://via.placeholder.com/52x52/f4f6fb/d4813a?text=☕' }}"
                             class="item-img"
                             onerror="this.src='https://via.placeholder.com/52x52/f4f6fb/d4813a?text=☕'">
                        <span style="position:absolute;top:-6px;right:-6px;background:var(--primary);color:#fff;border-radius:50%;width:18px;height:18px;font-size:10px;font-weight:700;display:flex;align-items:center;justify-content:center;">{{ $item->quantity }}</span>
                    </div>
                    <div style="flex:1;">
                        <div class="item-name">{{ $item->product->name ?? 'Sản phẩm đã xóa' }}</div>
                        @if($item->extras->count() > 0)
                        <div class="item-extras">
                            + {{ $item->extras->map(fn($e) => $e->extra_name . ($e->extra_price > 0 ? ' (' . number_format($e->extra_price, 0, ',', '.') . 'đ)' : ''))->join(', ') }}
                        </div>
                        @endif
                        @if($item->note)
                        <div class="item-note">📝 {{ $item->note }}</div>
                        @endif
                        <div class="item-qty">
                            {{ number_format($item->unit_price, 0, ',', '.') }}đ × {{ $item->quantity }}
                        </div>
                    </div>
                    <div class="item-price">{{ number_format($item->subtotal, 0, ',', '.') }}đ</div>
                </div>
                @endforeach

                {{-- Price summary --}}
                <div class="price-summary" style="margin-top:12px;border-top:2px solid #f0f2f5;padding-top:12px;">
                    <div class="price-row">
                        <span>Tạm tính</span>
                        <span>{{ number_format($order->total_price, 0, ',', '.') }}đ</span>
                    </div>
                    @if($order->discount_amount > 0)
                    <div class="price-row" style="color:#27ae60;">
                        <span>Giảm giá</span>
                        <span>-{{ number_format($order->discount_amount, 0, ',', '.') }}đ</span>
                    </div>
                    @endif
                    @if($order->shipping_fee > 0)
                    <div class="price-row">
                        <span>Phí giao hàng</span>
                        <span>{{ number_format($order->shipping_fee, 0, ',', '.') }}đ</span>
                    </div>
                    @endif
                    <div class="price-row total">
                        <span>Tổng thanh toán</span>
                        <span>{{ number_format($order->final_price, 0, ',', '.') }}đ</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Customer info --}}
        <div class="card">
            <div class="card-header"><i class="fas fa-user" style="color:var(--primary);margin-right:8px;"></i>Thông tin khách hàng</div>
            <div class="card-body">
                <div class="info-row">
                    <span class="info-label">Họ tên</span>
                    <span class="info-value">{{ $order->user->name ?? '—' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email</span>
                    <span class="info-value">{{ $order->user->email ?? '—' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Điện thoại</span>
                    <span class="info-value">{{ $order->user->phone ?? '—' }}</span>
                </div>
                @if($order->order_type === 'delivery' && $order->address)
                <div class="info-row" style="flex-direction:column;gap:6px;">
                    <span class="info-label">Địa chỉ giao hàng</span>
                    <div class="address-box">
                        <i class="fas fa-map-marker-alt"></i>
                        {{ $order->address->full_address }}
                    </div>
                </div>
                @endif
                @if($order->note)
                <div class="info-row" style="flex-direction:column;gap:6px;">
                    <span class="info-label">Ghi chú</span>
                    <div class="address-box" style="color:var(--primary);font-style:italic;">
                        📝 {{ $order->note }}
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Payment --}}
        @if($order->payment)
        <div class="card">
            <div class="card-header"><i class="fas fa-credit-card" style="color:var(--primary);margin-right:8px;"></i>Thanh toán</div>
            <div class="card-body">
                <div class="info-row">
                    <span class="info-label">Phương thức</span>
                    <span class="info-value">{{ $order->payment->method_label }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Trạng thái</span>
                    <span class="info-value">
                        <span class="badge-status {{ $order->payment->status === 'paid' ? 'badge-delivered' : 'badge-pending' }}">
                            {{ $order->payment->status_label }}
                        </span>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Số tiền</span>
                    <span class="info-value" style="color:var(--primary);">{{ number_format($order->payment->amount, 0, ',', '.') }}đ</span>
                </div>
                @if($order->payment->paid_at)
                <div class="info-row">
                    <span class="info-label">Thanh toán lúc</span>
                    <span class="info-value">{{ \Carbon\Carbon::parse($order->payment->paid_at)->format('H:i d/m/Y') }}</span>
                </div>
                @endif
                @if($order->payment->ref_code)
                <div class="info-row">
                    <span class="info-label">Mã giao dịch</span>
                    <span class="info-value" style="font-family:monospace;font-size:12px;">{{ $order->payment->ref_code }}</span>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>

    {{-- ── RIGHT: Status control ── --}}
    <div style="display:flex;flex-direction:column;gap:20px;">

        {{-- Status update --}}
        <div class="card">
            <div class="card-header"><i class="fas fa-exchange-alt" style="color:var(--primary);margin-right:8px;"></i>Cập nhật trạng thái</div>
            <div class="card-body">

                {{-- Timeline --}}
                <div class="status-timeline">
                    @php
                        $steps = ['pending','confirmed','processing','ready','delivering','delivered'];
                        $labels = [
                            'pending'    => 'Chờ xác nhận',
                            'confirmed'  => 'Đã xác nhận',
                            'processing' => 'Đang pha chế',
                            'ready'      => 'Sẵn sàng giao',
                            'delivering' => 'Đang giao',
                            'delivered'  => 'Đã giao',
                        ];
                        $icons = [
                            'pending'    => 'clock',
                            'confirmed'  => 'check',
                            'processing' => 'blender',
                            'ready'      => 'box',
                            'delivering' => 'motorcycle',
                            'delivered'  => 'check-double',
                        ];
                        $currentIdx = array_search($order->status, $steps);
                    @endphp

                    @if(in_array($order->status, ['cancelled','failed']))
                        <div style="text-align:center;padding:20px;color:#e53e3e;">
                            <i class="fas fa-{{ $order->status === 'cancelled' ? 'ban' : 'times-circle' }}" style="font-size:32px;margin-bottom:8px;display:block;"></i>
                            <span style="font-weight:600;">Đơn {{ $order->status_label }}</span>
                        </div>
                    @else
                        @foreach($steps as $i => $step)
                        <div class="timeline-item">
                            <div class="timeline-dot {{ $i < $currentIdx ? 'done' : ($i === $currentIdx ? 'current' : 'pending') }}">
                                <i class="fas fa-{{ $i <= $currentIdx ? $icons[$step] : '' }} {{ $i > $currentIdx ? 'fas fa-circle' : '' }}"
                                   style="{{ $i > $currentIdx ? 'color:#ddd;font-size:8px;' : '' }}"></i>
                            </div>
                            <div class="timeline-label {{ $i > $currentIdx ? 'pending' : '' }}">
                                {{ $labels[$step] }}
                            </div>
                        </div>
                        @if(!$loop->last)
                        <div style="width:1px;height:16px;background:#eee;margin-left:13px;"></div>
                        @endif
                        @endforeach
                    @endif
                </div>

                {{-- Action buttons --}}
                @if(count($order->next_statuses) > 0)
                <div class="status-actions">
                    @foreach($order->next_statuses as $nextStatus)
                    <form action="{{ route('staff.order.status', $order->id) }}" method="POST"
                          @if($nextStatus === 'cancelled') onsubmit="return confirm('Xác nhận hủy đơn này?')" @endif>
                        @csrf
                        <input type="hidden" name="status" value="{{ $nextStatus }}">
                        @php
                            $cfg = match($nextStatus) {
                                'confirmed'  => ['btn-action-confirm',  'check-circle',   'Xác nhận đơn hàng'],
                                'processing' => ['btn-action-process',  'blender',        'Bắt đầu pha chế'],
                                'ready'      => ['btn-action-ready',    'box',            'Đã sẵn sàng giao'],
                                'delivering' => ['btn-action-deliver',  'motorcycle',     'Bắt đầu giao hàng'],
                                'delivered'  => ['btn-action-done',     'check-double',   'Xác nhận đã giao'],
                                'cancelled'  => ['btn-action-cancel',   'ban',            'Hủy đơn hàng'],
                                default      => ['btn-action-confirm',  'arrow-right',    ucfirst($nextStatus)],
                            };
                        @endphp
                        <button type="submit" class="btn-action-lg {{ $cfg[0] }}" style="width:100%;">
                            <i class="fas fa-{{ $cfg[1] }}"></i> {{ $cfg[2] }}
                        </button>
                    </form>
                    @endforeach
                </div>
                @else
                <div style="text-align:center;padding:16px;color:#aaa;font-size:13px;">
                    Không có thao tác nào khả dụng
                </div>
                @endif
            </div>
        </div>

        {{-- Staff assigned --}}
        <div class="card">
            <div class="card-header"><i class="fas fa-user-tie" style="color:var(--primary);margin-right:8px;"></i>Nhân viên phụ trách</div>
            <div class="card-body">
                @if($order->staff)
                <div style="display:flex;align-items:center;gap:10px;">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($order->staff->name) }}&background=d4813a&color=fff&size=80"
                         style="width:44px;height:44px;border-radius:50%;" alt="">
                    <div>
                        <div style="font-size:14px;font-weight:600;">{{ $order->staff->name }}</div>
                        <div style="font-size:12px;color:#aaa;">{{ $order->staff->email }}</div>
                    </div>
                </div>
                @else
                <div style="color:#aaa;font-size:13px;text-align:center;padding:12px;">
                    Chưa phân công nhân viên
                </div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection
