<?php

namespace App\Filament\Resources\PembeLianBahans\Pages;

use App\Filament\Resources\PembelianBahans\PembelianBahanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPembelianBahans extends ListRecords
{
    protected static string $resource = PembelianBahanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(), // tampilkan tombol + New
        ];
    }
}
