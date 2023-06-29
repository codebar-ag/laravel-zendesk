<?php

namespace CodebarAg\Zendesk\Requests;

use CodebarAg\Zendesk\Dto\Tickets\AllTicketsDTO;
use Saloon\Contracts\Response;
use Saloon\Enums\Method;
use Saloon\Http\Request;

class AllTicketsRequest extends Request
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/tickets.json';
    }

    public function createDtoFromResponse(Response $response): mixed
    {
        return AllTicketsDTO::fromResponse($response);
    }
}
