<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashReconciliation extends Model
{
    protected $table = 'cash_reconciliations';

    protected $fillable = [
        'user_id',
        'range_start',
        'range_end',
        'app_cash_total',
        'physical_cash',
        'difference',
        'notes',
    ];

    protected $casts = [
        'range_start'    => 'datetime',
        'range_end'      => 'datetime',
        'app_cash_total' => 'decimal:2',
        'physical_cash'  => 'decimal:2',
        'difference'     => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

