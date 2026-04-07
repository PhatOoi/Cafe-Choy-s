<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

// Controller xử lý giỏ hàng
class CartController extends Controller
{
    // Thêm sản phẩm vào giỏ hàng
    public function add(Request $request)
    {
        // Kiểm tra người dùng đã đăng nhập chưa
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        // Tìm sản phẩm theo id
        $product = Product::find($request->product_id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        // Lấy giỏ hàng từ session
        $cart = session()->get('cart', []);

        // Xử lý toppings thành chuỗi để tạo key duy nhất cho sản phẩm
        $toppingsStr = is_array($request->toppings)
            ? implode(',', $request->toppings)
            : '';

        // Tạo key cho sản phẩm trong giỏ hàng dựa trên các thuộc tính
        $cartKey = $request->product_id . '_' .
                   $request->size . '_' .
                   $request->sugar . '_' .
                   $request->ice . '_' .
                   $toppingsStr;

        // Nếu sản phẩm đã có trong giỏ thì tăng số lượng, ngược lại thêm mới
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

        // Lưu lại giỏ hàng vào session
        session()->put('cart', $cart);

        // Đếm tổng số lượng sản phẩm trong giỏ
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
