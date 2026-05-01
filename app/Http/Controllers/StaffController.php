<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\OrderItem;
use App\Models\OrderItemExtra;
use App\Models\Payment;
use App\Models\WorkScheduleBoardLock;
use App\Models\Overtime;
use App\Support\DailyRevenueSnapshotService;
use App\Models\WorkScheduleRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    // Đồng bộ bảng snapshot doanh thu 30 ngày gần nhất sau các thay đổi liên quan tới thanh toán/trạng thái đơn.
    private function syncDailyRevenueSnapshots(): void
    {
        app(DailyRevenueSnapshotService::class)->syncLastDays(30);
    }

    // Tập hợp toàn bộ dữ liệu dùng cho trang doanh thu ngày của staff.
    private function getRevenueSnapshotData(): array
    {
        $staffId = Auth::id();
        $isStaffCreatedOrder = fn ($order) => in_array((int) optional($order->user)->role_id, [1, 2], true);
        $isWebAppOrder = fn ($order) => (int) optional($order->user)->role_id === 3;

        // Toàn bộ đơn đã thanh toán trong ngày (doanh thu quán hôm nay).
        $todayOrders = Order::with(['payment', 'user'])
            ->whereDate('created_at', today())
            ->whereHas('payment', fn ($query) => $query->where('status', 'paid'))
            ->get();

        // Đơn tại quán do chính nhân viên hiện tại tạo hôm nay.
        $staffCreatedOrders = $todayOrders->filter(
            fn ($order) => (int) $order->assigned_staff_id === (int) $staffId && $isStaffCreatedOrder($order)
        );

        // Thống kê số đơn trong ngày theo nguồn.
        $todayOrderBreakdown = [
            'total_orders'        => (int) $todayOrders->count(),
            'staff_created_orders' => (int) $staffCreatedOrders->count(),
            'web_app_orders'      => (int) $todayOrders->filter($isWebAppOrder)->count(),
        ];

        $todayRevenue = (object) [
            'revenue_date'         => now()->copy()->startOfDay(),
            'total_orders'         => $todayOrderBreakdown['total_orders'],
            'total_revenue'        => (float) $todayOrders->sum('final_price'),
            'staff_created_revenue' => (float) $staffCreatedOrders->sum('final_price'),
            'customer_revenue'     => (float) $todayOrders->filter($isWebAppOrder)->sum('final_price'),
            'cash_revenue'         => (float) $todayOrders
                ->filter(fn ($order) => optional($order->payment)->method === 'cash')
                ->sum('final_price'),
            'transfer_revenue'     => (float) $todayOrders
                ->filter(fn ($order) => optional($order->payment)->method === 'bank_transfer')
                ->sum('final_price'),
        ];

        return compact('todayRevenue', 'todayOrderBreakdown');
    }

    // ─── Dashboard ───────────────────────────────────────────────────────────

    public function dashboard()
    {
        // staffId dùng để tính số đơn hôm nay mà nhân viên hiện tại đã phụ trách.
        $staffId = Auth::id();

        // Các số liệu nhanh trên dashboard staff.
        $stats = [
            'pending'    => Order::where('status', 'pending')->count(),
            'processing' => Order::whereIn('status', ['confirmed', 'processing', 'ready'])->count(),
            'today'      => Order::whereDate('created_at', today())
                                 ->where('assigned_staff_id', $staffId)->count(),
        ];

        // Danh sách đơn đang hoạt động để staff xử lý nhanh, ưu tiên các đơn gần với bước chế biến.
        $recentOrders = Order::with(['user', 'items.product', 'payment'])
            ->whereNotIn('status', ['delivered', 'cancelled', 'failed'])
            ->orderByRaw("FIELD(status,'processing','confirmed','ready','pending')")
            ->orderBy('created_at', 'asc')
            ->take(10)
            ->get();

        return view('staff.dashboard', compact('stats', 'recentOrders'));
    }

    // ─── Danh sách đơn hàng ──────────────────────────────────────────────────

    public function orders(Request $request)
    {
        // Nạp danh sách đơn cho staff với đủ quan hệ cần hiển thị trong bảng.
        $query = Order::with(['user', 'items.product', 'payment']);

        // Lọc theo trạng thái
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Tìm theo ID hoặc tên khách
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%$search%")
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%$search%"));
            });
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        $statusCounts = Order::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('staff.orders', compact('orders', 'statusCounts'));
    }

    public function orderReminderStatuses(Request $request)
    {
        // Endpoint polling để frontend biết trạng thái mới nhất của các đơn đang theo dõi nhắc việc.
        $ids = collect($request->input('ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return response()->json(['orders' => []]);
        }

        $orders = Order::query()
            ->whereIn('id', $ids)
            ->get(['id', 'status'])
            ->mapWithKeys(fn ($order) => [
                (string) $order->id => [
                    'status' => $order->status,
                ],
            ]);

        return response()->json(['orders' => $orders]);
    }

    public function confirmedOrderReminderIds()
    {
        // Trả về tất cả id đơn còn bước xử lý tiếp theo để đồng bộ nhắc việc giữa các tab.
        $ids = Order::query()
            ->whereIn('status', ['pending', 'confirmed', 'processing', 'ready'])
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values();

        return response()->json(['ids' => $ids]);
    }

    public function dailyRevenueReport()
    {
        // Trang doanh thu ngày dùng chung data provider với trang doanh thu tháng.
        return view('staff.current-day-revenue', $this->getRevenueSnapshotData());
    }

    public function monthlyRevenueReport()
    {
        // Giao diện staff đã bỏ mục doanh thu tháng, giữ route cũ và chuyển về trang doanh thu ngày.
        return redirect()->route('staff.revenue.daily');
    }

    // ─── Chi tiết đơn hàng ───────────────────────────────────────────────────

    public function orderDetail($id)
    {
        // Nạp đầy đủ các quan hệ của đơn để staff xem và thao tác ngay trên trang chi tiết.
        $order = Order::with([
            'user',
            'address',
            'items.product',
            'items.extras',
            'payment',
            'staff',
        ])->findOrFail($id);

        return view('staff.order-detail', compact('order'));
    }

    // ─── Cập nhật trạng thái đơn ─────────────────────────────────────────────

    public function updateStatus(Request $request, $id)
    {
        // updateStatus là luồng trung tâm để staff chuyển bước đơn hàng hoặc hủy đơn.
        $order = Order::with(['payment', 'user'])->findOrFail($id);
        $cancelReasonOptions = [
            'change_option' => 'Thay đổi topping/kích cỡ',
            'no_longer_needed' => 'Không còn nhu cầu mua',
            'other' => 'Lý do khác',
        ];

        // Với đơn chuyển khoản, không được xác nhận đơn trước khi payment đã paid.
        if (
            $request->status === 'confirmed' &&
            $order->payment &&
            $order->payment->method === 'bank_transfer' &&
            $order->payment->status !== 'paid'
        ) {
            return back()->with('error', 'Cần xác nhận thanh toán chuyển khoản trước khi xác nhận đơn hàng.');
        }

        $validNext = $order->next_statuses;

        // Chặn các trạng thái không nằm trong flow hợp lệ của model Order.
        if (!in_array($request->status, $validNext)) {
            return back()->with('error', 'Trạng thái không hợp lệ cho đơn hàng này.');
        }

        $nextStatus = $request->status;

        // Nếu staff hủy đơn thì bắt buộc lưu lại lý do để truy vết lịch sử thao tác.
        if ($nextStatus === 'cancelled') {
            $reasonKey = (string) $request->input('cancel_reason', '');

            if (!array_key_exists($reasonKey, $cancelReasonOptions)) {
                return back()->with('error', 'Vui lòng chọn lý do hủy đơn.');
            }

            $cancelReasonText = $cancelReasonOptions[$reasonKey];

            if ($reasonKey === 'other') {
                $customReason = trim((string) $request->input('cancel_reason_other', ''));

                if ($customReason === '') {
                    return back()->with('error', 'Vui lòng nhập lý do hủy đơn khác.');
                }

                $cancelReasonText = $customReason;
            }

            $existingNote = trim((string) $order->note);
            $order->note = trim(($existingNote !== '' ? $existingNote . ' | ' : '') . 'Lý do hủy: ' . $cancelReasonText);
        }

        $order->status = $nextStatus;

        // Gán nhân viên nếu vừa xác nhận
        if ($nextStatus === 'confirmed' && !$order->assigned_staff_id) {
            $order->assigned_staff_id = Auth::id();
        }

        $order->save();

        // Xử lý điểm tích lũy khi hủy đơn.
        if ($nextStatus === 'cancelled') {
            $orderUser = $order->user;
            if ($orderUser) {
                $pointsUsed   = (int) ($order->points_used ?? 0);
                $wasPaid      = optional($order->payment)->status === 'paid';
                $pointsEarned = $wasPaid ? (int) floor($order->final_price / 10) : 0;
                // Hoàn điểm đã dùng, trừ điểm đã nhận (nếu đơn đã thanh toán).
                $orderUser->loyalty_points = max(0, $orderUser->loyalty_points + $pointsUsed - $pointsEarned);
                $orderUser->save();
            }
}

        // Nếu giao thành công → cập nhật payment
        if ($nextStatus === 'delivered') {
            $order->payment?->update([
                'status'  => 'paid',
                'paid_at' => now(),
            ]);

            $this->syncDailyRevenueSnapshots();
        }

        $response = back()->with('success', 'Cập nhật trạng thái thành công!');

        if (in_array($nextStatus, ['confirmed', 'processing', 'ready'], true)) {
            return $response->with('start_order_reminder_id', $order->id);
        }

        if (in_array($nextStatus, ['delivered', 'cancelled', 'failed'], true)) {
            return $response->with('clear_order_reminder_id', $order->id);
        }

        return $response;
    }

    public function confirmPayment($id)
    {
        // Staff dùng endpoint này để xác nhận khách đã chuyển khoản thành công.
        $order = Order::with('payment')->findOrFail($id);

        if (!$order->payment || $order->payment->method !== 'bank_transfer') {
            return back()->with('error', 'Đơn hàng này không sử dụng thanh toán QR/chuyển khoản.');
        }

        if ($order->payment->status === 'paid') {
            return back()->with('success', 'Đơn hàng này đã được xác nhận khách thanh toán QR trước đó.');
        }

        // Cập nhật payment và order trong cùng transaction để tránh lệch trạng thái.
        DB::transaction(function () use ($order) {
            $order->payment->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            if (in_array($order->status, ['pending', 'confirmed'], true)) {
                $order->status = 'confirmed';
            }

            if (!$order->assigned_staff_id) {
                $order->assigned_staff_id = Auth::id();
            }

            $order->save();

            DB::table('carts')->where('user_id', $order->user_id)->delete();
        });

        // Cộng điểm tích lũy cho khách = số tiền thực trả / 10 (10đ = 1 điểm).
        if ($order->user_id) {
            $orderUser = \App\Models\User::find($order->user_id);
            if ($orderUser) {
                $orderUser->loyalty_points = $orderUser->loyalty_points + (int) floor($order->final_price / 10);
                $orderUser->save();
            }
        }

        // Khi payment chuyển sang paid thì snapshot doanh thu cũng cần cập nhật.
        $this->syncDailyRevenueSnapshots();

        return back()
            ->with('success', 'Đã xác nhận khách hàng chuyển khoản thành công. Đơn hàng đang ở bước đã xác nhận.')
            ->with('start_order_reminder_id', $order->id);
    }

    // ─── Tạo đơn tại quán ────────────────────────────────────────────────────

    public function createInStoreOrder()
    {
        // Nạp menu nhóm theo category để staff tạo đơn tại quán thuận tiện hơn.
        $products = Product::where('status', 'available')
            ->with('category')
            ->orderBy('category_id')
            ->get()
            ->groupBy(fn($p) => $p->category->name ?? 'Khác');

        $sizes = \App\Models\Size::orderBy('extra_price')->get();
        $toppings = \App\Models\Extra::topping()->orderBy('name')->get();
        $sugars = \App\Models\Extra::sugar()->orderBy('name')->get();
        $ices = \App\Models\Extra::ice()->orderBy('name')->get();
        $nextOrderNumber = ((int) Order::max('id')) + 1;

        return view('staff.create-order', compact('products', 'sizes', 'toppings', 'sugars', 'ices', 'nextOrderNumber'));
    }

    public function storeInStoreOrder(Request $request)
    {
        // Validate payload đơn tại quán gồm danh sách món và thông tin thanh toán.
        $request->validate([
            'items'           => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|integer|min:1',
            'items.*.size'       => 'nullable|string|max:10',
            'items.*.sugar'      => 'nullable|string|max:50',
            'items.*.ice'        => 'nullable|string|max:50',
            'items.*.note'       => 'nullable|string|max:200',
            'items.*.toppings'   => 'nullable|array',
            'items.*.toppings.*' => 'string|max:100',
            'payment_method'     => 'nullable|string|max:50',
            'qr_note'            => 'nullable|string|max:50',
            'note'               => 'nullable|string|max:500',
            'customer_phone'     => 'nullable|string|max:20',
            'use_points'         => 'nullable|boolean',
        ]);

        // Tra cứu khách hàng theo SĐT nếu staff có nhập để gắn đơn vào tài khoản khách.
        $customerUser = null;
        if ($request->filled('customer_phone')) {
            $cleanPhone = preg_replace('/\D/', '', trim($request->customer_phone));
            if ($cleanPhone !== '') {
                $customerUser = \App\Models\User::where('phone', $cleanPhone)->where('role_id', 3)->first();
            }
        }

        // Tạo đơn và payment tại quán trong transaction để rollback được nếu có lỗi.
        DB::beginTransaction();
        try {
            $totalPrice = 0;
            $orderItems = [];

            // Chuẩn bị dữ liệu từng món, tính giá và dựng note option trước khi insert.
            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $qty     = (int) $item['quantity'];
                $sizeName = $item['size'] ?? 'S';
                $size = \App\Models\Size::where('name', $sizeName)->first();
                $sizeExtra = (float) ($size->extra_price ?? 0);
                $toppingNames = collect($item['toppings'] ?? [])->filter()->values();
                $toppingModels = $toppingNames->isEmpty()
                    ? collect()
                    : \App\Models\Extra::topping()->whereIn('name', $toppingNames)->get();
                $toppingTotal = (float) $toppingModels->sum('price');
                $unitPrice = (float) $product->price + $sizeExtra;
                $linePrice = $unitPrice + $toppingTotal;

                $itemNoteParts = [
                    'Size: ' . $sizeName,
                    'Đường & sữa: ' . ($item['sugar'] ?? '-'),
                    'Đá: ' . ($item['ice'] ?? '-'),
                ];

                if (!empty($item['note'])) {
                    $itemNoteParts[] = 'Ghi chú: ' . $item['note'];
                }

                $totalPrice += $linePrice * $qty;

                $orderItems[] = [
                    'product'  => $product,
                    'quantity' => $qty,
                    'price'    => $unitPrice,
                    'note'     => implode(' | ', $itemNoteParts),
                    'toppings' => $toppingModels,
                ];
            }

            // Tính giảm giá bằng điểm nếu khách đồng ý dùng điểm.
            $pointsUsed = 0;
            $discountAmount = 0;
            if ($customerUser && $request->boolean('use_points') && $customerUser->loyalty_points > 0) {
                $maxDiscount = $this->calcMaxPointsDiscountForStaff((int) $totalPrice, (int) $customerUser->loyalty_points);
                $pointsUsed  = $maxDiscount;
                $discountAmount = $pointsUsed;
            }
            $finalPrice = max(0, $totalPrice - $discountAmount);

            // Xác định phương thức thanh toán và note của đơn tại quán.
            $paymentMethod = $request->payment_method ?? 'cash';
            $qrNote = trim((string) $request->input('qr_note', ''));
            $orderNote = trim((string) $request->input('note', ''));

            if ($paymentMethod === 'bank_transfer' && $qrNote !== '') {
                $orderNote = trim($orderNote . ' | Mã chuyển khoản: ' . $qrNote, ' |');
            }

            // Các phương thức đã trả tiền sẽ bắt đầu từ confirmed để đi tiếp sang processing/ready/delivered.
            $initialStatus = in_array($paymentMethod, ['cash', 'bank_transfer', 'momo', 'vnpay'], true)
                ? 'confirmed'
                : 'delivered';

            // Tạo đơn gốc tại quán do staff hiện tại phụ trách.
            // Nếu khách có tài khoản → gán user_id của khách để đơn xuất hiện trong lịch sử của họ.
            $orderUserId = $customerUser ? $customerUser->id : Auth::id();
            $order = Order::create([
                'user_id'           => $orderUserId,
                'assigned_staff_id' => Auth::id(),
                'status'            => $initialStatus,
                'total_price'       => $totalPrice,
                'discount_amount'   => $discountAmount,
                'points_used'       => $pointsUsed,
                'final_price'       => $finalPrice,
                'note'              => $orderNote !== '' ? $orderNote : 'Bán tại quán',
                'created_at'        => now(),
            ]);

            // Insert từng order item và các topping đi kèm.
            foreach ($orderItems as $item) {
                $orderItem = OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $item['product']->id,
                    'quantity'   => $item['quantity'],
                    'unit_price' => $item['price'],
                    'note'       => $item['note'],
                ]);

                $extraRows = $item['toppings']->map(function ($extra) use ($orderItem) {
                    return [
                        'order_item_id' => $orderItem->id,
                        'extra_id' => $extra->id,
                        'extra_name' => $extra->name,
                        'extra_price' => $extra->price,
                    ];
                })->all();

                if (!empty($extraRows)) {
                    DB::table('order_item_extras')->insert($extraRows);
                }
            }

            // Ghi payment paid ngay vì đây là flow staff xác nhận trực tiếp tại quầy.
            Payment::create([
                'order_id' => $order->id,
                'method'   => $paymentMethod,
                'status'   => 'paid',
                'amount'   => $finalPrice,
                'paid_at'  => now(),
                'ref_code' => $paymentMethod === 'bank_transfer' && $qrNote !== '' ? $qrNote : null,
            ]);

            DB::commit();

            // Cập nhật điểm tích lũy cho khách: trừ điểm đã dùng, cộng điểm từ đơn mới.
            if ($customerUser) {
                $pointsEarned = (int) floor($finalPrice / 10);
                $customerUser->loyalty_points = max(0, $customerUser->loyalty_points - $pointsUsed) + $pointsEarned;
                $customerUser->save();
            }

            // Đồng bộ báo cáo sau khi đơn tại quán đã được tạo và thanh toán.
            $this->syncDailyRevenueSnapshots();

            $successMsg = 'Tạo đơn tại quán thành công! Mã đơn #' . $order->id;
            if ($customerUser) {
                $pointsEarned = (int) floor($finalPrice / 10);
                $parts = [];
                if ($pointsUsed > 0) $parts[] = 'giảm ' . number_format($pointsUsed, 0, ',', '.') . 'đ bằng điểm';
                $parts[] = 'cộng ' . $pointsEarned . ' điểm';
                $successMsg .= ' — ' . $customerUser->name . ': ' . implode(', ', $parts) . '.';
            }

            $response = redirect()->route('staff.order.detail', $order->id)
                ->with('success', $successMsg);

            if ($initialStatus === 'confirmed') {
                $response->with('start_order_reminder_id', $order->id);
            }

            return $response;

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Lỗi khi tạo đơn: ' . $e->getMessage());
        }
    }

    // Alias để map với các route cũ/tên route đang dùng trong dự án.
    public function createOrder() { return $this->createInStoreOrder(); }
    public function storeOrder(Request $request) { return $this->storeInStoreOrder($request); }

    // Tìm khách hàng theo SĐT để staff tra cứu nhanh khi tạo đơn tại quán.
    public function lookupCustomer(Request $request): \Illuminate\Http\JsonResponse
    {
        $phone = preg_replace('/\D/', '', trim((string) $request->input('phone', '')));

        if (strlen($phone) < 9) {
            return response()->json(['found' => false]);
        }

        $user = \App\Models\User::where('phone', $phone)->where('role_id', 3)->first();

        if (!$user) {
            return response()->json(['found' => false]);
        }

        // Tính số điểm tối đa có thể dùng (dựa trên tổng đơn nếu truyền lên, hoặc trả điểm thô).
        $baseTotal = (int) $request->input('total', 0);
        $maxDiscount = $baseTotal > 0 ? $this->calcMaxPointsDiscountForStaff($baseTotal, (int) $user->loyalty_points) : null;

        return response()->json([
            'found'          => true,
            'id'             => $user->id,
            'name'           => $user->name,
            'phone'          => $user->phone,
            'loyalty_points' => $user->loyalty_points,
            'max_discount'   => $maxDiscount,
        ]);
    }

    // Tính số điểm có thể dùng tối đa cho một đơn tại quán (copy logic CartController).
    private function calcMaxPointsDiscountForStaff(int $baseTotal, int $userPoints): int
    {
        if ($baseTotal >= 300000) {
            $max = (int) floor($baseTotal * 0.1);
        } else {
            $remainder = $baseTotal % 10000;
            $max = $remainder === 0 ? (int) floor($baseTotal * 0.1) : $remainder;
        }
        return min($max, $userPoints);
    }

    // Mở trang xem/sửa đơn hiện tại cho staff.
    public function editOrder($id) {
        $order = Order::with(['user','items.product','payment'])->findOrFail($id);
        return view('staff.order-detail', compact('order'));
    }

    // Cập nhật nhanh note của đơn.
    public function updateOrder(Request $request, $id) {
        Order::findOrFail($id)->update(['note' => $request->note]);
        return redirect()->route('staff.order.detail', $id)->with('success', 'Đã cập nhật đơn hàng.');
    }

    // Chỉ cho xóa hẳn những đơn đã kết thúc lỗi/hủy để tránh mất dữ liệu của đơn đang chạy.
    public function deleteOrder($id) {
        $order = Order::findOrFail($id);
        if (!in_array($order->status, ['cancelled','failed']))
            return back()->with('error', 'Chỉ xóa đơn đã hủy hoặc thất bại.');
        $order->delete();
        return redirect()->route('staff.orders')->with('success', "Đã xóa đơn #$id");
    }
    public function invoice($id) {
        // Mở chế độ in hóa đơn bằng chính view chi tiết đơn với cờ printMode.
        $order = Order::with(['user','items.product','items.extras','payment'])->findOrFail($id);
        return view('staff.order-detail', ['order'=>$order, 'printMode'=>true]);
    }

    // Gán nhân viên phụ trách cho đơn hàng.
    public function assignStaff(Request $request, $id) {
        Order::findOrFail($id)->update(['assigned_staff_id' => $request->staff_id]);
        return back()->with('success', 'Đã phân công nhân viên.');
    }

    // Trả về danh sách slot cố định theo loại nhân viên.
    private function getScheduleSlotsByEmploymentType(?string $employmentType): array
    {
        if ($employmentType === 'part_time') {
            return [
                '08_12' => ['start' => '08:00', 'end' => '12:00', 'label' => '8h-12h'],
                '12_16' => ['start' => '12:00', 'end' => '16:00', 'label' => '12h-16h'],
                '16_20' => ['start' => '16:00', 'end' => '20:00', 'label' => '16h-20h'],
                // Dùng 23:59 để biểu diễn slot 20h-24h trên cột time của SQL.
                '20_24' => ['start' => '20:00', 'end' => '23:59', 'label' => '20h-24h'],
            ];
        }

        if ($employmentType === 'full_time') {
            return [
                '08_16' => ['start' => '08:00', 'end' => '16:00', 'label' => '8h-16h'],
                // Dùng 23:59 để biểu diễn slot 16h-24h trên cột time của SQL.
                '16_24' => ['start' => '16:00', 'end' => '23:59', 'label' => '16h-24h'],
            ];
        }

        return [];
    }

    // Resolve slot key từ start/end time đã lưu trong DB.
    private function resolveSlotKey(string $employmentType, string $startTime, string $endTime): ?string
    {
        foreach ($this->getScheduleSlotsByEmploymentType($employmentType) as $slotKey => $slot) {
            if ($slot['start'] === substr($startTime, 0, 5) && $slot['end'] === substr($endTime, 0, 5)) {
                return $slotKey;
            }
        }

        return null;
    }

    // Mỗi slot có sức chứa khác nhau theo loại nhân viên.
    private function getScheduleSlotCapacity(?string $employmentType): int
    {
        return $employmentType === 'part_time' ? 2 : 1;
    }

    // Hiển thị trang đăng ký giờ làm và chia danh sách theo full-time / part-time.
    public function workSchedules()
    {
        $currentStaff = Auth::user();
        $weekStart = now()->startOfWeek(Carbon::MONDAY);
        $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);
        $isAutoClosedAtNight = now()->greaterThanOrEqualTo(now()->copy()->setTime(22, 0));
        $weekDays = collect(range(0, 6))->map(fn ($offset) => $weekStart->copy()->addDays($offset));
        $weekBoardLock = WorkScheduleBoardLock::with('locker:id,name')
            ->whereDate('week_start', $weekStart->toDateString())
            ->first();
        $isScheduleBoardLocked = (bool) $weekBoardLock || $isAutoClosedAtNight;
        $allowedSlots = $this->getScheduleSlotsByEmploymentType($currentStaff->employment_type);

        $weeklyAssignments = [];
        $mySelectedDates = [];

        if ($currentStaff->employment_type) {
            // Lấy đăng ký của đúng nhóm employment_type trong tuần hiện tại để khóa slot trên bảng tuần.
            $registrations = WorkScheduleRegistration::with('staff:id,name')
                ->where('employment_type', $currentStaff->employment_type)
                ->whereBetween('work_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
                ->get();

            foreach ($registrations as $registration) {
                $slotKey = $this->resolveSlotKey(
                    $currentStaff->employment_type,
                    (string) $registration->start_time,
                    (string) $registration->end_time
                );

                if (!$slotKey) {
                    continue;
                }

                $dateKey = Carbon::parse($registration->work_date)->toDateString();
                $weeklyAssignments[$dateKey][$slotKey] ??= [];
                $weeklyAssignments[$dateKey][$slotKey][] = $registration;

                if ((int) $registration->staff_id === (int) $currentStaff->id) {
                    $mySelectedDates[$dateKey] = true;
                }
            }
        }

        // Lấy riêng các đăng ký của nhân viên hiện tại để hiển thị lịch sử vừa đăng ký.
        $myRegistrations = WorkScheduleRegistration::query()
            ->where('staff_id', $currentStaff->id)
            ->orderByDesc('work_date')
            ->orderByDesc('start_time')
            ->take(6)
            ->get();

        // Lấy danh sách tăng ca của nhân viên hiện tại.
        $myOvertimes = Overtime::query()
            ->where('staff_id', $currentStaff->id)
            ->orderByDesc('overtime_date')
            ->take(6)
            ->get();

        return view('staff.work-schedules', compact(
            'currentStaff',
            'weekStart',
            'weekEnd',
            'weekDays',
            'weekBoardLock',
            'isAutoClosedAtNight',
            'isScheduleBoardLocked',
            'allowedSlots',
            'weeklyAssignments',
            'mySelectedDates',
            'myRegistrations',
            'myOvertimes'
        ));
    }

    // Nhân viên tự đăng ký slot cố định trong tuần hiện tại.
    public function storeWorkSchedule(Request $request)
    {
        $staff = Auth::user();

        // Chỉ nhân viên đã được phân loại full-time/part-time mới được đăng ký ca.
        if (!$staff->employment_type) {
            return back()->with('error', 'Nhân viên chưa được phân loại full-time hoặc part-time. Vui lòng liên hệ admin để cập nhật trước khi đăng ký giờ làm.');
        }

        $allowedSlots = $this->getScheduleSlotsByEmploymentType($staff->employment_type);
        $weekStart = now()->startOfWeek(Carbon::MONDAY);
        $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);
        $isAutoClosedAtNight = now()->greaterThanOrEqualTo(now()->copy()->setTime(22, 0));

        // Nếu admin đã đóng bảng tuần thì staff không thể đăng ký thêm.
        $isBoardLocked = WorkScheduleBoardLock::query()
            ->whereDate('week_start', $weekStart->toDateString())
            ->exists();

        if ($isBoardLocked) {
            return back()->with('error', 'Bảng đăng ký giờ làm tuần này đã được admin đóng. Bạn không thể đăng ký thêm.');
        }

        if ($isAutoClosedAtNight) {
            return back()->with('error', 'Bảng đăng ký giờ làm tự động đóng sau 22:00. Vui lòng đăng ký vào ngày mai.');
        }

        $data = $request->validate([
            'work_date' => 'required|date',
            'slot_key' => ['required', Rule::in(array_keys($allowedSlots))],
        ]);

        $workDate = Carbon::parse($data['work_date'])->startOfDay();

        // Chỉ cho đăng ký trong tuần hiện tại (thứ 2 -> chủ nhật) và không cho đăng ký ngày đã qua.
        if ($workDate->lt(now()->startOfDay()) || $workDate->lt($weekStart->startOfDay()) || $workDate->gt($weekEnd->endOfDay())) {
            return back()
                ->withInput()
                ->withErrors(['work_date' => 'Chỉ được đăng ký trong tuần hiện tại và từ ngày hôm nay trở đi.']);
        }

        $selectedSlot = $allowedSlots[$data['slot_key']];
        $slotCapacity = $this->getScheduleSlotCapacity($staff->employment_type);

        // Mỗi nhân viên chỉ được có đúng 1 ca trong 1 ngày.
        $myRegistrationOnDate = WorkScheduleRegistration::query()
            ->where('staff_id', $staff->id)
            ->whereDate('work_date', $workDate->toDateString())
            ->first();

        if ($myRegistrationOnDate) {
            $isSameSlot =
                substr((string) $myRegistrationOnDate->start_time, 0, 5) === $selectedSlot['start']
                && substr((string) $myRegistrationOnDate->end_time, 0, 5) === $selectedSlot['end'];

            if ($isSameSlot) {
                return back()->with('success', 'Bạn đã đăng ký khung giờ này trước đó.');
            }

            return back()->with('error', 'Mỗi nhân viên chỉ được chọn một ca trong một ngày.');
        }

        // Slot được phép theo sức chứa: full-time 1 người, part-time 2 người.
        $existingRegistrations = WorkScheduleRegistration::query()
            ->where('employment_type', $staff->employment_type)
            ->whereDate('work_date', $workDate->toDateString())
            ->where('start_time', $selectedSlot['start'])
            ->where('end_time', $selectedSlot['end'])
            ->get();

        if ($existingRegistrations->contains(fn ($registration) => (int) $registration->staff_id === (int) $staff->id)) {
            return back()->with('success', 'Bạn đã đăng ký khung giờ này trước đó.');
        }

        if ($existingRegistrations->count() >= $slotCapacity) {
            return back()->with('error', 'Khung giờ này đã có nhân viên khác chọn, vui lòng chọn khung giờ khác.');
        }

        // Lưu snapshot loại nhân viên để khi đổi loại staff thì lịch cũ vẫn giữ đúng ngữ cảnh.
        WorkScheduleRegistration::create([
            'staff_id' => $staff->id,
            'employment_type' => $staff->employment_type,
            'work_date' => $workDate->toDateString(),
            'start_time' => $selectedSlot['start'],
            'end_time' => $selectedSlot['end'],
            'shift_label' => $selectedSlot['label'],
            'note' => null,
        ]);

        return redirect()->route('staff.work-schedules.index')->with('success', 'Đăng ký giờ làm thành công.');
    }

    // Ghi nhận bắt đầu ca làm của staff hiện tại vào bảng shifts.
    public function startShift(Request $request) {
        \Illuminate\Support\Facades\DB::table('shifts')->insert([
            'staff_id'=>auth()->id(),'start_time'=>now(),'created_at'=>now()
        ]);
        return back()->with('success', 'Đã bắt đầu ca.');
    }

    // Kết thúc ca làm gần nhất chưa đóng của staff hiện tại.
    public function endShift(Request $request) {
        $shift = \Illuminate\Support\Facades\DB::table('shifts')->where('staff_id',auth()->id())->whereNull('end_time')->latest('start_time')->first();
        if ($shift) \Illuminate\Support\Facades\DB::table('shifts')->where('id',$shift->id)->update(['end_time'=>now()]);
        return back()->with('success', 'Đã kết thúc ca.');
    }

    // Nhân viên đăng ký giờ tăng ca.
    public function storeOvertime(Request $request)
    {
        $validated = $request->validate([
            'hours' => 'required|numeric|min:0.5|max:2',
            'notes' => 'nullable|string|max:500',
        ], [
            'hours.required' => 'Vui lòng nhập số giờ tăng ca.',
            'hours.numeric' => 'Số giờ phải là số.',
            'hours.min' => 'Số giờ tối thiểu là 0.5 giờ.',
            'hours.max' => 'Số giờ tối đa là 2 giờ.',
        ]);

        $staff = Auth::user();
        $overtimeDate = now()->toDateString();

        // Kiểm tra xem đã có đăng ký tăng ca cho ngày này chưa.
        $existing = Overtime::where('staff_id', $staff->id)
            ->whereDate('overtime_date', $overtimeDate)
            ->first();

        if ($existing) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Bạn đã đăng ký tăng ca cho ngày này rồi.',
                ], 422);
            }

            return back()->with('error', 'Bạn đã đăng ký tăng ca cho ngày này rồi.');
        }

        $overtime = Overtime::create([
            'staff_id' => $staff->id,
            'overtime_date' => $overtimeDate,
            'hours' => $validated['hours'],
            'notes' => $validated['notes'],
            'status' => 'pending',
        ]);

        if ($request->expectsJson()) {
            $hours = (float) $overtime->hours;

            return response()->json([
                'message' => 'Đăng ký tăng ca thành công. Chờ admin duyệt.',
                'overtime' => [
                    'overtime_date' => Carbon::parse($overtime->overtime_date)->format('d/m/Y'),
                    'hours' => fmod($hours, 1.0) === 0.0 ? (int) $hours : $hours,
                    'status' => $overtime->status,
                    'notes' => $overtime->notes,
                    'staff_name' => $staff->name,
                ],
            ], 201);
        }

        return back()->with('success', 'Đăng ký tăng ca thành công. Chờ admin duyệt.');
    }
}

