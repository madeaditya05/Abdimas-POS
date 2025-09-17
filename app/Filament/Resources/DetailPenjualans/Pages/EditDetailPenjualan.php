<?php

namespace App\Filament\Resources\DetailPenjualans\Pages;

use App\Filament\Resources\DetailPenjualans\DetailPenjualanResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditDetailPenjualan extends EditRecord
{
    protected static string $resource = DetailPenjualanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
