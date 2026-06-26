<?php

namespace App\Providers;

use App\Models\Team;
use App\Services\SchemaOrg;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::unguard();
        Cashier::useCustomerModel(Team::class);

        // View::share(['schema' => ['organization' => app(SchemaOrg::class)->organization()]]);
    }
}
