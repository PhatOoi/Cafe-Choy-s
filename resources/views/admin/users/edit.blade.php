@extends('admin.layout')

@section('title', 'Sửa tài khoản')
@section('page-title', 'Sửa tài khoản')
@section('breadcrumb', 'Admin / Tài khoản / Chỉnh sửa')

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

<div style="max-width:680px;">
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
            <form method="POST" action="{{ route('admin.users.update', $user->id) }}">
                @csrf @method('PUT')

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                    <div class="form-group" style="grid-column:1/-1;">
                        <label class="form-label">Họ và tên <span style="color:#e11d48;">*</span></label>
                        <input type="text" name="name" class="form-control"
                               value="{{ old('name', $user->name) }}" required>
                        @error('name')<div class="form-text form-text-error">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email <span style="color:#e11d48;">*</span></label>
                        <input type="email" name="email" class="form-control"
                               value="{{ old('email', $user->email) }}" required>
                        @error('email')<div class="form-text form-text-error">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Số điện thoại</label>
                        <input type="text" name="phone" class="form-control"
                               value="{{ old('phone', $user->phone) }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Căn cước công dân</label>
                        <input type="text" name="citizen_id" class="form-control {{ $errors->has('citizen_id') ? 'border-danger' : '' }}"
                               value="{{ old('citizen_id', $user->citizen_id) }}" inputmode="numeric" maxlength="12" pattern="[0-9]{12}" autocomplete="off">
                        @error('citizen_id')<div class="form-text form-text-error">{{ $message }}</div>@enderror
                    </div>

                    <div style="grid-column:1/-1;display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                        <div class="form-group">
                            <label class="form-label">Mật khẩu mới</label>
                            <input type="password" name="password" class="form-control" autocomplete="new-password">
                            @error('password')<div class="form-text form-text-error">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Xác nhận mật khẩu mới</label>
                            <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password">
                        </div>
                    </div>

                    <div class="form-group" style="grid-column:1/-1;">
                        <label class="form-label">Phân quyền <span style="color:#e11d48;">*</span></label>
                        <select name="role_id" class="form-select"
                                {{ $user->id === Auth::id() ? 'disabled' : '' }}>
                            @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>
                                {{ $role->name === 'admin' ? '👑 Admin' : ($role->name === 'staff' ? '👷 Nhân viên' : '🧑 Khách hàng') }}
                            </option>
                            @endforeach
                        </select>
                        @if($user->id === Auth::id())
                        <div class="form-text form-text-warning">⚠️ Bạn không thể thay đổi role của chính mình</div>
                        {{-- Hidden input để giữ value khi disabled --}}
                        <input type="hidden" name="role_id" value="{{ $user->role_id }}">
                        @endif
                    </div>

                    <div class="form-group" style="grid-column:1/-1;">
                        <label class="form-label">Hình thức làm việc</label>
                        <select name="employment_type" class="form-select">
                            <option value="">Không áp dụng / Chưa chọn</option>
                            <option value="full_time" {{ old('employment_type', $user->employment_type) === 'full_time' ? 'selected' : '' }}>Full-time</option>
                            <option value="part_time" {{ old('employment_type', $user->employment_type) === 'part_time' ? 'selected' : '' }}>Part-time</option>
                        </select>
                        <div class="form-text">Chỉ áp dụng cho tài khoản nhân viên.</div>
                        @error('employment_type')<div class="form-text form-text-error">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:8px;">
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
