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

// ── PUBLIC ────────────────────────────────────────────────────
Route::get('/', function () {
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

// Trang giỏ hàng
Route::post('/cart/add', [CartController::class, 'add'])->middleware('auth');
Route::get('/cart', [CartController::class, 'index'])->middleware('auth');
Route::get('/cart/remove/{id}', [CartController::class, 'remove'])->middleware('auth');
Route::post('/cart/update/{key}', [CartController::class, 'update'])->middleware('auth');
Route::post('/cart/checkout/cash', [CartController::class, 'confirmCashPayment'])->middleware('auth');
Route::post('/cart/checkout/qr', [CartController::class, 'confirmQrPayment'])->middleware('auth');
Route::get('/cart/qr-status', [CartController::class, 'qrPaymentStatus'])->middleware('auth')->name('cart.qr-status');
Route::get('/orders/history', [OrderHistoryController::class, 'index'])->middleware('auth')->name('orders.history');

Route::get('/test-db', function () {
    $users = DB::table('users')->get();
    return $users;
});
Route::get('/profile', [ProfileController::class, 'index'])->middleware('auth');

// Auth
Route::get('/login',  [LoginController::class, 'index'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout',[LoginController::class, 'logout'])->name('logout');
Route::get('/register', fn() => view('login'))->name('register.form');
Route::post('/register',[RegisterController::class, 'register'])->name('register');

// Quên mật khẩu
Route::prefix('forgot-password')->name('forgot-password.')->group(function () {
    Route::get('/',        [ForgotPasswordController::class, 'showEmailForm'])->name('email-form');
    Route::post('/send',   [ForgotPasswordController::class, 'sendCode'])->name('send-code');
    Route::get('/verify',  [ForgotPasswordController::class, 'showVerifyForm'])->name('verify-form');
    Route::post('/verify', [ForgotPasswordController::class, 'verifyCode'])->name('verify-code');
    Route::get('/reset',   [ForgotPasswordController::class, 'showResetForm'])->name('reset-form');
    Route::post('/reset',  [ForgotPasswordController::class, 'resetPassword'])->name('reset-password');
});

// ── CUSTOMER (auth) ───────────────────────────────────────────
Route::middleware(['auth'])->group(function () {
    Route::get('/cart',               [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add',          [CartController::class, 'add'])->name('cart.add');
    Route::post('/cart/update/{key}', [CartController::class, 'update'])->name('cart.update');
    Route::get('/cart/remove/{id}',   [CartController::class, 'remove'])->name('cart.remove');

    // FIX: route PUT profile bị thiếu hoàn toàn
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // FIX: thêm route payment
    Route::get('/payment', fn() => view('payment'))->name('payment');
});

// ── STAFF ─────────────────────────────────────────────────────
Route::prefix('staff')->name('staff.')->middleware(['auth','staff'])->group(function () {
    Route::get('/',        [StaffController::class, 'dashboard'])->name('dashboard');
    Route::get('/orders',  [StaffController::class, 'orders'])->name('orders');
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
Route::prefix('admin')->name('admin.')->middleware(['auth','admin'])->group(function () {
    Route::get('/',          fn() => redirect()->route('admin.dashboard'));
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/reports',   [AdminController::class, 'reports'])->name('reports');

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

// Debug (local only)
if (app()->environment('local')) {
    Route::get('/test-db', fn() => DB::table('users')->get());
}


