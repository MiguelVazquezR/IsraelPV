<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\GlobalPayment;
use Carbon\Carbon;
use Illuminate\Http\Request;

class GlobalPaymentController extends Controller
{
    public function index()
    {
        $global_payments = GlobalPayment::with('client:id,name')->latest()->get()->take(20);
        $clients = Client::all(['id', 'name']);
        $total_global_payments = GlobalPayment::all()->count();

        return inertia('GlobalPayment/Index', compact('global_payments', 'clients', 'total_global_payments'));
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function show(GlobalPayment $global_payament)
    {
        //
    }

    public function edit(GlobalPayment $global_payament)
    {
        //
    }

    public function update(Request $request, GlobalPayment $global_payament)
    {
        //
    }

    public function destroy(GlobalPayment $global_payament)
    {
        $global_payament->delete();
    }

    public function getItemsByPage($currentPage)
    {
        $offset = $currentPage * 20;
        $sales = GlobalPayment::with('client:id,name')->latest()
            ->skip($offset)
            ->take(20)
            ->get();

        return response()->json(['items' => $sales]);
    }

    public function searchPayments(Request $request)
    {
        $queryDate = $request->input('queryDate');
        $queryClient = $request->input('queryClient');

        $globalPaymentsQuery = GlobalPayment::with('client:id,name');

        // Filtrar por rango de fechas si se proporciona
        if (!empty($queryDate) && count($queryDate) === 2) {
            $startDate = Carbon::parse($queryDate[0])->startOfDay();
            $endDate = Carbon::parse($queryDate[1])->endOfDay();
            $globalPaymentsQuery->whereBetween('created_at', [$startDate, $endDate]);
        }

        // Filtrar por cliente si se proporciona
        if (!empty($queryClient)) {
            $globalPaymentsQuery->where('client_id', $queryClient);
        }

        // Realizar la consulta y devolver los resultados
        $global_payments = $globalPaymentsQuery->take(20)->get();

        return response()->json(['items' => $global_payments]);
    }
}
