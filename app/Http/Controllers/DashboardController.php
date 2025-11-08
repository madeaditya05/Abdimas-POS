<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
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
        $topProductsChart = \App\Models\OrderItem::select('product_id',
                \DB::raw('SUM(qty) as sold'))
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
        $lowStockCount = \App\Models\Product::whereColumn('stock','<=','min_stock')->count();
        $newCustomers = 12;
        $lowStockItems = \App\Models\Product::whereColumn('stock','<=','min_stock')->get();

        return view('tampilan.dashboard', compact(
            'salesToday', 'newOrders', 'lowStockCount', 'newCustomers',
            'weekly', 'monthly', 'yearly', 'topProductsChart', 'lowStockItems'
        ));
    }

    // Endpoint JSON untuk tombol lonceng
    public function notifications()
    {
        $items = Product::where(function($q){
                $q->whereColumn('stock','<=','min_stock')
                  ->orWhere('stock','<=',0);
            })
            ->orderBy('stock')
            ->get(['id','name','stock','min_stock','unit']);

        return response()->json([
            'count' => $items->count(),
            'items' => $items->map(fn($p)=>[
                'title' => $p->stock <= 0 ? 'Stok Habis' : 'Stok Rendah',
                'subtitle' => "{$p->name} — {$p->stock} ".($p->unit ?? '')." (min {$p->min_stock})",
                'color' => $p->stock <= 0 ? '#ef4444' : '#f59e0b',
            ]),
        ]);
    }
}
