@extends('admin.layout')

@section('title', 'Thêm tài khoản mới')
@section('page-title', 'Thêm tài khoản')
@section('breadcrumb', 'Admin / Tài khoản / Thêm mới')

@section('styles')
<style>
/* ── Wrapper ──────────────────────────────── */
.create-user-wrap {
    width: 100%;
}

/* ── Section divider ─────────────────────── */
.form-section-title {
    font-size: 11.5px;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: var(--primary);
    padding: 0 0 10px;
    border-bottom: 1.5px solid rgba(212,129,58,.18);
    margin: 24px 0 18px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.form-section-title:first-child { margin-top: 0; }

/* ── Input with leading icon ─────────────── */
.input-icon-wrap {
    position: relative;
}
.input-icon-wrap .input-icon {
    position: absolute;
    left: 13px;
    top: 50%;
    transform: translateY(-50%);
    color: #b0b9c8;
    font-size: 13px;
    pointer-events: none;
}
.input-icon-wrap .form-control,
.input-icon-wrap .form-select {
    padding-left: 36px;
}

/* ── 2-col grid ──────────────────────────── */
.form-grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}
.form-grid-2 .span-2 { grid-column: 1 / -1; }

/* ── Role card selector ──────────────────── */
.role-cards {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}
.role-card-label {
    cursor: pointer;
    border: 2px solid var(--border);
    border-radius: 12px;
    padding: 14px 16px;
    display: flex;
    align-items: flex-start;
    gap: 12px;
    transition: border-color .16s, background .16s, box-shadow .16s;
    position: relative;
}
.role-card-label:hover {
    border-color: var(--primary);
    background: rgba(212,129,58,.04);
}
.role-card-label input[type="radio"] {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
}
.role-card-label.selected {
    border-color: var(--primary);
    background: rgba(212,129,58,.06);
    box-shadow: 0 0 0 3px rgba(212,129,58,.12);
}
.role-card-icon {
    font-size: 22px;
    line-height: 1;
    flex-shrink: 0;
    margin-top: 1px;
}
.role-card-name {
    font-size: 13.5px;
    font-weight: 700;
    color: var(--text-dark);
    margin-bottom: 3px;
}
.role-card-desc {
    font-size: 11.5px;
    color: var(--text-muted);
    line-height: 1.4;
}

/* ── Employment badges ───────────────────── */
.emp-cards {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}
.emp-card-label {
    cursor: pointer;
    border: 2px solid var(--border);
    border-radius: 10px;
    padding: 11px 14px;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: border-color .16s, background .16s;
    position: relative;
}
.emp-card-label input[type="radio"] {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
}
.emp-card-label:hover { border-color: var(--primary); background: rgba(212,129,58,.04); }
.emp-card-label.selected { border-color: var(--primary); background: rgba(212,129,58,.06); }
.emp-card-badge {
    padding: 3px 10px;
    border-radius: 999px;
    font-size: 11.5px;
    font-weight: 700;
}
.emp-full { background: #e0f2fe; color: #0369a1; }
.emp-part { background: #fef3c7; color: #b45309; }
.emp-card-meta { font-size: 12px; color: var(--text-muted); margin-top: 2px; }

/* ── Employment section toggle ───────────── */
#employment-section {
    overflow: hidden;
    transition: max-height .25s ease, opacity .25s ease, margin .25s ease;
    max-height: 0;
    opacity: 0;
    margin-top: 0;
}
#employment-section.visible {
    max-height: 160px;
    opacity: 1;
    margin-top: 0;
}

/* ── Form action row ─────────────────────── */
.form-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    padding-top: 20px;
    border-top: 1px solid var(--border);
    margin-top: 8px;
}

@media (max-width: 600px) {
    .form-grid-2 { grid-template-columns: 1fr; }
    .form-grid-2 .span-2 { grid-column: 1; }
    .role-cards { grid-template-columns: 1fr; }
    .emp-cards  { grid-template-columns: 1fr; }
}
</style>
@endsection

@section('content')

<div class="page-header">
    <div>
        <div class="page-header-title">Tạo tài khoản mới</div>
        <div class="page-header-sub">Thêm Nhân viên hoặc Khách hàng vào hệ thống</div>
    </div>
    <a href="{{ route('admin.users') }}" class="btn-outline-admin">
        <i class="fas fa-arrow-left"></i> Quay lại
    </a>
</div>

<div class="create-user-wrap">
    <div class="card">
        <div class="card-header">
            <div class="card-header-title">
                <i class="fas fa-user-plus" style="color:var(--primary);"></i>
                Thông tin tài khoản
            </div>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.users.store') }}" id="createUserForm">
                @csrf

                {{-- ── 1. Thông tin cơ bản ── --}}
                <div class="form-section-title">
                    <i class="fas fa-id-card"></i> Thông tin cơ bản
                </div>

                <div class="form-grid-2">
                    <div class="form-group span-2">
                        <label class="form-label">Họ và tên <span style="color:#e11d48;">*</span></label>
                        <div class="input-icon-wrap">
                            <i class="fas fa-user input-icon"></i>
                            <input type="text" name="name"
                                   class="form-control {{ $errors->has('name') ? 'border-danger' : '' }}"
                                   value="{{ old('name') }}" placeholder="Nguyễn Văn A" required>
                        </div>
                        @error('name')<div class="form-text form-text-error">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email <span style="color:#e11d48;">*</span></label>
                        <div class="input-icon-wrap">
                            <i class="fas fa-envelope input-icon"></i>
                            <input type="email" name="email"
                                   class="form-control {{ $errors->has('email') ? 'border-danger' : '' }}"
                                   value="{{ old('email') }}" placeholder="email@example.com" required>
                        </div>
                        @error('email')<div class="form-text form-text-error">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Số điện thoại</label>
                        <div class="input-icon-wrap">
                            <i class="fas fa-phone input-icon"></i>
                            <input type="text" name="phone" class="form-control"
                                   value="{{ old('phone') }}" placeholder="0901 234 567">
                        </div>
                        @error('phone')<div class="form-text form-text-error">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group span-2">
                        <label class="form-label">Căn cước công dân</label>
                        <div class="input-icon-wrap">
                            <i class="fas fa-id-badge input-icon"></i>
                            <input type="text" name="citizen_id"
                                   class="form-control {{ $errors->has('citizen_id') ? 'border-danger' : '' }}"
                                   value="{{ old('citizen_id') }}" inputmode="numeric"
                                   maxlength="12" pattern="[0-9]{12}" autocomplete="off"
                                   placeholder="12 chữ số">
                        </div>
                        @error('citizen_id')<div class="form-text form-text-error">{{ $message }}</div>@enderror
                    </div>
                </div>

                {{-- ── 2. Bảo mật ── --}}
                <div class="form-section-title">
                    <i class="fas fa-lock"></i> Mật khẩu
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label">Mật khẩu <span style="color:#e11d48;">*</span></label>
                        <div class="input-icon-wrap">
                            <i class="fas fa-key input-icon"></i>
                            <input type="password" name="password"
                                   class="form-control {{ $errors->has('password') ? 'border-danger' : '' }}"
                                   autocomplete="new-password" required>
                        </div>
                        @error('password')<div class="form-text form-text-error">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Xác nhận mật khẩu <span style="color:#e11d48;">*</span></label>
                        <div class="input-icon-wrap">
                            <i class="fas fa-key input-icon"></i>
                            <input type="password" name="password_confirmation"
                                   class="form-control" autocomplete="new-password" required>
                        </div>
                    </div>
                </div>

                {{-- ── 3. Phân quyền ── --}}
                <div class="form-section-title">
                    <i class="fas fa-shield-alt"></i> Phân quyền
                </div>

                @php
                    $staffRoleId    = optional($roles->firstWhere('name', 'staff'))->id;
                    $customerRoleId = optional($roles->firstWhere('name', 'customer'))->id;
                    $defaultRoleId  = old('role_id', $staffRoleId ?? $customerRoleId);
                @endphp

                <div class="form-group">
                    <label class="form-label">Loại tài khoản <span style="color:#e11d48;">*</span></label>
                    <div class="role-cards" id="roleCards">
                        @foreach($roles as $role)
                            @continue($role->name === 'admin')
                            <label class="role-card-label {{ (string)$defaultRoleId === (string)$role->id ? 'selected' : '' }}"
                                   data-role="{{ $role->name }}">
                                <input type="radio" name="role_id" value="{{ $role->id }}"
                                       {{ (string)$defaultRoleId === (string)$role->id ? 'checked' : '' }} required>
                                <span class="role-card-icon">{{ $role->name === 'staff' ? '👷' : '🧑' }}</span>
                                <div>
                                    <div class="role-card-name">
                                        {{ $role->name === 'staff' ? 'Nhân viên' : 'Khách hàng' }}
                                    </div>
                                    <div class="role-card-desc">
                                        {{ $role->name === 'staff'
                                            ? 'Xử lý đơn hàng, vận hành tại quán'
                                            : 'Đặt hàng qua app, tích điểm mua sắm' }}
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    @error('role_id')<div class="form-text form-text-error" style="margin-top:8px;">{{ $message }}</div>@enderror
                </div>

                {{-- ── 4. Hình thức làm việc (chỉ hiện khi chọn Staff) ── --}}
                <div id="employment-section"
                     class="{{ (string)$defaultRoleId === (string)$staffRoleId ? 'visible' : '' }}">
                    <div class="form-section-title" style="margin-top:16px;">
                        <i class="fas fa-briefcase"></i> Hình thức làm việc
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            Loại hợp đồng <span style="color:#e11d48;">*</span>
                        </label>
                        <div class="emp-cards" id="empCards">
                            <label class="emp-card-label {{ old('employment_type','full_time') === 'full_time' ? 'selected' : '' }}">
                                <input type="radio" name="employment_type" value="full_time"
                                       {{ old('employment_type','full_time') === 'full_time' ? 'checked' : '' }}>
                                <div>
                                    <span class="emp-card-badge emp-full">Full-time</span>
                                    <div class="emp-card-meta">Ca cố định - 8h</div>
                                </div>
                            </label>
                            <label class="emp-card-label {{ old('employment_type') === 'part_time' ? 'selected' : '' }}">
                                <input type="radio" name="employment_type" value="part_time"
                                       {{ old('employment_type') === 'part_time' ? 'checked' : '' }}>
                                <div>
                                    <span class="emp-card-badge emp-part">Part-time</span>
                                    <div class="emp-card-meta">Ca linh hoạt — 4h mỗi ca</div>
                                </div>
                            </label>
                        </div>
                        @error('employment_type')<div class="form-text form-text-error" style="margin-top:8px;">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('admin.users') }}" class="btn-outline-admin">Hủy</a>
                    <button type="submit" class="btn-primary-admin">
                        <i class="fas fa-user-plus"></i> Tạo tài khoản
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
(function () {
    var staffRoleId = '{{ $staffRoleId }}';

    // ── Role card selection ──
    document.querySelectorAll('#roleCards .role-card-label').forEach(function (label) {
        label.addEventListener('click', function () {
            document.querySelectorAll('#roleCards .role-card-label').forEach(function (l) {
                l.classList.remove('selected');
            });
            this.classList.add('selected');
            var input = this.querySelector('input[type="radio"]');
            if (input) input.checked = true;

            // Show/hide employment section
            var empSection = document.getElementById('employment-section');
            if (String(input.value) === String(staffRoleId)) {
                empSection.classList.add('visible');
            } else {
                empSection.classList.remove('visible');
            }
        });
    });

    // ── Employment type card selection ──
    document.querySelectorAll('#empCards .emp-card-label').forEach(function (label) {
        label.addEventListener('click', function () {
            document.querySelectorAll('#empCards .emp-card-label').forEach(function (l) {
                l.classList.remove('selected');
            });
            this.classList.add('selected');
            var input = this.querySelector('input[type="radio"]');
            if (input) input.checked = true;
        });
    });
})();
</script>
@endsection
