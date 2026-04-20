@extends('staff.layout')

@section('title', 'Doanh thu ngày')
@section('page-title', 'Doanh thu ngày')

@section('styles')
<style>
    .daily-highlight {
        background: linear-gradient(135deg, #fff8f1, #ffffff);
        border: 1px solid #f3dfcb;
        border-radius: 12px;
        padding: 22px 24px;
        margin-bottom: 20px;
    }
    .daily-highlight-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 10px;
    }
    .daily-highlight-title {
        font-size: 20px;
        font-weight: 700;
        color: #1a1a2e;
    }
    .daily-highlight-date {
        font-size: 13px;
        color: #8a8fa8;
    }
    .daily-highlight-value {
        font-size: 36px;
        font-weight: 700;
        color: var(--primary);
        line-height: 1.1;
    }
    .daily-highlight-note {
        margin-top: 10px;
        font-size: 13px;
        color: #6f768b;
    }
    .daily-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 16px;
    }
    @media (max-width: 1100px) {
        .daily-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 640px) {
        .daily-grid { grid-template-columns: 1fr; }
    }
    .daily-box {
        background: #fff;
        border: 1px solid #eef0f4;
        border-radius: 12px;
        padding: 18px 20px;
    }
    .daily-box-label {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: #8a8fa8;
        margin-bottom: 8px;
        font-weight: 600;
    }
    .daily-box-value {
        font-size: 26px;
        font-weight: 700;
        color: #1a1a2e;
    }
    .daily-box-note {
        margin-top: 6px;
        font-size: 12px;
        color: #8a8fa8;
    }

    .daily-section-title {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 24px 0 14px;
        font-size: 16px;
        font-weight: 700;
        color: #1a1a2e;
    }
    .daily-section-title i {
        color: var(--primary);
    }

    .summary-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 20px;
    }
    @media (max-width: 1100px) {
        .summary-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 640px) {
        .summary-grid { grid-template-columns: 1fr; }
    }

    .summary-card {
        background: #fff;
        border: 1px solid #eef0f4;
        border-radius: 12px;
        padding: 18px 20px;
    }
    .summary-card-label {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: #8a8fa8;
        margin-bottom: 8px;
        font-weight: 600;
    }
    .summary-card-value {
        font-size: 24px;
        font-weight: 700;
        color: #1a1a2e;
        line-height: 1.15;
    }
    .summary-card-note {
        margin-top: 6px;
        font-size: 12px;
        color: #8a8fa8;
    }

    .history-card {
        background: #fff;
        border: 1px solid #eef0f4;
        border-radius: 12px;
        overflow: hidden;
    }
    .history-card-header {
        padding: 16px 20px;
        border-bottom: 1px solid #f0f2f5;
        font-size: 15px;
        font-weight: 600;
    }
    .history-table {
        width: 100%;
        border-collapse: collapse;
    }
    .history-table th {
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
    .history-table td {
        padding: 14px 16px;
        border-bottom: 1px solid #f3f4f8;
        font-size: 14px;
        vertical-align: middle;
    }
    .history-table tr:last-child td { border-bottom: none; }
    .history-date {
        font-weight: 700;
        color: #1a1a2e;
    }
    .history-sub {
        display: block;
        margin-top: 3px;
        font-size: 12px;
        color: #8a8fa8;
    }
    .history-amount {
        font-weight: 700;
        color: var(--primary);
        white-space: nowrap;
    }
    .empty-history {
        background: #fff;
        border: 1px solid #eef0f4;
        border-radius: 12px;
        padding: 60px 20px;
        text-align: center;
        color: #9aa1b1;
        margin-top: 20px;
    }
    .empty-history i {
        font-size: 42px;
        margin-bottom: 10px;
        color: #d8dce6;
        display: block;
    }
</style>
@endsection

@section('content')
@php
    $currentRevenueDate = $todayRevenue->revenue_date ?? now();
@endphp

<div class="daily-highlight">
    <div class="daily-highlight-head">
        <div class="daily-highlight-title">Doanh thu ngày hiện tại</div>
        <div class="daily-highlight-date">{{ $currentRevenueDate->format('d/m/Y') }}</div>
    </div>
    <div class="daily-highlight-value">{{ number_format($todayRevenue->total_revenue, 0, ',', '.') }}đ</div>
    <div class="daily-highlight-note">Hôm nay có {{ $todayOrderBreakdown['web_app_orders'] }} đơn khách mua trên web app và {{ $todayOrderBreakdown['staff_created_orders'] }} đơn do nhân viên tạo.</div>
</div>

<div class="daily-grid">
    <div class="daily-box">
        <div class="daily-box-label">Số đơn hôm nay</div>
        <div class="daily-box-value">{{ $todayOrderBreakdown['total_orders'] }}</div>
        <div class="daily-box-note">Tổng số đơn đã ghi nhận doanh thu hôm nay</div>
    </div>
    <div class="daily-box">
        <div class="daily-box-label">Đơn khách web app</div>
        <div class="daily-box-value">{{ $todayOrderBreakdown['web_app_orders'] }}</div>
        <div class="daily-box-note">Số đơn khách tự đặt và thanh toán trên web app</div>
    </div>
    <div class="daily-box">
        <div class="daily-box-label">Đơn nhân viên tạo</div>
        <div class="daily-box-value">{{ $todayOrderBreakdown['staff_created_orders'] }}</div>
        <div class="daily-box-note">Số đơn tại quán do nhân viên trực tiếp tạo</div>
    </div>
    <div class="daily-box">
        <div class="daily-box-label">Tiền mặt</div>
        <div class="daily-box-value">{{ number_format($todayRevenue->cash_revenue, 0, ',', '.') }}đ</div>
        <div class="daily-box-note">Các giao dịch thanh toán tiền mặt hôm nay</div>
    </div>
    <div class="daily-box">
        <div class="daily-box-label">Chuyển khoản</div>
        <div class="daily-box-value">{{ number_format($todayRevenue->transfer_revenue, 0, ',', '.') }}đ</div>
        <div class="daily-box-note">Các giao dịch thanh toán chuyển khoản hôm nay</div>
    </div>
</div>

<div class="daily-section-title">
    <i class="fas fa-chart-line"></i>
    <span>Tổng quan 30 ngày gần nhất</span>
</div>

<div class="summary-grid">
    <div class="summary-card">
        <div class="summary-card-label">Tổng doanh thu 30 ngày</div>
        <div class="summary-card-value">{{ number_format($summary['combined_revenue'], 0, ',', '.') }}đ</div>
        <div class="summary-card-note">Trong {{ $summary['range_label'] }}</div>
    </div>
    <div class="summary-card">
        <div class="summary-card-label">Nhân viên tạo đơn</div>
        <div class="summary-card-value">{{ number_format($summary['staff_created_revenue'], 0, ',', '.') }}đ</div>
        <div class="summary-card-note">Đơn tại quán do nhân viên tạo</div>
    </div>
    <div class="summary-card">
        <div class="summary-card-label">User tự mua</div>
        <div class="summary-card-value">{{ number_format($summary['customer_revenue'], 0, ',', '.') }}đ</div>
        <div class="summary-card-note">Đơn khách hàng tự thanh toán</div>
    </div>
    <div class="summary-card">
        <div class="summary-card-label">Tiền mặt</div>
        <div class="summary-card-value">{{ number_format($summary['cash_revenue'], 0, ',', '.') }}đ</div>
        <div class="summary-card-note">Các đơn đã thanh toán tiền mặt</div>
    </div>
    <div class="summary-card">
        <div class="summary-card-label">Chuyển khoản</div>
        <div class="summary-card-value">{{ number_format($summary['transfer_revenue'], 0, ',', '.') }}đ</div>
        <div class="summary-card-note">Các đơn đã thanh toán chuyển khoản</div>
    </div>
</div>

<div class="daily-section-title">
    <i class="fas fa-calendar-day"></i>
    <span>Lịch sử doanh thu theo ngày</span>
</div>

@if($dailyRevenue->isEmpty())
    <div class="empty-history">
        <i class="fas fa-chart-line"></i>
        <div style="font-size:16px;font-weight:600;">Chưa có doanh thu trong 30 ngày gần nhất</div>
        <div style="font-size:13px;margin-top:6px;">Dữ liệu sẽ xuất hiện khi có đơn hàng đã thanh toán được đồng bộ vào snapshot.</div>
    </div>
@else
    <div class="history-card">
        <div class="history-card-header">Bảng doanh thu ngày</div>
        <div style="overflow-x:auto;">
            <table class="history-table">
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
                                <span class="history-date">{{ $revenueDate->format('d/m/Y') }}</span>
                                <span class="history-sub">{{ $revenueDate->isoFormat('dddd') }}</span>
                            </td>
                            <td>{{ $day->total_orders }} đơn</td>
                            <td>{{ number_format($day->staff_created_revenue, 0, ',', '.') }}đ</td>
                            <td>{{ number_format($day->customer_revenue, 0, ',', '.') }}đ</td>
                            <td>{{ number_format($day->cash_revenue, 0, ',', '.') }}đ</td>
                            <td>{{ number_format($day->transfer_revenue, 0, ',', '.') }}đ</td>
                            <td><span class="history-amount">{{ number_format($day->total_revenue, 0, ',', '.') }}đ</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection