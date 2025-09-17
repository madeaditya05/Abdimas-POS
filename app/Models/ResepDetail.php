<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class ResepDetail extends Model
{
    /** @use HasFactory<\Database\Factories\ResepDetailFactory> */
    use HasFactory;

    protected $table = 'resep_detail';

    protected $fillable = [
        'resep_id',
        'bahan_baku_id',
        'qty_per_porsi',
        'keterangan',
    ];

    public function resep(): BelongsTo
    {
        return $this->belongsTo(Resep::class);
    }

    public function bahanBaku(): BelongsTo
    {
        return $this->belongsTo(BahanBaku::class);
    }
}
