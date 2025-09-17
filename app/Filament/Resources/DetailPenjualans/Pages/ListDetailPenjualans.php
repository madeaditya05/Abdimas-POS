<?php

namespace App\Filament\Resources\DetailPenjualans\Pages;

use App\Filament\Resources\DetailPenjualans\DetailPenjualanResource;
// use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDetailPenjualans extends ListRecords
{
    protected static string $resource = DetailPenjualanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
        ];
    }
}
