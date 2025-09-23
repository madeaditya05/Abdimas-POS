<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PenjualanPdfController;

Route::get('/', function () {
    return view('welcome');
});


Route::middleware('auth')
    ->get('/tickets/penjualan/{penjualan}', [PenjualanPdfController::class, 'show'])
    ->name('tickets.penjualan');