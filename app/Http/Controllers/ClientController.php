<?php

namespace App\Http\Controllers;

use App\Http\Resources\ClientResource;
use App\Models\Client;
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
        $clients = Client::all(['id','name']);
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
}
