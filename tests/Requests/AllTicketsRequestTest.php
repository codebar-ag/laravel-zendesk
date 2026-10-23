<?php

use CodebarAg\Zendesk\Dto\Tickets\AllTicketsDTO;
use CodebarAg\Zendesk\Dto\Tickets\SingleTicketDTO;
use CodebarAg\Zendesk\Enums\TicketPriority;
use CodebarAg\Zendesk\Requests\AllTicketsRequest;
use CodebarAg\Zendesk\ZendeskConnector;
use Saloon\Http\Faking\MockResponse;

it('can get all tickets', function () {
    $mockClient = fakeZendesk([
        AllTicketsRequest::class => MockResponse::fixture('all-tickets-request'),
    ]);

    $response = new ZendeskConnector()->send(new AllTicketsRequest);

    $pendingRequest = $mockClient->getLastPendingRequest();
    expect($pendingRequest->getMethod()->value)->toBe('GET')
        ->and($pendingRequest->getUrl())->toBe('https://codebar-zendesk.zendesk.com/api/v2/tickets.json');

    $dto = $response->dto();
    $ticket = $dto->tickets[0];

    expect($dto)->toBeInstanceOf(AllTicketsDTO::class)
        ->and($dto->count)->toBe(6)
        ->and($dto->tickets)->toHaveCount(6)
        ->each->toBeInstanceOf(SingleTicketDTO::class)
        ->and($ticket->id)->toBe(21)
        ->and($ticket->subject)->toBe('My printer is on fire!')
        ->and($ticket->raw_subject)->toBe('My printer is on fire!')
        ->and($ticket->description)->toBe('The smoke is very colorful.')
        ->and($ticket->priority)->toBe(TicketPriority::URGENT)
        ->and($ticket->due_at)->toBeNull()
        ->and($ticket->custom_fields[1]['id'])->toBe(10350920893084)
        ->and($ticket->custom_fields[1]['value'])->toBe('Check field works')
        ->and($ticket->custom_fields[2]['id'])->toBe(10350942541980)
        ->and($ticket->custom_fields[2]['value'])->toBe('Check field works number 2');
});
