<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PodDelivery extends Model
{
    protected $table = 'pod_deliveries';

    protected $fillable = [
        'team_id',
        'campaign_enrollment_id',
        'enrollment_mailing_id',
        'campaign_mailing_id',
        'contact_id',
        'status',
        'scheduled_for',
        'sent_at',
        'failed_at',
        'provider',
        'provider_id',
        'idempotency_key',
        'attempt_count',
        'cost_cents',
        'error_message',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'failed_at' => 'datetime',
            'metadata' => 'array',
            'scheduled_for' => 'date',
            'sent_at' => 'datetime',
        ];
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(PodCampaignEnrollment::class, 'campaign_enrollment_id');
    }
}
