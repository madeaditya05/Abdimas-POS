<?php

namespace App\Filament\Resources\JournalEntries\Pages;

use App\Filament\Resources\JournalEntries\JournalEntryResource;
use Filament\Resources\Pages\EditRecord;

class EditJournalEntry extends EditRecord
{
    protected static string $resource = JournalEntryResource::class;

    protected function beforeSave(): void
    {
        $lines = collect($this->data['lines'] ?? []);

        if ($lines->isEmpty()) {
            $this->addError('data.lines', 'Minimal 2 baris (Debit & Kredit).');
            $this->halt();
        }

        if (empty($this->data['date'])) {
            $this->addError('data.date', 'Tanggal wajib diisi.');
            $this->halt();
        }

        $totalDebit  = 0.0;
        $totalCredit = 0.0;

        foreach ($lines as $i => $line) {
            $d = (float) ($line['debit']  ?? 0);
            $c = (float) ($line['credit'] ?? 0);

            if ($d > 0 && $c > 0) {
                $this->addError("data.lines.$i.debit", 'Pilih salah satu: Debit atau Kredit.');
                $this->addError("data.lines.$i.credit", ' ');
                $this->halt();
            }

            if ($d <= 0 && $c <= 0) {
                $this->addError("data.lines.$i.debit", 'Isi Debit atau Kredit.');
                $this->addError("data.lines.$i.credit", ' ');
                $this->halt();
            }

            $totalDebit  += $d > 0 ? $d : 0;
            $totalCredit += $c > 0 ? $c : 0;
        }

        if (round($totalDebit, 2) !== round($totalCredit, 2) || $totalDebit <= 0) {
            $this->addError('data.lines', 'Total Debit dan Kredit harus seimbang dan > 0.');
            $this->halt();
        }
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (!empty($data['date']) && $data['date'] instanceof \Carbon\Carbon) {
            $data['date'] = $data['date']->toDateString();
        }

        $data['lines'] = collect($data['lines'] ?? [])
            ->map(function ($line) {
                $d = (float) ($line['debit']  ?? 0);
                $c = (float) ($line['credit'] ?? 0);

                $line['debit']  = $d > 0 ? round($d, 2) : 0;
                $line['credit'] = $c > 0 ? round($c, 2) : 0;

                return $line;
            })
            ->all();

        return $data;
    }
}
