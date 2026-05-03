@extends('admin.layout')

@section('title', 'Báo cáo & Thống kê')
@section('page-title', 'Báo cáo')
@section('breadcrumb', 'Admin / Báo cáo')

@section('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<style>
    .period-tabs { display:flex; gap:6px; }
    .period-tab {
        padding:7px 18px;
        border-radius:10px;
        font-size:13px;
        font-weight:600;
        text-decoration:none;
        transition:all .16s;
    }
    .period-tab.active  { background:var(--primary); color:#fff; }
    .period-tab:not(.active) { background:#fff; color:var(--text-muted); border:1px solid var(--border); }
    .period-tab:hover:not(.active) { border-color:var(--primary); color:var(--primary); text-decoration:none; }
    .chart-wrapper { position:relative; height:280px; }
    .daily-highlight {
        background: linear-gradient(135deg, #fff8f1, #ffffff);
        border: 1px solid #f3dfcb;
        border-radius: 12px;
        padding: 22px 24px;
        margin-bottom: 20px;
    }
    .daily-highlight-head { display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:10px; }
    .daily-highlight-title { font-size:20px; font-weight:700; color:#1a1a2e; }
    .daily-highlight-date { font-size:13px; color:#8a8fa8; }
    .daily-highlight-value { font-size:36px; font-weight:700; color:var(--primary); line-height:1.1; }
    .daily-highlight-note { margin-top:10px; font-size:13px; color:#6f768b; }
    .daily-grid {
        display:grid;
        grid-template-columns: repeat(5, minmax(0,1fr));
        gap:16px;
    }
    @media (max-width:1100px) { .daily-grid { grid-template-columns:repeat(2,minmax(0,1fr)); } }
    @media (max-width:640px)  { .daily-grid { grid-template-columns:1fr; } }
    .daily-box { background:#fff; border:1px solid #eef0f4; border-radius:12px; padding:18px 20px; }
    .daily-box-label { font-size:12px; text-transform:uppercase; letter-spacing:.06em; color:#8a8fa8; margin-bottom:8px; font-weight:600; }
    .daily-box-value { font-size:26px; font-weight:700; color:#1a1a2e; }
    .daily-box-note { margin-top:6px; font-size:12px; color:#8a8fa8; }
    .report-grid {
        display:grid;
        grid-template-columns:1fr 360px;
        gap:20px;
    }
    .top-row { display:flex; align-items:center; gap:10px; padding:12px 0; border-bottom:1px solid var(--border); }
    .top-row:last-child { border-bottom:none; padding-bottom:0; }
    .top-row:first-child { padding-top:0; }
    .rank-circle {
        width:32px; height:32px;
        border-radius:50%;
        display:flex; align-items:center; justify-content:center;
        font-size:13px; font-weight:800;
        flex-shrink:0;
    }
    .r1 { background:linear-gradient(135deg,#ffd700,#f59e0b); color:#fff; }
    .r2 { background:linear-gradient(135deg,#d1d5db,#9ca3af); color:#fff; }
    .r3 { background:linear-gradient(135deg,#c97c3a,#92400e); color:#fff; }
    .rn { background:#f3f4f8; color:#6b7280; }
    .progress-bar-wrap { height:6px; background:#f0f0f0; border-radius:6px; margin-top:5px; }
    .progress-bar { height:6px; border-radius:6px; background:linear-gradient(90deg,var(--primary),var(--admin-gold)); }

    @media (max-width:992px) { .report-grid { grid-template-columns:1fr; } }
</style>
@endsection

@section('content')

<div class="page-header">
    <div>
        <div class="page-header-title">Báo cáo & Thống kê</div>
    </div>
    <div class="period-tabs">
        <a href="{{ route('admin.reports', ['period'=>'day']) }}"
           class="period-tab {{ $period === 'day' ? 'active' : '' }}">Theo ngày</a>
        <a href="{{ route('admin.reports', ['period'=>'month']) }}"
           class="period-tab {{ $period === 'month' ? 'active' : '' }}">12 Tháng</a>
        <a href="{{ route('admin.reports', ['period'=>'year']) }}"
           class="period-tab {{ $period === 'year' ? 'active' : '' }}">Theo Năm</a>
    </div>
</div>

{{-- Summary --}}
@if($period === 'day')
{{-- Daily revenue layout giống staff --}}
<div class="daily-highlight">
    <div class="daily-highlight-head">
        <div class="daily-highlight-title">Doanh thu ngày hiện tại</div>
        <div class="daily-highlight-date">{{ now()->format('d/m/Y') }}</div>
    </div>
    <div class="daily-highlight-value">{{ number_format($todayRevenue->total_revenue, 0, ',', '.') }}đ</div>
    <div class="daily-highlight-note">Hôm nay quán ghi nhận {{ $todayOrderBreakdown['web_app_orders'] }} đơn khách đặt qua web và {{ $todayOrderBreakdown['staff_created_orders'] }} đơn do nhân viên tạo tại quầy.</div>
</div>
<div class="daily-grid">
    <div class="daily-box">
        <div class="daily-box-label">Số đơn hôm nay</div>
        <div class="daily-box-value">{{ $todayOrderBreakdown['total_orders'] }}</div>
        <div class="daily-box-note">Tổng số đơn đã ghi nhận doanh thu của quán hôm nay</div>
    </div>
    <div class="daily-box">
        <div class="daily-box-label">Đơn khách web app</div>
        <div class="daily-box-value">{{ $todayOrderBreakdown['web_app_orders'] }}</div>
        <div class="daily-box-note">Số đơn khách tự đặt trên web app hôm nay</div>
    </div>
    <div class="daily-box">
        <div class="daily-box-label">Đơn nhân viên tạo</div>
        <div class="daily-box-value">{{ $todayOrderBreakdown['staff_created_orders'] }}</div>
        <div class="daily-box-note">Số đơn tại quầy do nhân viên trực tiếp tạo hôm nay</div>
    </div>
    <div class="daily-box">
        <div class="daily-box-label">Tiền mặt</div>
        <div class="daily-box-value">{{ number_format($todayRevenue->cash_revenue, 0, ',', '.') }}đ</div>
        <div class="daily-box-note">Tổng giao dịch tiền mặt của quán hôm nay</div>
    </div>
    <div class="daily-box">
        <div class="daily-box-label">Chuyển khoản</div>
        <div class="daily-box-value">{{ number_format($todayRevenue->transfer_revenue, 0, ',', '.') }}đ</div>
        <div class="daily-box-note">Tổng giao dịch chuyển khoản của quán hôm nay</div>
    </div>
</div>
@else
{{-- Summary cho month/year --}}
@php
$totalRevenue = $revenueData->sum('total');
$maxRevenue   = $revenueData->max('total') ?: 1;
$avgRevenue   = $revenueData->count() ? $totalRevenue / $revenueData->count() : 0;
@endphp
<div class="stat-grid" style="margin-bottom:24px;">
    <div class="stat-card">
        <div class="stat-icon si-gold"><i class="fas fa-coins"></i></div>
        <div>
            <div class="stat-value">{{ number_format($totalRevenue / 1000, 0, ',', '.') }}K</div>
            <div class="stat-label">Tổng doanh thu (đ)</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon si-orange"><i class="fas fa-chart-bar"></i></div>
        <div>
            <div class="stat-value">{{ number_format($maxRevenue / 1000, 0, ',', '.') }}K</div>
            <div class="stat-label">Doanh thu cao nhất</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon si-blue"><i class="fas fa-calculator"></i></div>
        <div>
            <div class="stat-value">{{ number_format($avgRevenue / 1000, 0, ',', '.') }}K</div>
            <div class="stat-label">Trung bình mỗi kỳ</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon si-green"><i class="fas fa-trophy"></i></div>
        <div>
            <div class="stat-value">{{ number_format($topProducts->sum('total_sold')) }}</div>
            <div class="stat-label">Tổng sản phẩm bán ra</div>
        </div>
    </div>
</div>
@endif

@if($period !== 'day')
<div class="report-grid">

    {{-- Chart --}}
    <div class="card">
        <div class="card-header">
            <div class="card-header-title">
                <i class="fas fa-chart-line" style="color:var(--primary);"></i>
                Biểu đồ doanh thu —
                {{ $period === 'day' ? 'Hôm nay' : ($period === 'month' ? '12 tháng gần nhất' : 'Theo năm') }}
            </div>
        </div>
        <div class="card-body">
            <div class="chart-wrapper">
                <canvas id="revenueChart"></canvas>
            </div>

            {{-- Data table --}}
            <div style="margin-top:20px;max-height:240px;overflow-y:auto;">
                <table class="admin-table" style="font-size:12.5px;">
                    <thead>
                        <tr>
                            <th>Kỳ</th>
                            <th style="text-align:right;">Doanh thu</th>
                            <th>Tỷ lệ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($revenueData as $row)
                        <tr>
                            <td>{{ $row->label }}</td>
                            <td style="text-align:right;font-weight:600;color:var(--primary);">
                                {{ number_format($row->total, 0, ',', '.') }}đ
                            </td>
                            <td style="min-width:100px;">
                                <div class="progress-bar-wrap">
                                    <div class="progress-bar" style="width:{{ round($row->total / $maxRevenue * 100) }}%;"></div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" style="text-align:center;color:var(--text-muted);">Chưa có dữ liệu</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Top products --}}
    <div class="card">
        <div class="card-header">
            <div class="card-header-title">
                <i class="fas fa-trophy" style="color:var(--admin-gold);"></i>
                Top 10 sản phẩm bán chạy
            </div>
        </div>
        <div class="card-body">
            @php $maxSold = $topProducts->max('total_sold') ?: 1; @endphp
            @forelse($topProducts as $i => $p)
            <div class="top-row">
                <div class="rank-circle {{ $i===0?'r1':($i===1?'r2':($i===2?'r3':'rn')) }}">
                    {{ $i === 0 ? '🥇' : ($i === 1 ? '🥈' : ($i === 2 ? '🥉' : $i+1)) }}
                </div>
                <div style="flex:1;min-width:0;">
                    <div style="font-weight:600;font-size:13px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        {{ $p->name }}
                    </div>
                    <div class="progress-bar-wrap">
                        <div class="progress-bar" style="width:{{ round($p->total_sold / $maxSold * 100) }}%;"></div>
                    </div>
                    <div style="font-size:11.5px;color:var(--text-muted);margin-top:3px;">
                        {{ number_format($p->total_sold) }} ly · {{ number_format($p->revenue, 0, ',', '.') }}đ
                    </div>
                </div>
            </div>
            @empty
            <p style="color:var(--text-muted);text-align:center;">Chưa có dữ liệu</p>
            @endforelse
        </div>
    </div>
</div>
@endif

@endsection

@section('scripts')
@if($period !== 'day')
<script>
const raw = @json($revenueData);
const labels = raw.map(d => d.label);
const values = raw.map(d => d.total);

const ctx = document.getElementById('revenueChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels,
        datasets: [{
            label: 'Doanh thu',
            data: values,
            backgroundColor: values.map((v, i) =>
                i === values.indexOf(Math.max(...values))
                    ? 'rgba(212,129,58,0.9)'
                    : 'rgba(212,129,58,0.35)'
            ),
            borderColor: '#d4813a',
            borderWidth: 1.5,
            borderRadius: 6,
            borderSkipped: false,
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
                ticks: { font: { family: 'Poppins', size: 11 }, color: '#9ca3af', maxRotation: 45 }
            },
            y: {
                grid: { color: '#f3f4f8' },
                ticks: {
                    font: { family: 'Poppins', size: 11 },
                    color: '#9ca3af',
                    callback: v => (v >= 1000000 ? (v/1000000).toFixed(1) + 'M' : (v/1000).toFixed(0) + 'K')
                }
            }
        }
    }
});
</script>
@endif
@endsection
