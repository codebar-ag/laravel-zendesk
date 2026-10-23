<?php

use CodebarAg\Zendesk\Enums\TicketPriority;

it('has expected string values', function () {
    expect(TicketPriority::URGENT->value)->toBe('urgent')
        ->and(TicketPriority::HIGH->value)->toBe('high')
        ->and(TicketPriority::NORMAL->value)->toBe('normal')
        ->and(TicketPriority::LOW->value)->toBe('low');
});
