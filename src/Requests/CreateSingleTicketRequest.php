<?php

namespace CodebarAg\Zendesk\Http\Connector\Zendesk\Requests;

use CodebarAg\Zendesk\Dto\Zendesk\Tickets\CreateTicketDTO;
use CodebarAg\Zendesk\Dto\Zendesk\Tickets\SingleTicketDTO;
use Saloon\Contracts\Body\HasBody;
use Saloon\Contracts\Response;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class CreateSingleTicketRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function resolveEndpoint(): string
    {
        return '/tickets';
    }

    public function __construct(
        readonly protected CreateTicketDTO $createTicketDTO
    ) {
    }

    protected function defaultBody(): array
    {
        return [
            'ticket' => $this->createTicketDTO->toArray(),
        ];
    }

    public function createDtoFromResponse(Response $response): mixed
    {
        $response = $response->json();

        return SingleTicketDTO::fromResponse($response);
    }
}
