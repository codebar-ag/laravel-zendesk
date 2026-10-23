<?php

namespace CodebarAg\Zendesk\Requests;

use CodebarAg\Zendesk\Dto\Tickets\AllTicketsDTO;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class AllTicketsRequest extends Request
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/tickets.json';
    }

    public function createDtoFromResponse(Response $response): AllTicketsDTO
    {
        $response->throw();

        return AllTicketsDTO::fromResponse($response);
    }
}
