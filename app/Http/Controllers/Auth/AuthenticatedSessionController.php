<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Providers\AppServiceProvider;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
	/**
	 * Attempt to authenticate a new session.
	 *
	 * @return mixed
	 */
	public function store(Request $request)
	{
		$request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

		if(!Auth::attempt($request->only(['email','password']))) {
			throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
		}

		$user = User::where('email', $request->email)->first();

		// find Existing
		$user->tokens()->whereHas('client',fn($q) => $q->where('name',AppServiceProvider::PersonalAccessClientName))->delete();
	
        $token = $user->createToken(AppServiceProvider::PersonalAccessClientName,[])->accessToken;

		return $request->wantsJson()
				? response()->json([
					'user' => $user,
					'token' => $token
				])
				: redirect()->intended('/');
	}
}
