<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        return view('profile', compact('user'));
    }
    public function profile()
    {
        $user = auth()->user()->load('role');
        return view('profile', compact('user'));
    }
}