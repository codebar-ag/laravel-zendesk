<?php

return [
    // Your Zendesk subdomain, e.g. 'acme' for https://acme.zendesk.com
    'subdomain' => env('ZENDESK_SUBDOMAIN'),

    'auth' => [
        // 'oauth' (client credentials grant, recommended) or 'access_token'
        'method' => env('ZENDESK_AUTHENTICATION_METHOD', 'oauth'),

        // Used by the 'oauth' method
        'oauth' => [
            'client_id' => env('ZENDESK_OAUTH_CLIENT_ID'),
            'client_secret' => env('ZENDESK_OAUTH_CLIENT_SECRET'),
            'scopes' => env('ZENDESK_OAUTH_SCOPES', 'read write'), // space separated
            'expires_in' => env('ZENDESK_OAUTH_EXPIRES_IN'), // seconds, 300 - 172800, null = Zendesk default
        ],

        // Used by the 'access_token' method (a bearer token you manage yourself)
        'access_token' => env('ZENDESK_ACCESS_TOKEN'),
    ],

    'cache' => [
        'store' => env('ZENDESK_CACHE_STORE'), // null = default cache store
        'prefix' => 'zendesk:oauth:',
        'fallback_ttl' => 86400, // seconds, used when Zendesk returns a token without expiry
    ],
];
