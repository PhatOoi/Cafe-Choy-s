<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\OrderItem;
use App\Models\OrderItemExtra;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StaffController extends Controller
{
    // ─── Dashboard ───────────────────────────────────────────────────────────

    public function dashboard()
    {
        $staffId = Auth::id();

        $stats = [
            'pending'    => Order::where('status', 'pending')->count(),
            'processing' => Order::whereIn('status', ['confirmed', 'processing', 'ready'])->count(),
            'delivering' => Order::where('status', 'delivering')->count(),
            'today'      => Order::whereDate('created_at', today())
                                 ->where('assigned_staff_id', $staffId)->count(),
        ];

        $recentOrders = Order::with(['user', 'items.product', 'payment'])
            ->whereNotIn('status', ['delivered', 'cancelled', 'failed'])
            ->orderByRaw("FIELD(status,'delivering','processing','confirmed','ready','pending')")
            ->orderBy('created_at', 'asc')
            ->take(10)
            ->get();

        return view('staff.dashboard', compact('stats', 'recentOrders'));
    }

    // ─── Danh sách đơn hàng ──────────────────────────────────────────────────

    public function orders(Request $request)
    {
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

    // ─── Chi tiết đơn hàng ───────────────────────────────────────────────────

    public function orderDetail($id)
    {
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
        $order = Order::findOrFail($id);

        $validNext = $order->next_statuses;

        if (!in_array($request->status, $validNext)) {
            return back()->with('error', 'Trạng thái không hợp lệ cho đơn hàng này.');
        }

        $order->status = $request->status;

        // Gán nhân viên nếu vừa xác nhận
        if ($request->status === 'confirmed' && !$order->assigned_staff_id) {
            $order->assigned_staff_id = Auth::id();
        }

        $order->save();

        // Nếu giao thành công → cập nhật payment
        if ($request->status === 'delivered') {
            $order->payment?->update([
                'status'  => 'paid',
                'paid_at' => now(),
            ]);
        }

        return back()->with('success', 'Cập nhật trạng thái thành công!');
    }

    // ─── Tạo đơn tại quán ────────────────────────────────────────────────────

    public function createInStoreOrder()
    {
        $products = Product::where('status', 'available')
            ->with('category')
            ->orderBy('category_id')
            ->get()
            ->groupBy(fn($p) => $p->category->name ?? 'Khác');

        return view('staff.create-order', compact('products'));
    }

    public function storeInStoreOrder(Request $request)
    {
        $request->validate([
            'items'           => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $totalPrice = 0;
            $orderItems = [];

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $qty     = (int) $item['quantity'];
                $price   = $product->price;
                $totalPrice += $price * $qty;

                $orderItems[] = [
                    'product'  => $product,
                    'quantity' => $qty,
                    'price'    => $price,
                    'note'     => $item['note'] ?? null,
                ];
            }

            $order = Order::create([
                'user_id'           => Auth::id(),
                'assigned_staff_id' => Auth::id(),
                'order_type'        => 'in_store',
                'status'            => 'delivered',
                'total_price'       => $totalPrice,
                'discount_amount'   => 0,
                'shipping_fee'      => 0,
                'final_price'       => $totalPrice,
                'note'              => $request->note ?? 'Bán tại quán',
                'created_at'        => now(),
            ]);

            foreach ($orderItems as $item) {
                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $item['product']->id,
                    'quantity'   => $item['quantity'],
                    'unit_price' => $item['price'],
                    'note'       => $item['note'],
                ]);
            }

            Payment::create([
                'order_id' => $order->id,
                'method'   => $request->payment_method ?? 'cash',
                'status'   => 'paid',
                'amount'   => $totalPrice,
                'paid_at'  => now(),
            ]);

            DB::commit();

            return redirect()->route('staff.order.detail', $order->id)
                ->with('success', 'Tạo đơn tại quán thành công! Mã đơn #' . $order->id);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Lỗi khi tạo đơn: ' . $e->getMessage());
        }
    }
}
