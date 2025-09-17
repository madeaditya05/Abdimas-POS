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
    ];

    protected $casts = [
        'harga'    => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function penjualan(): BelongsTo
    {
        return $this->belongsTo(Penjualan::class, 'penjualan_id');
    }

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }

    protected static function booted(): void
    {
        // Hitung subtotal = harga * qty
        static::saving(function (PenjualanDetail $row) {
            $row->subtotal = ($row->harga ?? 0) * (int) ($row->qty ?? 0);
        });

        // Recalculate total tiap kali detail disimpan/dihapus
        static::saved(function (PenjualanDetail $row) {
            $row->penjualan?->recalcTotal();
        });

        static::deleted(function (PenjualanDetail $row) {
            $row->penjualan?->recalcTotal();
        });

        // Hapus mutasi OUT ketika baris repeater ini dihapus
        static::deleting(function (self $detail) {
            app(\App\Services\StokMutasiService::class)->remove($detail);
        });
    }
}
