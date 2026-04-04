<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\User;

class ForgotPasswordController extends Controller
{
    public function showEmailForm()
    {
        return view('auth.forgot-password-email');
    }

    public function sendCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);
        $code = random_int(100000, 999999);
        DB::table('password_resets')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => Hash::make($code),
                'created_at' => now(),
            ]
        );
        // Gửi email
        Mail::raw('Mã xác thực của bạn là: ' . $code, function ($message) use ($request) {
            $message->to($request->email)
                ->subject('Mã xác thực đặt lại mật khẩu');
        });
        return redirect()->route('forgot-password.verify-form')->with('email', $request->email);
    }

    public function showVerifyForm(Request $request)
    {
        $email = session('email');
        return view('auth.forgot-password-verify', compact('email'));
    }

    public function verifyCode(Request $request)
    {
        $request->validate([
            'code' => 'required|digits:6',
        ]);
        $email = $request->email;
        $reset = DB::table('password_resets')->where('email', $email)->first();
        if (!$reset || !Hash::check($request->code, $reset->token)) {
            return back()->withErrors(['code' => 'Mã xác thực không đúng!']);
        }
        session(['reset_email' => $email]);
        return redirect()->route('forgot-password.reset-form');
    }

    public function showResetForm(Request $request)
    {
        $email = session('reset_email');
        return view('auth.forgot-password-reset', compact('email'));
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|min:6|confirmed',
        ]);
        $email = $request->email;
        $user = User::where('email', $email)->first();
        if (!$user) {
            return back()->withErrors(['email' => 'Email không tồn tại!']);
        }
        $user->password = Hash::make($request->password);
        $user->save();
        // Xóa mã reset
        DB::table('password_resets')->where('email', $email)->delete();
        return redirect()->route('login')->with('status', 'Đổi mật khẩu thành công!');
    }
}
