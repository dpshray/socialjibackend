<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\v1\OAuth\DuplicateEmailException;
use App\Services\v1\OAuth\Trustap;
use App\Services\v1\Payment\TrustAppException;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * Controller for creating a full user in Trustap.
 * This process is compulsory for sellers.
 */
class TrustapAuthController extends Controller
{
    use ResponseTrait;

    public function __construct(private Trustap $trustap) {}

    public function redirectToTrustap()
    {
        if (Auth::user()->userTrustapMetadata->trustapFullUserId) {
            return $this->apiError('full user already exists');
        }
        $url = $this->trustap->getAuthUrl();
        // dd($url);
        return redirect($url);
    }

    public function handleProviderCallback(Request $request)
    {
        // dd(auth()->id());
        try {
            $this->trustap->getUser($request['code']);

            // $token = JWTAuth::fromUser($user);

            // return $this->respondSuccess(['token' => $token], 'User registered successfully.', 201);
            return $this->apiSuccess('User registered successfully.');
        } catch (DuplicateEmailException $e) {
            return $this->apiSuccess('Email Already Exists');
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
            $trustapData = null;
            DB::transaction(function () use($validated, &$trustapData){                
                $trustapData = $this->trustap->createGuestUser([
                    'email' => Auth::user()->email,
                    'first_name' => $validated['first_name'],
                    'last_name' => $validated['last_name'],
                    'country_code' => $validated['country_code'],
                    'timestamp' => time(),
                    'ip' => request()->ip(),
                ]);
            });
            if (!$trustapData) {
                return $this->apiError('Unable to create guest user.');
            }
            return $this->apiSuccess('Guest user created successfully.', $trustapData);
        } catch(TrustAppException $e){
            return $this->apiError($e->getMessage());
        } 
        catch (\Exception $e) {
            return $this->apiError('Trustap user already exists.');
        }
    }
}
