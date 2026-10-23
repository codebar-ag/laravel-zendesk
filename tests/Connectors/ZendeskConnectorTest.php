<?php

use CodebarAg\Zendesk\Auth\AuthMethod;
use CodebarAg\Zendesk\Auth\OAuthCredentials;
use CodebarAg\Zendesk\Auth\ZendeskOAuthConnector;
use CodebarAg\Zendesk\Exceptions\ZendeskAuthenticationException;
use CodebarAg\Zendesk\Exceptions\ZendeskConfigurationException;
use CodebarAg\Zendesk\Requests\CountTicketsRequest;
use CodebarAg\Zendesk\Requests\OAuth\CreateAccessTokenRequest;
use CodebarAg\Zendesk\Requests\SingleTicketRequest;
use CodebarAg\Zendesk\ZendeskConnector;
use Saloon\Exceptions\Request\Statuses\NotFoundException;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\PendingRequest;

function countResponse(): MockResponse
{
    return MockResponse::make(['count' => ['value' => 4, 'refreshed_at' => '2026-10-23T12:00:00+00:00']]);
}

describe('base url', function () {
    it('resolves the base url from the config', function () {
        expect(new ZendeskConnector()->resolveBaseUrl())->toBe('https://codebar-zendesk.zendesk.com/api/v2');
    });

    it('prefers the subdomain passed to the constructor', function () {
        expect(new ZendeskConnector('acme')->resolveBaseUrl())->toBe('https://acme.zendesk.com/api/v2');
    });

    it('throws if no subdomain is configured', function (?string $subdomain) {
        config(['zendesk.subdomain' => $subdomain]);

        new ZendeskConnector()->resolveBaseUrl();
    })->with([null, ''])->throws(ZendeskConfigurationException::class, 'No subdomain provided.');

    it('rejects subdomains that would change the host', function (string $subdomain) {
        new ZendeskConnector($subdomain)->resolveBaseUrl();
    })->with([
        'evil.com/',
        'acme.zendesk.com',
        'acme@evil',
        '-acme',
        'acme-',
        'ac me',
    ])->throws(ZendeskConfigurationException::class, 'Invalid subdomain provided');

    it('accepts subdomains with digits and dashes', function () {
        expect(new ZendeskConnector('codebar-2')->resolveBaseUrl())->toBe('https://codebar-2.zendesk.com/api/v2');
    });

    it('uses the account host for the oauth connector', function () {
        expect(new ZendeskOAuthConnector()->resolveBaseUrl())->toBe('https://codebar-zendesk.zendesk.com')
            ->and(new ZendeskOAuthConnector('acme')->resolveBaseUrl())->toBe('https://acme.zendesk.com');
    });
});

describe('authentication method', function () {
    it('defaults to oauth', function () {
        expect(new ZendeskConnector()->authMethod())->toBe(AuthMethod::OAUTH);
    });

    it('supports access tokens', function () {
        config(['zendesk.auth.method' => 'access_token']);

        expect(new ZendeskConnector()->authMethod())->toBe(AuthMethod::ACCESS_TOKEN);
    });

    it('uses oauth when credentials are passed to the constructor', function () {
        config(['zendesk.auth.method' => 'access_token']);

        $connector = new ZendeskConnector(credentials: new OAuthCredentials('client', 'secret'));

        expect($connector->authMethod())->toBe(AuthMethod::OAUTH);
    });

    it('throws if no method is configured', function (?string $method) {
        config(['zendesk.auth.method' => $method]);

        new ZendeskConnector()->authMethod();
    })->with([null, ''])->throws(ZendeskConfigurationException::class, 'No authentication method provided.');

    it('rejects the removed and unknown methods', function (string $method) {
        config(['zendesk.auth.method' => $method]);

        new ZendeskConnector()->authMethod();
    })->with(['token', 'basic', 'nope'])->throws(
        ZendeskConfigurationException::class,
        'Supported methods: oauth, access_token.',
    );
});

describe('oauth', function () {
    it('sends requests with an oauth bearer token', function () {
        $mockClient = fakeZendesk([
            CountTicketsRequest::class => countResponse(),
        ]);

        new ZendeskConnector()->send(new CountTicketsRequest);

        $mockClient->assertSentCount(1, CreateAccessTokenRequest::class);
        expect($mockClient->getLastPendingRequest()->headers()->get('Authorization'))->toBe('Bearer test-access-token');
    });

    it('reuses the cached token across connectors', function () {
        $mockClient = fakeZendesk([
            CountTicketsRequest::class => countResponse(),
        ]);

        new ZendeskConnector()->send(new CountTicketsRequest);
        new ZendeskConnector()->send(new CountTicketsRequest);

        $mockClient->assertSentCount(1, CreateAccessTokenRequest::class);
        $mockClient->assertSentCount(2, CountTicketsRequest::class);
    });

    it('uses the credentials and subdomain passed to the constructor', function () {
        $mockClient = fakeZendesk([
            CreateAccessTokenRequest::class => fn (PendingRequest $request) => tokenResponse([
                'access_token' => $request->body()->all()['client_id'].'@'.parse_url($request->getUrl(), PHP_URL_HOST),
            ]),
            CountTicketsRequest::class => countResponse(),
        ]);

        new ZendeskConnector('acme', new OAuthCredentials('acme-client', 'acme-secret'))->send(new CountTicketsRequest);

        $pendingRequest = $mockClient->getLastPendingRequest();

        expect($pendingRequest->getUrl())->toBe('https://acme.zendesk.com/api/v2/tickets/count.json')
            ->and($pendingRequest->headers()->get('Authorization'))->toBe('Bearer acme-client@acme.zendesk.com');
    });

    it('throws a configuration exception for missing credentials before sending anything', function () {
        config(['zendesk.auth.oauth.client_secret' => null]);
        $mockClient = fakeZendesk();

        expect(fn () => new ZendeskConnector()->send(new CountTicketsRequest))
            ->toThrow(ZendeskConfigurationException::class, 'No OAuth client secret provided.');

        $mockClient->assertNothingSent();
    });

    it('throws an authentication exception when no token can be obtained', function () {
        $mockClient = fakeZendesk([
            CreateAccessTokenRequest::class => MockResponse::make(['error' => 'invalid_client'], 401),
        ]);

        expect(fn () => new ZendeskConnector()->send(new CountTicketsRequest))
            ->toThrow(ZendeskAuthenticationException::class);

        $mockClient->assertNotSent(CountTicketsRequest::class);
    });

    it('retries once with a fresh token when the cached token is rejected', function () {
        $tokens = 0;
        $requests = 0;

        $mockClient = fakeZendesk([
            CreateAccessTokenRequest::class => function () use (&$tokens) {
                return tokenResponse(['access_token' => 'token-'.++$tokens]);
            },
            CountTicketsRequest::class => function (PendingRequest $request) use (&$requests) {
                $requests++;

                return $request->headers()->get('Authorization') === 'Bearer token-2'
                    ? countResponse()
                    : MockResponse::make(['error' => 'invalid_token'], 401);
            },
        ]);

        $response = new ZendeskConnector()->send(new CountTicketsRequest);

        expect($response->status())->toBe(200)
            ->and($tokens)->toBe(2)
            ->and($requests)->toBe(2)
            ->and($response->dto()->value)->toBe(4);
    });

    it('returns the 401 response if the fresh token is rejected as well', function () {
        $mockClient = fakeZendesk([
            CountTicketsRequest::class => MockResponse::make(['error' => 'invalid_token'], 401),
        ]);

        $response = new ZendeskConnector()->send(new CountTicketsRequest);

        expect($response->status())->toBe(401);
        $mockClient->assertSentCount(2, CreateAccessTokenRequest::class);
        $mockClient->assertSentCount(2, CountTicketsRequest::class);
    });

    it('does not retry other failed responses', function (int $status) {
        $mockClient = fakeZendesk([
            CountTicketsRequest::class => MockResponse::make([], $status),
        ]);

        expect(new ZendeskConnector()->send(new CountTicketsRequest)->status())->toBe($status);
        $mockClient->assertSentCount(1, CountTicketsRequest::class);
    })->with([403, 404, 422, 429, 500]);
});

describe('access token', function () {
    beforeEach(function () {
        config([
            'zendesk.auth.method' => 'access_token',
            'zendesk.auth.access_token' => 'my-access-token',
        ]);
    });

    it('sends requests with the configured bearer token', function () {
        $mockClient = fakeZendesk([
            CountTicketsRequest::class => countResponse(),
        ]);

        new ZendeskConnector()->send(new CountTicketsRequest);

        $mockClient->assertNotSent(CreateAccessTokenRequest::class);
        expect($mockClient->getLastPendingRequest()->headers()->get('Authorization'))->toBe('Bearer my-access-token');
    });

    it('does not retry on 401', function () {
        $mockClient = fakeZendesk([
            CountTicketsRequest::class => MockResponse::make([], 401),
        ]);

        expect(new ZendeskConnector()->send(new CountTicketsRequest)->status())->toBe(401);
        $mockClient->assertSentCount(1, CountTicketsRequest::class);
    });

    it('throws if no access token is configured', function (?string $token) {
        config(['zendesk.auth.access_token' => $token]);
        fakeZendesk();

        new ZendeskConnector()->send(new CountTicketsRequest);
    })->with([null, ''])->throws(ZendeskConfigurationException::class, 'No access token provided');
});

describe('custom authenticator', function () {
    it('prefers an authenticator set at runtime and skips oauth', function () {
        $mockClient = fakeZendesk([
            CountTicketsRequest::class => MockResponse::make([], 401),
        ]);

        $connector = new ZendeskConnector;
        $connector->authenticate(new TokenAuthenticator('user-token'));

        $response = $connector->send(new CountTicketsRequest);

        expect($response->status())->toBe(401)
            ->and($mockClient->getLastPendingRequest()->headers()->get('Authorization'))->toBe('Bearer user-token');
        $mockClient->assertNotSent(CreateAccessTokenRequest::class);
        $mockClient->assertSentCount(1, CountTicketsRequest::class);
    });
});

describe('headers', function () {
    it('accepts json and does not force a content type on requests without a body', function () {
        $mockClient = fakeZendesk([
            CountTicketsRequest::class => countResponse(),
        ]);

        new ZendeskConnector()->send(new CountTicketsRequest);

        $headers = $mockClient->getLastPendingRequest()->headers();

        expect($headers->get('Accept'))->toBe('application/json')
            ->and($headers->get('Content-Type'))->toBeNull();
    });
});

it('returns failed responses and throws when creating a dto from them', function () {
    fakeZendesk([
        SingleTicketRequest::class => MockResponse::make(['error' => 'RecordNotFound', 'description' => 'Not found'], 404),
    ]);

    $response = new ZendeskConnector()->send(new SingleTicketRequest(81));

    expect($response->status())->toBe(404)
        ->and(fn () => $response->dto())->toThrow(NotFoundException::class);
});
