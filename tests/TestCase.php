<?php

namespace CodebarAg\Zendesk\Tests;

use CodebarAg\Zendesk\ZendeskServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Saloon\Config;
use Saloon\Http\Faking\MockClient;
use Saloon\MockConfig;
use Spatie\LaravelData\LaravelDataServiceProvider;
use Spatie\LaravelData\Support\DataConfig;

class TestCase extends Orchestra
{
    private static bool $saloonConfigured = false;

    protected function setUp(): void
    {
        parent::setUp();

        // Never hit the real Zendesk API from the test suite.
        if (! self::$saloonConfigured) {
            Config::preventStrayRequests();
            MockConfig::setFixturePath(__DIR__.'/Fixtures/Saloon');
            MockConfig::throwOnMissingFixtures();
            self::$saloonConfigured = true;
        }

        $this->app->when(DataConfig::class)
            ->needs('$config')
            ->give([]);
    }

    protected function tearDown(): void
    {
        MockClient::destroyGlobal();

        parent::tearDown();
    }

    protected function getPackageProviders($app): array
    {
        return [
            ZendeskServiceProvider::class,
            LaravelDataServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        $app['config']->set('cache.default', 'array');
        $app['config']->set('zendesk.subdomain', 'codebar-zendesk');
        $app['config']->set('zendesk.auth.method', 'oauth');
        $app['config']->set('zendesk.auth.oauth.client_id', 'test-client');
        $app['config']->set('zendesk.auth.oauth.client_secret', 'test-secret');
        $app['config']->set('zendesk.auth.oauth.scopes', 'read write');
        $app['config']->set('zendesk.auth.oauth.expires_in', null);
    }
}
