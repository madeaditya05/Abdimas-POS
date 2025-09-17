<?php

namespace App\Filament\Resources\PembelianBahans\Pages;

use App\Filament\Resources\PembelianBahans\PembelianBahanResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPembelianBahan extends EditRecord
{
    protected static string $resource = PembelianBahanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
