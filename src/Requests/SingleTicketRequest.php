<?php

namespace CodebarAg\Zendesk\Requests;

use CodebarAg\Zendesk\Dto\Tickets\SingleTicketDTO;
use Saloon\Contracts\Response;
use Saloon\Enums\Method;
use Saloon\Http\Request;

class SingleTicketRequest extends Request
{
    protected Method $method = Method::GET;

    protected int $ticketId;

    public function __construct(int $ticketId)
    {
        $this->ticketId = $ticketId;
    }

    public function resolveEndpoint(): string
    {
        return '/tickets/'.$this->ticketId.'.json';
    }

    public function createDtoFromResponse(Response $response): mixed
    {
        return SingleTicketDTO::fromResponse($response);
    }
}
