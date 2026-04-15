<?php

use App\Http\Controllers\Controller;

// Controller hiển thị trang chủ
class HomeController extends Controller
{
    // Hiển thị trang chủ
    public function index()
    {
        // Trang này chỉ render view landing page, không cần nạp thêm dữ liệu động.
        return view('home');
    }
}