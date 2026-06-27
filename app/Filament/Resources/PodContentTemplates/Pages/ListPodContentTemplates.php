<?php

namespace App\Filament\Resources\PodContentTemplates\Pages;

use App\Filament\Resources\PodContentTemplates\PodContentTemplateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPodContentTemplates extends ListRecords
{
    protected static string $resource = PodContentTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
