<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PodCampaignMailing extends Model
{
    protected $table = 'pod_campaign_mailings';

    protected $fillable = [
        'campaign_id',
        'name',
        'sequence',
        'delay_days_after_previous',
        'pause_until_reply',
        'cover_letter_template_id',
        'bible_study_template_id',
        'status',
        'description',
        'provider',
        'provider_template_id',
        'mail_class',
        'color',
        'double_sided',
        'address_placement',
        'return_envelope',
        'perforated_page',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'color' => 'boolean',
            'double_sided' => 'boolean',
            'metadata' => 'array',
            'pause_until_reply' => 'boolean',
            'return_envelope' => 'boolean',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(PodCampaign::class, 'campaign_id');
    }

    public function coverLetterTemplate(): BelongsTo
    {
        return $this->belongsTo(PodContentTemplate::class, 'cover_letter_template_id');
    }

    public function bibleStudyTemplate(): BelongsTo
    {
        return $this->belongsTo(PodContentTemplate::class, 'bible_study_template_id');
    }

    public function pages(): HasMany
    {
        return $this->hasMany(PodCampaignMailingPage::class, 'campaign_mailing_id')->orderBy('page_number');
    }
}
