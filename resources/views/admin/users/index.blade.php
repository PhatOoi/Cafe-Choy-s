@extends('admin.layout')

@section('title', 'Quản lý tài khoản')
@section('page-title', 'Quản lý tài khoản')
@section('breadcrumb', 'Admin / Tài khoản')

@section('content')

<style>
    .role-wrap {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
    }

    .employment-note {
        display: inline-flex;
        align-items: center;
        padding: 3px 9px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        line-height: 1;
    }

    .employment-note-full {
        background: #e0f2fe;
        color: #0369a1;
    }

    .employment-note-part {
        background: #fef3c7;
        color: #b45309;
    }

    .employment-note-unknown {
        background: #f1f5f9;
        color: #475569;
    }
</style>

<div class="page-header">
    <div>
        <div class="page-header-title">Danh sách tài khoản</div>
        <div class="page-header-sub">Quản lý Admin, Nhân viên và Khách hàng</div>
    </div>
    <a href="{{ route('admin.users.create') }}" class="btn-primary-admin">
        <i class="fas fa-user-plus"></i> Thêm tài khoản
    </a>
</div>

<div class="card">
    @php
        $staffUsers = $users->filter(fn($u) => optional($u->role)->name === 'staff')->values();
        $customerUsers = $users->filter(fn($u) => optional($u->role)->name === 'customer')->values();
    @endphp

    {{-- Filter --}}
    <form method="GET" action="{{ route('admin.users') }}">
        <div class="filter-bar">
            <input type="text" name="search" class="form-control"
                   placeholder="🔍 Tìm tên, email, SĐT..." value="{{ request('search') }}"
                   style="min-width:220px;">

            <select name="role" class="form-select">
                <option value="">Tất cả role</option>
                @foreach($roles as $role)
                <option value="{{ $role->id }}" {{ request('role') == $role->id ? 'selected' : '' }}>
                    {{ $role->name === 'admin' ? '👑 Admin' : ($role->name === 'staff' ? '👷 Nhân viên' : '🧑 Khách hàng') }}
                </option>
                @endforeach
            </select>

            <select name="status" class="form-select">
                <option value="">Tất cả trạng thái</option>
                <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>✅ Đang hoạt động</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>🔒 Đã khóa</option>
            </select>

            <button type="submit" class="btn-primary-admin btn-sm">
                <i class="fas fa-filter"></i> Lọc
            </button>
            @if(request()->hasAny(['search','role','status']))
            <a href="{{ route('admin.users') }}" class="btn-outline-admin btn-sm">
                <i class="fas fa-times"></i> Xóa lọc
            </a>
            @endif
        </div>
    </form>

    {{-- Table --}}
    <div style="overflow-x:auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Tài khoản</th>
                    <th>Liên hệ</th>
                    <th>Role</th>
                    <th>Trạng thái</th>
                    <th>Ngày tạo</th>
                    <th style="text-align:right;">Hành động</th>
                </tr>
            </thead>
            <tbody>
                @if($staffUsers->isNotEmpty())
                <tr>
                    <td colspan="6" style="background:#eff6ff;color:#1e3a8a;font-weight:700;">👷 Danh sách nhân viên</td>
                </tr>
                @foreach($staffUsers as $user)
                <tr>
                    <td>
                        <div class="user-cell">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background={{ optional($user->role)->name === 'admin' ? 'd97706' : (optional($user->role)->name === 'staff' ? '2563eb' : '16a34a') }}&color=fff&size=72"
                                 class="user-avatar" alt="{{ $user->name }}">
                            <div>
                                <div class="user-cell-name">
                                    {{ $user->name }}
                                    @if($user->id === Auth::id())
                                    <span style="font-size:10px;color:var(--primary);font-weight:700;">(bạn)</span>
                                    @endif
                                </div>
                                <div class="user-cell-email">{{ $user->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td style="color:var(--text-muted);font-size:13px;">{{ $user->phone ?? '—' }}</td>
                    <td>
                        @php($roleName = optional($user->role)->name)
                        <div class="role-wrap">
                            <span class="badge {{ $roleName === 'admin' ? 'badge-admin' : ($roleName === 'staff' ? 'badge-staff' : 'badge-customer') }}">
                                {{ $roleName === 'admin' ? '👑 Admin' : ($roleName === 'staff' ? '👷 Nhân viên' : '🧑 Khách hàng') }}
                            </span>
                            @if($roleName === 'staff')
                                <span class="employment-note {{ $user->employment_type === 'full_time' ? 'employment-note-full' : ($user->employment_type === 'part_time' ? 'employment-note-part' : 'employment-note-unknown') }}">
                                    {{ $user->employment_type === 'full_time' ? 'Full-time' : ($user->employment_type === 'part_time' ? 'Part-time' : 'Chưa phân loại') }}
                                </span>
                            @endif
                        </div>
                    </td>
                    <td>
                        <span class="badge {{ $user->is_active ? 'badge-active' : 'badge-inactive' }}">
                            {{ $user->is_active ? '✅ Hoạt động' : '🔒 Đã khóa' }}
                        </span>
                    </td>
                    <td style="color:var(--text-muted);font-size:12.5px;">{{ $user->created_at->format('d/m/Y') }}</td>
                    <td>
                        <div style="display:flex;gap:6px;justify-content:flex-end;flex-wrap:wrap;">
                            <a href="{{ route('admin.users.edit', $user->id) }}" class="btn-edit btn-sm">
                                <i class="fas fa-pen"></i> Sửa
                            </a>

                            @if($user->id !== Auth::id())
                            {{-- Toggle active --}}
                            <form method="POST" action="{{ route('admin.users.toggle', $user->id) }}" style="margin:0;">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn-warning btn-sm"
                                        onclick="return confirm('{{ $user->is_active ? 'Khóa' : 'Mở khóa' }} tài khoản {{ $user->name }}?')">
                                    <i class="fas fa-{{ $user->is_active ? 'lock' : 'lock-open' }}"></i>
                                    {{ $user->is_active ? 'Khóa' : 'Mở' }}
                                </button>
                            </form>

                            {{-- Delete --}}
                            <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}" style="margin:0;">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-danger btn-sm"
                                        onclick="return confirm('Xóa tài khoản {{ $user->name }}? Không thể hoàn tác!')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
                @endif

                @if($customerUsers->isNotEmpty())
                <tr>
                    <td colspan="6" style="background:#f0fdf4;color:#166534;font-weight:700;">🧑 Danh sách khách hàng</td>
                </tr>
                @foreach($customerUsers as $user)
                <tr>
                    <td>
                        <div class="user-cell">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background={{ optional($user->role)->name === 'admin' ? 'd97706' : (optional($user->role)->name === 'staff' ? '2563eb' : '16a34a') }}&color=fff&size=72"
                                 class="user-avatar" alt="{{ $user->name }}">
                            <div>
                                <div class="user-cell-name">
                                    {{ $user->name }}
                                    @if($user->id === Auth::id())
                                    <span style="font-size:10px;color:var(--primary);font-weight:700;">(bạn)</span>
                                    @endif
                                </div>
                                <div class="user-cell-email">{{ $user->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td style="color:var(--text-muted);font-size:13px;">{{ $user->phone ?? '—' }}</td>
                    <td>
                        @php($roleName = optional($user->role)->name)
                        <div class="role-wrap">
                            <span class="badge {{ $roleName === 'admin' ? 'badge-admin' : ($roleName === 'staff' ? 'badge-staff' : 'badge-customer') }}">
                                {{ $roleName === 'admin' ? '👑 Admin' : ($roleName === 'staff' ? '👷 Nhân viên' : '🧑 Khách hàng') }}
                            </span>
                            @if($roleName === 'staff')
                                <span class="employment-note {{ $user->employment_type === 'full_time' ? 'employment-note-full' : ($user->employment_type === 'part_time' ? 'employment-note-part' : 'employment-note-unknown') }}">
                                    {{ $user->employment_type === 'full_time' ? 'Full-time' : ($user->employment_type === 'part_time' ? 'Part-time' : 'Chưa phân loại') }}
                                </span>
                            @endif
                        </div>
                    </td>
                    <td>
                        <span class="badge {{ $user->is_active ? 'badge-active' : 'badge-inactive' }}">
                            {{ $user->is_active ? '✅ Hoạt động' : '🔒 Đã khóa' }}
                        </span>
                    </td>
                    <td style="color:var(--text-muted);font-size:12.5px;">{{ $user->created_at->format('d/m/Y') }}</td>
                    <td>
                        <div style="display:flex;gap:6px;justify-content:flex-end;flex-wrap:wrap;">
                            <a href="{{ route('admin.users.edit', $user->id) }}" class="btn-edit btn-sm">
                                <i class="fas fa-pen"></i> Sửa
                            </a>

                            @if($user->id !== Auth::id())
                            {{-- Toggle active --}}
                            <form method="POST" action="{{ route('admin.users.toggle', $user->id) }}" style="margin:0;">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn-warning btn-sm"
                                        onclick="return confirm('{{ $user->is_active ? 'Khóa' : 'Mở khóa' }} tài khoản {{ $user->name }}?')">
                                    <i class="fas fa-{{ $user->is_active ? 'lock' : 'lock-open' }}"></i>
                                    {{ $user->is_active ? 'Khóa' : 'Mở' }}
                                </button>
                            </form>

                            {{-- Delete --}}
                            <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}" style="margin:0;">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-danger btn-sm"
                                        onclick="return confirm('Xóa tài khoản {{ $user->name }}? Không thể hoàn tác!')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
                @endif

                @if($staffUsers->isEmpty() && $customerUsers->isEmpty())
                <tr>
                    <td colspan="6" style="text-align:center;padding:40px;color:var(--text-muted);">
                        <i class="fas fa-search" style="font-size:36px;margin-bottom:12px;display:block;opacity:.3;"></i>
                        Không tìm thấy tài khoản nào
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($users->hasPages())
    <div style="padding:16px 22px;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
        <div style="font-size:13px;color:var(--text-muted);">
            Hiển thị {{ $users->firstItem() }}–{{ $users->lastItem() }} / {{ $users->total() }} tài khoản
        </div>
        {{ $users->withQueryString()->links() }}
    </div>
    @endif
</div>

@endsection
