<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClosingPeriod extends Model
{
    protected $table = 'closing_periods';

    protected $fillable = [
        'period_start',
        'period_end',
        'is_closed',
        'closed_at',
        'closed_by',
        'begin_inventory_value',
        'purchases_value',
        'end_inventory_value',
        'cogs_value',
        'journal_entry_id',
        'meta',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end'   => 'date',
        'is_closed'    => 'boolean',
        'closed_at'    => 'datetime',
        'meta'         => 'array',
    ];
}
