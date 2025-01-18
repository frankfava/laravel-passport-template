<?php

namespace App\Http\Responses;

use App\Models\User;
use Illuminate\Contracts\Support\Responsable;
use Laravel\Passport\PersonalAccessTokenResult;

class AuthResponse implements Responsable
{
    public function __construct(
        readonly User $user,
        readonly string|PersonalAccessTokenResult $token,
        readonly ?string $message = null,
        readonly ?int $responseCode = 200,
    ) {}

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        return $request->wantsJson()
            ? response()->json([
                'message' => $this->message,
                'user' => $this->user,
                'token' => $this->token instanceof PersonalAccessTokenResult ? $this->token->accessToken : $this->token,
            ], $this->responseCode)
            : redirect()->intended('/');
    }
}
