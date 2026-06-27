<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('requires a current team before starting stripe subscription checkout', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('stripe.subscription.checkout', ['price' => 'price_ministry']));

    $response->assertRedirect(route('teams.create'));
});

it('requires a non personal team before starting stripe subscription checkout', function () {
    $user = User::factory()->withPersonalTeam()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('stripe.subscription.checkout', ['price' => 'price_ministry']));

    $response->assertRedirect(route('teams.create'));
});
