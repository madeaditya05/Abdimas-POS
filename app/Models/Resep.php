<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Resep extends Model
{
    /** @use HasFactory<\Database\Factories\ResepFactory> */
    use HasFactory;

    protected $table = 'resep';
    protected $fillable = ['produk_id', 'is_active', 'catatan'];

     public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(ResepDetail::class);
    }

    // Pastikan hanya satu resep aktif per produk (soft rule di level model)
    protected static function booted(): void
    {
        static::saving(function (Resep $resep) {
            if ($resep->is_active) {
                static::where('produk_id', $resep->produk_id)
                    ->where('id', '!=', $resep->id)
                    ->update(['is_active' => false]);
            }
        });
    }
}
