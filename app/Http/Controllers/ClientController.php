<?php

namespace App\Http\Controllers;

use App\Http\Resources\ClientResource;
use App\Models\Client;
use App\Models\Payment;
use App\Models\Sale;
use Illuminate\Http\Request;

class ClientController extends Controller
{

    public function index()
    {
        $clients = ClientResource::collection(Client::get()->take(20));

        // return $clients;
        return inertia('Client/Index', compact('clients'));
    }


    public function create()
    {
        return inertia('Client/Create');
    }


    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'rfc' => 'nullable|string|max:100',
            'phone' => 'required|string|min:8|max:13',
            'address' => 'nullable|string',
        ]);

        $client = Client::create($request->all());

        // return to_route('clients.show', $client->id);
    }


    public function show(Client $client)
    {
        $client = $client->load(['sales']);
        $clients = Client::all(['id', 'name']);

        return inertia('Client/Show', compact('client', 'clients'));
    }


    public function edit(Client $client)
    {
        return inertia('Client/Edit', compact('client'));
    }


    public function update(Request $request, Client $client)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'rfc' => 'nullable|string|max:100',
            'phone' => 'required|string|min:8|max:13',
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
}
