<?php

use App\Filament\Resources\Invoices\InvoiceResource;
use App\Models\Price;
use App\Models\Product;
use App\Models\StripeOrder;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Cashier\Subscription;

uses(RefreshDatabase::class);

it('prefills stripe subscription invoices from the billed team', function () {
    $owner = User::factory()->create([
        'email' => 'pastor@example.com',
    ]);

    $team = Team::factory()->create([
        'name' => 'Grace Church',
        'user_id' => $owner->id,
        'personal_team' => false,
    ]);

    $product = Product::query()->create([
        'stripe_id' => 'prod_ministry',
        'name' => 'Ministry Plan',
        'active' => true,
        'type' => 'service',
    ]);

    Price::query()->create([
        'stripe_id' => 'price_ministry',
        'product_id' => $product->stripe_id,
        'active' => true,
        'currency' => 'usd',
        'type' => 'recurring',
        'billing_scheme' => 'per_unit',
        'unit_amount' => 2900,
    ]);

    $subscription = $team->subscriptions()->create([
        'type' => 'default',
        'stripe_id' => 'sub_grace_church',
        'stripe_status' => 'active',
        'stripe_price' => 'price_ministry',
        'quantity' => 1,
    ]);

    $url = InvoiceResource::stripeSubscriptionCreateUrl($subscription);

    parse_str(parse_url($url, PHP_URL_QUERY) ?? '', $query);

    expect($query)
        ->toMatchArray([
            'team_id' => (string) $team->id,
            'user_id' => (string) $owner->id,
            'customer_name' => 'Grace Church',
            'customer_email' => 'pastor@example.com',
            'currency' => 'usd',
            'item_description' => 'Ministry Plan',
            'quantity' => '1',
            'unit_price' => '2900',
            'provider' => 'stripe',
            'provider_type' => 'subscription',
            'provider_id' => 'sub_grace_church',
        ]);
});

it('preserves legacy user invoice prefill when no billed team is attached', function () {
    $user = User::factory()->create([
        'name' => 'Legacy Pastor',
        'email' => 'legacy@example.com',
    ]);

    $subscription = Subscription::query()->create([
        'user_id' => $user->id,
        'type' => 'default',
        'stripe_id' => 'sub_legacy_user',
        'stripe_status' => 'active',
        'stripe_price' => 'price_legacy',
        'quantity' => 1,
    ]);

    $url = InvoiceResource::stripeSubscriptionCreateUrl($subscription);

    parse_str(parse_url($url, PHP_URL_QUERY) ?? '', $query);

    expect($query)
        ->toMatchArray([
            'user_id' => (string) $user->id,
            'customer_name' => 'Legacy Pastor',
            'customer_email' => 'legacy@example.com',
            'provider' => 'stripe',
            'provider_type' => 'subscription',
            'provider_id' => 'sub_legacy_user',
        ])
        ->not->toHaveKey('team_id');
});

it('prefills stripe order invoices from the billed team', function () {
    $owner = User::factory()->create([
        'email' => 'orders@example.com',
    ]);

    $team = Team::factory()->create([
        'name' => 'Mercy Fellowship',
        'user_id' => $owner->id,
        'personal_team' => false,
    ]);

    $product = Product::query()->create([
        'stripe_id' => 'prod_letters',
        'name' => 'Letter Credits',
        'active' => true,
        'type' => 'service',
    ]);

    Price::query()->create([
        'stripe_id' => 'price_letters',
        'product_id' => $product->stripe_id,
        'active' => true,
        'currency' => 'usd',
        'type' => 'one_time',
        'billing_scheme' => 'per_unit',
        'unit_amount' => 4500,
    ]);

    $order = StripeOrder::query()->create([
        'stripe_id' => 'cs_letters',
        'user_id' => $owner->id,
        'team_id' => $team->id,
        'price_id' => 'price_letters',
        'amount' => 4500,
        'currency' => 'usd',
        'status' => 'complete',
        'payment_status' => 'paid',
    ]);

    $url = InvoiceResource::stripeOrderCreateUrl($order);

    parse_str(parse_url($url, PHP_URL_QUERY) ?? '', $query);

    expect($query)
        ->toMatchArray([
            'team_id' => (string) $team->id,
            'user_id' => (string) $owner->id,
            'customer_name' => 'Mercy Fellowship',
            'customer_email' => 'orders@example.com',
            'currency' => 'usd',
            'item_description' => 'Letter Credits',
            'quantity' => '1',
            'unit_price' => '4500',
            'provider' => 'stripe',
            'provider_type' => 'order',
            'provider_id' => 'cs_letters',
        ]);
});
