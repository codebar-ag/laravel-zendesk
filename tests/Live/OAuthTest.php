<?php

use CodebarAg\Zendesk\Auth\OAuthCredentials;
use CodebarAg\Zendesk\Exceptions\ZendeskAuthenticationException;
use CodebarAg\Zendesk\Requests\OAuth\CreateAccessTokenRequest;
use CodebarAg\Zendesk\ZendeskConnector;
use Illuminate\Support\Facades\Cache;

it('obtains an access token with the client credentials grant', function () {
    requiresFixtures('oauth/token');
    $mockClient = fakeZendeskLive();

    $connector = new ZendeskConnector;
    $token = $connector->tokens()->get();

    expect($token)->toBeString()->not->toBeEmpty()
        ->and(Cache::get($connector->tokens()->key()))->toBe($token);

    $mockClient->assertSent(CreateAccessTokenRequest::class);
    expect($mockClient->getLastResponse()->json('token_type'))->toBe('bearer')
        ->and($mockClient->getLastResponse()->json('expires_in'))->toBeInt();
})->group('live');

it('rejects invalid client credentials', function () {
    requiresFixtures('oauth/token-invalid-client');
    fakeZendeskLive([
        CreateAccessTokenRequest::class => zendeskFixture('oauth/token-invalid-client'),
    ]);

    $connector = new ZendeskConnector(credentials: new OAuthCredentials(
        clientId: (string) config('zendesk.auth.oauth.client_id'),
        clientSecret: 'invalid-client-secret',
    ));

    expect(fn () => $connector->tokens()->get())->toThrow(function (ZendeskAuthenticationException $exception) {
        expect($exception->getMessage())->toStartWith('Unable to obtain a Zendesk OAuth access token')
            ->and($exception->getCode())->toBeGreaterThanOrEqual(400)->toBeLessThan(500);
    });
})->group('live');
