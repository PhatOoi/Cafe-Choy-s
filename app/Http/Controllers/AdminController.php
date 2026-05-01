<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Category;
use App\Models\Overtime;
use App\Models\Ingredient;
use App\Models\UserRole;
use App\Models\WorkScheduleBoardLock;
use App\Models\WorkScheduleRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    // Đơn giá theo giờ dùng cho bảng lương tạm tính nội bộ.
    private const PAYROLL_RATES = [
        'full_time' => 32000,
        'part_time' => 25000,
    ];

    // Đơn giá tăng ca theo loại nhân viên.
    private const OVERTIME_RATES = [
        'full_time' => 40000,
        'part_time' => 30000,
    ];

    private const HOLIDAY_HOURLY_MULTIPLIER = 4;

    // Ngày lễ cố định theo dương lịch (MM-DD).
    private const FIXED_HOLIDAYS = [
        '01-01', // Tết Dương lịch
        '04-30', // Giải phóng miền Nam
        '05-01', // Quốc tế lao động
        '09-02', // Quốc khánh
    ];

    // Các ngày lễ/tết theo năm (cập nhật thêm mỗi năm khi cần).
    private const SPECIAL_HOLIDAYS = [
        '2026-02-16',
        '2026-02-17',
        '2026-02-18',
        '2026-02-19',
        '2026-02-20',
    ];

    // Slot làm việc cố định theo loại nhân viên để render bảng tuần giống staff.
    private const SCHEDULE_SLOTS = [
        'full_time' => [
            '08_16' => ['start' => '08:00', 'end' => '16:00', 'label' => '8h-16h'],
            '16_24' => ['start' => '16:00', 'end' => '23:59', 'label' => '16h-24h'],
        ],
        'part_time' => [
            '08_12' => ['start' => '08:00', 'end' => '12:00', 'label' => '8h-12h'],
            '12_16' => ['start' => '12:00', 'end' => '16:00', 'label' => '12h-16h'],
            '16_20' => ['start' => '16:00', 'end' => '20:00', 'label' => '16h-20h'],
            '20_24' => ['start' => '20:00', 'end' => '23:59', 'label' => '20h-24h'],
        ],
    ];

    // Query doanh thu chuẩn: chỉ lấy các đơn đã thanh toán thành công.
    private function paidRevenueOrders()
    {
        return Order::query()->whereHas('payment', fn ($query) => $query->where('status', 'paid'));
    }

    // ─── Dashboard ───────────────────────────────────────────────────────────

    public function dashboard()
    {
        // Tổng hợp các chỉ số chính để render dashboard admin.
        $stats = [
            'total_revenue'   => $this->paidRevenueOrders()->sum('final_price'),
            'today_revenue'   => $this->paidRevenueOrders()->whereDate('created_at', today())->sum('final_price'),
            'total_orders'    => Order::count(),
            'pending_orders'  => Order::where('status', 'pending')->count(),
            'total_products'  => Product::count(),
            'total_customers' => User::where('role_id', 3)->count(),
            'total_staff'     => User::where('role_id', 2)->count(),
        ];

        // Dữ liệu biểu đồ doanh thu 7 ngày gần nhất.
        $revenueChart = $this->paidRevenueOrders()
            ->selectRaw('DATE(created_at) as date, SUM(final_price) as total')
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top 10 sản phẩm bán chạy nhất toàn thời gian (cả đơn staff và khách web app).
        $topProducts = $this->getTop10Products();

        // 10 đơn gần nhất để admin theo dõi nhanh hoạt động hệ thống.
        $recentOrders = Order::with(['user', 'payment'])
            ->latest()
            ->take(10)
            ->get();

        return view('admin.dashboard', compact('stats', 'revenueChart', 'topProducts', 'recentOrders'));
    }

    // Trả về top 10 sản phẩm bán chạy dạng JSON cho AJAX auto-refresh dashboard.
    public function topProductsJson(): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->getTop10Products());
    }

    // Query dùng chung để lấy top 10 sản phẩm bán chạy từ tất cả đơn đã thanh toán.
    private function getTop10Products(): \Illuminate\Support\Collection
    {
        return DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('payments', 'payments.order_id', '=', 'orders.id')
            ->where('payments.status', 'paid')
            ->selectRaw('products.id, products.name, SUM(order_items.quantity) as total_sold, SUM(order_items.quantity * order_items.unit_price) as revenue')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_sold')
            ->limit(10)
            ->get();
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
        $request->validate([
            'name'        => 'required|string|max:200',
            'category_id' => 'required|exists:categories,id',
            'price'       => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'status'      => 'required|in:available,unavailable',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'image_url'   => 'nullable|url|max:500',
        ]);

        $imagePath = null;

        // ✅ Ưu tiên upload file
        if ($request->hasFile('image')) {
            // Ảnh upload local sẽ được lưu vào public/images và chỉ lưu tên file trong database.
            $imageName = time() . '.' . $request->image->extension();
            $request->image->move(public_path('images'), $imageName);
            $imagePath = $imageName;
        }
        // ✅ Nếu nhập URL
        elseif ($request->image_url) {
            $imagePath = $request->image_url;
        }

        // Tạo record sản phẩm mới trong menu.
        Product::create([
            'name'        => $request->name,
            'category_id' => $request->category_id,
            'price'       => $request->price,
            'description' => $request->description,
            'status'      => $request->status,
            'image_url'   => $imagePath,
        ]);

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

        $request->validate([
            'name'        => 'required|string|max:200',
            'category_id' => 'required|exists:categories,id',
            'price'       => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'status'      => 'required|in:available,unavailable',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'image_url'   => 'nullable|url|max:500',
        ]);

        $imagePath = $product->image_url;

        // ✅ Nếu upload ảnh mới
        if ($request->hasFile('image')) {
            // Xóa ảnh cũ nếu là ảnh local
            if ($product->image_url && !str_starts_with($product->image_url, ['http://', 'https://'])) {
                $oldPath = public_path('images/' . $product->image_url);
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }

            // Lưu ảnh local mới
            $imageName = time() . '.' . $request->image->extension();
            $request->image->move(public_path('images'), $imageName);
            $imagePath = $imageName;
        }
        // ✅ Nếu nhập URL mới
        elseif ($request->image_url) {
            $imagePath = $request->image_url;
        }

        $product->update([
            'name'        => $request->name,
            'category_id' => $request->category_id,
            'price'       => $request->price,
            'description' => $request->description,
            'status'      => $request->status,
            'image_url'   => $imagePath,
        ]);

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

    // ─── Kho nguyên liệu ────────────────────────────────────────────────────

    public function ingredients()
    {
        $ingredients = Ingredient::query()
            ->orderBy('name')
            ->get();

        return view('admin.ingredients.index', compact('ingredients'));
    }

    public function storeIngredient(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:120|unique:ingredients,name',
            'brand' => 'required|string|max:120',
            'unit' => 'required|string|max:30',
            'stock_quantity' => 'required|numeric|min:0',
            'unit_price' => 'required|numeric|min:0',
            'received_date' => 'required|date',
            'manufacture_date' => 'required|date',
            'expiry_date' => 'required|date|after_or_equal:manufacture_date',
            'lot_number' => 'required|string|max:80|unique:ingredients,lot_number',
            'note' => 'nullable|string|max:255',
        ]);
        $data['minimum_quantity'] = 0;
        $data['total_amount'] = $data['stock_quantity'] * $data['unit_price'];

        Ingredient::create($data);

        return back()->with('success', 'Đã thêm nguyên liệu mới vào kho.');
    }

    public function updateIngredient(Request $request, $id)
    {
        $ingredient = Ingredient::findOrFail($id);

        $data = $request->validate([
            'brand' => 'required|string|max:120',
            'unit' => 'required|string|max:30',
            'stock_quantity' => 'required|numeric|min:0',
            'received_date' => 'required|date',
            'manufacture_date' => 'required|date',
            'expiry_date' => 'required|date|after_or_equal:manufacture_date',
            'lot_number' => 'required|string|max:80|unique:ingredients,lot_number,' . $id,
        ]);
        $data['minimum_quantity'] = 0;

        $ingredient->update($data);

        return back()->with('success', 'Đã cập nhật số lượng kho nguyên liệu.');
    }

    public function destroyIngredient($id)
    {
        $ingredient = Ingredient::findOrFail($id);
        $ingredient->delete();

        return back()->with('success', 'Đã xóa nguyên liệu khỏi kho.');
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
        // Chỉ nạp các role nhân viên và khách hàng để tạo tài khoản.
        $roles = UserRole::whereIn('name', ['staff', 'customer'])->get();
        return view('admin.users.create', compact('roles'));
    }

    public function storeUser(Request $request)
    {
        // Validate dữ liệu tạo user mới.
        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'phone'    => 'nullable|string|max:20|unique:users,phone',
            'role_id'  => 'required|exists:user_roles,id',
            'employment_type' => 'nullable|in:full_time,part_time',
            'citizen_id' => 'nullable|digits:12|unique:users,citizen_id',
        ]);

        $selectedRoleName = UserRole::whereKey($data['role_id'])->value('name');

        if ($selectedRoleName === 'admin') {
            return back()->withInput()->withErrors([
                'role_id' => 'Không thể tạo tài khoản admin từ màn hình này.',
            ]);
        }

        $isStaffRole = $selectedRoleName === 'staff';

        // Chỉ staff mới có loại nhân viên; các role khác luôn để null.
        if ($isStaffRole && empty($data['employment_type'])) {
            return back()->withInput()->withErrors([
                'employment_type' => 'Vui lòng chọn loại nhân viên cho tài khoản staff.',
            ]);
        }

        if ($isStaffRole && empty($data['citizen_id'])) {
            return back()->withInput()->withErrors([
                'citizen_id' => 'Vui lòng nhập căn cước công dân gồm đúng 12 số cho tài khoản nhân viên.',
            ]);
        }

        if (!$isStaffRole) {
            $data['employment_type'] = null;
            $data['citizen_id'] = null;
        }

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
            'phone'   => 'nullable|string|max:20|unique:users,phone,' . $id,
            'role_id' => 'required|exists:user_roles,id',
            'employment_type' => 'nullable|in:full_time,part_time',
            'citizen_id' => 'nullable|digits:12|unique:users,citizen_id,' . $id,
        ]);

        $selectedRoleName = UserRole::whereKey($data['role_id'])->value('name');
        $isStaffRole = $selectedRoleName === 'staff';

        // Chỉ staff mới giữ được loại nhân viên; role khác sẽ reset về null.
        if ($isStaffRole && empty($data['employment_type'])) {
            return back()->withInput()->withErrors([
                'employment_type' => 'Vui lòng chọn loại nhân viên cho tài khoản staff.',
            ]);
        }

        if ($isStaffRole && empty($data['citizen_id'])) {
            return back()->withInput()->withErrors([
                'citizen_id' => 'Vui lòng nhập căn cước công dân gồm đúng 12 số cho tài khoản nhân viên.',
            ]);
        }

        if (!$isStaffRole) {
            $data['employment_type'] = null;
            $data['citizen_id'] = null;
        }

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

    // ─── Đăng ký giờ làm (Admin) ────────────────────────────────────────────

    public function workSchedules(Request $request)
    {
        $weekStart = now()->startOfWeek(Carbon::MONDAY);
        $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);
        $isAutoClosedAtNight = now()->greaterThanOrEqualTo(now()->copy()->setTime(22, 0));
        $weekDays = collect(range(0, 6))->map(fn ($offset) => $weekStart->copy()->addDays($offset));
        $weekBoardLock = WorkScheduleBoardLock::with('locker:id,name')
            ->whereDate('week_start', $weekStart->toDateString())
            ->first();

        // Nạp tất cả đăng ký trong tuần hiện tại để admin xem bảng giống staff theo từng nhóm nhân viên.
        $weeklySchedules = WorkScheduleRegistration::with('staff:id,name,email,employment_type')
            ->whereBetween('work_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->whereIn('employment_type', ['full_time', 'part_time'])
            ->orderBy('work_date')
            ->orderBy('start_time')
            ->get();

        $weeklyAssignments = [
            'full_time' => [],
            'part_time' => [],
        ];

        foreach ($weeklySchedules as $schedule) {
            $employmentType = (string) $schedule->employment_type;
            $slotKey = $this->resolveScheduleSlotKey($employmentType, (string) $schedule->start_time, (string) $schedule->end_time);

            if (!$slotKey) {
                continue;
            }

            $dateKey = Carbon::parse($schedule->work_date)->toDateString();
            $weeklyAssignments[$employmentType][$dateKey][$slotKey] ??= [];
            $weeklyAssignments[$employmentType][$dateKey][$slotKey][] = $schedule;
        }

        $monthInput = $request->input('month', now()->format('Y-m'));

        // Khối duyệt ca làm luôn theo tuần hiện tại (7 ngày), có thể vắt qua tháng kế tiếp.
        $scheduleQuery = WorkScheduleRegistration::with(['staff:id,name,email,employment_type', 'approver:id,name', 'closer:id,name'])
            ->whereBetween('work_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->orderByDesc('work_date')
            ->orderBy('start_time');

        if ($request->filled('employment_type')) {
            $scheduleQuery->where('employment_type', $request->employment_type);
        }

        if ($request->filled('status')) {
            $scheduleQuery->where('status', $request->status);
        }

        $scheduleRows = $scheduleQuery->get();

        // Danh sách tăng ca trong tuần hiện tại để admin duyệt.
        $overtimeQuery = Overtime::with('staff:id,name,email,employment_type')
            ->whereBetween('overtime_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->orderByDesc('overtime_date')
            ->orderByDesc('created_at');

        if ($request->filled('employment_type')) {
            $overtimeQuery->whereHas('staff', function ($query) use ($request) {
                $query->where('employment_type', $request->employment_type);
            });
        }

        $overtimeRows = $overtimeQuery->get();

        $scheduleStats = [
            'pending_count' => $scheduleRows->where('status', 'pending')->count(),
            'approved_count' => $scheduleRows->where('status', 'approved')->count(),
            'closed_count' => $scheduleRows->where('status', 'closed')->count(),
            'pending_overtime_count' => $overtimeRows->where('status', 'pending')->count(),
        ];

        $pendingSchedules = $scheduleRows->where('status', 'pending')->values();
        $approvedSchedules = $scheduleRows->where('status', 'approved')->values();
        $closedSchedules = $scheduleRows->where('status', 'closed')->values();
        $pendingOvertimes = $overtimeRows->where('status', 'pending')->values();
        $processedOvertimes = $overtimeRows->whereIn('status', ['approved', 'rejected'])->take(10)->values();
        $scheduleSlots = self::SCHEDULE_SLOTS;

        return view('admin.work-schedules.index', compact(
            'monthInput',
            'weekStart',
            'weekEnd',
            'weekDays',
            'isAutoClosedAtNight',
            'weekBoardLock',
            'weeklyAssignments',
            'scheduleStats',
            'pendingSchedules',
            'approvedSchedules',
            'closedSchedules',
            'pendingOvertimes',
            'processedOvertimes',
            'scheduleSlots'
        ));
    }

    // Admin mở lại bảng đăng ký tuần hiện tại để staff tiếp tục đăng ký trước 22:00.
    public function openWeeklyWorkScheduleBoard()
    {
        if (now()->greaterThanOrEqualTo(now()->copy()->setTime(22, 0))) {
            return back()->with('error', 'Sau 22:00 bảng đăng ký tự động đóng, không thể mở lại.');
        }

        $weekStart = now()->startOfWeek(Carbon::MONDAY);

        $existingLock = WorkScheduleBoardLock::query()
            ->whereDate('week_start', $weekStart->toDateString())
            ->first();

        if (!$existingLock) {
            return back()->with('success', 'Bảng đăng ký giờ làm hiện đang mở.');
        }

        $existingLock->delete();

        return back()->with('success', 'Đã mở bảng đăng ký giờ làm tuần hiện tại.');
    }

    // ─── Bảng lương (Admin) ─────────────────────────────────────────────────

    public function payroll(Request $request)
    {
        $monthInput = $request->input('month', now()->format('Y-m'));
        $monthStart = Carbon::createFromFormat('Y-m', $monthInput)->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();
        $now = now();
        $today = $now->copy()->startOfDay();

        // Lấy toàn bộ đăng ký trong tháng để tổng hợp bảng lương.
        $scheduleQuery = WorkScheduleRegistration::with('staff:id,name,email,employment_type')
            ->whereBetween('work_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->orderByDesc('work_date')
            ->orderBy('start_time');

        if ($request->filled('employment_type')) {
            $scheduleQuery->where('employment_type', $request->employment_type);
        }

        $scheduleRows = $scheduleQuery->get();

        // Chỉ cộng lương khi ca đã hoàn thành (đã qua giờ kết thúc ca) và không phải ca vắng.
        $payrollSourceRows = $scheduleRows
            ->whereIn('status', ['approved', 'closed'])
            ->filter(function ($row) use ($now, $today) {
                $workDate = Carbon::parse($row->work_date)->startOfDay();
                $endTime = substr((string) $row->end_time, 0, 5);
                $shiftEndAt = Carbon::parse($workDate->toDateString() . ' ' . $endTime);

                if ($workDate->lt($today)) {
                    return true;
                }

                return $workDate->isSameDay($today) && $now->greaterThanOrEqualTo($shiftEndAt);
            })
            ->reject(function ($row) {
                $note = strtoupper((string) ($row->note ?? ''));
                return str_contains($note, '[ABSENT]');
            });

        // Tổng hợp giờ tăng ca đã duyệt và ngày tăng ca đã kết thúc.
        $overtimeQuery = Overtime::query()
            ->select(['staff_id', 'overtime_date', 'hours'])
            ->whereBetween('overtime_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->where('status', 'approved')
            ->whereDate('overtime_date', '<', $today->toDateString())
            ->orderBy('overtime_date');

        if ($request->filled('employment_type')) {
            $overtimeQuery->whereHas('staff', function ($query) use ($request) {
                $query->where('employment_type', $request->employment_type);
            });
        }

        $overtimeByStaff = $overtimeQuery
            ->get()
            ->groupBy('staff_id')
            ->map(function ($rows) {
                $normalHours = 0.0;
                $holidayHours = 0.0;

                foreach ($rows as $row) {
                    $hours = (float) $row->hours;
                    $isHoliday = $this->isHolidayDate(Carbon::parse($row->overtime_date));

                    if ($isHoliday) {
                        $holidayHours += $hours;
                    } else {
                        $normalHours += $hours;
                    }
                }

                return [
                    'normal_hours' => round($normalHours, 2),
                    'holiday_hours' => round($holidayHours, 2),
                ];
            });

        $payrollRows = $payrollSourceRows
            ->groupBy('staff_id')
            ->map(function ($rows) use ($overtimeByStaff) {
                $staff = $rows->first()->staff;
                $normalMinutes = 0;
                $holidayMinutes = 0;

                foreach ($rows as $row) {
                    $minutes = $this->calculateWorkMinutes((string) $row->start_time, (string) $row->end_time);
                    $isHoliday = $this->isHolidayDate(Carbon::parse($row->work_date));

                    if ($isHoliday) {
                        $holidayMinutes += $minutes;
                    } else {
                        $normalMinutes += $minutes;
                    }
                }

                $employmentType = $rows->first()->employment_type;
                $hourlyRate = self::PAYROLL_RATES[$employmentType] ?? 0;
                $overtimeRate = self::OVERTIME_RATES[$employmentType] ?? 0;
                $staffOvertime = $overtimeByStaff[(int) $staff->id] ?? ['normal_hours' => 0, 'holiday_hours' => 0];
                $normalOvertimeHours = (float) ($staffOvertime['normal_hours'] ?? 0);
                $holidayOvertimeHours = (float) ($staffOvertime['holiday_hours'] ?? 0);
                $overtimeHours = round($normalOvertimeHours + $holidayOvertimeHours, 2);

                $baseSalary = round(($normalMinutes / 60) * $hourlyRate)
                    + round(($holidayMinutes / 60) * $hourlyRate * self::HOLIDAY_HOURLY_MULTIPLIER);

                $overtimeSalary = round($normalOvertimeHours * $overtimeRate)
                    + round($holidayOvertimeHours * $overtimeRate * self::HOLIDAY_HOURLY_MULTIPLIER);

                return [
                    'staff' => $staff,
                    'employment_type' => $employmentType,
                    'shift_count' => $rows->count(),
                    'total_hours' => round(($normalMinutes + $holidayMinutes) / 60, 2),
                    'hourly_rate' => $hourlyRate,
                    'overtime_hours' => $overtimeHours,
                    'overtime_rate' => $overtimeRate,
                    'overtime_salary' => $overtimeSalary,
                    'gross_salary' => $baseSalary + $overtimeSalary,
                ];
            })
            ->sortBy('staff.name')
            ->values();

        $payrollStats = [
            'completed_shift_count' => $payrollSourceRows->count(),
            'gross_salary_total' => $payrollRows->sum('gross_salary'),
        ];

        return view('admin.payroll.index', compact(
            'monthInput',
            'payrollRows',
            'payrollStats'
        ));
    }

    // Admin bấm nút để duyệt và đóng toàn bộ bảng đăng ký giờ làm trong tuần hiện tại.
    public function closeWeeklyWorkScheduleBoard()
    {
        $weekStart = now()->startOfWeek(Carbon::MONDAY);
        $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);

        $existingLock = WorkScheduleBoardLock::query()
            ->whereDate('week_start', $weekStart->toDateString())
            ->first();

        if ($existingLock) {
            return back()->with('success', 'Bảng đăng ký giờ làm tuần này đã được đóng trước đó.');
        }

        DB::transaction(function () use ($weekStart, $weekEnd) {
            // Duyệt các bản ghi pending trước để không bỏ sót công đăng ký hợp lệ.
            WorkScheduleRegistration::query()
                ->whereBetween('work_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
                ->where('status', 'pending')
                ->update([
                    'status' => 'approved',
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                ]);

            // Đóng toàn bộ ca đã duyệt trong tuần để chốt bảng lương.
            WorkScheduleRegistration::query()
                ->whereBetween('work_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
                ->where('status', 'approved')
                ->update([
                    'status' => 'closed',
                    'closed_by' => auth()->id(),
                    'closed_at' => now(),
                ]);

            WorkScheduleBoardLock::create([
                'week_start' => $weekStart->toDateString(),
                'week_end' => $weekEnd->toDateString(),
                'locked_by' => auth()->id(),
                'locked_at' => now(),
            ]);
        });

        return back()->with('success', 'Đã duyệt và đóng bảng đăng ký giờ làm tuần hiện tại. Staff không thể đăng ký thêm cho tuần này.');
    }

    // Admin duyệt một đăng ký giờ làm để đưa vào bảng lương tạm tính.
    public function approveWorkSchedule(Request $request, $id)
    {
        $registration = WorkScheduleRegistration::findOrFail($id);
        $weekStart = now()->startOfWeek(Carbon::MONDAY);
        $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);
        $workDate = Carbon::parse($registration->work_date);

        if ($workDate->lt($weekStart->copy()->startOfDay()) || $workDate->gt($weekEnd->copy()->endOfDay())) {
            $message = 'Chỉ được duyệt các ca nằm trong tuần hiện tại (7 ngày).';

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 422);
            }

            return back()->with('error', $message);
        }

        if ($registration->status === 'closed') {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bản đăng ký này đã được đóng bảng lương nên không thể duyệt lại.',
                ], 422);
            }

            return back()->with('error', 'Bản đăng ký này đã được đóng bảng lương nên không thể duyệt lại.');
        }

        $registration->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã duyệt đăng ký giờ làm.',
                'registration_id' => $registration->id,
            ]);
        }

        return back()->with('success', 'Đã duyệt đăng ký giờ làm.');
    }

    // Admin duyệt đơn tăng ca của staff.
    public function approveOvertime($id)
    {
        $overtime = Overtime::with('staff:id,employment_type')->findOrFail($id);

        if ($overtime->status === 'approved') {
            return back()->with('success', 'Đơn tăng ca này đã được duyệt trước đó.');
        }

        $overtime->update([
            'status' => 'approved',
        ]);

        return back()->with('success', 'Đã duyệt đơn tăng ca.');
    }

    // Admin từ chối đơn tăng ca của staff.
    public function rejectOvertime($id)
    {
        $overtime = Overtime::findOrFail($id);

        if ($overtime->status === 'rejected') {
            return back()->with('success', 'Đơn tăng ca này đã được từ chối trước đó.');
        }

        $overtime->update([
            'status' => 'rejected',
        ]);

        return back()->with('success', 'Đã từ chối đơn tăng ca.');
    }

    // Admin đóng một đăng ký giờ làm để chốt lương và khóa thao tác chỉnh sửa tiếp theo.
    public function closeWorkSchedule($id)
    {
        $registration = WorkScheduleRegistration::findOrFail($id);

        if ($registration->status === 'pending') {
            return back()->with('error', 'Vui lòng duyệt đăng ký giờ làm trước khi đóng.');
        }

        if ($registration->status === 'closed') {
            return back()->with('success', 'Đăng ký giờ làm này đã được đóng trước đó.');
        }

        $registration->update([
            'status' => 'closed',
            'closed_by' => auth()->id(),
            'closed_at' => now(),
        ]);

        return back()->with('success', 'Đã đóng bảng đăng ký giờ làm.');
    }

    // Admin đánh dấu vắng ca để loại khỏi danh sách đã duyệt.
    public function markWorkScheduleAbsent($id)
    {
        $registration = WorkScheduleRegistration::findOrFail($id);

        if ($registration->status === 'pending') {
            return back()->with('error', 'Ca chưa duyệt, không thể đánh dấu vắng.');
        }

        if ($registration->status === 'closed') {
            return back()->with('success', 'Ca này đã ở trạng thái đóng.');
        }

        $currentNote = trim((string) $registration->note);
        $absentNote = '[ABSENT] Vắng ca do admin đánh dấu lúc ' . now()->format('d/m/Y H:i');

        $registration->update([
            'status' => 'closed',
            'closed_by' => auth()->id(),
            'closed_at' => now(),
            'note' => $currentNote === '' ? $absentNote : ($currentNote . ' | ' . $absentNote),
        ]);

        return back()->with('success', 'Đã đánh dấu vắng ca.');
    }

    // Admin đánh dấu ca phát sinh để ghi nhận nội bộ.
    public function markWorkScheduleExtra($id)
    {
        $registration = WorkScheduleRegistration::findOrFail($id);

        if ($registration->status === 'pending') {
            return back()->with('error', 'Ca chưa duyệt, không thể đánh dấu ca phát sinh.');
        }

        if ($registration->status === 'closed') {
            return back()->with('error', 'Ca đã đóng, không thể đánh dấu ca phát sinh.');
        }

        $currentNote = trim((string) $registration->note);

        if (str_contains($currentNote, '[EXTRA_SHIFT]')) {
            return back()->with('success', 'Ca này đã được đánh dấu phát sinh trước đó.');
        }

        $extraNote = '[EXTRA_SHIFT] Ca phát sinh do admin đánh dấu lúc ' . now()->format('d/m/Y H:i');

        $registration->update([
            'note' => $currentNote === '' ? $extraNote : ($currentNote . ' | ' . $extraNote),
        ]);

        return back()->with('success', 'Đã đánh dấu ca phát sinh.');
    }

    // Admin điều chỉnh ngày làm và khung giờ của một ca đã duyệt/chờ duyệt.
    public function adjustWorkSchedule(Request $request, $id)
    {
        $registration = WorkScheduleRegistration::findOrFail($id);

        if ($registration->status === 'closed') {
            return back()->with('error', 'Ca đã đóng không thể điều chỉnh.');
        }

        $employmentType = (string) $registration->employment_type;
        $allowedSlots = self::SCHEDULE_SLOTS[$employmentType] ?? [];

        if (empty($allowedSlots)) {
            return back()->with('error', 'Không tìm thấy khung giờ hợp lệ cho loại nhân viên này.');
        }

        $data = $request->validate([
            'work_date' => 'required|date',
            'slot_key' => ['required', Rule::in(array_keys($allowedSlots))],
        ]);

        $workDate = Carbon::parse($data['work_date'])->toDateString();
        $selectedSlot = $allowedSlots[$data['slot_key']];

        // Mỗi nhân viên chỉ được có đúng 1 ca trong 1 ngày (trừ chính bản ghi đang chỉnh).
        $myOtherShiftInDate = WorkScheduleRegistration::query()
            ->where('staff_id', $registration->staff_id)
            ->whereDate('work_date', $workDate)
            ->where('id', '!=', $registration->id)
            ->exists();

        if ($myOtherShiftInDate) {
            return back()->with('error', 'Nhân viên này đã có ca khác trong ngày đã chọn.');
        }

        $slotCapacity = $this->getScheduleSlotCapacity($employmentType);

        // Kiểm tra sức chứa slot theo loại nhân viên (không tính chính bản ghi đang chỉnh).
        $slotOccupancy = WorkScheduleRegistration::query()
            ->where('employment_type', $employmentType)
            ->whereDate('work_date', $workDate)
            ->where('start_time', $selectedSlot['start'])
            ->where('end_time', $selectedSlot['end'])
            ->where('id', '!=', $registration->id)
            ->count();

        if ($slotOccupancy >= $slotCapacity) {
            return back()->with('error', 'Khung giờ này đã đủ số lượng nhân viên. Vui lòng chọn khung giờ khác.');
        }

        $registration->update([
            'work_date' => $workDate,
            'start_time' => $selectedSlot['start'],
            'end_time' => $selectedSlot['end'],
            'shift_label' => $selectedSlot['label'],
        ]);

        return back()->with('success', 'Đã điều chỉnh ca làm thành công.');
    }

    // ─── Thống kê & Báo cáo ──────────────────────────────────────────────────

    public function reports(Request $request)
    {
        // period điều khiển cách gom dữ liệu doanh thu theo ngày/tháng/năm.
        $period = $request->input('period', 'month'); // day | month | year

        // Chọn nguồn dữ liệu biểu đồ phù hợp với period đang xem.
        $revenueData = match ($period) {
            'day' => $this->revenueByDay($request),
            'year'  => $this->revenueByYear(),
            default => $this->revenueByMonth(),
        };

        // Danh sách top sản phẩm bán chạy cho báo cáo tổng hợp.
        $topProducts = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('payments', 'payments.order_id', '=', 'orders.id')
            ->where('payments.status', 'paid')
            ->selectRaw('products.name, SUM(order_items.quantity) as total_sold, SUM(order_items.quantity * order_items.unit_price) as revenue')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_sold')
            ->limit(10)
            ->get();

        return view('admin.reports', compact('revenueData', 'topProducts', 'period'));
    }

        private function revenueByDay(Request $request = null)
    {
        $from = $request?->input('from') ? now()->parse($request->input('from')) : now()->subDays(29)->startOfDay();
        $to   = $request?->input('to')   ? now()->parse($request->input('to'))->endOfDay() : now()->endOfDay();

            return $this->paidRevenueOrders()
            ->selectRaw('DATE(created_at) as label, SUM(final_price) as total')
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('label')
            ->orderBy('label')
            ->get();
    }

    private function revenueByMonth()
    {
        // Gom doanh thu theo tháng cho 12 tháng gần nhất.
        return $this->paidRevenueOrders()
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as label, SUM(final_price) as total")
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->groupBy('label')
            ->orderBy('label')
            ->get();
    }

    private function revenueByYear()
    {
        // Gom doanh thu theo năm để nhìn xu hướng dài hạn.
        return $this->paidRevenueOrders()
            ->selectRaw("YEAR(created_at) as label, SUM(final_price) as total")
            ->groupBy('label')
            ->orderBy('label')
            ->get();
    }

    // Tính tổng phút làm việc của một ca, có bù 1 phút cho slot 24h được lưu thành 23:59.
    private function calculateWorkMinutes(string $startTime, string $endTime): int
    {
        $start = Carbon::createFromFormat('H:i:s', strlen($startTime) === 5 ? $startTime . ':00' : $startTime);
        $end = Carbon::createFromFormat('H:i:s', strlen($endTime) === 5 ? $endTime . ':00' : $endTime);
        $minutes = $start->diffInMinutes($end);

        return substr($endTime, 0, 5) === '23:59' ? $minutes + 1 : $minutes;
    }

    private function isHolidayDate(Carbon $date): bool
    {
        $dateKey = $date->toDateString();
        $monthDayKey = $date->format('m-d');

        return in_array($monthDayKey, self::FIXED_HOLIDAYS, true)
            || in_array($dateKey, self::SPECIAL_HOLIDAYS, true);
    }

    // Resolve slot key theo nhóm nhân viên để map dữ liệu vào bảng tuần.
    private function resolveScheduleSlotKey(string $employmentType, string $startTime, string $endTime): ?string
    {
        foreach (self::SCHEDULE_SLOTS[$employmentType] ?? [] as $slotKey => $slot) {
            if ($slot['start'] === substr($startTime, 0, 5) && $slot['end'] === substr($endTime, 0, 5)) {
                return $slotKey;
            }
        }

        return null;
    }

    // Sức chứa slot theo loại nhân viên: full-time 1 người, part-time 2 người.
    private function getScheduleSlotCapacity(string $employmentType): int
    {
        return $employmentType === 'part_time' ? 2 : 1;
    }
   
}
