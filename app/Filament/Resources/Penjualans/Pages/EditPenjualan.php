<?php

namespace App\Filament\Resources\Penjualans\Pages;

use App\Filament\Resources\Penjualans\PenjualanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPenjualan extends EditRecord
{
    protected static string $resource = PenjualanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('print')
                ->label('Cetak Tiket')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->url(fn () => route('tickets.penjualan', $this->record))
                ->openUrlInNewTab(),
        ];
    }

    // setelah user klik "Simpan" di Edit, pastikan total ke-update
    protected function afterSave(): void
    {
        $this->record->recalcTotal();
    }
}
