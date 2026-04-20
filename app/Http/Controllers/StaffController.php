<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\DailyRevenue;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\OrderItem;
use App\Models\OrderItemExtra;
use App\Models\Payment;
use App\Support\DailyRevenueSnapshotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        $historyStart = now()->subDays(29)->startOfDay();
        $isStaffCreatedOrder = fn ($order) => in_array((int) optional($order->user)->role_id, [1, 2], true)
            && $order->order_type === 'in_store';
        $isWebAppOrder = fn ($order) => (int) optional($order->user)->role_id === 3;

        // Mỗi lần vào trang báo cáo sẽ sync lại snapshot để hạn chế lệch dữ liệu hiển thị.
        $this->syncDailyRevenueSnapshots();

        // Lấy danh sách snapshot doanh thu 30 ngày gần nhất từ bảng daily_revenues.
        $dailyRevenue = DailyRevenue::query()
            ->where('revenue_date', '>=', $historyStart->toDateString())
            ->orderByDesc('revenue_date')
            ->get();

        // Lấy snapshot của hôm nay, nếu chưa có thì dựng object rỗng để view không lỗi.
        $todayRevenue = DailyRevenue::query()
            ->whereDate('revenue_date', today())
            ->first()
            ?? new DailyRevenue([
                'revenue_date' => now()->toDateString(),
                'total_orders' => 0,
                'total_revenue' => 0,
                'staff_created_revenue' => 0,
                'customer_revenue' => 0,
                'cash_revenue' => 0,
                'transfer_revenue' => 0,
            ]);

        // Query riêng các đơn paid trong ngày để tách số đơn theo nguồn tạo đơn.
        $todayOrders = Order::with(['payment', 'user'])
            ->whereDate('created_at', today())
            ->whereHas('payment', fn ($query) => $query->where('status', 'paid'))
            ->where(function ($query) {
                $query->where(function ($staffQuery) {
                    $staffQuery->where('order_type', 'in_store')
                        ->whereHas('user', fn ($userQuery) => $userQuery->whereIn('role_id', [1, 2]));
                })->orWhereHas('user', fn ($userQuery) => $userQuery->where('role_id', 3));
            })
            ->get();

        // Thống kê số đơn trong ngày theo 2 nguồn: staff tạo tại quán và khách tự đặt trên web.
        $todayOrderBreakdown = [
            'total_orders' => (int) $todayOrders->count(),
            'staff_created_orders' => (int) $todayOrders
                ->filter($isStaffCreatedOrder)
                ->count(),
            'web_app_orders' => (int) $todayOrders
                ->filter($isWebAppOrder)
                ->count(),
        ];

        // Summary chung cho khối thống kê tổng quan của 30 ngày gần nhất.
        $summary = [
            'combined_revenue' => (float) $dailyRevenue->sum('total_revenue'),
            'staff_created_revenue' => (float) $dailyRevenue->sum('staff_created_revenue'),
            'customer_revenue' => (float) $dailyRevenue->sum('customer_revenue'),
            'cash_revenue' => (float) $dailyRevenue->sum('cash_revenue'),
            'transfer_revenue' => (float) $dailyRevenue->sum('transfer_revenue'),
            'active_days' => $dailyRevenue->count(),
            'range_label' => $historyStart->format('d/m/Y') . ' - ' . now()->format('d/m/Y'),
        ];

        return compact('dailyRevenue', 'todayRevenue', 'todayOrderBreakdown', 'summary');
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
        $query = Order::with(['user', 'items.product', 'payment', 'address']);

        // Lọc theo trạng thái
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Lọc theo loại đơn
        if ($request->filled('type')) {
            $query->where('order_type', $request->type);
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

    public function createdOrderHistory()
    {
        // Chỉ lấy các đơn tại quán do chính nhân viên hiện tại tạo trong 1 tháng gần nhất.
        $staffId = Auth::id();
        $historyStart = now()->subMonth()->startOfDay();

        $orders = Order::with(['items.product', 'payment'])
            ->where('assigned_staff_id', $staffId)
            ->where('order_type', 'in_store')
            ->where('created_at', '>=', $historyStart)
            ->orderBy('created_at', 'desc')
            ->get();

        $revenueOrders = Order::with(['payment', 'user'])
            ->where('created_at', '>=', $historyStart)
            ->whereHas('payment', fn ($query) => $query->where('status', 'paid'))
            ->where(function ($query) use ($staffId) {
                $query->where(function ($staffQuery) use ($staffId) {
                    $staffQuery->where('assigned_staff_id', $staffId)
                        ->where('order_type', 'in_store');
                })->orWhereHas('user', fn ($userQuery) => $userQuery->where('role_id', 3));
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // Gom doanh thu theo từng ngày để view lịch sử hiển thị theo block ngày.
        $dailyGroups = $revenueOrders
            ->groupBy(fn ($order) => $order->created_at->format('Y-m-d'))
            ->map(function ($group, $date) use ($orders, $staffId) {
                $staffOrdersForDay = $orders->filter(fn ($order) => $order->created_at->format('Y-m-d') === $date)->values();
                $staffCreatedRevenue = (float) $group
                    ->filter(fn ($order) => (int) $order->assigned_staff_id === (int) $staffId && $order->order_type === 'in_store')
                    ->sum('final_price');
                $customerRevenue = (float) $group
                    ->filter(fn ($order) => optional($order->user)->role_id === 3)
                    ->sum('final_price');
                $cashRevenue = (float) $group
                    ->filter(fn ($order) => optional($order->payment)->method === 'cash')
                    ->sum('final_price');
                $transferRevenue = (float) $group
                    ->filter(fn ($order) => optional($order->payment)->method === 'bank_transfer')
                    ->sum('final_price');

                return [
                    'date' => $group->first()->created_at->copy()->startOfDay(),
                    'total_orders' => $group->count(),
                    'total_revenue' => $staffCreatedRevenue + $customerRevenue,
                    'staff_created_revenue' => $staffCreatedRevenue,
                    'customer_revenue' => $customerRevenue,
                    'cash_revenue' => $cashRevenue,
                    'transfer_revenue' => $transferRevenue,
                    'orders' => $staffOrdersForDay,
                ];
            })
            ->sortByDesc(fn ($group) => $group['date']->timestamp)
            ->values();

        // Summary tổng quan cho phần đầu trang lịch sử tạo đơn.
        $summary = [
            'total_orders' => $orders->count(),
            'total_revenue' => (float) $orders->sum('final_price'),
            'customer_revenue' => (float) $revenueOrders
                ->filter(fn ($order) => optional($order->user)->role_id === 3)
                ->sum('final_price'),
            'cash_revenue' => (float) $revenueOrders
                ->filter(fn ($order) => optional($order->payment)->method === 'cash')
                ->sum('final_price'),
            'transfer_revenue' => (float) $revenueOrders
                ->filter(fn ($order) => optional($order->payment)->method === 'bank_transfer')
                ->sum('final_price'),
            'combined_revenue' => (float) $revenueOrders->sum('final_price'),
            'active_days' => $dailyGroups->count(),
            'range_label' => $historyStart->format('d/m/Y') . ' - ' . now()->format('d/m/Y'),
        ];

        return view('staff.created-order-history', compact('dailyGroups', 'summary'));
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
        $order = Order::findOrFail($id);
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
        ]);

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
            $order = Order::create([
                'user_id'           => Auth::id(),
                'assigned_staff_id' => Auth::id(),
                'order_type'        => 'in_store',
                'status'            => $initialStatus,
                'total_price'       => $totalPrice,
                'discount_amount'   => 0,
                'shipping_fee'      => 0,
                'final_price'       => $totalPrice,
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
                'amount'   => $totalPrice,
                'paid_at'  => now(),
                'ref_code' => $paymentMethod === 'bank_transfer' && $qrNote !== '' ? $qrNote : null,
            ]);

            DB::commit();

            // Đồng bộ báo cáo sau khi đơn tại quán đã được tạo và thanh toán.
            $this->syncDailyRevenueSnapshots();

            $response = redirect()->route('staff.order.detail', $order->id)
                ->with('success', 'Tạo đơn tại quán thành công! Mã đơn #' . $order->id);

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

    // Mở trang xem/sửa đơn hiện tại cho staff.
    public function editOrder($id) {
        $order = Order::with(['user','items.product','address','payment'])->findOrFail($id);
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
        $order = Order::with(['user','address','items.product','items.extras','payment'])->findOrFail($id);
        return view('staff.order-detail', ['order'=>$order, 'printMode'=>true]);
    }

    // Gán nhân viên phụ trách cho đơn giao hàng hoặc đơn cần điều phối nội bộ.
    public function assignDelivery(Request $request, $id) {
        Order::findOrFail($id)->update(['assigned_staff_id' => $request->staff_id]);
        return back()->with('success', 'Đã phân công nhân viên.');
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
}
