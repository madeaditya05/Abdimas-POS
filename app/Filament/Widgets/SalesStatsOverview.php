<?php

namespace App\Filament\Widgets;

use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SalesStatsOverview extends BaseWidget
{
    /**
     * Auto-refresh the widget (mis. '10s', '30s', '1m'). Null = disable.
     * (Harus NON-STATIC di Filament versi kamu.)
     */
    protected ?string $pollingInterval = '30s';

    /** Lebar widget di dashboard */
    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $tz = config('app.timezone', 'Asia/Jakarta');

        $todayStart = now($tz)->startOfDay();
        $todayEnd   = now($tz)->endOfDay();
        $monthStart = now($tz)->startOfMonth();
        $monthEnd   = now($tz)->endOfMonth();
        $daysWindow = 14; // sparkline 14 hari terakhir

        // === Aggregates ===
        $ordersToday    = Penjualan::whereBetween('created_at', [$todayStart, $todayEnd])->count();
        $revenueToday   = (float) Penjualan::whereBetween('created_at', [$todayStart, $todayEnd])->sum('total');
        $revenueMonth   = (float) Penjualan::whereBetween('created_at', [$monthStart, $monthEnd])->sum('total');
        $itemsSoldToday = (int) PenjualanDetail::whereBetween('created_at', [$todayStart, $todayEnd])->sum('qty');
        $aovToday       = $ordersToday > 0 ? $revenueToday / $ordersToday : 0.0;

        // === Sparkline revenue per hari ===
        $startWindow = now($tz)->copy()->subDays($daysWindow - 1)->startOfDay();

        $rows = Penjualan::query()
            ->selectRaw('DATE(created_at) as d, SUM(total) as s')
            ->where('created_at', '>=', $startWindow)
            ->groupBy('d')
            ->orderBy('d')
            ->pluck('s', 'd')
            ->all();

        $spark = [];
        for ($i = 0; $i < $daysWindow; $i++) {
            $d = $startWindow->copy()->addDays($i)->toDateString();
            $spark[] = isset($rows[$d]) ? (float) $rows[$d] : 0.0;
        }

        // Tren vs rata-rata 7 hari terakhir (pendekatan)
        $last7    = array_slice($spark, -7);
        $avg7     = array_sum($last7) / max(count($last7), 1);
        $baseline = $avg7 / 7; // estimasi harian
        $trendPct = $baseline > 0 ? (($revenueToday - $baseline) / $baseline) * 100 : 0;

        return [
            Stat::make('Pendapatan Hari Ini', $this->rupiah($revenueToday))
                ->description(($trendPct >= 0 ? '+' : '') . number_format($trendPct, 1, ',', '.') . '% vs rata-rata 7 hari')
                ->descriptionIcon($trendPct >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->chart($spark)
                ->color($trendPct >= 0 ? 'success' : 'danger'),

            Stat::make('Pesanan Hari Ini', number_format($ordersToday, 0, ',', '.'))
                ->description('Rata-rata nilai pesanan: ' . $this->rupiah($aovToday))
                ->descriptionIcon('heroicon-m-rectangle-stack')
                ->chart($this->diffChart($spark))
                ->color('primary'),

            Stat::make('Pendapatan Bulan Ini', $this->rupiah($revenueMonth))
                ->description('Item terjual hari ini: ' . number_format($itemsSoldToday, 0, ',', '.'))
                ->descriptionIcon('heroicon-m-cube')
                ->chart($spark)
                ->color('info'),
        ];
    }

    /** Format Rupiah */
    private function rupiah(float $value): string
    {
        return 'Rp ' . number_format($value, 0, ',', '.');
    }

    /** Transform sederhana untuk sparkline pembanding */
    private function diffChart(array $values): array
    {
        $out = [];
        foreach ($values as $i => $v) {
            $prev = $i > 0 ? $values[$i - 1] : $v;
            $out[] = $v - $prev;
        }
        return $out;
    }
}
