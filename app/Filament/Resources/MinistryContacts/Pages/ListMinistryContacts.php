<?php

namespace App\Filament\Resources\MinistryContacts\Pages;

use App\Filament\Resources\MinistryContacts\MinistryContactResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMinistryContacts extends ListRecords
{
    protected static string $resource = MinistryContactResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
