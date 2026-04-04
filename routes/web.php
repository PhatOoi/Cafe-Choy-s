<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\AboutController;
use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\RegisterController;

// Trang chủ
Route::get('/', function () {
    return view('home');
});

// Trang menu
Route::get('/menu', [MenuController::class, 'index']);
// Trang about
Route::get('/about',[AboutController::class,'index']);

Route::get('/login',[LoginController::class,'index']);
Route::post('/login', [LoginController::class, 'login'])->name('login');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Trang giỏ hàng
Route::get('/cart', function () {
    return view('cart');
});

Route::get('/test-db', function () {
    $users = DB::table('users')->get();
    return $users;
});

// Quên mật khẩu
Route::get('/forgot-password', [ForgotPasswordController::class, 'showEmailForm'])->name('forgot-password.email-form');
Route::post('/forgot-password/send-code', [ForgotPasswordController::class, 'sendCode'])->name('forgot-password.send-code');
Route::get('/forgot-password/verify', [ForgotPasswordController::class, 'showVerifyForm'])->name('forgot-password.verify-form');
Route::post('/forgot-password/verify', [ForgotPasswordController::class, 'verifyCode'])->name('forgot-password.verify-code');
Route::get('/forgot-password/reset', [ForgotPasswordController::class, 'showResetForm'])->name('forgot-password.reset-form');
Route::post('/forgot-password/reset', [ForgotPasswordController::class, 'resetPassword'])->name('forgot-password.reset-password');

// Đăng ký người dùng mới
Route::post('/register', [RegisterController::class, 'register'])->name('register');