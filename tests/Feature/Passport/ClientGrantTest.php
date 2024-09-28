<?php

namespace Tests\Feature\Passport;

// use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Models\User;
use Laravel\Passport\{Passport, Client, ClientRepository, Token};
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see https://laravel.com/docs/11.x/passport#client-credentials-grant-tokens
 * 
 * The client credentials grant is suitable for machine-to-machine authentication. 
 * For example, you might use this grant in a scheduled job which is performing maintenance tasks over an API.
 */
class ClientGrantTest extends TestCase
{
	protected ClientRepository $clients;

	protected User $user;

	protected function setUp(): void
    {
        parent::setUp();

		Passport::$scopes = [
			'sample-scope' => 'Place orders',
		];

		$this->clients = app(ClientRepository::class);

		$this->user = User::factory()->create();
    }
    
    #[Test]
    public function get_clients_for_user(): void
    {
		$this->actingAs($this->user,'web');

		$client = $this->clients->create(
			userId: $this->user->id,
			name : 'AUTH ClientCredentials Grant Client',
			redirect : ''
		);

		$response = $this
			->postJson(route('passport.token'), [
				'grant_type' => 'client_credentials',
				'client_id' => $client->id,
				'client_secret' => $client->secret,
				'scope' => '',
			])
			->assertJsonStructure([
				'token_type',
				'access_token',
				'expires_in',
			]);

		// $resp = json_decode($response->content());
    }
}
