<?php

namespace CodebarAg\Zendesk\Requests;

use CodebarAg\Zendesk\Dto\Tickets\Attachments\UploadDTO;
use Psr\Http\Message\StreamInterface;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasStreamBody;

class CreateAttachmentRequest extends Request implements HasBody
{
    use HasStreamBody;

    protected Method $method = Method::POST;

    /**
     * @param  resource|StreamInterface  $stream
     * @param  string|null  $token  Upload token of a previous upload to add this file to the same upload
     */
    public function __construct(
        protected readonly string $fileName,
        protected readonly string $mimeType,
        protected readonly mixed $stream,
        protected readonly ?string $token = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/uploads.json';
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'filename' => $this->fileName,
            'token' => $this->token,
        ], fn (?string $value): bool => $value !== null);
    }

    protected function defaultHeaders(): array
    {
        return [
            'Content-Type' => $this->mimeType,
            'Accept' => 'application/json',
        ];
    }

    protected function defaultBody(): mixed
    {
        return $this->stream;
    }

    public function createDtoFromResponse(Response $response): UploadDTO
    {
        $response->throw();

        return UploadDTO::fromResponse($response);
    }
}
