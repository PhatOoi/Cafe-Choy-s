@extends('admin.layout')

@section('title', 'Đăng ký giờ làm')
@section('page-title', 'Đăng ký giờ làm nhân viên')
@section('breadcrumb', 'Admin / Đăng ký giờ làm')

@section('content')
<style>
    .schedule-filter-bar {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: end;
        margin-bottom: 22px;
    }

    .schedule-filter-group {
        min-width: 180px;
    }

    .schedule-mini-label {
        display: block;
        margin-bottom: 6px;
        font-size: 12px;
        font-weight: 600;
        color: var(--text-muted);
    }

    .schedule-field {
        width: 100%;
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 10px 12px;
        font-size: 13px;
        background: #fff;
    }

    .schedule-status {
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
    .status-rejected { background: #fee2e2; color: #991b1b; }
    .type-full { background: #e0f2fe; color: #0369a1; }
    .type-part { background: #fef3c7; color: #b45309; }

    .schedule-subgrid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-top: 22px;
    }

    .schedule-subgrid > .card:only-child {
        grid-column: 1 / -1;
    }

    @media (max-width: 1180px) {
        .schedule-subgrid {
            grid-template-columns: 1fr;
        }
    }

    .schedule-table-note {
        font-size: 12px;
        color: var(--text-muted);
        margin-top: 4px;
    }

    .schedule-inline-actions {
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

    .btn-soft-admin.adjust {
        background: linear-gradient(180deg, #eff6ff 0%, #dbeafe 100%);
        border-color: #93c5fd;
        color: #1e3a8a;
        box-shadow: 0 4px 10px rgba(59, 130, 246, .18);
        transition: all .18s ease;
    }

    .btn-soft-admin.adjust:hover {
        background: linear-gradient(180deg, #dbeafe 0%, #bfdbfe 100%);
        border-color: #60a5fa;
        color: #1e40af;
        transform: translateY(-1px);
        box-shadow: 0 6px 14px rgba(59, 130, 246, .25);
    }

    .btn-soft-admin.adjust:focus-visible {
        outline: 0;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, .25);
    }

    .btn-soft-admin.absent {
        background: #fff1f2;
        border-color: #fecdd3;
        color: #be123c;
        transition: all .18s ease;
    }

    .btn-soft-admin.absent:hover {
        background: #ffe4e6;
        border-color: #fda4af;
        color: #9f1239;
    }


    details.adjust-details {
        margin-top: 8px;
    }

    details.adjust-details > summary {
        list-style: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        user-select: none;
    }

    details.adjust-details > summary::-webkit-details-marker {
        display: none;
    }

    details.adjust-details > summary::before {
        content: '\f044';
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        font-size: 11px;
    }

    .adjust-form-box {
        margin-top: 10px;
        padding: 10px;
        border: 1px solid var(--border);
        border-radius: 10px;
        background: #f8fafc;
        display: grid;
        gap: 8px;
    }

    .adjust-form-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .adjust-form-field {
        width: 100%;
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 8px 10px;
        font-size: 12px;
        background: #fff;
    }

    .schedule-empty {
        padding: 28px 16px;
        text-align: center;
        color: var(--text-muted);
        font-size: 13px;
    }

    .week-board-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 20px;
        margin-bottom: 22px;
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
        <div class="page-header-title">Đăng ký giờ làm nhân viên</div>
        <div class="page-header-sub">Theo dõi và xử lý các đăng ký ca làm của staff theo tuần và theo tháng.</div>
    </div>
</div>

<div class="card" style="margin-bottom:22px;">
    <div class="card-header">
        <div>
            <div class="card-header-title"><i class="fas fa-calendar-week" style="color:var(--primary);"></i> Bảng đăng ký giờ làm tuần hiện tại</div>
            <p class="schedule-table-note">Tuần: {{ $weekStart->format('d/m/Y') }} - {{ $weekEnd->format('d/m/Y') }}</p>
            <p class="schedule-table-note" style="color:#b45309;font-weight:600;">Bảng đăng ký tự động đóng sau 22:00 mỗi ngày.</p>
            @if($weekBoardLock)
                <p class="schedule-table-note" style="color:#3730a3;font-weight:600;">
                    Bảng tuần đã khóa lúc {{ optional($weekBoardLock->locked_at)?->format('d/m/Y H:i') }}
                    @if(optional($weekBoardLock->locker)->name)
                        bởi {{ $weekBoardLock->locker->name }}
                    @endif
                </p>
            @endif
            @if($isAutoClosedAtNight)
                <p class="schedule-table-note" style="color:#991b1b;font-weight:600;">
                    Hiện tại đã quá 22:00, bảng đăng ký đang ở trạng thái tự động đóng.
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
            <form method="POST" action="{{ route('admin.work-schedules.open-week-board') }}">
                @csrf
                <button type="submit" class="btn-lock-board" {{ (!$weekBoardLock || $isAutoClosedAtNight) ? 'disabled' : '' }}>
                    <i class="fas fa-lock-open"></i> Mở bảng đăng ký giờ làm
                </button>
            </form>
        </div>
    </div>
</div>

<form method="GET" action="{{ route('admin.work-schedules.index') }}" class="schedule-filter-bar">
    <div class="schedule-filter-group">
        <label class="schedule-mini-label">Tháng</label>
        <input type="month" name="month" value="{{ $monthInput }}" class="schedule-field">
    </div>

    <div class="schedule-filter-group">
        <label class="schedule-mini-label">Loại nhân viên</label>
        <select name="employment_type" class="schedule-field">
            <option value="">Tất cả</option>
            <option value="full_time" {{ request('employment_type') === 'full_time' ? 'selected' : '' }}>Full-time</option>
            <option value="part_time" {{ request('employment_type') === 'part_time' ? 'selected' : '' }}>Part-time</option>
        </select>
    </div>

    <div class="schedule-filter-group">
        <label class="schedule-mini-label">Trạng thái đăng ký</label>
        <select name="status" class="schedule-field">
            <option value="">Tất cả</option>
            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Chờ duyệt</option>
            <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Đã duyệt</option>
        </select>
    </div>

    <button type="submit" class="btn-primary-admin"><i class="fas fa-filter"></i> Lọc dữ liệu</button>
</form>

<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-icon si-gold"><i class="fas fa-hourglass-half"></i></div>
        <div>
            <div class="stat-value" id="pending-count-value">{{ $scheduleStats['pending_count'] }}</div>
            <div class="stat-label">Đăng ký chờ duyệt</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon si-blue"><i class="fas fa-check-circle"></i></div>
        <div>
            <div class="stat-value" id="approved-count-value">{{ $scheduleStats['approved_count'] }}</div>
            <div class="stat-label">Đăng ký đã duyệt</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon si-gold"><i class="fas fa-business-time"></i></div>
        <div>
            <div class="stat-value">{{ $scheduleStats['pending_overtime_count'] }}</div>
            <div class="stat-label">Đơn tăng ca chờ duyệt</div>
        </div>
    </div>
</div>

<div class="card" style="margin-bottom:22px;">
    <div class="card-header">
        <div>
            <div class="card-header-title"><i class="fas fa-business-time" style="color:#d97706;"></i> Duyệt giờ tăng ca</div>
            <p class="schedule-table-note">Sau khi staff gửi đơn tăng ca, admin duyệt/từ chối tại đây trước khi tính lương tăng ca.</p>
        </div>
    </div>
    <div class="card-body" style="padding:0;">
        @if($pendingOvertimes->isNotEmpty())
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Nhân viên</th>
                        <th>Ngày tăng ca</th>
                        <th>Số giờ</th>
                        <th>Loại NV</th>
                        <th>Ghi chú</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pendingOvertimes as $overtime)
                        <tr>
                            <td>
                                <div class="staff-cell-name">{{ $overtime->staff->name }}</div>
                                <div class="staff-cell-sub">{{ $overtime->staff->email }}</div>
                            </td>
                            <td>{{ $overtime->overtime_date->format('d/m/Y') }}</td>
                            <td>{{ (float) $overtime->hours == floor((float) $overtime->hours) ? number_format($overtime->hours, 0, ',', '.') : number_format($overtime->hours, 2, ',', '.') }} giờ</td>
                            <td>
                                <span class="schedule-status {{ $overtime->staff->employment_type === 'full_time' ? 'type-full' : 'type-part' }}">
                                    {{ $overtime->staff->employment_type === 'full_time' ? 'Full-time' : 'Part-time' }}
                                </span>
                            </td>
                            <td>{{ $overtime->notes ?: '—' }}</td>
                            <td>
                                <div class="schedule-inline-actions">
                                    <form method="POST" action="{{ route('admin.overtimes.approve', $overtime->id) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn-soft-admin approve">Duyệt</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.overtimes.reject', $overtime->id) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn-soft-admin absent" onclick="return confirm('Xác nhận từ chối đơn tăng ca này?');">Từ chối</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="schedule-empty">Không có đơn tăng ca nào đang chờ duyệt.</div>
        @endif
    </div>
</div>

<div class="card" style="margin-bottom:22px;">
    <div class="card-header">
        <div>
            <div class="card-header-title"><i class="fas fa-history" style="color:#0369a1;"></i> Lịch sử xử lý tăng ca</div>
            <p class="schedule-table-note">Hiển thị 10 đơn tăng ca đã duyệt hoặc đã từ chối gần nhất.</p>
        </div>
    </div>
    <div class="card-body" style="padding:0;">
        @if($processedOvertimes->isNotEmpty())
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Nhân viên</th>
                        <th>Ngày tăng ca</th>
                        <th>Số giờ</th>
                        <th>Trạng thái</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($processedOvertimes as $overtime)
                        <tr>
                            <td>{{ $overtime->staff->name }}</td>
                            <td>{{ $overtime->overtime_date->format('d/m/Y') }}</td>
                            <td>{{ (float) $overtime->hours == floor((float) $overtime->hours) ? number_format($overtime->hours, 0, ',', '.') : number_format($overtime->hours, 2, ',', '.') }} giờ</td>
                            <td>
                                <span class="schedule-status {{ $overtime->status === 'approved' ? 'status-approved' : 'status-rejected' }}">
                                    {{ $overtime->status === 'approved' ? 'Đã duyệt' : 'Từ chối' }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="schedule-empty">Chưa có lịch sử xử lý đơn tăng ca.</div>
        @endif
    </div>
</div>

<div class="schedule-subgrid">
    @if($pendingSchedules->isNotEmpty())
        <div class="card" id="pending-approvals-card">
            <div class="card-header">
                <div>
                    <div class="card-header-title"><i class="fas fa-clock" style="color:#d97706;"></i> Đăng ký chờ duyệt</div>
                    <p class="schedule-table-note">Admin có thể duyệt từng ca trước khi đưa vào chốt lương.</p>
                    <p class="schedule-table-note" id="approve-feedback" style="display:none;font-weight:600;"></p>
                </div>
            </div>
            <div class="card-body" id="pending-schedules-card-body" style="padding:0;">
                <table class="admin-table" id="pending-schedules-table">
                    <thead>
                        <tr>
                            <th>Nhân viên</th>
                            <th>Ngày làm</th>
                            <th>Ca</th>
                            <th>Loại</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody id="pending-schedules-tbody">
                        @foreach($pendingSchedules as $schedule)
                            <tr data-schedule-id="{{ $schedule->id }}">
                                <td>
                                    <div class="staff-cell-name">{{ $schedule->staff->name }}</div>
                                    <div class="staff-cell-sub">{{ $schedule->staff->email }}</div>
                                </td>
                                <td>{{ $schedule->work_date->format('d/m/Y') }}</td>
                                <td>{{ $schedule->shift_label }}</td>
                                <td>
                                    <span class="schedule-status {{ $schedule->employment_type === 'full_time' ? 'type-full' : 'type-part' }}">
                                        {{ $schedule->employment_type === 'full_time' ? 'Full-time' : 'Part-time' }}
                                    </span>
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('admin.work-schedules.approve', $schedule->id) }}" class="js-approve-form" data-schedule-id="{{ $schedule->id }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn-soft-admin approve">Duyệt</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-header-title"><i class="fas fa-lock-open" style="color:#4338ca;"></i> Đăng ký đã duyệt</div>
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
                                    @php
                                        $slotOptions = $scheduleSlots[$schedule->employment_type] ?? [];
                                        $currentSlotKey = null;

                                        foreach ($slotOptions as $slotKey => $slotConfig) {
                                            if (
                                                $slotConfig['start'] === substr((string) $schedule->start_time, 0, 5)
                                                && $slotConfig['end'] === substr((string) $schedule->end_time, 0, 5)
                                            ) {
                                                $currentSlotKey = $slotKey;
                                                break;
                                            }
                                        }
                                    @endphp

                                    <details class="adjust-details">
                                        <summary class="btn-soft-admin adjust">Điều chỉnh ca làm</summary>
                                        <form method="POST" action="{{ route('admin.work-schedules.adjust', $schedule->id) }}" class="adjust-form-box" id="adjust-form-{{ $schedule->id }}">
                                            @csrf
                                            @method('PATCH')
                                            <input
                                                type="date"
                                                name="work_date"
                                                value="{{ $schedule->work_date->format('Y-m-d') }}"
                                                class="adjust-form-field"
                                                required
                                            >
                                            <select name="slot_key" class="adjust-form-field" required>
                                                @foreach($slotOptions as $slotKey => $slotConfig)
                                                    <option value="{{ $slotKey }}" {{ $currentSlotKey === $slotKey ? 'selected' : '' }}>
                                                        {{ $slotConfig['label'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </form>

                                        <div class="adjust-form-actions" style="margin-top:8px;">
                                            <button type="submit" form="adjust-form-{{ $schedule->id }}" class="btn-soft-admin adjust">Lưu điều chỉnh</button>

                                            <form method="POST" action="{{ route('admin.work-schedules.absent', $schedule->id) }}" style="margin:0;">
                                                @csrf
                                                @method('PATCH')
                                                <button
                                                    type="submit"
                                                    class="btn-soft-admin absent"
                                                    onclick="return confirm('Xác nhận đánh dấu vắng ca cho nhân viên này?');"
                                                >
                                                    Vắng ca
                                                </button>
                                            </form>
                                        </div>

                                    </details>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="schedule-empty">Chưa có ca nào ở trạng thái đã duyệt.</div>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const approveForms = document.querySelectorAll('.js-approve-form');
    if (!approveForms.length) {
        return;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const pendingCountEl = document.getElementById('pending-count-value');
    const approvedCountEl = document.getElementById('approved-count-value');
    const pendingCard = document.getElementById('pending-approvals-card');
    const pendingCardBody = document.getElementById('pending-schedules-card-body');
    const pendingTable = document.getElementById('pending-schedules-table');
    const pendingTbody = document.getElementById('pending-schedules-tbody');
    const feedbackEl = document.getElementById('approve-feedback');

    const changeStat = function (element, delta) {
        if (!element) {
            return;
        }

        const current = parseInt(element.textContent || '0', 10);
        const next = Number.isNaN(current) ? 0 : Math.max(0, current + delta);
        element.textContent = String(next);
    };

    const showFeedback = function (message, isError) {
        if (!feedbackEl) {
            return;
        }

        feedbackEl.style.display = 'block';
        feedbackEl.style.color = isError ? '#b91c1c' : '#166534';
        feedbackEl.textContent = message;
    };

    approveForms.forEach(function (form) {
        form.addEventListener('submit', async function (event) {
            event.preventDefault();

            const button = form.querySelector('button[type="submit"]');
            if (!button || button.disabled) {
                return;
            }

            const originalText = button.textContent;
            button.disabled = true;
            button.textContent = 'Đang duyệt...';

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                    },
                    body: '_method=PATCH',
                });

                const payload = await response.json().catch(function () {
                    return {};
                });

                if (!response.ok || !payload.success) {
                    throw new Error(payload.message || 'Không thể duyệt ca. Vui lòng thử lại.');
                }

                const row = form.closest('tr');
                if (row) {
                    row.remove();
                }

                changeStat(pendingCountEl, -1);
                changeStat(approvedCountEl, 1);
                showFeedback(payload.message || 'Đã duyệt đăng ký giờ làm.', false);

                if (pendingTbody && !pendingTbody.querySelector('tr')) {
                    if (pendingCard) {
                        pendingCard.remove();
                    } else if (pendingCardBody) {
                        if (pendingTable) {
                            pendingTable.remove();
                        }

                        const emptyEl = document.createElement('div');
                        emptyEl.className = 'schedule-empty';
                        emptyEl.textContent = 'Không còn đăng ký nào đang chờ duyệt.';
                        pendingCardBody.appendChild(emptyEl);
                    }
                }
            } catch (error) {
                showFeedback(error.message || 'Có lỗi xảy ra khi duyệt ca.', true);
                button.disabled = false;
                button.textContent = originalText;
            }
        });
    });
});
</script>
@endsection