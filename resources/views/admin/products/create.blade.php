@extends('admin.layout')

@section('title', 'Thêm sản phẩm')
@section('page-title', 'Thêm sản phẩm')
@section('breadcrumb', 'Admin / Sản phẩm / Thêm mới')

@section('content')

<div class="page-header">
    <div>
        <div class="page-header-title">Thêm sản phẩm mới</div>
        <div class="page-header-sub">Điền đầy đủ thông tin sản phẩm</div>
    </div>
    <a href="{{ route('admin.products') }}" class="btn-outline-admin">
        <i class="fas fa-arrow-left"></i> Quay lại
    </a>
</div>

<div style="display:grid;grid-template-columns:1fr 320px;gap:20px;align-items:start;">

    <div class="card">
        <div class="card-header">
            <div class="card-header-title"><i class="fas fa-coffee" style="color:var(--primary);"></i> Thông tin sản phẩm</div>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data" id="productForm">
                @csrf

                <div class="form-group">
                    <label class="form-label">Tên sản phẩm <span style="color:#e11d48;">*</span></label>
                    <input type="text" name="name" class="form-control"
                           value="{{ old('name') }}" placeholder="VD: Cà phê sữa đá" required>
                    @error('name')<div class="form-text" style="color:#dc2626;">{{ $message }}</div>@enderror
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                    <div class="form-group">
                        <label class="form-label">Danh mục <span style="color:#e11d48;">*</span></label>
                        <select name="category_id" class="form-select" required>
                            <option value="">Chọn danh mục</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('category_id')<div class="form-text" style="color:#dc2626;">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Giá bán (đ) <span style="color:#e11d48;">*</span></label>
                        <input type="number" name="price" class="form-control"
                               value="{{ old('price') }}" placeholder="35000" min="0" step="500" required>
                        @error('price')<div class="form-text" style="color:#dc2626;">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Mô tả</label>
                    <textarea name="description" class="form-control" rows="3"
                              placeholder="Mô tả ngắn về sản phẩm...">{{ old('description') }}</textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Hình ảnh sản phẩm</label>
                    <input type="file" name="image" class="form-control" accept="image/*"
                        onchange="previewFile(this)">
                </div>
                <div class="form-group">
                    <label class="form-label">Trạng thái <span style="color:#e11d48;">*</span></label>
                    <select name="status" class="form-select" required>
                        <option value="available" {{ old('status') !== 'unavailable' ? 'selected' : '' }}>✅ Đang bán</option>
                        <option value="unavailable" {{ old('status') === 'unavailable' ? 'selected' : '' }}>⏸ Tạm ngưng</option>
                    </select>
                </div>

                <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:8px;">
                    <a href="{{ route('admin.products') }}" class="btn-outline-admin">Hủy</a>
                    <button type="submit" class="btn-primary-admin">
                        <i class="fas fa-save"></i> Lưu sản phẩm
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Preview card --}}
    <div class="card" style="position:sticky;top:80px;">
        <div class="card-header">
            <div class="card-header-title"><i class="fas fa-eye" style="color:var(--primary);"></i> Preview</div>
        </div>
        <div class="card-body" style="text-align:center;">
            <div id="imgPreview" style="width:100%;height:160px;border-radius:12px;background:#f8f9fc;display:flex;align-items:center;justify-content:center;font-size:48px;margin-bottom:12px;overflow:hidden;border:1px solid var(--border);">
                ☕
            </div>
            <div style="font-weight:700;font-size:15px;" id="previewName">Tên sản phẩm</div>
            <div style="color:var(--primary);font-weight:800;font-size:18px;margin-top:4px;" id="previewPrice">—</div>
            <div style="font-size:12px;color:var(--text-muted);margin-top:6px;" id="previewCat">Danh mục</div>
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
    } else {
        wrap.innerHTML = '☕';
    }
}

// Live preview
document.querySelector('[name="name"]').addEventListener('input', function() {
    document.getElementById('previewName').textContent = this.value || 'Tên sản phẩm';
});
document.querySelector('[name="price"]').addEventListener('input', function() {
    if (this.value) {
        document.getElementById('previewPrice').textContent = parseInt(this.value).toLocaleString('vi-VN') + 'đ';
    } else {
        document.getElementById('previewPrice').textContent = '—';
    }
});
document.querySelector('[name="category_id"]').addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    document.getElementById('previewCat').textContent = opt.value ? opt.text : 'Danh mục';
});
</script>
@endsection
