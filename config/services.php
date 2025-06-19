<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_TOKEN'),
        'redirect' => env('FACEBOOK_REDIRECT_URL'),
    ],

    'trustap' => [
        'url' => env('TRUSTAP_URL'),
        'client_id' => env('TRUSTAP_CLIENT_ID'),
        'client_secret' => env('TRUSTAP_CLIENT_SECRET'),
        'api_key' => env('TRUSTAP_API_KEY'),
        'payment_action' => env('TRUSTAP_BUYER_PAYMENT_ACTION'),
        'payment_callback_uri' => env('TRUSTAP_PAYMENT_CALLBACK_URI'),
        'sso_url' => env('TRUSTAP_SSO_URL'),
        'auth_redirect_url' => env('TRUSTAP_AUTH_REDIRECT_URI'),
    ],

    'frontend' => [
        'url' => env('APP_FRONTEND_URL', 'https://frontend.stage.dworklabs.com/'),
    ],

];
