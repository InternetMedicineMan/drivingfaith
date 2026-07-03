<?php

namespace App\Filament\Resources\PodPrintLayoutTemplates\Pages;

use App\Filament\Resources\PodPrintLayoutTemplates\PodPrintLayoutTemplateResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPodPrintLayoutTemplate extends EditRecord
{
    protected static string $resource = PodPrintLayoutTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
