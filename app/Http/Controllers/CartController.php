<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\Size;
use App\Models\Extra;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;

// Controller xử lý giỏ hàng
class CartController extends Controller
{
    private function syncPendingQrOrderCart(): void
    {
        $pendingOrderId = session('pending_qr_order_id');

        if (!$pendingOrderId || !auth()->check()) {
            return;
        }

        $payment = Payment::where('order_id', $pendingOrderId)->first();

        if ($payment && $payment->status === 'paid') {
            session()->forget(['cart', 'pending_qr_order_id']);
        }
    }

    private function calculateCartTotal(array $cart): float
    {
        $total = 0;
        foreach ($cart as $item) {
            $total += $item['price'] * $item['qty'];
        }

        return $total;
    }

    private function createOrderFromCart(array $cart, float $total, array $orderData, ?array $paymentData = null): Order
    {
        return DB::transaction(function () use ($cart, $total, $orderData, $paymentData) {
            $order = Order::create(array_merge([
                'user_id' => auth()->id(),
                'address_id' => null,
                'assigned_staff_id' => null,
                'voucher_id' => null,
                'order_type' => 'in_store',
                'status' => 'pending',
                'total_price' => $total,
                'discount_amount' => 0,
                'shipping_fee' => 0,
                'final_price' => $total,
                'note' => null,
            ], $orderData));

            foreach ($cart as $item) {
                $itemNoteParts = [
                    'Size: ' . ($item['size'] ?? '-'),
                    'Đường: ' . ($item['sugar'] ?? '-'),
                    'Đá: ' . ($item['ice'] ?? '-'),
                ];

                if (!empty($item['note'])) {
                    $itemNoteParts[] = 'Ghi chú: ' . $item['note'];
                }

                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['qty'],
                    'unit_price' => $item['price'],
                    'note' => implode(' | ', $itemNoteParts),
                ]);

                $toppings = is_array($item['toppings'] ?? null) ? $item['toppings'] : [];
                if (!empty($toppings)) {
                    $extras = Extra::whereIn('name', $toppings)->get()->keyBy('name');
                    $extraRows = [];

                    foreach ($toppings as $toppingName) {
                        $extra = $extras->get($toppingName);
                        if (!$extra) {
                            continue;
                        }

                        $extraRows[] = [
                            'order_item_id' => $orderItem->id,
                            'extra_id' => $extra->id,
                            'extra_name' => $extra->name,
                            'extra_price' => $extra->price,
                        ];
                    }

                    if (!empty($extraRows)) {
                        DB::table('order_item_extras')->insert($extraRows);
                    }
                }
            }

            if ($paymentData) {
                Payment::create(array_merge([
                    'order_id' => $order->id,
                    'method' => 'bank_transfer',
                    'status' => 'pending',
                    'amount' => $total,
                    'paid_at' => null,
                    'ref_code' => null,
                ], $paymentData));
            }

            return $order;
        });
    }

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
        $this->syncPendingQrOrderCart();

        $cart = session()->get('cart', []);
        $cartCount = array_sum(array_column($cart, 'qty'));

        return view('cart', compact('cart', 'cartCount'));
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
            $cartCount = array_sum(array_column($cart, 'qty'));

            return response()->json([
                'success' => true,
                'cart' => $cart,
                'total' => $total,
                'cart_count' => $cartCount
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
            $cartCount = array_sum(array_column($cart, 'qty'));

            return response()->json([
                'success' => true,
                'cart' => $cart,
                'total' => $total,
                'cart_count' => $cartCount
            ]);
        }
        return redirect('/cart');
    }

    public function confirmCashPayment(Request $request)
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return response()->json([
                'success' => false,
                'message' => 'Giỏ hàng đang trống.'
            ], 422);
        }

        $total = $this->calculateCartTotal($cart);

        $order = $this->createOrderFromCart($cart, $total, [
            'status' => 'processing',
            'note' => 'Thanh toán tiền mặt tại quầy - đơn hàng đã chuyển sang chuẩn bị',
        ], [
            'method' => 'cash',
            'status' => 'paid',
            'amount' => $total,
            'paid_at' => now(),
        ]);

        session()->forget(['cart', 'pending_qr_order_id']);

        return response()->json([
            'success' => true,
            'message' => 'Thanh toán thành công!',
            'order_id' => $order->id,
            'cart_count' => 0,
            'redirect_url' => route('orders.history')
        ]);
    }

    public function confirmQrPayment(Request $request)
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return response()->json([
                'success' => false,
                'message' => 'Giỏ hàng đang trống.'
            ], 422);
        }

        $pendingOrderId = session('pending_qr_order_id');
        if ($pendingOrderId) {
            $existingPayment = Payment::where('order_id', $pendingOrderId)
                ->where('method', 'bank_transfer')
                ->where('status', 'pending')
                ->first();

            if ($existingPayment) {
                return response()->json([
                    'success' => true,
                    'message' => 'Đơn QR của bạn đang chờ nhân viên xác nhận thanh toán.',
                    'cart_count' => array_sum(array_column($cart, 'qty')),
                ]);
            }
        }

        $total = $this->calculateCartTotal($cart);
        $qrNote = trim((string) $request->input('qr_note', ''));

        $order = $this->createOrderFromCart($cart, $total, [
            'status' => 'pending',
            'note' => $qrNote !== ''
                ? 'Khách đã gửi xác nhận chuyển khoản QR. Mã tham chiếu: ' . $qrNote
                : 'Khách đã gửi xác nhận chuyển khoản QR.',
        ], [
            'method' => 'bank_transfer',
            'status' => 'pending',
            'amount' => $total,
            'ref_code' => $qrNote !== '' ? $qrNote : null,
        ]);

        session()->put('pending_qr_order_id', $order->id);

        return response()->json([
            'success' => true,
            'message' => 'Đã gửi yêu cầu xác nhận. Nhân viên sẽ kiểm tra và xác nhận sau khi đối chiếu thanh toán QR của bạn.',
            'order_id' => $order->id,
            'cart_count' => array_sum(array_column($cart, 'qty')),
        ]);
    }

    public function qrPaymentStatus(Request $request)
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $pendingOrderId = session('pending_qr_order_id');

        if (!$pendingOrderId) {
            return response()->json([
                'success' => true,
                'has_pending_qr' => false,
                'paid' => false,
                'cart_count' => array_sum(array_column(session()->get('cart', []), 'qty')),
            ]);
        }

        $payment = Payment::where('order_id', $pendingOrderId)->first();

        if (!$payment) {
            session()->forget('pending_qr_order_id');

            return response()->json([
                'success' => true,
                'has_pending_qr' => false,
                'paid' => false,
                'cart_count' => array_sum(array_column(session()->get('cart', []), 'qty')),
            ]);
        }

        if ($payment->status === 'paid') {
            session()->forget(['cart', 'pending_qr_order_id']);

            return response()->json([
                'success' => true,
                'has_pending_qr' => false,
                'paid' => true,
                'cart_count' => 0,
                'redirect_url' => route('orders.history'),
                'message' => 'Đơn hàng của bạn đã được nhân viên xác nhận thanh toán.',
            ]);
        }

        return response()->json([
            'success' => true,
            'has_pending_qr' => true,
            'paid' => false,
            'cart_count' => array_sum(array_column(session()->get('cart', []), 'qty')),
        ]);
    }
}
