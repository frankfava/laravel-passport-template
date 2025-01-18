<?php

namespace App\Http\Controllers\Auth;

use App\Http\Responses\AuthResponse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;

class RegistrationController extends Controller
{
    /**
     * Attempt to authenticate a new session.
     *
     * @return mixed
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $token = $user->createToken('auth_token');

        return new AuthResponse($user, $token, 'User registered successfully', 201);
    }
}
