<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Size;
use App\Models\Extra;
use App\Models\Category;
use Illuminate\Http\Request;

// Controller hiển thị menu sản phẩm cho khách hàng.
class MenuController extends Controller
{
    // Nạp toàn bộ dữ liệu cần thiết cho trang menu và modal tùy chỉnh món.
    public function index()
    {
        // Staff không thao tác bằng giao diện menu khách nên điều hướng về dashboard staff.
        if (auth()->check() && auth()->user()->isStaff()) {
            return redirect()->route('staff.dashboard')->with('error', 'Nhân viên không có quyền truy cập trang menu.');
        }

        // Lấy tất cả category có sản phẩm (kể cả unavailable) để hiển thị xám + "Đang cập nhật".
        $categories = Category::whereHas('products')->with(['products' => function ($q) {
            // Lấy tất cả sản phẩm (available + unavailable), sắp xếp theo tên.
            $q->orderBy('name');
        }])->orderBy('sort_order')->get();

        // Lấy các nhóm option dùng trong modal chọn topping/đường/đá/kích cỡ.
        $toppings = Extra::topping()->get();
        $sugars = Extra::sugar()->get();
        $ices = Extra::ice()->get();
        $sizes = Size::all();

        // Trả dữ liệu sang view menu để render danh mục và modal đặt món.
        return view('menu', compact('categories', 'toppings', 'sugars', 'ices', 'sizes'));
    }
}