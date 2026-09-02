<?php

return [

    'openai' => [
        'key' => env('OPENAI_API_KEY'),
        'url' => env('OPENAI_RESPONSES_URL', 'https://api.openai.com/v1/responses'),
        'model' => env('OPENAI_MODEL', 'gpt-5.6-sol'),
        'reasoning_effort' => env('OPENAI_REASONING_EFFORT', 'medium'),
        'default_execution_profile' => env('OPENAI_EXECUTION_PROFILE', 'fast'),
        'execution_profiles' => [
            'fast' => [
                'model' => env('OPENAI_FAST_MODEL', 'gpt-5.6-luna'),
                'reasoning_effort' => env('OPENAI_FAST_REASONING_EFFORT', 'low'),
            ],
            'balanced' => [
                'model' => env('OPENAI_BALANCED_MODEL', 'gpt-5.6-terra'),
                'reasoning_effort' => env('OPENAI_BALANCED_REASONING_EFFORT', 'medium'),
            ],
            'quality' => [
                'model' => env('OPENAI_QUALITY_MODEL', 'gpt-5.6-sol'),
                'reasoning_effort' => env('OPENAI_QUALITY_REASONING_EFFORT', 'high'),
            ],
        ],
        'timeout' => (int) env('OPENAI_TIMEOUT', 300),
    ],

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

];
