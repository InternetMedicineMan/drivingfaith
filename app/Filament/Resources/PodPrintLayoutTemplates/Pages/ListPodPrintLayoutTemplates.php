<?php

namespace App\Filament\Resources\PodPrintLayoutTemplates\Pages;

use App\Filament\Resources\PodPrintLayoutTemplates\PodPrintLayoutTemplateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPodPrintLayoutTemplates extends ListRecords
{
    protected static string $resource = PodPrintLayoutTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
