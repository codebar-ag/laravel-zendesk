<?php

namespace CodebarAg\Zendesk\Dto\Tickets;

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
        if ($response->failed()) {
            throw new \Exception('Failed to get tickets count', $response->status());
        }

        $data = $response->json()['count'];

        return new self(
            value: $data['value'],
            refreshed_at: Carbon::parse($data['refreshed_at']),
        );
    }
}
