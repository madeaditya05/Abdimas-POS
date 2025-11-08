<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CustomerDisplayController extends Controller
{
    public function index()
    {
        // ==== DETEKSI NAMA TABEL ====
        $orderTable      = Schema::hasTable('order')       ? 'order'       : (Schema::hasTable('orders') ? 'orders' : null);
        $orderItemTable  = Schema::hasTable('order_item')  ? 'order_item'  : (Schema::hasTable('order_items') ? 'order_items' : null);
        $productTable    = Schema::hasTable('product')     ? 'product'     : (Schema::hasTable('products') ? 'products' : null);

        if (!$orderTable || !$orderItemTable || !$productTable) {
            abort(500, 'Tabel wajib tidak ditemukan. Pastikan ada: order/orders, order_item/order_items, dan product/products.');
        }

        // ==== DETEKSI KOLOM DI TABEL ORDER ====
        $orderDateCol = collect(['created_at','order_date','tanggal','date'])
            ->first(fn($c) => Schema::hasColumn($orderTable, $c)) ?? 'created_at';

        $orderTotalCol = collect(['grand_total','total_amount','total','amount','net_total'])
            ->first(fn($c) => Schema::hasColumn($orderTable, $c)) ?? 'grand_total';

        // ==== DETEKSI KOLOM DI TABEL ORDER_ITEM ====
        $qtyCol       = collect(['qty','quantity','qty_order','jumlah'])
            ->first(fn($c) => Schema::hasColumn($orderItemTable, $c)) ?? 'qty';

        $prodIdCol    = collect(['product_id','produk_id','id_product'])
            ->first(fn($c) => Schema::hasColumn($orderItemTable, $c)) ?? 'product_id';

        // ==== DETEKSI KOLOM DI TABEL PRODUCT ====
        $productIdCol   = Schema::hasColumn($productTable, 'id') ? 'id' : (Schema::hasColumn($productTable, 'product_id') ? 'product_id' : 'id');
        $productNameCol = collect(['name','product_name','nama','nama_produk'])
            ->first(fn($c) => Schema::hasColumn($productTable, $c)) ?? 'name';

        // ==== QUERY TOTAL PENJUALAN HARI INI ====
        $salesToday = DB::table($orderTable)
            ->whereDate($orderDateCol, now()->toDateString())
            ->sum($orderTotalCol);

        // ==== QUERY PRODUK TERLARIS ====
        $topProducts = DB::table($orderItemTable)
            ->join($productTable, $orderItemTable.'.'.$prodIdCol, '=', $productTable.'.'.$productIdCol)
            ->select($productTable.'.'.$productNameCol.' as name', DB::raw('SUM('.$orderItemTable.'.'.$qtyCol.') as qty'))
            ->groupBy('name')
            ->orderByDesc('qty')
            ->take(5)
            ->get();

        return view('customer.display', compact('salesToday', 'topProducts'));
    }
}
