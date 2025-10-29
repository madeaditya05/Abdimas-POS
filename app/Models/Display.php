<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Display extends Model
{
    protected $table = 'display';
    protected $fillable = ['code','order_no'];
}
