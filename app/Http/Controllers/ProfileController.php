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
        $user = Auth::user();
        $orders = Order::with(['items.product', 'items.extras'])
            ->where('user_id', $user->id)
            ->latest('created_at')
            ->get();
        $cart = session()->get('cart', []);
        $cartCount = array_sum(array_column($cart, 'qty'));

        return view('profile', compact('user', 'orders', 'cartCount'));
    }
    public function profile()
    {
        $user = auth()->user()->load('role');
        $orders = Order::with(['items.product', 'items.extras'])
            ->where('user_id', $user->id)
            ->latest('created_at')
            ->get();
        $cart = session()->get('cart', []);
        $cartCount = array_sum(array_column($cart, 'qty'));

        return view('profile', compact('user', 'orders', 'cartCount'));
    }
}
