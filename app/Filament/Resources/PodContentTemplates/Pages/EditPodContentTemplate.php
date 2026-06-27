<?php

namespace App\Filament\Resources\PodContentTemplates\Pages;

use App\Filament\Resources\PodContentTemplates\PodContentTemplateResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPodContentTemplate extends EditRecord
{
    protected static string $resource = PodContentTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
