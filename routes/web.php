<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::middleware('auth.filament')
    ->group(function() {
        Route::get('/daily', \App\Livewire\CreateDailyBudget::class);
        Route::get('/exchange', \App\Livewire\CreateExchange::class);
    });

