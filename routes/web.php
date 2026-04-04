<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\AboutController;
use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\DB;
// Trang chủ
Route::get('/', function () {
    return view('home');
});

// Trang menu
Route::get('/menu', [MenuController::class, 'index']);
// Trang about
Route::get('/about',[AboutController::class,'index']);

Route::get('/login',[LoginController::class,'index']);

// Trang giỏ hàng
Route::get('/cart', function () {
    return view('cart');
});

Route::get('/test-db', function () {
    $users = DB::table('users')->get();
    return $users;
});