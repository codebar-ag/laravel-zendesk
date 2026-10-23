<?php

namespace CodebarAg\Zendesk\Requests;

use CodebarAg\Zendesk\Dto\Tickets\AllTicketsDTO;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class AllTicketsRequest extends Request
{
    protected Method $method = Method::GET;

    /**
     * @param  int|null  $perPage  Tickets per page, max. 100
     * @param  string|null  $sortBy  e.g. 'created_at', 'updated_at', 'id', 'status', 'subject'
     * @param  string|null  $sortOrder  'asc' or 'desc'
     */
    public function __construct(
        protected readonly ?int $perPage = null,
        protected readonly ?int $page = null,
        protected readonly ?string $sortBy = null,
        protected readonly ?string $sortOrder = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/tickets.json';
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'per_page' => $this->perPage,
            'page' => $this->page,
            'sort_by' => $this->sortBy,
            'sort_order' => $this->sortOrder,
        ], fn (int|string|null $value): bool => $value !== null);
    }

    public function createDtoFromResponse(Response $response): AllTicketsDTO
    {
        $response->throw();

        return AllTicketsDTO::fromResponse($response);
    }
}
