<?php

use CodebarAg\Zendesk\Enums\TicketPriority;
use CodebarAg\Zendesk\Requests\SingleTicketRequest;
use CodebarAg\Zendesk\ZendeskConnector;
use Saloon\Exceptions\Request\Statuses\NotFoundException;
use Saloon\Http\Faking\MockResponse;

it('can get a single ticket', function () {
    $mockClient = fakeZendesk([
        SingleTicketRequest::class => MockResponse::fixture('single-ticket-request'),
    ]);

    $response = new ZendeskConnector()->send(new SingleTicketRequest(21));

    expect($mockClient->getLastPendingRequest()->getUrl())->toBe('https://codebar-zendesk.zendesk.com/api/v2/tickets/21.json');

    $ticket = $response->dto();

    expect($ticket->id)->toBe(21)
        ->and($ticket->subject)->toBe('My printer is on fire!')
        ->and($ticket->raw_subject)->toBe('My printer is on fire!')
        ->and($ticket->description)->toBe('The smoke is very colorful.')
        ->and($ticket->priority)->toBe(TicketPriority::URGENT)
        ->and($ticket->type)->toBeNull()
        ->and($ticket->status)->toBe('open')
        ->and($ticket->created_at->toIso8601ZuluString())->toBe('2023-09-11T21:08:16Z')
        ->and($ticket->due_at)->toBeNull()
        ->and($ticket->custom_fields[1]['id'])->toBe(10350920893084)
        ->and($ticket->custom_fields[1]['value'])->toBe('Check field works')
        ->and($ticket->custom_fields[2]['id'])->toBe(10350942541980)
        ->and($ticket->custom_fields[2]['value'])->toBe('Check field works number 2');
});

it('throws when creating a dto for a missing ticket', function () {
    fakeZendesk([
        SingleTicketRequest::class => MockResponse::fixture('details-incorrect-request'),
    ]);

    new ZendeskConnector()->send(new SingleTicketRequest(81))->dto();
})->throws(NotFoundException::class);
