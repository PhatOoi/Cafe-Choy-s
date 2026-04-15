<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Controller xử lý đăng nhập/đăng xuất
class LoginController extends Controller
{
    // Hiển thị trang đăng nhập
    public function index()
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user()->role_id);
        }

        return view('login');
    }

    // Xử lý đăng nhập
    public function login(Request $request)
    {
        // Kiểm tra dữ liệu đầu vào: email và password phải hợp lệ
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Kiểm tra người dùng có chọn "Nhớ đăng nhập" không
        $remember = $request->has('remember');

        // Thử đăng nhập với thông tin đã nhập
        if (Auth::attempt($credentials, $remember)) {
            // Đăng nhập thành công, tạo lại session
            $request->session()->regenerate();
            return $this->redirectByRole(Auth::user()->role_id);
        }

        // Đăng nhập thất bại, quay lại với thông báo lỗi
        return back()->with('error', 'Email hoặc mật khẩu không đúng!');
    }

    // Xử lý đăng xuất
    public function logout(Request $request)
    {
        Auth::logout(); // Đăng xuất tài khoản
        $request->session()->invalidate(); // Hủy session hiện tại
        $request->session()->regenerateToken(); // Tạo lại CSRF token
        return redirect('/'); // Chuyển về trang chủ
    }

    private function redirectByRole(int $roleId)
    {
        return match ($roleId) {
            1 => redirect('/admin'),
            2 => redirect()->route('staff.dashboard'),
            default => redirect('/'),
        };
    }
}