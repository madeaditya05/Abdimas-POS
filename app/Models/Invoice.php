<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    public const STATUS_UNPAID = 'unpaid';
    public const STATUS_PAID = 'paid';

    protected $fillable = [
        'penjualan_id',
        'nomor_invoice',
        'nama_toko',
        'tanggal_invoice',
        'tanggal_jatuh_tempo',
        'total_tagihan',
        'status',
    ];

    protected $casts = [
        'tanggal_invoice' => 'date',
        'tanggal_jatuh_tempo' => 'date',
        'total_tagihan' => 'decimal:2',
    ];

    public function penjualan(): BelongsTo
    {
        return $this->belongsTo(Penjualan::class, 'penjualan_id');
    }

    public function getSisaHariAttribute(): int
    {
        if (! $this->tanggal_jatuh_tempo) {
            return 0;
        }

        return (int) today()->diffInDays($this->tanggal_jatuh_tempo, false);
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status === self::STATUS_UNPAID
            && $this->tanggal_jatuh_tempo
            && $this->tanggal_jatuh_tempo->lt(today());
    }

    public function getStatusPiutangLabelAttribute(): string
    {
        if ($this->status === self::STATUS_PAID) {
            return 'Lunas';
        }

        return $this->is_overdue ? 'Terlambat/Overdue' : 'Menunggu';
    }
}
