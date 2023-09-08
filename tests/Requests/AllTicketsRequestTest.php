<?php

use CodebarAg\Zendesk\Enums\TicketPriority;
use CodebarAg\Zendesk\Requests\AllTicketsRequest;
use CodebarAg\Zendesk\ZendeskConnector;
use Saloon\Http\Faking\MockResponse;
use Saloon\Laravel\Http\Faking\MockClient;

it('can get all tickets', closure: function () {
    $mockClient = new MockClient([
        AllTicketsRequest::class => MockResponse::fixture('all-tickets-request'),
    ]);

    $connector = new ZendeskConnector;
    $connector->withMockClient($mockClient);

    $response = $connector->send(new AllTicketsRequest());

    $mockClient->assertSent(AllTicketsRequest::class);

    expect($response->dto()->count)->toBe(1)
        ->and($response->dto()->tickets[0]['id'])->toBe(15)
        ->and($response->dto()->tickets[0]['subject'])->toBe('My printer is on fire!')
        ->and($response->dto()->tickets[0]['raw_subject'])->toBe('My printer is on fire!')
        ->and($response->dto()->tickets[0]['description'])->toBe('The smoke is very colorful.')
        ->and($response->dto()->tickets[0]['priority'])->toBe(TicketPriority::URGENT)
        ->and($response->dto()->tickets[0]['custom_fields'][1]['id'])->toBe(17195718961677)
        ->and($response->dto()->tickets[0]['custom_fields'][1]['value'])->toBe('Check field works')
        ->and($response->dto()->tickets[0]['custom_fields'][2]['id'])->toBe(17195752153741)
        ->and($response->dto()->tickets[0]['custom_fields'][2]['value'])->toBe('Check field works number 2');
});
