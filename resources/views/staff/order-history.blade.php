@extends('staff.layout')

@section('title', 'Lịch sử đơn hàng')

@section('styles')
<style>
    .page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
        flex-wrap: wrap;
        gap: 12px;
    }
    .page-title { font-size: 22px; font-weight: 700; color: #1a1a2e; margin: 0; }
    .page-subtitle { font-size: 13px; color: #8a8fa8; margin-top: 2px; }

    .history-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }
    @media (max-width: 992px) { .history-grid { grid-template-columns: 1fr; } }

    .section-tag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        font-weight: 600;
        padding: 3px 10px;
        border-radius: 20px;
        margin-left: 8px;
        vertical-align: middle;
    }
    .tag-staff   { background: #fff4ec; color: #c05e10; }
    .tag-online  { background: #e5f6ff; color: #0077b6; }
    .tag-count   { background: #f3f4f8; color: #4a5568; font-size: 11px; }

    .status-dot {
        width: 8px; height: 8px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 4px;
    }
    .dot-pending    { background: #f6ad55; }
    .dot-confirmed  { background: #4da6ff; }
    .dot-processing { background: #667eea; }
    .dot-ready      { background: #27ae60; }
    .dot-delivering { background: #27ae60; }
    .dot-delivered  { background: #1a8a1a; }
    .dot-cancelled  { background: #aaa; }
    .dot-failed     { background: #e53e3e; }

    .no-data {
        text-align: center;
        padding: 40px 20px;
        color: #b0b7c7;
        font-size: 14px;
    }
    .no-data i { font-size: 32px; margin-bottom: 10px; display: block; }

    .order-amount {
        font-weight: 600;
        color: #1a1a2e;
        white-space: nowrap;
    }
    .order-time { font-size: 12px; color: #8a8fa8; }
    .staff-name { font-size: 12px; color: #6b7280; }

    .prune-note {
        font-size: 12px;
        color: #9ca3af;
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 8px 14px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
</style>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">📋 Lịch sử đơn hàng</h1>
        <div class="page-subtitle">Hiển thị đơn trong 24 giờ qua · Tự động xóa đơn cũ hơn 7 ngày</div>
    </div>
    <div class="prune-note">
        <i class="fas fa-clock" style="color:#d4813a;"></i>
        Dữ liệu lưu tối đa <strong>7 ngày</strong> — hệ thống tự dọn mỗi ngày lúc 00:05
    </div>
</div>

<div class="history-grid">
    {{-- ── Bảng 1: Đơn nhân viên tạo tại quán ── --}}
    <div class="card">
        <div class="card-header" style="background:#fff8ef;">
            <i class="fas fa-user-tie" style="color:#d4813a;"></i>
            Đơn tại quán (nhân viên tạo)
            <span class="section-tag tag-staff">POS</span>
            <span class="section-tag tag-count">{{ $staffOrders->count() }} đơn</span>
        </div>
        <div class="card-body" style="padding:0;">
            @if($staffOrders->isEmpty())
                <div class="no-data">
                    <i class="fas fa-clipboard-list"></i>
                    Chưa có đơn nào trong 24 giờ qua
                </div>
            @else
                <table class="staff-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Khách hàng</th>
                            <th>Nhân viên</th>
                            <th>Món</th>
                            <th>Tổng</th>
                            <th>Trạng thái</th>
                            <th>Giờ</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($staffOrders as $order)
                        <tr>
                            <td><strong>#{{ $order->id }}</strong></td>
                            <td>
                                {{ $order->user->name ?? 'Khách vãng lai' }}
                                @if($order->user?->phone)
                                    <div class="order-time">{{ $order->user->phone }}</div>
                                @endif
                            </td>
                            <td>
                                <div class="staff-name">
                                    <i class="fas fa-user" style="color:#d4813a;font-size:10px;"></i>
                                    {{ $order->staff->name ?? '—' }}
                                </div>
                            </td>
                            <td>{{ $order->items_count }} món</td>
                            <td>
                                <span class="order-amount">
                                    {{ number_format($order->final_price, 0, ',', '.') }}đ
                                </span>
                                @if($order->discount_amount > 0)
                                    <div class="order-time" style="color:#15803d;">
                                        -{{ number_format($order->discount_amount, 0, ',', '.') }}đ giảm
                                    </div>
                                @endif
                            </td>
                            <td>
                                <span class="badge-status badge-{{ $order->status }}">
                                    <span class="status-dot dot-{{ $order->status }}"></span>
                                    {{ $order->status_label }}
                                </span>
                            </td>
                            <td><span class="order-time">{{ $order->created_at->format('H:i') }}</span></td>
                            <td>
                                <a href="{{ route('staff.order.detail', $order->id) }}" class="btn-outline-staff" style="padding:4px 10px;font-size:12px;">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    {{-- ── Bảng 2: Đơn khách đặt online ── --}}
    <div class="card">
        <div class="card-header" style="background:#e5f6ff;">
            <i class="fas fa-mobile-alt" style="color:#0077b6;"></i>
            Đơn khách tự đặt (online)
            <span class="section-tag tag-online">App</span>
            <span class="section-tag tag-count">{{ $customerOrders->count() }} đơn</span>
        </div>
        <div class="card-body" style="padding:0;">
            @if($customerOrders->isEmpty())
                <div class="no-data">
                    <i class="fas fa-shopping-bag"></i>
                    Chưa có đơn nào trong 24 giờ qua
                </div>
            @else
                <table class="staff-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Khách hàng</th>
                            <th>Món</th>
                            <th>Tổng</th>
                            <th>Thanh toán</th>
                            <th>Trạng thái</th>
                            <th>Giờ</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($customerOrders as $order)
                        <tr>
                            <td><strong>#{{ $order->id }}</strong></td>
                            <td>
                                {{ $order->user->name ?? '—' }}
                                @if($order->user?->phone)
                                    <div class="order-time">{{ $order->user->phone }}</div>
                                @endif
                            </td>
                            <td>{{ $order->items_count }} món</td>
                            <td>
                                <span class="order-amount">
                                    {{ number_format($order->final_price, 0, ',', '.') }}đ
                                </span>
                                @if($order->discount_amount > 0)
                                    <div class="order-time" style="color:#15803d;">
                                        -{{ number_format($order->discount_amount, 0, ',', '.') }}đ giảm
                                    </div>
                                @endif
                            </td>
                            <td>
                                @if($order->payment)
                                    <span style="font-size:12px;text-transform:uppercase;font-weight:600;color:{{ $order->payment->status === 'paid' ? '#1a8a1a' : '#c09000' }};">
                                        {{ $order->payment->method }}
                                        @if($order->payment->status === 'paid')
                                            <i class="fas fa-check-circle" style="color:#27ae60;"></i>
                                        @else
                                            <i class="fas fa-clock" style="color:#f6ad55;"></i>
                                        @endif
                                    </span>
                                @else
                                    <span class="order-time">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge-status badge-{{ $order->status }}">
                                    <span class="status-dot dot-{{ $order->status }}"></span>
                                    {{ $order->status_label }}
                                </span>
                            </td>
                            <td><span class="order-time">{{ $order->created_at->format('H:i') }}</span></td>
                            <td>
                                <a href="{{ route('staff.order.detail', $order->id) }}" class="btn-outline-staff" style="padding:4px 10px;font-size:12px;">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>
@endsection
