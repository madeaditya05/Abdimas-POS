<?php

namespace App\Filament\Widgets;

use App\Models\BahanBaku;
use App\Models\StokMutasi;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class LowStockWidget extends TableWidget
{
    protected static ?string $heading = 'Stok Hampir Habis';
    protected static ?int $sort = 30;

    /**
     * Lebar kartu di dashboard:
     * - HP (sm): full
     * - Tablet (md): 1/2
     * - Desktop (lg/xl): 1/3 (asumsi grid dashboard lg=3 kolom)
     */
    protected int|string|array $columnSpan = [
        'sm' => 12,
        'md' => 6,
        'lg' => 4,
        'xl' => 4,
    ];

    // Auto refresh (opsional)
    protected static ?string $pollingInterval = '60s';

    public function table(Table $table): Table
    {
        return $table
            ->query($this->query())
            ->columns([
                TextColumn::make('nama_bahan')
                    ->label('Item')
                    ->wrap()
                    ->alignCenter()
                    ->limit(26),

                TextColumn::make('stok_saat_ini')
                    ->label('Sisa')
                    ->alignCenter()
                    ->badge()
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 0, ',', '.'))
                    ->color(fn ($record) => $this->badgeColor(
                        (float) $record->stok_saat_ini,
                        (float) $record->min_order_qty
                    )),

                TextColumn::make('min_order_qty')
                    ->label('Min')
                    ->alignCenter()
                    ->badge()
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 0, ',', '.'))
                    ->color('warning'),

                TextColumn::make('butuh_reorder')
                    ->label('Butuh')
                    ->alignCenter()
                    ->state(fn ($record) => max(0, (int) $record->min_order_qty - (int) $record->stok_saat_ini))
                    ->badge()
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 0, ',', '.'))
                    ->color(fn ($record) => ((float) $record->stok_saat_ini < (float) $record->min_order_qty) ? 'danger' : 'gray'),
            ])
            // tampilkan ringkas
            ->paginated([5, 10])
            ->defaultPaginationPageOption(5)
            ->defaultSort(fn () => null) // urutan kita atur di query()
            ->recordUrl(null)            // biar ga klik ke halaman lain
            ->emptyStateHeading('Semua stok aman 🎉')
            ->emptyStateDescription('Tidak ada item di bawah batas minimum.')
            ->striped();
    }

    /**
     * Hitung stok dari tabel `stok_mutasi`:
     *   stok = SUM(IN) - SUM(OUT) + SUM(ADJ)
     *
     * Kita hitung dulu di subquery (alias: s) lalu join ke `bahan_baku`.
     */
    protected function query(): Builder
    {
        // Subquery: agregat stok per bahan_baku_id
        $sub = StokMutasi::query()
            ->selectRaw("
                bahan_baku_id,
                COALESCE(SUM(CASE WHEN tipe = 'IN'  THEN qty ELSE 0 END), 0) AS in_sum,
                COALESCE(SUM(CASE WHEN tipe = 'OUT' THEN qty ELSE 0 END), 0) AS out_sum,
                COALESCE(SUM(CASE WHEN tipe = 'ADJ' THEN qty ELSE 0 END), 0) AS adj_sum,
                -- stok saat ini
                COALESCE(SUM(CASE WHEN tipe = 'IN'  THEN qty ELSE 0 END), 0)
              - COALESCE(SUM(CASE WHEN tipe = 'OUT' THEN qty ELSE 0 END), 0)
              + COALESCE(SUM(CASE WHEN tipe = 'ADJ' THEN qty ELSE 0 END), 0) AS stok_saat_ini
            ")
            ->groupBy('bahan_baku_id');

        return BahanBaku::query()
            ->leftJoinSub($sub, 's', 's.bahan_baku_id', '=', 'bahan_baku.id')
            ->selectRaw('
                bahan_baku.id,
                bahan_baku.nama_bahan,
                bahan_baku.min_order_qty,
                COALESCE(s.stok_saat_ini, 0) AS stok_saat_ini
            ')
            ->whereNotNull('bahan_baku.min_order_qty')
            ->where('bahan_baku.min_order_qty', '>', 0)
            ->whereRaw('COALESCE(s.stok_saat_ini, 0) <= bahan_baku.min_order_qty')
            // urut yang paling kritis di atas
            ->orderByRaw("
                CASE
                    WHEN bahan_baku.min_order_qty > 0
                        THEN (COALESCE(s.stok_saat_ini, 0) / bahan_baku.min_order_qty)
                    ELSE 1
                END ASC
            ")
            ->orderByRaw('COALESCE(s.stok_saat_ini, 0) ASC')
            ->orderBy('bahan_baku.id', 'asc')
            ->limit(50);
    }

    protected function badgeColor(float $stok, float $min): string
    {
        if ($stok <= 0) return 'danger';   // merah
        if ($stok < $min) return 'warning';// kuning
        return 'gray';
    }
}
