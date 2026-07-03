<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PodPrintLayoutTemplate extends Model
{
    protected $table = 'pod_print_layout_templates';

    protected $fillable = [
        'team_id',
        'scope',
        'name',
        'slug',
        'mailing_format',
        'slot',
        'status',
        'html_shell',
        'css',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function campaignMailings(): HasMany
    {
        return $this->hasMany(PodCampaignMailing::class, 'print_layout_template_id');
    }
}
