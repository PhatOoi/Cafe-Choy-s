<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\OrderHistoryController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\AiChatController;
use Illuminate\Http\Request;

// ── PUBLIC ────────────────────────────────────────────────────
// Các route mở cho khách chưa đăng nhập hoặc mọi người dùng truy cập.
Route::get('/', function () {
    // Nếu là nhân viên đã đăng nhập thì bỏ qua trang chủ khách và vào dashboard staff.
    if (auth()->check() && auth()->user()->isStaff()) {
        return redirect()->route('staff.dashboard');
    }

    return view('home');
});
Route::get('/menu', [MenuController::class, 'index']);

// FIX: /search trùng lặp — giữ SearchController, xóa MenuController::search
Route::get('/search', [SearchController::class, 'index'])->name('search');

Route::get('/login',[LoginController::class,'index']);
Route::post('/login', [LoginController::class, 'login'])->name('login');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Nhóm route giỏ hàng và checkout của khách hàng.
Route::post('/cart/add', [CartController::class, 'add'])->middleware('auth');
Route::get('/cart', [CartController::class, 'index'])->middleware('auth');
Route::get('/cart/remove/{id}', [CartController::class, 'remove'])->middleware('auth');
Route::post('/cart/update/{key}', [CartController::class, 'update'])->middleware('auth');
Route::post('/cart/checkout/cash', [CartController::class, 'confirmCashPayment'])->middleware('auth');
Route::post('/cart/checkout/qr', [CartController::class, 'confirmQrPayment'])->middleware('auth');
Route::get('/cart/qr-status', [CartController::class, 'qrPaymentStatus'])->middleware('auth')->name('cart.qr-status');
Route::get('/orders/history', [OrderHistoryController::class, 'index'])->middleware('auth')->name('orders.history');
Route::post('/orders/{id}/cancel', [OrderHistoryController::class, 'cancel'])->middleware('auth')->name('orders.cancel');
Route::get('/support', fn() => view('support'))->name('support');

// Widget AI — public, không cần đăng nhập (dùng cho floating chatbot trên mọi trang).
Route::post('/widget/ai-send',  [AiChatController::class, 'widgetSend'])->name('widget.ai-send');
Route::post('/widget/ai-clear', [AiChatController::class, 'widgetClear'])->name('widget.ai-clear');
Route::post('/widget/ai-order/confirm', [AiChatController::class, 'widgetConfirmOrder'])->name('widget.ai-order.confirm');

// Route debug test DB — chỉ bật ở local, không dùng trên production.
Route::get('/test-db', function () {
    $users = DB::table('users')->get();
    return $users;
});

// Hồ sơ cá nhân của người dùng đã đăng nhập.
Route::get('/profile', [ProfileController::class, 'index'])->middleware('auth');

// Auth truyền thống cho form login/register.
Route::get('/login',  [LoginController::class, 'index'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout',[LoginController::class, 'logout'])->name('logout');
Route::get('/register', fn() => view('login'))->name('register.form');
Route::post('/register',[RegisterController::class, 'register'])->name('register');

// Luồng quên mật khẩu theo các bước: email -> mã xác minh -> đặt lại mật khẩu.
Route::prefix('forgot-password')->name('forgot-password.')->group(function () {
    Route::get('/',        [ForgotPasswordController::class, 'showEmailForm'])->name('email-form');
    Route::post('/send',   [ForgotPasswordController::class, 'sendCode'])->name('send-code');
    Route::get('/verify',  [ForgotPasswordController::class, 'showVerifyForm'])->name('verify-form');
    Route::post('/verify', [ForgotPasswordController::class, 'verifyCode'])->name('verify-code');
    Route::get('/reset',   [ForgotPasswordController::class, 'showResetForm'])->name('reset-form');
    Route::post('/reset',  [ForgotPasswordController::class, 'resetPassword'])->name('reset-password');
});

// ── CUSTOMER (auth) ───────────────────────────────────────────
// Các route khách hàng chỉ dùng khi đã đăng nhập.
Route::middleware(['auth'])->group(function () {
    Route::get('/cart',               [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add',          [CartController::class, 'add'])->name('cart.add');
    Route::post('/cart/update/{key}', [CartController::class, 'update'])->name('cart.update');
    Route::post('/cart/clear-for-support', [CartController::class, 'clearForSupport'])->name('cart.clear-for-support');
    Route::get('/cart/remove/{id}',   [CartController::class, 'remove'])->name('cart.remove');

    // FIX: route PUT profile bị thiếu hoàn toàn
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar');

    // FIX: thêm route payment
    Route::get('/payment', fn() => view('payment'))->name('payment');

    // Chat hỗ trợ khách hàng — 2 route cho cả polling lấy tin và gửi tin.
    Route::get('/chat/messages',  [ChatController::class, 'messages'])->name('chat.messages');
    Route::post('/chat/send',     [ChatController::class, 'send'])->name('chat.send');

    // AI Chat — Gemini-powered chatbot giới hạn phạm vi quán.
    Route::get('/ai-chat',        [AiChatController::class, 'index'])->name('ai-chat.index');
    Route::post('/ai-chat/send',  [AiChatController::class, 'send'])->name('ai-chat.send');
    Route::post('/ai-chat/clear', [AiChatController::class, 'clear'])->name('ai-chat.clear');
});

// ── STAFF ─────────────────────────────────────────────────────
// Khu vực vận hành cho staff/admin: xử lý đơn, tạo đơn tại quán, doanh thu, nhắc việc.
Route::prefix('staff')->name('staff.')->middleware(['auth','staff'])->group(function () {
    Route::get('/',        [StaffController::class, 'dashboard'])->name('dashboard');
    Route::get('/work-schedules', [StaffController::class, 'workSchedules'])->name('work-schedules.index');
    Route::post('/work-schedules', [StaffController::class, 'storeWorkSchedule'])->name('work-schedules.store');
    Route::post('/overtimes', [StaffController::class, 'storeOvertime'])->name('overtimes.store');
    Route::get('/orders',  [StaffController::class, 'orders'])->name('orders');
    // Chat hỗ trợ khách hàng
    Route::get('/support',                      fn() => view('staff.support'))->name('support');
    Route::get('/chat/conversations',           [ChatController::class, 'conversations'])->name('chat.conversations');
    Route::get('/chat/conversation/{userId}',   [ChatController::class, 'conversation'])->name('chat.conversation');
    Route::post('/chat/reply/{userId}',         [ChatController::class, 'reply'])->name('chat.reply');
    Route::get('/chat/unread',                  [ChatController::class, 'unreadCount'])->name('chat.unread');
    Route::get('/orders/confirmed-reminder-ids', [StaffController::class, 'confirmedOrderReminderIds'])->name('orders.confirmed-reminder-ids');
    Route::get('/orders/reminder-statuses', [StaffController::class, 'orderReminderStatuses'])->name('orders.reminder-statuses');
    Route::get('/orders/created-history', [StaffController::class, 'createdOrderHistory'])->name('orders.created-history');
    Route::get('/revenue/daily', [StaffController::class, 'dailyRevenueReport'])->name('revenue.daily');
    Route::get('/revenue/monthly', [StaffController::class, 'monthlyRevenueReport'])->name('revenue.monthly');
    Route::get('/orders/create',        [StaffController::class, 'createOrder'])->name('create-order');
    Route::post('/orders',              [StaffController::class, 'storeOrder'])->name('store-order');
    Route::get('/orders/{id}',          [StaffController::class, 'orderDetail'])->name('order.detail');
    Route::get('/orders/{id}/edit',     [StaffController::class, 'editOrder'])->name('order.edit');
    Route::put('/orders/{id}',          [StaffController::class, 'updateOrder'])->name('order.update');
    Route::delete('/orders/{id}',       [StaffController::class, 'deleteOrder'])->name('order.delete');
    Route::post('/orders/{id}/status',  [StaffController::class, 'updateStatus'])->name('order.status');
    Route::post('/orders/{id}/confirm-payment', [StaffController::class, 'confirmPayment'])->name('order.payment.confirm');
    Route::get('/orders/{id}/invoice',  [StaffController::class, 'invoice'])->name('order.invoice');
    Route::post('/orders/{id}/assign',  [StaffController::class, 'assignDelivery'])->name('order.assign');
    Route::post('/shift/start', [StaffController::class, 'startShift'])->name('shift.start');
    Route::post('/shift/end',   [StaffController::class, 'endShift'])->name('shift.end');
});

// ── ADMIN ──────────────────────────────────────────────────────
// Khu vực quản trị tổng thể: dashboard, sản phẩm, danh mục, user và báo cáo.
Route::prefix('admin')->name('admin.')->middleware(['auth','admin'])->group(function () {
    Route::get('/',          fn() => redirect()->route('admin.dashboard'));
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/reports',   [AdminController::class, 'reports'])->name('reports');
    Route::get('/work-schedules', [AdminController::class, 'workSchedules'])->name('work-schedules.index');
    Route::get('/payroll',   [AdminController::class, 'payroll'])->name('payroll');
    Route::post('/work-schedules/open-week-board', [AdminController::class, 'openWeeklyWorkScheduleBoard'])->name('work-schedules.open-week-board');
    Route::post('/work-schedules/close-week-board', [AdminController::class, 'closeWeeklyWorkScheduleBoard'])->name('work-schedules.close-week-board');
    Route::patch('/work-schedules/{id}/approve', [AdminController::class, 'approveWorkSchedule'])->name('work-schedules.approve');
    Route::patch('/work-schedules/{id}/adjust', [AdminController::class, 'adjustWorkSchedule'])->name('work-schedules.adjust');
    Route::patch('/work-schedules/{id}/extra', [AdminController::class, 'markWorkScheduleExtra'])->name('work-schedules.extra');
    Route::patch('/work-schedules/{id}/absent', [AdminController::class, 'markWorkScheduleAbsent'])->name('work-schedules.absent');
    Route::patch('/work-schedules/{id}/close', [AdminController::class, 'closeWorkSchedule'])->name('work-schedules.close');
    Route::patch('/overtimes/{id}/approve', [AdminController::class, 'approveOvertime'])->name('overtimes.approve');
    Route::patch('/overtimes/{id}/reject', [AdminController::class, 'rejectOvertime'])->name('overtimes.reject');

    // Sản phẩm
    Route::get('/products',           [AdminController::class, 'products'])->name('products');
    Route::get('/products/create',    [AdminController::class, 'createProduct'])->name('products.create');
    Route::post('/products',          [AdminController::class, 'storeProduct'])->name('products.store');
    Route::get('/products/{id}/edit', [AdminController::class, 'editProduct'])->name('products.edit');
    Route::put('/products/{id}',      [AdminController::class, 'updateProduct'])->name('products.update');
    Route::delete('/products/{id}',   [AdminController::class, 'destroyProduct'])->name('products.destroy');

    // Danh mục
    Route::get('/categories',         [AdminController::class, 'categories'])->name('categories');
    Route::post('/categories',        [AdminController::class, 'storeCategory'])->name('categories.store');
    Route::put('/categories/{id}',    [AdminController::class, 'updateCategory'])->name('categories.update');
    Route::delete('/categories/{id}', [AdminController::class, 'destroyCategory'])->name('categories.destroy');

    // Kho nguyên liệu
    Route::get('/ingredients', [AdminController::class, 'ingredients'])->name('ingredients');
    Route::post('/ingredients', [AdminController::class, 'storeIngredient'])->name('ingredients.store');
    Route::put('/ingredients/{id}', [AdminController::class, 'updateIngredient'])->name('ingredients.update');
    Route::delete('/ingredients/{id}', [AdminController::class, 'destroyIngredient'])->name('ingredients.destroy');

    // Người dùng
    Route::get('/users',               [AdminController::class, 'users'])->name('users');
    Route::get('/users/create',        [AdminController::class, 'createUser'])->name('users.create');
    Route::post('/users',              [AdminController::class, 'storeUser'])->name('users.store');
    Route::get('/users/{id}/edit',     [AdminController::class, 'editUser'])->name('users.edit');
    Route::put('/users/{id}',          [AdminController::class, 'updateUser'])->name('users.update');
    Route::patch('/users/{id}/toggle', [AdminController::class, 'toggleUserActive'])->name('users.toggle');
    Route::delete('/users/{id}',       [AdminController::class, 'destroyUser'])->name('users.destroy');

    // Đơn hàng
    Route::get('/orders',      [AdminController::class, 'orders'])->name('orders');
    Route::get('/orders/{id}', [AdminController::class, 'orderDetail'])->name('orders.detail');
});

Route::post('/subscribe', [App\Http\Controllers\NewsletterController::class, 'subscribe']);

// Route debug chỉ bật ở local để tránh lộ dữ liệu môi trường production.
if (app()->environment('local')) {
    Route::get('/test-db', fn() => DB::table('users')->get());
}

Route::get('/revenue',       [AdminController::class, 'reports'])->name('revenue');
Route::get('/revenue/day',   fn(Request $r) => app(AdminController::class)->reports($r->merge(['period'=>'day'])))->name('revenue.day');
Route::get('/revenue/month', fn(Request $r) => app(AdminController::class)->reports($r->merge(['period'=>'month'])))->name('revenue.month');
Route::get('/revenue/year',  fn(Request $r) => app(AdminController::class)->reports($r->merge(['period'=>'year'])))->name('revenue.year');

