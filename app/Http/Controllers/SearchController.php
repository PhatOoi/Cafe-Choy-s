<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->input('q');
        $products = [];
        if ($q) {
            $products = Product::where('name', 'like', '%'.$q.'%')
                ->orWhere('description', 'like', '%'.$q.'%')
                ->get();
        }
        return view('search-result', compact('products', 'q'));
    }
}
