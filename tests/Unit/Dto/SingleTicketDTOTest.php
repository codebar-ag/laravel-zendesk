<?php

use CodebarAg\Zendesk\Dto\Tickets\Comments\CommentDTO;
use CodebarAg\Zendesk\Dto\Tickets\SingleTicketDTO;
use CodebarAg\Zendesk\Enums\TicketPriority;
use CodebarAg\Zendesk\Enums\TicketType;
use Illuminate\Support\Carbon;

it('keeps missing dates null instead of using the current time', function () {
    $ticket = SingleTicketDTO::fromArray(['subject' => 'No dates']);

    expect($ticket->created_at)->toBeNull()
        ->and($ticket->updated_at)->toBeNull()
        ->and($ticket->due_at)->toBeNull()
        ->and($ticket->updated_stamp)->toBeNull();
});

it('parses dates', function () {
    $ticket = SingleTicketDTO::fromArray([
        'created_at' => '2026-01-02T03:04:05Z',
        'due_at' => Carbon::parse('2026-02-01T00:00:00Z'),
        'updated_stamp' => '2026-01-03T00:00:00Z',
    ]);

    expect($ticket->created_at->toIso8601ZuluString())->toBe('2026-01-02T03:04:05Z')
        ->and($ticket->due_at->toIso8601ZuluString())->toBe('2026-02-01T00:00:00Z')
        ->and($ticket->updated_stamp)->toBeInstanceOf(Carbon::class);
});

it('maps enums from strings and enum instances', function () {
    $fromStrings = SingleTicketDTO::fromArray(['priority' => 'low', 'type' => 'task']);
    $fromEnums = SingleTicketDTO::fromArray(['priority' => TicketPriority::HIGH, 'type' => TicketType::PROBLEM]);
    $unknown = SingleTicketDTO::fromArray(['priority' => 'unknown', 'type' => 'unknown']);

    expect($fromStrings->priority)->toBe(TicketPriority::LOW)
        ->and($fromStrings->type)->toBe(TicketType::TASK)
        ->and($fromEnums->priority)->toBe(TicketPriority::HIGH)
        ->and($fromEnums->type)->toBe(TicketType::PROBLEM)
        ->and($unknown->priority)->toBeNull()
        ->and($unknown->type)->toBeNull();
});

it('maps comments from arrays and dtos', function () {
    $comment = CommentDTO::fromArray(['body' => 'dto']);

    expect(SingleTicketDTO::fromArray(['comment' => ['body' => 'array']])->comment->body)->toBe('array')
        ->and(SingleTicketDTO::fromArray(['comment' => $comment])->comment)->toBe($comment);
});

it('maps custom status and generated timestamp', function () {
    $ticket = SingleTicketDTO::fromArray([
        'custom_status_id' => 123,
        'generated_timestamp' => 1700000000,
    ]);

    expect($ticket->custom_status_id)->toBe(123)
        ->and($ticket->generated_timestamp)->toBe(1700000000);
});

it('builds a request payload without null values', function () {
    $ticket = SingleTicketDTO::fromArray([
        'subject' => 'Payload',
        'priority' => TicketPriority::NORMAL,
        'comment' => ['body' => 'Hello'],
    ]);

    expect($ticket->toRequestArray())->toBe([
        'comment' => ['body' => 'Hello'],
        'priority' => 'normal',
        'subject' => 'Payload',
    ]);
});
