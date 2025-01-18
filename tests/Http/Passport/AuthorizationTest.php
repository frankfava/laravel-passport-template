<?php

namespace Tests\Http\Passport;

use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;
use Laravel\Passport\Token;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see https://laravel.com/docs/11.x/passport#issuing-access-tokens
 * @see https://laravel.com/docs/11.x/passport#code-grant-pkce
 *
 *
 * ## Authorization Screen
 *
 * The prompt parameter may be used to specify the authentication behavior of the Passport application.
 *
 * - If the prompt value is none, Passport will always throw an authentication error if the user is not already authenticated with the Passport application.
 * - If the value is consent, Passport will always display the authorization approval screen, even if all scopes were previously granted to the consuming application.
 * - When the value is login, the Passport application will always prompt the user to re-login to the application, even if they already have an existing session.
 *
 * If no prompt value is provided, the user will be prompted for authorization only if they have not previously authorized access to the consuming application for the requested scopes.
 */
class AuthorizationTest extends TestCase
{
    protected ClientRepository $clients;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Passport::$scopes = [
            'sample-scope' => 'Sample Scope',
        ];

        $this->clients = app(ClientRepository::class);

        $this->user = User::factory()->create();
    }

    #[Test]
    public function client_can_get_authourization_and_approve(): void
    {
        $this->actingAs($this->user, 'web');

        $client = $this->clients->create(
            userId: null,
            name : 'AUTH Personal Access Client',
            redirect : ($thirdPartyCallbackUrl = 'https://auth-server.test/auth/callback')
        );

        // Get the Authorization Screen
        $response = $this
            ->followingRedirects()
            ->get(route('passport.authorizations.authorize', [
                'client_id' => $client->id,
                'redirect_uri' => $thirdPartyCallbackUrl,
                'response_type' => 'code',
                'scope' => '',
                'state' => ($state = Str::random(40)),
                'prompt' => 'consent', // "none", "consent", or "login"
            ]));

        $this->assertNotNull(($authToken = session()->get('authToken')));

        /** @var \Illuminate\Testing\TestResponse $response Approve Request */
        $response = $this
            ->followingRedirects()
            ->post(route('passport.authorizations.authorize'), [
                'client_id' => $client->id,
                'state' => $state,
                'auth_token' => $authToken,
            ]);

        // After Authorization, Validate the code and mimic the /auth/callback
        parse_str(parse_url(url()->full(), PHP_URL_QUERY), $query);
        if (! $query['state'] || $state != $query['state']) {
            $this->fail('Invalid state value.');
        }

        // Mimic the /auth/callback to get the auth code
        $response = $this
            ->postJson(route('passport.token'), [
                'grant_type' => 'authorization_code',
                'client_id' => $client->id,
                'client_secret' => $client->secret,
                'redirect_uri' => $thirdPartyCallbackUrl,
                'code' => $query['code'],
            ])
            ->assertJsonStructure([
                'token_type',
                'access_token',
                'expires_in',
                'refresh_token',
            ]);
    }

    #[Test]
    public function client_can_get_authourization_and_deny(): void
    {
        $this->actingAs($this->user, 'web');

        $client = $this->clients->create(
            userId: null,
            name : 'AUTH Personal Access Client',
            redirect : ($thirdPartyCallbackUrl = 'https://auth-server.test/auth/callback')
        );

        $response = $this
            ->followingRedirects()
            ->get(route('passport.authorizations.authorize', [
                'client_id' => $client->id,
                'redirect_uri' => $thirdPartyCallbackUrl,
                'response_type' => 'code',
                'scope' => '',
                'state' => ($state = Str::random(40)),
                'prompt' => 'consent', // "none", "consent", or "login"
            ]));

        $this->assertNotNull(($authToken = session()->get('authToken')));

        /** @var \Illuminate\Testing\TestResponse $response Approve Request */
        $response = $this
            ->followingRedirects()
            ->delete(route('passport.authorizations.deny'), [
                'client_id' => $client->id,
                'state' => $state,
                'auth_token' => $authToken,
            ]);

        // After Authorization, Validate the code and mimic the /auth/callback
        parse_str(parse_url(url()->full(), PHP_URL_QUERY), $query);
        if (! $query['state'] || $state != $query['state']) {
            $this->fail('Invalid state value.');
        }

        $this->assertNotNull($query['error']);
        $this->assertNotNull($query['error_description']);
        $this->assertNotNull($query['hint']);
        $this->assertNotNull($query['message']);
    }

    #[Test]
    public function user_can_refresh_a_token(): void
    {
        $this->actingAs($this->user, 'web');

        $client = $this->clients->create(
            userId: null,
            name : 'AUTH Personal Access Client',
            redirect : ($thirdPartyCallbackUrl = 'https://auth-server.test/auth/callback')
        );

        $response = $this
            ->followingRedirects()
            ->get(route('passport.authorizations.authorize', [
                'client_id' => $client->id,
                'redirect_uri' => $thirdPartyCallbackUrl,
                'response_type' => 'code',
                'scope' => '',
                'state' => ($state = Str::random(40)),
                'prompt' => 'consent', // "none", "consent", or "login"
            ]));

        $this->assertNotNull(($authToken = session()->get('authToken')));

        $response = $this
            ->followingRedirects()
            ->post(route('passport.authorizations.authorize'), [
                'client_id' => $client->id,
                'state' => $state,
                'auth_token' => $authToken,
            ]);

        // After Authorization, Validate the code and mimic the /auth/callback
        parse_str(parse_url(url()->full(), PHP_URL_QUERY), $query);
        if (! $query['state'] || $state != $query['state']) {
            $this->fail('Invalid state value.');
        }

        // Mimic the /auth/callback to get the auth code
        $response = $this
            ->postJson(route('passport.token'), [
                'grant_type' => 'authorization_code',
                'client_id' => $client->id,
                'client_secret' => $client->secret,
                'redirect_uri' => $thirdPartyCallbackUrl,
                'code' => $query['code'],
            ]);

        ['refresh_token' => $refreshToken] = json_decode($response->getContent(), 1);

        $response = $this
            ->postJson(route('passport.token'), [
                'grant_type' => 'refresh_token',
                'refresh_token' => $refreshToken,
                'client_id' => $client->id,
                'client_secret' => $client->secret,
                'scope' => '',
            ])
            ->assertJsonStructure([
                'token_type',
                'access_token',
                'expires_in',
                'refresh_token',
            ]);
    }

    // GET|HEAD  oauth/tokens ..................................... passport.tokens.index
    // DELETE    oauth/tokens/{token_id} .......................... passport.tokens.destroy
}
