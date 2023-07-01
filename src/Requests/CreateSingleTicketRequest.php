<?php

namespace CodebarAg\Zendesk\Requests;

use CodebarAg\Zendesk\Dto\Tickets\CreateTicketDTO;
use CodebarAg\Zendesk\Dto\Tickets\SingleTicketDTO;
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
        readonly protected array|SingleTicketDTO $createTicket
    ) {
    }

    protected function defaultBody(): array
    {
        $body = $this->createTicket;

        if (! $body instanceof SingleTicketDTO) {
            $body = SingleTicketDTO::fromArray($body);
        }

        return [
            'ticket' => $body->toArray(),
        ];
    }

    public function createDtoFromResponse(Response $response): mixed
    {
        return SingleTicketDTO::fromResponse($response);
    }
}
