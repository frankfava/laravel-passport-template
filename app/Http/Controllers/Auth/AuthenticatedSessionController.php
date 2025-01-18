<?php

namespace App\Http\Controllers\Auth;

use App\Http\Responses\AuthResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
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

        if (! Auth::attempt($request->only(['email', 'password']))) {
            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        /** @var \App\Models\User */
        $user = Auth::user();

        // find Existing
        $user->tokens()->where('name', 'auth_token')->delete();

        $token = $user->createToken('auth_token', []);

        return new AuthResponse($user, $token, 'Login successful');
    }
}
