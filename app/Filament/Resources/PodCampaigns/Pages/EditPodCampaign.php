<?php

namespace App\Filament\Resources\PodCampaigns\Pages;

use App\Filament\Resources\PodCampaigns\PodCampaignResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPodCampaign extends EditRecord
{
    protected static string $resource = PodCampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
