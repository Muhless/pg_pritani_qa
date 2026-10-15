<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\FarmerController;
use App\Http\Controllers\ItemTypeController;
use App\Http\Controllers\MillingProcessController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\StockController;
use Illuminate\Support\Facades\Route;


Route::get('/', fn() => view('dashboard'))->name('dashboard');

Route::resource('farmers', FarmerController::class)
    ->only(['index', 'store', 'update', 'destroy']);

Route::resource('customers', CustomerController::class)
    ->only(['index', 'store', 'update', 'destroy']);

Route::resource('item-types', ItemTypeController::class)
    ->only(['index', 'store', 'update', 'destroy']);
Route::resource('purchases', PurchaseController::class)
    ->only(['index', 'store', 'destroy']);
Route::resource('milling', MillingProcessController::class)
    ->parameters(['milling' => 'millingProcess'])
    ->only(['index', 'store', 'destroy']);


Route::get('stocks', [StockController::class, 'index'])->name('stocks.index');

Route::resource('sales', SaleController::class)
    ->only(['index', 'store', 'destroy']);

Route::patch('sales/{sale}/pay', [SaleController::class, 'markPaid'])->name('sales.pay');