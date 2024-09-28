<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use App\Models\Passport\Client;
use Laravel\Passport\Passport;

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
		Passport::personalAccessTokensExpireIn(now()->addMonths(6));
		Passport::useClientModel(Client::class);
		Passport::tokensCan([]);
        Vite::prefetch(concurrency: 3);
    }
}
