<?php

namespace App\Filament\Resources\PodCampaignEnrollments\Pages;

use App\Filament\Resources\PodCampaignEnrollments\PodCampaignEnrollmentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPodCampaignEnrollment extends EditRecord
{
    protected static string $resource = PodCampaignEnrollmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
