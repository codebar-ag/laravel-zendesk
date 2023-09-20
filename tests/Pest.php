<?php

use CodebarAg\Zendesk\Tests\TestCase;
use Illuminate\Support\Facades\Event;

uses(TestCase::class)->in(__DIR__);

uses()->beforeEach(function () {
    Event::fake();
})->in(__DIR__);

uses()->beforeAll(function () {
    config()->set('zendesk.subdomain', 'codebar-zendesk');
    config()->set('zendesk.credentials.email', 'fake-email');
    config()->set('zendesk.credentials.token', 'fake-token');
})->in(__DIR__);
