<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StokMutasi extends Model
{
    protected $table = 'stok_mutasi'; // karena bukan jamak

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
            ->withDefault(['nama_bahan' => '(bahan tidak ada)']); // optional biar gak null
    }
}
