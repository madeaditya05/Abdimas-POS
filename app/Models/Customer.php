<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Customer extends Model
{
  protected $guarded = [];
  protected $table = 'customer';

  protected $casts = [
    'discount_min_transactions' => 'integer',
    'discount_percent' => 'decimal:2',
  ];

  public function orders() {
    return $this->hasMany(Order::class, 'customer_id');
  }

  public function penjualans(): HasMany {
    return $this->hasMany(Penjualan::class, 'customer_id');
  }

  public function completedPenjualans(): HasMany {
    return $this->hasMany(Penjualan::class, 'customer_id')->completedPurchase();
  }

  public function getPurchaseCountAttribute(): int {
    $count = $this->getAttribute('completed_penjualans_count');
    if ($count !== null) return (int) $count;

    return (int) $this->completedPenjualans()->count();
  }

  public function eligibleDiscountPercent(?int $purchaseCount = null): float {
    $count = $purchaseCount ?? $this->purchase_count;
    $minimum = max(1, (int) ($this->discount_min_transactions ?? 10));
    $percent = max(0, min(99.99, (float) ($this->discount_percent ?? 0)));

    return $percent > 0 && $count >= $minimum ? $percent : 0.0;
  }

  // normalisasi nama: trim + rapikan spasi + lower
  public static function normalizeName(?string $name): ?string {
    if (!$name) return null;
    return Str::of($name)->squish()->lower()->value();
  }

  // cari customer berdasarkan nama (case-insensitive)
  public static function findByNameCI(?string $name) {
    $norm = self::normalizeName($name);
    if (!$norm) return null;
    // gunakan kolom generated (paling cepat & akurat)
    return static::where('normalized_name', $norm)->first();
    // alternatif tanpa kolom generated:
    // return static::whereRaw('LOWER(TRIM(name)) = ?', [$norm])->first();
  }
}
