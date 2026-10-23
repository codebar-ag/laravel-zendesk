<?php

use CodebarAg\Zendesk\Auth\AccessTokenRepository;
use CodebarAg\Zendesk\Auth\OAuthCredentials;
use CodebarAg\Zendesk\Exceptions\ZendeskAuthenticationException;
use CodebarAg\Zendesk\Exceptions\ZendeskException;
use CodebarAg\Zendesk\Requests\OAuth\CreateAccessTokenRequest;
use Illuminate\Support\Facades\Cache;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\PendingRequest;

function repository(?OAuthCredentials $credentials = null, string $subdomain = 'codebar-zendesk'): AccessTokenRepository
{
    return new AccessTokenRepository($subdomain, $credentials ?? new OAuthCredentials('test-client', 'test-secret'));
}

it('requests a token with the client credentials grant', function () {
    $mockClient = fakeZendesk();

    expect(repository()->get())->toBe('test-access-token');

    $pendingRequest = $mockClient->getLastPendingRequest();

    expect($pendingRequest->getMethod()->value)->toBe('POST')
        ->and($pendingRequest->getUrl())->toBe('https://codebar-zendesk.zendesk.com/oauth/tokens')
        ->and($pendingRequest->headers()->get('Content-Type'))->toBe('application/json')
        ->and($pendingRequest->headers()->get('Authorization'))->toBeNull()
        ->and($pendingRequest->body()->all())->toBe([
            'grant_type' => 'client_credentials',
            'client_id' => 'test-client',
            'client_secret' => 'test-secret',
            'scope' => 'read write',
        ]);
});

it('sends the requested token lifetime', function () {
    $mockClient = fakeZendesk();

    repository(new OAuthCredentials('test-client', 'test-secret', ['tickets:read'], 600))->get();

    expect($mockClient->getLastPendingRequest()->body()->all())
        ->toMatchArray(['scope' => 'tickets:read', 'expires_in' => 600]);
});

it('caches the token', function () {
    $mockClient = fakeZendesk();
    $repository = repository();

    $repository->get();
    $repository->get();
    repository()->get();

    $mockClient->assertSentCount(1, CreateAccessTokenRequest::class);
    expect(Cache::get($repository->key()))->toBe('test-access-token');
});

it('renews the token shortly before it expires', function () {
    $calls = 0;
    fakeZendesk([
        CreateAccessTokenRequest::class => function () use (&$calls) {
            return tokenResponse(['access_token' => 'token-'.++$calls, 'expires_in' => 1800]);
        },
    ]);

    expect(repository()->get())->toBe('token-1');

    $this->travel(1800 - AccessTokenRepository::EXPIRY_BUFFER - 1)->seconds();
    expect(repository()->get())->toBe('token-1');

    $this->travel(2)->seconds();
    expect(repository()->get())->toBe('token-2');
});

it('uses the fallback ttl for tokens without expiry', function () {
    config(['zendesk.cache.fallback_ttl' => 120]);

    $calls = 0;
    fakeZendesk([
        CreateAccessTokenRequest::class => function () use (&$calls) {
            return tokenResponse(['access_token' => 'token-'.++$calls, 'expires_in' => null]);
        },
    ]);

    expect(repository()->get())->toBe('token-1');

    $this->travel(119)->seconds();
    expect(repository()->get())->toBe('token-1');

    $this->travel(2)->seconds();
    expect(repository()->get())->toBe('token-2');
});

it('caches very short lived tokens for at least one second', function () {
    $mockClient = fakeZendesk([
        CreateAccessTokenRequest::class => tokenResponse(['expires_in' => 30]),
    ]);

    repository()->get();
    repository()->get();

    $mockClient->assertSentCount(1, CreateAccessTokenRequest::class);
});

it('forces a new token on refresh', function () {
    $calls = 0;
    fakeZendesk([
        CreateAccessTokenRequest::class => function () use (&$calls) {
            return tokenResponse(['access_token' => 'token-'.++$calls]);
        },
    ]);

    $repository = repository();

    expect($repository->get())->toBe('token-1')
        ->and($repository->refresh())->toBe('token-2')
        ->and($repository->get())->toBe('token-2');
});

it('forgets the cached token', function () {
    $mockClient = fakeZendesk();
    $repository = repository();

    $repository->get();
    $repository->forget();

    expect(Cache::has($repository->key()))->toBeFalse();

    $repository->get();

    $mockClient->assertSentCount(2, CreateAccessTokenRequest::class);
});

it('uses separate cache keys per subdomain, client and scopes', function () {
    $keys = [
        repository()->key(),
        repository(subdomain: 'other')->key(),
        repository(new OAuthCredentials('other-client', 'test-secret'))->key(),
        repository(new OAuthCredentials('test-client', 'test-secret', 'read'))->key(),
    ];

    expect(array_unique($keys))->toHaveCount(4)
        ->and($keys[0])->toStartWith('zendesk:oauth:');
});

it('uses the configured cache prefix', function () {
    config(['zendesk.cache.prefix' => 'custom:']);

    expect(repository()->key())->toStartWith('custom:');
});

it('uses the configured cache store', function () {
    config([
        'cache.stores.zendesk' => ['driver' => 'array'],
        'zendesk.cache.store' => 'zendesk',
    ]);
    fakeZendesk();

    $repository = repository();
    $repository->get();

    expect(Cache::store('zendesk')->get($repository->key()))->toBe('test-access-token')
        ->and(Cache::store('array')->get($repository->key()))->toBeNull();
});

it('works with cache stores without lock support', function () {
    config(['cache.stores.null' => ['driver' => 'null']]);
    $mockClient = fakeZendesk();

    $repository = new AccessTokenRepository(
        'codebar-zendesk',
        new OAuthCredentials('test-client', 'test-secret'),
        Cache::store('null'),
    );

    expect($repository->get())->toBe('test-access-token');
    $mockClient->assertSentCount(1, CreateAccessTokenRequest::class);
});

it('throws an authentication exception when Zendesk rejects the client', function () {
    fakeZendesk([
        CreateAccessTokenRequest::class => MockResponse::make([
            'error' => 'invalid_client',
            'error_description' => 'Client authentication failed.',
        ], 401),
    ]);

    expect(fn () => repository()->get())->toThrow(function (ZendeskAuthenticationException $exception) {
        expect($exception)->toBeInstanceOf(ZendeskException::class)
            ->and($exception->getMessage())->toBe('Unable to obtain a Zendesk OAuth access token (invalid_client): Client authentication failed.')
            ->and($exception->getCode())->toBe(401);
    });
});

it('throws an authentication exception for invalid scopes', function () {
    fakeZendesk([
        CreateAccessTokenRequest::class => MockResponse::make(['error' => 'invalid_scope'], 400),
    ]);

    repository()->get();
})->throws(ZendeskAuthenticationException::class, 'Unable to obtain a Zendesk OAuth access token (invalid_scope).');

it('throws an authentication exception for unexpected error bodies', function () {
    fakeZendesk([
        CreateAccessTokenRequest::class => MockResponse::make('Service Unavailable', 503),
    ]);

    repository()->get();
})->throws(ZendeskAuthenticationException::class, '(unknown_error)');

it('throws when Zendesk returns an empty token', function () {
    fakeZendesk([
        CreateAccessTokenRequest::class => tokenResponse(['access_token' => '']),
    ]);

    repository()->get();
})->throws(ZendeskAuthenticationException::class, 'empty OAuth access token');

it('does not cache failed token requests', function () {
    fakeZendesk([
        CreateAccessTokenRequest::class => MockResponse::make(['error' => 'invalid_client'], 401),
    ]);

    $repository = repository();

    try {
        $repository->get();
    } catch (ZendeskAuthenticationException) {
    }

    expect(Cache::has($repository->key()))->toBeFalse();
});

it('builds the token request from the credentials', function () {
    $mockClient = fakeZendesk([
        CreateAccessTokenRequest::class => fn (PendingRequest $pendingRequest) => tokenResponse([
            'access_token' => $pendingRequest->body()->all()['client_id'].'-token',
        ]),
    ]);

    expect(repository()->get())->toBe('test-client-token');
    $mockClient->assertSent(CreateAccessTokenRequest::class);
});

it('throws when Zendesk returns an invalid token response', function () {
    fakeZendesk([
        CreateAccessTokenRequest::class => MockResponse::make('<html>ok</html>', 200),
    ]);

    repository()->get();
})->throws(ZendeskAuthenticationException::class, 'invalid OAuth token response');
