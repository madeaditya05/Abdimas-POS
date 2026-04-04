<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriProduk extends Model
{
    use HasFactory;

    protected $table = 'kategori_produk';

    protected $guarded = [];

    protected $casts = [
        'aktif' => 'boolean',
    ];

    public function produks(): HasMany
    {
        return $this->hasMany(Produk::class, 'kategori', 'slug');
    }

    public static function nextUrutan(): int
    {
        return ((int) static::max('urutan')) + 1;
    }

    public static function ordered()
    {
        return static::query()->orderBy('urutan')->orderBy('nama');
    }

    public static function activeOptions(): array
    {
        return static::ordered()
            ->where('aktif', true)
            ->pluck('nama', 'slug')
            ->all();
    }

    public static function allOptions(): array
    {
        return static::ordered()
            ->pluck('nama', 'slug')
            ->all();
    }
}
