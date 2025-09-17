<?php

namespace App\Models;

use App\Services\JournalPoster;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Penjualan extends Model
{
    protected $table = 'penjualan';

    protected $fillable = [
        'kode_penjualan',
        'tanggal',
        'user_id',
        'total',
        'bayar',
        'kembalian',
        'metode',
    ];

    protected $casts = [
        'tanggal'   => 'datetime',
        'total'     => 'decimal:2',
        'bayar'     => 'decimal:2',
        'kembalian' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(PenjualanDetail::class, 'penjualan_id');
    }

    public static function generateKodeHarian(): string
    {
        $today  = now()->format('ymd');
        $prefix = "CFF-{$today}-";

        $lastKode = static::whereDate('tanggal', now()->toDateString())
            ->where('kode_penjualan', 'like', $prefix . '%')
            ->orderByDesc('kode_penjualan')
            ->value('kode_penjualan');

        $nextNumber = 1;
        if ($lastKode && preg_match('/^CFF-\d{6}-(\d{4})$/', $lastKode, $m)) {
            $nextNumber = (int) $m[1] + 1;
        }

        $suffix = str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);

        return $prefix . $suffix;
    }

    /** Hitung ulang total dari detail & kembalian dari bayar */
    public function recalcTotal(): void
    {
        $total = (float) $this->details()->sum('subtotal');

        $this->total     = $total;
        $this->kembalian = max(0, (float) ($this->bayar ?? 0) - $total);

        // penting: quietly → tidak memicu event "saved" lagi (hindari loop)
        $this->saveQuietly();
    }

    protected static function booted(): void
    {
        // Default value saat membuat
        static::creating(function (Penjualan $model) {
            $model->tanggal        ??= now();
            $model->kode_penjualan ??= static::generateKodeHarian();
            $model->metode         ??= 'cash';
            $model->total          ??= 0;
            $model->bayar          ??= 0;
            $model->kembalian      ??= 0;
        });

        // Pastikan anak terhapus pakai delete() agar event di detail terpanggil
        static::deleting(function (self $header) {
            foreach ($header->details as $d) {
                $d->delete();
            }
        });

        // =========================
        // [JOURNAL] Integrasi jurnal
        // =========================

        // 1) Setelah header tersimpan:
        //    - hitung ulang total (supaya akurat kalau detail berubah)
        //    - post jurnal (Kas/Bank/Piutang vs Pendapatan)
        static::saved(function (self $m) {
            $m->recalcTotal(); // quietly, tidak ngetrigger saved lagi
            app(JournalPoster::class)->postForPenjualan($m);
        });

        // 2) Saat header dihapus: hapus jurnal sumbernya
        static::deleted(function (self $m) {
            app(JournalPoster::class)->deleteFor(self::class, $m->id);
        });
    }
}
