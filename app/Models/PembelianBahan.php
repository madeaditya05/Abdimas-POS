<?php

namespace App\Models;

use App\Services\JournalPoster;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PembelianBahan extends Model
{
    protected $table = 'pembelian_bahan';

    protected $fillable = [
        'kode_pembelian','tanggal','user_id',
        'supplier_nama','supplier_kontak',
        'total','catatan',
        'bukti_file', // ✅ tambahan: path/filename bukti upload
    ];

    protected $casts = [
        'tanggal' => 'datetime',
        'total'   => 'float',
    ];

    public function details(): HasMany
    {
        return $this->hasMany(PembelianBahanDetail::class, 'pembelian_bahan_id');
    }

    /** Generate kode harian: PBL-YYMMDD-0001 */
    public static function generateKodeHarian(): string
    {
        $today = now()->format('ymd');
        $prefix = "PBL-{$today}-";

        $last = static::whereDate('tanggal', now()->toDateString())
            ->where('kode_pembelian', 'like', $prefix.'%')
            ->orderByDesc('kode_pembelian')
            ->value('kode_pembelian');

        $next = 1;
        if ($last && preg_match('/^PBL-\d{6}-(\d{4})$/', $last, $m)) {
            $next = (int) $m[1] + 1;
        }

        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    /** Hitung ulang total dari detail */
    public function recalcTotal(): void
    {
        $this->total = (float) $this->details()->sum('subtotal');
        $this->saveQuietly(); // tidak memicu "saved" lagi
    }

    /** Auto isi kode & tanggal + integrasi jurnal */
    protected static function booted(): void
    {
        static::creating(function (self $m) {
            $m->tanggal ??= now();
            $m->kode_pembelian ??= static::generateKodeHarian();
        });

        // Urutan penting:
        // 1) saved -> recalcTotal (quietly)
        static::saved(function (self $m) {
            $m->recalcTotal();
        });

        // 2) saved -> post jurnal (setelah total diperbarui oleh recalcTotal)
        static::saved(function (self $m) {
            app(JournalPoster::class)->postForPembelianBahan($m);
        });

        // 3) deleted -> hapus jurnal
        static::deleted(function (self $m) {
            app(JournalPoster::class)->deleteFor(self::class, $m->id);
        });
    }
}
