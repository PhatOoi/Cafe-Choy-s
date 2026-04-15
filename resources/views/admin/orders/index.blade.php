@extends('admin.layout')

@section('title', 'Quản lý đơn hàng')
@section('page-title', 'Đơn hàng')
@section('breadcrumb', 'Admin / Đơn hàng')

@section('content')

<div class="page-header">
    <div>
        <div class="page-header-title">Quản lý đơn hàng</div>
        <div class="page-header-sub">Theo dõi và kiểm soát tất cả đơn hàng</div>
    </div>
</div>

{{-- Status tabs --}}
<div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:18px;">
    @php
    $statusTabs = [
        '' => ['label'=>'Tất cả','icon'=>'fas fa-list'],
        'pending'    => ['label'=>'Chờ xác nhận','icon'=>'fas fa-clock'],
        'confirmed'  => ['label'=>'Đã xác nhận','icon'=>'fas fa-check'],
        'processing' => ['label'=>'Pha chế','icon'=>'fas fa-blender'],
        'ready'      => ['label'=>'Sẵn sàng','icon'=>'fas fa-box'],
        'delivering' => ['label'=>'Đang giao','icon'=>'fas fa-motorcycle'],
        'delivered'  => ['label'=>'Đã giao','icon'=>'fas fa-check-circle'],
        'cancelled'  => ['label'=>'Đã hủy','icon'=>'fas fa-times-circle'],
    ];
    $currentStatus = request('status', '');
    @endphp
    @foreach($statusTabs as $val => $tab)
    <a href="{{ route('admin.orders', array_merge(request()->except('status','page'), $val ? ['status'=>$val] : [])) }}"
       style="display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:10px;font-size:12.5px;font-weight:600;text-decoration:none;transition:all .16s;
              {{ $currentStatus === $val ? 'background:var(--primary);color:#fff;' : 'background:#fff;color:var(--text-muted);border:1px solid var(--border);' }}">
        <i class="{{ $tab['icon'] }}"></i>
        {{ $tab['label'] }}
        @if($val && isset($statusCounts[$val]))
        <span style="background:{{ $currentStatus === $val ? 'rgba(255,255,255,.3)' : 'var(--bg)' }};padding:1px 7px;border-radius:20px;font-size:11px;">
            {{ $statusCounts[$val] }}
        </span>
        @endif
    </a>
    @endforeach
</div>

<div class="card">
    {{-- Filter --}}
    <form method="GET" action="{{ route('admin.orders') }}">
        <input type="hidden" name="status" value="{{ request('status') }}">
        <div class="filter-bar">
            <input type="text" name="search" class="form-control"
                   placeholder="🔍 Mã đơn hoặc tên khách..." value="{{ request('search') }}"
                   style="min-width:220px;">
            <input type="date" name="from" class="form-control" value="{{ request('from') }}" title="Từ ngày">
            <input type="date" name="to"   class="form-control" value="{{ request('to') }}"   title="Đến ngày">
            <button type="submit" class="btn-primary-admin btn-sm"><i class="fas fa-filter"></i> Lọc</button>
            @if(request()->hasAny(['search','from','to']))
            <a href="{{ route('admin.orders', request()->only('status')) }}" class="btn-outline-admin btn-sm">
                <i class="fas fa-times"></i> Xóa lọc
            </a>
            @endif
        </div>
    </form>

    <div style="overflow-x:auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>#ID</th>
                    <th>Khách hàng</th>
                    <th>Loại đơn</th>
                    <th>Trạng thái</th>
                    <th>Tổng tiền</th>
                    <th>Thanh toán</th>
                    <th>Nhân viên</th>
                    <th>Thời gian</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                <tr>
                    <td style="font-weight:700;color:var(--primary);">#{{ $order->id }}</td>
                    <td>
                        <div style="font-weight:600;font-size:13.5px;">{{ $order->user->name ?? 'Khách vãng lai' }}</div>
                        @if($order->user)
                        <div style="font-size:11.5px;color:var(--text-muted);">{{ $order->user->phone ?? $order->user->email }}</div>
                        @endif
                    </td>
                    <td>
                        <span style="font-size:12px;">
                            {{ $order->order_type === 'delivery' ? '🛵 Giao hàng' : '🏠 Tại quán' }}
                        </span>
                    </td>
                    <td><span class="badge badge-{{ $order->status }}">{{ $order->status_label ?? $order->status }}</span></td>
                    <td style="font-weight:700;white-space:nowrap;">{{ number_format($order->final_price, 0, ',', '.') }}đ</td>
                    <td>
                        @if($order->payment)
                        <span class="badge {{ $order->payment->status === 'paid' ? 'badge-active' : 'badge-pending' }}">
                            {{ $order->payment->status === 'paid' ? '✅ Đã TT' : '⏳ Chờ TT' }}
                        </span>
                        @else
                        <span style="color:var(--text-muted);font-size:12px;">—</span>
                        @endif
                    </td>
                    <td style="font-size:12.5px;color:var(--text-muted);">{{ $order->staff->name ?? '—' }}</td>
                    <td style="font-size:12px;color:var(--text-muted);white-space:nowrap;">
                        {{ $order->created_at->format('d/m H:i') }}
                    </td>
                    <td>
                        <a href="{{ route('admin.orders.detail', $order->id) }}" class="btn-edit btn-sm">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align:center;padding:40px;color:var(--text-muted);">
                        <i class="fas fa-inbox" style="font-size:36px;display:block;margin-bottom:10px;opacity:.3;"></i>
                        Không có đơn hàng nào
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($orders->hasPages())
    <div style="padding:16px 22px;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
        <div style="font-size:13px;color:var(--text-muted);">
            Hiển thị {{ $orders->firstItem() }}–{{ $orders->lastItem() }} / {{ $orders->total() }} đơn
        </div>
        {{ $orders->withQueryString()->links() }}
    </div>
    @endif
</div>

@endsection
