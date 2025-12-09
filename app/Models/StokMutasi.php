<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StokMutasi extends Model
{
    protected $table = 'stok_mutasi'; // karena bukan jamak

    public const TYPE_IN  = 'in';
    public const TYPE_OUT = 'out';

    public const SUMBER_PENYESUAIAN = 'penyesuaian_stok';

    protected $fillable = [
        'bahan_baku_id',
        'tipe',
        'qty',
        'tanggal',
        'sumber_type',
        'sumber_id',
        'note',
    ];

    protected $casts = [
        'tanggal' => 'datetime',
        'qty'     => 'float',
    ];

    public function sumber()
    {
        return $this->morphTo(__FUNCTION__, 'sumber_type', 'sumber_id');
    }

    public function bahan()
    {
        return $this->belongsTo(\App\Models\BahanBaku::class, 'bahan_baku_id')
            ->withDefault([
                'nama_bahan' => '(bahan tidak ada)',
            ]);
    }

    public function scopeIn($query)
    {
        return $query->where('tipe', self::TYPE_IN);
    }

    public function scopeOut($query)
    {
        return $query->where('tipe', self::TYPE_OUT);
    }

    public function scopeAdjustment($query)
    {
        return $query->where('sumber_type', self::SUMBER_PENYESUAIAN);
    }

    public static function getStock($bahanBakuId)
    {
        $in = self::where('bahan_baku_id', $bahanBakuId)
            ->where('tipe', self::TYPE_IN)
            ->sum('qty');

        $out = self::where('bahan_baku_id', $bahanBakuId)
            ->where('tipe', self::TYPE_OUT)
            ->sum('qty');

        return $in - $out;
    }

    public static function adjustStock(
        int $bahanBakuId,
        float $deltaQty,
        ?string $note = null,
        $tanggal = null
    ): self {
        $tanggal = $tanggal ?: now();

        $tipe = $deltaQty >= 0
            ? self::TYPE_IN
            : self::TYPE_OUT;

        return self::create([
            'bahan_baku_id' => $bahanBakuId,
            'tipe'          => $tipe,
            'qty'           => abs($deltaQty),
            'tanggal'       => $tanggal,
            'sumber_type'   => self::SUMBER_PENYESUAIAN,
            'sumber_id'     => 0,           // ⬅ DI SINI yang tadinya null
            'note'          => $note,
        ]);
    }
}
