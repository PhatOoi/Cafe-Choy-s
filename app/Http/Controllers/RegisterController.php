<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

// Controller xử lý đăng ký tài khoản mới
class RegisterController extends Controller
{
    // Xử lý đăng ký tài khoản
    public function register(Request $request)
    {
        \Log::info('Đã vào hàm register'); // Ghi log khi vào hàm
        // Kiểm tra dữ liệu đầu vào và validate mật khẩu mạnh
        $request->validate([
            'username' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'min:6', 'confirmed', function($attribute, $value, $fail) {
                // Kiểm tra độ mạnh của mật khẩu: ít nhất 1 chữ hoa, 1 chữ thường, 1 số
                if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{6,}$/', $value)) {
                    $fail('Mật khẩu phải có ít nhất 6 ký tự, bao gồm chữ hoa, chữ thường và số.');
                }
            }],
            'phone' => 'required|numeric|digits_between:8,10',
        ], [
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự, bao gồm chữ hoa, chữ thường và số.',
            'password.required' => 'Bạn chưa nhập mật khẩu!',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp!',
            'email.unique' => 'Gmail đã được sử dụng!'
        ]);

        try {
            $user = DB::transaction(function () use ($request) {
                $customerRoleId = DB::table('user_roles')
                    ->where('name', 'customer')
                    ->value('id');

                if (!$customerRoleId) {
                    $customerRoleId = DB::table('user_roles')->insertGetId([
                        'name' => 'customer',
                        'description' => 'Khách hàng — đặt hàng và thanh toán',
                    ]);
                }

                $user = new User();
                $user->name = $request->username;
                $user->email = $request->email;
                $user->password = Hash::make($request->password);
                $user->phone = $request->phone;
                $user->role_id = $customerRoleId;
                $user->is_active = true;
                $user->save();

                return $user;
            });

            \Log::info('Đã lưu user thành công', ['user' => $user]);
        } catch (\Exception $e) {
            \Log::error('Lỗi đăng ký', [
                'message' => $e->getMessage(),
                'email' => $request->email,
            ]);

            return back()->withInput()->withErrors([
                'register_error' => 'Đăng ký thất bại. Vui lòng thử lại sau.',
            ]);
        }

        // Đăng ký thành công, chuyển hướng về trang đăng nhập
        return redirect('/login')->with('register_success', 'Tạo tài khoản thành công! Bạn có thể đăng nhập.');
    }
}
