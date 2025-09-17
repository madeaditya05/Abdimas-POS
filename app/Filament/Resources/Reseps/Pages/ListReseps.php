<?php

namespace App\Filament\Resources\Reseps\Pages;

use App\Filament\Resources\Reseps\ResepResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListReseps extends ListRecords
{
    protected static string $resource = ResepResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
