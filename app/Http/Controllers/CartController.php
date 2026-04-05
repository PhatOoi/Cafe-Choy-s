<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class CartController extends Controller
{
    public function add(Request $request)
{
    if (!auth()->check()) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized'
        ], 401);
    }

    $product = Product::find($request->product_id);

    if (!$product) {
        return response()->json([
            'success' => false,
            'message' => 'Product not found'
        ], 404);
    }

    $cart = session()->get('cart', []);

    $toppingsStr = is_array($request->toppings)
        ? implode(',', $request->toppings)
        : '';

    $cartKey = $request->product_id . '_' .
               $request->size . '_' .
               $request->sugar . '_' .
               $request->ice . '_' .
               $toppingsStr;

    if (isset($cart[$cartKey])) {
        $cart[$cartKey]['qty'] += $request->qty;
    } else {
        $cart[$cartKey] = [
            'product_id' => $request->product_id,
            'name' => $product->name,
            'price' => $product->price,
            'size' => $request->size,
            'sugar' => $request->sugar,
            'ice' => $request->ice,
            'toppings' => $request->toppings,
            'note' => $request->note,
            'qty' => $request->qty,
        ];
    }

    session()->put('cart', $cart);

    $cartCount = array_sum(array_column($cart, 'qty'));

    // ✅ FIX QUAN TRỌNG
    return response()->json([
        'success' => true,
        'cart_count' => $cartCount
    ]);
}
    public function index()
    {
        $cart = session()->get('cart', []);
        return view('cart', compact('cart'));
    }

    public function remove($id)
{
    if (!auth()->check()) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized'
        ], 401);
    }

    $cart = session()->get('cart', []);

    if (isset($cart[$id])) {
        unset($cart[$id]);
        session()->put('cart', $cart);
    }

    return redirect('/cart');
}
}
