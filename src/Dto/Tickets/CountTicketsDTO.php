<?php

namespace CodebarAg\Zendesk\Dto\Tickets;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Saloon\Http\Response;
use Spatie\LaravelData\Data;

class CountTicketsDTO extends Data
{
    public function __construct(
        public ?int $value,
        public ?Carbon $refreshed_at,
    ) {
    }

    public static function fromResponse(Response $response): self
    {
        $data = Arr::get($response->json(), 'count');

        if (! $data) {
            throw new Exception('Unable to create DTO. Data missing from response.');
        }

        return new self(
            value: $data['value'],
            refreshed_at: Carbon::parse($data['refreshed_at']),
        );
    }
}
