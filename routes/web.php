<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KasirController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\CustomerPembayaranController;
use App\Http\Controllers\CashReconciliationController;
use App\Http\Controllers\PanelController;

use App\Http\Controllers\BahanBakuController;
use App\Http\Controllers\PembelianBahanController;
use App\Http\Controllers\PembelianBahanDetailController;
use App\Http\Controllers\StokMutasiController;
use App\Http\Controllers\PenyesuaianStokController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\KategoriProdukController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ChartOfAccountController;
use App\Http\Controllers\BebanOperasionalController;
use App\Http\Controllers\LaporanJurnalController;
use App\Http\Controllers\EspressoController;

use App\Http\Controllers\Reports\KasirReportController;
use App\Http\Controllers\Reports\OwnerReportController;
use App\Http\Controllers\Reports\OwnerClosingController;

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
    Route::get('/dashboard', [DashboardController::class,'index'])->name('dashboard');
    Route::get('/notifications', [DashboardController::class,'notifications'])->name('notifications');

    // Switch panel (Owner/Kasir) via session
    Route::get('/panel/{panel}', [PanelController::class, 'switch'])->name('panel.switch');

    // Logout wajib rekonsiliasi kas
    Route::get('/logout/reconcile', [CashReconciliationController::class, 'show'])->name('logout.reconcile');
    Route::post('/logout/reconcile', [CashReconciliationController::class, 'store'])->name('logout.reconcile.store');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Kasir POS
    Route::get('/kasir', [KasirController::class, 'index'])->name('kasir.index');
    Route::post('/kasir', [KasirController::class, 'prosesForm'])->name('kasir.store');
    Route::get('/kasir/invoice/{kode}', [KasirController::class, 'invoice'])->name('kasir.invoice');
    Route::get('/kasir/struk/{kode}', [KasirController::class, 'cetakStruk'])->name('kasir.struk');
    Route::post('/kasir/struk/{kode}/print', [KasirController::class, 'printStruk'])->name('kasir.struk.print');
    Route::post('/kasir/selesai-cetak/{kode}', [KasirController::class, 'selesaiCetak'])->name('kasir.selesaiCetak');
    Route::get('/kasir/customer/discount', [KasirController::class, 'customerDiscountInfo'])->name('kasir.customer.discount');

    Route::get('/kasir/cart',            [KasirController::class, 'dataKeranjang'])->name('kasir.cart.data');
    Route::post('/kasir/cart/tambah',    [KasirController::class, 'tambahKeKeranjang'])->name('kasir.cart.tambah');
    Route::post('/kasir/cart/kurang',    [KasirController::class, 'kurangKeranjang'])->name('kasir.cart.kurang');
    Route::delete('/kasir/cart/hapus',   [KasirController::class, 'hapusDariKeranjang'])->name('kasir.cart.hapus');
    Route::post('/kasir/cart/kosongkan', [KasirController::class, 'kosongkanKeranjang'])->name('kasir.cart.kosongkan');
    Route::get('/kasir/status/{kode}',   [KasirController::class, 'statusPenjualan'])->name('kasir.status');

    // Laporan (Kasir)
    Route::get('/reports/kasir',     [KasirReportController::class, 'index'])->name('kasir.rekap');
    Route::get('/reports/kasir/pdf', [KasirReportController::class, 'pdf'])->name('kasir.rekap.pdf');

    // Laporan (Owner)
    Route::get('/reports/owner', [OwnerReportController::class, 'menu'])->name('owner.reports.menu');
    Route::get('/reports/owner/laba-rugi',     [OwnerReportController::class, 'index'])->name('owner.labarugi');
    Route::get('/reports/owner/laba-rugi/pdf', [OwnerReportController::class, 'pdf'])->name('owner.labarugi.pdf');

    // Invoice / Piutang Tempo
    Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::patch('/invoices/{invoice}/lunas', [InvoiceController::class, 'updateStatusLunas'])
        ->name('invoices.updateStatusLunas');

    // Tutup Buku (Owner)
    Route::get('/reports/owner/closing', [OwnerClosingController::class, 'index'])->name('owner.tutupbuku');
    Route::post('/reports/owner/closing', [OwnerClosingController::class, 'close'])->name('owner.tutupbuku.close');

    // Manajemen (Owner)
    Route::prefix('app')->group(function () {
        Route::resource('bahan-baku', BahanBakuController::class)
            ->only(['index','create','store','edit','update','destroy'])
            ->names('bahan-baku');

        Route::resource('pembelian-bahan', PembelianBahanController::class)
            ->only(['index','create','store','edit','update','destroy'])
            ->names('pembelian-bahan');

        Route::resource('produk', ProdukController::class)
            ->only(['index','create','store','edit','update','destroy'])
            ->names('produk');
        Route::patch('produk/{produk}/toggle-status', [ProdukController::class, 'toggleStatus'])
            ->name('produk.toggle-status');

        Route::resource('kategori-produk', KategoriProdukController::class)
            ->only(['index','create','store','edit','update','destroy'])
            ->names('kategori-produk');

        Route::get('pembelian-bahan-detail', [PembelianBahanDetailController::class, 'index'])
            ->name('pembelian-bahan-detail.index');

        Route::resource('mutasi-stok', StokMutasiController::class)
            ->only(['index'])
            ->names('mutasi-stok');

        Route::resource('chart-of-accounts', ChartOfAccountController::class)
            ->only(['index','create','store','edit','update','destroy'])
            ->names('chart-of-accounts');

        Route::resource('penyesuaian-stok', PenyesuaianStokController::class)
            ->only(['index', 'create', 'store'])
            ->names('penyesuaian-stok');

        Route::resource('customer', CustomerController::class)
            ->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])
            ->names('customer');

        // Beban Operasional
        Route::get('beban-operasional', [BebanOperasionalController::class, 'index'])->name('beban-operasional.index');
        Route::get('beban-operasional/create', [BebanOperasionalController::class, 'create'])->name('beban-operasional.create');
        Route::post('beban-operasional', [BebanOperasionalController::class, 'store'])->name('beban-operasional.store');
        Route::delete('beban-operasional/{id}', [BebanOperasionalController::class, 'destroy'])->name('beban-operasional.destroy');

        // Laporan Jurnal (read-only)
        Route::get('laporan/jurnal', [LaporanJurnalController::class, 'index'])->name('laporan.jurnal.index');
        Route::get('laporan/jurnal/{id}', [LaporanJurnalController::class, 'show'])->name('laporan.jurnal.show');
        Route::get('laporan/jurnal-lines', [LaporanJurnalController::class, 'lines'])->name('laporan.jurnal.lines');
    });

    // Promo display (internal)
    Route::get('/promo-display', fn () => view('promo.display'))->name('promo.display');

    // Espresso display (internal)
    Route::get('/barista/espresso', [EspressoController::class, 'index'])->name('espresso.index');
    Route::get('/barista/espresso/data', [EspressoController::class, 'getData'])->name('espresso.data');
    Route::get('/barista/espresso/preview', [EspressoController::class, 'preview'])->name('espresso.preview');
    Route::get('/display/espresso', [EspressoController::class, 'screen'])->name('espresso.screen');
});

// ===== Public customer screen =====
Route::get('/pembayaran', [CustomerPembayaranController::class, 'layar'])->name('customer.pembayaran.live');
Route::get('/public/display/{code}', [CustomerPembayaranController::class, 'dataDisplay']);
