<?php

use CodebarAg\Zendesk\Requests\OAuth\CreateAccessTokenRequest;
use CodebarAg\Zendesk\Tests\Fixtures\ZendeskFixture;
use CodebarAg\Zendesk\Tests\TestCase;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Laravel\Facades\Saloon;

uses(TestCase::class)->in(__DIR__);

/**
 * @param  array<string, mixed>  $overrides
 */
function tokenResponse(array $overrides = [], int $status = 200): MockResponse
{
    return MockResponse::make(array_merge([
        'access_token' => 'test-access-token',
        'token_type' => 'bearer',
        'scope' => 'read write',
        'expires_in' => 1800,
        'refresh_token' => null,
        'refresh_token_expires_in' => null,
    ], $overrides), $status);
}

/**
 * Fakes every Saloon request globally. The OAuth token endpoint is mocked by default.
 *
 * @param  array<class-string, mixed>  $responses
 */
function fakeZendesk(array $responses = []): MockClient
{
    return Saloon::fake(array_merge([
        CreateAccessTokenRequest::class => tokenResponse(),
    ], $responses));
}

function zendeskFixture(string $name): ZendeskFixture
{
    return new ZendeskFixture($name);
}

/**
 * Fakes requests with recorded live responses, including the OAuth token request.
 *
 * @param  array<class-string, mixed>  $responses
 */
function fakeZendeskLive(array $responses = []): MockClient
{
    return Saloon::fake(array_merge([
        CreateAccessTokenRequest::class => zendeskFixture('oauth/token'),
    ], $responses));
}

/**
 * Live tests replay their fixtures. They are skipped until the fixtures were recorded with `composer test:record`.
 */
function requiresFixtures(string ...$names): void
{
    if (ZendeskFixture::recording()) {
        return;
    }

    foreach ($names as $name) {
        if (! ZendeskFixture::exists($name)) {
            test()->markTestSkipped("Fixture [{$name}] is missing. Record it with `composer test:record`.");
        }
    }
}
