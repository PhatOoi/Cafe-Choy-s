<?php

namespace App\Http\Controllers;

use App\Models\Product;

class MenuController extends Controller
{
    public function index()
    {
        $products = Product::all(); // lấy tất cả sản phẩm
        return view('menu', compact('products'));
    }
}