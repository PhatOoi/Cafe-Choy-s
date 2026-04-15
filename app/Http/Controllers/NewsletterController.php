<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class NewsletterController extends Controller
{
    public function subscribe(Request $request)
    {
        $email = $request->email;

        Mail::raw("Có người đăng ký newsletter: " . $email, function ($message) {
            $message->to('tphat1716@gmail.com')
                    ->subject('Đăng ký newsletter mới');
        });

        return response()->json(['success' => true]);
    }
}