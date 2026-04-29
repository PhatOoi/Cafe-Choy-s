@extends('admin.layout')

@section('title', 'Danh mục sản phẩm')
@section('page-title', 'Danh mục')
@section('breadcrumb', 'Admin / Danh mục')

@section('content')

<div class="page-header">
    <div>
        <div class="page-header-title">Danh mục sản phẩm</div>
        <div class="page-header-sub">Phân loại sản phẩm theo nhóm</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 380px;gap:20px;align-items:start;">

    {{-- List --}}
    <div class="card">
        <div class="card-header">
            <div class="card-header-title">
                <i class="fas fa-tags" style="color:var(--primary);"></i>
                Danh sách danh mục
            </div>
        </div>
        <div style="overflow-x:auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Tên danh mục</th>
                        <th>Mô tả</th>
                        <th>Số SP</th>
                        <th style="text-align:right;">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $cat)
                    <tr>
                        <td style="color:var(--text-muted);font-size:12px;">{{ $cat->id }}</td>
                        <td style="font-weight:600;">{{ $cat->name }}</td>
                        <td style="color:var(--text-muted);font-size:13px;">{{ $cat->description ?? '—' }}</td>
                        <td>
                            <span style="background:var(--primary-light);color:var(--primary);font-weight:700;font-size:12px;padding:2px 10px;border-radius:20px;">
                                {{ $cat->products_count }}
                            </span>
                        </td>
                        <td>
                            <div style="display:flex;gap:6px;justify-content:flex-end;">
                                <button class="btn-edit btn-sm"
                                        onclick="fillEditForm({{ $cat->id }}, '{{ addslashes($cat->name) }}', '{{ addslashes($cat->description ?? '') }}')">
                                    <i class="fas fa-pen"></i> Sửa
                                </button>
                                <form method="POST" action="{{ route('admin.categories.destroy', $cat->id) }}" style="margin:0;">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-danger btn-sm"
                                            onclick="return confirm('Xóa danh mục \'{{ $cat->name }}\'? Chỉ xóa được nếu không có sản phẩm!')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align:center;padding:30px;color:var(--text-muted);">Chưa có danh mục nào</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($categories->hasPages())
        <div style="padding:14px 22px;border-top:1px solid var(--border);">
            {{ $categories->links() }}
        </div>
        @endif
    </div>

    {{-- Add / Edit form --}}
    <div style="position:sticky;top:80px;">
        <div class="card">
            <div class="card-header">
                <div class="card-header-title" id="formTitle">
                    <i class="fas fa-plus" style="color:var(--primary);"></i>
                    Thêm danh mục mới
                </div>
            </div>
            <div class="card-body">
                <form method="POST" id="catForm" action="{{ route('admin.categories.store') }}">
                    @csrf
                    <input type="hidden" name="_method" id="methodInput" value="POST">
                    <input type="hidden" name="cat_id" id="catIdInput">

                    <div class="form-group">
                        <label class="form-label">Tên danh mục <span style="color:#e11d48;">*</span></label>
                        <input type="text" name="name" id="catNameInput" class="form-control"
                               placeholder="VD: Cà phê, Trà sữa, Nước ép..." required>
                        @error('name')<div class="form-text form-text-error">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Mô tả</label>
                        <textarea name="description" id="catDescInput" class="form-control" rows="2"
                                  placeholder="Mô tả ngắn về danh mục..."></textarea>
                    </div>

                    <div style="display:flex;gap:8px;">
                        <button type="button" id="resetBtn" class="btn-outline-admin btn-sm"
                                onclick="resetForm()" style="display:none;">
                            <i class="fas fa-times"></i> Hủy sửa
                        </button>
                        <button type="submit" class="btn-primary-admin" style="flex:1;justify-content:center;" id="submitBtn">
                            <i class="fas fa-plus"></i> <span id="submitText">Thêm danh mục</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
function fillEditForm(id, name, desc) {
    document.getElementById('catIdInput').value = id;
    document.getElementById('catNameInput').value = name;
    document.getElementById('catDescInput').value = desc;

    // Switch form to PUT
    const form = document.getElementById('catForm');
    form.action = '/admin/categories/' + id;
    document.getElementById('methodInput').value = 'PUT';
    document.getElementById('submitText').textContent = 'Lưu thay đổi';
    document.getElementById('submitBtn').querySelector('i').className = 'fas fa-save';

    document.getElementById('formTitle').innerHTML = '<i class="fas fa-pen" style="color:var(--primary);"></i> Chỉnh sửa danh mục';
    document.getElementById('resetBtn').style.display = 'inline-flex';
    document.getElementById('catNameInput').focus();
}

function resetForm() {
    document.getElementById('catForm').action = '{{ route("admin.categories.store") }}';
    document.getElementById('methodInput').value = 'POST';
    document.getElementById('catNameInput').value = '';
    document.getElementById('catDescInput').value = '';
    document.getElementById('catIdInput').value = '';
    document.getElementById('submitText').textContent = 'Thêm danh mục';
    document.getElementById('submitBtn').querySelector('i').className = 'fas fa-plus';
    document.getElementById('formTitle').innerHTML = '<i class="fas fa-plus" style="color:var(--primary);"></i> Thêm danh mục mới';
    document.getElementById('resetBtn').style.display = 'none';
}
</script>
@endsection
