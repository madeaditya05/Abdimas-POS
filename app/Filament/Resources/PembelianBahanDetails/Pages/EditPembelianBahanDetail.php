<?php

namespace App\Filament\Resources\PembelianBahanDetails\Pages;

use App\Filament\Resources\PembelianBahanDetails\PembelianBahanDetailResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;
use App\Services\StokMutasiService;

class EditPembelianBahanDetail extends EditRecord
{
    protected static string $resource = PembelianBahanDetailResource::class;

    protected function afterSave(): void
    {
        $d = $this->record;
        app(StokMutasiService::class)->remove($d);
        app(StokMutasiService::class)->in($d, (int) $d->bahan_baku_id, (float) $d->qty_beli, 'PURCHASE');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->after(fn () => app(StokMutasiService::class)->remove($this->record)),
        ];
    }
}
