<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PodCampaignMailingPage extends Model
{
    protected $table = 'pod_campaign_mailing_pages';

    protected $fillable = [
        'campaign_mailing_id',
        'page_number',
        'name',
        'html_path',
        'html_content',
        'paper_size',
        'orientation',
        'expected_page_count',
        'checksum',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'expected_page_count' => 'integer',
            'metadata' => 'array',
        ];
    }

    public function mailing(): BelongsTo
    {
        return $this->belongsTo(PodCampaignMailing::class, 'campaign_mailing_id');
    }
}
