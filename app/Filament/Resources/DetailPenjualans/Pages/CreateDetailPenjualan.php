<?php

namespace App\Filament\Resources\DetailPenjualans\Pages;

use App\Filament\Resources\DetailPenjualans\DetailPenjualanResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDetailPenjualan extends CreateRecord
{
    protected static string $resource = DetailPenjualanResource::class;

    protected function afterCreate(): void
    
    {
        app(\App\Services\StokMutasiService::class)
            ->out($this->record, $this->record->bahan_baku_id, $this->record->qty, 'Penjualan');
    }

}
