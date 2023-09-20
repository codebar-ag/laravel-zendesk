<?php

use CodebarAg\Zendesk\Tests\TestCase;
use Illuminate\Support\Facades\Event;

uses(TestCase::class)->in(__DIR__);

uses()->beforeEach(function () {
	Event::fake();
})->in(__DIR__);

uses()->beforeEach(function () {
	config([
		'zendesk.subdomain' => 'subdomain',
		'zendesk.auth.method' => 'token',
		'zendesk.auth.email_address' => 'test@example.com',
		'zendesk.auth.api_token' => 'token',
	]);
})->in(__DIR__ . '/Requests');
