<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Customer extends Model
{
  protected $guarded = [];
  protected $table = 'customer';

  public function orders() {
    return $this->hasMany(Order::class, 'customer_id');
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
