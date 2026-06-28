<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PodEnrollmentMailing extends Model
{
    protected $table = 'pod_enrollment_mailings';

    protected $fillable = [
        'team_id',
        'campaign_enrollment_id',
        'campaign_mailing_id',
        'contact_id',
        'sequence',
        'status',
        'scheduled_for',
        'cover_letter_template_id',
        'bible_study_template_id',
        'override_cover_letter_template_id',
        'override_cover_letter_html',
        'cover_letter_override_reason',
        'rendered_html',
        'rendered_at',
        'sent_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'rendered_at' => 'datetime',
            'scheduled_for' => 'date',
            'sent_at' => 'datetime',
        ];
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(PodCampaignEnrollment::class, 'campaign_enrollment_id');
    }

    public function campaignMailing(): BelongsTo
    {
        return $this->belongsTo(PodCampaignMailing::class, 'campaign_mailing_id');
    }

    public function coverLetterTemplate(): BelongsTo
    {
        return $this->belongsTo(PodContentTemplate::class, 'cover_letter_template_id');
    }

    public function overrideCoverLetterTemplate(): BelongsTo
    {
        return $this->belongsTo(PodContentTemplate::class, 'override_cover_letter_template_id');
    }
}
