<?php

namespace App\Filament\Resources\DetailPenjualans\Pages;

use App\Filament\Resources\DetailPenjualans\DetailPenjualanResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewDetailPenjualan extends ViewRecord
{
    protected static string $resource = DetailPenjualanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
