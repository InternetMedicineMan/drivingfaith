<?php

use App\Filament\Resources\LemonSqueezyOrders\LemonSqueezyOrderResource;
use App\Filament\Resources\LemonSqueezySubscriptions\LemonSqueezySubscriptionResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('does not expose lemonsqueezy billing routes', function (string $uri) {
    $user = User::factory()->withPersonalTeam()->create();

    $this
        ->actingAs($user)
        ->get($uri)
        ->assertNotFound();
})->with([
    '/lemonsqueezy/subscription-checkout/123/456',
    '/lemonsqueezy/product-checkout/456',
    '/lemonsqueezy/billing',
]);

it('does not expose paddle billing routes', function (string $uri) {
    $user = User::factory()->withPersonalTeam()->create();

    $this
        ->actingAs($user)
        ->get($uri)
        ->assertNotFound();
})->with([
    '/paddle/checkout/pri_123',
    '/paddle/subscription/pri_123/swap',
    '/paddle/subscription/cancel',
]);

it('hides lemonsqueezy filament resources', function () {
    expect(LemonSqueezyOrderResource::canAccess())->toBeFalse()
        ->and(LemonSqueezyOrderResource::shouldRegisterNavigation())->toBeFalse()
        ->and(LemonSqueezySubscriptionResource::canAccess())->toBeFalse()
        ->and(LemonSqueezySubscriptionResource::shouldRegisterNavigation())->toBeFalse();
});
