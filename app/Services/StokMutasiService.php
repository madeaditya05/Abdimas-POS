<?php

namespace App\Services;

use App\Models\StokMutasi;
use Illuminate\Database\Eloquent\Model;

class StokMutasiService
{
    public function in(Model $source, int $bahanId, float $qtyBeli = 0, string $note = 'PURCHASE'): void
    {
        // Kalau $source adalah PembelianBahanDetail, hitung qtyIn pakai konversi
        $qtyIn = $qtyBeli;

        // deteksi properti yang ada di detail
        $isi   = (float) ($source->isi_per_kemasan   ?? 1);
        $konv  = (float) ($source->konversi_ke_pakai ?? 1);
        $qty   = (float) ($source->qty_beli          ?? $qtyBeli);

        // hitung konversi (qty beli * isi per kemasan * konversi ke pakai)
        $qtyIn = $qty * $isi * $konv;

        if ($qtyIn <= 0) return;

        StokMutasi::create([
            'bahan_baku_id' => $bahanId,
            'tipe'          => 'IN',
            'qty'           => $qtyIn,
            'tanggal'       => $source->tanggal ?? $source->created_at ?? now(),
            'sumber_type'   => get_class($source),
            'sumber_id'     => $source->getKey(),
            'note'          => $note,
        ]);
    }

    public function out(Model $source, int $bahanId, float $qty, string $note = ''): void
    {
        if ($qty <= 0) return;

        StokMutasi::create([
            'bahan_baku_id' => $bahanId,
            'tipe'          => 'OUT',
            'qty'           => $qty,
            'tanggal'       => $source->tanggal ?? $source->created_at ?? now(),
            'sumber_type'   => get_class($source),
            'sumber_id'     => $source->getKey(),
            'note'          => $note,
        ]);
    }

    public function remove(Model $source): void
    {
        StokMutasi::where('sumber_type', get_class($source))
            ->where('sumber_id', $source->getKey())
            ->delete();
    }
}
