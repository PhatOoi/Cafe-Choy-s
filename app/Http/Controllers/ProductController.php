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
    $q = $request->q;

    $categories = Category::whereHas('products', function ($query) use ($q) {
        $query->where('name', 'like', '%' . $q . '%');
    })
    ->with(['products' => function ($query) use ($q) {
        $query->where('name', 'like', '%' . $q . '%')
              ->orderBy('name');
    }])
    ->orderBy('sort_order')
    ->get();

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
            $imageName = time() . '.' . $request->image->extension();
            $request->image->move(public_path('images'), $imageName);

            $imagePath = $imageName;
        }

        // ✅ Nếu nhập URL
        elseif ($request->image_url) {
            $imagePath = $request->image_url;
        }

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

            $imageName = time() . '.' . $request->image->extension();
            $request->image->move(public_path('images'), $imageName);

            $imagePath = $imageName;
        }

        // ✅ Nếu nhập URL mới
        elseif ($request->image_url) {
            $imagePath = $request->image_url;
        }

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
        $product = Product::findOrFail($id);

        // Xóa ảnh local
        if ($product->image_url && !Str::startsWith($product->image_url, ['http://', 'https://'])) {
            $path = public_path('images/' . $product->image_url);
            if (file_exists($path)) {
                unlink($path);
            }
        }

        $product->delete();

        return redirect()->route('admin.products')
            ->with('success', 'Đã xóa sản phẩm!');
    }
}