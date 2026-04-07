<?php

namespace App\Http\Controllers;

// Controller hiển thị trang giới thiệu
class AboutController extends Controller
{
    // Hiển thị trang giới thiệu
    public function index()
    {
        return view('about');
    }
}