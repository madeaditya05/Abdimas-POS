<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChartOfAccount extends Model
{
    use HasFactory;

    // Pastikan pakai snake_case plural
    protected $table = 'chart_of_account';

    protected $fillable = [
        'code', 'name', 'type', 'normal_side', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
