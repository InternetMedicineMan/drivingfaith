<?php

use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows the team workspace dashboard for authenticated users', function () {
    $user = User::factory()->withPersonalTeam()->create([
        'name' => 'Zion Pastor',
    ]);
    $owner = User::factory()->create();

    $team = Team::factory()->create([
        'name' => 'Grace Church',
        'user_id' => $owner->id,
        'personal_team' => false,
    ]);

    $user->teams()->attach($team, ['role' => 'admin']);
    $user->switchTeam($team);

    $team->subscriptions()->create([
        'type' => 'default',
        'stripe_id' => 'sub_grace_church',
        'stripe_status' => 'active',
        'stripe_price' => 'price_grace_church',
        'quantity' => 1,
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->where('auth.user.current_team.name', 'Grace Church')
            ->where('auth.user.all_teams.0.name', 'Grace Church')
            ->where('hasBillableTeam', true)
            ->where('teamBilling.subscribed', true)
            ->where('teamBilling.status', 'active')
        );
});

it('renders the dashboard for users without a current team', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->where('auth.user.current_team', null)
            ->where('hasBillableTeam', false)
            ->where('teamBilling.subscribed', false)
            ->where('teamBilling.status', null)
        );
});

it('does not show team billing prompts for a personal team dashboard', function () {
    $user = User::factory()->withPersonalTeam()->create();

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->where('auth.user.current_team.personal_team', true)
            ->where('hasBillableTeam', false)
            ->where('teamBilling.subscribed', false)
            ->where('teamBilling.status', null)
        );
});
