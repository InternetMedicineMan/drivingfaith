<?php

namespace App\Filament\Resources\PodCampaignMailings\Pages;

use App\Filament\Resources\PodCampaignMailings\PodCampaignMailingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPodCampaignMailings extends ListRecords
{
    protected static string $resource = PodCampaignMailingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
