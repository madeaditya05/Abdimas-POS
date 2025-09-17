<?php

namespace App\Filament\Resources\StokMutasis\Pages;

use App\Filament\Resources\StokMutasis\StokMutasiResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditStokMutasi extends EditRecord
{
    protected static string $resource = StokMutasiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
