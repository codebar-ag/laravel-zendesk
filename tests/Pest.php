<?php

use CodebarAg\Zendesk\Requests\OAuth\CreateAccessTokenRequest;
use CodebarAg\Zendesk\Tests\TestCase;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

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
    return MockClient::global(array_merge([
        CreateAccessTokenRequest::class => tokenResponse(),
    ], $responses));
}
