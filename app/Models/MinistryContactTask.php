<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MinistryContactTask extends Model
{
    protected $fillable = [
        'team_id',
        'contact_id',
        'created_from_event_id',
        'assigned_to_user_id',
        'completed_by_user_id',
        'completion_event_id',
        'type',
        'status',
        'priority',
        'title',
        'description',
        'due_at',
        'completed_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
            'due_at' => 'datetime',
            'metadata' => 'array',
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

    public function createdFromEvent(): BelongsTo
    {
        return $this->belongsTo(MinistryContactEvent::class, 'created_from_event_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by_user_id');
    }

    public function completionEvent(): BelongsTo
    {
        return $this->belongsTo(MinistryContactEvent::class, 'completion_event_id');
    }

    protected static function booted(): void
    {
        static::creating(function (MinistryContactTask $task): void {
            if ($task->team_id === null && $task->contact_id !== null) {
                $task->team_id = $task->contact?->team_id;
            }
        });
    }
}
