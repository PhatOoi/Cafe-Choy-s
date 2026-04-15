@extends('admin.layout')

@section('title', 'Sửa sản phẩm')
@section('page-title', 'Sửa sản phẩm')
@section('breadcrumb', 'Admin / Sản phẩm / Chỉnh sửa')

@section('content')

<div class="page-header">
    <div>
        <div class="page-header-title">Chỉnh sửa: {{ $product->name }}</div>
    </div>
    <a href="{{ route('admin.products') }}" class="btn-outline-admin">
        <i class="fas fa-arrow-left"></i> Quay lại
    </a>
</div>

<div style="display:grid;grid-template-columns:1fr 320px;gap:20px;align-items:start;">
    <div class="card">
        <div class="card-header">
            <div class="card-header-title"><i class="fas fa-pen" style="color:var(--primary);"></i> Thông tin sản phẩm</div>
        </div>
        <div class="card-body">
           <form method="POST" action="{{ route('admin.products.update', $product->id) }}" enctype="multipart/form-data">
                @csrf @method('PUT')

                <div class="form-group">
                    <label class="form-label">Tên sản phẩm <span style="color:#e11d48;">*</span></label>
                    <input type="text" name="name" class="form-control"
                           value="{{ old('name', $product->name) }}" required>
                    @error('name')<div class="form-text" style="color:#dc2626;">{{ $message }}</div>@enderror
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                    <div class="form-group">
                        <label class="form-label">Danh mục <span style="color:#e11d48;">*</span></label>
                        <select name="category_id" class="form-select" required>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Giá bán (đ) <span style="color:#e11d48;">*</span></label>
                        <input type="number" name="price" class="form-control"
                               value="{{ old('price', $product->price) }}" min="0" step="500" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Mô tả</label>
                    <textarea name="description" class="form-control" rows="3">{{ old('description', $product->description) }}</textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Hình ảnh sản phẩm</label>
                    <input type="file" name="image" class="form-control" accept="image/*"
                        onchange="previewFile(this)">
                </div>

                <div class="form-group">
                    <label class="form-label">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="available"   {{ old('status', $product->status) === 'available'   ? 'selected' : '' }}>✅ Đang bán</option>
                        <option value="unavailable" {{ old('status', $product->status) === 'unavailable' ? 'selected' : '' }}>⏸ Tạm ngưng</option>
                    </select>
                </div>

                <div style="display:flex;gap:10px;justify-content:space-between;margin-top:8px;flex-wrap:wrap;">
                    <form method="POST" action="{{ route('admin.products.destroy', $product->id) }}">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-danger"
                                onclick="return confirm('Xóa sản phẩm này? Không thể hoàn tác!')">
                            <i class="fas fa-trash"></i> Xóa sản phẩm
                        </button>
                    </form>
                    <div style="display:flex;gap:10px;">
                        <a href="{{ route('admin.products') }}" class="btn-outline-admin">Hủy</a>
                        <button type="submit" class="btn-primary-admin">
                            <i class="fas fa-save"></i> Lưu thay đổi
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card" style="position:sticky;top:80px;">
        <div class="card-header"><div class="card-header-title"><i class="fas fa-eye" style="color:var(--primary);"></i> Preview</div></div>
        <div class="card-body" style="text-align:center;">
            <div id="imgPreview" style="width:100%;height:160px;border-radius:12px;background:#f8f9fc;display:flex;align-items:center;justify-content:center;font-size:48px;margin-bottom:12px;overflow:hidden;border:1px solid var(--border);">
               @if($product->image_url)
                <img src="{{ asset('images/' . $product->image_url) }}" 
                    style="width:100%;height:100%;object-fit:cover;">
                @else
                ☕
                @endif
            </div>
            <div style="font-weight:700;font-size:15px;">{{ $product->name }}</div>
            <div style="color:var(--primary);font-weight:800;font-size:18px;margin-top:4px;">
                {{ number_format($product->price, 0, ',', '.') }}đ
            </div>
            <div style="font-size:12px;color:var(--text-muted);margin-top:6px;">{{ $product->category->name ?? '—' }}</div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
function previewFile(input) {
    const wrap = document.getElementById('imgPreview');

    if (input.files && input.files[0]) {
        const reader = new FileReader();

        reader.onload = function(e) {
            wrap.innerHTML = `<img src="${e.target.result}" 
                style="width:100%;height:100%;object-fit:cover;">`;
        }

        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection
