<?php

namespace App\Filament\Resources\PodCampaignMailings\Pages;

use App\Filament\Resources\PodCampaignMailings\PodCampaignMailingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPodCampaignMailing extends EditRecord
{
    protected static string $resource = PodCampaignMailingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
