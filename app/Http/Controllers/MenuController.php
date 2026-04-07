<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Size;
use App\Models\Extra;

// Controller hiển thị menu sản phẩm
class MenuController extends Controller
{
    // Hiển thị trang menu với danh sách sản phẩm, topping, đường, đá, size
    public function index()
    {
        $products = Product::all(); // Lấy tất cả sản phẩm
        $toppings = Extra::topping()->get(); // Lấy danh sách topping
        $sugars = Extra::sugar()->get(); // Lấy danh sách đường
        $ices = Extra::ice()->get(); // Lấy danh sách đá
        $sizes = Size::all(); // Lấy danh sách size

        // Trả về view menu với dữ liệu đã lấy
        return view('menu', compact('products', 'toppings', 'sugars', 'ices', 'sizes'));
    }
}