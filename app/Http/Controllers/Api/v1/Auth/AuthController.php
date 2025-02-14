<?php

namespace App\Http\Controllers\Api\v1\Auth;

use App\Events\Registered;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();

        if (! $token = JWTAuth::attempt($validated)) {
            return $this->respondForbidden('Invalid Credentials');
        }

        return $this->respondSuccess(['token' => $token]);
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $user = User::create($validated);
            $user->assignRole($validated['role']);

            defer(fn () => event(new Registered($user)));

            $token = JWTAuth::fromUser($user);

            return $this->respondSuccess(['token' => $token], 'User registered successfully. A verification link has been sent to your email address.', 201);
        } catch (\Exception $e) {
            Log::error('Registration Error: '.$e->getMessage());

            return $this->respondError('Registration Failed. Please try again');
        }
    }

    public function logout(): JsonResponse
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return $this->respondOk('Logged Out Successfully');
    }

    public function refresh(): mixed
    {
        return $this->respondSuccess(['token' => JWTAuth::refresh()]);
    }

    public function userProfile(): JsonResponse
    {
        return $this->respondSuccess(['data' => auth()->user()]);
    }
}
