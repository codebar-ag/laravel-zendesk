<?php

namespace CodebarAg\Zendesk\Requests;

use CodebarAg\Zendesk\Dto\Tickets\SingleTicketDTO;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class SingleTicketRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly int $ticketId,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/tickets/'.$this->ticketId.'.json';
    }

    public function createDtoFromResponse(Response $response): SingleTicketDTO
    {
        $response->throw();

        return SingleTicketDTO::fromResponse($response);
    }
}
