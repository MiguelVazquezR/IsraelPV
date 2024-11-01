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
        $clients = Client::all(['id', 'name', 'debt']);

        // return $clients;
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

            $product_price = $prd['product']['public_price']; //Es el precio que se manejó en la venta con o sin descuento.
            $product = Product::find($prd['product']['id']);
            
            // Asociar producto a la venta con sus atributos adicionales
            $sale->products()->attach($product->id, [
                'quantity' => $prd['quantity'],
                'price' => $product_price
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
        
        //recupera al cliente para actualizar su deuda
        $client = Client::find($request->data['client_id']);

        //suba el total de la compra a la deuda anterior
        $updated_debt = $client->debt + $request->data['total'];

        // si la variable del abono no es null se crea un registro de abono
        //se cambio a actualizar la deuda total del cliente restando el abono al total de la columna debt de la tabla clients
        if ($request->data['deposit'] !== null) {

            //suba el total de la compra a la deuda anterior y resta el abono
            $remain_debt = $updated_debt - floatval($request->data['deposit']); 
            $client->update(['debt' =>  $remain_debt]);

            // Payment::create([
            //     'amount' => $request->data['deposit'],
            //     'notes' => $request->data['deposit_notes'],
            //     'sale_id' => $sale->id,
            // ]);
        } else {
            $client->update(['debt' =>  $updated_debt]);
        }
        return response()->json(['item' => $sale]);
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

    
    public function printTicket(Request $request, $sale_id)
    {
        $sale = SaleResource::make(Sale::with(['client', 'products'])->find($sale_id));

        // si la venta tiene cliente asignado
        if ( $sale->client ) {
            // // Obtener todas las ventas del cliente cuya propiedad has_credit == 1 y paid_at == null
            // // para calcular el saldo antes de esta venta
            // $sales = Sale::where('client_id', $sale->client->id)
            // ->where('has_credit', 1)
            // ->whereNull('paid_at')
            // ->where('id', '!=', $sale_id) // Excluir la venta actual
            // ->where('created_at', '<', $sale->created_at) // Solo ventas anteriores a la fecha de la venta actual
            // ->get();
            
            // // Inicializar el total adeudado del cliente
            // $initial_saldo = 0;
            
            // // Iterar sobre las ventas encontradas
            // foreach ($sales as $sale_temp) {
                //     // Obtener los abonos para esta venta
                //     $payment = Payment::where('sale_id', $sale_temp->id)->sum('amount');
                
            //     // Restar la suma de los abonos al total de la venta
            //     $initial_saldo += $sale_temp->total - $payment;
            // }
            
            // //recupera el abono de la nueva venta si es que se hizo
            // $payment = Payment::where('sale_id', $sale_id)->get(['id', 'amount', 'notes'])->first();
            
            //recupero al cliente que hizo la compra para tomar su deuda y abono
            $client = Client::find($sale->client)->first();
            
            // return $client;
            // Obtén el parámetro 'payment' y decodifícalo
            $paymentData = $request->query('payment');
            $payment = $paymentData ? json_decode($paymentData, true)['payment'] : null;
            if ( $payment ) {
                $initial_saldo = $client->debt - $sale->total + $payment;
            } else {
                $initial_saldo = $client->debt;
            }
        } else {
            $payment = null;
            $initial_saldo = null;
        }
        
        // return $sale;
        return inertia('Sale/PrintTicket', compact('sale', 'payment', 'initial_saldo'));
    }

    public function printPaymentTicket(Request $request, $client_id)
    {     
        $client = Client::find($client_id)->first();
        
        // return $client;

        // Obtén el parámetro 'payment' y decodifícalo
        $paymentData = $request->query('payment');
        $payment = $paymentData ? json_decode($paymentData, true)['payment'] : null;
        $initial_saldo = $client->debt + $payment;
        
        // return $sale;
        return inertia('Sale/PrintPaymentTicket', compact('payment', 'initial_saldo', 'client'));
    }
}
