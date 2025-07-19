<?php
return [
    /*
    |--------------------------------------------------------------------------
    | OpenAI API Key and Organization
    |--------------------------------------------------------------------------
    |
    | Here you may specify your OpenAI API Key and organization. This will be
    | used to authenticate with the OpenAI API - you can find your API key
    | and organization on your OpenAI dashboard, at https://openai.com.
    */
    'api_key' => env('OPENAI_API_KEY', 'randomString'),
    'organization' => env('OPENAI_ORGANIZATION', 'randomString'),
    'request_timeout' => env('OPENAI_REQUEST_TIMEOUT', 120),
    'client' => [
        'timeout' => (int)env('OPENAI_REQUEST_TIMEOUT', 120),
        'connect_timeout' => 120,
    ],
];
