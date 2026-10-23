<?php

use CodebarAg\Zendesk\Dto\Tickets\AllTicketsDTO;
use CodebarAg\Zendesk\Dto\Tickets\SingleTicketDTO;
use CodebarAg\Zendesk\Enums\TicketPriority;
use CodebarAg\Zendesk\Requests\AllTicketsRequest;
use CodebarAg\Zendesk\ZendeskConnector;
use Saloon\Http\Faking\MockResponse;

it('lists tickets', function () {
    $mockClient = fakeZendesk([
        AllTicketsRequest::class => MockResponse::make([
            'tickets' => [
                ['id' => 2, 'subject' => 'Second', 'priority' => 'urgent'],
                ['id' => 1, 'subject' => 'First', 'priority' => null],
            ],
            'count' => 2,
            'next_page' => null,
            'previous_page' => null,
        ]),
    ]);

    $dto = new ZendeskConnector()->send(new AllTicketsRequest)->dto();

    $pendingRequest = $mockClient->getLastPendingRequest();

    expect($pendingRequest->getMethod()->value)->toBe('GET')
        ->and($pendingRequest->getUrl())->toBe('https://codebar-zendesk.zendesk.com/api/v2/tickets.json')
        ->and($pendingRequest->query()->all())->toBe([])
        ->and($dto)->toBeInstanceOf(AllTicketsDTO::class)
        ->and($dto->count)->toBe(2)
        ->and($dto->tickets)->each->toBeInstanceOf(SingleTicketDTO::class)
        ->and($dto->tickets[0]->id)->toBe(2)
        ->and($dto->tickets[0]->priority)->toBe(TicketPriority::URGENT)
        ->and($dto->tickets[1]->priority)->toBeNull();
});

it('sends pagination and sorting parameters', function () {
    $mockClient = fakeZendesk([
        AllTicketsRequest::class => MockResponse::make(['tickets' => [], 'count' => 0]),
    ]);

    new ZendeskConnector()->send(new AllTicketsRequest(perPage: 25, page: 2, sortBy: 'updated_at', sortOrder: 'desc'));

    expect($mockClient->getLastPendingRequest()->query()->all())->toBe([
        'per_page' => 25,
        'page' => 2,
        'sort_by' => 'updated_at',
        'sort_order' => 'desc',
    ]);
});
