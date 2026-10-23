<?php

namespace CodebarAg\Zendesk\Dto\Tickets;

use Illuminate\Support\Arr;
use Saloon\Http\Response;
use Spatie\LaravelData\Data;

class AllTicketsDTO extends Data
{
    /**
     * @param  array<int, SingleTicketDTO>  $tickets
     */
    public function __construct(
        public array $tickets,
        public int $count,
        public ?string $next_page_url,
        public ?string $previous_page_url,
    ) {}

    public static function fromResponse(Response $response): self
    {
        return self::fromArray((array) $response->json());
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            tickets: array_map(
                fn (array $ticket): SingleTicketDTO => SingleTicketDTO::fromArray($ticket),
                array_values((array) Arr::get($data, 'tickets', [])),
            ),
            count: (int) Arr::get($data, 'count', 0),
            next_page_url: Arr::get($data, 'next_page'),
            previous_page_url: Arr::get($data, 'previous_page'),
        );
    }
}
