<?php

namespace App\Models;

use App\Services\JournalPoster;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Penjualan extends Model
{
    protected $table = 'penjualan';

    // Biarkan model yang mengisi kode_penjualan (jangan dimass-assign dari form)
    protected $fillable = [
        'tanggal',
        'user_id',
        'customer_id',
        'total',
        'subtotal_sebelum_diskon',
        'diskon_persen',
        'diskon_nominal',
        'bayar',
        'kembalian',
        'metode',
        'invoice_to_name',
        'invoice_to_company',
        'tempo_due_date',
        'struk_dicetak',
        'struk_dicetak_at',
    ];

    protected $casts = [
        'tanggal'   => 'datetime',
        'total'     => 'decimal:2',
        'subtotal_sebelum_diskon' => 'decimal:2',
        'diskon_persen' => 'decimal:2',
        'diskon_nominal' => 'decimal:2',
        'bayar'     => 'decimal:2',
        'kembalian' => 'decimal:2',
        'tempo_due_date' => 'date',
        'struk_dicetak' => 'boolean',
        'struk_dicetak_at' => 'datetime',
    ];

    /* =======================
     |  Relasi
     =======================*/
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(PenjualanDetail::class, 'penjualan_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'penjualan_id');
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class, 'penjualan_id');
    }

    public function scopeCompletedPurchase($query)
    {
        return $query
            ->where('total', '>', 0)
            ->where(function ($q) {
                $q->whereColumn('bayar', '>=', 'total')
                    ->orWhere('metode', 'tempo')
                    ->orWhereHas('payments', function ($paymentQuery) {
                        $paymentQuery->whereIn('transaction_status', ['settlement', 'capture']);
                    });
            });
    }

    /* =======================
     |  Generator Kode Harian
     |  Format: PJL-ddmmyy-0001
     |  Basis hari: created_at (hari ini)
     =======================*/
    public static function nextKode(): string
    {
        $prefix = 'PJL-' . now()->format('dmy') . '-';

        return DB::transaction(function () use ($prefix) {
            $last = static::whereDate('created_at', today())
                ->where('kode_penjualan', 'like', $prefix . '%')
                ->lockForUpdate()
                ->orderByDesc('id')
                ->value('kode_penjualan');

            $next = 1;
            if ($last && preg_match('/^PJL-\d{6}-(\d{4})$/', $last, $m)) {
                $next = (int) $m[1] + 1;
            }

            return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
        });
    }

    /* =======================
     |  Hitung Total & Kembalian
     |  (dari detail: subtotal = harga * qty)
     =======================*/
    public function recalcTotal(): void
    {
        $subtotal = (float) ($this->details()->sum('subtotal') ?? 0);
        $discountPercent = max(0, min(99.99, (float) ($this->diskon_persen ?? 0)));
        $discountAmount = $discountPercent > 0
            ? floor($subtotal * $discountPercent / 100)
            : 0;
        $discountAmount = min($subtotal, max(0, (float) $discountAmount));
        $total = max(0, $subtotal - $discountAmount);

        $this->subtotal_sebelum_diskon = $subtotal;
        $this->diskon_nominal = $discountAmount;
        $this->total = $total;
        $this->kembalian = max(0, (float) ($this->bayar ?? 0) - $total);

        // quietly → tidak memicu event "saved" lagi (hindari loop)
        $this->saveQuietly();
    }

    /* =======================
     |  Model Hooks
     =======================*/
    protected static function booted(): void
    {
        // Nilai default saat create
        static::creating(function (self $m) {
            $m->tanggal        ??= now();
            $m->kode_penjualan ??= static::nextKode();
            $m->metode         ??= 'cash';
            $m->total          ??= 0;
            $m->subtotal_sebelum_diskon ??= 0;
            $m->diskon_persen  ??= 0;
            $m->diskon_nominal ??= 0;
            $m->bayar          ??= 0;
            $m->kembalian      ??= 0;
        });

        // Hapus anak2nya pakai delete() supaya event di detail tetap jalan
        static::deleting(function (self $header) {
            foreach ($header->details as $d) {
                $d->delete();
            }
        });

        // Setelah tersimpan: hitung ulang total & post jurnal
        static::saved(function (self $m) {
            $m->recalcTotal(); // quietly

            try {
                app(JournalPoster::class)->postForPenjualan($m);
            } catch (\Throwable $e) {
                Log::warning('Gagal post jurnal penjualan, transaksi tetap dicatat.', [
                    'penjualan_id' => $m->id,
                    'kode_penjualan' => $m->kode_penjualan,
                    'message' => $e->getMessage(),
                ]);
            }
        });

        // Saat dihapus: hapus jurnal yang terkait
        static::deleted(function (self $m) {
            app(JournalPoster::class)->deleteFor(self::class, $m->id);
        });
    }
}
