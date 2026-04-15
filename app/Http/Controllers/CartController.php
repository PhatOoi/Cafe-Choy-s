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
use App\Support\DailyRevenueSnapshotService;

// Controller xử lý toàn bộ nghiệp vụ giỏ hàng và checkout của khách.
class CartController extends Controller
{
    // Đồng bộ bảng snapshot doanh thu sau những thay đổi ảnh hưởng tới đơn đã thanh toán.
    private function syncDailyRevenueSnapshots(): void
    {
        app(DailyRevenueSnapshotService::class)->syncLastDays(30);
    }

    // Kiểm tra đơn QR đang treo trong session đã được staff xác nhận hay chưa.
    // Nếu đã thanh toán xong thì dọn giỏ hàng cũ khỏi session.
    private function syncPendingQrOrderCart(): void
    {
        $pendingOrderId = session('pending_qr_order_id');

        // Không có đơn QR theo dõi hoặc chưa đăng nhập thì bỏ qua.
        if (!$pendingOrderId || !auth()->check()) {
            return;
        }

        // Lấy payment của đơn QR đang được theo dõi.
        $payment = Payment::where('order_id', $pendingOrderId)->first();

        // Khi payment đã được staff xác nhận là paid thì session cart không còn cần giữ nữa.
        if ($payment && $payment->status === 'paid') {
            session()->forget(['cart', 'pending_qr_order_id']);
        }
    }

    // Tính tổng tiền giỏ hàng từ giá đã cộng option của từng item.
    private function calculateCartTotal(array $cart): float
    {
        $total = 0;
        foreach ($cart as $item) {
            $total += $item['price'] * $item['qty'];
        }

        return $total;
    }

    // Chuyển toàn bộ session cart thành order thật trong database.
    // Hàm này gom cả order, order item, extra và payment vào chung một transaction.
    private function createOrderFromCart(array $cart, float $total, array $orderData, ?array $paymentData = null): Order
    {
        return DB::transaction(function () use ($cart, $total, $orderData, $paymentData) {
            // Tạo record đơn hàng gốc với mặc định chung cho checkout của khách.
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

            // Mỗi item trong giỏ trở thành một dòng order item riêng.
            foreach ($cart as $item) {
                // Gộp option của món vào note để staff/admin xem lại dễ hơn.
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

                // Tách topping ra để lưu ở bảng order_item_extras.
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

                    // Insert hàng loạt để giảm số query.
                    if (!empty($extraRows)) {
                        DB::table('order_item_extras')->insert($extraRows);
                    }
                }
            }

            // Tạo payment nếu flow checkout yêu cầu gắn thanh toán ngay khi tạo đơn.
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

    // Thêm sản phẩm đã chọn option vào session cart.
    public function add(Request $request)
    {
        // Chỉ khách đã đăng nhập mới được thêm món vào giỏ.
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        // Lấy sản phẩm từ database để chắc chắn id hợp lệ.
        $product = Product::find($request->product_id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        $cart = session()->get('cart', []);

        // Gộp topping thành chuỗi để tạo key phân biệt các biến thể cùng một món.
        $toppingsStr = is_array($request->toppings)
            ? implode(',', $request->toppings)
            : '';

        // Key cart được ghép từ product + toàn bộ option để tránh đè nhầm biến thể khác nhau.
        $cartKey = $request->product_id . '_'
                   . $request->size . '_'
                   . $request->sugar . '_'
                   . $request->ice . '_'
                   . $toppingsStr;

        // Tính phụ thu của size đã chọn.
        $size = Size::where('name', $request->size)->first();
        $extraPrice = $size ? (float)$size->extra_price : 0;

        // Tính tổng giá topping đã chọn.
        $toppingPrice = 0;
        if (!empty($request->toppings) && is_array($request->toppings)) {
            $toppingObjs = Extra::whereIn('name', $request->toppings)->get();
            foreach ($toppingObjs as $tp) {
                $toppingPrice += (float)$tp->price;
            }
        }

        // Giá 1 đơn vị món sau khi cộng option.
        $price = $product->price + $extraPrice + $toppingPrice;

        // Nếu biến thể món đã có trong giỏ thì cộng dồn số lượng.
        if (isset($cart[$cartKey])) {
            $cart[$cartKey]['qty'] += $request->qty;
        } else {
            // Nếu chưa có thì tạo mới một dòng trong session cart.
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

        // Ghi cart mới vào session.
        session()->put('cart', $cart);

        // Tính badge số lượng món để update UI.
        $cartCount = array_sum(array_column($cart, 'qty'));

        // Nếu frontend gọi AJAX thì trả JSON để update popup/cart badge mà không reload.
        if ($request->expectsJson() || $request->isJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã thêm vào giỏ hàng!',
                'cart_count' => $cartCount,
            ]);
        }

        // Request thường thì chuyển về trang cart.
        return redirect('/cart');
    }

    // Hiển thị trang cart hiện tại từ session.
    public function index()
    {
        // Mỗi lần mở cart đều đồng bộ lại trạng thái đơn QR cũ trước.
        $this->syncPendingQrOrderCart();

        $cart = session()->get('cart', []);
        $cartCount = array_sum(array_column($cart, 'qty'));

        return view('cart', compact('cart', 'cartCount'));
    }

    // Xóa một item khỏi cart theo key biến thể.
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

        // Trả cart mới cho frontend nếu đây là request AJAX.
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

    // Cập nhật số lượng của một item trong giỏ.
    public function update(Request $request, $key)
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $cart = session()->get('cart', []);

        // Nếu item tồn tại thì cập nhật qty hoặc xóa khi qty <= 0.
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

        // AJAX sẽ nhận lại cart/tổng tiền mới để render lại ngay.
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

    // Xác nhận thanh toán tiền mặt tại quầy.
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

        // Đơn tiền mặt được xem là đã xác nhận và đã thanh toán ngay.
        $order = $this->createOrderFromCart($cart, $total, [
            'status' => 'confirmed',
            'note' => 'Thanh toán tiền mặt tại quầy - đơn hàng đã được xác nhận',
        ], [
            'method' => 'cash',
            'status' => 'paid',
            'amount' => $total,
            'paid_at' => now(),
        ]);

        // Đồng bộ báo cáo doanh thu vì đây là đơn đã paid.
        $this->syncDailyRevenueSnapshots();

        // Xóa cart khỏi session sau khi checkout thành công.
        session()->forget(['cart', 'pending_qr_order_id']);

        return response()->json([
            'success' => true,
            'message' => 'Thanh toán thành công!',
            'order_id' => $order->id,
            'cart_count' => 0,
            'redirect_url' => route('orders.history')
        ]);
    }

    // Gửi yêu cầu xác nhận thanh toán QR cho staff kiểm tra thủ công.
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

        // Nếu đã có một đơn QR đang pending trong session thì không tạo thêm đơn trùng.
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

        // Đơn QR ban đầu chỉ ở trạng thái pending cho tới khi staff đối chiếu payment thành công.
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

        // Lưu id đơn đang chờ để frontend có thể poll trạng thái xác nhận.
        session()->put('pending_qr_order_id', $order->id);

        return response()->json([
            'success' => true,
            'message' => 'Đã gửi yêu cầu xác nhận. Nhân viên sẽ kiểm tra và xác nhận sau khi đối chiếu thanh toán QR của bạn.',
            'order_id' => $order->id,
            'cart_count' => array_sum(array_column($cart, 'qty')),
        ]);
    }

    // Frontend gọi API này theo chu kỳ để biết đơn QR đã được duyệt chưa.
    public function qrPaymentStatus(Request $request)
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $pendingOrderId = session('pending_qr_order_id');

        // Không có đơn QR chờ trong session thì frontend dừng trạng thái chờ.
        if (!$pendingOrderId) {
            return response()->json([
                'success' => true,
                'has_pending_qr' => false,
                'paid' => false,
                'cart_count' => array_sum(array_column(session()->get('cart', []), 'qty')),
            ]);
        }

        $payment = Payment::where('order_id', $pendingOrderId)->first();

        // Nếu payment không tồn tại nữa thì xóa cờ pending trong session để tránh treo UI.
        if (!$payment) {
            session()->forget('pending_qr_order_id');

            return response()->json([
                'success' => true,
                'has_pending_qr' => false,
                'paid' => false,
                'cart_count' => array_sum(array_column(session()->get('cart', []), 'qty')),
            ]);
        }

        // Khi staff xác nhận payment thành paid, frontend được báo để redirect lịch sử đơn.
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

        // Trường hợp còn pending thì frontend tiếp tục giữ popup/trạng thái chờ xác nhận.
        return response()->json([
            'success' => true,
            'has_pending_qr' => true,
            'paid' => false,
            'cart_count' => array_sum(array_column(session()->get('cart', []), 'qty')),
        ]);
    }
}
