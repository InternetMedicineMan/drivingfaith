<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;
use Laravel\Jetstream\Events\TeamCreated;
use Laravel\Jetstream\Events\TeamDeleted;
use Laravel\Jetstream\Events\TeamUpdated;
use Laravel\Jetstream\Team as JetstreamTeam;

class Team extends JetstreamTeam
{
    use Billable;
    use HasFactory;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'personal_team',
    ];

    /**
     * The event map for the model.
     *
     * @var array<string, class-string>
     */
    protected $dispatchesEvents = [
        'created' => TeamCreated::class,
        'updated' => TeamUpdated::class,
        'deleted' => TeamDeleted::class,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'personal_team' => 'boolean',
            'trial_ends_at' => 'datetime',
        ];
    }

    public function stripeName(): ?string
    {
        return $this->name;
    }

    public function stripeEmail(): ?string
    {
        return $this->owner?->email;
    }

    /**
     * @return array<string, string>
     */
    public function stripeMetadata(): array
    {
        return [
            'team_id' => (string) $this->id,
            'owner_user_id' => (string) $this->user_id,
        ];
    }

    public function podCampaigns(): HasMany
    {
        return $this->hasMany(PodCampaign::class);
    }

    public function podContentTemplates(): HasMany
    {
        return $this->hasMany(PodContentTemplate::class);
    }

    public function ministryContacts(): HasMany
    {
        return $this->hasMany(MinistryContact::class);
    }

    public function ministryContactEvents(): HasMany
    {
        return $this->hasMany(MinistryContactEvent::class);
    }

    public function podCampaignEnrollments(): HasMany
    {
        return $this->hasMany(PodCampaignEnrollment::class);
    }
}
