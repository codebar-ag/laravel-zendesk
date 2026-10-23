<?php

use CodebarAg\Zendesk\Auth\AuthMethod;
use CodebarAg\Zendesk\Auth\OAuthCredentials;
use CodebarAg\Zendesk\Facades\Zendesk as ZendeskFacade;
use CodebarAg\Zendesk\Zendesk;
use CodebarAg\Zendesk\ZendeskConnector;
use CodebarAg\Zendesk\ZendeskServiceProvider;
use Illuminate\Support\ServiceProvider;

it('is registered as a singleton', function () {
    expect(app(Zendesk::class))->toBe(app(Zendesk::class));
});

it('creates connectors', function () {
    $connector = app(Zendesk::class)->connector();

    expect($connector)->toBeInstanceOf(ZendeskConnector::class)
        ->and($connector->resolveBaseUrl())->toBe('https://codebar-zendesk.zendesk.com/api/v2');
});

it('creates connectors for other accounts', function () {
    $connector = ZendeskFacade::connector('acme', new OAuthCredentials('client', 'secret'));

    expect($connector->resolveBaseUrl())->toBe('https://acme.zendesk.com/api/v2')
        ->and($connector->authMethod())->toBe(AuthMethod::OAUTH);
});

it('merges the package config', function () {
    expect(config('zendesk.auth.method'))->toBe('oauth')
        ->and(config('zendesk.cache.prefix'))->toBe('zendesk:oauth:')
        ->and(config('zendesk.cache.fallback_ttl'))->toBe(86400);
});

it('publishes the config with the documented tag', function () {
    $paths = ServiceProvider::pathsToPublish(ZendeskServiceProvider::class, 'zendesk-config');

    expect($paths)->toHaveCount(1)
        ->and(array_values($paths)[0])->toEndWith('config/zendesk.php');
});
