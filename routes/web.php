<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GlobalPaymentController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductHistoryController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UserController;
use App\Models\Client;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return Inertia::render('Welcome', [
//         'canLogin' => Route::has('login'),
//         'canRegister' => Route::has('register'),
//         'laravelVersion' => Application::VERSION,
//         'phpVersion' => PHP_VERSION,
//     ]);
// });

Route::redirect('/', 'login');

//Dashboard routes----------------------------------------------------------------------------------
//-------------------------------------------------------------------------------------------------
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard-get-day-data', [DashboardController::class, 'getDayData'])->name('dashboard.get-day-data');
    Route::get('/dashboard-get-week-data', [DashboardController::class, 'getWeekData'])->name('dashboard.get-week-data');
    Route::get('/dashboard-get-month-data', [DashboardController::class, 'getMonthData'])->name('dashboard.get-month-data');
});

//sales routes-------------------------------------------------------------------------------------
//-------------------------------------------------------------------------------------------------
Route::resource('sales', SaleController::class)->middleware('auth');
Route::get('sales-search', [SaleController::class, 'searchProduct'])->name('sales.search')->middleware('auth');
Route::get('sales-get-by-id/{sale}', [SaleController::class, 'getById'])->middleware('auth')->name('sales.get-by-id');
Route::post('sales-get-by-ids', [SaleController::class, 'getByIds'])->middleware('auth')->name('sales.get-by-ids');
Route::get('sales-point', [SaleController::class, 'pointIndex'])->name('sales.point')->middleware('auth');
Route::get('sales-get-by-page/{currentPage}', [SaleController::class, 'getItemsByPage'])->name('sales.get-by-page')->middleware('auth');
Route::get('sales-get-by-id/{sale}', [SaleController::class, 'getById'])->middleware('auth')->name('sales.get-by-id');
Route::get('sales-get-by-id-copy/{sale}', [SaleController::class, 'getByIdCopy'])->middleware('auth')->name('sales.get-by-id-copy'); //es lo mismo que la de arriba pero con resource
Route::get('sales-print-ticket/{sale_id}', [SaleController::class, 'printTicket'])->middleware('auth')->name('sales.print-ticket');
Route::get('sales-print-payment-ticket/{client_id}', [SaleController::class, 'printPaymentTicket'])->middleware('auth')->name('sales.print-payment-ticket');


//products routes-------------------------------------------------------------------------------------
//----------------------------------------------------------------------------------------------------
Route::resource('products', ProductController::class)->middleware('auth');
Route::post('products/update-with-media/{product}', [ProductController::class, 'updateWithMedia'])->name('products.update-with-media')->middleware('auth');
Route::put('products-entry/{product_id}', [ProductController::class, 'entryStock'])->name('products.entry')->middleware('auth');
Route::get('products-search', [ProductController::class, 'searchProduct'])->name('products.search')->middleware('auth');
Route::get('products-get-product-scaned/{product_id}', [ProductController::class, 'getProductScaned'])->name('products.get-product-scaned')->middleware('auth');
Route::get('products-fetch-history/{product_id}', [ProductController::class, 'fetchHistory'])->name('products.fetch-history')->middleware('auth');
Route::get('products-get-by-page/{currentPage}', [ProductController::class, 'getItemsByPage'])->name('products.get-by-page')->middleware('auth');


//history routes-------------------------------------------------------------------------------------
//-----------------------------------------------------------------------------------------------------
Route::resource('product-histories', ProductHistoryController::class)->middleware('auth');
Route::get('product-histories-get-by-page/{currentPage}', [ProductHistoryController::class, 'getItemsByPage'])->name('product-histories.get-by-page')->middleware('auth');
Route::get('product-histories-filter', [ProductHistoryController::class, 'filterHistory'])->name('product-histories.filter')->middleware('auth');
Route::get('product-histories-get-by-id/{product_history}', [ProductHistoryController::class, 'getById'])->middleware('auth')->name('product-histories.get-by-id');
Route::get('product-histories-fetch', [ProductHistoryController::class, 'fetch'])->middleware('auth')->name('product-histories.fetch');


//clients routes-------------------------------------------------------------------------------------
//---------------------------------------------------------------------------------------------------
Route::resource('clients', ClientController::class)->middleware('auth');
Route::get('clients-get-pendent-amount/{client}', [ClientController::class, 'getClientPendentAmount'])->name('clients.get-pendent-amount')->middleware('auth');
Route::get('clients-get-by-id/{client}', [ClientController::class, 'getById'])->middleware('auth')->name('clients.get-by-id');
Route::get('clients-get-by-page/{currentPage}', [ClientController::class, 'getItemsByPage'])->name('clients.get-by-page')->middleware('auth');
Route::get('clients-search', [ClientController::class, 'search'])->name('clients.search')->middleware('auth');
Route::post('clients-store-debt', [ClientController::class, 'storeDebt'])->name('clients.store-debt')->middleware('auth');
Route::post('clients-store-payment', [ClientController::class, 'storePayment'])->name('clients.store-payment')->middleware('auth');


//categories routes-------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------
Route::resource('categories', CategoryController::class)->middleware('auth');


//payments routes-------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------
Route::resource('payments', PaymentController::class)->middleware('auth');


//global payments routes-------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------
Route::resource('global-payments', GlobalPaymentController::class)->middleware('auth');
Route::get('global-payments-search', [GlobalPaymentController::class, 'searchPayments'])->name('global-payments.search')->middleware('auth');


//settings routes-------------------------------------------------------------------------------------
//----------------------------------------------------------------------------------------------------
Route::resource('settings', SettingController::class)->middleware('auth');

// User routes-----------------------------------------------------------------------------------------
//-----------------------------------------------------------------------------------------------------
Route::get('users-get-notifications', [UserController::class, 'getNotifications'])->middleware('auth')->name('users.get-notifications');
Route::post('users-read-notifications', [UserController::class, 'readNotifications'])->middleware('auth')->name('users.read-user-notifications');
Route::post('users-delete-notifications', [UserController::class, 'deleteNotifications'])->middleware('auth')->name('users.delete-user-notifications');


//artisan commands -------------------
Route::get('/storage-link', function () {
    Artisan::call('storage:link');
    return 'linked!.';
});

// borrar caché
// Route::get('/clear-cache', function () {
//     // Limpiar la caché de la aplicación
//     Artisan::call('cache:clear');

//     // Limpiar la caché de configuración
//     Artisan::call('config:clear');

//     // Limpiar la caché de rutas
//     Artisan::call('route:clear');

//     // Limpiar la caché de vistas
//     Artisan::call('view:clear');

//     return "Caché limpiada correctamente.";
// });

Route::get('calculate-total-client-debt', function () {
    $clients = Client::with([
        'sales' => function ($query) {
            $query->select('id', 'client_id', 'has_credit', 'total')->where('has_credit', true);
        },
        'sales.payments:id,amount,sale_id'])
        ->get(['id', 'name', 'debt']);

        $clientsWithDebt = $clients->map(function ($client) {
            $totalDebt = $client->sales->sum(function ($sale) {
                // Suma los montos de los pagos realizados para esta venta
                $paidAmount = $sale->payments->sum('amount');
                // Calcula el saldo pendiente para esta venta
                return max($sale->total - $paidAmount, 0); // Evita saldos negativos
            });

            // Actualiza el atributo `debt` en la base de datos
            $client->update(['debt' => $totalDebt]);
    
        });
    return "Deuda de clientes actualizada correctamente.";
});