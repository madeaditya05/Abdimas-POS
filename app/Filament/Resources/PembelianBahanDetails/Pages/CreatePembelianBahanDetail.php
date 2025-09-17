<?php

namespace App\Filament\Resources\PembelianBahanDetails\Pages;

use App\Filament\Resources\PembelianBahanDetails\PembelianBahanDetailResource;
use Filament\Resources\Pages\CreateRecord;
use App\Services\StokMutasiService;

class CreatePembelianBahanDetail extends CreateRecord
{
    protected static string $resource = PembelianBahanDetailResource::class;

    // protected function afterCreate(): void
    // {
    //     $d = $this->record;
    //     app(StokMutasiService::class)->in($d, (int) $d->bahan_baku_id, (float) $d->qty_beli, 'Pembelian Bahan Baku');
    // }

    

}