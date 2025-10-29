<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'order'; // singular
    protected $fillable = ['order_no','subtotal','discount','tax','grand_total','status','payment_method'];

    public function items()
    {
        return $this->hasMany(\App\Models\OrderItem::class, 'order_id', 'id');
    }
}
