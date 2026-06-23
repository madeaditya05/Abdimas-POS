<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Customer;
use App\Models\Penjualan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

use App\Models\StokMutasi;
use App\Models\BahanBaku;
use App\Models\PenjualanDetail;
use App\Models\Produk;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        [
            $filters,
            $rangeStart,
            $rangeEnd,
            $periodLabel,
            $salesSummaryTitle,
            $chartTitle,
        ] = $this->resolveDashboardFilter($request);

        $channelComparison = $this->channelComparisonData($rangeStart, $rangeEnd);
        $salesTotal = $channelComparison['total_revenue'];
        $paidOrdersCount = $channelComparison['total_count'];
        $newOrders = Order::where('status', 'pending')
            ->whereBetween('created_at', [$rangeStart, $rangeEnd])
            ->count();
        $newCustomers = Customer::whereBetween('created_at', [$rangeStart, $rangeEnd])->count();

        $salesChartData = $this->salesChartData($filters['period'], $rangeStart, $rangeEnd, $chartTitle);

        // ---- Pie chart produk terlaris: gabung OrderItem (online) + PenjualanDetail (kasir)
        // Subquery 1: online orders
        $onlineSub = DB::table('order_item')
            ->join('order', 'order_item.order_id', '=', 'order.id')
            ->where('order.status', 'paid')
            ->whereBetween('order.created_at', [$rangeStart, $rangeEnd])
            ->selectRaw('order_item.name as product_name, SUM(order_item.qty) as total_qty')
            ->groupBy('order_item.name');

        // Subquery 2: kasir sales (semua channel)
        $kasirSub = DB::table('penjualan_detail')
            ->join('produk', 'penjualan_detail.produk_id', '=', 'produk.id')
            ->join('penjualan', 'penjualan_detail.penjualan_id', '=', 'penjualan.id')
            ->where('penjualan.total', '>', 0)
            ->where(function ($q) {
                $q->whereColumn('penjualan.bayar', '>=', 'penjualan.total')
                  ->orWhere('penjualan.metode', 'tempo');
            })
            ->whereBetween('penjualan.tanggal', [$rangeStart, $rangeEnd])
            ->selectRaw('produk.nama_barang as product_name, SUM(penjualan_detail.qty) as total_qty')
            ->groupBy('produk.nama_barang');

        // Gabung keduanya lalu aggregate
        $topProductsChart = DB::query()
            ->fromSub(
                $onlineSub->unionAll($kasirSub),
                'combined'
            )
            ->selectRaw('product_name, SUM(total_qty) as sold')
            ->groupBy('product_name')
            ->orderByDesc('sold')
            ->limit(5)
            ->get()
            ->map(fn ($row) => [
                'name' => $row->product_name ?? '-',
                'sold' => (int) $row->sold,
            ]);

        return view('tampilan.dashboard', compact(
            'salesTotal',
            'paidOrdersCount',
            'newOrders',
            'newCustomers',
            'filters',
            'periodLabel',
            'salesSummaryTitle',
            'salesChartData',
            'topProductsChart',
            'channelComparison'
        ));
    }

    private function channelComparisonData(Carbon $rangeStart, Carbon $rangeEnd): array
    {
        $offlineQuery = Penjualan::completedPurchase()
            ->where(function ($q) {
                $q->whereNull('channel')
                  ->orWhere('channel', '!=', 'online');
            })
            ->whereBetween('tanggal', [$rangeStart, $rangeEnd]);

        $onlinePenjualanQuery = Penjualan::completedPurchase()
            ->where('channel', 'online')
            ->whereBetween('tanggal', [$rangeStart, $rangeEnd]);

        $onlineOrderQuery = Order::where('status', 'paid')
            ->whereBetween('created_at', [$rangeStart, $rangeEnd]);

        $offlineCount = (clone $offlineQuery)->count();
        $onlineCount = (clone $onlinePenjualanQuery)->count() + (clone $onlineOrderQuery)->count();
        $offlineRevenue = (float) (clone $offlineQuery)->sum('total');
        $onlineRevenue = (float) (clone $onlinePenjualanQuery)->sum('total') + (float) (clone $onlineOrderQuery)->sum('grand_total');

        $totalCount = $offlineCount + $onlineCount;
        $totalRevenue = $offlineRevenue + $onlineRevenue;

        $channels = [
            'offline' => [
                'key' => 'offline',
                'label' => 'Offline',
                'count' => $offlineCount,
                'revenue' => $offlineRevenue,
                'count_percent' => $totalCount > 0 ? round(($offlineCount / $totalCount) * 100, 1) : 0,
                'revenue_percent' => $totalRevenue > 0 ? round(($offlineRevenue / $totalRevenue) * 100, 1) : 0,
            ],
            'online' => [
                'key' => 'online',
                'label' => 'Online',
                'count' => $onlineCount,
                'revenue' => $onlineRevenue,
                'count_percent' => $totalCount > 0 ? round(($onlineCount / $totalCount) * 100, 1) : 0,
                'revenue_percent' => $totalRevenue > 0 ? round(($onlineRevenue / $totalRevenue) * 100, 1) : 0,
            ],
        ];

        if ($totalCount === 0) {
            $dominant = [
                'key' => 'none',
                'label' => 'Belum ada data',
                'count' => 0,
                'revenue' => 0,
            ];
        } elseif ($offlineCount === $onlineCount && $offlineRevenue === $onlineRevenue) {
            $dominant = [
                'key' => 'balanced',
                'label' => 'Seimbang',
                'count' => $offlineCount,
                'revenue' => $offlineRevenue,
            ];
        } else {
            $dominantKey = (
                $offlineCount > $onlineCount
                || ($offlineCount === $onlineCount && $offlineRevenue > $onlineRevenue)
            ) ? 'offline' : 'online';
            $dominant = $channels[$dominantKey];
        }

        return [
            'channels' => $channels,
            'dominant' => $dominant,
            'total_count' => $totalCount,
            'total_revenue' => $totalRevenue,
            'difference_count' => abs($offlineCount - $onlineCount),
            'difference_revenue' => abs($offlineRevenue - $onlineRevenue),
        ];
    }

    private function resolveDashboardFilter(Request $request): array
    {
        $period = $request->query('period', 'week');
        if (! in_array($period, ['day', 'week', 'month', 'year'], true)) {
            $period = 'week';
        }

        $today = now();
        $selectedDay = $this->parseDay($request->query('date'), $today);
        $selectedWeek = $this->parseWeek($request->query('week'), $today);
        $selectedMonth = $this->parseMonth($request->query('month'), $today);
        $selectedYear = $this->parseYear($request->query('year'), (int) $today->format('Y'));

        if ($period === 'month') {
            $rangeStart = $selectedMonth->copy()->startOfMonth();
            $rangeEnd = $selectedMonth->copy()->endOfMonth();
            $periodLabel = 'Bulan ' . $selectedMonth->locale('id')->translatedFormat('F Y');
            $salesSummaryTitle = 'Penjualan Bulanan';
            $chartTitle = 'Penjualan per Hari';
        } elseif ($period === 'year') {
            $rangeStart = Carbon::create($selectedYear, 1, 1)->startOfYear();
            $rangeEnd = Carbon::create($selectedYear, 12, 31)->endOfYear();
            $periodLabel = 'Tahun ' . $selectedYear;
            $salesSummaryTitle = 'Penjualan Tahunan';
            $chartTitle = 'Penjualan per Bulan';
        } elseif ($period === 'week') {
            $rangeStart = $selectedWeek->copy()->startOfDay();
            $rangeEnd = $selectedWeek->copy()->addDays(6)->endOfDay();
            $periodLabel = 'Minggu ' . $rangeStart->format('d/m/Y') . ' - ' . $rangeEnd->format('d/m/Y');
            $salesSummaryTitle = 'Penjualan Mingguan';
            $chartTitle = 'Penjualan per Hari';
        } else {
            $rangeStart = $selectedDay->copy()->startOfDay();
            $rangeEnd = $selectedDay->copy()->endOfDay();
            $periodLabel = 'Tanggal ' . $selectedDay->format('d/m/Y');
            $salesSummaryTitle = 'Penjualan Harian';
            $chartTitle = 'Penjualan per Jam';
        }

        return [
            [
                'period' => $period,
                'date' => $selectedDay->toDateString(),
                'week' => $selectedWeek->format('o-\WW'),
                'month' => $selectedMonth->format('Y-m'),
                'year' => (string) $selectedYear,
            ],
            $rangeStart,
            $rangeEnd,
            $periodLabel,
            $salesSummaryTitle,
            $chartTitle,
        ];
    }

    private function salesChartData(string $period, Carbon $rangeStart, Carbon $rangeEnd, string $chartTitle): array
    {
        if ($period === 'week') {
            $onlineRows = Order::selectRaw('DATE(created_at) as bucket, SUM(grand_total) as total')
                ->where('status', 'paid')
                ->whereBetween('created_at', [$rangeStart, $rangeEnd])
                ->groupBy('bucket')
                ->orderBy('bucket')
                ->pluck('total', 'bucket');
            $offlineRows = Penjualan::completedPurchase()
                ->selectRaw('DATE(tanggal) as bucket, SUM(total) as total')
                ->whereBetween('tanggal', [$rangeStart, $rangeEnd])
                ->groupBy('bucket')
                ->orderBy('bucket')
                ->pluck('total', 'bucket');

            $labels = [];
            $values = [];

            for ($day = 0; $day < 7; $day++) {
                $date = $rangeStart->copy()->addDays($day);
                $labels[] = $date->locale('id')->translatedFormat('D d/m');
                $values[] = (float) ($onlineRows[$date->toDateString()] ?? 0)
                    + (float) ($offlineRows[$date->toDateString()] ?? 0);
            }

            return compact('labels', 'values', 'chartTitle');
        }

        if ($period === 'month') {
            $onlineRows = Order::selectRaw('DAY(created_at) as bucket, SUM(grand_total) as total')
                ->where('status', 'paid')
                ->whereBetween('created_at', [$rangeStart, $rangeEnd])
                ->groupBy('bucket')
                ->orderBy('bucket')
                ->pluck('total', 'bucket');
            $offlineRows = Penjualan::completedPurchase()
                ->selectRaw('DAY(tanggal) as bucket, SUM(total) as total')
                ->whereBetween('tanggal', [$rangeStart, $rangeEnd])
                ->groupBy('bucket')
                ->orderBy('bucket')
                ->pluck('total', 'bucket');

            $labels = [];
            $values = [];

            for ($day = 1; $day <= $rangeStart->daysInMonth; $day++) {
                $labels[] = str_pad((string) $day, 2, '0', STR_PAD_LEFT) . '/' . $rangeStart->format('m');
                $values[] = (float) ($onlineRows[$day] ?? 0)
                    + (float) ($offlineRows[$day] ?? 0);
            }

            return compact('labels', 'values', 'chartTitle');
        }

        if ($period === 'year') {
            $onlineRows = Order::selectRaw('MONTH(created_at) as bucket, SUM(grand_total) as total')
                ->where('status', 'paid')
                ->whereBetween('created_at', [$rangeStart, $rangeEnd])
                ->groupBy('bucket')
                ->orderBy('bucket')
                ->pluck('total', 'bucket');
            $offlineRows = Penjualan::completedPurchase()
                ->selectRaw('MONTH(tanggal) as bucket, SUM(total) as total')
                ->whereBetween('tanggal', [$rangeStart, $rangeEnd])
                ->groupBy('bucket')
                ->orderBy('bucket')
                ->pluck('total', 'bucket');

            $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            $values = [];

            for ($month = 1; $month <= 12; $month++) {
                $values[] = (float) ($onlineRows[$month] ?? 0)
                    + (float) ($offlineRows[$month] ?? 0);
            }

            return compact('labels', 'values', 'chartTitle');
        }

        $onlineRows = Order::selectRaw('HOUR(created_at) as bucket, SUM(grand_total) as total')
            ->where('status', 'paid')
            ->whereBetween('created_at', [$rangeStart, $rangeEnd])
            ->groupBy('bucket')
            ->orderBy('bucket')
            ->pluck('total', 'bucket');
        $offlineRows = Penjualan::completedPurchase()
            ->selectRaw('HOUR(tanggal) as bucket, SUM(total) as total')
            ->whereBetween('tanggal', [$rangeStart, $rangeEnd])
            ->groupBy('bucket')
            ->orderBy('bucket')
            ->pluck('total', 'bucket');

        $labels = [];
        $values = [];

        for ($hour = 0; $hour < 24; $hour++) {
            $labels[] = str_pad((string) $hour, 2, '0', STR_PAD_LEFT) . ':00';
            $values[] = (float) ($onlineRows[$hour] ?? 0)
                + (float) ($offlineRows[$hour] ?? 0);
        }

        return compact('labels', 'values', 'chartTitle');
    }

    private function parseDay(?string $value, Carbon $fallback): Carbon
    {
        if ($value) {
            try {
                $date = Carbon::createFromFormat('!Y-m-d', $value);
                if ($date && $date->format('Y-m-d') === $value) {
                    return $date;
                }
            } catch (\Throwable $e) {
                //
            }
        }

        return $fallback->copy()->startOfDay();
    }

    private function parseWeek(?string $value, Carbon $fallback): Carbon
    {
        if ($value && preg_match('/^(\d{4})-W(\d{2})$/', $value, $matches)) {
            try {
                $year = (int) $matches[1];
                $week = (int) $matches[2];

                if ($week >= 1 && $week <= 53) {
                    return Carbon::now()
                        ->setISODate($year, $week, 1)
                        ->startOfDay();
                }
            } catch (\Throwable $e) {
                //
            }
        }

        return $fallback->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
    }

    private function parseMonth(?string $value, Carbon $fallback): Carbon
    {
        if ($value) {
            try {
                $date = Carbon::createFromFormat('!Y-m-d', $value . '-01');
                if ($date && $date->format('Y-m') === $value) {
                    return $date->startOfMonth();
                }
            } catch (\Throwable $e) {
                //
            }
        }

        return $fallback->copy()->startOfMonth();
    }

    private function parseYear(?string $value, int $fallback): int
    {
        if ($value && preg_match('/^\d{4}$/', $value)) {
            return (int) $value;
        }

        return $fallback;
    }

    // Endpoint JSON untuk tombol lonceng
    public function notifications()
    {
        return response()->json(Cache::remember(
            'dashboard.stock_notifications',
            now()->addSeconds(30),
            fn () => $this->stockNotificationsPayload()
        ));
    }

    private function stockNotificationsPayload(): array
    {
        // 1) Hitung stok current per bahan dari tabel stok_mutasi (IN - OUT)
        $stokPerBahan = StokMutasi::select(
                'bahan_baku_id',
                DB::raw("SUM(CASE WHEN tipe = '" . StokMutasi::TYPE_IN . "' THEN qty ELSE 0 END) as total_in"),
                DB::raw("SUM(CASE WHEN tipe = '" . StokMutasi::TYPE_OUT . "' THEN qty ELSE 0 END) as total_out")
            )
            ->groupBy('bahan_baku_id')
            ->get()
            ->mapWithKeys(function ($row) {
                $stok = (float) $row->total_in - (float) $row->total_out;
                return [$row->bahan_baku_id => $stok];
            });

        // Ambil semua bahan yang punya batas minimal, termasuk yang belum pernah punya mutasi.
        $bahan = BahanBaku::query()
            ->where('aktif', true)
            ->where('min_order_qty', '>', 0)
            ->orderBy('nama_bahan')
            ->get();

        $items = collect();

        // 2) Notif untuk stok habis / rendah
        foreach ($bahan as $row) {
            $stok = (float) ($stokPerBahan[$row->id] ?? 0);
            $minThreshold = (float) ($row->min_order_qty ?? 0);
            $satuan = $row->satuan_pakai ? ' ' . $row->satuan_pakai : '';

            if ($stok <= 0) {
                $items->push([
                    'title'    => 'Stok Habis',
                    'subtitle' => "{$row->nama_bahan} - {$stok}{$satuan} tersedia, minimal {$minThreshold}{$satuan}",
                    'color'    => '#ef4444', // merah
                ]);
            } elseif ($stok <= $minThreshold) {
                $items->push([
                    'title'    => 'Stok Rendah',
                    'subtitle' => "{$row->nama_bahan} - {$stok}{$satuan} tersedia, minimal {$minThreshold}{$satuan}",
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
            $sign = $adj->tipe === StokMutasi::TYPE_IN ? '+' : '-';
            $qty  = (float) $adj->qty;
            $tgl  = optional($adj->tanggal)->format('d M Y');

            $items->push([
                'title'    => 'Penyesuaian Stok',
                'subtitle' => "{$nama} {$sign}{$qty} ({$tgl})",
                'color'    => '#3b82f6', // biru
            ]);
        }

        // 4) Balikin JSON ke front-end
        return [
            'count' => $items->count(),
            'items' => $items->take(50)->values(), // batasi max 50 item
        ];
    }
}
