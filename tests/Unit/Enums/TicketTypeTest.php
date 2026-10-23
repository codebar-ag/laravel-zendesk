<?php

use CodebarAg\Zendesk\Enums\TicketType;

it('has expected string values', function () {
    expect(TicketType::INCIDENT->value)->toBe('incident')
        ->and(TicketType::PROBLEM->value)->toBe('problem')
        ->and(TicketType::QUESTION->value)->toBe('question')
        ->and(TicketType::TASK->value)->toBe('task');
});
