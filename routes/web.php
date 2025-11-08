<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\KasirController;
use App\Http\Controllers\CustomerPembayaranController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\BahanBakuController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CustomerDisplayController;
use App\Http\Controllers\EspressoController;

use Illuminate\Support\Facades\Auth;

// Laporan
use App\Http\Controllers\Reports\KasirReportController;
use App\Http\Controllers\Reports\OwnerReportController;

Route::get('/', function () {
    return Auth::check()
        ? to_route('dashboard')
        : to_route('login');
});

// ===== Guest only =====
Route::middleware('guest')->group(function () {
    Route::get('/login',    [LoginController::class, 'showLogin'])->name('login');
    Route::post('/login',   [LoginController::class, 'login'])->name('login.store');

    Route::get('/register', [LoginController::class, 'showRegister'])->name('register.show');
    Route::post('/register',[LoginController::class, 'register'])->name('register.store');
});

// ===== Auth only =====
Route::middleware('auth')->group(function () {
    Route::get('/dashboard_admin', fn() => view('tampilan.dashboard_admin'))->name('dashboard_admin');

    Route::get('/dashboard', [DashboardController::class,'index'])->name('dashboard');
    Route::get('/notifications', [DashboardController::class,'notifications'])->name('notifications');

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Kasir POS
    Route::get('/kasir', [KasirController::class, 'index'])->name('kasir.index');
    Route::post('/kasir', [KasirController::class, 'prosesForm'])->name('kasir.store');
    Route::get('/kasir/cart',            [KasirController::class, 'dataKeranjang'])->name('kasir.cart.data');
    Route::post('/kasir/cart/tambah',    [KasirController::class, 'tambahKeKeranjang'])->name('kasir.cart.tambah');
    Route::post('/kasir/cart/kurang',    [KasirController::class, 'kurangKeranjang'])->name('kasir.cart.kurang');
    Route::delete('/kasir/cart/hapus',   [KasirController::class, 'hapusDariKeranjang'])->name('kasir.cart.hapus');
    Route::post('/kasir/cart/kosongkan', [KasirController::class, 'kosongkanKeranjang'])->name('kasir.cart.kosongkan');

    // 🔄 route status baru berbasis kode penjualan
    Route::get('/kasir/status/{kode}',  [KasirController::class, 'statusPenjualan'])->name('kasir.status');

    // (Opsional) route lama show order masih ada, tapi FE sudah tidak memakainya
    Route::get('/kasir/orders/{order}',  [KasirController::class, 'show'])->name('kasir.orders.show');

    // Laporan (Kasir)
    Route::get('/reports/kasir',     [KasirReportController::class, 'index'])->name('kasir.rekap');
    Route::get('/reports/kasir/pdf', [KasirReportController::class, 'pdf'])->name('kasir.rekap.pdf');

    // Owner only
    Route::middleware('owner')->group(function () {
        Route::resource('product', ProductController::class)->except(['show']);
        Route::get('/product/destroy/{id}', [ProductController::class, 'destroy'])->name('product.delete');
        Route::get('/product/search', [ProductController::class, 'search'])->name('product.search');

        // Laporan (Owner)
        Route::get('/reports/owner/laba-rugi',     [OwnerReportController::class, 'index'])->name('owner.labarugi');
        Route::get('/reports/owner/laba-rugi/pdf', [OwnerReportController::class, 'pdf'])->name('owner.labarugi.pdf');
    });
});

// Layar customer publik
Route::get('/pembayaran', [CustomerPembayaranController::class, 'layar'])->name('customer.pembayaran.live');
Route::get('/public/display/{code}', [CustomerPembayaranController::class, 'dataDisplay']);
Route::get('/public/order/{orderNo}', [CustomerPembayaranController::class, 'dataPublik']);

Route::prefix('app')->middleware('auth')->group(function () {
    Route::resource('bahan-baku', BahanBakuController::class)
        ->only(['index','create','store','edit','update','destroy'])
        ->names('bahan-baku');
});

// routes/web.php
Route::get('/promo-display', fn () => view('promo.display'))->name('promo.display');

// espressooo ilustration
Route::get('/barista/espresso', [EspressoController::class, 'index'])->name('espresso.index');
Route::get('/barista/espresso/data', [EspressoController::class, 'getData'])->name('espresso.data');
Route::get('/barista/espresso/preview', [EspressoController::class, 'preview'])->name('espresso.preview');
Route::get('/display/espresso', [EspressoController::class, 'screen'])->name('espresso.screen');
