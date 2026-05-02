@extends('admin.layout')

@section('title', 'Quản lý sản phẩm')
@section('page-title', 'Sản phẩm')
@section('breadcrumb', 'Admin / Sản phẩm')

@section('content')

<div class="page-header">
    <div>
        <div class="page-header-title">Danh sách sản phẩm</div>
        <div class="page-header-sub">{{ $products->count() }} sản phẩm trong hệ thống</div>
    </div>
    <a href="{{ route('admin.products.create') }}" class="btn-primary-admin">
        <i class="fas fa-plus"></i> Thêm sản phẩm
    </a>
</div>

<div class="card">
    <form method="GET" action="{{ route('admin.products') }}">
        <div class="filter-bar">
            <input type="text" name="search" class="form-control"
                   placeholder="🔍 Tìm tên sản phẩm..." value="{{ request('search') }}"
                   style="min-width:200px;">

            <select name="category" class="form-select">
                <option value="">Tất cả danh mục</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                    {{ $cat->name }}
                </option>
                @endforeach
            </select>

            <select name="status" class="form-select">
                <option value="">Tất cả trạng thái</option>
                <option value="available"   {{ request('status') === 'available'   ? 'selected' : '' }}>✅ Đang bán</option>
                <option value="unavailable" {{ request('status') === 'unavailable' ? 'selected' : '' }}>⏸ Tạm ngưng</option>
            </select>

            <button type="submit" class="btn-primary-admin btn-sm">
                <i class="fas fa-filter"></i> Lọc
            </button>
            @if(request()->hasAny(['search','category','status']))
            <a href="{{ route('admin.products') }}" class="btn-outline-admin btn-sm">
                <i class="fas fa-times"></i> Xóa lọc
            </a>
            @endif
        </div>
    </form>

    <div style="overflow-x:auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Sản phẩm</th>
                    <th>Danh mục</th>
                    <th>Giá</th>
                    <th>Trạng thái</th>
                    <th style="text-align:right;">Hành động</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:12px;">
                            @if($product->image_url)
                                <img src="{{ asset('images/' . $product->image_url) }}"
                                    alt="{{ $product->name }}"
                                    onerror="this.src='https://via.placeholder.com/100x100?text=No+Image'"
                                    style="width:44px;height:44px;border-radius:10px;object-fit:cover;border:1px solid var(--border);">
                                @else
                            <div style="width:44px;height:44px;border-radius:10px;background:var(--primary-light);display:flex;align-items:center;justify-content:center;font-size:20px;">☕</div>
                            @endif
                            <div>
                                <div style="font-weight:600;font-size:13.5px;">{{ $product->name }}</div>
                                @if($product->description)
                                <div style="font-size:11.5px;color:var(--text-muted);max-width:250px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                    {{ $product->description }}
                                </div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>
                        <span style="background:#f3f4f8;color:var(--text-muted);font-size:12px;padding:3px 10px;border-radius:20px;font-weight:500;">
                            {{ $product->category->name ?? '—' }}
                        </span>
                    </td>
                    <td style="font-weight:700;color:var(--primary);white-space:nowrap;">
                        {{ number_format($product->price, 0, ',', '.') }}đ
                    </td>
                    <td>
                        <span class="badge {{ $product->status === 'available' ? 'badge-active' : 'badge-inactive' }}">
                            {{ $product->status === 'available' ? '✅ Đang bán' : '⏸ Tạm ngưng' }}
                        </span>
                    </td>
                    <td>
                        <div style="display:flex;gap:6px;justify-content:flex-end;">
                            <a href="{{ route('admin.products.edit', $product->id) }}" class="btn-edit btn-sm">
                                <i class="fas fa-pen"></i> Sửa
                            </a>
                            <form method="POST" action="{{ route('admin.products.destroy', $product->id) }}" style="margin:0;">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-danger btn-sm"
                                        onclick="return confirm('Xóa sản phẩm \'{{ $product->name }}\'?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align:center;padding:40px;color:var(--text-muted);">
                        <div style="font-size:40px;margin-bottom:12px;">☕</div>
                        Không có sản phẩm nào. <a href="{{ route('admin.products.create') }}" style="color:var(--primary);">Thêm ngay!</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>


</div>

@endsection
