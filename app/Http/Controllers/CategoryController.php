<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    
    public function index()
    {
        //
    }

    
    public function create()
    {
        //
    }

    
    public function store(Request $request)
    {
        $category = Category::create($request->all());

        return response()->json(['item' => $category]);
    }

    
    public function show(Category $category)
    {
        //
    }

    
    public function edit(Category $category)
    {
        //
    }

    
    public function update(Request $request, Category $category)
    {
        //
    }

    
    public function destroy(Category $category)
    {
        //
    }
}
