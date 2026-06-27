<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PodContentTemplate extends Model
{
    use SoftDeletes;

    protected $table = 'pod_content_templates';

    protected $fillable = [
        'team_id',
        'type',
        'name',
        'slug',
        'version',
        'status',
        'provider',
        'provider_template_id',
        'html_path',
        'html_content',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'version' => 'integer',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function coverLetterMailings(): HasMany
    {
        return $this->hasMany(PodCampaignMailing::class, 'cover_letter_template_id');
    }

    public function bibleStudyMailings(): HasMany
    {
        return $this->hasMany(PodCampaignMailing::class, 'bible_study_template_id');
    }
}
