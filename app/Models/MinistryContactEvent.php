<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MinistryContactEvent extends Model
{
    protected $fillable = [
        'team_id',
        'contact_id',
        'user_id',
        'eventable_type',
        'eventable_id',
        'type',
        'source',
        'source_label',
        'occurred_at',
        'summary',
        'notes',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'occurred_at' => 'datetime',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(MinistryContact::class, 'contact_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function eventable(): MorphTo
    {
        return $this->morphTo();
    }

    protected static function booted(): void
    {
        static::creating(function (MinistryContactEvent $event): void {
            if ($event->team_id === null && $event->contact_id !== null) {
                $event->team_id = $event->contact?->team_id;
            }
        });
    }
}
