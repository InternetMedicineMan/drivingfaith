<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PodCampaignEnrollment extends Model
{
    protected $table = 'pod_campaign_enrollments';

    protected $fillable = [
        'team_id',
        'campaign_id',
        'contact_id',
        'status',
        'enrolled_at',
        'completed_at',
        'paused_until',
        'next_mailing_id',
        'next_send_on',
        'current_sequence',
        'reply_required_by_mailing_id',
        'reply_required_at',
        'reply_received_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
            'current_sequence' => 'integer',
            'enrolled_at' => 'datetime',
            'metadata' => 'array',
            'next_send_on' => 'date',
            'paused_until' => 'date',
            'reply_received_at' => 'datetime',
            'reply_required_at' => 'datetime',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(PodCampaign::class, 'campaign_id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(MinistryContact::class, 'contact_id');
    }

    public function nextMailing(): BelongsTo
    {
        return $this->belongsTo(PodCampaignMailing::class, 'next_mailing_id');
    }

    public function replyRequiredByMailing(): BelongsTo
    {
        return $this->belongsTo(PodCampaignMailing::class, 'reply_required_by_mailing_id');
    }

    public function enrollmentMailings(): HasMany
    {
        return $this->hasMany(PodEnrollmentMailing::class, 'campaign_enrollment_id');
    }
}
