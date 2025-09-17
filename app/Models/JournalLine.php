<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalLine extends Model
{
    // match migration: snake_case single
    protected $table = 'journal_line';

    protected $fillable = [
        'journal_entry_id', 'account_id',
        'debit', 'credit', 'memo', 'line_no',
    ];

    protected $casts = [
        'debit'   => 'float',
        'credit'  => 'float',
        'line_no' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->line_no)) {
                $max = static::where('journal_entry_id', $model->journal_entry_id)->max('line_no');
                $model->line_no = ($max ?? 0) + 1;
            }
        });
    }

    public function entry(): BelongsTo
    {
        // FK -> journal_entry.id
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id', 'id');
    }

    public function coa(): BelongsTo
    {
        // Model COA kamu: App\Models\ChartOfAccount (tabel default: chart_of_account)
        return $this->belongsTo(\App\Models\ChartOfAccount::class, 'account_id', 'id');
    }
}
