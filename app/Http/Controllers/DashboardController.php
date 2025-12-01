<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Produk;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now();

        // ---- Penjualan per minggu (7 hari terakhir, per hari)
        $weekly = Order::selectRaw('DATE(created_at) as d, SUM(grand_total) as total')
            ->where('status', 'paid')
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('d')
            ->orderBy('d')
            ->pluck('total', 'd');

        // ---- Penjualan per bulan (12 bulan terakhir, per bulan)
        $monthly = Order::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as ym, SUM(grand_total) as total')
            ->where('status', 'paid')
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->groupBy('ym')
            ->orderBy('ym')
            ->pluck('total', 'ym');

        // ---- Penjualan per tahun (per tahun)
        $yearly = Order::selectRaw('YEAR(created_at) as y, SUM(grand_total) as total')
            ->where('status', 'paid')
            ->groupBy('y')
            ->orderBy('y')
            ->pluck('total', 'y');

        // ---- Pie chart produk terlaris
        $topProductsChart = OrderItem::select(
                'product_id',
                DB::raw('SUM(qty) as sold')
            )
            ->groupBy('product_id')
            ->orderByDesc('sold')
            ->limit(5)
            ->with('product:id,name')
            ->get();

        // Ambil data lain seperti sebelumnya
        $salesToday = \App\Models\Order::where('status','paid')
            ->whereDate('created_at', now())
            ->sum('grand_total');

        $newOrders = \App\Models\Order::where('status','pending')->count();
        $lowStockCount = Produk::where('stok', '<=', 0)->count();
        $newCustomers = 12;
        $lowStockItems = Produk::where('stok', '<=', 0)->get();


        return view('tampilan.dashboard', compact(
            'salesToday', 'newOrders', 'lowStockCount', 'newCustomers',
            'weekly', 'monthly', 'yearly', 'topProductsChart', 'lowStockItems'
        ));
    }

    // Endpoint JSON untuk tombol lonceng
    public function notifications()
    {
        $rows = Produk::query()
            ->select('id', 'nama_barang', 'stok', 'min_stock', 'kategori')
            // Habis atau menipis (stok <= min_stock, min_stock > 0)
            ->where(function ($q) {
                $q->whereNull('stok')->orWhere('stok', '<=', 0);
            })
            ->orWhere(function ($q) {
                $q->whereRaw('COALESCE(stok,0) <= COALESCE(min_stock,0)')
                  ->whereRaw('COALESCE(min_stock,0) > 0');
            })
            ->orderByRaw('COALESCE(stok,0) ASC')
            ->limit(50)
            ->get();

        $items = $rows->map(function ($p) {
            $stok = (int) ($p->stok ?? 0);
            $min  = (int) ($p->min_stock ?? 0);
            $habis = $stok <= 0;

            return [
                'title'    => $habis ? 'Stok Habis' : 'Stok Rendah',
                'subtitle' => "{$p->nama_barang} — {$stok} (min {$min})",
                'color'    => $habis ? '#ef4444' : '#f59e0b', // merah / oranye
            ];
        });

        return response()->json([
            'count' => $items->count(),
            'items' => $items->values(),
        ]);
    }
}
