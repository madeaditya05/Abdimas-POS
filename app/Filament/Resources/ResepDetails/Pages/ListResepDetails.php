<?php

namespace App\Filament\Resources\ResepDetails\Pages;

use App\Filament\Resources\ResepDetails\ResepDetailResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListResepDetails extends ListRecords
{
    protected static string $resource = ResepDetailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
