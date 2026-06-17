<?php

use App\Models\ComingSoonEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders the driving faith landing page', function () {
    $this->withoutVite();

    $this->get('/')
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('Home')
            ->where('canLogin', true)
            ->where('seo.title', 'DrivingFaith - Run your whole church from one place')
        );
});

it('adds an email address to the waitlist', function () {
    $this->from('/')
        ->post(route('coming-soon.store'), [
            'email' => 'pastor@example.org',
        ])
        ->assertRedirect('/');

    $this->assertDatabaseHas(ComingSoonEmail::class, [
        'email' => 'pastor@example.org',
    ]);
});

it('validates waitlist email addresses', function () {
    $this->from('/')
        ->post(route('coming-soon.store'), [
            'email' => 'not-an-email',
        ])
        ->assertRedirect('/')
        ->assertSessionHasErrors('email');
});

it('prevents duplicate waitlist emails', function () {
    ComingSoonEmail::create([
        'email' => 'pastor@example.org',
    ]);

    $this->from('/')
        ->post(route('coming-soon.store'), [
            'email' => 'pastor@example.org',
        ])
        ->assertRedirect('/')
        ->assertSessionHasErrors('email');
});
