<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'anthropic' => [
        'key' => env('ANTHROPIC_API_KEY'),
        'model' => env('ANTHROPIC_MODEL', 'claude-opus-5-5'),
    ],

    'whatsapp' => [
        // gateway (free, dedicated number), cloud (official paid API) or log (development).
        'driver' => env('WHATSAPP_DRIVER', 'log'),
        'fallback' => env('WHATSAPP_FALLBACK'),
        // Percentage of messages sent through the fallback channel first (0 = only as a backup).
        'official_share' => env('WHATSAPP_OFFICIAL_SHARE', 0),

        'gateway' => [
            'url' => env('WHATSAPP_GATEWAY_URL', 'http://127.0.0.1:3001'),
            'token' => env('WHATSAPP_GATEWAY_TOKEN'),
        ],

        'cloud' => [
            'version' => env('WHATSAPP_CLOUD_VERSION', 'v21.0'),
            'token' => env('WHATSAPP_CLOUD_TOKEN'),
            'phone_number_id' => env('WHATSAPP_CLOUD_PHONE_NUMBER_ID'),
            'template' => env('WHATSAPP_CLOUD_TEMPLATE', 'remerciement_visiteur'),
            'template_language' => env('WHATSAPP_CLOUD_TEMPLATE_LANGUAGE', 'fr'),
        ],
    ],

];
