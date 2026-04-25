<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Category;
use App\Models\UserRole;
use App\Models\WorkScheduleBoardLock;
use App\Models\WorkScheduleRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    // Đơn giá theo giờ dùng cho bảng lương tạm tính nội bộ.
    private const PAYROLL_RATES = [
        'full_time' => 32000,
        'part_time' => 28000,
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
            'phone'    => 'nullable|string|max:20|unique:users,phone',
            'role_id'  => 'required|exists:user_roles,id',
            'employment_type' => 'nullable|in:full_time,part_time',
        ]);

        // Chỉ staff mới có loại nhân viên; các role khác luôn để null.
        if ((int) $data['role_id'] === 2 && empty($data['employment_type'])) {
            return back()->withInput()->withErrors([
                'employment_type' => 'Vui lòng chọn loại nhân viên cho tài khoản staff.',
            ]);
        }

        if ((int) $data['role_id'] !== 2) {
            $data['employment_type'] = null;
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
        ]);

        // Chỉ staff mới giữ được loại nhân viên; role khác sẽ reset về null.
        if ((int) $data['role_id'] === 2 && empty($data['employment_type'])) {
            return back()->withInput()->withErrors([
                'employment_type' => 'Vui lòng chọn loại nhân viên cho tài khoản staff.',
            ]);
        }

        if ((int) $data['role_id'] !== 2) {
            $data['employment_type'] = null;
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

    // ─── Bảng lương & duyệt đăng ký giờ làm ─────────────────────────────────

    public function payroll(Request $request)
    {
        $weekStart = now()->startOfWeek(Carbon::MONDAY);
        $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);
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
        $monthStart = Carbon::createFromFormat('Y-m', $monthInput)->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();

        // Lấy toàn bộ đăng ký trong tháng để tổng hợp bảng lương và khối thao tác admin.
        $scheduleQuery = WorkScheduleRegistration::with(['staff:id,name,email,employment_type', 'approver:id,name', 'closer:id,name'])
            ->whereBetween('work_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->orderByDesc('work_date')
            ->orderBy('start_time');

        if ($request->filled('employment_type')) {
            $scheduleQuery->where('employment_type', $request->employment_type);
        }

        if ($request->filled('status')) {
            $scheduleQuery->where('status', $request->status);
        }

        $scheduleRows = $scheduleQuery->get();

        $payrollRows = $scheduleRows
            ->whereIn('status', ['approved', 'closed'])
            ->groupBy('staff_id')
            ->map(function ($rows) {
                $staff = $rows->first()->staff;
                $totalMinutes = $rows->sum(fn ($row) => $this->calculateWorkMinutes((string) $row->start_time, (string) $row->end_time));
                $hourlyRate = self::PAYROLL_RATES[$rows->first()->employment_type] ?? 0;
                $approvedRows = $rows->where('status', 'approved')->count();
                $closedRows = $rows->where('status', 'closed')->count();

                return [
                    'staff' => $staff,
                    'employment_type' => $rows->first()->employment_type,
                    'shift_count' => $rows->count(),
                    'approved_count' => $approvedRows,
                    'closed_count' => $closedRows,
                    'total_hours' => round($totalMinutes / 60, 2),
                    'hourly_rate' => $hourlyRate,
                    'gross_salary' => round(($totalMinutes / 60) * $hourlyRate),
                ];
            })
            ->sortBy('staff.name')
            ->values();

        $payrollStats = [
            'pending_count' => $scheduleRows->where('status', 'pending')->count(),
            'approved_count' => $scheduleRows->where('status', 'approved')->count(),
            'closed_count' => $scheduleRows->where('status', 'closed')->count(),
            'gross_salary_total' => $payrollRows->sum('gross_salary'),
        ];

        $pendingSchedules = $scheduleRows->where('status', 'pending')->values();
        $approvedSchedules = $scheduleRows->where('status', 'approved')->values();
        $closedSchedules = $scheduleRows->where('status', 'closed')->values();

        return view('admin.payroll.index', compact(
            'monthInput',
            'weekStart',
            'weekEnd',
            'weekDays',
            'weekBoardLock',
            'weeklyAssignments',
            'payrollRows',
            'payrollStats',
            'pendingSchedules',
            'approvedSchedules',
            'closedSchedules'
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
    public function approveWorkSchedule($id)
    {
        $registration = WorkScheduleRegistration::findOrFail($id);

        if ($registration->status === 'closed') {
            return back()->with('error', 'Bản đăng ký này đã được đóng bảng lương nên không thể duyệt lại.');
        }

        $registration->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Đã duyệt đăng ký giờ làm.');
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
            ->where('orders.status', 'delivered')
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

        return Order::where('status', 'delivered')
            ->selectRaw('DATE(created_at) as label, SUM(final_price) as total')
            ->whereBetween('created_at', [$from, $to])
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

    // Tính tổng phút làm việc của một ca, có bù 1 phút cho slot 24h được lưu thành 23:59.
    private function calculateWorkMinutes(string $startTime, string $endTime): int
    {
        $start = Carbon::createFromFormat('H:i:s', strlen($startTime) === 5 ? $startTime . ':00' : $startTime);
        $end = Carbon::createFromFormat('H:i:s', strlen($endTime) === 5 ? $endTime . ':00' : $endTime);
        $minutes = $start->diffInMinutes($end);

        return substr($endTime, 0, 5) === '23:59' ? $minutes + 1 : $minutes;
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
   
}
