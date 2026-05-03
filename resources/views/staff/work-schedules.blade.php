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

    .schedule-error-text {
        color: #d32f2f;
        font-size: 12px;
        margin-top: 4px;
        display: block;
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

{{-- Bảng đăng ký giờ làm theo tuần tới từ thứ 2 tới chủ nhật. --}}
<div class="schedule-card">
    <div class="schedule-card-header">
        <div>
            <h3 class="schedule-card-title">Đăng ký giờ làm theo tuần</h3>
            <p class="schedule-card-sub">
                Tuần tới: {{ $weekStart->format('d/m/Y') }} - {{ $weekEnd->format('d/m/Y') }}
            </p>
        </div>
        @if($currentStaff->employment_type === 'full_time')
            <span class="employment-badge full-time">Full-time</span>
        @elseif($currentStaff->employment_type === 'part_time')
            <span class="employment-badge part-time">Part-time</span>
        @endif
    </div>

    @if(!$isScheduleBoardLocked)
        <div class="week-summary">
            @if(!$currentStaff->employment_type)
                Tài khoản của bạn chưa được admin gán loại nhân viên. Vui lòng liên hệ admin để được gán full-time hoặc part-time trước khi đăng ký.
            @elseif($currentStaff->employment_type === 'part_time')
                Bảng đăng ký giờ làm sẽ được đóng vào 22h00 tối nay. Slot part-time: 8h-12h, 12h-16h, 16h-20h, 20h-24h. Mỗi slot được tối đa 2 nhân viên part-time chọn trong cùng ngày.
            @else
                Bảng đăng ký giờ làm sẽ được đóng vào 22h00 tối nay. Slot full-time: 8h-16h, 16h-24h. Mỗi slot chỉ một nhân viên full-time được chọn.
            @endif
        </div>
    @endif

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
                                    @if(!empty($assignments))
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
                                            @elseif($isScheduleBoardLocked)
                                                <div class="slot-state closed">Bảng đã khóa</div>
                                            @elseif($isPastDate)
                                                <div class="slot-state closed">Đã qua</div>
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
                                    @elseif($isScheduleBoardLocked)
                                        <div class="slot-state closed">Bảng đã khóa</div>
                                    @elseif($isPastDate)
                                        <div class="slot-state closed">Đã qua</div>
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

{{-- Bảng đăng ký giờ tăng ca --}}
<div class="schedule-card" style="margin-top:20px;">
    <div class="schedule-card-header">
        <div>
            <h3 class="schedule-card-title">📋 Đăng ký giờ tăng ca</h3>
            <p class="schedule-card-sub">Đăng ký giờ tăng ca và chờ admin duyệt.</p>
        </div>
    </div>

    <div class="schedule-form">
        <div id="overtimeFormMessage" class="schedule-note-box" style="display:none; margin:0 0 14px;"></div>
        <form id="overtimeForm" method="POST" action="{{ route('staff.overtimes.store') }}">
            @csrf
            
            <div class="schedule-form-row">
                <div>
                    <label class="schedule-label">Ngày tăng ca</label>
                    <div class="schedule-input" style="background:#f8fafc; color:#475569;">{{ now()->format('d/m/Y') }} (hôm nay)</div>
                </div>
                <div>
                    <label class="schedule-label">Số giờ tăng ca <span style="color: #d32f2f;">*</span></label>
                    <input type="number" name="hours" class="schedule-input" required step="0.5" min="0.5" max="2"
                        value="{{ old('hours') }}" placeholder="VD: 1.5 hoặc 2">
                    <span data-error-for="hours" class="schedule-error-text"></span>
                    @error('hours')
                        <span class="schedule-error-text">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div>
                <label class="schedule-label">Ghi chú (tùy chọn)</label>
                <textarea name="notes" class="schedule-textarea" placeholder="VD: Phục vụ sự kiện, hỗ trợ bếp...">{{ old('notes') }}</textarea>
                <span data-error-for="notes" class="schedule-error-text"></span>
                @error('notes')
                    <span class="schedule-error-text">{{ $message }}</span>
                @enderror
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 14px;">
                <button id="overtimeSubmitBtn" type="submit" style="
                    background: #d4813a;
                    color: #fff;
                    border: 0;
                    padding: 10px 18px;
                    border-radius: 10px;
                    font-weight: 700;
                    cursor: pointer;
                    transition: filter .18s ease;
                " onmouseover="this.style.filter='brightness(.95)'" onmouseout="this.style.filter='brightness(1)'">
                    Đăng ký tăng ca
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Bảng danh sách tăng ca gần đây --}}
<div class="schedule-card" style="margin-top:20px;">
    <div class="schedule-card-header">
        <div>
            <h3 class="schedule-card-title">📊 Danh sách tăng ca của tôi</h3>
            <p class="schedule-card-sub">Hiển thị 6 đơn tăng ca gần nhất.</p>
        </div>
    </div>

    <div style="overflow-x: auto;">
        <table class="schedule-table" style="min-width: 500px;">
            <thead>
                <tr>
                    <th>Tên nhân viên</th>
                    <th>Ngày tăng ca</th>
                    <th>Số giờ</th>
                    <th>Trạng thái</th>
                    <th>Ghi chú</th>
                </tr>
            </thead>
            <tbody id="overtimeTableBody">
                @forelse($myOvertimes as $overtime)
                    <tr>
                        <td style="font-weight: 700;">{{ $currentStaff->name }}</td>
                        <td style="font-weight: 700;">{{ $overtime->overtime_date->format('d/m/Y') }}</td>
                        <td style="text-align: center;">
                            @php
                                $hours = (int) $overtime->hours === $overtime->hours 
                                    ? (int) $overtime->hours 
                                    : $overtime->hours;
                            @endphp
                            {{ $hours }} giờ
                        </td>
                        <td>
                            @if($overtime->status === 'pending')
                                <span style="
                                    display: inline-block;
                                    background: #fef3c7;
                                    color: #b45309;
                                    padding: 5px 10px;
                                    border-radius: 8px;
                                    font-size: 11px;
                                    font-weight: 700;
                                    text-transform: uppercase;
                                ">Chờ duyệt</span>
                            @elseif($overtime->status === 'approved')
                                <span style="
                                    display: inline-block;
                                    background: #dcfce7;
                                    color: #166534;
                                    padding: 5px 10px;
                                    border-radius: 8px;
                                    font-size: 11px;
                                    font-weight: 700;
                                    text-transform: uppercase;
                                ">Được duyệt</span>
                            @elseif($overtime->status === 'rejected')
                                <span style="
                                    display: inline-block;
                                    background: #fee2e2;
                                    color: #991b1b;
                                    padding: 5px 10px;
                                    border-radius: 8px;
                                    font-size: 11px;
                                    font-weight: 700;
                                    text-transform: uppercase;
                                ">Từ chối</span>
                            @endif
                        </td>
                        <td style="font-size: 12px; color: #8a8fa8;">{{ $overtime->notes ?: '—' }}</td>
                    </tr>
                @empty
                    <tr id="overtimeEmptyRow">
                        <td colspan="5" class="schedule-empty">Bạn chưa có đơn tăng ca nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Đăng ký giờ làm gần đây --}}
<div class="schedule-card" style="margin-top:20px;">
    <div class="schedule-card-header">
        <div>
            <h3 class="schedule-card-title">🗓️ Đăng ký gần đây của tôi</h3>
            <p class="schedule-card-sub">Hiển thị 6 đăng ký giờ làm mới nhất.</p>
        </div>
    </div>
    <div style="overflow-x:auto;">
        <table class="schedule-table" style="min-width:500px;">
            <thead>
                <tr>
                    <th>Ngày làm việc</th>
                    <th>Khung giờ</th>
                    <th>Trạng thái</th>
                </tr>
            </thead>
            <tbody>
                @forelse($myRegistrations as $registration)
                    <tr>
                        <td style="font-weight:700;">{{ $registration->work_date->format('d/m/Y') }}</td>
                        <td>{{ substr($registration->start_time, 0, 5) }} – {{ substr($registration->end_time, 0, 5) }}</td>
                        <td>
                            @if($registration->status === 'pending')
                                <span style="display:inline-block;background:#fef3c7;color:#b45309;padding:4px 10px;border-radius:8px;font-size:11px;font-weight:700;text-transform:uppercase;">Chờ duyệt</span>
                            @elseif($registration->status === 'approved')
                                <span style="display:inline-block;background:#dcfce7;color:#166534;padding:4px 10px;border-radius:8px;font-size:11px;font-weight:700;text-transform:uppercase;">Đã duyệt</span>
                            @elseif($registration->status === 'closed')
                                <span style="display:inline-block;background:#f1f5f9;color:#475569;padding:4px 10px;border-radius:8px;font-size:11px;font-weight:700;text-transform:uppercase;">Đã chốt</span>
                            @else
                                <span style="color:#94a3b8;font-size:12px;">{{ $registration->status }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="schedule-empty">Bạn chưa có đăng ký giờ làm nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

@section('scripts')
<script>
(() => {
    const form = document.getElementById('overtimeForm');
    const messageBox = document.getElementById('overtimeFormMessage');
    const submitBtn = document.getElementById('overtimeSubmitBtn');
    const tableBody = document.getElementById('overtimeTableBody');

    if (!form || !messageBox || !submitBtn || !tableBody) {
        return;
    }

    const showMessage = (text, type) => {
        messageBox.style.display = 'block';
        messageBox.textContent = text;
        if (type === 'success') {
            messageBox.style.background = '#ecfdf3';
            messageBox.style.borderColor = '#c8f3d9';
            messageBox.style.color = '#166534';
            return;
        }

        messageBox.style.background = '#fef2f2';
        messageBox.style.borderColor = '#fecaca';
        messageBox.style.color = '#991b1b';
    };

    const clearFieldErrors = () => {
        form.querySelectorAll('[data-error-for]').forEach((node) => {
            node.textContent = '';
        });
    };

    const setFieldErrors = (errors) => {
        Object.entries(errors).forEach(([field, messages]) => {
            const errorNode = form.querySelector(`[data-error-for="${field}"]`);
            if (!errorNode || !Array.isArray(messages) || messages.length === 0) {
                return;
            }
            errorNode.textContent = messages[0];
        });
    };

    const statusBadgeHtml = (status) => {
        if (status === 'approved') {
            return '<span style="display:inline-block;background:#dcfce7;color:#166534;padding:5px 10px;border-radius:8px;font-size:11px;font-weight:700;text-transform:uppercase;">Được duyệt</span>';
        }
        if (status === 'rejected') {
            return '<span style="display:inline-block;background:#fee2e2;color:#991b1b;padding:5px 10px;border-radius:8px;font-size:11px;font-weight:700;text-transform:uppercase;">Từ chối</span>';
        }
        return '<span style="display:inline-block;background:#fef3c7;color:#b45309;padding:5px 10px;border-radius:8px;font-size:11px;font-weight:700;text-transform:uppercase;">Chờ duyệt</span>';
    };

    const appendOvertimeRow = (overtime) => {
        const emptyRow = document.getElementById('overtimeEmptyRow');
        if (emptyRow) {
            emptyRow.remove();
        }

        const row = document.createElement('tr');
        row.innerHTML = `
            <td style="font-weight: 700;">${overtime.staff_name || ''}</td>
            <td style="font-weight: 700;">${overtime.overtime_date || ''}</td>
            <td style="text-align: center;">${overtime.hours} giờ</td>
            <td>${statusBadgeHtml(overtime.status)}</td>
            <td style="font-size: 12px; color: #8a8fa8;">${overtime.notes || '—'}</td>
        `;
        tableBody.prepend(row);

        while (tableBody.querySelectorAll('tr').length > 6) {
            tableBody.lastElementChild?.remove();
        }
    };

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearFieldErrors();
        messageBox.style.display = 'none';
        submitBtn.disabled = true;
        const originalLabel = submitBtn.textContent;
        submitBtn.textContent = 'Đang gửi...';

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: new FormData(form),
            });

            const payload = await response.json();

            if (!response.ok) {
                if (response.status === 422 && payload.errors) {
                    setFieldErrors(payload.errors);
                }
                showMessage(payload.message || 'Không thể đăng ký tăng ca. Vui lòng thử lại.', 'error');
                return;
            }

            appendOvertimeRow(payload.overtime || {});
            showMessage(payload.message || 'Đăng ký tăng ca thành công.', 'success');
            form.reset();
        } catch (_error) {
            showMessage('Không thể kết nối máy chủ. Vui lòng thử lại.', 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = originalLabel;
        }
    });
})();
</script>
@endsection