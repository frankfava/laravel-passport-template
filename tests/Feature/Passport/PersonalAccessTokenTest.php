<?php

namespace Tests\Feature\Passport;

// use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Models\User;
use Laravel\Passport\{Passport, Client, ClientRepository};
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PersonalAccessTokenTest extends TestCase
{
	protected Client $client;

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

		$this->client = $this->clients->createPersonalAccessClient(
			userId: null,
			name : 'AUTH Personal Access Client',
			redirect : url('/')
		);
    }

    #[Test]
    public function get_scopes(): void
    {
		$this->actingAs($this->user,'web');

		$response = $this->getJson(route('passport.scopes.index'))
			->assertJsonCount(1);
    }
    
    #[Test]
    public function get_all_personal_tokens_user_has_created(): void
    {
		$this->actingAs($this->user,'web');

		/** @var \Laravel\Passport\PersonalAccessTokenResult $token */
		$token = $this->user->createToken($this->client->name,['sample-scope']);

		$response = $this->getJson(route('passport.personal.tokens.index'))
			->assertJsonCount(1)
			->assertJson([
				[
					'name' => $this->client->name,
					'scopes' => [
						'sample-scope',
					],
				],
			]);
    }
    
    #[Test]
    public function store_new_personal_tokens_on_global_client(): void
    {
		$this->actingAs($this->user,'web');
		
		$data = [
			'name' => 'Token Name',
			'scopes' => [],
		];

		// Uses Most recently created personal access client

		$response = $this->postJson(route('passport.personal.tokens.store'),$data)
			->assertOk()
			->assertJsonStructure([
				'accessToken',
				'token',
			]);
    }

    #[Test]
    public function delete_personal_tokens_user_has_created(): void
    {
		$this->actingAs($this->user,'web');

		/** @var \Laravel\Passport\PersonalAccessTokenResult $token */
		$token = $this->user->createToken($this->client->name,['sample-scope']);

		$response = $this->deleteJson(route('passport.personal.tokens.destroy',[$token->token->id]))
			->assertNoContent();
    }
    
	#[Test]
    public function can_ping_api_with_personal_access_token(): void
    {
		/** @var \Laravel\Passport\PersonalAccessTokenResult $token */
		$token = $this->user->createToken($this->client->name,['sample-scope']);

		$accessToken = $token->accessToken;

		$response = $this
			->withToken($accessToken)
			->getJson('/api/ping')
			->assertSeeText('pong');
    }
}
