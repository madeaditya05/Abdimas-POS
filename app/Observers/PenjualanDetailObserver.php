<?php

namespace App\Observers;

use App\Models\PenjualanDetail;
use App\Models\Resep;
use App\Models\ResepDetail;
use App\Models\StokMutasi;
use Illuminate\Support\Facades\DB;

class PenjualanDetailObserver
{
    public function created(PenjualanDetail $detail): void
    {
        DB::afterCommit(function () use ($detail) {
            // 1) Cari resep untuk produk yang dijual
            $resepIds = Resep::query()
                ->where('produk_id', $detail->produk_id)
                ->when(
                    SchemaHasColumn('reseps', 'aktif'),
                    fn ($q) => $q->where('aktif', true)
                )
                ->pluck('id');

            if ($resepIds->isEmpty()) {
                // Tidak ada resep → tidak ada mutasi OUT
                return;
            }

            // 2) Ambil semua bahan dari resep_id yang ditemukan
            $resepDetails = ResepDetail::query()
                ->whereIn('resep_id', $resepIds)
                ->get();

            foreach ($resepDetails as $rd) {
                $qtyOut = (float) ($detail->qty ?? 0) * (float) ($rd->qty ?? 0);
                if ($qtyOut <= 0) continue;

                StokMutasi::create([
                    'bahan_baku_id' => $rd->bahan_baku_id,
                    'tipe'          => 'OUT',                 // kolommu = 'tipe'
                    'qty'           => $qtyOut,
                    'tanggal'       => now(),
                    'sumber_type'   => PenjualanDetail::class,
                    'sumber_id'     => $detail->id,
                    'note'          => 'Pemakaian penjualan (BOM) #' . ($detail->penjualan_id ?? '-'),
                ]);
            }
        });
    }
}

/**
 * Helper kecil: cek ada kolom 'aktif' atau tidak (biar fleksibel).
 */
if (! function_exists('SchemaHasColumn')) {
    function SchemaHasColumn(string $table, string $column): bool
    {
        try {
            return \Illuminate\Support\Facades\Schema::hasColumn($table, $column);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
