<?php

use CodebarAg\Zendesk\Tests\TestCase;
use Illuminate\Support\Facades\Event;

uses(TestCase::class)->in(__DIR__);

uses()->beforeEach(function () {
    Event::fake();
})->in(__DIR__);

uses()->beforeAll(function () {
    if (is_dir('Fixtures/Saloon') && count(scandir('Fixtures/Saloon')) > 0) {
        config()->set('zendesk.subdomain', 'codebar-zendesk');
        config()->set('zendesk.auth.email_address', 'fake-email');
        config()->set('zendesk.auth.api_token', 'fake-token');
    }
})->in(__DIR__);
