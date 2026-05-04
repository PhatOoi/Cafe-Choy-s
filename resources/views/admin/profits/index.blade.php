@extends('admin.layout')

@section('title', 'Lợi nhuận tháng')
@section('page-title', 'Lợi nhuận tháng')
@section('breadcrumb', 'Admin / Doanh thu / Lợi nhuận tháng')

@section('styles')
<style>
    .profit-grid {
        display: grid;
        grid-template-columns: minmax(360px, 1fr) minmax(320px, 1fr);
        gap: 18px;
    }

    .mini-title {
        font-size: 12px;
        font-weight: 600;
        color: var(--text-muted);
        margin-bottom: 8px;
    }

    .value-strong {
        font-size: 24px;
        font-weight: 800;
        line-height: 1.1;
    }

    .profit-positive { color: #15803d; }
    .profit-negative { color: #b91c1c; }

    .cost-list {
        display: grid;
        gap: 10px;
        margin-top: 10px;
    }

    .cost-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 10px 12px;
        background: #fff;
    }

    .cost-item-label {
        font-size: 13px;
        color: var(--text-dark);
        font-weight: 500;
    }

    .cost-item-value {
        font-size: 13px;
        font-weight: 700;
        color: #111827;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .form-grid .full {
        grid-column: 1 / -1;
    }

    .history-table-wrap {
        margin-top: 18px;
        border: 1px solid var(--border);
        border-radius: 12px;
        overflow: hidden;
    }

    .history-table {
        width: 100%;
        border-collapse: collapse;
    }

    .history-table th,
    .history-table td {
        padding: 10px 12px;
        border-bottom: 1px solid #f0f2f6;
        font-size: 13px;
    }

    .history-table th {
        background: #f8fafc;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: .4px;
        font-size: 11px;
        text-align: left;
    }

    .history-table tr:last-child td {
        border-bottom: none;
    }

    .profit-text-positive { color: #15803d; font-weight: 700; }
    .profit-text-negative { color: #b91c1c; font-weight: 700; }

    @media (max-width: 1100px) {
        .profit-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 640px) {
        .form-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('content')
@php
    $netClass = $netProfit >= 0 ? 'profit-positive' : 'profit-negative';
@endphp

<div class="page-header">
    <div>
        <div class="page-header-title">Bảng lợi nhuận theo tháng</div>
        <div class="page-header-sub">Lợi nhuận = Doanh thu tháng - (nguyên liệu + điện + nước + dịch vụ + khấu hao + mặt bằng + lương nhân viên)</div>
    </div>
</div>

<div class="profit-grid">
    <div class="card">
        <div class="card-header">
            <div class="card-header-title">
                <i class="fas fa-calculator" style="color:var(--primary);"></i>
                Cập nhật chi phí vận hành
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.profits') }}" style="margin-bottom:16px;">
                <div class="form-group" style="max-width:220px;margin-bottom:0;">
                    <label class="form-label">Chọn tháng</label>
                    <input type="month" name="month" value="{{ $monthInput }}" class="form-control" onchange="this.form.submit()">
                </div>
            </form>

            <form method="POST" action="{{ route('admin.profits.save') }}">
                @csrf
                <input type="hidden" name="month" value="{{ $monthInput }}">

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Tiền nguyên liệu</label>
                        <input type="text" class="form-control" value="{{ number_format($costs['ingredient_cost'], 0, ',', '.') }}đ" readonly>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Tiền điện</label>
                        <input type="number" min="0" step="1000" name="electricity_cost" class="form-control"
                               value="{{ old('electricity_cost', (int) $costs['electricity_cost']) }}" required>
                        @error('electricity_cost')<div class="form-text form-text-error">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Tiền nước</label>
                        <input type="number" min="0" step="1000" name="water_cost" class="form-control"
                               value="{{ old('water_cost', (int) $costs['water_cost']) }}" required>
                        @error('water_cost')<div class="form-text form-text-error">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Tiền dịch vụ</label>
                        <input type="number" min="0" step="1000" name="service_cost" class="form-control"
                               value="{{ old('service_cost', (int) $costs['service_cost']) }}" required>
                        @error('service_cost')<div class="form-text form-text-error">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Khấu hao máy móc</label>
                        <input type="number" min="0" step="1000" name="depreciation_cost" class="form-control"
                               value="{{ old('depreciation_cost', (int) $costs['depreciation_cost']) }}" required>
                        @error('depreciation_cost')<div class="form-text form-text-error">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Mặt bằng</label>
                        <input type="number" min="0" step="1000" name="rent_cost" class="form-control"
                               value="{{ old('rent_cost', (int) $costs['rent_cost']) }}" required>
                        @error('rent_cost')<div class="form-text form-text-error">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group full">
                        <button type="submit" class="btn-primary-admin">
                            <i class="fas fa-save"></i> Lưu chi phí tháng
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-header-title">
                <i class="fas fa-chart-line" style="color:var(--primary);"></i>
                Kết quả tháng {{ \Illuminate\Support\Carbon::createFromFormat('Y-m', $monthInput)->format('m/Y') }}
            </div>
        </div>
        <div class="card-body">
            <div class="mini-title">Tổng doanh thu tháng</div>
            <div class="value-strong" style="color:#0f766e;">{{ number_format($monthlyRevenue, 0, ',', '.') }}đ</div>

            <div class="cost-list">
                <div class="cost-item">
                    <div class="cost-item-label">Nguyên liệu</div>
                    <div class="cost-item-value">{{ number_format($costs['ingredient_cost'], 0, ',', '.') }}đ</div>
                </div>
                <div class="cost-item">
                    <div class="cost-item-label">Điện</div>
                    <div class="cost-item-value">{{ number_format($costs['electricity_cost'], 0, ',', '.') }}đ</div>
                </div>
                <div class="cost-item">
                    <div class="cost-item-label">Nước</div>
                    <div class="cost-item-value">{{ number_format($costs['water_cost'], 0, ',', '.') }}đ</div>
                </div>
                <div class="cost-item">
                    <div class="cost-item-label">Dịch vụ</div>
                    <div class="cost-item-value">{{ number_format($costs['service_cost'], 0, ',', '.') }}đ</div>
                </div>
                <div class="cost-item">
                    <div class="cost-item-label">Khấu hao máy móc</div>
                    <div class="cost-item-value">{{ number_format($costs['depreciation_cost'], 0, ',', '.') }}đ</div>
                </div>
                <div class="cost-item">
                    <div class="cost-item-label">Mặt bằng</div>
                    <div class="cost-item-value">{{ number_format($costs['rent_cost'], 0, ',', '.') }}đ</div>
                </div>
                <div class="cost-item" style="background:#fffbeb;border-color:#f3e8cc;">
                    <div class="cost-item-label">Lương nhân viên</div>
                    <div class="cost-item-value">{{ number_format($costs['salary_cost'], 0, ',', '.') }}đ</div>
                </div>
            </div>

            <hr style="margin:16px 0; border:none; border-top:1px solid var(--border);">

            <div class="mini-title">Tổng chi phí tháng</div>
            <div class="value-strong" style="color:#7c2d12;">{{ number_format($totalExpense, 0, ',', '.') }}đ</div>

            <div class="mini-title" style="margin-top:16px;">Tổng lợi nhuận tháng</div>
            <div class="value-strong {{ $netClass }}">{{ number_format($netProfit, 0, ',', '.') }}đ</div>

            <div class="mini-title" style="margin-top:20px;">Lịch sử 12 tháng gần nhất</div>
            <div class="history-table-wrap">
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>Tháng</th>
                            <th>Doanh thu</th>
                            <th>Tổng chi phí</th>
                            <th>Lợi nhuận</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($historyRows as $row)
                        <tr>
                            <td>
                                <a href="{{ route('admin.profits', ['month' => $row['month_key']]) }}" style="color:var(--primary);font-weight:600;text-decoration:none;">
                                    {{ $row['month_label'] }}
                                </a>
                            </td>
                            <td>{{ number_format($row['revenue'], 0, ',', '.') }}đ</td>
                            <td>{{ number_format($row['expense'], 0, ',', '.') }}đ</td>
                            <td class="{{ $row['profit'] >= 0 ? 'profit-text-positive' : 'profit-text-negative' }}">
                                {{ number_format($row['profit'], 0, ',', '.') }}đ
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mini-title" style="margin-top:20px;">Tổng kết lợi nhuận năm {{ $yearSummary['year'] }}</div>
            <div class="cost-list" style="margin-top:8px;">
                <div class="cost-item">
                    <div class="cost-item-label">Số tháng đã chốt</div>
                    <div class="cost-item-value">{{ $yearSummary['months_count'] }}/12</div>
                </div>
                <div class="cost-item">
                    <div class="cost-item-label">Tổng doanh thu năm</div>
                    <div class="cost-item-value">{{ number_format($yearSummary['total_revenue'], 0, ',', '.') }}đ</div>
                </div>
                <div class="cost-item">
                    <div class="cost-item-label">Tổng chi phí năm</div>
                    <div class="cost-item-value">{{ number_format($yearSummary['total_expense'], 0, ',', '.') }}đ</div>
                </div>
                <div class="cost-item" style="background:#f8fafc;">
                    <div class="cost-item-label">Lợi nhuận ròng năm</div>
                    <div class="cost-item-value {{ $yearSummary['net_profit'] >= 0 ? 'profit-text-positive' : 'profit-text-negative' }}">
                        {{ number_format($yearSummary['net_profit'], 0, ',', '.') }}đ
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
