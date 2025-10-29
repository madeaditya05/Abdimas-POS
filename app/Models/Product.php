<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'product'; // singular
    protected $fillable = ['name','sku','price','is_active'];
}
