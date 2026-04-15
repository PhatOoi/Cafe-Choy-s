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
        // Ghi log để dễ dò luồng khi xử lý lỗi đăng ký trên local/log production.
        \Log::info('Đã vào hàm register');

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
            'phone' => 'required|numeric|digits_between:8,10|unique:users,phone',
        ], [
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự, bao gồm chữ hoa, chữ thường và số.',
            'password.required' => 'Bạn chưa nhập mật khẩu!',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp!',
            'email.unique' => 'Gmail đã được sử dụng!',
            'phone.unique' => 'Số điện thoại đã được sử dụng!'
        ]);

        try {
            // Gói việc tìm role customer và tạo user vào transaction để không lưu dở dang.
            $user = DB::transaction(function () use ($request) {
                $customerRoleId = DB::table('user_roles')
                    ->where('name', 'customer')
                    ->value('id');

                // Nếu role customer chưa có trong database thì tạo mới để quá trình đăng ký không bị chặn.
                if (!$customerRoleId) {
                    $customerRoleId = DB::table('user_roles')->insertGetId([
                        'name' => 'customer',
                        'description' => 'Khách hàng — đặt hàng và thanh toán',
                    ]);
                }

                // Tạo tài khoản customer mới với mật khẩu đã hash.
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

            // Log lại user vừa tạo để tiện kiểm tra khi cần debug luồng đăng ký.
            \Log::info('Đã lưu user thành công', ['user' => $user]);
        } catch (\Exception $e) {
            // Bắt mọi lỗi phát sinh trong transaction và trả thông báo thân thiện cho người dùng.
            \Log::error('Lỗi đăng ký', [
                'message' => $e->getMessage(),
                'email' => $request->email,
            ]);

            return back()->withInput()->withErrors([
                'register_error' => 'Đăng ký thất bại. Vui lòng thử lại sau.',
            ]);
        }

        // Đăng ký xong thì quay về login để người dùng đăng nhập bằng tài khoản vừa tạo.
        return redirect('/login')->with('register_success', 'Tạo tài khoản thành công! Bạn có thể đăng nhập.');
    }
}
