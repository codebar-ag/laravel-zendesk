<?php

namespace CodebarAg\Zendesk\Requests;

use CodebarAg\Zendesk\Dto\Tickets\SingleTicketDTO;
use Exception;
use Saloon\Contracts\Response;
use Saloon\Enums\Method;
use Saloon\Http\Request;

class ShowUserRequest extends Request
{
    protected Method $method = Method::GET;

    protected int|string $userId;

    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    public function resolveEndpoint(): string
    {
        return '/users/'.$this->userId.'.json';
    }

    public function createDtoFromResponse(Response $response): mixed
    {
        if (! $response->successful()) {
            throw new Exception('Request was not successful. Unable to create DTO.');
        }

        return $response->json();
    }
}
