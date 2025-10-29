<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\KasirController;
use App\Http\Controllers\CustomerPembayaranController;
use App\Http\Controllers\ProductController;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

// ====== Guest only ======
Route::middleware('guest')->group(function () {
    Route::get('/login',    [LoginController::class, 'showLogin'])->name('login');
    Route::post('/login',   [LoginController::class, 'login'])->name('login.store');

    Route::get('/register', [LoginController::class, 'showRegister'])->name('register.show');
    Route::post('/register',[LoginController::class, 'register'])->name('register.store');
});

// ====== Auth only ======
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', fn() => view('tampilan.dashboard'))->name('dashboard');
    Route::get('/dashboard_admin', fn() => view('tampilan.dashboard_admin'))->name('dashboard_admin');

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Kasir & API keranjang kamu taruh di sini seperti sebelumnya...
    Route::get('/kasir', [KasirController::class, 'index'])->name('kasir.index');
    Route::post('/kasir', [KasirController::class, 'prosesForm'])->name('kasir.store');
    Route::get('/kasir/cart',            [KasirController::class, 'dataKeranjang'])->name('kasir.cart.data');
    Route::post('/kasir/cart/tambah',    [KasirController::class, 'tambahKeKeranjang'])->name('kasir.cart.tambah');
    Route::post('/kasir/cart/kurang',    [KasirController::class, 'kurangKeranjang'])->name('kasir.cart.kurang');
    Route::delete('/kasir/cart/hapus',   [KasirController::class, 'hapusDariKeranjang'])->name('kasir.cart.hapus');
    Route::post('/kasir/cart/kosongkan', [KasirController::class, 'kosongkanKeranjang'])->name('kasir.cart.kosongkan');
    Route::get('/kasir/orders/{order}',  [KasirController::class, 'show'])->name('kasir.orders.show');

    Route::middleware('owner')->group(function () {
        Route::resource('product', ProductController::class);
        // (opsi tombol hapus via GET + modal seperti supplier/karyawan)
        Route::get('/product/destroy/{id}', [ProductController::class, 'destroy'])->name('product.destroy');
    
    });
});



// Layar customer publik (opsional tanpa auth)
Route::get('/pembayaran', [CustomerPembayaranController::class, 'layar'])->name('customer.pembayaran.live');
Route::get('/public/display/{code}', [CustomerPembayaranController::class, 'dataDisplay']);
Route::get('/public/order/{orderNo}', [CustomerPembayaranController::class, 'dataPublik']);
