@extends('admin.layout')

@section('title', 'Dashboard Quản trị')
@section('page-title', 'Dashboard')

@section('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<style>
    .dashboard-grid {
        display: grid;
        grid-template-columns: 1fr 340px;
        gap: 20px;
    }
    .top-products-list { display: flex; flex-direction: column; gap: 10px; }
    .top-product-item {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .top-product-rank {
        width: 28px; height: 28px;
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        font-size: 12px;
        font-weight: 700;
        flex-shrink: 0;
    }
    .rank-1 { background: linear-gradient(135deg,#ffd700,#ffa500); color: #fff; }
    .rank-2 { background: linear-gradient(135deg,#c0c0c0,#a0a0a0); color: #fff; }
    .rank-3 { background: linear-gradient(135deg,#cd7f32,#a0522d); color: #fff; }
    .rank-other { background: #f3f4f8; color: #6b7280; }
    .top-product-info { flex: 1; min-width: 0; }
    .top-product-name { font-size: 13.5px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .top-product-sold { font-size: 11.5px; color: #6b7280; }
    .top-product-bar-wrap { height: 4px; background: #f0f0f0; border-radius: 4px; margin-top: 4px; }
    .top-product-bar { height: 4px; background: linear-gradient(90deg, var(--primary), var(--admin-gold)); border-radius: 4px; }

    .chart-wrapper { position: relative; height: 240px; }

    .quick-stat-row {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
        margin-bottom: 20px;
    }
    .mini-stat {
        background: var(--card-bg);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 16px;
        text-align: center;
    }
    .mini-stat-val { font-size: 22px; font-weight: 800; color: var(--text-dark); }
    .mini-stat-lbl { font-size: 11.5px; color: var(--text-muted); margin-top: 3px; }

    @media (max-width: 992px) {
        .dashboard-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 600px) {
        .quick-stat-row { grid-template-columns: 1fr 1fr; }
    }
</style>
@endsection

@section('content')

{{-- ── Page header ── --}}
<div class="page-header">
    <div>
        <div class="page-header-title">Xin chào, {{ Auth::user()->name }} 👋</div>
        <div class="page-header-sub">Đây là tổng quan hệ thống hôm nay</div>
    </div>
    <a href="{{ route('admin.reports') }}" class="btn-primary-admin">
        <i class="fas fa-chart-line"></i> Xem báo cáo chi tiết
    </a>
</div>

{{-- ── Main stat cards ── --}}
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-icon si-gold"><i class="fas fa-coins"></i></div>
        <div>
            <div class="stat-value">{{ number_format($stats['total_revenue'] / 1000, 0, ',', '.') }}K</div>
            <div class="stat-label">Tổng doanh thu (đ)</div>
            <div class="stat-change up"><i class="fas fa-arrow-up"></i> Hôm nay: {{ number_format($stats['today_revenue'], 0, ',', '.') }}đ</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon si-orange"><i class="fas fa-receipt"></i></div>
        <div>
            <div class="stat-value">{{ number_format($stats['total_orders']) }}</div>
            <div class="stat-label">Tổng đơn hàng</div>
            <div class="stat-change" style="color:var(--primary);">
                <i class="fas fa-clock"></i> Chờ: {{ $stats['pending_orders'] }} đơn
            </div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon si-blue"><i class="fas fa-coffee"></i></div>
        <div>
            <div class="stat-value">{{ number_format($stats['total_products']) }}</div>
            <div class="stat-label">Sản phẩm</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon si-green"><i class="fas fa-users"></i></div>
        <div>
            <div class="stat-value">{{ number_format($stats['total_customers']) }}</div>
            <div class="stat-label">Khách hàng</div>
            <div class="stat-change" style="color:#16a34a;">
                <i class="fas fa-user-tie"></i> Nhân viên: {{ $stats['total_staff'] }}
            </div>
        </div>
    </div>
</div>

{{-- ── Dashboard grid ── --}}
<div class="dashboard-grid">

    {{-- Left col --}}
    <div style="display:flex;flex-direction:column;gap:20px;">

        {{-- Revenue chart --}}
        <div class="card">
            <div class="card-header">
                <div class="card-header-title">
                    <i class="fas fa-chart-area" style="color:var(--primary);"></i>
                    Doanh thu 7 ngày gần nhất
                </div>
                <a href="{{ route('admin.reports') }}" class="btn-outline-admin btn-sm">Chi tiết</a>
            </div>
            <div class="card-body">
                <div class="chart-wrapper">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Recent orders --}}
        <div class="card">
            <div class="card-header">
                <div class="card-header-title">
                    <i class="fas fa-fire" style="color:var(--primary);"></i>
                    Đơn hàng gần đây
                </div>
                <a href="{{ route('admin.orders') }}" class="btn-outline-admin btn-sm">Tất cả đơn</a>
            </div>
            <div style="overflow-x:auto;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>#ID</th>
                            <th>Khách hàng</th>
                            <th>Trạng thái</th>
                            <th>Tổng tiền</th>
                            <th>Thời gian</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentOrders as $order)
                        <tr>
                            <td style="font-weight:700;color:var(--primary);">#{{ $order->id }}</td>
                            <td>{{ $order->user->name ?? 'Khách vãng lai' }}</td>
                            <td><span class="badge badge-{{ $order->status }}">{{ $order->status_label ?? $order->status }}</span></td>
                            <td style="font-weight:600;">{{ number_format($order->final_price, 0, ',', '.') }}đ</td>
                            <td style="color:var(--text-muted);font-size:12px;">{{ $order->created_at->diffForHumans() }}</td>
                            <td>
                                <a href="{{ route('admin.orders.detail', $order->id) }}" class="btn-edit btn-sm">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" style="text-align:center;padding:30px;color:var(--text-muted);">Chưa có đơn hàng nào</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Right col --}}
    <div style="display:flex;flex-direction:column;gap:20px;">

        {{-- Quick stats --}}
        <div class="quick-stat-row">
            <div class="mini-stat">
                <div class="mini-stat-val" style="color:#d97706;">{{ $stats['pending_orders'] }}</div>
                <div class="mini-stat-lbl">Chờ xử lý</div>
            </div>
            <div class="mini-stat">
                <div class="mini-stat-val" style="color:#2563eb;">{{ $stats['total_staff'] }}</div>
                <div class="mini-stat-lbl">Nhân viên</div>
            </div>
            <div class="mini-stat">
                <div class="mini-stat-val" style="color:#16a34a;">{{ $stats['total_products'] }}</div>
                <div class="mini-stat-lbl">Sản phẩm</div>
            </div>
        </div>

        {{-- Top products --}}
        <div class="card" style="flex:1;">
            <div class="card-header">
                <div class="card-header-title">
                    <i class="fas fa-trophy" style="color:var(--admin-gold);"></i>
                    Top sản phẩm bán chạy
                </div>
            </div>
            <div class="card-body">
                @php $maxSold = $topProducts->max('total_sold') ?: 1; @endphp
                <div class="top-products-list">
                    @forelse($topProducts as $i => $product)
                    <div class="top-product-item">
                        <div class="top-product-rank {{ $i === 0 ? 'rank-1' : ($i === 1 ? 'rank-2' : ($i === 2 ? 'rank-3' : 'rank-other')) }}">
                            {{ $i + 1 }}
                        </div>
                        <div class="top-product-info">
                            <div class="top-product-name">{{ $product->name }}</div>
                            <div class="top-product-bar-wrap">
                                <div class="top-product-bar" style="width:{{ round($product->total_sold / $maxSold * 100) }}%;"></div>
                            </div>
                            <div class="top-product-sold">{{ number_format($product->total_sold) }} ly · {{ number_format($product->revenue, 0, ',', '.') }}đ</div>
                        </div>
                    </div>
                    @empty
                    <p style="color:var(--text-muted);text-align:center;">Chưa có dữ liệu</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Quick actions --}}
        <div class="card">
            <div class="card-header">
                <div class="card-header-title">
                    <i class="fas fa-bolt" style="color:var(--primary);"></i>
                    Thao tác nhanh
                </div>
            </div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:10px;">
                <a href="{{ route('admin.products.create') }}" class="btn-primary-admin" style="justify-content:center;">
                    <i class="fas fa-plus"></i> Thêm sản phẩm mới
                </a>
                <a href="{{ route('admin.users.create') }}" class="btn-outline-admin" style="justify-content:center;">
                    <i class="fas fa-user-plus"></i> Tạo tài khoản nhân viên
                </a>
                <a href="{{ route('admin.orders', ['status'=>'pending']) }}" class="btn-outline-admin" style="justify-content:center;">
                    <i class="fas fa-clock"></i> Đơn chờ xác nhận ({{ $stats['pending_orders'] }})
                </a>
                <a href="{{ route('admin.reports') }}" class="btn-outline-admin" style="justify-content:center;">
                    <i class="fas fa-chart-bar"></i> Xem báo cáo doanh thu
                </a>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
const chartData = @json($revenueChart);
const labels = chartData.map(d => d.label);
const values = chartData.map(d => d.total);

const ctx = document.getElementById('revenueChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels,
        datasets: [{
            label: 'Doanh thu (đ)',
            data: values,
            borderColor: '#d4813a',
            backgroundColor: 'rgba(212,129,58,0.08)',
            borderWidth: 2.5,
            pointBackgroundColor: '#d4813a',
            pointRadius: 4,
            pointHoverRadius: 6,
            tension: 0.4,
            fill: true,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: ctx => new Intl.NumberFormat('vi-VN').format(ctx.raw) + 'đ'
                }
            }
        },
        scales: {
            x: {
                grid: { display: false },
                ticks: { font: { family: 'Poppins', size: 11 }, color: '#9ca3af' }
            },
            y: {
                grid: { color: '#f3f4f8', drawBorder: false },
                ticks: {
                    font: { family: 'Poppins', size: 11 },
                    color: '#9ca3af',
                    callback: v => (v/1000).toFixed(0) + 'K'
                }
            }
        }
    }
});
</script>
@endsection
