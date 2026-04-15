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
        // Nếu đã đăng nhập thì không cho quay lại form login, chuyển thẳng theo vai trò.
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

        // Thử xác thực user bằng email/password và tùy chọn remember me.
        if (Auth::attempt($credentials, $remember)) {
            // Đăng nhập thành công, tạo lại session
            $request->session()->regenerate();

            // Điều hướng theo role để admin/staff/customer vào đúng khu vực của họ.
            return $this->redirectByRole(Auth::user()->role_id);
        }

        // Đăng nhập thất bại, quay lại với thông báo lỗi
        return back()->with('error', 'Email hoặc mật khẩu không đúng!');
    }

    // Xử lý đăng xuất
    public function logout(Request $request)
    {
        Auth::logout(); // Gỡ trạng thái đăng nhập hiện tại
        $request->session()->invalidate(); // Hủy session cũ để tránh dùng lại dữ liệu cũ
        $request->session()->regenerateToken(); // Tạo CSRF token mới sau logout
        return redirect('/'); // Chuyển về trang chủ của khách
    }

    // Gom logic redirect theo role vào một hàm riêng để tái sử dụng ở login/index.
    private function redirectByRole(int $roleId)
    {
        return match ($roleId) {
            1 => redirect('/admin'),
            2 => redirect()->route('staff.dashboard'),
            default => redirect('/'),
        };
    }
}