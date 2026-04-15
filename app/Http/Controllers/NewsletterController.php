<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class NewsletterController extends Controller
{
    public function subscribe(Request $request)
    {
        // Lấy email được nhập ở form footer/newsletter.
        $email = $request->email;

        // Hiện tại dự án gửi email thông báo nội bộ thay vì lưu subscriber vào database.
        Mail::raw("Có người đăng ký newsletter: " . $email, function ($message) {
            $message->to('tphat1716@gmail.com')
                    ->subject('Đăng ký newsletter mới');
        });

        // Frontend nhận JSON success để hiện thông báo mà không cần reload trang.
        return response()->json(['success' => true]);
    }
}