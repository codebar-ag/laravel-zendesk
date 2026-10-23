<?php

use CodebarAg\Zendesk\Enums\TicketPriority;
use CodebarAg\Zendesk\Requests\SingleTicketRequest;
use CodebarAg\Zendesk\ZendeskConnector;
use Saloon\Exceptions\Request\Statuses\NotFoundException;
use Saloon\Http\Faking\MockResponse;

it('gets a single ticket', function () {
    $mockClient = fakeZendesk([
        SingleTicketRequest::class => MockResponse::make([
            'ticket' => [
                'id' => 21,
                'subject' => 'My printer is on fire!',
                'priority' => 'urgent',
                'created_at' => '2026-10-23T12:00:00Z',
                'due_at' => null,
            ],
        ]),
    ]);

    $ticket = new ZendeskConnector()->send(new SingleTicketRequest(21))->dto();

    expect($mockClient->getLastPendingRequest()->getUrl())->toBe('https://codebar-zendesk.zendesk.com/api/v2/tickets/21.json')
        ->and($ticket->id)->toBe(21)
        ->and($ticket->subject)->toBe('My printer is on fire!')
        ->and($ticket->priority)->toBe(TicketPriority::URGENT)
        ->and($ticket->created_at->toIso8601ZuluString())->toBe('2026-10-23T12:00:00Z')
        ->and($ticket->due_at)->toBeNull();
});

it('throws when creating a dto for a missing ticket', function () {
    fakeZendesk([
        SingleTicketRequest::class => MockResponse::make(['error' => 'RecordNotFound', 'description' => 'Not found'], 404),
    ]);

    new ZendeskConnector()->send(new SingleTicketRequest(81))->dto();
})->throws(NotFoundException::class);
