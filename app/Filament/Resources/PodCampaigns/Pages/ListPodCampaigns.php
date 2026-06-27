<?php

namespace App\Filament\Resources\PodCampaigns\Pages;

use App\Filament\Resources\PodCampaigns\PodCampaignResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPodCampaigns extends ListRecords
{
    protected static string $resource = PodCampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
