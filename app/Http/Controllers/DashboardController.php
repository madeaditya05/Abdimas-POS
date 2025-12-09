<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Produk;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

use App\Models\StokMutasi;
use App\Models\BahanBaku;

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

        // ---- KPI lain (masih sama)
        $salesToday = \App\Models\Order::where('status','paid')
            ->whereDate('created_at', now())
            ->sum('grand_total');

        $newOrders = \App\Models\Order::where('status','pending')->count();
        $newCustomers = 12;

        // ===============================
        //   Peringatan Stok (pakai stok_mutasi + min_stock bahan baku)
        // ===============================

        // Hitung stok current per bahan dari stok_mutasi (IN - OUT)
        $stokPerBahan = StokMutasi::select(
                'bahan_baku_id',
                DB::raw("SUM(CASE WHEN tipe = 'in'  THEN qty ELSE 0 END) as total_in"),
                DB::raw("SUM(CASE WHEN tipe = 'out' THEN qty ELSE 0 END) as total_out")
            )
            ->groupBy('bahan_baku_id')
            ->get()
            ->mapWithKeys(function ($row) {
                $stok = (float) $row->total_in - (float) $row->total_out;
                return [$row->bahan_baku_id => $stok];
            });

        // Ambil semua bahan baku (atau boleh difilter aktif saja kalau mau)
        $allBahan = BahanBaku::all();

        // Tentukan mana yang stoknya sudah di bawah min_stock
        $lowStockItems = $allBahan->filter(function ($bahan) use ($stokPerBahan) {
                // stok real dari mutasi, default 0 kalau belum pernah ada mutasi
                $currentStock = $stokPerBahan[$bahan->id] ?? 0.0;

                // override property stok di instance ini (hanya buat tampilan, tidak di-save)
                $bahan->stok = $currentStock;

                $min = (float) ($bahan->min_stock ?? 0);

                // kalau min_stock <= 0, anggap tidak dipantau
                if ($min <= 0) {
                    return false;
                }

                return $currentStock <= $min;
            })
            ->values(); // reset index

        $lowStockCount = $lowStockItems->count();

        return view('tampilan.dashboard', compact(
            'salesToday',
            'newOrders',
            'lowStockCount',
            'newCustomers',
            'weekly',
            'monthly',
            'yearly',
            'topProductsChart',
            'lowStockItems'
        ));
    }

    // Endpoint JSON untuk tombol lonceng
    public function notifications()
    {
        // batas stok rendah global (boleh kamu ubah: 5, 10, dll)
        $minThreshold = 10;

        // 1) Hitung stok current per bahan dari tabel stok_mutasi (IN - OUT)
        $stokPerBahan = StokMutasi::select(
                'bahan_baku_id',
                DB::raw("SUM(CASE WHEN tipe = 'in'  THEN qty ELSE 0 END) as total_in"),
                DB::raw("SUM(CASE WHEN tipe = 'out' THEN qty ELSE 0 END) as total_out")
            )
            ->groupBy('bahan_baku_id')
            ->get()
            ->mapWithKeys(function ($row) {
                $stok = (float) $row->total_in - (float) $row->total_out;
                return [$row->bahan_baku_id => $stok];
            });

        // Ambil data nama/kode bahan untuk ditampilkan di notif
        $bahan = BahanBaku::whereIn('id', $stokPerBahan->keys())
            ->get()
            ->keyBy('id');

        $items = collect();

        // 2) Notif untuk stok habis / rendah
        foreach ($stokPerBahan as $bahanId => $stok) {
            $row = $bahan->get($bahanId);
            if (! $row) {
                continue;
            }

            if ($stok <= 0) {
                $items->push([
                    'title'    => 'Stok Habis',
                    'subtitle' => "{$row->nama_bahan} — {$stok} (<= 0)",
                    'color'    => '#ef4444', // merah
                ]);
            } elseif ($stok <= $minThreshold) {
                $items->push([
                    'title'    => 'Stok Rendah',
                    'subtitle' => "{$row->nama_bahan} — {$stok} (<= {$minThreshold})",
                    'color'    => '#f59e0b', // oranye
                ]);
            }
        }

        // 3) Notif khusus setiap ada penyesuaian stok
        $adjustments = StokMutasi::with('bahan')
            ->where('sumber_type', StokMutasi::SUMBER_PENYESUAIAN)
            ->orderByDesc('tanggal')
            ->limit(5)
            ->get();

        foreach ($adjustments as $adj) {
            $nama = $adj->bahan->nama_bahan ?? 'Bahan tidak diketahui';
            $sign = $adj->tipe === 'in' ? '+' : '-';
            $qty  = (float) $adj->qty;
            $tgl  = optional($adj->tanggal)->format('d M Y');

            $items->push([
                'title'    => 'Penyesuaian Stok',
                'subtitle' => "{$nama} {$sign}{$qty} ({$tgl})",
                'color'    => '#3b82f6', // biru
            ]);
        }

        // 4) Balikin JSON ke front-end
        return response()->json([
            'count' => $items->count(),
            'items' => $items->take(50)->values(), // batasi max 50 item
        ]);
    }
}
