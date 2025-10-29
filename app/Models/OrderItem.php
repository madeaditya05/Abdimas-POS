<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $table = 'order_item'; // ← WAJIB karena tabelmu singular
    protected $fillable = ['order_id','product_id','name','price','qty','line_total'];
    // atau: protected $guarded = [];  // kalau mau bebas mass-assign

    public function order()  { return $this->belongsTo(Order::class,  'order_id','id'); }
    public function product(){ return $this->belongsTo(Product::class,'product_id','id'); }
}
