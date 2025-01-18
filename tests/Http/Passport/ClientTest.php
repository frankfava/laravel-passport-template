<?php

namespace Tests\Http\Passport;

use App\Models\User;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ClientTest extends TestCase
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
        $this->actingAs($this->user, 'web');

        $this->clients->create(
            userId: $this->user->id,
            name : $this->faker->words(2, true),
            redirect : url('/')
        );

        $response = $this->getJson(route('passport.clients.index'))
            ->assertJsonCount(1);
    }

    #[Test]
    public function create_new_clients_for_user(): void
    {
        $this->actingAs($this->user, 'web');

        $response = $this->postJson(route('passport.clients.store'), [
            'name' => $this->faker->words(2, true),
            'redirect' => url('/'),
        ])
            ->assertCreated()
            ->assertJsonIsObject()
            ->assertJsonStructure([
                'user_id',
                'name',
                'secret',
            ]);
    }

    #[Test]
    public function update_client_for_user(): void
    {
        $this->actingAs($this->user, 'web');

        $client = $this->clients->create(
            userId: $this->user->id,
            name : $this->faker->words(2, true),
            redirect : url('/')
        );

        $response = $this->putJson(route('passport.clients.update', [$client->id]), [
            'name' => ($newName = $this->faker->words(2, true)),
            'redirect' => ($newUrl = url('/callback')),
        ])
            ->assertOk()
            ->assertJsonIsObject();

        $updated = json_decode($response->getContent());

        $this->assertEquals($newName, $updated->name);
        $this->assertEquals($newUrl, $updated->redirect);
    }

    #[Test]
    public function delete_client_for_user(): void
    {
        $this->actingAs($this->user, 'web');

        $client = $this->clients->create(
            userId: $this->user->id,
            name : $this->faker->words(2, true),
            redirect : url('/')
        );

        $response = $this->deleteJson(route('passport.clients.destroy', [$client->id]))
            ->assertNoContent();

        $this->assertCount(0, $this->user->tokens);
    }
}
