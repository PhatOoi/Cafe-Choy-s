@extends('admin.layout')

@section('title', 'Sửa tài khoản')
@section('page-title', 'Sửa tài khoản')
@section('breadcrumb', 'Admin / Tài khoản / Chỉnh sửa')

@section('styles')
<style>
.edit-user-wrap { max-width: 760px; margin: 0 auto; }
.form-section-title {
    font-size: 11.5px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase;
    color: var(--primary); padding: 0 0 10px;
    border-bottom: 1.5px solid rgba(212,129,58,.18);
    margin: 24px 0 18px; display: flex; align-items: center; gap: 8px;
}
.form-section-title:first-child { margin-top: 0; }
.input-icon-wrap { position: relative; }
.input-icon-wrap .input-icon {
    position: absolute; left: 13px; top: 50%; transform: translateY(-50%);
    color: #b0b9c8; font-size: 13px; pointer-events: none;
}
.input-icon-wrap .form-control,
.input-icon-wrap .form-select { padding-left: 36px; }
.form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.form-grid-2 .span-2 { grid-column: 1 / -1; }
.emp-cards { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.emp-card-label {
    cursor: pointer; border: 2px solid var(--border); border-radius: 10px;
    padding: 11px 14px; display: flex; align-items: center; gap: 10px;
    transition: border-color .16s, background .16s; position: relative;
}
.emp-card-label input[type="radio"] { position: absolute; opacity: 0; width: 0; height: 0; }
.emp-card-label:hover { border-color: var(--primary); background: rgba(212,129,58,.04); }
.emp-card-label.selected { border-color: var(--primary); background: rgba(212,129,58,.06); }
.emp-card-badge { padding: 3px 10px; border-radius: 999px; font-size: 11.5px; font-weight: 700; }
.emp-full { background: #e0f2fe; color: #0369a1; }
.emp-part { background: #fef3c7; color: #b45309; }
.emp-card-meta { font-size: 12px; color: var(--text-muted); margin-top: 2px; }
.form-actions {
    display: flex; gap: 10px; justify-content: flex-end;
    padding-top: 20px; border-top: 1px solid var(--border); margin-top: 8px;
}
@media (max-width: 600px) {
    .form-grid-2 { grid-template-columns: 1fr; }
    .form-grid-2 .span-2 { grid-column: 1; }
    .emp-cards { grid-template-columns: 1fr; }
}
</style>
@endsection

@section('content')

<div class="page-header">
    <div>
        <div class="page-header-title">Chỉnh sửa tài khoản</div>
        <div class="page-header-sub">{{ $user->email }}</div>
    </div>
    <a href="{{ route('admin.users') }}" class="btn-outline-admin">
        <i class="fas fa-arrow-left"></i> Quay lại
    </a>
</div>

<div class="edit-user-wrap">
    <div class="card">
        <div class="card-header">
            <div class="card-header-title">
                <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=d4813a&color=fff&size=72"
                     style="width:30px;height:30px;border-radius:50%;" alt="">
                {{ $user->name }}
            </div>
            <span class="badge {{ $user->is_active ? 'badge-active' : 'badge-inactive' }}">
                {{ $user->is_active ? '✅ Hoạt động' : '🔒 Đã khóa' }}
            </span>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.users.update', $user->id) }}" id="editUserForm">
                @csrf @method('PUT')

                {{-- ── 1. Thông tin cơ bản ── --}}
                <div class="form-section-title">
                    <i class="fas fa-id-card"></i> Thông tin cơ bản
                </div>

                <div class="form-grid-2">
                    <div class="form-group span-2">
                        <label class="form-label">Họ và tên <span style="color:#e11d48;">*</span></label>
                        <div class="input-icon-wrap">
                            <i class="fas fa-user input-icon"></i>
                            <input type="text" name="name" class="form-control"
                                   value="{{ old('name', $user->name) }}" required>
                        </div>
                        @error('name')<div class="form-text form-text-error">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email <span style="color:#e11d48;">*</span></label>
                        <div class="input-icon-wrap">
                            <i class="fas fa-envelope input-icon"></i>
                            <input type="email" name="email" class="form-control"
                                   value="{{ old('email', $user->email) }}" required>
                        </div>
                        @error('email')<div class="form-text form-text-error">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Số điện thoại</label>
                        <div class="input-icon-wrap">
                            <i class="fas fa-phone input-icon"></i>
                            <input type="text" name="phone" class="form-control"
                                   value="{{ old('phone', $user->phone) }}">
                        </div>
                    </div>

                    <div class="form-group span-2">
                        <label class="form-label">Căn cước công dân</label>
                        <div class="input-icon-wrap">
                            <i class="fas fa-id-badge input-icon"></i>
                            <input type="text" name="citizen_id"
                                   class="form-control {{ $errors->has('citizen_id') ? 'border-danger' : '' }}"
                                   value="{{ old('citizen_id', $user->citizen_id) }}"
                                   inputmode="numeric" maxlength="12" pattern="[0-9]{12}" autocomplete="off">
                        </div>
                        @error('citizen_id')<div class="form-text form-text-error">{{ $message }}</div>@enderror
                    </div>
                </div>

                {{-- ── 2. Bảo mật ── --}}
                <div class="form-section-title">
                    <i class="fas fa-lock"></i> Đổi mật khẩu
                    <span style="font-size:11px;font-weight:400;color:var(--text-muted);text-transform:none;letter-spacing:0;">(bỏ trống nếu không thay đổi)</span>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label">Mật khẩu mới</label>
                        <div class="input-icon-wrap">
                            <i class="fas fa-key input-icon"></i>
                            <input type="password" name="password" class="form-control" autocomplete="new-password">
                        </div>
                        @error('password')<div class="form-text form-text-error">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Xác nhận mật khẩu mới</label>
                        <div class="input-icon-wrap">
                            <i class="fas fa-key input-icon"></i>
                            <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password">
                        </div>
                    </div>
                </div>

                {{-- ── 3. Phân quyền ── --}}
                <div class="form-section-title">
                    <i class="fas fa-shield-alt"></i> Phân quyền
                </div>

                <div class="form-group">
                    <label class="form-label">Loại tài khoản <span style="color:#e11d48;">*</span></label>
                    <select name="role_id" class="form-select"
                            {{ $user->id === Auth::id() ? 'disabled' : '' }}>
                        @foreach($roles->whereNotIn('name', ['admin']) as $role)
                        <option value="{{ $role->id }}" {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>
                            {{ $role->name === 'staff' ? '👷 Nhân viên — Vận hành đơn hàng' : '🧑 Khách hàng — Đặt hàng và mua sắm' }}
                        </option>
                        @endforeach
                    </select>
                    @if($user->id === Auth::id())
                        <div class="form-text form-text-warning">⚠️ Bạn không thể thay đổi role của chính mình</div>
                        <input type="hidden" name="role_id" value="{{ $user->role_id }}">
                    @endif
                    @error('role_id')<div class="form-text form-text-error">{{ $message }}</div>@enderror
                </div>

                {{-- ── 4. Hình thức làm việc ── --}}
                <div class="form-section-title" style="margin-top:16px;">
                    <i class="fas fa-briefcase"></i> Hình thức làm việc
                </div>

                <div class="form-group">
                    <div class="emp-cards" id="empCardsEdit">
                        @php $currentEmp = old('employment_type', $user->employment_type); @endphp
                        <label class="emp-card-label {{ $currentEmp === 'full_time' ? 'selected' : '' }}">
                            <input type="radio" name="employment_type" value="full_time"
                                   {{ $currentEmp === 'full_time' ? 'checked' : '' }}>
                            <div>
                                <span class="emp-card-badge emp-full">Full-time</span>
                                <div class="emp-card-meta">Ca cố định — 8h hoặc 16h</div>
                            </div>
                        </label>
                        <label class="emp-card-label {{ $currentEmp === 'part_time' ? 'selected' : '' }}">
                            <input type="radio" name="employment_type" value="part_time"
                                   {{ $currentEmp === 'part_time' ? 'checked' : '' }}>
                            <div>
                                <span class="emp-card-badge emp-part">Part-time</span>
                                <div class="emp-card-meta">Ca linh hoạt — 4h mỗi ca</div>
                            </div>
                        </label>
                    </div>
                    <div class="form-text" style="margin-top:8px;">Chỉ áp dụng cho tài khoản nhân viên. Có thể bỏ trống với khách hàng.</div>
                    @error('employment_type')<div class="form-text form-text-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-actions">
                    <a href="{{ route('admin.users') }}" class="btn-outline-admin">Hủy</a>
                    <button type="submit" class="btn-primary-admin">
                        <i class="fas fa-save"></i> Lưu thay đổi
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
    document.querySelectorAll('#empCardsEdit .emp-card-label').forEach(function (label) {
        label.addEventListener('click', function () {
            document.querySelectorAll('#empCardsEdit .emp-card-label').forEach(function (l) {
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
