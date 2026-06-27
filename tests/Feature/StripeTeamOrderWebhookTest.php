<?php

use App\Listeners\StripeEventListener;
use App\Models\Price;
use App\Models\Product;
use App\Models\Team;
use App\Models\User;
use App\Notifications\SubscriptionCreatedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Cashier\Events\WebhookReceived;

uses(RefreshDatabase::class);

it('creates stripe orders for team checkout sessions', function () {
    Notification::fake();

    $owner = User::factory()->create();

    $team = Team::factory()->create([
        'user_id' => $owner->id,
        'personal_team' => false,
        'stripe_id' => 'cus_team_123',
    ]);

    $product = Product::query()->create([
        'stripe_id' => 'prod_mailings',
        'name' => 'Mailing Credits',
        'active' => true,
        'type' => 'service',
    ]);

    Price::query()->create([
        'stripe_id' => 'price_mailings',
        'product_id' => $product->stripe_id,
        'active' => true,
        'currency' => 'usd',
        'type' => 'one_time',
        'billing_scheme' => 'per_unit',
        'unit_amount' => 9900,
    ]);

    app(StripeEventListener::class)->handle(new WebhookReceived([
        'type' => 'checkout.session.completed',
        'data' => [
            'object' => [
                'id' => 'cs_team_mailings',
                'customer' => 'cus_team_123',
                'mode' => 'payment',
                'amount_total' => 9900,
                'currency' => 'usd',
                'status' => 'complete',
                'payment_status' => 'paid',
                'metadata' => [
                    'price' => 'price_mailings',
                    'team_id' => (string) $team->id,
                    'user_id' => (string) $owner->id,
                ],
            ],
        ],
    ]));

    $this->assertDatabaseHas('stripe_orders', [
        'stripe_id' => 'cs_team_mailings',
        'user_id' => $owner->id,
        'team_id' => $team->id,
        'price_id' => 'price_mailings',
        'amount' => 9900,
        'currency' => 'usd',
        'status' => 'complete',
        'payment_status' => 'paid',
    ]);

    Notification::assertSentTo($owner, SubscriptionCreatedNotification::class);
});
