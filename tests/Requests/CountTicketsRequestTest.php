<?php

use CodebarAg\Zendesk\Requests\CountTicketsRequest;
use CodebarAg\Zendesk\ZendeskConnector;
use Saloon\Http\Faking\MockResponse;

it('can count all tickets', function () {
    $mockClient = fakeZendesk([
        CountTicketsRequest::class => MockResponse::fixture('count-tickets-request'),
    ]);

    $response = new ZendeskConnector()->send(new CountTicketsRequest);

    expect($mockClient->getLastPendingRequest()->getUrl())->toBe('https://codebar-zendesk.zendesk.com/api/v2/tickets/count.json')
        ->and($response->dto()->value)->toBe(4)
        ->and($response->dto()->refreshed_at->toDateTimeString())->toBe('2023-09-20 22:08:07');
});
