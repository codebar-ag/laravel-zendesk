<?php

namespace CodebarAg\Zendesk\Dto\Tickets;

use CodebarAg\Zendesk\Dto\Concerns\ParsesDates;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Saloon\Http\Response;
use Spatie\LaravelData\Data;

class CountTicketsDTO extends Data
{
    use ParsesDates;

    public function __construct(
        public ?int $value,
        public ?Carbon $refreshed_at,
    ) {}

    public static function fromResponse(Response $response): self
    {
        return self::fromArray((array) $response->json('count'));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            value: Arr::get($data, 'value'),
            refreshed_at: self::parseDate(Arr::get($data, 'refreshed_at')),
        );
    }
}
