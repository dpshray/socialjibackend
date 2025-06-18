<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\v1\OAuth\DuplicateEmailException;
use App\Services\v1\OAuth\Trustap;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * Controller for creating a full user in Trustap.
 * This process is compulsory for sellers.
 */
class TrustapAuthController extends Controller
{
    public function __construct(private Trustap $trustap) {}

    public function redirectToTrustap()
    {
        return redirect($this->trustap->getAuthUrl());
    }

    public function handleProviderCallback(Request $request)
    {
        try {
            $this->trustap->getUser($request['code']);

            // $token = JWTAuth::fromUser($user);

            // return $this->respondSuccess(['token' => $token], 'User registered successfully.', 201);
            return $this->respondOk('User registered successfully.', 201);
        } catch (DuplicateEmailException $e) {
            return $this->respondError('Email Already Exists');
        }
    }

    public function createGuestUser(Request $request)
    {
        try {
            $validated = $request->validate([
                // 'email' => ['required', 'email'],
                'first_name' => ['required', 'string', 'max:255'],
                'last_name' => ['required', 'string', 'max:255'],
                'country_code' => ['required', 'string', 'size:2'],
            ]);

            // For testing purposes, remove this
            // $user = \App\Models\User::find(16);
            // auth()->login($user);
            $trustapData = $this->trustap->createGuestUser([
                'email' => auth()->user()->email,
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'country_code' => $validated['country_code'],
                'timestamp' => time(),
                'ip' => request()->ip(),
            ]);

            if (! $trustapData) {
                return $this->respondError('Unable to create guest user.');
            }

            return $this->respondSuccess(['data' => $trustapData], 'Guest user created successfully.', 201);
        } catch (DuplicateEmailException $e) {
            return $this->respondError('Trustap user already exists.');
        }
    }
}
