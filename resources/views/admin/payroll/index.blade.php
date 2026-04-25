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
        min-width: 180px;
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

    .status-pending { background: #fff7ed; color: #c2410c; }
    .status-approved { background: #ecfeff; color: #0f766e; }
    .status-closed { background: #eef2ff; color: #4338ca; }
    .type-full { background: #e0f2fe; color: #0369a1; }
    .type-part { background: #fef3c7; color: #b45309; }

    .payroll-subgrid {
        display: grid;
        grid-template-columns: 1.2fr 1fr;
        gap: 20px;
        margin-top: 22px;
    }

    @media (max-width: 1180px) {
        .payroll-subgrid {
            grid-template-columns: 1fr;
        }
    }

    .payroll-table-note {
        font-size: 12px;
        color: var(--text-muted);
        margin-top: 4px;
    }

    .payroll-inline-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .btn-soft-admin {
        border: 1px solid var(--border);
        background: #fff;
        color: var(--text-dark);
        padding: 8px 12px;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        font-family: 'Poppins', sans-serif;
    }

    .btn-soft-admin.approve {
        background: #ecfdf5;
        border-color: #bbf7d0;
        color: #166534;
    }

    .btn-soft-admin.close {
        background: #eef2ff;
        border-color: #c7d2fe;
        color: #4338ca;
    }

    .payroll-empty {
        padding: 28px 16px;
        text-align: center;
        color: var(--text-muted);
        font-size: 13px;
    }

    .week-board-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 22px;
    }

    @media (max-width: 1200px) {
        .week-board-grid {
            grid-template-columns: 1fr;
        }
    }

    .week-calendar-wrap {
        padding: 18px 20px 20px;
        overflow-x: auto;
    }

    .week-calendar {
        min-width: 760px;
        width: 100%;
        border-collapse: collapse;
    }

    .week-calendar th,
    .week-calendar td {
        border: 1px solid var(--border);
        padding: 9px;
        text-align: center;
        vertical-align: middle;
    }

    .week-calendar th {
        background: #f8fafc;
        color: #475569;
        font-size: 11px;
        font-weight: 700;
    }

    .slot-title {
        background: #fafbff;
        color: var(--text-dark);
        font-weight: 700;
        font-size: 12px;
        min-width: 100px;
    }

    .day-label {
        line-height: 1.35;
    }

    .day-title {
        font-size: 11px;
        font-weight: 700;
        color: #334155;
    }

    .day-date {
        font-size: 10px;
        color: #94a3b8;
    }

    .slot-stack {
        display: grid;
        gap: 6px;
    }

    .slot-chip {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        border-radius: 10px;
        padding: 7px 9px;
        font-size: 11px;
        font-weight: 700;
        line-height: 1.35;
    }

    .slot-chip.pending {
        background: #fff7ed;
        color: #c2410c;
    }

    .slot-chip.approved {
        background: #ecfeff;
        color: #0f766e;
    }

    .slot-chip.closed {
        background: #eef2ff;
        color: #4338ca;
    }

    .slot-chip.empty {
        background: #f8fafc;
        color: #94a3b8;
        font-weight: 600;
    }

    .board-action-wrap {
        display: flex;
        justify-content: flex-end;
        margin-top: 14px;
    }

    .btn-lock-board {
        border: 1px solid #c7d2fe;
        background: #eef2ff;
        color: #3730a3;
        padding: 10px 16px;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 700;
        font-family: 'Poppins', sans-serif;
        cursor: pointer;
        transition: all .15s ease;
    }

    .btn-lock-board:hover {
        background: #e0e7ff;
    }

    .btn-lock-board[disabled] {
        opacity: .6;
        cursor: not-allowed;
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
        <div class="page-header-sub">Theo dõi ca chờ duyệt, ca đã duyệt và ca đã đóng để chốt lương.</div>
    </div>
</div>

<div class="card" style="margin-bottom:22px;">
    <div class="card-header">
        <div>
            <div class="card-header-title"><i class="fas fa-calendar-week" style="color:var(--primary);"></i> Bảng đăng ký giờ làm tuần hiện tại</div>
            <p class="payroll-table-note">Giao diện bảng tuần giống staff, theo dõi đầy đủ slot của full-time và part-time.</p>
            <p class="payroll-table-note">Tuần: {{ $weekStart->format('d/m/Y') }} - {{ $weekEnd->format('d/m/Y') }}</p>
            @if($weekBoardLock)
                <p class="payroll-table-note" style="color:#3730a3;font-weight:600;">
                    Bảng tuần đã khóa lúc {{ optional($weekBoardLock->locked_at)?->format('d/m/Y H:i') }}
                    @if(optional($weekBoardLock->locker)->name)
                        bởi {{ $weekBoardLock->locker->name }}
                    @endif
                </p>
            @endif
        </div>
    </div>

    <div class="card-body">
        <div class="week-board-grid">
            <div class="card" style="margin:0;">
                <div class="card-header">
                    <div class="card-header-title">Nhân viên Full-time</div>
                </div>
                <div class="week-calendar-wrap">
                    <table class="week-calendar">
                        <thead>
                            <tr>
                                <th>Khung giờ</th>
                                @foreach($weekDays as $day)
                                    <th>
                                        <div class="day-label">
                                            <div class="day-title">{{ ['Thứ 2','Thứ 3','Thứ 4','Thứ 5','Thứ 6','Thứ 7','Chủ nhật'][$loop->index] }}</div>
                                            <div class="day-date">{{ $day->format('d/m') }}</div>
                                        </div>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(['08_16' => '8h-16h', '16_24' => '16h-24h'] as $slotKey => $slotLabel)
                                <tr>
                                    <td class="slot-title">{{ $slotLabel }}</td>
                                    @foreach($weekDays as $day)
                                        @php
                                            $dateKey = $day->toDateString();
                                            $entries = $weeklyAssignments['full_time'][$dateKey][$slotKey] ?? [];
                                        @endphp
                                        <td>
                                            @if(!empty($entries))
                                                <div class="slot-stack">
                                                    @foreach($entries as $entry)
                                                        <span class="slot-chip {{ $entry->status }}">
                                                            {{ $entry->staff->name ?? 'Nhân viên' }} · {{ $entry->status === 'pending' ? 'Chờ duyệt' : ($entry->status === 'approved' ? 'Đã duyệt' : 'Đã đóng') }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="slot-chip empty">Trống</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card" style="margin:0;">
                <div class="card-header">
                    <div class="card-header-title">Nhân viên Part-time</div>
                </div>
                <div class="week-calendar-wrap">
                    <table class="week-calendar">
                        <thead>
                            <tr>
                                <th>Khung giờ</th>
                                @foreach($weekDays as $day)
                                    <th>
                                        <div class="day-label">
                                            <div class="day-title">{{ ['Thứ 2','Thứ 3','Thứ 4','Thứ 5','Thứ 6','Thứ 7','Chủ nhật'][$loop->index] }}</div>
                                            <div class="day-date">{{ $day->format('d/m') }}</div>
                                        </div>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(['08_12' => '8h-12h', '12_16' => '12h-16h', '16_20' => '16h-20h', '20_24' => '20h-24h'] as $slotKey => $slotLabel)
                                <tr>
                                    <td class="slot-title">{{ $slotLabel }}</td>
                                    @foreach($weekDays as $day)
                                        @php
                                            $dateKey = $day->toDateString();
                                            $entries = $weeklyAssignments['part_time'][$dateKey][$slotKey] ?? [];
                                        @endphp
                                        <td>
                                            @if(!empty($entries))
                                                <div class="slot-stack">
                                                    @foreach($entries as $entry)
                                                        <span class="slot-chip {{ $entry->status }}">
                                                            {{ $entry->staff->name ?? 'Nhân viên' }} · {{ $entry->status === 'pending' ? 'Chờ duyệt' : ($entry->status === 'approved' ? 'Đã duyệt' : 'Đã đóng') }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="slot-chip empty">Trống</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="board-action-wrap">
            <form method="POST" action="{{ route('admin.work-schedules.close-week-board') }}">
                @csrf
                <button type="submit" class="btn-lock-board" {{ $weekBoardLock ? 'disabled' : '' }}>
                    <i class="fas fa-lock"></i> Duyệt và đóng bảng đăng ký giờ làm tuần này
                </button>
            </form>
        </div>
    </div>
</div>

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

    <div class="payroll-filter-group">
        <label class="payroll-mini-label">Trạng thái đăng ký</label>
        <select name="status" class="payroll-field">
            <option value="">Tất cả</option>
            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Chờ duyệt</option>
            <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Đã duyệt</option>
            <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Đã đóng</option>
        </select>
    </div>

    <button type="submit" class="btn-primary-admin"><i class="fas fa-filter"></i> Lọc dữ liệu</button>
</form>

<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-icon si-gold"><i class="fas fa-hourglass-half"></i></div>
        <div>
            <div class="stat-value">{{ $payrollStats['pending_count'] }}</div>
            <div class="stat-label">Đăng ký chờ duyệt</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon si-blue"><i class="fas fa-check-circle"></i></div>
        <div>
            <div class="stat-value">{{ $payrollStats['approved_count'] }}</div>
            <div class="stat-label">Ca đã duyệt</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon si-purple"><i class="fas fa-lock"></i></div>
        <div>
            <div class="stat-value">{{ $payrollStats['closed_count'] }}</div>
            <div class="stat-label">Ca đã đóng</div>
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

<div class="card">
    <div class="card-header">
        <div>
            <div class="card-header-title"><i class="fas fa-file-invoice-dollar" style="color:var(--primary);"></i> Tổng hợp bảng lương</div>
            <p class="payroll-table-note">Lương tạm tính chỉ cộng các ca đã duyệt hoặc đã đóng trong tháng đang xem.</p>
        </div>
    </div>
    <div class="card-body" style="padding:0;">
        @if($payrollRows->isNotEmpty())
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Nhân viên</th>
                        <th>Loại</th>
                        <th>Số ca</th>
                        <th>Giờ công</th>
                        <th>Đơn giá/giờ</th>
                        <th>Tạm tính</th>
                        <th>Trạng thái</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payrollRows as $row)
                        <tr>
                            <td>
                                <div class="staff-cell-name">{{ $row['staff']->name }}</div>
                                <div class="staff-cell-sub">{{ $row['staff']->email }}</div>
                            </td>
                            <td>
                                <span class="payroll-status {{ $row['employment_type'] === 'full_time' ? 'type-full' : 'type-part' }}">
                                    {{ $row['employment_type'] === 'full_time' ? 'Full-time' : 'Part-time' }}
                                </span>
                            </td>
                            <td>{{ $row['shift_count'] }}</td>
                            <td>{{ number_format($row['total_hours'], 2, ',', '.') }} giờ</td>
                            <td>{{ number_format($row['hourly_rate'], 0, ',', '.') }}đ</td>
                            <td><strong>{{ number_format($row['gross_salary'], 0, ',', '.') }}đ</strong></td>
                            <td>
                                <div class="staff-cell-sub">Đã duyệt: {{ $row['approved_count'] }}</div>
                                <div class="staff-cell-sub">Đã đóng: {{ $row['closed_count'] }}</div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="payroll-empty">Chưa có dữ liệu bảng lương trong bộ lọc hiện tại.</div>
        @endif
    </div>
</div>

<div class="payroll-subgrid">
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-header-title"><i class="fas fa-clock" style="color:#d97706;"></i> Đăng ký chờ duyệt</div>
                <p class="payroll-table-note">Admin có thể duyệt từng ca để đưa vào bảng lương tạm tính.</p>
            </div>
        </div>
        <div class="card-body" style="padding:0;">
            @if($pendingSchedules->isNotEmpty())
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Nhân viên</th>
                            <th>Ngày làm</th>
                            <th>Ca</th>
                            <th>Loại</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingSchedules as $schedule)
                            <tr>
                                <td>
                                    <div class="staff-cell-name">{{ $schedule->staff->name }}</div>
                                    <div class="staff-cell-sub">{{ $schedule->staff->email }}</div>
                                </td>
                                <td>{{ $schedule->work_date->format('d/m/Y') }}</td>
                                <td>{{ $schedule->shift_label }}</td>
                                <td>
                                    <span class="payroll-status {{ $schedule->employment_type === 'full_time' ? 'type-full' : 'type-part' }}">
                                        {{ $schedule->employment_type === 'full_time' ? 'Full-time' : 'Part-time' }}
                                    </span>
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('admin.work-schedules.approve', $schedule->id) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn-soft-admin approve">Duyệt</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="payroll-empty">Không còn đăng ký nào đang chờ duyệt.</div>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-header-title"><i class="fas fa-lock-open" style="color:#4338ca;"></i> Đăng ký đã duyệt</div>
                <p class="payroll-table-note">Khi cần chốt lương, bấm Đóng để khóa ca làm.</p>
            </div>
        </div>
        <div class="card-body" style="padding:0;">
            @if($approvedSchedules->isNotEmpty())
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Nhân viên</th>
                            <th>Ngày làm</th>
                            <th>Ca</th>
                            <th>Đã duyệt bởi</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($approvedSchedules as $schedule)
                            <tr>
                                <td>
                                    <div class="staff-cell-name">{{ $schedule->staff->name }}</div>
                                    <div class="staff-cell-sub">{{ $schedule->employment_type === 'full_time' ? 'Full-time' : 'Part-time' }}</div>
                                </td>
                                <td>{{ $schedule->work_date->format('d/m/Y') }}</td>
                                <td>{{ $schedule->shift_label }}</td>
                                <td>
                                    <div class="staff-cell-sub">{{ optional($schedule->approver)->name ?? 'Admin' }}</div>
                                    <div class="staff-cell-sub">{{ optional($schedule->approved_at)?->format('d/m/Y H:i') }}</div>
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('admin.work-schedules.close', $schedule->id) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn-soft-admin close">Đóng</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="payroll-empty">Chưa có ca nào ở trạng thái đã duyệt.</div>
            @endif
        </div>
    </div>
</div>

<div class="card" style="margin-top:22px;">
    <div class="card-header">
        <div>
            <div class="card-header-title"><i class="fas fa-shield-alt" style="color:#4338ca;"></i> Lịch đã đóng</div>
            <p class="payroll-table-note">Các ca đã đóng được dùng để chốt lương và không xử lý lại.</p>
        </div>
    </div>
    <div class="card-body" style="padding:0;">
        @if($closedSchedules->isNotEmpty())
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Nhân viên</th>
                        <th>Ngày làm</th>
                        <th>Ca</th>
                        <th>Trạng thái</th>
                        <th>Đóng bởi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($closedSchedules as $schedule)
                        <tr>
                            <td>
                                <div class="staff-cell-name">{{ $schedule->staff->name }}</div>
                                <div class="staff-cell-sub">{{ $schedule->staff->email }}</div>
                            </td>
                            <td>{{ $schedule->work_date->format('d/m/Y') }}</td>
                            <td>{{ $schedule->shift_label }}</td>
                            <td><span class="payroll-status status-closed">Đã đóng</span></td>
                            <td>
                                <div class="staff-cell-sub">{{ optional($schedule->closer)->name ?? 'Admin' }}</div>
                                <div class="staff-cell-sub">{{ optional($schedule->closed_at)?->format('d/m/Y H:i') }}</div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="payroll-empty">Chưa có đăng ký giờ làm nào được đóng.</div>
        @endif
    </div>
</div>
@endsection