<?php

namespace App\Http\Controllers;

use App\Http\Resources\SaleResource;
use App\Models\Client;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    
    public function pointIndex()
    {
        $products = Product::all(['id', 'name', 'code']);
        $clients = Client::all(['id', 'name']);

        // return $products;
        return inertia('Sale/Point', compact('products', 'clients'));
    }

    public function index()
    {
        $sales = SaleResource::collection(Sale::latest()->with('client:id,name', 'products:id,name')->get());

        // return $sales;
        return inertia('Sale/Index', compact('sales'));
    }

    
    public function create()
    {
        //
    }

    
    public function store(Request $request)
    {
        // return $request;
        $sale = Sale::create([
            'has_credit' => $request->data['has_credit'],
            'total' => $request->data['total'],
            'client_id' => $request->data['client_id'],
        ]);

        foreach ($request->data['saleProducts'] as $product) {
            // Asociar producto a la venta con sus atributos adicionales
            $sale->products()->attach($product['product']['id'], [
                'quantity' => $product['quantity'],
                'price' => $product['product']['public_price']
            ]);
        }

        // foreach ($request->data['saleProducts'] as $sale) {
        //     //registra cada producto vendido
        //     Sale::create([
        //         'current_price' => $sale['product']['public_price'],
        //         'quantity' => $sale['quantity'],
        //         'product_id' => $sale['product']['id'],
        //     ]);

        //     //Registra el historial de venta de cada producto
        //     ProductHistory::create([
        //         'description' => 'Registro de venta. ' . $sale['quantity'] . ' piezas',
        //         'type' => 'Venta',
        //         'product_id' => $sale['product']['id']
        //     ]);

        //     //rebaja del stock la cantidad de piezas vendidas
        //     $product = Product::find($sale['product']['id']);
        //     $product->decrement('current_stock', $sale['quantity']);

        //     // notificar si ha llegado al limite de existencias bajas
        //     if ($product->current_stock <= $product->min_stock) {
        //         $title = "Bajo stock";
        //         $description = "Producto <span class='text-primary'>$product->name</span> alcanzó el nivel mínimo establecido";
        //         $url = route('products.show', $product->id);

        //         auth()->user()->notify(new BasicNotification($title, $description, $url));
        //     }

        // }
    }

    
    public function show(Sale $sale)
    {
        //
    }

    
    public function edit(Sale $sale)
    {
        //
    }

    
    public function update(Request $request, Sale $sale)
    {
        //
    }

    
    public function destroy(Sale $sale)
    {
        //
    }
}
