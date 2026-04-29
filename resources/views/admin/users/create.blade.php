@extends('admin.layout')

@section('title', 'Thêm tài khoản mới')
@section('page-title', 'Thêm tài khoản')
@section('breadcrumb', 'Admin / Tài khoản / Thêm mới')

@section('content')

<div class="page-header">
    <div>
        <div class="page-header-title">Tạo tài khoản mới</div>
        <div class="page-header-sub">Thêm Admin, Nhân viên hoặc Khách hàng</div>
    </div>
    <a href="{{ route('admin.users') }}" class="btn-outline-admin">
        <i class="fas fa-arrow-left"></i> Quay lại
    </a>
</div>

<div style="max-width:680px;">
    <div class="card">
        <div class="card-header">
            <div class="card-header-title">
                <i class="fas fa-user-plus" style="color:var(--primary);"></i>
                Thông tin tài khoản
            </div>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.users.store') }}">
                @csrf

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                    <div class="form-group" style="grid-column:1/-1;">
                        <label class="form-label">Họ và tên <span style="color:#e11d48;">*</span></label>
                        <input type="text" name="name" class="form-control {{ $errors->has('name') ? 'border-danger' : '' }}"
                               value="{{ old('name') }}" placeholder="Nguyễn Văn A" required>
                        @error('name')<div class="form-text" style="color:#dc2626;">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email <span style="color:#e11d48;">*</span></label>
                        <input type="email" name="email" class="form-control {{ $errors->has('email') ? 'border-danger' : '' }}"
                               value="{{ old('email') }}" placeholder="email@example.com" required>
                        @error('email')<div class="form-text" style="color:#dc2626;">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Số điện thoại</label>
                        <input type="text" name="phone" class="form-control"
                               value="{{ old('phone') }}" placeholder="0901234567">
                        @error('phone')<div class="form-text" style="color:#dc2626;">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Căn cước công dân</label>
                        <input type="text" name="citizen_id" class="form-control {{ $errors->has('citizen_id') ? 'border-danger' : '' }}"
                               value="{{ old('citizen_id') }}" inputmode="numeric" maxlength="12" pattern="[0-9]{12}" autocomplete="off">
                        @error('citizen_id')<div class="form-text" style="color:#dc2626;">{{ $message }}</div>@enderror
                    </div>

                    <div style="grid-column:1/-1;display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                        <div class="form-group">
                            <label class="form-label">Mật khẩu <span style="color:#e11d48;">*</span></label>
                            <input type="password" name="password" class="form-control {{ $errors->has('password') ? 'border-danger' : '' }}" autocomplete="new-password" required>
                            @error('password')<div class="form-text" style="color:#dc2626;">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Xác nhận mật khẩu <span style="color:#e11d48;">*</span></label>
                            <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password" required>
                        </div>
                    </div>

                    <div class="form-group" style="grid-column:1/-1;">
                        <label class="form-label">Phân quyền <span style="color:#e11d48;">*</span></label>
                        <select name="role_id" class="form-select" required>
                            @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ old('role_id', 3) == $role->id ? 'selected' : '' }}>
                                {{ $role->name === 'admin' ? '👑 Admin — Toàn quyền hệ thống' : ($role->name === 'staff' ? '👷 Nhân viên — Vận hành đơn hàng' : '🧑 Khách hàng — Đặt hàng và mua sắm') }}
                            </option>
                            @endforeach
                        </select>
                        <div class="form-text">Chọn cẩn thận — Admin có toàn quyền hệ thống</div>
                        @error('role_id')<div class="form-text" style="color:#dc2626;">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group" style="grid-column:1/-1;">
                        <label class="form-label">Hình thức làm việc</label>
                        <select name="employment_type" class="form-select">
                            <option value="">Không áp dụng / Chưa chọn</option>
                            <option value="full_time" {{ old('employment_type') === 'full_time' ? 'selected' : '' }}>Full-time</option>
                            <option value="part_time" {{ old('employment_type') === 'part_time' ? 'selected' : '' }}>Part-time</option>
                        </select>
                        <div class="form-text">Chỉ bắt buộc khi tạo tài khoản có role là nhân viên.</div>
                        @error('employment_type')<div class="form-text" style="color:#dc2626;">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:8px;">
                    <a href="{{ route('admin.users') }}" class="btn-outline-admin">Hủy</a>
                    <button type="submit" class="btn-primary-admin">
                        <i class="fas fa-save"></i> Tạo tài khoản
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
