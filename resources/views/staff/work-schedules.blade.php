@extends('staff.layout')

@section('title', 'Đăng ký giờ làm')
@section('page-title', 'Đăng ký giờ làm')

@section('styles')
<style>
    .schedule-shell {
        display: grid;
        grid-template-columns: 360px 1fr;
        gap: 20px;
    }
    @media (max-width: 1100px) {
        .schedule-shell {
            grid-template-columns: 1fr;
        }
    }

    .schedule-card {
        background: #fff;
        border-radius: 16px;
        border: 1px solid #edf0f5;
        box-shadow: 0 10px 24px rgba(15, 23, 42, .05);
        overflow: hidden;
    }

    .schedule-card-header {
        padding: 18px 20px;
        border-bottom: 1px solid #edf0f5;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .schedule-card-title {
        font-size: 16px;
        font-weight: 700;
        color: #172033;
        margin: 0;
    }

    .schedule-card-sub {
        margin: 4px 0 0;
        font-size: 12px;
        color: #8a8fa8;
    }

    .employment-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 12px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
    }

    .employment-badge.full-time {
        background: #e0f2fe;
        color: #0369a1;
    }

    .employment-badge.part-time {
        background: #fef3c7;
        color: #b45309;
    }

    .schedule-form {
        padding: 20px;
        display: grid;
        gap: 14px;
    }

    .schedule-form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }

    @media (max-width: 600px) {
        .schedule-form-row {
            grid-template-columns: 1fr;
        }
    }

    .schedule-label {
        display: block;
        margin-bottom: 6px;
        font-size: 12px;
        font-weight: 600;
        color: #334155;
    }

    .schedule-input,
    .schedule-textarea {
        width: 100%;
        border: 1px solid #d8dee8;
        border-radius: 12px;
        padding: 11px 13px;
        font-size: 13px;
        color: #172033;
        background: #fff;
        outline: none;
        transition: border-color .18s ease, box-shadow .18s ease;
    }

    .schedule-input:focus,
    .schedule-textarea:focus {
        border-color: #d4813a;
        box-shadow: 0 0 0 4px rgba(212, 129, 58, .12);
    }

    .schedule-textarea {
        min-height: 96px;
        resize: vertical;
    }

    .schedule-note-box {
        margin: 0 20px 20px;
        padding: 14px 16px;
        border-radius: 12px;
        background: #fff8ec;
        color: #9a6700;
        font-size: 12px;
        line-height: 1.65;
        border: 1px solid #fde7bf;
    }

    .my-schedule-list {
        padding: 0 20px 20px;
        display: grid;
        gap: 10px;
    }

    .my-schedule-item {
        border: 1px solid #edf0f5;
        border-radius: 12px;
        padding: 12px 14px;
        background: #fafbff;
    }

    .my-schedule-date {
        font-size: 13px;
        font-weight: 700;
        color: #172033;
    }

    .my-schedule-meta {
        font-size: 12px;
        color: #8a8fa8;
        margin-top: 4px;
    }

    .schedule-table-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    @media (max-width: 1200px) {
        .schedule-table-grid {
            grid-template-columns: 1fr;
        }
    }

    .schedule-table-wrap {
        padding: 18px 20px 20px;
    }

    .schedule-table {
        width: 100%;
        border-collapse: collapse;
    }

    .schedule-table th,
    .schedule-table td {
        padding: 12px 10px;
        text-align: left;
        border-bottom: 1px solid #eef1f6;
        vertical-align: top;
        font-size: 12.5px;
    }

    .schedule-table th {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #94a3b8;
        font-weight: 700;
    }

    .schedule-staff-name {
        font-size: 13px;
        font-weight: 700;
        color: #172033;
    }

    .schedule-empty {
        padding: 28px 14px;
        text-align: center;
        color: #9aa4b2;
        font-size: 13px;
    }

    .table-note {
        font-size: 12px;
        color: #8a8fa8;
        margin: 0;
    }

    .week-summary {
        padding: 16px 20px;
        background: #fff8ec;
        border-top: 1px solid #fde7bf;
        border-bottom: 1px solid #fde7bf;
        color: #8b5a1e;
        font-size: 12px;
    }

    .week-calendar-wrap {
        padding: 18px 20px 20px;
        overflow-x: auto;
    }

    .week-calendar {
        min-width: 860px;
        width: 100%;
        border-collapse: collapse;
    }

    .week-calendar th,
    .week-calendar td {
        border: 1px solid #edf0f5;
        padding: 10px;
        vertical-align: middle;
        text-align: center;
    }

    .week-calendar th {
        background: #f8fafc;
        color: #475569;
        font-size: 12px;
        font-weight: 700;
    }

    .slot-label {
        min-width: 130px;
        font-weight: 700;
        font-size: 13px;
        color: #172033;
        background: #fafbff;
    }

    .day-label {
        line-height: 1.35;
    }

    .day-title {
        font-size: 12px;
        color: #334155;
        font-weight: 700;
    }

    .day-date {
        font-size: 11px;
        color: #8a8fa8;
    }

    .slot-action,
    .slot-state {
        width: 100%;
        border: 0;
        border-radius: 10px;
        padding: 8px 9px;
        font-size: 12px;
        font-weight: 700;
        line-height: 1.35;
    }

    .slot-action {
        background: #d4813a;
        color: #fff;
        cursor: pointer;
        transition: filter .18s ease;
    }

    .slot-action:hover {
        filter: brightness(.95);
    }

    .slot-state.mine {
        background: #dcfce7;
        color: #166534;
    }

    .slot-state.booked {
        background: #fee2e2;
        color: #991b1b;
    }

    .slot-state.closed {
        background: #e2e8f0;
        color: #64748b;
    }

    .slot-stack {
        display: grid;
        gap: 6px;
    }

    .slot-meta {
        display: block;
        margin-top: 2px;
        font-size: 11px;
        font-weight: 600;
    }

    .recent-list {
        padding: 16px 20px 22px;
        display: grid;
        gap: 10px;
    }

    .recent-item {
        border: 1px solid #edf0f5;
        border-radius: 12px;
        padding: 11px 13px;
        background: #fafbff;
    }

    .recent-item-title {
        font-size: 13px;
        font-weight: 700;
        color: #172033;
    }

    .recent-item-sub {
        margin-top: 3px;
        font-size: 12px;
        color: #8a8fa8;
    }
</style>
@endsection

@section('content')

{{-- Bảng đăng ký giờ làm theo tuần hiện tại từ thứ 2 tới chủ nhật. --}}
<div class="schedule-card">
    <div class="schedule-card-header">
        <div>
            <h3 class="schedule-card-title">Đăng ký giờ làm theo tuần</h3>
            <p class="schedule-card-sub">
                Tuần hiện tại: {{ $weekStart->format('d/m/Y') }} - {{ $weekEnd->format('d/m/Y') }}
            </p>
        </div>
        @if($currentStaff->employment_type === 'full_time')
            <span class="employment-badge full-time">Full-time</span>
        @elseif($currentStaff->employment_type === 'part_time')
            <span class="employment-badge part-time">Part-time</span>
        @endif
    </div>

    <div class="week-summary">
        @if($isScheduleBoardLocked)
            Bảng đăng ký giờ làm tuần này đã được admin đóng lúc {{ optional($weekBoardLock->locked_at)?->format('d/m/Y H:i') }}
            @if(optional($weekBoardLock->locker)->name)
                bởi {{ $weekBoardLock->locker->name }}
            @endif
            . Bạn không thể đăng ký thêm.
        @elseif(!$currentStaff->employment_type)
            Tài khoản của bạn chưa được admin gán loại nhân viên. Vui lòng liên hệ admin để được gán full-time hoặc part-time trước khi đăng ký.
        @elseif($currentStaff->employment_type === 'part_time')
            Slot part-time: 8h-12h, 12h-16h, 16h-20h, 20h-24h. Mỗi slot được tối đa 2 nhân viên part-time chọn trong cùng ngày.
        @else
            Slot full-time: 8h-16h, 16h-24h. Mỗi slot chỉ một nhân viên full-time được chọn.
        @endif
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
                @if($currentStaff->employment_type && !empty($allowedSlots))
                    @foreach($allowedSlots as $slotKey => $slot)
                        <tr>
                            <td class="slot-label">{{ $slot['label'] }}</td>

                            @foreach($weekDays as $day)
                                @php
                                    $dateKey = $day->toDateString();
                                    $assignments = $weeklyAssignments[$dateKey][$slotKey] ?? [];
                                    $isPastDate = $day->lt(now()->startOfDay());
                                    $slotCapacity = $currentStaff->employment_type === 'part_time' ? 2 : 1;
                                    $isMine = collect($assignments)->contains(fn ($assignment) => (int) $assignment->staff_id === (int) $currentStaff->id);
                                    $alreadyPickedAnotherShift = empty($assignments) && !empty($mySelectedDates[$dateKey]);
                                    $isSlotFull = count($assignments) >= $slotCapacity;
                                @endphp
                                <td>
                                    @if($isScheduleBoardLocked)
                                        <div class="slot-state closed">Bảng đã khóa</div>
                                    @elseif($isPastDate)
                                        <div class="slot-state closed">Đã qua</div>
                                    @elseif(!empty($assignments))
                                        <div class="slot-stack">
                                            @foreach($assignments as $assignment)
                                                @if((int) $assignment->staff_id === (int) $currentStaff->id)
                                                    <div class="slot-state mine">
                                                        Đã chọn
                                                        <span class="slot-meta">{{ $assignment->staff->name ?? 'Bạn' }}</span>
                                                    </div>
                                                @else
                                                    <div class="slot-state booked">
                                                        Đã có người chọn
                                                        <span class="slot-meta">{{ $assignment->staff->name ?? 'Nhân viên' }}</span>
                                                    </div>
                                                @endif
                                            @endforeach

                                            @if($isMine)
                                            @elseif($alreadyPickedAnotherShift)
                                                <div class="slot-state closed">Bạn đã chọn ca khác</div>
                                            @elseif(!$isSlotFull)
                                                <form method="POST" action="{{ route('staff.work-schedules.store') }}">
                                                    @csrf
                                                    <input type="hidden" name="work_date" value="{{ $dateKey }}">
                                                    <input type="hidden" name="slot_key" value="{{ $slotKey }}">
                                                    <button type="submit" class="slot-action">Chọn slot còn lại</button>
                                                </form>
                                            @endif
                                        </div>
                                    @elseif($alreadyPickedAnotherShift)
                                        <div class="slot-state closed">Bạn đã chọn ca khác</div>
                                    @else
                                        <form method="POST" action="{{ route('staff.work-schedules.store') }}">
                                            @csrf
                                            <input type="hidden" name="work_date" value="{{ $dateKey }}">
                                            <input type="hidden" name="slot_key" value="{{ $slotKey }}">
                                            <button type="submit" class="slot-action">Chọn slot</button>
                                        </form>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="8" class="schedule-empty">Không có slot đăng ký vì tài khoản chưa có loại nhân viên hợp lệ.</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>

<div class="schedule-card" style="margin-top:20px;">
    <div class="schedule-card-header">
        <div>
            <h3 class="schedule-card-title">Đăng ký gần đây của tôi</h3>
            <p class="schedule-card-sub">Hiển thị 6 đăng ký mới nhất.</p>
        </div>
    </div>
    <div class="recent-list">
        @forelse($myRegistrations as $registration)
            <div class="recent-item">
                <div class="recent-item-title">
                    {{ $registration->work_date->format('d/m/Y') }} · {{ $registration->shift_label ?: (substr($registration->start_time, 0, 5) . ' - ' . substr($registration->end_time, 0, 5)) }}
                </div>
                <div class="recent-item-sub">{{ substr($registration->start_time, 0, 5) }} - {{ substr($registration->end_time, 0, 5) }}</div>
            </div>
        @empty
            <div class="schedule-empty">Bạn chưa có đăng ký giờ làm nào.</div>
        @endforelse
    </div>
</div>
@endsection