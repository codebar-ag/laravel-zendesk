<?php

use CodebarAg\Zendesk\Requests\CountTicketsRequest;
use CodebarAg\Zendesk\ZendeskConnector;
use Saloon\Http\Faking\MockResponse;

it('counts tickets', function () {
    $mockClient = fakeZendesk([
        CountTicketsRequest::class => MockResponse::make([
            'count' => ['value' => 4, 'refreshed_at' => '2026-10-23T12:00:00+00:00'],
        ]),
    ]);

    $dto = new ZendeskConnector()->send(new CountTicketsRequest)->dto();

    expect($mockClient->getLastPendingRequest()->getUrl())->toBe('https://codebar-zendesk.zendesk.com/api/v2/tickets/count.json')
        ->and($dto->value)->toBe(4)
        ->and($dto->refreshed_at->toDateTimeString())->toBe('2026-10-23 12:00:00');
});
