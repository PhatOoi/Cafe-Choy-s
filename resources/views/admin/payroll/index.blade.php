@extends('admin.layout')

@section('title', 'Bảng lương')
@section('page-title', 'Bảng lương nhân viên')
@section('breadcrumb', 'Admin / Bảng lương')

@section('content')
<style>
    .payroll-filter-bar {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: end;
        margin-bottom: 22px;
    }

    .payroll-filter-group {
        min-width: 200px;
    }

    .payroll-mini-label {
        display: block;
        margin-bottom: 6px;
        font-size: 12px;
        font-weight: 600;
        color: var(--text-muted);
    }

    .payroll-field {
        width: 100%;
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 10px 12px;
        font-size: 13px;
        background: #fff;
    }

    .payroll-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
    }

    .type-full { background: #e0f2fe; color: #0369a1; }
    .type-part { background: #fef3c7; color: #b45309; }

    .payroll-empty {
        padding: 28px 16px;
        text-align: center;
        color: var(--text-muted);
        font-size: 13px;
    }

    .payroll-table-note {
        font-size: 12px;
        color: var(--text-muted);
        margin-top: 4px;
    }

    .staff-cell-name {
        font-weight: 700;
        color: var(--text-dark);
    }

    .staff-cell-sub {
        font-size: 12px;
        color: var(--text-muted);
        margin-top: 3px;
    }
</style>

<div class="page-header">
    <div>
        <div class="page-header-title">Bảng lương nhân viên</div>
        <div class="page-header-sub">Cập nhật hàng ngày theo ca đã duyệt. Cuối tháng bấm Chốt lương để gửi email cho từng nhân viên.</div>
    </div>
    @if($canFinalize)
    <form method="POST" action="{{ route('admin.payroll.finalize') }}" onsubmit="return confirm('Chốt lương và gửi email bảng lương tháng {{ $monthStart->format('m/Y') }} đến toàn bộ nhân viên?')">
        @csrf
        <input type="hidden" name="month" value="{{ $monthInput }}">
        <button type="submit" class="btn-primary-admin">
            <i class="fas fa-paper-plane"></i> Chốt lương &amp; Gửi email
        </button>
    </form>
    @endif
</div>

@if(session('success'))
<div class="alert alert-success" id="payroll-success-alert">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
@endif

<form method="GET" action="{{ route('admin.payroll') }}" class="payroll-filter-bar">
    <div class="payroll-filter-group">
        <label class="payroll-mini-label">Tháng</label>
        <input type="month" name="month" value="{{ $monthInput }}" class="payroll-field">
    </div>

    <div class="payroll-filter-group">
        <label class="payroll-mini-label">Loại nhân viên</label>
        <select name="employment_type" class="payroll-field">
            <option value="">Tất cả</option>
            <option value="full_time" {{ request('employment_type') === 'full_time' ? 'selected' : '' }}>Full-time</option>
            <option value="part_time" {{ request('employment_type') === 'part_time' ? 'selected' : '' }}>Part-time</option>
        </select>
    </div>

    <button type="submit" class="btn-primary-admin"><i class="fas fa-filter"></i> Lọc dữ liệu</button>
</form>

<div class="stat-grid">
        <div class="stat-card">
            <div class="stat-icon si-gold"><i class="fas fa-check-circle"></i></div>
            <div>
                <div class="stat-value">{{ $payrollStats['completed_shift_count'] }}</div>
                <div class="stat-label">Ca đã hoàn thành</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon si-green"><i class="fas fa-wallet"></i></div>
            <div>
                <div class="stat-value">{{ number_format($payrollStats['gross_salary_total'], 0, ',', '.') }}đ</div>
                <div class="stat-label">Tổng lương tạm tính</div>
            </div>
        </div>
    </div>

    @php
        $fullTimeRows = $payrollRows->filter(fn($r) => $r['employment_type'] === 'full_time')->values();
        $partTimeRows = $payrollRows->filter(fn($r) => $r['employment_type'] === 'part_time')->values();
        $fullTimeTotal = $fullTimeRows->sum('gross_salary');
        $partTimeTotal = $partTimeRows->sum('gross_salary');
    @endphp

    {{-- Full-time --}}
    <div class="card" style="margin-bottom:20px;">
        <div class="card-header">
            <div>
                <div class="card-header-title">
                    <i class="fas fa-user-tie" style="color:#0369a1;"></i>
                    Bảng lương <span class="payroll-status type-full" style="font-size:13px;">Full-time</span>
                </div>
                <p class="payroll-table-note">Cộng dồn số ca và giờ công của nhân viên full-time trong tháng đang lọc.</p>
            </div>
        </div>
        <div class="card-body" style="padding:0;">
            @if($fullTimeRows->isNotEmpty())
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Nhân viên</th>
                            <th>Số ca</th>
                            <th>Giờ công</th>
                            <th>Giờ tăng ca</th>
                            <th>Đơn giá/giờ</th>
                            <th>Đơn giá tăng ca</th>
                            <th>Tạm tính</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($fullTimeRows as $row)
                            <tr>
                                <td>
                                    <div class="staff-cell-name">{{ $row['staff']->name }}</div>
                                    <div class="staff-cell-sub">{{ $row['staff']->email }}</div>
                                </td>
                                <td>{{ $row['shift_count'] }}</td>
                                <td>{{ $row['total_hours'] == floor($row['total_hours']) ? number_format($row['total_hours'], 0, ',', '.') : number_format($row['total_hours'], 2, ',', '.') }} giờ</td>
                                <td>{{ $row['overtime_hours'] == floor($row['overtime_hours']) ? number_format($row['overtime_hours'], 0, ',', '.') : number_format($row['overtime_hours'], 2, ',', '.') }} giờ</td>
                                <td>{{ number_format($row['hourly_rate'], 0, ',', '.') }}đ</td>
                                <td>{{ number_format($row['overtime_rate'], 0, ',', '.') }}đ</td>
                                <td><strong>{{ number_format($row['gross_salary'], 0, ',', '.') }}đ</strong></td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="background:#e0f2fe; font-weight:700;">
                            <td colspan="6" style="text-align:right; padding-right:16px;">Tổng lương full-time:</td>
                            <td>{{ number_format($fullTimeTotal, 0, ',', '.') }}đ</td>
                        </tr>
                    </tfoot>
                </table>
            @else
                <div class="payroll-empty">Chưa có dữ liệu lương full-time trong tháng này.</div>
            @endif
        </div>
    </div>

    {{-- Part-time --}}
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-header-title">
                    <i class="fas fa-user-clock" style="color:#b45309;"></i>
                    Bảng lương <span class="payroll-status type-part" style="font-size:13px;">Part-time</span>
                </div>
                <p class="payroll-table-note">Cộng dồn số ca và giờ công của nhân viên part-time trong tháng đang lọc.</p>
            </div>
        </div>
        <div class="card-body" style="padding:0;">
            @if($partTimeRows->isNotEmpty())
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Nhân viên</th>
                            <th>Số ca</th>
                            <th>Giờ công</th>
                            <th>Giờ tăng ca</th>
                            <th>Đơn giá/giờ</th>
                            <th>Đơn giá tăng ca</th>
                            <th>Tạm tính</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($partTimeRows as $row)
                            <tr>
                                <td>
                                    <div class="staff-cell-name">{{ $row['staff']->name }}</div>
                                    <div class="staff-cell-sub">{{ $row['staff']->email }}</div>
                                </td>
                                <td>{{ $row['shift_count'] }}</td>
                                <td>{{ $row['total_hours'] == floor($row['total_hours']) ? number_format($row['total_hours'], 0, ',', '.') : number_format($row['total_hours'], 2, ',', '.') }} giờ</td>
                                <td>{{ $row['overtime_hours'] == floor($row['overtime_hours']) ? number_format($row['overtime_hours'], 0, ',', '.') : number_format($row['overtime_hours'], 2, ',', '.') }} giờ</td>
                                <td>{{ number_format($row['hourly_rate'], 0, ',', '.') }}đ</td>
                                <td>{{ number_format($row['overtime_rate'], 0, ',', '.') }}đ</td>
                                <td><strong>{{ number_format($row['gross_salary'], 0, ',', '.') }}đ</strong></td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="background:#fef3c7; font-weight:700;">
                            <td colspan="6" style="text-align:right; padding-right:16px;">Tổng lương part-time:</td>
                            <td>{{ number_format($partTimeTotal, 0, ',', '.') }}đ</td>
                        </tr>
                    </tfoot>
                </table>
            @else
                <div class="payroll-empty">Chưa có dữ liệu lương part-time trong tháng này.</div>
            @endif
        </div>
    </div>
@endsection
