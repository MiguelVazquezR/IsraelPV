<?php

namespace App\Http\Controllers;

use App\Http\Resources\SaleResource;
use App\Models\Client;
use App\Models\GlobalPayment;
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
        // Optimización: Solo traer columnas necesarias
        $products = Product::select(['id', 'name', 'code', 'public_price', 'current_stock', 'min_stock'])->get();
        $clients = Client::select(['id', 'name', 'debt'])->get();

        return inertia('Sale/Point', compact('products', 'clients'));
    }

    public function index()
    {
        // CAMBIO: Paginación aumentada a 100 por página
        $sales = SaleResource::collection(
            Sale::latest()
                ->with(['client:id,name', 'products:id,name', 'payments'])
                ->paginate(100)
        );

        $clients = Client::all(['id', 'name']);
        
        $total_sales = Sale::count();

        return inertia('Sale/Index', compact('sales', 'clients', 'total_sales'));
    }


    public function create()
    {
        //
    }


    public function store(Request $request)
    {   
        $sale = Sale::create([
            'has_credit' => $request->data['has_credit'],
            'total' => $request->data['total'],
            'client_id' => $request->data['client_id'],
            'limit_date' => $request->data['limit_date'],
            'paid_at' => $request->data['has_credit'] ? null : now(),
        ]);

        foreach ($request->data['saleProducts'] as $prd) {
            $product_price = $prd['product']['public_price'];
            $product = Product::find($prd['product']['id']);
            
            $sale->products()->attach($product->id, [
                'quantity' => $prd['quantity'],
                'price' => $product_price
            ]);

            $product->decrement('current_stock', $prd['quantity']);

            if ($product->current_stock <= $product->min_stock) {
                $title = "Bajo stock";
                $description = "Producto <span class='text-primary'>$product->name</span> alcanzó el nivel mínimo establecido";
                $url = route('products.show', $product->id);

                auth()->user()->notify(new BasicNotification($title, $description, $url));
            }
        }
        
        $client = Client::find($request->data['client_id']);
        $updated_debt = $client->debt + $request->data['total'];

        if ($request->data['deposit'] !== null) {
            $remain_debt = $updated_debt - floatval($request->data['deposit']); 
            $client->update(['debt' =>  $remain_debt]);

            GlobalPayment::create([
                'initial_amount' => $updated_debt,
                'amount' => floatval($request->data['deposit']),
                'date' => now(),
                'notes' => $request->data['deposit_notes'],
                'client_id' => $client->id,
            ]);
        } else {
            $client->update(['debt' =>  $updated_debt]);
        }
        return response()->json(['item' => $sale]);
    }

    public function show($sale_id)
    {
        $sale = SaleResource::make(Sale::with('client', 'payments', 'products')->findOrFail($sale_id));
        $clients = Client::all(['id', 'name']);

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


    public function getByIdCopy(Sale $sale)
    {
        $sale = SaleResource::make(Sale::latest()->with('client:id,name', 'products:id,name,cost', 'payments' )->find($sale->id));
        return response()->json(compact('sale'));
    }


    public function getByIds(Request $request)
    {
        $items = Sale::whereIn('id', $request->ids)->with(['payments', 'products'])->latest()->get();
        return response()->json(compact('items'));
    }

    public function searchProduct(Request $request)
    {
        $queryDate = $request->input('queryDate');
        $queryClient = $request->input('queryClient');

        $salesQuery = Sale::with('client', 'payments', 'products');

        if (!empty($queryDate) && count($queryDate) === 2) {
            $startDate = Carbon::parse($queryDate[0])->startOfDay();
            $endDate = Carbon::parse($queryDate[1])->endOfDay();
            $salesQuery->whereBetween('created_at', [$startDate, $endDate]);
        }

        if (!empty($queryClient)) {
            $salesQuery->where('client_id', $queryClient);
        }

        // CAMBIO: Paginación de búsqueda también a 100
        $sales = SaleResource::collection($salesQuery->latest()->paginate(100));

        return response()->json(['items' => $sales]);
    }

    public function getItemsByPage($currentPage)
    {
        // CAMBIO: Paginación "Cargar más" a 100
        $sales = SaleResource::collection(
            Sale::latest()
                ->with(['client:id,name', 'products:id,name', 'payments']) 
                ->paginate(100, ['*'], 'page', $currentPage)
        );

        return response()->json(['items' => $sales]);
    }

    
    public function printTicket(Request $request, $sale_id)
    {
        $sale = SaleResource::make(Sale::with(['client', 'products'])->find($sale_id));
        
        $client = Client::find($sale->client_id);
        
        $paymentData = $request->query('payment');
        $payment = $paymentData ? json_decode($paymentData, true)['payment'] : null;
        
        $printDebt = $request->query('printDebt') ? $request->query('printDebt') : false;

        if ( $payment ) {
            $initial_saldo = $client->debt - $sale->total + $payment;
        } else {
            $initial_saldo = $client->debt - $sale->total;
        }

        return inertia('Sale/PrintTicket', compact('sale', 'payment', 'initial_saldo', 'printDebt'));
    }

    public function printPaymentTicket(Request $request, $client_id)
    {     
        $client = Client::where('id', $client_id)->first();
        
        $paymentData = $request->query('payment');
        $payment = $paymentData ? json_decode($paymentData, true)['payment'] : null;
        $initial_saldo = $client->debt + $payment;
        
        return inertia('Sale/PrintPaymentTicket', compact('payment', 'initial_saldo', 'client'));
    }
}