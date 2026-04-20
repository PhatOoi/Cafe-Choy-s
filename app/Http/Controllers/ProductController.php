<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Str;
use App\Models\Size;
use App\Models\Extra;
use App\Models\Category;

class ProductController extends Controller
{
    // 🔍 SEARCH
    public function search(Request $request)
    {
        // Nhận từ khóa tìm kiếm từ query string.
        $q = $request->q;

        // Chỉ lấy category có sản phẩm khớp với từ khóa và đang bán.
        $categories = Category::whereHas('products', function ($query) use ($q) {
            $query->where('name', 'like', '%' . $q . '%')
                  ->where('status', 'available');
        })
        ->with(['products' => function ($query) use ($q) {
            // Trong mỗi category, chỉ giữ lại các sản phẩm khớp, đang bán và sắp xếp theo tên.
            $query->where('name', 'like', '%' . $q . '%')
                  ->where('status', 'available')
                  ->orderBy('name');
        }])
        ->orderBy('sort_order')
        ->get();

        // Nạp đủ option để trang search có thể tái sử dụng modal chọn món giống trang menu.
        $toppings = Extra::topping()->get();
        $sugars   = Extra::sugar()->get();
        $ices     = Extra::ice()->get();
        $sizes    = Size::all();

        return view('search', compact(
            'categories',
            'toppings',
            'sugars',
            'ices',
            'sizes',
            'q'
        ));
    }
    // ➕ CREATE
    public function store(Request $request)
    {
        // Validate dữ liệu cơ bản và cho phép chọn 1 trong 2 cách ảnh: upload file hoặc nhập URL.
        $request->validate([
            'name' => 'required',
            'price' => 'required|numeric',
            'category_id' => 'required',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'image_url' => 'nullable|url'
        ]);

        $imagePath = null;

        // ✅ Ưu tiên upload file
        if ($request->hasFile('image')) {
            // Ảnh upload local sẽ được lưu vào public/images và chỉ lưu tên file trong database.
            $imageName = time() . '.' . $request->image->extension();
            $request->image->move(public_path('images'), $imageName);

            $imagePath = $imageName;
        }

        // ✅ Nếu nhập URL
        elseif ($request->image_url) {
            // Nếu dùng URL thì lưu nguyên chuỗi URL để frontend load ảnh từ nguồn ngoài.
            $imagePath = $request->image_url;
        }

        // Tạo sản phẩm mới cho khu vực admin quản lý menu.
        Product::create([
            'name' => $request->name,
            'price' => $request->price,
            'category_id' => $request->category_id,
            'description' => $request->description,
            'status' => $request->status ?? 'available',
            'image_url' => $imagePath
        ]);

        return redirect()->route('admin.products')
            ->with('success', 'Thêm sản phẩm thành công!');
    }

    // ✏️ UPDATE
    public function update(Request $request, $id)
    {
        // Tìm sản phẩm cần sửa, nếu không có sẽ trả 404 tự động.
        $product = Product::findOrFail($id);

        $request->validate([
            'name' => 'required',
            'price' => 'required|numeric',
            'category_id' => 'required',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'image_url' => 'nullable|url'
        ]);

        $imagePath = $product->image_url;

        // ✅ Nếu upload ảnh mới
        if ($request->hasFile('image')) {

            // Xóa ảnh cũ nếu là ảnh local
            if ($product->image_url && !Str::startsWith($product->image_url, ['http://', 'https://'])) {
                $oldPath = public_path('images/' . $product->image_url);
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }

            // Lưu ảnh local mới và thay lại image path cho sản phẩm.
            $imageName = time() . '.' . $request->image->extension();
            $request->image->move(public_path('images'), $imageName);

            $imagePath = $imageName;
        }

        // ✅ Nếu nhập URL mới
        elseif ($request->image_url) {
            $imagePath = $request->image_url;
        }

        // Cập nhật thông tin sản phẩm sau khi xử lý ảnh.
        $product->update([
            'name' => $request->name,
            'price' => $request->price,
            'category_id' => $request->category_id,
            'description' => $request->description,
            'status' => $request->status,
            'image_url' => $imagePath
        ]);

        return redirect()->route('admin.products')
            ->with('success', 'Cập nhật thành công!');
    }

    // ❌ DELETE
    public function destroy($id)
    {
        // Xóa sản phẩm theo id trong khu vực admin.
        $product = Product::findOrFail($id);

        // Xóa ảnh local
        if ($product->image_url && !Str::startsWith($product->image_url, ['http://', 'https://'])) {
            $path = public_path('images/' . $product->image_url);
            if (file_exists($path)) {
                unlink($path);
            }
        }

        // Sau cùng xóa record sản phẩm khỏi database.
        $product->delete();

        return redirect()->route('admin.products')
            ->with('success', 'Đã xóa sản phẩm!');
    }
}