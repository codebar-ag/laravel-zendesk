<?php

namespace CodebarAg\Zendesk\Requests;

use CodebarAg\Zendesk\Dto\Tickets\SingleTicketDTO;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

class CreateSingleTicketRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    /**
     * @param  array<string, mixed>|SingleTicketDTO  $ticket
     */
    public function __construct(
        protected readonly array|SingleTicketDTO $ticket,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/tickets.json';
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultBody(): array
    {
        $ticket = $this->ticket instanceof SingleTicketDTO
            ? $this->ticket
            : SingleTicketDTO::fromArray($this->ticket);

        return [
            'ticket' => $ticket->toRequestArray(),
        ];
    }

    public function createDtoFromResponse(Response $response): SingleTicketDTO
    {
        $response->throw();

        return SingleTicketDTO::fromResponse($response);
    }
}
