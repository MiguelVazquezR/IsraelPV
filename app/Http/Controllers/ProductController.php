<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{

    public function index()
    {
        $products = ProductResource::collection(Product::get()->take(20));
        $total_products = Product::all()->count();

        return inertia('Product/Index', compact('products', 'total_products'));
    }


    public function create()
    {
        $products_quantity = Product::all()->count();
        $categories = Category::all();
        
        return inertia('Product/Create', compact('products_quantity', 'categories'));
    }

    
    public function store(Request $request)
    {
        //
    }


    public function show(Product $product)
    {
        //
    }


    public function edit(Product $product)
    {
        //
    }


    public function update(Request $request, Product $product)
    {
        //
    }


    public function destroy(Product $product)
    {
        //
    }
}
