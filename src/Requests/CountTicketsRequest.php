<?php

namespace CodebarAg\Zendesk\Requests;

use CodebarAg\Zendesk\Dto\Tickets\CountTicketsDTO;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class CountTicketsRequest extends Request
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/tickets/count.json';
    }

    public function createDtoFromResponse(Response $response): CountTicketsDTO
    {
        $response->throw();

        return CountTicketsDTO::fromResponse($response);
    }
}
