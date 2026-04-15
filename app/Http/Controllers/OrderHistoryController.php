<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class OrderHistoryController extends Controller
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

        return view('order-history', compact('orders', 'cartCount'));
    }
}