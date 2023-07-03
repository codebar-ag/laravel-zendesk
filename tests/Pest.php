<?php

use CodebarAg\Zendesk\Tests\TestCase;
use Illuminate\Support\Facades\Event;

uses(TestCase::class)->in(__DIR__);

uses()->beforeEach(function () {
    Event::fake();
})->in(__DIR__);

uses()->beforeEach(function () {
    config([
        'zendesk.subdomain' => 'codebarsolutionsag',
        'zendesk.auth.method' => 'token',
        'zendesk.auth.email_address' => 'invoice@codebar.ch',
        'zendesk.auth.api_token' => 'TqxDOhfFVseETyvdQ0ISV6JKaXuXtmoFRX67gKnZ',
    ]);
})->in(__DIR__.'/Requests');
