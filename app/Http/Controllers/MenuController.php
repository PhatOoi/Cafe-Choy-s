<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Size;
use App\Models\Extra;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        // Lấy 6 món bestseller theo đúng logic top của admin: chỉ tính đơn đã thanh toán.
        $topProducts = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('payments', 'payments.order_id', '=', 'orders.id')
            ->where('payments.status', 'paid')
            ->selectRaw('products.id, products.name, products.description, products.price, products.image_url, products.status, categories.name as category_name, SUM(order_items.quantity) as total_sold')
            ->groupBy(
                'products.id',
                'products.name',
                'products.description',
                'products.price',
                'products.image_url',
                'products.status',
                'categories.name'
            )
            ->orderByDesc('total_sold')
            ->limit(6)
            ->get();

        // Trả dữ liệu sang view menu để render danh mục và modal đặt món.
        return view('menu', compact('categories', 'toppings', 'sugars', 'ices', 'sizes', 'topProducts'));
    }
}