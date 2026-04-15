<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class ForgotPasswordController extends Controller
{
    public function showEmailForm()
    {
        return view('auth.forgot-password-email');
    }

    public function sendCode(Request $request)
    {
        // Kiểm tra email hợp lệ và tồn tại
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);
        // Tạo mã xác thực ngẫu nhiên 6 số
        $code = random_int(100000, 999999);
        // Lưu mã xác thực vào bảng password_resets
        DB::table('password_resets')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => Hash::make($code),
                'created_at' => now(),
            ]
        );
        // Gửi email mã xác thực cho người dùng
        Mail::raw('Mã xác thực của bạn là: ' . $code, function ($message) use ($request) {
            $message->to($request->email)
                ->subject('Mã xác thực đặt lại mật khẩu');
        });
        return redirect()->route('forgot-password.verify-form')->with('email', $request->email);
    }

    // Hiển thị form nhập mã xác thực
    public function showVerifyForm(Request $request)
    {
        $email = session('email');
        return view('auth.forgot-password-verify', compact('email'));
    }

    // Xác thực mã xác nhận
    public function verifyCode(Request $request)
    {
        $request->validate([
            'code' => 'required|digits:6',
        ]);
        $email = $request->email;
        $reset = DB::table('password_resets')->where('email', $email)->first();
        // Kiểm tra mã xác thực có đúng không
        if (!$reset || !Hash::check($request->code, $reset->token)) {
            return back()->withErrors(['code' => 'Mã xác thực không đúng!']);
        }
        // Lưu email vào session để chuyển sang bước đặt lại mật khẩu
        session(['reset_email' => $email]);
        return redirect()->route('forgot-password.reset-form');
    }

    public function showResetForm(Request $request)
    {
        $email = session('reset_email');

        if (!$email) {
            return redirect()->route('forgot-password.email-form')
                ->withErrors(['email' => 'Phiên đặt lại mật khẩu đã hết hạn. Vui lòng thực hiện lại.']);
        }

        return view('auth.forgot-password-reset', compact('email'));
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => ['required', 'min:6', 'confirmed', function ($attribute, $value, $fail) {
                if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{6,}$/', $value)) {
                    $fail('Mật khẩu phải có ít nhất 6 ký tự, bao gồm chữ hoa, chữ thường và số.');
                }
            }],
        ], [
            'password.required' => 'Bạn chưa nhập mật khẩu mới!',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp!',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự, bao gồm chữ hoa, chữ thường và số.',
            'email.exists' => 'Email không tồn tại trong hệ thống!',
        ]);

        $sessionEmail = session('reset_email');

        if (!$sessionEmail || $sessionEmail !== $request->email) {
            return redirect()->route('forgot-password.email-form')
                ->withErrors(['email' => 'Phiên đặt lại mật khẩu không hợp lệ. Vui lòng thực hiện lại.']);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Không tìm thấy tài khoản để đặt lại mật khẩu.']);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        DB::table('password_resets')->where('email', $request->email)->delete();
        session()->forget('reset_email');

        return redirect('/login')->with('register_success', 'Đặt lại mật khẩu thành công! Bạn có thể đăng nhập.');
    }
}
