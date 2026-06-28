<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MinistryContact extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'team_id',
        'external_key',
        'status',
        'first_source_type',
        'first_source_name',
        'first_contacted_at',
        'first_name',
        'last_name',
        'organization',
        'email',
        'phone',
        'address1',
        'address2',
        'city',
        'state',
        'zip',
        'country',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'first_contacted_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(MinistryContactEvent::class, 'contact_id')->latest('occurred_at');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(MinistryContactTask::class, 'contact_id')->orderBy('status')->orderBy('due_at');
    }

    public function podCampaignEnrollments(): HasMany
    {
        return $this->hasMany(PodCampaignEnrollment::class, 'contact_id');
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }
}
