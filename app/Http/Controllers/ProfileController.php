<?php
namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function index()
    {
        // Lấy user hiện tại và toàn bộ đơn của user để hiển thị cùng trang hồ sơ.
        $user = Auth::user();
        $orders = Order::with(['items.product', 'items.extras'])
            ->where('user_id', $user->id)
            ->latest('created_at')
            ->get();

        // Cart count dùng cho badge giỏ hàng ở navbar trang profile.
        $cart = session()->get('cart', []);
        $cartCount = array_sum(array_column($cart, 'qty'));

        return view('profile', compact('user', 'orders', 'cartCount'));
    }

    // Bản route/profile phụ cũng render cùng view profile nhưng preload thêm relation role.
    public function profile()
    {
        $user = auth()->user()->load('role');
        $orders = Order::with(['items.product', 'items.extras'])
            ->where('user_id', $user->id)
            ->latest('created_at')
            ->get();

        // Vẫn giữ cart count để giao diện đồng nhất với các trang khách khác.
        $cart = session()->get('cart', []);
        $cartCount = array_sum(array_column($cart, 'qty'));

        return view('profile', compact('user', 'orders', 'cartCount'));
    }

    // Xử lý đổi mật khẩu cho người dùng ngay trên trang hồ sơ.
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => 'required',
            'password' => ['required', 'min:6', 'confirmed', function ($attribute, $value, $fail) {
                if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{6,}$/', $value)) {
                    $fail('Mật khẩu phải có ít nhất 6 ký tự, bao gồm chữ hoa, chữ thường và số.');
                }
            }],
        ], [
            'current_password.required' => 'Bạn chưa nhập mật khẩu hiện tại!',
            'password.required' => 'Bạn chưa nhập mật khẩu mới!',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp!',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự, bao gồm chữ hoa, chữ thường và số.',
        ]);

        // Mật khẩu hiện tại phải đúng thì mới cho phép cập nhật.
        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors([
                'current_password' => 'Mật khẩu hiện tại không đúng.',
            ]);
        }

        // Không cho đổi sang đúng mật khẩu đang dùng để tránh cập nhật giả.
        if (Hash::check($validated['password'], $user->password)) {
            return back()->withErrors([
                'password' => 'Mật khẩu mới phải khác mật khẩu hiện tại.',
            ]);
        }

        $user->password = Hash::make($validated['password']);
        $user->save();

        return redirect()->route('profile.index')->with('success', 'Đổi mật khẩu thành công!');
    }

    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'avatar.required' => 'Vui lòng chọn ảnh.',
            'avatar.image'    => 'File phải là ảnh.',
            'avatar.mimes'    => 'Chỉ chấp nhận jpg, jpeg, png, webp.',
            'avatar.max'      => 'Ảnh không được vượt quá 2MB.',
        ]);

        $user = Auth::user();

        // Xóa avatar cũ nếu có
        if ($user->avatar_url && Storage::disk('public')->exists($user->avatar_url)) {
            Storage::disk('public')->delete($user->avatar_url);
        }

        $path = $request->file('avatar')->store('avatars', 'public');
        $user->avatar_url = $path;
        $user->save();

        return redirect()->route('profile.index')->with('success', 'Cập nhật ảnh đại diện thành công!');
    }
}
