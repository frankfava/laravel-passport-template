<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use App\Models\Passport\Client;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
	const PersonalAccessClientName = 'AUTH Personal Access Client';

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
		Passport::tokensCan([
			'sample-scope' => 'Sample Scope',
		]);
        Vite::prefetch(concurrency: 3);

		if(Client::where('name',self::PersonalAccessClientName)->doesntExist()) {
			app(ClientRepository::class)->createPersonalAccessClient(
				userId: null,
				name : self::PersonalAccessClientName,
				redirect : url('/')
			);
		}
    }
}
