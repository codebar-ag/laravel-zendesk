<?php

use CodebarAg\Zendesk\Dto\Tickets\Comments\CommentDTO;
use CodebarAg\Zendesk\Dto\Tickets\SingleTicketDTO;
use CodebarAg\Zendesk\Enums\TicketPriority;
use CodebarAg\Zendesk\Enums\TicketType;
use CodebarAg\Zendesk\Requests\CreateSingleTicketRequest;
use CodebarAg\Zendesk\ZendeskConnector;
use Saloon\Exceptions\Request\Statuses\UnprocessableEntityException;
use Saloon\Http\Faking\MockResponse;

function createdResponse(): MockResponse
{
    return MockResponse::make([
        'ticket' => [
            'id' => 26,
            'subject' => 'My printer is on fire!',
            'raw_subject' => 'My printer is on fire!',
            'description' => 'The smoke is very colorful.',
            'priority' => 'urgent',
            'custom_fields' => [
                ['id' => 10350920893084, 'value' => 'Check field works'],
            ],
        ],
    ], 201);
}

$ticket = fn (): array => [
    'comment' => CommentDTO::fromArray([
        'body' => 'The smoke is very colorful.',
    ]),
    'priority' => TicketPriority::URGENT,
    'subject' => 'My printer is on fire!',
    'custom_fields' => [
        ['id' => 10350920893084, 'value' => 'Check field works'],
        ['id' => 10350942541980, 'value' => 'Check field works number 2'],
    ],
];

it('can create a single ticket', function () use ($ticket) {
    $mockClient = fakeZendesk([
        CreateSingleTicketRequest::class => createdResponse(),
    ]);

    $response = new ZendeskConnector()->send(new CreateSingleTicketRequest($ticket()));

    $pendingRequest = $mockClient->getLastPendingRequest();

    expect($pendingRequest->getMethod()->value)->toBe('POST')
        ->and($pendingRequest->getUrl())->toBe('https://codebar-zendesk.zendesk.com/api/v2/tickets.json')
        ->and($pendingRequest->headers()->get('Content-Type'))->toBe('application/json');

    $dto = $response->dto();

    expect($response->status())->toBe(201)
        ->and($dto->id)->toBe(26)
        ->and($dto->subject)->toBe('My printer is on fire!')
        ->and($dto->raw_subject)->toBe('My printer is on fire!')
        ->and($dto->description)->toBe('The smoke is very colorful.')
        ->and($dto->priority)->toBe(TicketPriority::URGENT)
        ->and($dto->custom_fields[0]['id'])->toBe(10350920893084)
        ->and($dto->custom_fields[0]['value'])->toBe('Check field works');
});

it('only sends the attributes that were set', function () use ($ticket) {
    $mockClient = fakeZendesk([
        CreateSingleTicketRequest::class => createdResponse(),
    ]);

    new ZendeskConnector()->send(new CreateSingleTicketRequest($ticket()));

    expect($mockClient->getLastPendingRequest()->body()->all())->toBe([
        'ticket' => [
            'comment' => ['body' => 'The smoke is very colorful.'],
            'custom_fields' => [
                ['id' => 10350920893084, 'value' => 'Check field works'],
                ['id' => 10350942541980, 'value' => 'Check field works number 2'],
            ],
            'priority' => 'urgent',
            'subject' => 'My printer is on fire!',
        ],
    ]);
});

it('accepts a dto and serializes enums, comments and uploads', function () {
    $mockClient = fakeZendesk([
        CreateSingleTicketRequest::class => createdResponse(),
    ]);

    new ZendeskConnector()->send(new CreateSingleTicketRequest(SingleTicketDTO::fromArray([
        'subject' => 'Printer',
        'type' => TicketType::INCIDENT,
        'priority' => 'high',
        'tags' => ['printer'],
        'comment' => [
            'body' => 'See attachment',
            'public' => false,
            'uploads' => ['upload-token'],
        ],
    ])));

    expect($mockClient->getLastPendingRequest()->body()->all()['ticket'])->toBe([
        'comment' => [
            'body' => 'See attachment',
            'public' => false,
            'uploads' => ['upload-token'],
        ],
        'priority' => 'high',
        'subject' => 'Printer',
        'tags' => ['printer'],
        'type' => 'incident',
    ]);
});

it('keeps null values inside custom fields', function () {
    $mockClient = fakeZendesk([
        CreateSingleTicketRequest::class => createdResponse(),
    ]);

    new ZendeskConnector()->send(new CreateSingleTicketRequest([
        'subject' => 'Clear a field',
        'custom_fields' => [['id' => 1, 'value' => null]],
    ]));

    expect($mockClient->getLastPendingRequest()->body()->all()['ticket']['custom_fields'])
        ->toBe([['id' => 1, 'value' => null]]);
});

it('throws when creating a dto from a validation error', function () {
    fakeZendesk([
        CreateSingleTicketRequest::class => MockResponse::make([
            'error' => 'RecordInvalid',
            'description' => 'Record validation errors',
        ], 422),
    ]);

    $response = new ZendeskConnector()->send(new CreateSingleTicketRequest(['subject' => 'Invalid']));

    expect($response->status())->toBe(422)
        ->and(fn () => $response->dto())->toThrow(UnprocessableEntityException::class);
});
