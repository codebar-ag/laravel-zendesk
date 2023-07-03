<?php

use CodebarAg\Zendesk\Requests\CountTicketsRequest;
use CodebarAg\Zendesk\ZendeskConnector;
use Saloon\Http\Faking\MockResponse;
use Saloon\Laravel\Http\Faking\MockClient;

it('can count all tickets', closure: function () {
    $mockClient = new MockClient([
        CountTicketsRequest::class => MockResponse::fixture('count-tickets-request'),
    ]);

    $connector = new ZendeskConnector;
    $connector->withMockClient($mockClient);

    $response = $connector->send(new CountTicketsRequest());

    $mockClient->assertSent(CountTicketsRequest::class);

    expect($response->dto()->value)->toBe(4)
        ->and($response->dto()->refreshed_at->toDateTimeString())->toBe('2023-07-03 13:19:53');
});
