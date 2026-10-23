<?php

use CodebarAg\Zendesk\Tests\Fixtures\ZendeskFixture;
use Saloon\Data\RecordedResponse;
use Saloon\Helpers\Storage;

function storeFixture(array $headers, array $body): array
{
    $directory = sys_get_temp_dir().'/laravel-zendesk-fixtures-'.uniqid();
    mkdir($directory);

    new ZendeskFixture('redaction', new Storage($directory, true))
        ->store(new RecordedResponse(200, $headers, json_encode($body)));

    $stored = json_decode(file_get_contents($directory.'/redaction.json'), true);

    unlink($directory.'/redaction.json');
    rmdir($directory);

    return ['headers' => $stored['headers'], 'body' => json_decode($stored['data'], true)];
}

it('redacts oauth tokens', function () {
    $stored = storeFixture([], [
        'access_token' => 'real-access-token',
        'refresh_token' => 'real-refresh-token',
        'token_type' => 'bearer',
        'expires_in' => 1800,
    ]);

    expect($stored['body'])->toBe([
        'access_token' => 'redacted-access-token',
        'refresh_token' => 'redacted-refresh-token',
        'token_type' => 'bearer',
        'expires_in' => 1800,
    ]);
});

it('redacts personal data and keeps nulls', function () {
    $stored = storeFixture([], [
        'ticket' => [
            'subject' => 'Contact jane.doe@acme.ch please',
            'via' => ['source' => ['from' => ['name' => 'Jane Doe', 'address' => 'jane.doe@acme.ch'], 'to' => ['name' => null]]],
            'requester' => ['email' => 'jane.doe@acme.ch', 'phone' => '+41 79 000 00 00'],
        ],
    ]);

    expect($stored['body']['ticket'])->toBe([
        'subject' => 'Contact redacted@example.com please',
        'via' => ['source' => ['from' => ['name' => 'Redacted', 'address' => 'redacted@example.com'], 'to' => ['name' => null]]],
        'requester' => ['email' => 'redacted@example.com', 'phone' => 'REDACTED'],
    ]);
});

it('redacts cookies and tracing headers', function () {
    $stored = storeFixture([
        'Set-Cookie' => ['_zendesk_cookie=secret; path=/'],
        'x-request-id' => ['abc'],
        'Content-Type' => 'application/json',
    ], ['ok' => true]);

    expect($stored['headers'])->toBe([
        'Set-Cookie' => 'REDACTED',
        'x-request-id' => 'REDACTED',
        'Content-Type' => 'application/json',
    ]);
});

it('replaces the real subdomain with the test subdomain', function () {
    putenv('ZENDESK_SUBDOMAIN=acme-real');

    try {
        $stored = storeFixture(['Location' => 'https://acme-real.zendesk.com/api/v2/tickets/1.json'], [
            'url' => 'https://acme-real.zendesk.com/api/v2/tickets/1.json',
        ]);
    } finally {
        putenv('ZENDESK_SUBDOMAIN');
    }

    expect($stored['body']['url'])->toBe('https://codebar-zendesk.zendesk.com/api/v2/tickets/1.json')
        ->and($stored['headers']['Location'])->toBe('https://codebar-zendesk.zendesk.com/api/v2/tickets/1.json');
});

it('only records against the live api when enabled', function () {
    putenv('ZENDESK_RECORD_FIXTURES=true');
    expect(ZendeskFixture::recording())->toBeTrue()
        ->and(new ZendeskFixture('anything')->getMockResponse())->toBeNull();

    putenv('ZENDESK_RECORD_FIXTURES');
    expect(ZendeskFixture::recording())->toBeFalse();
});
