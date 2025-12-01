<?php

namespace App\Models;

use App\Models\StokMutasi;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BahanBaku extends Model
{
    use HasFactory;

    protected $table = 'bahan_baku';
    protected $guarded = [];

    protected $casts = [
        'aktif' => 'boolean',
        'is_perishable' => 'boolean',
        'kelola_expired' => 'boolean',
        'dipakai_di_resep' => 'boolean',
        'konversi_beli_ke_pakai' => 'float',
        'isi_per_kemasan' => 'float',
        'yield_persen' => 'float',
        'min_order_qty' => 'float',
        'lead_time_hari' => 'integer',
        'masa_simpan_hari' => 'integer',
    ];

    public function resepDetails(): HasMany
    {
        return $this->hasMany(ResepDetail::class, 'bahan_baku_id');
    }

    /** relasi ke baris pembelian detail (untuk ambil harga terakhir) */
    public function pembelianDetails(): HasMany
    {
        return $this->hasMany(PembelianBahanDetail::class, 'bahan_baku_id');
    }

    /** Format kode berikutnya: BHK0001, BHK0002, ... */
    public static function nextKode(string $prefix = 'BHK', int $pad = 4): string
    {
        $last = static::where('kode_bahan', 'like', $prefix . '%')
            ->orderByDesc('kode_bahan')
            ->value('kode_bahan');

        if (! $last) {
            return $prefix . str_pad('1', $pad, '0', STR_PAD_LEFT);
        }

        $num = (int) substr($last, strlen($prefix));
        $next = $num + 1;

        return $prefix . str_pad((string) $next, $pad, '0', STR_PAD_LEFT);
    }

    public function mutasi()
    {
        return $this->hasMany(\App\Models\StokMutasi::class, 'bahan_baku_id');
    }

    /**
     * Accessor stok:
     * gunakan helper StokMutasi::getStock() sebagai satu-satunya sumber perhitungan stok.
     * (IN - OUT, termasuk penyesuaian yang juga dicatat sebagai IN/OUT di stok_mutasi)
     *
     * Kolom DB 'stok' dibiarkan sebagai legacy/cache dan tidak dipakai di UI.
     */
    public function getStokAttribute()
    {
        return StokMutasi::getStock($this->id);
    }

    protected static function booted(): void
    {
        static::creating(function (BahanBaku $row) {
            if (empty($row->kode_bahan)) {
                for ($i = 0; $i < 3; $i++) {
                    $kode = static::nextKode('BHK', 4);
                    if (! static::where('kode_bahan', $kode)->exists()) {
                        $row->kode_bahan = $kode;
                        break;
                    }
                }
                if (empty($row->kode_bahan)) {
                    $row->kode_bahan = static::nextKode('BHK', 4);
                }
            }
        });
    }
}
