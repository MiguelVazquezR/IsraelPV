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
        // CAMBIO: Aumentado de 20 a 100 para optimizar la vista general
        $global_payments = GlobalPayment::with('client:id,name')->latest()->get()->take(100);
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
        // CAMBIO: Paginación de 100 en 100
        $itemsPerPage = 100;
        $offset = $currentPage * $itemsPerPage;
        
        $sales = GlobalPayment::with('client:id,name')->latest()
            ->skip($offset)
            ->take($itemsPerPage)
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

        // Realizar la consulta y devolver los resultados (limitado a 100 en búsqueda también)
        $global_payments = $globalPaymentsQuery->take(100)->get();

        return response()->json(['items' => $global_payments]);
    }
}