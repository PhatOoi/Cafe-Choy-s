<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Size;
use App\Models\Extra;
use App\Models\Category;
use Illuminate\Http\Request;

// Controller hiển thị menu sản phẩm
class MenuController extends Controller
{
    // Hiển thị trang menu với danh sách sản phẩm, topping, đường, đá, size
    public function index()
    {
        if (auth()->check() && auth()->user()->isStaff()) {
            return redirect()->route('staff.dashboard')->with('error', 'Nhân viên không có quyền truy cập trang menu.');
        }

        // Lấy tất cả category có sản phẩm
        $categories = Category::whereHas('products')->with(['products' => function($q) {
            $q->orderBy('name');
        }])->orderBy('sort_order')->get();
        $toppings = Extra::topping()->get(); // Lấy danh sách topping
        $sugars = Extra::sugar()->get(); // Lấy danh sách đường
        $ices = Extra::ice()->get(); // Lấy danh sách đá
        $sizes = Size::all(); // Lấy danh sách size

        // Trả về view menu với dữ liệu đã lấy
        return view('menu', compact('categories', 'toppings', 'sugars', 'ices', 'sizes'));
    }
        
    
}