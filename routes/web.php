<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UserController;
use Illuminate\Foundation\Application;
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

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

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


//products routes-------------------------------------------------------------------------------------
//----------------------------------------------------------------------------------------------------
Route::resource('products', ProductController::class)->middleware('auth');
Route::post('products/update-with-media/{product}', [ProductController::class, 'updateWithMedia'])->name('products.update-with-media')->middleware('auth');
Route::put('products-entry/{product_id}', [ProductController::class, 'entryStock'])->name('products.entry')->middleware('auth');
Route::get('products-search', [ProductController::class, 'searchProduct'])->name('products.search')->middleware('auth');
Route::get('products-get-product-scaned/{product_id}', [ProductController::class, 'getProductScaned'])->name('products.get-product-scaned')->middleware('auth');
Route::get('products-fetch-history/{product_id}', [ProductController::class, 'fetchHistory'])->name('products.fetch-history')->middleware('auth');
Route::get('products-get-by-page/{currentPage}', [ProductController::class, 'getItemsByPage'])->name('products.get-by-page')->middleware('auth');


//clients routes-------------------------------------------------------------------------------------
//---------------------------------------------------------------------------------------------------
Route::resource('clients', ClientController::class)->middleware('auth');

//categories routes-------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------
Route::resource('categories', CategoryController::class)->middleware('auth');


//payments routes-------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------
Route::resource('payments', PaymentController::class)->middleware('auth');


//settings routes-------------------------------------------------------------------------------------
//----------------------------------------------------------------------------------------------------
Route::resource('settings', SettingController::class)->middleware('auth');

// User routes-----------------------------------------------------------------------------------------
//-----------------------------------------------------------------------------------------------------
Route::get('users-get-notifications', [UserController::class, 'getNotifications'])->middleware('auth')->name('users.get-notifications');
Route::post('users-read-notifications', [UserController::class, 'readNotifications'])->middleware('auth')->name('users.read-user-notifications');
Route::post('users-delete-notifications', [UserController::class, 'deleteNotifications'])->middleware('auth')->name('users.delete-user-notifications');
