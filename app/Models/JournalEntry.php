<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JournalEntry extends Model
{
    // match ke migration: snake_case tanpa "s"
    protected $table = 'journal_entry';

    protected $fillable = [
        'entry_no', 'date', 'ref_no', 'memo',
        'source_type', 'source_id',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->entry_no)) {
                $prefix = 'JU-' . now()->format('Ym') . '-';
                $last   = static::where('entry_no', 'like', $prefix.'%')
                    ->orderByDesc('entry_no')->value('entry_no');
                $next = $last ? ((int) substr($last, -4)) + 1 : 1;
                $model->entry_no = $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
            }

            if (empty($model->ref_no)) {
                $rPrefix = 'REF-' . now()->format('Ym') . '-';
                $lastRef = static::where('ref_no', 'like', $rPrefix.'%')
                    ->orderByDesc('ref_no')->value('ref_no');
                $rNext = $lastRef ? ((int) substr($lastRef, -4)) + 1 : 1;
                $model->ref_no = $rPrefix . str_pad($rNext, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function lines(): HasMany
    {
        // JournalLine tabelnya nanti juga snake_case tanpa "s" → 'journal_line'
        return $this->hasMany(JournalLine::class, 'journal_entry_id', 'id')
            ->orderBy('line_no');
    }
}
