<?php

namespace App\Filament\Resources\ResepDetails\Pages;

use App\Filament\Resources\ResepDetails\ResepDetailResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditResepDetail extends EditRecord
{
    protected static string $resource = ResepDetailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
