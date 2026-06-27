<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PodReply extends Model
{
    protected $table = 'pod_replies';

    protected $fillable = [
        'team_id',
        'campaign_enrollment_id',
        'enrollment_mailing_id',
        'campaign_mailing_id',
        'contact_id',
        'received_at',
        'channel',
        'summary',
        'raw_content',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'received_at' => 'datetime',
        ];
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(PodCampaignEnrollment::class, 'campaign_enrollment_id');
    }
}
