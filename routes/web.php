<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FarmerController;
use App\Http\Controllers\ItemTypeController;
use App\Http\Controllers\MillingProcessController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\StockController;
use App\Http\Middleware\EnsureModuleAccess;
use App\Models\Customer;
use App\Models\ItemType;
use App\Models\Stock;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::middleware(EnsureModuleAccess::class . ':master')->group(function () {
        Route::resource('farmers', FarmerController::class)
            ->only(['index', 'store', 'update', 'destroy']);

        Route::resource('customers', CustomerController::class)
            ->only(['index', 'store', 'update', 'destroy']);

        Route::resource('item-types', ItemTypeController::class)
            ->only(['index', 'store', 'update', 'destroy']);
    });

    Route::middleware(EnsureModuleAccess::class . ':purchases')->group(function () {
        Route::resource('purchases', PurchaseController::class)
            ->only(['index', 'store', 'destroy']);
    });

    Route::middleware(EnsureModuleAccess::class . ':milling')->group(function () {
        Route::resource('milling', MillingProcessController::class)
            ->parameters(['milling' => 'millingProcess'])
            ->only(['index', 'store', 'destroy']);
    });

    Route::middleware(EnsureModuleAccess::class . ':sales')->group(function () {
        Route::resource('sales', SaleController::class)
            ->only(['index', 'store', 'destroy']);

        Route::patch('sales/{sale}/pay', [SaleController::class, 'markPaid'])->name('sales.pay');
    });

    Route::middleware(EnsureModuleAccess::class . ':stocks')->group(function () {
        Route::get('stocks', [StockController::class, 'index'])->name('stocks.index');
        Route::get('stocks/{item}/edit', [StockController::class, 'edit'])->name('stocks.edit');
        Route::put('stocks/{item}', [StockController::class, 'update'])->name('stocks.update');
    });



});


if (app()->environment('testing')) {
    Route::post('/_test/customers', function (Request $request) {
        return Customer::create($request->validate([
            'name' => "required",
            'phone' => 'nullable',
            'address' => 'nullable'
        ]));
    })->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);


    Route::post('/_test/item-types', function (Request $request) {
        $data = $request->validate([
            'name' => 'required',
            'category' => 'required|in:grain,rice,bran,husk,broken_rice',
            'unit' => 'nullable',
            'quantity_kg' => 'nullable|numeric',
        ]);

        $item = ItemType::create([
            'name' => $data['name'],
            'category' => $data['category'],
            'unit' => $data['unit'] ?? 'kg',
        ]);

        // stocks.item_type_id UNIQUE, jadi updateOrCreate mencegah duplikat
        $stock = Stock::updateOrCreate(
            ['item_type_id' => $item->id],
            ['quantity_kg' => $data['quantity_kg'] ?? 0],
        );

        return response()->json(['item' => $item, 'stock' => $stock], 201);
    });
}