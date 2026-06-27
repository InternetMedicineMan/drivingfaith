<?php

namespace App\Filament\Resources\MinistryContacts\Pages;

use App\Filament\Resources\MinistryContacts\MinistryContactResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMinistryContact extends EditRecord
{
    protected static string $resource = MinistryContactResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
