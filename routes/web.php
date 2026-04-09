
<?php
use App\Http\Controllers\SearchController;
// Tìm kiếm sản phẩm
Route::get('/search', [SearchController::class, 'index'])->name('search');

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\AboutController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\CartController;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\ProfileController;

// Trang chủ
Route::get('/', function () {
    return view('home');
});

// Trang menu
Route::get('/menu', [MenuController::class, 'index']);


Route::get('/login',[LoginController::class,'index']);
Route::post('/login', [LoginController::class, 'login'])->name('login');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Trang giỏ hàng
Route::post('/cart/add', [CartController::class, 'add'])->middleware('auth');
Route::get('/cart', [CartController::class, 'index'])->middleware('auth');
Route::get('/cart/remove/{id}', [CartController::class, 'remove'])->middleware('auth');

Route::get('/test-db', function () {
    $users = DB::table('users')->get();
    return $users;
});
Route::get('/profile', [ProfileController::class, 'index'])->middleware('auth');

Route::get('/search', [MenuController::class, 'search']);

// Quên mật khẩu
Route::get('/forgot-password', [ForgotPasswordController::class, 'showEmailForm'])->name('forgot-password.email-form');
Route::post('/forgot-password/send-code', [ForgotPasswordController::class, 'sendCode'])->name('forgot-password.send-code');
Route::get('/forgot-password/verify', [ForgotPasswordController::class, 'showVerifyForm'])->name('forgot-password.verify-form');
Route::post('/forgot-password/verify', [ForgotPasswordController::class, 'verifyCode'])->name('forgot-password.verify-code');
Route::get('/forgot-password/reset', [ForgotPasswordController::class, 'showResetForm'])->name('forgot-password.reset-form');
Route::post('/forgot-password/reset', [ForgotPasswordController::class, 'resetPassword'])->name('forgot-password.reset-password');

// Đăng ký người dùng mới
Route::post('/register', [RegisterController::class, 'register'])->name('register');