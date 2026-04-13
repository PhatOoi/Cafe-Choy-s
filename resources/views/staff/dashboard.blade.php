@extends('staff.layout')

@section('title', 'Dashboard nhân viên')
@section('page-title', 'Dashboard')

@section('styles')
<style>
    .stat-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px; }
    @media (max-width: 900px) { .stat-row { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 500px) { .stat-row { grid-template-columns: 1fr; } }

    .orders-grid { display: grid; grid-template-columns: 1fr; gap: 12px; }

    .order-card {
        background: #fff;
        border-radius: 12px;
        border: 1px solid #eef0f4;
        padding: 16px 20px;
        display: flex;
        align-items: center;
        gap: 16px;
        transition: box-shadow .18s;
    }
    .order-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.08); }

    .order-card-id {
        font-size: 13px; font-weight: 700; color: var(--primary);
        min-width: 56px;
    }
    .order-card-info { flex: 1; }
    .order-card-name { font-size: 14px; font-weight: 600; color: #1a1a2e; }
    .order-card-meta { font-size: 12px; color: #8a8fa8; margin-top: 2px; }

    .order-card-price {
        font-size: 15px; font-weight: 700;
        color: var(--primary);
        min-width: 80px; text-align: right;
    }

    .quick-action-btn {
        font-size: 12px;
        padding: 5px 12px;
        border-radius: 7px;
        border: none;
        cursor: pointer;
        font-weight: 500;
        text-decoration: none;
        display: inline-block;
        transition: all .18s;
    }
    .qa-confirm  { background: #eef4ff; color: #4d7cfe; }
    .qa-confirm:hover  { background: #4d7cfe; color: #fff; }
    .qa-prepare  { background: #fff8e5; color: #c09000; }
    .qa-prepare:hover  { background: #c09000; color: #fff; }
    .qa-deliver  { background: #f0f0ff; color: #6366f1; }
    .qa-deliver:hover  { background: #6366f1; color: #fff; }
    .qa-view { background: #f4f6fb; color: #555; }
    .qa-view:hover { background: #ddd; color: #222; }
</style>
@endsection

@section('content')

{{-- Stat cards --}}
<div class="stat-row">
    <div class="stat-card">
        <div class="stat-icon orange"><i class="fas fa-hourglass-half"></i></div>
        <div>
            <div class="stat-value">{{ $stats['pending'] }}</div>
            <div class="stat-label">Chờ xác nhận</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-blender"></i></div>
        <div>
            <div class="stat-value">{{ $stats['processing'] }}</div>
            <div class="stat-label">Đang xử lý</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon purple"><i class="fas fa-motorcycle"></i></div>
        <div>
            <div class="stat-value">{{ $stats['delivering'] }}</div>
            <div class="stat-label">Đang giao</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
        <div>
            <div class="stat-value">{{ $stats['today'] }}</div>
            <div class="stat-label">Đơn của tôi hôm nay</div>
        </div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 320px;gap:20px;" class="dashboard-grid">

    {{-- Recent active orders --}}
    <div class="card">
        <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;">
            <span><i class="fas fa-fire" style="color:var(--primary);margin-right:8px;"></i>Đơn hàng cần xử lý</span>
            <a href="{{ route('staff.orders') }}" class="btn-outline-staff" style="font-size:12px;padding:4px 12px;">Xem tất cả</a>
        </div>
        <div class="card-body" style="padding:12px;">
            @forelse($recentOrders as $order)
            <div class="order-card">
                <div class="order-card-id">#{{ $order->id }}</div>
                <div class="order-card-info">
                    <div class="order-card-name">{{ $order->user->name ?? 'Khách vãng lai' }}</div>
                    <div class="order-card-meta">
                        <span class="badge-status badge-{{ $order->status }}">{{ $order->status_label }}</span>
                        &nbsp;·&nbsp;{{ $order->items->count() }} món
                        &nbsp;·&nbsp;{{ $order->order_type === 'delivery' ? '🛵 Giao hàng' : '🏠 Tại quán' }}
                    </div>
                </div>
                <div style="display:flex;flex-direction:column;align-items:flex-end;gap:6px;">
                    <div class="order-card-price">{{ number_format($order->final_price, 0, ',', '.') }}đ</div>
                    <div style="display:flex;gap:6px;">
                        @if(in_array('confirmed', $order->next_statuses))
                        <form action="{{ route('staff.order.status', $order->id) }}" method="POST" style="margin:0;">
                            @csrf
                            <input type="hidden" name="status" value="confirmed">
                            <button class="quick-action-btn qa-confirm" type="submit">Xác nhận</button>
                        </form>
                        @elseif(in_array('processing', $order->next_statuses))
                        <form action="{{ route('staff.order.status', $order->id) }}" method="POST" style="margin:0;">
                            @csrf
                            <input type="hidden" name="status" value="processing">
                            <button class="quick-action-btn qa-prepare" type="submit">Pha chế</button>
                        </form>
                        @elseif(in_array('delivering', $order->next_statuses))
                        <form action="{{ route('staff.order.status', $order->id) }}" method="POST" style="margin:0;">
                            @csrf
                            <input type="hidden" name="status" value="delivering">
                            <button class="quick-action-btn qa-deliver" type="submit">Giao hàng</button>
                        </form>
                        @endif
                        <a href="{{ route('staff.order.detail', $order->id) }}" class="quick-action-btn qa-view">Chi tiết</a>
                    </div>
                </div>
            </div>
            @empty
            <div style="text-align:center;padding:40px;color:#aaa;">
                <i class="fas fa-check-circle" style="font-size:40px;margin-bottom:10px;color:#cde8d0;"></i>
                <p>Không có đơn nào cần xử lý</p>
            </div>
            @endforelse
        </div>
    </div>

    {{-- Quick actions --}}
    <div style="display:flex;flex-direction:column;gap:16px;">
        <div class="card">
            <div class="card-header"><i class="fas fa-bolt" style="color:var(--primary);margin-right:8px;"></i>Thao tác nhanh</div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:10px;">
                <a href="{{ route('staff.create-order') }}" class="btn-primary-staff" style="justify-content:center;">
                    <i class="fas fa-plus"></i> Tạo đơn tại quán
                </a>
                <a href="{{ route('staff.orders', ['status' => 'pending']) }}" class="btn-outline-staff" style="justify-content:center;">
                    <i class="fas fa-clock"></i> Đơn chờ xác nhận ({{ $stats['pending'] }})
                </a>
                <a href="{{ route('staff.orders', ['status' => 'delivering']) }}" class="btn-outline-staff" style="justify-content:center;">
                    <i class="fas fa-motorcycle"></i> Đang giao ({{ $stats['delivering'] }})
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><i class="fas fa-info-circle" style="color:var(--primary);margin-right:8px;"></i>Luồng trạng thái</div>
            <div class="card-body">
                @foreach([
                    ['pending', 'Chờ xác nhận', 'warning'],
                    ['confirmed', 'Đã xác nhận', 'info'],
                    ['processing', 'Đang pha chế', 'primary'],
                    ['ready', 'Sẵn sàng', 'success'],
                    ['delivering', 'Đang giao', 'purple'],
                    ['delivered', 'Đã giao', 'success'],
                ] as [$s, $label, $c])
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
                    <span class="badge-status badge-{{ $s }}" style="min-width:100px;text-align:center;">{{ $label }}</span>
                    @if(!$loop->last)<i class="fas fa-arrow-down" style="color:#ccc;font-size:10px;"></i>@endif
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<style>
@media (max-width: 768px) {
    .dashboard-grid { grid-template-columns: 1fr !important; }
}
</style>
@endsection
