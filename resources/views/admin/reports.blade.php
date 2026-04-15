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
        <div class="page-header-sub">Phân tích doanh thu và hiệu suất bán hàng</div>
    </div>
    <div class="period-tabs">
        <a href="{{ route('admin.reports', ['period'=>'day']) }}"
           class="period-tab {{ $period === 'day' ? 'active' : '' }}">30 Ngày</a>
        <a href="{{ route('admin.reports', ['period'=>'month']) }}"
           class="period-tab {{ $period === 'month' ? 'active' : '' }}">12 Tháng</a>
        <a href="{{ route('admin.reports', ['period'=>'year']) }}"
           class="period-tab {{ $period === 'year' ? 'active' : '' }}">Theo Năm</a>
    </div>
</div>

{{-- Summary --}}
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

<div class="report-grid">

    {{-- Chart --}}
    <div class="card">
        <div class="card-header">
            <div class="card-header-title">
                <i class="fas fa-chart-line" style="color:var(--primary);"></i>
                Biểu đồ doanh thu —
                {{ $period === 'day' ? '30 ngày gần nhất' : ($period === 'month' ? '12 tháng gần nhất' : 'Theo năm') }}
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

@endsection

@section('scripts')
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
@endsection
