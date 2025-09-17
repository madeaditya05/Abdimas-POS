<?php

namespace App\Filament\Resources\StokMutasis\Pages;

use App\Filament\Resources\StokMutasis\StokMutasiResource;
use Filament\Resources\Pages\ListRecords;

class ListStokMutasis extends ListRecords
{
    protected static string $resource = StokMutasiResource::class;

    protected function getHeaderActions(): array
    {
        return []; // sembunyikan tombol Create
    }
}
