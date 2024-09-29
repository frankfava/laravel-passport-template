<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Providers\AppServiceProvider;
use Laravel\Passport\ClientRepository;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{	
	#[Test]
	public function a_user_can_authenticate_using_their_email_and_password()
	{
		app(ClientRepository::class)->createPersonalAccessClient(
			userId: null,
			name : AppServiceProvider::PersonalAccessClientName,
			redirect : url('/')
		);

		$this->withoutExceptionHandling();

		$user = User::factory()->create();

		$response = $this->postJson(route('login.post'), [
			'email' => $user->email,
			'password' => 'password',
		])->assertJsonStructure(['user','token']);
	}
}