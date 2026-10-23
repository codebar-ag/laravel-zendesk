<?php

use CodebarAg\Zendesk\Dto\Tickets\Attachments\UploadDTO;
use CodebarAg\Zendesk\Dto\Tickets\SingleTicketDTO;
use CodebarAg\Zendesk\Enums\TicketPriority;
use CodebarAg\Zendesk\Enums\TicketType;
use CodebarAg\Zendesk\Requests\AllTicketsRequest;
use CodebarAg\Zendesk\Requests\CountTicketsRequest;
use CodebarAg\Zendesk\Requests\CreateAttachmentRequest;
use CodebarAg\Zendesk\Requests\CreateSingleTicketRequest;
use CodebarAg\Zendesk\Requests\SingleTicketRequest;
use CodebarAg\Zendesk\ZendeskConnector;
use Saloon\Exceptions\Request\Statuses\NotFoundException;

// Tickets created while recording are tagged, so they can be found and deleted in Zendesk.
const LIVE_TEST_TAG = 'laravel-zendesk-test';

function uploadTestFile(ZendeskConnector $connector): UploadDTO
{
    return $connector->send(new CreateAttachmentRequest(
        fileName: 'test.png',
        mimeType: 'image/png',
        stream: fopen(__DIR__.'/../Fixtures/Files/test.png', 'r'),
    ))->dtoOrFail();
}

function createTestTicket(ZendeskConnector $connector, array $attributes = []): SingleTicketDTO
{
    return $connector->send(new CreateSingleTicketRequest(array_merge([
        'subject' => 'laravel-zendesk test ticket',
        'comment' => ['body' => 'Created by the laravel-zendesk test suite.', 'public' => false],
        'priority' => TicketPriority::LOW,
        'type' => TicketType::TASK,
        'tags' => [LIVE_TEST_TAG],
    ], $attributes)))->dtoOrFail();
}

it('uploads an attachment', function () {
    requiresFixtures('oauth/token', 'tickets/create-attachment');
    fakeZendeskLive([
        CreateAttachmentRequest::class => zendeskFixture('tickets/create-attachment'),
    ]);

    $upload = uploadTestFile(new ZendeskConnector);

    expect($upload->token)->not->toBeEmpty()
        ->and($upload->expires_at)->not->toBeNull()
        ->and($upload->attachments)->toHaveCount(1)
        ->and($upload->attachment->file_name)->toBe('test.png')
        ->and($upload->attachment->content_type)->toBe('image/png')
        ->and($upload->attachment->size)->toBe(filesize(__DIR__.'/../Fixtures/Files/test.png'))
        ->and($upload->attachment->width)->toBe(640)
        ->and($upload->attachment->height)->toBe(360);
})->group('live');

it('creates a ticket with an attachment and fetches it', function () {
    requiresFixtures('oauth/token', 'tickets/create-attachment', 'tickets/create-ticket', 'tickets/get-ticket');
    fakeZendeskLive([
        CreateAttachmentRequest::class => zendeskFixture('tickets/create-attachment'),
        CreateSingleTicketRequest::class => zendeskFixture('tickets/create-ticket'),
        SingleTicketRequest::class => zendeskFixture('tickets/get-ticket'),
    ]);

    $connector = new ZendeskConnector;
    $upload = uploadTestFile($connector);

    $created = createTestTicket($connector, [
        'comment' => [
            'body' => 'Created by the laravel-zendesk test suite.',
            'public' => false,
            'uploads' => [$upload->token],
        ],
    ]);

    expect($created->id)->toBeInt()
        ->and($created->subject)->toBe('laravel-zendesk test ticket')
        ->and($created->priority)->toBe(TicketPriority::LOW)
        ->and($created->type)->toBe(TicketType::TASK)
        ->and($created->created_at)->not->toBeNull();

    $ticket = $connector->send(new SingleTicketRequest($created->id))->dtoOrFail();

    expect($ticket)->toBeInstanceOf(SingleTicketDTO::class)
        ->and($ticket->id)->toBe($created->id)
        ->and($ticket->subject)->toBe('laravel-zendesk test ticket')
        ->and($ticket->description)->toBe('Created by the laravel-zendesk test suite.')
        ->and($ticket->priority)->toBe(TicketPriority::LOW)
        ->and($ticket->type)->toBe(TicketType::TASK)
        ->and($ticket->tags)->toContain(LIVE_TEST_TAG)
        ->and($ticket->status)->not->toBeNull()
        ->and($ticket->url)->toEndWith("/tickets/{$created->id}.json");
})->group('live');

it('returns a not found response for an unknown ticket', function () {
    requiresFixtures('oauth/token', 'tickets/get-ticket-not-found');
    fakeZendeskLive([
        SingleTicketRequest::class => zendeskFixture('tickets/get-ticket-not-found'),
    ]);

    $response = new ZendeskConnector()->send(new SingleTicketRequest(999999999999));

    expect($response->status())->toBe(404)
        ->and(fn () => $response->dto())->toThrow(NotFoundException::class);
})->group('live');

it('lists the newest tickets first', function () {
    requiresFixtures('oauth/token', 'tickets/create-ticket', 'tickets/list-tickets');
    fakeZendeskLive([
        CreateSingleTicketRequest::class => zendeskFixture('tickets/create-ticket'),
        AllTicketsRequest::class => zendeskFixture('tickets/list-tickets'),
    ]);

    $connector = new ZendeskConnector;
    $created = createTestTicket($connector);

    // Only request the newest ticket, which is the one created above, so no other tickets are recorded.
    $tickets = $connector->send(new AllTicketsRequest(perPage: 1, sortBy: 'created_at', sortOrder: 'desc'))->dtoOrFail();

    expect($tickets->count)->toBeGreaterThanOrEqual(1)
        ->and($tickets->tickets)->toHaveCount(1)
        ->and($tickets->tickets[0])->toBeInstanceOf(SingleTicketDTO::class)
        ->and($tickets->tickets[0]->id)->toBe($created->id)
        ->and($tickets->tickets[0]->tags)->toContain(LIVE_TEST_TAG);

    if ($tickets->count > 1) {
        expect($tickets->next_page_url)->toContain('page=2');
    }
})->group('live');

it('counts tickets', function () {
    requiresFixtures('oauth/token', 'tickets/count-tickets');
    fakeZendeskLive([
        CountTicketsRequest::class => zendeskFixture('tickets/count-tickets'),
    ]);

    $count = new ZendeskConnector()->send(new CountTicketsRequest)->dtoOrFail();

    expect($count->value)->toBeInt()->toBeGreaterThanOrEqual(0)
        ->and($count->refreshed_at)->not->toBeNull();
})->group('live');
