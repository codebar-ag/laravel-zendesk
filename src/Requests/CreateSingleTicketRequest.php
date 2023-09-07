<?php

namespace CodebarAg\Zendesk\Requests;

use CodebarAg\Zendesk\Dto\Tickets\SingleTicketDTO;
use Exception;
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
        readonly protected array|SingleTicketDTO $createTicket,
        readonly protected null|string $onBehalfOf = null,
    ) {
    }

    protected function defaultHeaders(): array
    {
        $headers = [];

        if ($this->onBehalfOf) {
            $headers['X-On-Behalf-Of'] = $this->onBehalfOf;
        }

        return $headers;
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
        if (! $response->successful()) {
            throw new Exception('Request was not successful. Unable to create DTO.');
        }

        return SingleTicketDTO::fromResponse($response);
    }
}
