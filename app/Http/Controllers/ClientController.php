<?php

namespace App\Http\Controllers;

use App\Http\Resources\ClientResource;
use App\Models\Client;
use App\Models\GlobalPayment;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;

class ClientController extends Controller
{

    public function index()
    {
        $clients = ClientResource::collection(Client::get()->take(20));
        $total_clients = Client::all()->count();

        return inertia('Client/Index', compact('clients', 'total_clients'));
    }


    public function create()
    {
        return inertia('Client/Create');
    }


    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'rfc' => 'nullable|string|max:255',
            'phone' => 'required|string|min:10|max:13',
            'address' => 'nullable|string',
        ]);

        $client = Client::create($request->all());

        // return to_route('clients.show', $client->id);
    }


    public function show(Client $client)
    {   
        // recupera las ultimas 30 ventas del cliente
        $client = $client->load(['sales' => function($query) {
            $query->limit(10);
        }]);
        $clients = Client::all(['id', 'name']);

        // return $client;
        return inertia('Client/Show', compact('client', 'clients'));
    }


    public function edit(Client $client)
    {
        return inertia('Client/Edit', compact('client'));
    }


    public function update(Request $request, Client $client)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'rfc' => 'nullable|string|max:255',
            'phone' => 'required|string|min:10|max:13',
            'address' => 'nullable|string',
        ]);

        $client->update($request->all());

        return to_route('clients.show', $client->id);
    }


    public function destroy(Client $client)
    {
        $client->delete();
    }

    // API
    public function getById(Client $client)
    {
        return response()->json(['item' => $client]);
    }

    public function getClientPendentAmount(Client $client)
    {
        // Obtener todas las ventas del cliente cuya propiedad has_credit == 1 y paid_at == null
        $sales = Sale::where('client_id', $client->id)
            ->where('has_credit', 1)
            ->whereNull('paid_at')
            ->get();

        // Inicializar el total adeudado del cliente
        $pendentAmount = 0;

        // Iterar sobre las ventas encontradas
        foreach ($sales as $sale) {
            // Obtener los abonos para esta venta
            $payment = Payment::where('sale_id', $sale->id)->sum('amount');

            // Restar la suma de los abonos al total de la venta
            $pendentAmount += $sale->total - $payment;
        }

        return response()->json(['item' => $pendentAmount]);
    }

    public function search(Request $request)
    {
        $query = $request->input('query');

        // Realiza la búsqueda en la base de datos
        $clients = ClientResource::collection(Client::where('name', 'like', "%$query%")
            ->orWhere('phone', 'like', "%$query%")
            ->get()
            ->take(5));

        return response()->json(['items' => $clients]);
    }


    public function getItemsByPage($currentPage)
    {
        $offset = $currentPage * 20;
        $clients = ClientResource::collection(Client::skip($offset)
            ->take(20)
            ->get());

        return response()->json(['items' => $clients]);
    }

    public function storeDebt(Request $request)
    {
        $request->validate([
            'total' => 'required|numeric|min:0|max:999999'
        ]);

        $client = Client::where('id', $request->client_id)->first();
        $updated_debt = $client->debt + $request->total;
        $client->update(['debt' => $updated_debt]);
        
        // // Registra la venta
        // $sale = Sale::create([
        //     'has_credit' => $request->has_credit,
        //     'total' => $request->total,
        //     'client_id' => $request->client_id,
        // ]);

        // // Agrega adeudo a la venta
        // $product = Product::where('name', 'Adeudo anterior')->first();
            
        // // Asociar producto a la venta con sus atributos adicionales
        // $sale->products()->attach($product->id, [
        //     'quantity' => 1,
        //     'price' => $request->total
        // ]);
    }

    public function storePayment(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1|max:999999',
        ]);

        $client = Client::find($request->client_id);

        GlobalPayment::create([
            'initial_amount' => $client->debt,
            'amount' => $request->amount,
            'date' => $request->date,
            'notes' => $request->notes,
            'client_id' => $request->client_id,
        ]);

        $updated_debt = $client->debt - $request->amount;
        $client->update(['debt' => $updated_debt]);

    }
}
