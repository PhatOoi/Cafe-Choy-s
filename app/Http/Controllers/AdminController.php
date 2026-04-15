<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Category;
use App\Models\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    // ─── Dashboard ───────────────────────────────────────────────────────────

    public function dashboard()
    {
        // Tổng hợp các chỉ số chính để render dashboard admin.
        $stats = [
            'total_revenue'   => Order::where('status', 'delivered')->sum('final_price'),
            'today_revenue'   => Order::where('status', 'delivered')->whereDate('created_at', today())->sum('final_price'),
            'total_orders'    => Order::count(),
            'pending_orders'  => Order::where('status', 'pending')->count(),
            'total_products'  => Product::count(),
            'total_customers' => User::where('role_id', 3)->count(),
            'total_staff'     => User::where('role_id', 2)->count(),
        ];

        // Dữ liệu biểu đồ doanh thu 7 ngày gần nhất.
        $revenueChart = Order::where('status', 'delivered')
            ->selectRaw('DATE(created_at) as date, SUM(final_price) as total')
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top 5 sản phẩm bán chạy theo số lượng và doanh thu.
        $topProducts = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', 'delivered')
            ->selectRaw('products.name, SUM(order_items.quantity) as total_sold, SUM(order_items.quantity * order_items.unit_price) as revenue')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

        // 10 đơn gần nhất để admin theo dõi nhanh hoạt động hệ thống.
        $recentOrders = Order::with(['user', 'payment'])
            ->latest()
            ->take(10)
            ->get();

        return view('admin.dashboard', compact('stats', 'revenueChart', 'topProducts', 'recentOrders'));
    }

    // ─── Quản lý sản phẩm ────────────────────────────────────────────────────

    public function products(Request $request)
    {
        // Query sản phẩm kèm category để phục vụ lọc ở trang quản trị.
        $query = Product::with('category');

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $products   = $query->latest()->paginate(15)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function createProduct()
    {
        // Nạp danh mục cho form tạo sản phẩm mới.
        $categories = Category::orderBy('name')->get();
        return view('admin.products.create', compact('categories'));
    }

    public function storeProduct(Request $request)
    {
        // Validate dữ liệu đầu vào của form tạo sản phẩm.
        $data = $request->validate([
            'name'        => 'required|string|max:200',
            'category_id' => 'required|exists:categories,id',
            'price'       => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'status'      => 'required|in:available,unavailable',
            'image_url'   => 'nullable|url|max:500',
        ]);

        // Tạo record sản phẩm mới trong menu.
        Product::create($data);

        return redirect()->route('admin.products')->with('success', 'Thêm sản phẩm thành công!');
    }

    public function editProduct($id)
    {
        // Nạp sản phẩm hiện tại và danh mục để admin chỉnh sửa.
        $product    = Product::findOrFail($id);
        $categories = Category::orderBy('name')->get();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function updateProduct(Request $request, $id)
    {
        // Tìm sản phẩm và validate dữ liệu mới trước khi update.
        $product = Product::findOrFail($id);

        $data = $request->validate([
            'name'        => 'required|string|max:200',
            'category_id' => 'required|exists:categories,id',
            'price'       => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'status'      => 'required|in:available,unavailable',
            'image_url'   => 'nullable|url|max:500',
        ]);

        $product->update($data);

        return redirect()->route('admin.products')->with('success', 'Cập nhật sản phẩm thành công!');
    }

    public function destroyProduct($id)
    {
        // Xóa sản phẩm khỏi hệ thống.
        $product = Product::findOrFail($id);
        $product->delete();

        return back()->with('success', 'Đã xóa sản phẩm!');
    }

    // ─── Quản lý danh mục ────────────────────────────────────────────────────

    public function categories()
    {
        // Danh sách category kèm số lượng sản phẩm để admin quản lý nhanh.
        $categories = Category::withCount('products')->latest()->paginate(20);
        return view('admin.categories.index', compact('categories'));
    }

    public function storeCategory(Request $request)
    {
        // Tạo danh mục mới với tên duy nhất.
        $data = $request->validate([
            'name'        => 'required|string|max:100|unique:categories,name',
            'description' => 'nullable|string',
        ]);

        Category::create($data);
        return back()->with('success', 'Thêm danh mục thành công!');
    }

    public function updateCategory(Request $request, $id)
    {
        // Sửa thông tin danh mục nhưng vẫn giữ ràng buộc tên duy nhất.
        $category = Category::findOrFail($id);
        $data = $request->validate([
            'name'        => 'required|string|max:100|unique:categories,name,' . $id,
            'description' => 'nullable|string',
        ]);
        $category->update($data);
        return back()->with('success', 'Cập nhật danh mục thành công!');
    }

    public function destroyCategory($id)
    {
        // Chỉ cho phép xóa danh mục khi không còn sản phẩm phụ thuộc.
        $category = Category::findOrFail($id);
        if ($category->products()->count() > 0) {
            return back()->with('error', 'Không thể xóa danh mục đang có sản phẩm!');
        }
        $category->delete();
        return back()->with('success', 'Đã xóa danh mục!');
    }

    // ─── Quản lý nhân viên & người dùng ──────────────────────────────────────

    public function users(Request $request)
    {
        // Danh sách user kèm role để admin lọc và quản lý tài khoản.
        $query = User::with('role');

        if ($request->filled('role')) {
            $query->where('role_id', $request->role);
        }
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('name', 'like', "%$s%")->orWhere('email', 'like', "%$s%")->orWhere('phone', 'like', "%$s%"));
        }

        $users = $query->latest('created_at')->paginate(20)->withQueryString();
        $roles = UserRole::all();

        return view('admin.users.index', compact('users', 'roles'));
    }

    public function createUser()
    {
        // Chỉ nạp các role chính để tạo tài khoản nội bộ hoặc khách hàng.
        $roles = UserRole::whereIn('id', [1, 2, 3])->get(); // admin, staff, customer
        return view('admin.users.create', compact('roles'));
    }

    public function storeUser(Request $request)
    {
        // Validate dữ liệu tạo user mới.
        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'phone'    => 'nullable|string|max:20',
            'role_id'  => 'required|exists:user_roles,id',
        ]);

        $data['password']  = Hash::make($data['password']);
        $data['is_active'] = true;

        // Tạo user mới sau khi đã hash mật khẩu.
        User::create($data);

        return redirect()->route('admin.users')->with('success', 'Tạo tài khoản thành công!');
    }

    public function editUser($id)
    {
        // Nạp user hiện tại và danh sách role cho form chỉnh sửa.
        $user  = User::findOrFail($id);
        $roles = UserRole::all();
        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function updateUser(Request $request, $id)
    {
        // Cập nhật thông tin tài khoản, mật khẩu chỉ đổi khi admin nhập mới.
        $user = User::findOrFail($id);

        $data = $request->validate([
            'name'    => 'required|string|max:100',
            'email'   => 'required|email|unique:users,email,' . $id,
            'phone'   => 'nullable|string|max:20',
            'role_id' => 'required|exists:user_roles,id',
        ]);

        if ($request->filled('password')) {
            $request->validate(['password' => 'min:6|confirmed']);
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('admin.users')->with('success', 'Cập nhật tài khoản thành công!');
    }

    public function toggleUserActive($id)
    {
        // Bật/tắt trạng thái hoạt động của user để khóa/mở tài khoản nhanh.
        $user = User::findOrFail($id);

        // Không cho phép tự vô hiệu hóa chính mình
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Không thể thay đổi trạng thái tài khoản của chính bạn!');
        }

        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'kích hoạt' : 'vô hiệu hóa';
        return back()->with('success', "Đã $status tài khoản {$user->name}!");
    }

    public function destroyUser($id)
    {
        // Không cho admin xóa chính tài khoản đang đăng nhập.
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Không thể xóa tài khoản của chính bạn!');
        }

        $user->delete();
        return back()->with('success', 'Đã xóa tài khoản!');
    }

    // ─── Quản lý đơn hàng ────────────────────────────────────────────────────

    public function orders(Request $request)
    {
        // Nạp danh sách đơn cùng thông tin user, payment và staff để admin lọc theo nhiều tiêu chí.
        $query = Order::with(['user', 'payment', 'staff']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('id', 'like', "%$s%")->orWhereHas('user', fn($u) => $u->where('name', 'like', "%$s%")));
        }

        $orders      = $query->latest()->paginate(20)->withQueryString();
        $statusCounts = Order::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.orders.index', compact('orders', 'statusCounts'));
    }

    public function orderDetail($id)
    {
        // Xem chi tiết đầy đủ của một đơn cho admin kiểm tra.
        $order = Order::with(['user', 'address', 'items.product', 'items.extras', 'payment', 'staff'])->findOrFail($id);
        return view('admin.orders.detail', compact('order'));
    }

    // ─── Thống kê & Báo cáo ──────────────────────────────────────────────────

    public function reports(Request $request)
    {
        // period điều khiển cách gom dữ liệu doanh thu theo ngày/tháng/năm.
        $period = $request->input('period', 'month'); // day | month | year

        // Chọn nguồn dữ liệu biểu đồ phù hợp với period đang xem.
        $revenueData = match ($period) {
            'day'   => $this->revenueByDay(),
            'year'  => $this->revenueByYear(),
            default => $this->revenueByMonth(),
        };

        // Danh sách top sản phẩm bán chạy cho báo cáo tổng hợp.
        $topProducts = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', 'delivered')
            ->selectRaw('products.name, SUM(order_items.quantity) as total_sold, SUM(order_items.quantity * order_items.unit_price) as revenue')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_sold')
            ->limit(10)
            ->get();

        return view('admin.reports', compact('revenueData', 'topProducts', 'period'));
    }

    private function revenueByDay()
    {
        // Gom doanh thu delivered theo từng ngày trong 30 ngày gần nhất.
        return Order::where('status', 'delivered')
            ->selectRaw('DATE(created_at) as label, SUM(final_price) as total')
            ->where('created_at', '>=', now()->subDays(29)->startOfDay())
            ->groupBy('label')
            ->orderBy('label')
            ->get();
    }

    private function revenueByMonth()
    {
        // Gom doanh thu theo tháng cho 12 tháng gần nhất.
        return Order::where('status', 'delivered')
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as label, SUM(final_price) as total")
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->groupBy('label')
            ->orderBy('label')
            ->get();
    }

    private function revenueByYear()
    {
        // Gom doanh thu theo năm để nhìn xu hướng dài hạn.
        return Order::where('status', 'delivered')
            ->selectRaw("YEAR(created_at) as label, SUM(final_price) as total")
            ->groupBy('label')
            ->orderBy('label')
            ->get();
    }
   
}
