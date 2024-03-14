<?php

namespace App\Http\Controllers;

use App\Http\Resources\SaleResource;
use App\Models\Client;
use App\Models\Payment;
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

        // Registra la venta
        $sale = Sale::create([
            'has_credit' => $request->data['has_credit'],
            'total' => $request->data['total'],
            'client_id' => $request->data['client_id'],
        ]);

        // Agrega todos los productos a la venta
        foreach ($request->data['saleProducts'] as $product) {
            // Asociar producto a la venta con sus atributos adicionales
            $sale->products()->attach($product['product']['id'], [
                'quantity' => $product['quantity'],
                'price' => $product['product']['public_price']
            ]);
        }

        // si la variable del abono no es null se crea un registro de abono
        if ($request->data['deposit'] !== null) {
            Payment::create([
                'amount' => $request->data['deposit'],
                'notes' => $request->data['deposit_notes'],
                'sale_id' => $sale->id,
            ]);
        }
    }

    
    public function show($sale_id)
    {
        $sale = SaleResource::make(Sale::with('client', 'payments', 'products')->find($sale_id));
        $clients = Client::all(['id', 'name']);

        // return $sale;
        return inertia('Sale/Show', compact('sale', 'clients'));
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
        $sale->delete();
    }

    // API
    public function getById(Sale $sale)
    {
        $item = $sale->load(['payments', 'products']);

        return response()->json(compact('item'));
    }

    public function getByIds(Request $request)
    {
        $items = Sale::whereIn('id', $request->ids)->with(['payments', 'products'])->get();

        return response()->json(compact('items'));
    }
}
