<?php

namespace CodebarAg\Zendesk\Dto\OAuth;

use Illuminate\Support\Arr;
use Saloon\Http\Response;
use Spatie\LaravelData\Data;

class AccessTokenDTO extends Data
{
    public function __construct(
        public string $access_token,
        public string $token_type,
        public ?string $scope,
        public ?int $expires_in,
        public ?string $refresh_token,
        public ?int $refresh_token_expires_in,
    ) {}

    public static function fromResponse(Response $response): self
    {
        return self::fromArray($response->json());
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            access_token: (string) Arr::get($data, 'access_token'),
            token_type: (string) Arr::get($data, 'token_type', 'bearer'),
            scope: Arr::get($data, 'scope'),
            expires_in: Arr::get($data, 'expires_in'),
            refresh_token: Arr::get($data, 'refresh_token'),
            refresh_token_expires_in: Arr::get($data, 'refresh_token_expires_in'),
        );
    }
}
