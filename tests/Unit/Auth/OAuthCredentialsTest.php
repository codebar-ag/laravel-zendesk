<?php

use CodebarAg\Zendesk\Auth\OAuthCredentials;
use CodebarAg\Zendesk\Exceptions\ZendeskConfigurationException;

it('holds the client credentials', function () {
    $credentials = new OAuthCredentials('client', 'secret', 'tickets:read', 600);

    expect($credentials->clientId)->toBe('client')
        ->and($credentials->clientSecret)->toBe('secret')
        ->and($credentials->scopes)->toBe('tickets:read')
        ->and($credentials->expiresIn)->toBe(600);
});

it('defaults to read write scopes without an expiry', function () {
    $credentials = new OAuthCredentials('client', 'secret');

    expect($credentials->scopes)->toBe('read write')
        ->and($credentials->expiresIn)->toBeNull();
});

it('normalizes scopes to a space separated string', function (string|array $scopes, string $expected) {
    expect(new OAuthCredentials('client', 'secret', $scopes)->scopes)->toBe($expected);
})->with([
    'array' => [['tickets:read', 'tickets:write'], 'tickets:read tickets:write'],
    'comma separated' => ['tickets:read,tickets:write', 'tickets:read tickets:write'],
    'extra whitespace' => ['  read   write ', 'read write'],
    'newlines' => ["read\nwrite", 'read write'],
]);

it('rejects missing credentials', function (string $clientId, string $clientSecret, string $message) {
    expect(fn () => new OAuthCredentials($clientId, $clientSecret))
        ->toThrow(ZendeskConfigurationException::class, $message);
})->with([
    'client id' => ['', 'secret', 'No OAuth client ID provided.'],
    'blank client id' => ['  ', 'secret', 'No OAuth client ID provided.'],
    'client secret' => ['client', '', 'No OAuth client secret provided.'],
]);

it('rejects empty scopes', function (string|array $scopes) {
    expect(fn () => new OAuthCredentials('client', 'secret', $scopes))
        ->toThrow(ZendeskConfigurationException::class, 'No OAuth scopes provided.');
})->with([
    'empty string' => [''],
    'whitespace' => ['   '],
    'empty array' => [[]],
]);

it('accepts token lifetimes within the Zendesk limits', function (int $expiresIn) {
    expect(new OAuthCredentials('client', 'secret', expiresIn: $expiresIn)->expiresIn)->toBe($expiresIn);
})->with([300, 1800, 172800]);

it('rejects token lifetimes outside the Zendesk limits', function (int $expiresIn) {
    expect(fn () => new OAuthCredentials('client', 'secret', expiresIn: $expiresIn))
        ->toThrow(ZendeskConfigurationException::class, 'between 300 and 172800 seconds');
})->with([0, 299, 172801]);

it('can be created from the config', function () {
    config([
        'zendesk.auth.oauth.client_id' => 'config-client',
        'zendesk.auth.oauth.client_secret' => 'config-secret',
        'zendesk.auth.oauth.scopes' => 'tickets:read',
        'zendesk.auth.oauth.expires_in' => '3600',
    ]);

    $credentials = OAuthCredentials::fromConfig();

    expect($credentials->clientId)->toBe('config-client')
        ->and($credentials->clientSecret)->toBe('config-secret')
        ->and($credentials->scopes)->toBe('tickets:read')
        ->and($credentials->expiresIn)->toBe(3600);
});

it('falls back to defaults for optional config values', function () {
    config([
        'zendesk.auth.oauth.scopes' => null,
        'zendesk.auth.oauth.expires_in' => '',
    ]);

    $credentials = OAuthCredentials::fromConfig();

    expect($credentials->scopes)->toBe('read write')
        ->and($credentials->expiresIn)->toBeNull();
});

it('throws when the config is missing the client credentials', function () {
    config(['zendesk.auth.oauth.client_id' => null]);

    OAuthCredentials::fromConfig();
})->throws(ZendeskConfigurationException::class, 'No OAuth client ID provided.');
