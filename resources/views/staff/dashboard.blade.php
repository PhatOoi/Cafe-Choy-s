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
            <div class="stat-label">Đang chuẩn bị</div>
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
                        &nbsp;·&nbsp;🏠 Tại quán
                    </div>
                </div>
                <div style="display:flex;flex-direction:column;align-items:flex-end;gap:6px;">
                    <div class="order-card-price">{{ number_format($order->final_price, 0, ',', '.') }}đ</div>
                    <div style="display:flex;gap:6px;">
                        @if($order->payment && $order->payment->method === 'bank_transfer' && $order->payment->status !== 'paid')
                        <form action="{{ route('staff.order.payment.confirm', $order->id) }}" method="POST" style="margin:0;">
                            @csrf
                            <button class="quick-action-btn qa-confirm" type="submit">Chuyển khoản thành công</button>
                        </form>
                        @elseif(in_array('confirmed', $order->next_statuses))
                        <form action="{{ route('staff.order.status', $order->id) }}" method="POST" style="margin:0;">
                            @csrf
                            <input type="hidden" name="status" value="confirmed">
                            <button class="quick-action-btn qa-confirm" type="submit">Xác nhận</button>
                        </form>
                        @elseif(in_array('processing', $order->next_statuses))
                        <form action="{{ route('staff.order.status', $order->id) }}" method="POST" style="margin:0;">
                            @csrf
                            <input type="hidden" name="status" value="processing">
                            <button class="quick-action-btn qa-prepare" type="submit">Chuẩn bị</button>
                        </form>
                        @elseif(in_array('delivered', $order->next_statuses))
                        <form action="{{ route('staff.order.status', $order->id) }}" method="POST" style="margin:0;">
                            @csrf
                            <input type="hidden" name="status" value="delivered">
                            <button class="quick-action-btn qa-deliver" type="submit">Hoàn thành</button>
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
            </div>
        </div>

        <div class="card">
            <div class="card-header"><i class="fas fa-info-circle" style="color:var(--primary);margin-right:8px;"></i>Luồng trạng thái</div>
            <div class="card-body">
                @foreach([
                    ['pending', 'Chờ xác nhận', 'warning'],
                    ['confirmed', 'Đã xác nhận', 'info'],
                    ['processing', 'Đang chuẩn bị', 'primary'],
                    ['ready', 'Sẵn sàng', 'success'],
                    ['delivered', 'Hoàn thành', 'success'],
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

{{-- Widget lịch tuần này --}}
<div class="card" style="margin-top:20px;">
    <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;gap:12px;">
        <div>
            <div class="card-header-title" style="display:flex;align-items:center;gap:8px;">
                <i class="fas fa-calendar-week" style="color:var(--primary);"></i> Lịch làm việc tuần này
            </div>
            <div style="font-size:12px;color:var(--text-muted);margin-top:2px;">
                {{ $dashWeekStart->format('d/m') }} – {{ $dashWeekEnd->format('d/m/Y') }}
            </div>
        </div>
        <a href="{{ route('staff.work-schedules.index') }}" style="font-size:12px;color:var(--primary);font-weight:600;text-decoration:none;">Xem chi tiết →</a>
    </div>
    <div style="padding:16px 20px;overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;min-width:600px;">
            <thead>
                <tr>
                    <th style="padding:9px 10px;background:#f8fafc;border:1px solid #e2e8f0;font-size:11px;font-weight:700;color:#475569;min-width:90px;">Khung giờ</th>
                    @foreach($dashWeekDays as $day)
                        @php $isToday = $day->isToday(); @endphp
                        <th style="padding:9px 8px;border:1px solid #e2e8f0;background:{{ $isToday ? '#f0fdf4' : '#f8fafc' }};text-align:center;">
                            <div style="font-size:11px;font-weight:800;color:{{ $isToday ? '#166534' : '#334155' }};">
                                {{ ['CN','T2','T3','T4','T5','T6','T7'][$day->dayOfWeek] }}
                                @if($isToday) <span style="display:inline-block;background:#166534;color:#fff;font-size:9px;padding:1px 5px;border-radius:999px;margin-left:2px;">nay</span>@endif
                            </div>
                            <div style="font-size:10px;color:#94a3b8;">{{ $day->format('d/m') }}</div>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @php
                    $allSlots = [
                        '08_16' => '8h–16h',
                        '16_24' => '16h–24h',
                        '08_12' => '8h–12h',
                        '12_16' => '12h–16h',
                        '16_20' => '16h–20h',
                        '20_24' => '20h–24h',
                    ];
                    $hasAnyRow = false;
                @endphp
                @foreach($allSlots as $slotKey => $slotLabel)
                    <tr>
                        <td style="padding:8px 10px;border:1px solid #e2e8f0;background:#fafbff;font-size:12px;font-weight:700;color:#475569;white-space:nowrap;">{{ $slotLabel }}</td>
                        @foreach($dashWeekDays as $day)
                            @php
                                $dateKey = $day->toDateString();
                                $isToday = $day->isToday();
                                $cellEntries = collect($thisWeekSchedule[$dateKey] ?? [])->filter(function($e) use ($slotKey) {
                                    $s = substr($e->start_time, 0, 5);
                                    $et = $e->employment_type;
                                    if ($et === 'full_time') {
                                        if ($slotKey === '08_16') return $s === '08:00';
                                        if ($slotKey === '16_24') return $s === '16:00';
                                    } else {
                                        if ($slotKey === '08_12') return $s === '08:00';
                                        if ($slotKey === '12_16') return $s === '12:00';
                                        if ($slotKey === '16_20') return $s === '16:00';
                                        if ($slotKey === '20_24') return $s === '20:00';
                                    }
                                    return false;
                                });
                            @endphp
                            <td style="padding:6px 8px;border:1px solid #e2e8f0;text-align:center;vertical-align:middle;background:{{ $isToday ? '#f9fefb' : '#fff' }};">
                                @if($cellEntries->isNotEmpty())
                                    <div style="display:flex;flex-direction:column;gap:4px;align-items:center;">
                                        @foreach($cellEntries as $entry)
                                            @php $isMe = (int)$entry->staff_id === auth()->id(); @endphp
                                            <span style="display:inline-block;padding:4px 8px;border-radius:8px;font-size:11px;font-weight:700;background:{{ $isMe ? '#dcfce7' : ($entry->employment_type==='full_time'?'#e0f2fe':'#fef3c7') }};color:{{ $isMe ? '#166534' : ($entry->employment_type==='full_time'?'#0369a1':'#b45309') }};white-space:nowrap;max-width:90px;overflow:hidden;text-overflow:ellipsis;" title="{{ $entry->staff->name ?? '' }}">
                                                @if($isMe)
                                                    Bạn
                                                @else
                                                    {{ $entry->staff->name ?? '—' }}
                                                @endif
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span style="color:#e2e8f0;font-size:13px;">—</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
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
