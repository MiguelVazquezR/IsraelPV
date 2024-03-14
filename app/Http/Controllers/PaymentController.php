<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Sale;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    
    public function index()
    {
        //
    }

    
    public function create()
    {
        //
    }

    
    public function store(Request $request)
    {
        // $request->validate([
        //     'sale_id' => 'required',
        //     'amount' => 'required',
        //     'notes' => 'nullable',
        //     'date' => 'required',
        // ]);

        $sale = Sale::find($request->sale_id); //recupero la venta
        $total_sale = $sale->total; //guardo el total de venta

        // return $total_sale;
        Payment::create([
            'amount' => $request->amount,
            'notes' => $request->notes,
            'sale_id' => $request->sale_id,
            'created_at' => $request->date,
        ]);

        $total_payments = Payment::where('sale_id', $request->sale_id)->sum('amount');


        if ( ($total_sale - $total_payments) == 0) {
            $sale->update([
                'paid_at' => now()
            ]);
        }
    }

    
    public function show(Payment $payment)
    {
        //
    }

    
    public function edit(Payment $payment)
    {
        //
    }

    
    public function update(Request $request, Payment $payment)
    {
        //
    }

    
    public function destroy(Payment $payment)
    {
        //
    }
}
