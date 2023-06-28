<?php

namespace CodebarAg\Zendesk\Http\Connector\Zendesk\Requests;

use CodebarAg\Zendesk\Dto\Zendesk\Tickets\CountTicketsDTO;
use Saloon\Contracts\Response;
use Saloon\Enums\Method;
use Saloon\Http\Request;

class CountTicketsRequest extends Request
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/tickets/count.json';
    }

    public function createDtoFromResponse(Response $response): mixed
    {
        return CountTicketsDTO::fromResponse($response);
    }
}
