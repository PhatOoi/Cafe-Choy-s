<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Size;
use App\Models\Extra;

class MenuController extends Controller
{
    public function index()
    {
        $products = Product::all();
        $toppings = Extra::topping()->get();
        $sugars = Extra::sugar()->get();
        $ices = Extra::ice()->get();
        $sizes = Size::all();

        return view('menu', compact('products', 'toppings', 'sugars', 'ices', 'sizes'));
    }
}