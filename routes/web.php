<?php

use App\Http\Controllers\FarmerController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => view('dashboard'))->name('dashboard');

Route::resource('farmers', FarmerController::class)
    ->only(['index', 'store', 'update', 'destroy']);