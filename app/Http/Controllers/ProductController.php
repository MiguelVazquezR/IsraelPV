<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Models\Expense;
use App\Models\Product;
use App\Models\ProductHistory;
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
        $request->validate([
            'name' => 'required|string|max:100',
            'category_id' => 'required',
            'public_price' => 'required|numeric|min:0|max:9999',
            'cost' => 'nullable|numeric|min:0|max:9999',
            'current_stock' => 'nullable|numeric|min:0|max:9999',
            'min_stock' => 'nullable|numeric|min:0|max:9999',
            'max_stock' => 'nullable|numeric|min:0|max:9999',
        ]);

        $product = Product::create($request->except('imageCover'));

        // Guardar el archivo en la colección 'guest_images'
        if ($request->hasFile('imageCover')) {
            $product->addMediaFromRequest('imageCover')->toMediaCollection('imageCover');
        }

        return to_route('products.show', $product->id);
    }


    public function show($product_id)
    {
        $product = ProductResource::make(Product::with('category')->find($product_id));

        return inertia('Product/Show', compact('product'));
    }


    public function edit($product_id)
    {
        $product = ProductResource::make(Product::with('category')->find($product_id));
        $categories = Category::all();

        return inertia('Product/Edit', compact('product', 'categories'));
    }


    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'category_id' => 'required',
            'public_price' => 'required|numeric|min:0|max:9999',
            'cost' => 'nullable|numeric|min:0|max:9999',
            'current_stock' => 'nullable|numeric|min:0|max:9999',
            'min_stock' => 'nullable|numeric|min:0|max:9999',
            'max_stock' => 'nullable|numeric|min:0|max:9999',
        ]);

        $product->update($request->except('imageCover'));

        // media
        // Eliminar imagen sólo si se borró desde el input y no se agregó una nueva
        if ($request->imageCoverCleared) {
            $product->clearMediaCollection('imageCover');
        }

        return to_route('products.show', $product->id);
    }

    public function updateWithMedia(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'category_id' => 'required',
            'public_price' => 'required|numeric|min:0|max:9999',
            'cost' => 'nullable|numeric|min:0|max:9999',
            'current_stock' => 'nullable|numeric|min:0|max:9999',
            'min_stock' => 'nullable|numeric|min:0|max:9999',
            'max_stock' => 'nullable|numeric|min:0|max:9999',
        ]);

        $product->update($request->except('imageCover'));

        // media ------------
        // Eliminar imagen antigua sólo si se proporciona una nueva
        if ($request->hasFile('imageCover')) {
            $product->clearMediaCollection('imageCover');
        }

        // Guardar el archivo en la colección 'imageCover'
        if ($request->hasFile('imageCover')) {
            $product->addMediaFromRequest('imageCover')->toMediaCollection('imageCover');
        }

        return to_route('products.show', $product->id);
    }


    public function destroy(Product $product)
    {
        $product->delete();
    }

    public function searchProduct(Request $request)
    {
        $query = $request->input('query');

        // Realiza la búsqueda en la base de datos
        $products = ProductResource::collection(Product::with('category')->where('name', 'like', "%$query%")->orWhere('id', $query)->get()->take(20));

        return response()->json(['items' => $products]);
    }


    public function getProductScaned($product_id)
    {
        // Realiza la búsqueda en la base de datos
        $product = ProductResource::make(Product::with('category')->find($product_id));

        return response()->json(['item' => $product]);
    }


    public function entryStock(Request $request, $product_id)
    {
        $product = Product::find($product_id);

        // Asegúrate de convertir la cantidad a un número antes de sumar
        $product->current_stock += intval($request->quantity);

        // Guarda el producto
        $product->save();

        //Crea un registro de entrada en historial de producto 
        ProductHistory::create([
            'type' => 'Entrada',
            'quantity' => $request->quantity,
            'notes' => null,
            'product_id' => $product_id,
        ]);
        
        // Crear egreso
        Expense::create([
            'concept' => 'Compra de producto: ' . $product->name,
            'current_price' => $product->cost,
            'quantity' => $request->quantity
        ]);
    }

    public function getItemsByPage($currentPage)
    {
        $offset = $currentPage * 20;
        $products = ProductResource::collection(Product::latest()
            ->skip($offset)
            ->take(20)
            ->get());

        return response()->json(['items' => $products]);
    }
}
