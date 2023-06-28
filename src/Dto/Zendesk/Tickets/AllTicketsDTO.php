<?php

namespace CodebarAg\Zendesk\Dto\Zendesk\Tickets;

use Illuminate\Support\Collection;
use Saloon\Http\Response;
use Spatie\LaravelData\Data;

class AllTicketsDTO extends Data
{
    public function __construct(
        public Collection $tickets,
        public int $count,
        public ?string $next_page_url,
        public ?string $previous_page_url,
    ) {
    }

    public static function fromResponse(Response $response): self
    {
        $data = $response->json();

        return new static(
            tickets: collect($data['tickets'])->map(function (array $ticket) {
                return SingleTicketDTO::fromArray($ticket);
            }),
            count: $data['count'],
            next_page_url: $data['next_page'],
            previous_page_url: $data['previous_page'],
        );
    }
}
