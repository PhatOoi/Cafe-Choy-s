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
@endsection