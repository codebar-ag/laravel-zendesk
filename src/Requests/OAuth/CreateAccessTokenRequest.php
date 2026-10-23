<?php

namespace CodebarAg\Zendesk\Requests\OAuth;

use CodebarAg\Zendesk\Auth\OAuthCredentials;
use CodebarAg\Zendesk\Dto\OAuth\AccessTokenDTO;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

class CreateAccessTokenRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected readonly OAuthCredentials $credentials,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/oauth/tokens';
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultBody(): array
    {
        return array_filter([
            'grant_type' => 'client_credentials',
            'client_id' => $this->credentials->clientId,
            'client_secret' => $this->credentials->clientSecret,
            'scope' => $this->credentials->scopes,
            'expires_in' => $this->credentials->expiresIn,
        ], fn (mixed $value): bool => $value !== null);
    }

    public function createDtoFromResponse(Response $response): AccessTokenDTO
    {
        $response->throw();

        return AccessTokenDTO::fromResponse($response);
    }
}
