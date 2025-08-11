<?php

namespace App\Http\Controllers\Api\v1\Auth;

use App\Constants\Constants;
use App\Events\Registered;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PHPUnit\TextUI\Configuration\Constant;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $email_is_unverified = DB::table('users')->where([['email', $validated['email']], ['email_verified_at', '!=', null]])->doesntExist();
        if ($email_is_unverified) {
            return $this->apiError('email is not verified', 403);
        }
        if (! $token = JWTAuth::attempt($validated)) {
            return $this->apiError('Invalid Credentials');
        }
        $user = auth()->user();
        $user->loadMissing(['media','userTrustapMetadata']);
        $role = $user->getRoleNames()->first();
        event(new Registered($user));
        $user = new UserResource($user);
        return $this->apiSuccess('logged in successfull', compact('user','role','token'));
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $user = null;
            DB::transaction(function () use($request, $validated, &$user){
                $user = User::create($validated)->assignRole($request->role_id);
                $user->addMedia($request->image)->toMediaCollection(Constants::MEDIA_USER);
                event(new Registered($user));
            });
            $token = JWTAuth::fromUser($user);
            return $this->apiSuccess('User registered successfully. A verification link has been sent to your email address.', compact('token'));
        } catch (\Exception $e) {
            Log::error('Registration Error: '.$e->getMessage());
            return $this->apiError('Registration Failed. Please try again',500);
        }
    }

    public function logout(): JsonResponse
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return $this->apiSuccess('Logged Out Successfully');
    }

    public function refresh(): JsonResponse
    {
        return $this->apiSuccess('token refreshed', ['token' => JWTAuth::refresh()]);
    }

    public function userProfile(): JsonResponse
    {
        $user = auth()->user()->loadMissing(['userTrustapMetadata', 'gigReviews','media', 'socialProfiles.socialSite:id,name,label','roles']);
        // $role = $user->getRoleNames()->first();
        $data = new UserResource($user);
        return $this->apiSuccess('user data', $data);
    }

    public function fetchRoles(){
        $roles = collect(Cache::get('roles'))->whereNotIn('name', [Constants::ROLE_ADMIN]);
        return $this->apiSuccess('role list', $roles);
    }

    public function accountRemover(){
        auth()->user()->delete();
        JWTAuth::invalidate(JWTAuth::getToken());
        return $this->apiError('Account removed successfully');
    }

    public function fetchUserProfile(User $user){
        if ($user->isBrand()) {
            return $this->apiError('the user is not an influencer',404);
        }
        $user = $user->loadMissing(['gigReviews', 'media', 'socialProfiles.socialSite:id,name,label', 'roles','gigs.media']);
        $data = new UserResource($user);
        return $this->apiSuccess('user profile data', $data);
    }
}
