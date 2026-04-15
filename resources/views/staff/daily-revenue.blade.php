@extends('staff.layout')

@section('title', 'Doanh thu tháng')
@section('page-title', 'Doanh thu tháng')

@section('styles')
<style>
    .revenue-stats {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 20px;
    }
    @media (max-width: 1100px) {
        .revenue-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 640px) {
        .revenue-stats { grid-template-columns: 1fr; }
    }

    .revenue-stat {
        background: #fff;
        border: 1px solid #eef0f4;
        border-radius: 12px;
        padding: 18px 20px;
    }
    .revenue-stat-label {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: #8a8fa8;
        margin-bottom: 8px;
        font-weight: 600;
    }
    .revenue-stat-value {
        font-size: 24px;
        font-weight: 700;
        color: #1a1a2e;
        line-height: 1.15;
    }
    .revenue-stat-note {
        margin-top: 6px;
        font-size: 12px;
        color: #8a8fa8;
    }

    .revenue-card {
        background: #fff;
        border: 1px solid #eef0f4;
        border-radius: 12px;
        overflow: hidden;
    }
    .revenue-table {
        width: 100%;
        border-collapse: collapse;
    }
    .revenue-table th {
        text-align: left;
        font-size: 11px;
        color: #8a8fa8;
        letter-spacing: .05em;
        text-transform: uppercase;
        background: #f8f9fc;
        padding: 12px 16px;
        border-bottom: 1px solid #eef0f4;
        white-space: nowrap;
    }
    .revenue-table td {
        padding: 14px 16px;
        border-bottom: 1px solid #f3f4f8;
        font-size: 14px;
        vertical-align: middle;
    }
    .revenue-table tr:last-child td { border-bottom: none; }
    .revenue-date {
        font-weight: 700;
        color: #1a1a2e;
    }
    .revenue-sub {
        display: block;
        margin-top: 3px;
        font-size: 12px;
        color: #8a8fa8;
    }
    .revenue-amount {
        font-weight: 700;
        color: var(--primary);
        white-space: nowrap;
    }
    .empty-revenue {
        background: #fff;
        border: 1px solid #eef0f4;
        border-radius: 12px;
        padding: 60px 20px;
        text-align: center;
        color: #9aa1b1;
    }
    .empty-revenue i {
        font-size: 42px;
        margin-bottom: 10px;
        color: #d8dce6;
        display: block;
    }
    .current-day-revenue {
        background: linear-gradient(135deg, #fff8f1, #ffffff);
        border: 1px solid #f3dfcb;
        border-radius: 12px;
        padding: 18px 20px;
        margin-bottom: 20px;
    }
    .current-day-revenue-title {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 8px;
    }
    .current-day-revenue-title strong {
        font-size: 18px;
        color: #1a1a2e;
    }
    .current-day-revenue-date {
        font-size: 13px;
        color: #8a8fa8;
    }
    .current-day-revenue-value {
        font-size: 32px;
        font-weight: 700;
        color: var(--primary);
        line-height: 1.15;
    }
    .current-day-revenue-meta {
        margin-top: 10px;
        font-size: 13px;
        color: #6f768b;
    }
    .current-month-revenue {
        background: linear-gradient(135deg, #f7fbf5, #ffffff);
        border: 1px solid #dfead7;
        border-radius: 12px;
        padding: 18px 20px;
        margin-bottom: 20px;
    }
    .current-month-revenue-title {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 8px;
    }
    .current-month-revenue-title strong {
        font-size: 18px;
        color: #1a1a2e;
    }
    .current-month-revenue-date {
        font-size: 13px;
        color: #8a8fa8;
    }
    .current-month-revenue-value {
        font-size: 32px;
        font-weight: 700;
        color: #2f6b2f;
        line-height: 1.15;
    }
    .current-month-revenue-meta {
        margin-top: 10px;
        font-size: 13px;
        color: #6f768b;
    }
</style>
@endsection

@section('content')
@php
    $currentRevenueDate = $todayRevenue->revenue_date ?? now();
@endphp

<div class="current-day-revenue">
    <div class="current-day-revenue-title">
        <strong>Doanh thu ngày hiện tại</strong>
        <span class="current-day-revenue-date">{{ $currentRevenueDate->format('d/m/Y') }}</span>
    </div>
    <div class="current-day-revenue-value">{{ number_format($todayRevenue->total_revenue, 0, ',', '.') }}đ</div>
    <div class="current-day-revenue-meta">
        {{ $todayRevenue->total_orders }} đơn đã thanh toán hôm nay. Tiền mặt {{ number_format($todayRevenue->cash_revenue, 0, ',', '.') }}đ, chuyển khoản {{ number_format($todayRevenue->transfer_revenue, 0, ',', '.') }}đ.
    </div>
</div>

<div class="current-month-revenue">
    <div class="current-month-revenue-title">
        <strong>Doanh thu tháng hiện tại</strong>
        <span class="current-month-revenue-date">Tháng {{ $currentMonthRevenue['month_label'] }}</span>
    </div>
    <div class="current-month-revenue-value">{{ number_format($currentMonthRevenue['total_revenue'], 0, ',', '.') }}đ</div>
    <div class="current-month-revenue-meta">
        {{ $currentMonthRevenue['total_orders'] }} đơn đã thanh toán trong tháng này. Tiền mặt {{ number_format($currentMonthRevenue['cash_revenue'], 0, ',', '.') }}đ, chuyển khoản {{ number_format($currentMonthRevenue['transfer_revenue'], 0, ',', '.') }}đ.
    </div>
</div>

<div class="revenue-stats">
    <div class="revenue-stat">
        <div class="revenue-stat-label">Tổng doanh thu 30 ngày</div>
        <div class="revenue-stat-value">{{ number_format($summary['combined_revenue'], 0, ',', '.') }}đ</div>
        <div class="revenue-stat-note">Trong {{ $summary['range_label'] }}</div>
    </div>
    <div class="revenue-stat">
        <div class="revenue-stat-label">Nhân viên tạo đơn</div>
        <div class="revenue-stat-value">{{ number_format($summary['staff_created_revenue'], 0, ',', '.') }}đ</div>
        <div class="revenue-stat-note">Đơn tại quán do nhân viên tạo</div>
    </div>
    <div class="revenue-stat">
        <div class="revenue-stat-label">User tự mua</div>
        <div class="revenue-stat-value">{{ number_format($summary['customer_revenue'], 0, ',', '.') }}đ</div>
        <div class="revenue-stat-note">Đơn khách hàng tự thanh toán</div>
    </div>
    <div class="revenue-stat">
        <div class="revenue-stat-label">Tiền mặt</div>
        <div class="revenue-stat-value">{{ number_format($summary['cash_revenue'], 0, ',', '.') }}đ</div>
        <div class="revenue-stat-note">Các đơn đã thanh toán tiền mặt</div>
    </div>
    <div class="revenue-stat">
        <div class="revenue-stat-label">Chuyển khoản</div>
        <div class="revenue-stat-value">{{ number_format($summary['transfer_revenue'], 0, ',', '.') }}đ</div>
        <div class="revenue-stat-note">Các đơn đã thanh toán chuyển khoản</div>
    </div>
</div>

@if($dailyRevenue->isEmpty())
    <div class="empty-revenue">
        <i class="fas fa-chart-line"></i>
        <div style="font-size:16px;font-weight:600;">Chưa có doanh thu trong 1 tháng gần nhất</div>
        <div style="font-size:13px;margin-top:6px;">Sau 1 tháng, lịch sử doanh thu cũ sẽ tự không còn hiển thị ở đây.</div>
    </div>
@else
    <div class="revenue-card">
        <div style="padding:16px 20px;border-bottom:1px solid #f0f2f5;font-size:15px;font-weight:600;">
            <i class="fas fa-calendar-day" style="color:var(--primary);margin-right:8px;"></i>Doanh thu ngày
        </div>
        <div style="overflow-x:auto;">
            <table class="revenue-table">
                <thead>
                    <tr>
                        <th>Ngày</th>
                        <th>Số đơn</th>
                        <th>Nhân viên tạo</th>
                        <th>User tự mua</th>
                        <th>Tiền mặt</th>
                        <th>Chuyển khoản</th>
                        <th>Tổng doanh thu</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dailyRevenue as $day)
                        @php
                            $revenueDate = $day->revenue_date ?? now();
                        @endphp
                        <tr>
                            <td>
                                <span class="revenue-date">{{ $revenueDate->format('d/m/Y') }}</span>
                                <span class="revenue-sub">{{ $revenueDate->isoFormat('dddd') }}</span>
                            </td>
                            <td>{{ $day->total_orders }} đơn</td>
                            <td>{{ number_format($day->staff_created_revenue, 0, ',', '.') }}đ</td>
                            <td>{{ number_format($day->customer_revenue, 0, ',', '.') }}đ</td>
                            <td>{{ number_format($day->cash_revenue, 0, ',', '.') }}đ</td>
                            <td>{{ number_format($day->transfer_revenue, 0, ',', '.') }}đ</td>
                            <td><span class="revenue-amount">{{ number_format($day->total_revenue, 0, ',', '.') }}đ</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection