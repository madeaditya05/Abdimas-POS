<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = 'payment'; // ← WAJIB karena tabelmu singular
    protected $guarded = [];
    protected $casts = ['meta' => 'array', 'paid_at' => 'datetime'];
    public function penjualan(){ return $this->belongsTo(\App\Models\Penjualan::class); }


}
