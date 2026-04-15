<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Str;
use App\Models\Size;
use App\Models\Extra;
use App\Models\Category;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->q;

        $categories = Category::whereHas('products', function ($query) use ($q) {
            $query->where('name', 'like', '%' . $q . '%');
        })
            ->with(['products' => function ($query) use ($q) {
                $query->where('name', 'like', '%' . $q . '%')
                    ->orderBy('name');
            }])
            ->orderBy('sort_order')
            ->get();

        $toppings = Extra::topping()->get();
        $sugars = Extra::sugar()->get();
        $ices = Extra::ice()->get();
        $sizes = Size::all();
        $cartCount = collect(session('cart', []))->sum('qty');

        return view('search-result', compact(
            'categories',
            'toppings',
            'sugars',
            'ices',
            'sizes',
            'cartCount',
            'q'
        ));
    }
}
