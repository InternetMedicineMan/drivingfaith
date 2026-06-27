<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PodCampaign extends Model
{
    protected $table = 'pod_campaigns';

    protected $fillable = [
        'team_id',
        'name',
        'slug',
        'source_key',
        'status',
        'description',
        'starts_at',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function mailings(): HasMany
    {
        return $this->hasMany(PodCampaignMailing::class, 'campaign_id')->orderBy('sequence');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(PodCampaignEnrollment::class, 'campaign_id');
    }
}
