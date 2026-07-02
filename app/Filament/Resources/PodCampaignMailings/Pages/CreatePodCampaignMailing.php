<?php

namespace App\Filament\Resources\PodCampaignMailings\Pages;

use App\Filament\Resources\PodCampaignMailings\PodCampaignMailingResource;
use App\Models\PodCampaignMailing;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreatePodCampaignMailing extends CreateRecord
{
    protected static string $resource = PodCampaignMailingResource::class;

    protected function beforeCreate(): void
    {
        $campaignId = (int) $this->data['campaign_id'];
        $sequence = (int) $this->data['sequence'];

        if (! PodCampaignMailing::query()
            ->where('campaign_id', $campaignId)
            ->where('sequence', $sequence)
            ->exists()) {
            return;
        }

        Notification::make()
            ->danger()
            ->title('Sequence already used')
            ->body("This campaign already has mailing step {$sequence}. Use the next sequence number instead.")
            ->send();

        $this->halt();
    }
}
