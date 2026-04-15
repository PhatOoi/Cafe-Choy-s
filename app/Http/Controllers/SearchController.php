<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Str;
use App\Models\Size;
use App\Models\Extra;
use App\Models\Category;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        // Từ khóa tìm kiếm lấy từ query string trên thanh search.
        $q = $request->q;

        // Chỉ lấy các category có ít nhất một sản phẩm khớp từ khóa.
        $categories = Category::whereHas('products', function ($query) use ($q) {
            $query->where('name', 'like', '%' . $q . '%');
        })
            ->with(['products' => function ($query) use ($q) {
                // Trong mỗi category chỉ giữ các sản phẩm khớp từ khóa, đồng thời sắp xếp theo tên.
                $query->where('name', 'like', '%' . $q . '%')
                    ->orderBy('name');
            }])
            ->orderBy('sort_order')
            ->get();

        // Vẫn nạp đủ option giống trang menu để modal tùy chỉnh món trên trang search dùng lại được.
        $toppings = Extra::topping()->get();
        $sugars = Extra::sugar()->get();
        $ices = Extra::ice()->get();
        $sizes = Size::all();

        // Badge giỏ hàng ở navbar của trang search-result.
        $cartCount = collect(session('cart', []))->sum('qty');

        return view('search-result', compact(
            'categories',
            'toppings',
            'sugars',
            'ices',
            'sizes',
            'cartCount',
            'q'
        ));
    }
}
