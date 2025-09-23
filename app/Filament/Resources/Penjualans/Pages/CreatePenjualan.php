<?php

namespace App\Filament\Resources\Penjualans\Pages;

use App\Filament\Resources\Penjualans\PenjualanResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePenjualan extends CreateRecord
{
    protected static string $resource = PenjualanResource::class;

    protected function getRedirectUrl(): string
    {
        // setelah simpan, ke halaman Edit record ini
        return static::$resource::getUrl('edit', ['record' => $this->record]);
    }
}
