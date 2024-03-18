<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductHistoryResource;
use App\Models\Product;
use App\Models\ProductHistory;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ProductHistoryController extends Controller
{
    
    public function index()
    {
        $history = ProductHistoryResource::collection(ProductHistory::latest()->with('product:id,name,cost')->get()->take(20));
        $total_histories = ProductHistory::all()->count();
        $products = Product::all(['id', 'name']);

        // return $history;
        return inertia('ProductHistory/Index', compact('history', 'total_histories', 'products'));
    }

    
    public function create()
    {
        //
    }

    
    public function store(Request $request)
    {
        //
    }

    
    public function show(ProductHistory $product_history)
    {
        //
    }

    
    public function edit(ProductHistory $product_history)
    {
        //
    }

    
    public function update(Request $request, ProductHistory $product_history)
    {
        //
    }

    
    public function destroy(ProductHistory $product_history)
    {
        $product_history->delete();
    }


    public function getItemsByPage($currentPage)
    {
        $offset = $currentPage * 20;
        $histories = ProductHistoryResource::collection(ProductHistory::latest()
            ->with('product:id,name,cost')
            ->skip($offset)
            ->take(20)
            ->get());

        return response()->json(['items' => $histories]);
    }


    public function filterHistory(Request $request)
    {
        $queryDate = $request->input('queryDate');
        $queryProduct = $request->input('queryProduct');

        $history = ProductHistory::with('product');

        // Filtrar por rango de fechas si se proporciona
        if (!empty($queryDate) && count($queryDate) === 2) {
            $startDate = Carbon::parse($queryDate[0])->startOfDay();
            $endDate = Carbon::parse($queryDate[1])->endOfDay();
            $history->whereBetween('created_at', [$startDate, $endDate]);
        }

        // Filtrar por producto si se proporciona
        if (!empty($queryProduct)) {
            $history->where('product_id', $queryProduct);
        }

        // Realizar la consulta y devolver los resultados
        $histories = ProductHistoryResource::collection($history->take(20)->get());

        return response()->json(['items' => $histories]);
    }


     // API
     public function getById(ProductHistory $product_history)
     {
 
         $history = ProductHistoryResource::make(ProductHistory::with(['product:id,name,cost'])->find($product_history->id));
 
         return response()->json(compact('history'));
     }


     public function fetch()
     {
 
        $histories = ProductHistoryResource::collection(ProductHistory::latest()->with('product:id,name,cost')->get()->take(20));
 
        return response()->json(compact('histories'));
     }
}
