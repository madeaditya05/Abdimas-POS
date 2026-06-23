<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;


class Produk extends Model
{
    use HasFactory;

    protected $table = 'produk';

    protected $fillable = [
        'kode_barang',
        'nama_barang',
        'aktif',
        'harga',
        'harga_online',
        'kategori',
        'gambar',
        'deskripsi',
    ];

    protected $casts = [
        'harga' => 'decimal:2',
        'harga_online' => 'decimal:2',
        'aktif' => 'boolean',
    ];

    public function reseps(): HasMany
    {
        return $this->hasMany(\App\Models\Resep::class, 'produk_id');
    }

    public function penjualanDetails(): HasMany
    {
        return $this->hasMany(\App\Models\PenjualanDetail::class, 'produk_id');
    }

    public function resepAktif(): HasOne
    {
        return $this->hasOne(\App\Models\Resep::class, 'produk_id')->where('is_active', 1);
    }

    public function kategoriProduk(): BelongsTo
    {
        return $this->belongsTo(KategoriProduk::class, 'kategori', 'slug');
    }

    public function getKategoriLabelAttribute(): string
    {
        if ($this->kategoriProduk?->nama) {
            return $this->kategoriProduk->nama;
        }

        return (string) Str::of((string) $this->kategori)->replace('_', ' ')->title();
    }

    public static function generateKodeBarang()
    {
        $last = self::orderBy('id', 'desc')->first();
        if (!$last || !$last->kode_barang) {
            $number = 1;
        } else {
            $number = (int)substr($last->kode_barang, 2) + 1;
        }
        return 'CF' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }
}
