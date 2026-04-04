<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        \Log::info('Đã vào hàm register');
        $request->validate([
            'username' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'min:6', 'confirmed', function($attribute, $value, $fail) {
                // Kiểm tra độ mạnh của mật khẩu: ít nhất 1 chữ hoa, 1 chữ thường, 1 số
                if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{6,}$/', $value)) {
                    $fail('Mật khẩu phải có ít nhất 6 ký tự, bao gồm chữ hoa, chữ thường và số.');
                }
            }],
            'phone' => 'required|string|max:20',
        ], [
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự, bao gồm chữ hoa, chữ thường và số.',
            'password.required' => 'Bạn chưa nhập mật khẩu!',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp!',
            'email.unique' => 'Gmail đã được sử dụng!'
        ]);

        try {
            $user = new User();
            $user->name = $request->username;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->phone = $request->phone;
            $user->role_id = 3; // Mặc định là khách hàng
            $user->is_active = true;
            $user->save();
            \Log::info('Đã lưu user thành công', ['user' => $user]);
        } catch (\Exception $e) {
            \Log::error('Lỗi đăng ký: ' . $e->getMessage());
            return back()->withInput()->withErrors(['register_error' => 'Đăng ký thất bại: ' . $e->getMessage()]);
        }

        return redirect('/login')->with('register_success', 'Tạo tài khoản thành công! Bạn có thể đăng nhập.');
    }
}
