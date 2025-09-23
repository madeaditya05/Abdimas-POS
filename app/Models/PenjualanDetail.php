<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PenjualanDetail extends Model
{
    protected $table = 'penjualan_detail';

    protected $fillable = [
        'penjualan_id',
        'produk_id',
        'harga',
        'qty',
        'subtotal',
        // jika menyimpan snapshot nama/catatan di detail, aktifkan juga:
        // 'nama_produk', 'catatan',
    ];

    protected $casts = [
        'harga'    => 'decimal:2',
        'subtotal' => 'decimal:2',
        'qty'      => 'integer', // ubah ke 'decimal:2' jika butuh qty pecahan
    ];

    // updated_at header (penjualan) ikut berubah saat detail berubah
    protected $touches = ['penjualan'];

    public function penjualan(): BelongsTo
    {
        return $this->belongsTo(Penjualan::class, 'penjualan_id');
    }

    public function produk(): BelongsTo
    {
        // withDefault agar aman jika produk null/terhapus
        return $this->belongsTo(Produk::class, 'produk_id')->withDefault();
    }

    /**
     * Nama untuk ditampilkan/cetak:
     * snapshot (nama_produk) > produk.nama_barang > produk.name > produk.nama
     */
    public function getNamaCetakAttribute(): string
    {
        $p = $this->produk;

        return (string) (
            $this->nama_produk
            ?? $p->nama_barang
            ?? $p->name
            ?? $p->nama
            ?? '-'
        );
    }

    protected static function booted(): void
    {
        // Pastikan harga & subtotal benar sebelum simpan
        static::saving(function (self $row) {
            // Jika harga kosong/tidak dikirim dari form, ambil dari produk
            if (empty($row->harga) && $row->produk) {
                $row->harga = $row->produk->harga
                    ?? $row->produk->harga_jual
                    ?? $row->produk->price
                    ?? 0;
            }

            // subtotal = harga * qty (dibulatkan 2 desimal)
            $row->subtotal = round(
                (float) ($row->harga ?? 0) * (float) ($row->qty ?? 0),
                2
            );
        });

        // Setelah simpan/hapus, hitung ulang total header
        static::saved(function (self $row) {
            $row->penjualan?->recalcTotal();
        });

        static::deleted(function (self $row) {
            $row->penjualan?->recalcTotal();
        });

        // Hapus mutasi stok OUT saat baris dihapus (jika menggunakan service ini)
        static::deleting(function (self $row) {
            app(\App\Services\StokMutasiService::class)->remove($row);
        });
    }
}
