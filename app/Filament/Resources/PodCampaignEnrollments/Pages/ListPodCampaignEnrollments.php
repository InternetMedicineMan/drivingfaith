<?php

namespace App\Filament\Resources\PodCampaignEnrollments\Pages;

use App\Filament\Resources\PodCampaignEnrollments\PodCampaignEnrollmentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPodCampaignEnrollments extends ListRecords
{
    protected static string $resource = PodCampaignEnrollmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
