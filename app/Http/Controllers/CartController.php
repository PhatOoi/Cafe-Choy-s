<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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

    // Xác định option nào hợp lệ theo category của sản phẩm.
    private function getProductOptionRules(Product $product): array
    {
        $categorySlug = Str::slug(optional($product->category)->name ?? '');
        $productName = Str::lower((string) $product->name);
        $asciiProductName = Str::lower(Str::ascii((string) $product->name));

        $rules = [
            'size' => true,
            'topping' => true,
            'sugar' => true,
            'ice' => true,
        ];

        if ($categorySlug === 'tra-sua') {
            $rules['sugar'] = false;
        } elseif ($categorySlug === 'da-xay') {
            $rules['topping'] = false;
            $rules['sugar'] = false;
            $rules['ice'] = false;
        } elseif (in_array($categorySlug, ['nuoc-ep', 'nuoc-ep-sinh-to'], true)) {
            $rules['topping'] = false;
            $rules['sugar'] = false;

            if (str_contains($productName, 'sinh tố') || str_contains($asciiProductName, 'sinh to')) {
                $rules['ice'] = false;
            }
        } elseif ($categorySlug === 'ca-phe') {
            $rules['topping'] = false;
        } elseif ($categorySlug === 'tra-va-thuc-uong-theo-mua') {
            $rules['topping'] = false;
            $rules['sugar'] = false;
        } elseif ($categorySlug === 'banh-snack') {
            $rules['size'] = false;
            $rules['topping'] = false;
            $rules['sugar'] = false;
            $rules['ice'] = false;
        }

        return $rules;
    }

    // Chuẩn hóa option để các item cũ hoặc request thủ công không giữ dữ liệu thừa.
    private function normalizeCartOptions(Product $product, array $item): array
    {
        $rules = $this->getProductOptionRules($product);

        $normalizeText = static function ($value): ?string {
            $value = trim((string) $value);
            return $value !== '' ? $value : null;
        };

        $toppings = [];
        if ($rules['topping']) {
            $toppings = collect($item['toppings'] ?? [])
                ->filter(fn ($value) => trim((string) $value) !== '')
                ->map(fn ($value) => trim((string) $value))
                ->unique()
                ->values()
                ->all();
        }

        return [
            'size' => $rules['size'] ? $normalizeText($item['size'] ?? null) : null,
            'sugar' => $rules['sugar'] ? $normalizeText($item['sugar'] ?? null) : null,
            'ice' => $rules['ice'] ? $normalizeText($item['ice'] ?? null) : null,
            'toppings' => $toppings,
            'note' => $normalizeText($item['note'] ?? null),
        ];
    }

    // Tạo key từ option đã chuẩn hóa để tránh tách item sai biến thể.
    private function buildCartKey($productId, array $options): string
    {
        return implode('_', [
            $productId,
            $options['size'] ?? '-',
            $options['sugar'] ?? '-',
            $options['ice'] ?? '-',
            implode(',', $options['toppings'] ?? []),
        ]);
    }

    // Tính lại đơn giá sau khi loại các option không hợp lệ.
    private function calculateCartItemPrice(Product $product, array $options): float
    {
        $sizeExtra = 0;
        if (!empty($options['size'])) {
            $size = Size::where('name', $options['size'])->first();
            $sizeExtra = $size ? (float) $size->extra_price : 0;
        }

        $toppingPrice = 0;
        if (!empty($options['toppings'])) {
            $toppingPrice = (float) Extra::whereIn('name', $options['toppings'])->sum('price');
        }

        return (float) $product->price + $sizeExtra + $toppingPrice;
    }

    // Làm sạch cart hiện tại để sửa ngay các item đã lưu sai trong session.
    private function normalizeCart(array $cart): array
    {
        if (empty($cart)) {
            return [];
        }

        $productIds = collect($cart)
            ->pluck('product_id')
            ->filter()
            ->unique()
            ->values();

        $products = Product::with('category')
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy('id');

        $normalizedCart = [];

        foreach ($cart as $item) {
            $product = $products->get($item['product_id'] ?? null);
            if (!$product) {
                continue;
            }

            $options = $this->normalizeCartOptions($product, is_array($item) ? $item : []);
            $cartKey = $this->buildCartKey($product->id, $options);
            $qty = max(1, (int) ($item['qty'] ?? 1));

            if (isset($normalizedCart[$cartKey])) {
                $normalizedCart[$cartKey]['qty'] += $qty;

                if (empty($normalizedCart[$cartKey]['note']) && !empty($options['note'])) {
                    $normalizedCart[$cartKey]['note'] = $options['note'];
                }

                continue;
            }

            $normalizedCart[$cartKey] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => $this->calculateCartItemPrice($product, $options),
                'size' => $options['size'],
                'sugar' => $options['sugar'],
                'ice' => $options['ice'],
                'toppings' => $options['toppings'],
                'note' => $options['note'],
                'qty' => $qty,
                'image_url' => $product->image_url,
            ];
        }

        return $normalizedCart;
    }

    // Đọc cart từ session theo một chuẩn duy nhất để các API không bị lệch state.
    private function getNormalizedSessionCart(bool $persist = false): array
    {
        $cart = $this->normalizeCart(session()->get('cart', []));

        if ($persist) {
            session()->put('cart', $cart);
        }

        return $cart;
    }

    // Tính badge/cart count từ cart đã normalize.
    private function getCartCount(array $cart): int
    {
        return array_sum(array_column($cart, 'qty'));
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
                $itemNoteParts = [];

                if (!empty($item['size'])) {
                    $itemNoteParts[] = 'Size: ' . $item['size'];
                }

                if (!empty($item['sugar'])) {
                    $itemNoteParts[] = 'Đường: ' . $item['sugar'];
                }

                if (!empty($item['ice'])) {
                    $itemNoteParts[] = 'Đá: ' . $item['ice'];
                }

                if (!empty($item['note'])) {
                    $itemNoteParts[] = 'Ghi chú: ' . $item['note'];
                }

                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['qty'],
                    'unit_price' => $item['price'],
                    'note' => !empty($itemNoteParts) ? implode(' | ', $itemNoteParts) : null,
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
        $product = Product::with('category')->find($request->product_id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        $cart = $this->getNormalizedSessionCart(true);
        $options = $this->normalizeCartOptions($product, $request->all());
        $cartKey = $this->buildCartKey($request->product_id, $options);
        $price = $this->calculateCartItemPrice($product, $options);

        // Kiểm tra giới hạn số lượng (tối đa 10 sản phẩm)
        $newQty = $request->qty;
        if (isset($cart[$cartKey])) {
            $newQty += $cart[$cartKey]['qty'];
        }

        if ($newQty > 10) {
            return response()->json([
                'success' => false,
                'message' => 'Số lượng sản phẩm này không được vượt quá 10!',
                'is_limit_exceeded' => true,
                'support_url' => route('support')
            ], 400);
        }

        // Nếu biến thể món đã có trong giỏ thì cộng dồn số lượng.
        if (isset($cart[$cartKey])) {
            $cart[$cartKey]['qty'] += $request->qty;
        } else {
            // Nếu chưa có thì tạo mới một dòng trong session cart.
            $cart[$cartKey] = [
                'product_id' => $request->product_id,
                'name' => $product->name,
                'price' => $price,
                'size' => $options['size'],
                'sugar' => $options['sugar'],
                'ice' => $options['ice'],
                'toppings' => $options['toppings'],
                'note' => $options['note'],
                'qty' => $request->qty,
                'image_url' => $product->image_url,
            ];
        }

        // Ghi cart mới vào session.
        session()->put('cart', $cart);

        // Tính badge số lượng món để update UI.
        $cartCount = $this->getCartCount($cart);

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

        $cart = $this->getNormalizedSessionCart(true);
        $cartCount = $this->getCartCount($cart);

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

        $cart = $this->getNormalizedSessionCart(true);

        if (isset($cart[$id])) {
            unset($cart[$id]);
            session()->put('cart', $cart);
        }

        // Trả cart mới cho frontend nếu đây là request AJAX.
        if (request()->expectsJson() || request()->isJson() || request()->wantsJson()) {
            $cart = $this->getNormalizedSessionCart(true);
            $total = $this->calculateCartTotal($cart);
            $cartCount = $this->getCartCount($cart);

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

        $cart = $this->getNormalizedSessionCart(true);

        // Nếu item tồn tại thì cập nhật qty hoặc xóa khi qty <= 0.
        if (isset($cart[$key])) {
            $qty = (int) $request->input('qty', 1);

            // Kiểm tra giới hạn (tối đa 10)
            if ($qty > 10) {
                if ($request->expectsJson() || $request->isJson() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Số lượng sản phẩm này không được vượt quá 10!',
                        'is_limit_exceeded' => true,
                        'support_url' => route('support')
                    ], 400);
                }
                return back()->with('error', 'Số lượng sản phẩm này không được vượt quá 10!');
            }

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
            $cart = $this->getNormalizedSessionCart(true);
            $total = $this->calculateCartTotal($cart);
            $cartCount = $this->getCartCount($cart);

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

        $cart = $this->getNormalizedSessionCart(true);

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

        $cart = $this->getNormalizedSessionCart(true);

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
                    'cart_count' => $this->getCartCount($cart),
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
            'cart_count' => $this->getCartCount($cart),
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
            $cart = $this->getNormalizedSessionCart(true);

            return response()->json([
                'success' => true,
                'has_pending_qr' => false,
                'paid' => false,
                'cart_count' => $this->getCartCount($cart),
            ]);
        }

        $payment = Payment::where('order_id', $pendingOrderId)->first();

        // Nếu payment không tồn tại nữa thì xóa cờ pending trong session để tránh treo UI.
        if (!$payment) {
            session()->forget('pending_qr_order_id');
            $cart = $this->getNormalizedSessionCart(true);

            return response()->json([
                'success' => true,
                'has_pending_qr' => false,
                'paid' => false,
                'cart_count' => $this->getCartCount($cart),
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
        $cart = $this->getNormalizedSessionCart(true);

        return response()->json([
            'success' => true,
            'has_pending_qr' => true,
            'paid' => false,
            'cart_count' => $this->getCartCount($cart),
        ]);
    }
}
