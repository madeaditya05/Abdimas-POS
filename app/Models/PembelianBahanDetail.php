<?php

namespace App\Models;

use App\Services\StokMutasiService;
use App\Services\JournalPoster;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PembelianBahanDetail extends Model
{
    protected $table = 'pembelian_bahan_detail';

    protected $fillable = [
        'pembelian_bahan_id', 'bahan_baku_id',
        'nama_bahan', 'satuan_beli', 'isi_per_kemasan', 'konversi_ke_pakai',
        'expired_date',
        'qty_beli', 'harga_satuan', 'subtotal',
        'catatan',
    ];

    protected $casts = [
        'isi_per_kemasan'   => 'float',
        'konversi_ke_pakai' => 'float',
        'qty_beli'          => 'float',
        'harga_satuan'      => 'float',
        'subtotal'          => 'float',
        'expired_date'      => 'date',
    ];

    public function header(): BelongsTo
    {
        return $this->belongsTo(PembelianBahan::class, 'pembelian_bahan_id');
    }

    public function bahan(): BelongsTo
    {
        return $this->belongsTo(BahanBaku::class, 'bahan_baku_id');
    }

    /**
     * - Hitung subtotal otomatis.
     * - Snapshot field dari master BahanBaku (kalau belum terisi).
     * - Setelah save/delete: recalculating total header & sync StokMutasi (IN).
     * - TAMBAHAN: auto-post jurnal pembelian (JournalPoster).
     */
    protected static function booted(): void
    {
        // sebelum simpan
        static::saving(function (PembelianBahanDetail $d) {
            // subtotal
            $d->subtotal = (float) ($d->qty_beli ?? 0) * (float) ($d->harga_satuan ?? 0);

            // snapshot dari master (isi kalau masih kosong)
            if ($d->bahan_baku_id && (!$d->nama_bahan || !$d->konversi_ke_pakai)) {
                $b = BahanBaku::find($d->bahan_baku_id);
                if ($b) {
                    $d->nama_bahan        ??= $b->nama_bahan;
                    $d->satuan_beli       ??= $b->satuan_beli;
                    $d->isi_per_kemasan   ??= $b->isi_per_kemasan;
                    $d->konversi_ke_pakai ??= $b->konversi_beli_ke_pakai;
                    // expired_date tetap opsional → diisi manual kalau perlu
                }
            }
        });

        // setelah simpan (create/update)
        static::saved(function (PembelianBahanDetail $d) {
            // 1) Recalc total header (ini yang bikin $buy->total bener)
            $header = $d->header;
            if ($header) {
                $header->recalcTotal();
                $header->refresh(); // pastiin total terbaru kebaca
            }

            // 2) Sinkron stok: hapus mutasi lama milik record ini, lalu buat IN baru
            app(StokMutasiService::class)->remove($d);

            if ($d->bahan_baku_id && (float)$d->qty_beli > 0) {
                app(StokMutasiService::class)->in(
                    $d,
                    (int) $d->bahan_baku_id,
                    0,          // <-- JANGAN kirim qty_beli di sini
                    'PURCHASE'
                );
            }

            // 3) TAMBAHAN PENTING: Auto-post jurnal pembelian berdasarkan TOTAL header
            if ($header && (float)($header->total ?? 0) > 0) {
                app(JournalPoster::class)->postForPembelianBahan($header);
            }
        });

        // kalau detail dihapus, total berubah => jurnal harus ikut update/hapus
        static::deleted(function (PembelianBahanDetail $d) {
            $header = $d->header;
            if ($header) {
                $header->recalcTotal();
                $header->refresh();

                if ((float)($header->total ?? 0) > 0) {
                    app(JournalPoster::class)->postForPembelianBahan($header);
                } else {
                    app(JournalPoster::class)->deleteFor(PembelianBahan::class, (int)$header->id);
                }
            }

            // rapihin stok mutasi terkait detail yang dihapus
            app(StokMutasiService::class)->remove($d);
        });
    }
}
