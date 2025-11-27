<?php

namespace App\Http\Controllers;

use App\Http\Resources\ClientResource;
use App\Models\Client;
use App\Models\GlobalPayment;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache; // Importante para el bloqueo

class ClientController extends Controller
{

    public function index()
    {
        $clients = ClientResource::collection(Client::latest()->take(50)->get());
        $total_clients = Client::count();

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
    }


    public function show(Client $client)
    {   
        // OPTIMIZACIÓN: Eager Loading completo. 
        // Cargamos las ventas Y sus relaciones (productos, pagos) de una vez.
        // Así el frontend no tiene que hacer NINGUNA petición extra.
        $client->load([
            'sales' => function($query) {
                $query->latest()->limit(10)
                      ->with(['products', 'payments']); // Carga productos y pagos de cada venta
            }
        ]);
        
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
        $sales = Sale::where('client_id', $client->id)
            ->where('has_credit', 1)
            ->whereNull('paid_at')
            ->get();

        $pendentAmount = 0;

        foreach ($sales as $sale) {
            $payment = Payment::where('sale_id', $sale->id)->sum('amount');
            $pendentAmount += $sale->total - $payment;
        }

        return response()->json(['item' => $pendentAmount]);
    }

    public function search(Request $request)
    {
        $query = $request->input('query');

        $clients = ClientResource::collection(Client::where('name', 'like', "%$query%")
            ->orWhere('phone', 'like', "%$query%")
            ->take(50)
            ->get());

        return response()->json(['items' => $clients]);
    }


    public function getItemsByPage($currentPage)
    {
        $offset = $currentPage * 50;
        $clients = ClientResource::collection(Client::latest()
            ->skip($offset)
            ->take(50)
            ->get());

        return response()->json(['items' => $clients]);
    }

    public function storeDebt(Request $request)
    {
        // PROTECCIÓN: Evitar duplicados usando Cache Lock (atomic lock)
        // Bloquea peticiones con el mismo client_id y monto por 5 segundos
        $lockKey = 'store_debt_' . $request->client_id . '_' . $request->total;

        // Intentar obtener el bloqueo por 5 segundos
        if (Cache::lock($lockKey, 5)->get()) {
            try {
                $request->validate([
                    'total' => 'required|numeric|min:0|max:999999'
                ]);
        
                $client = Client::where('id', $request->client_id)->first();
                $updated_debt = $client->debt + $request->total;
                $client->update(['debt' => $updated_debt]);
            } finally {
                // Liberar bloqueo es opcional si queremos permitir rápido, 
                // pero mejor dejar que expire para evitar doble click humano
                // Cache::lock($lockKey)->release(); 
            }
        } else {
            // Si está bloqueado, abortamos silenciosamente o retornamos error
            return abort(429, 'Demasiadas peticiones. Espere un momento.');
        }
    }

    public function storePayment(Request $request)
    {
        // PROTECCIÓN CONTRA DUPLICADOS
        // Generamos una llave única basada en cliente, monto y fecha (y tal vez user_id si tienes)
        $lockKey = 'payment_client_' . $request->client_id . '_' . $request->amount . '_' . $request->date;

        // El bloqueo dura 5 segundos. Si llega otra petición igual en ese tiempo, falla.
        if (Cache::lock($lockKey, 5)->get()) {
            try {
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
                // Evitar deuda negativa si es posible, aunque la validación está en front
                $client->update(['debt' => $updated_debt]);

            } finally {
                // Dejamos que el lock expire solo
            }
        } else {
             return abort(429, 'Procesando pago anterior. Espere.');
        }

    }
}