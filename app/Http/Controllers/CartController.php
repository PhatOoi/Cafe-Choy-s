<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Size;
use App\Models\Extra;

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



        // Lấy extra_price của size từ bảng sizes
        $size = Size::where('name', $request->size)->first();
        $extraPrice = $size ? (float)$size->extra_price : 0;

        // Tính tổng giá topping
        $toppingPrice = 0;
        if (!empty($request->toppings) && is_array($request->toppings)) {
            $toppingObjs = Extra::whereIn('name', $request->toppings)->get();
            foreach ($toppingObjs as $tp) {
                $toppingPrice += (float)$tp->price;
            }
        }

        $price = $product->price + $extraPrice + $toppingPrice;

        // Nếu sản phẩm đã có trong giỏ thì tăng số lượng, ngược lại thêm mới
        if (isset($cart[$cartKey])) {
            $cart[$cartKey]['qty'] += $request->qty;
        } else {
            $cart[$cartKey] = [
                'product_id' => $request->product_id,
                'name' => $product->name,
                'price' => $price,
                'size' => $request->size,
                'sugar' => $request->sugar,
                'ice' => $request->ice,
                'toppings' => $request->toppings,
                'note' => $request->note,
                'qty' => $request->qty,
                'image_url' => $product->image_url,
            ];
        }

        // Lưu lại giỏ hàng vào session
        session()->put('cart', $cart);

        // Đếm tổng số lượng sản phẩm trong giỏ
        $cartCount = array_sum(array_column($cart, 'qty'));

        // Nếu là AJAX (application/json), trả về JSON
        if ($request->expectsJson() || $request->isJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã thêm vào giỏ hàng!',
                'cart_count' => $cartCount,
            ]);
        }
        // Nếu là request thường, chuyển hướng về trang giỏ hàng
        return redirect('/cart');
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
        if (request()->expectsJson() || request()->isJson() || request()->wantsJson()) {
            $cart = session()->get('cart', []);
            $total = 0;
            foreach ($cart as $item) {
                $total += $item['price'] * $item['qty'];
            }
            return response()->json([
                'success' => true,
                'cart' => $cart,
                'total' => $total
            ]);
        }
        return redirect('/cart');
    }

    // Cập nhật số lượng sản phẩm trong giỏ hàng
    public function update(Request $request, $key)
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $cart = session()->get('cart', []);

        if (isset($cart[$key])) {
            $qty = (int) $request->input('qty', 1);
            if ($qty > 0) {
                $cart[$key]['qty'] = $qty;
                session()->put('cart', $cart);
            } else {
                unset($cart[$key]);
                session()->put('cart', $cart);
            }
        }
        if ($request->expectsJson() || $request->isJson() || $request->wantsJson()) {
            $cart = session()->get('cart', []);
            $total = 0;
            foreach ($cart as $item) {
                $total += $item['price'] * $item['qty'];
            }
            return response()->json([
                'success' => true,
                'cart' => $cart,
                'total' => $total
            ]);
        }
        return redirect('/cart');
    }
}
