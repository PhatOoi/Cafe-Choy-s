<?php
namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function index()
    {
        // Lấy user hiện tại và toàn bộ đơn của user để hiển thị cùng trang hồ sơ.
        $user = Auth::user();
        $orders = Order::with(['items.product', 'items.extras'])
            ->where('user_id', $user->id)
            ->latest('created_at')
            ->get();

        // Cart count dùng cho badge giỏ hàng ở navbar trang profile.
        $cart = session()->get('cart', []);
        $cartCount = array_sum(array_column($cart, 'qty'));

        return view('profile', compact('user', 'orders', 'cartCount'));
    }

    // Bản route/profile phụ cũng render cùng view profile nhưng preload thêm relation role.
    public function profile()
    {
        $user = auth()->user()->load('role');
        $orders = Order::with(['items.product', 'items.extras'])
            ->where('user_id', $user->id)
            ->latest('created_at')
            ->get();

        // Vẫn giữ cart count để giao diện đồng nhất với các trang khách khác.
        $cart = session()->get('cart', []);
        $cartCount = array_sum(array_column($cart, 'qty'));

        return view('profile', compact('user', 'orders', 'cartCount'));
    }
}
