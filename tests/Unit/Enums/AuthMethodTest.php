<?php

use CodebarAg\Zendesk\Auth\AuthMethod;

it('has expected string values', function () {
    expect(AuthMethod::OAUTH->value)->toBe('oauth')
        ->and(AuthMethod::ACCESS_TOKEN->value)->toBe('access_token')
        ->and(AuthMethod::cases())->toHaveCount(2);
});
