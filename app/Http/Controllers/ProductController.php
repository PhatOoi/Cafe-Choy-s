<?php
 
 namespace App\Http\Controllers;
    use App\Models\Product;
    

 class ProductController extends Controller
 {
 public function search(Request $request)
{
    $query = $request->q;

    $products = Product::where('name', 'LIKE', "%$query%")
        ->orWhere('description', 'LIKE', "%$query%")
        ->paginate(12);

    return view('search', compact('products', 'query'));
}
 }