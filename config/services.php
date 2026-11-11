<?php

return [
    'mailgun' => ['domain' => env('MAILGUN_DOMAIN'), 'secret' => env('MAILGUN_SECRET'), 'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'), 'scheme' => 'https'],
    'postmark' => ['token' => env('POSTMARK_TOKEN')],
    'resend' => ['key' => env('RESEND_KEY')],
    'hotel_api' => ['base_url' => env('HOTEL_API_BASE_URL', 'https://jsonplaceholder.typicode.com'), 'timeout' => env('HOTEL_API_TIMEOUT', 8), 'cache_ttl' => env('HOTEL_CACHE_TTL', 180)],
];
