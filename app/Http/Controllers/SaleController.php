<?php

namespace App\Http\Controllers;

use App\Http\Resources\SaleResource;
use App\Models\Client;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Notifications\BasicNotification;
use Carbon\Carbon;
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
        $sales = SaleResource::collection(Sale::latest()->with('client:id,name', 'products:id,name')->get()->take(20));
        $clients = Client::all(['id', 'name']);
        $total_sales = Sale::all()->count();

        // return $sales;
        return inertia('Sale/Index', compact('sales', 'clients', 'total_sales'));
    }


    public function create()
    {
        //
    }


    public function store(Request $request)
    {
        // Registra la venta
        $sale = Sale::create([
            'has_credit' => $request->data['has_credit'],
            'total' => $request->data['total'],
            'client_id' => $request->data['client_id'],
            'limit_date' => $request->data['limit_date'],
            'paid_at' => $request->data['has_credit'] ? null : now(),
        ]);

        // Agrega todos los productos a la venta
        foreach ($request->data['saleProducts'] as $prd) {
            $product = Product::find($prd['product']['id']);
            
            // Asociar producto a la venta con sus atributos adicionales
            $sale->products()->attach($product->id, [
                'quantity' => $prd['quantity'],
                'price' => $product->public_price
            ]);

            // restar de inventario
            $product->decrement('current_stock', $prd['quantity']);

            // notificar si ha llegado al limite de existencias bajas
            if ($product->current_stock <= $product->min_stock) {
                $title = "Bajo stock";
                $description = "Producto <span class='text-primary'>$product->name</span> alcanzó el nivel mínimo establecido";
                $url = route('products.show', $product->id);

                auth()->user()->notify(new BasicNotification($title, $description, $url));
            }
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
        $item = $sale->load(['products', 'payments']);

        return response()->json(compact('item'));
    }


    public function getByIds(Request $request)
    {
        $items = Sale::whereIn('id', $request->ids)->with(['payments', 'products'])->get();

        return response()->json(compact('items'));
    }


    public function searchProduct(Request $request)
    {
        $queryDate = $request->input('queryDate');
        $queryClient = $request->input('queryClient');

        $salesQuery = Sale::with('client', 'payments', 'products');

        // Filtrar por rango de fechas si se proporciona
        if (!empty($queryDate) && count($queryDate) === 2) {
            $startDate = Carbon::parse($queryDate[0])->startOfDay();
            $endDate = Carbon::parse($queryDate[1])->endOfDay();
            $salesQuery->whereBetween('created_at', [$startDate, $endDate]);
        }

        // Filtrar por cliente si se proporciona
        if (!empty($queryClient)) {
            $salesQuery->where('client_id', $queryClient);
        }

        // Realizar la consulta y devolver los resultados
        $sales = SaleResource::collection($salesQuery->take(20)->get());

        return response()->json(['items' => $sales]);
    }


    public function getItemsByPage($currentPage)
    {
        $offset = $currentPage * 20;
        $sales = SaleResource::collection(Sale::latest()
            ->skip($offset)
            ->take(20)
            ->get());

        return response()->json(['items' => $sales]);
    }
}
