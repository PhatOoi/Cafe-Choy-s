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
                    Top 10 sản phẩm bán chạy
                </div>
                <span id="top-products-updated" style="font-size:11px;color:#9ca3af;"></span>
            </div>
            <div class="card-body">
                @php $maxSold = $topProducts->max('total_sold') ?: 1; @endphp
                <div class="top-products-list" id="top-products-list">
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

{{-- Widget lịch làm việc tuần này --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:20px;">

    {{-- Hôm nay --}}
    <div class="card">
        <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;gap:12px;">
            <div>
                <div class="card-header-title"><i class="fas fa-calendar-day" style="color:var(--primary);"></i> Ca làm hôm nay</div>
                <div style="font-size:12px;color:var(--text-muted);margin-top:2px;">{{ now()->format('d/m/Y') }}</div>
            </div>
            <a href="{{ route('admin.work-schedules.index') }}" style="font-size:12px;color:var(--primary);font-weight:600;text-decoration:none;">Xem toàn bộ →</a>
        </div>
        <div style="padding:14px 18px;">
            @if($todaySchedule->isEmpty())
                <div style="text-align:center;padding:18px;color:#94a3b8;font-size:13px;">Không có nhân viên nào được duyệt ca hôm nay.</div>
            @else
                <div style="display:grid;gap:8px;">
                    @foreach($todaySchedule as $entry)
                        <div style="display:flex;align-items:center;gap:10px;padding:9px 12px;border-radius:10px;background:#f8fafc;border:1px solid #eef1f6;">
                            <div style="width:32px;height:32px;border-radius:50%;background:#e2e8f0;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:13px;color:#475569;">
                                {{ mb_substr($entry->staff->name ?? '?', 0, 1) }}
                            </div>
                            <div style="flex:1;min-width:0;">
                                <div style="font-size:13px;font-weight:700;color:#172033;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $entry->staff->name ?? '—' }}</div>
                                <div style="font-size:11px;color:#64748b;">{{ substr($entry->start_time,0,5) }}–{{ substr($entry->end_time,0,5) }} · {{ $entry->employment_type === 'full_time' ? 'Full-time' : 'Part-time' }}</div>
                            </div>
                            <span style="font-size:10px;font-weight:700;padding:3px 8px;border-radius:999px;background:#dcfce7;color:#166534;flex-shrink:0;">Đã duyệt</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Tuần này --}}
    <div class="card">
        <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;gap:12px;">
            <div>
                <div class="card-header-title"><i class="fas fa-calendar-week" style="color:var(--primary);"></i> Lịch tuần này</div>
                <div style="font-size:12px;color:var(--text-muted);margin-top:2px;">{{ $thisWeekStart->format('d/m') }} – {{ $thisWeekEnd->format('d/m/Y') }}</div>
            </div>
            <a href="{{ route('admin.work-schedules.index') }}" style="font-size:12px;color:var(--primary);font-weight:600;text-decoration:none;">Chi tiết →</a>
        </div>
        <div style="padding:14px 18px;">
            @if($thisWeekSchedule->isEmpty())
                <div style="text-align:center;padding:18px;color:#94a3b8;font-size:13px;">Chưa có lịch được duyệt trong tuần này.</div>
            @else
                <div style="display:grid;gap:8px;max-height:280px;overflow-y:auto;">
                    @foreach($thisWeekSchedule as $dateKey => $entries)
                        @php $day = \Carbon\Carbon::parse($dateKey); @endphp
                        <div>
                            <div style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:5px;">
                                {{ ['CN','T2','T3','T4','T5','T6','T7'][$day->dayOfWeek] }} {{ $day->format('d/m') }}
                                @if($day->isToday())<span style="background:#fde68a;color:#92400e;padding:1px 6px;border-radius:6px;font-size:10px;margin-left:4px;">Hôm nay</span>@endif
                            </div>
                            @foreach($entries as $entry)
                                <div style="display:flex;align-items:center;gap:8px;padding:6px 10px;border-radius:8px;background:#f8fafc;border:1px solid #eef1f6;margin-bottom:4px;">
                                    <span style="font-size:12px;font-weight:600;color:#172033;">{{ $entry->staff->name ?? '—' }}</span>
                                    <span style="font-size:11px;color:#94a3b8;">{{ substr($entry->start_time,0,5) }}–{{ substr($entry->end_time,0,5) }}</span>
                                    <span style="margin-left:auto;font-size:10px;font-weight:700;padding:2px 7px;border-radius:999px;background:{{ $entry->employment_type === 'full_time' ? '#e0f2fe' : '#fef3c7' }};color:{{ $entry->employment_type === 'full_time' ? '#0369a1' : '#b45309' }};">{{ $entry->employment_type === 'full_time' ? 'FT' : 'PT' }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            @endif
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

// Auto-refresh top 10 sản phẩm bán chạy mỗi 60 giây.
const topProductsUrl = '{{ route('admin.dashboard.top-products') }}';

function renderTopProducts(products) {
    const list = document.getElementById('top-products-list');
    const label = document.getElementById('top-products-updated');
    if (!list) return;

    if (!products.length) {
        list.innerHTML = '<p style="color:#9ca3af;text-align:center;">Chưa có dữ liệu</p>';
        return;
    }

    const maxSold = Math.max(...products.map(p => p.total_sold));
    const rankClass = i => i === 0 ? 'rank-1' : (i === 1 ? 'rank-2' : (i === 2 ? 'rank-3' : 'rank-other'));

    list.innerHTML = products.map((p, i) => `
        <div class="top-product-item">
            <div class="top-product-rank ${rankClass(i)}">${i + 1}</div>
            <div class="top-product-info">
                <div class="top-product-name">${p.name}</div>
                <div class="top-product-bar-wrap">
                    <div class="top-product-bar" style="width:${Math.round(p.total_sold / maxSold * 100)}%;"></div>
                </div>
                <div class="top-product-sold">${Number(p.total_sold).toLocaleString('vi-VN')} ly · ${Number(p.revenue).toLocaleString('vi-VN')}đ</div>
            </div>
        </div>
    `).join('');

    const now = new Date();
    label.textContent = 'Cập nhật ' + now.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
}

function fetchTopProducts() {
    fetch(topProductsUrl)
        .then(r => r.json())
        .then(data => renderTopProducts(data))
        .catch(() => {});
}

setInterval(fetchTopProducts, 60000);
</script>
@endsection
