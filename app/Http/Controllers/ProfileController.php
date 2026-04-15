<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user()->load('role');
        return view('profile', compact('user'));
    }

    // FIX: method này hoàn toàn chưa tồn tại - route PUT /profile crash
    public function update(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'name'  => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'email' => 'required|email|unique:users,email,' . $user->id,
        ], [
            'name.required'  => 'Vui lòng nhập họ tên.',
            'email.unique'   => 'Email này đã được sử dụng.',
        ]);

        $data = $request->only('name','phone','email');

        if ($request->filled('password')) {
            $request->validate([
                'current_password' => 'required',
                'password' => ['required','min:6','confirmed', function($a,$v,$f){
                    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{6,}$/', $v))
                        $f('Mật khẩu cần chữ hoa, chữ thường và số.');
                }],
            ]);
            if (!Hash::check($request->current_password, $user->password))
                return back()->withErrors(['current_password' => 'Mật khẩu hiện tại không đúng.']);
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);
        return back()->with('success', 'Cập nhật hồ sơ thành công!');
    }
}
