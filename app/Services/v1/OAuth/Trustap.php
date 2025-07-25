<?php

namespace App\Services\v1\OAuth;

use App\Models\User;
use App\Models\UserTrustapMetadata;
use App\Services\v1\Payment\PaymentFailedException;
use App\Services\v1\Payment\TrustAppException;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Trustap
{
    protected $clientId;

    protected $redirectUri;

    protected $ssoUrl;

    protected $state;

    protected $clientSecret;

    public function __construct()
    {
        $this->clientId = config('services.trustap.client_id');
        $this->clientSecret = config('services.trustap.client_secret');
        $this->redirectUri = config('services.trustap.auth_redirect_url');
        $this->ssoUrl = config('services.trustap.sso_url');
        $this->state = 'testing'; // Generate a random state parameter
    }

    public function getAuthUrl()
    {
        $query = [
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'response_type' => 'code',
            'scope' => 'openid p2p_tx:offline_create_join p2p_tx:offline_accept_deposit p2p_tx:offline_cancel p2p_tx:offline_confirm_handover p2p_tx:offline_claim',
            'state' => $this->state,
        ];

        return $this->ssoUrl.'/auth?'.http_build_query($query);
    }

    public function getAccessToken($code)
    {
        $response = Http::asForm()->post($this->ssoUrl.'/token', [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type' => 'authorization_code',
            'code' => $code,
            // 'redirect_uri' => 'http://localhost:8000/trustap/auth/callback',
            'redirect_uri' => config('services.trustap.auth_redirect_url'),
        ]);
        if ($response->failed()) {
            Log::debug('createFullUser: ', $response->json());
            throw new PaymentFailedException('Error while creating full user');
        }
        return $response->json();
    }

    public function getUser($code)
    {
        // dd($code);
        $response = $this->getAccessToken($code);
        $tokenData = $this->getTrustapUserId($response['id_token']);

        $socialUser = [
            'provider_id' => $tokenData['trustapUserId'],
            'email' => $tokenData['email'],
            'provider' => 'trustap',
        ];
        // dd($tokenData);
        // $user = $this->findOrCreateUser($socialUser);
        if (empty($tokenData['trustapUserId']) || empty($tokenData['email'])) {
            throw new PaymentFailedException('user must be logged in to proceed.');
        }
        UserTrustapMetadata::where('user_id', auth()->id())
            ->update([
                'trustapFullUserId' => $tokenData['trustapUserId'],
                'trustapFullUserEmail' => $tokenData['email'],
            ]);

    }

    protected function findOrCreateUser(array $socialUser): User
    {
        // this if condition is only for trustap, not for other providers
        if (
            ! $user = User::whereEmail($socialUser['email'])
                ->Where('provider_id', null)
                ->first()
        ) {
            throw new DuplicateEmailException('Email Already Exists');
        }

        $user = User::firstOrCreate(
            ['provider_id' => $socialUser['provider_id']],
            [
                'email' => $socialUser['email'],
                'provider' => $socialUser['provider'],
                'email_verified_at' => Carbon::now(),
            ]
        );

        return $user;
    }

    private function getTrustapUserId($token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        // Decode the payload (second part)
        $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);

        $data = [
            'trustapUserId' => $payload['sub'] ?? null,
            'email' => $payload['email'] ?? null,
        ];

        return $data;
    }

    public function createGuestUser(array $data)
    {
        $trustapUser = UserTrustapMetadata::whereUserId(auth()->id())->first();
        if ($trustapUser) {
            throw new DuplicateEmailException;
        }
        $response = Http::withBasicAuth(config('services.trustap.api_key'), '')
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])
            ->post(config('services.trustap.url').'/guest_users', [
                'email' => $data['email'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'country_code' => $data['country_code'],
                'tos_acceptance' => [
                    'unix_timestamp' => $data['timestamp'],
                    'ip' => $data['ip'],
                ],
            ]);
        if ($response->failed() && $response->status() == 400) {
            $error_to_object = json_decode($response->body());
            throw new TrustAppException($error_to_object->error);
        }
        Log::info('createGuestUser : '.$response);
        return UserTrustapMetadata::create([
            'user_id' => auth()->user()->id,
            'trustapGuestUserId' => $response['id'],
            'created_at' => now(),
        ]);
    }
}
