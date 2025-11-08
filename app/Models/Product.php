<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'product'; // singular
    protected $fillable = ['name','sku','price','stock','min_stock','is_active'];
}
