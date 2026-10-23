<?php

namespace CodebarAg\Zendesk;

use CodebarAg\Zendesk\Auth\AccessTokenRepository;
use CodebarAg\Zendesk\Auth\AuthMethod;
use CodebarAg\Zendesk\Auth\OAuthCredentials;
use CodebarAg\Zendesk\Concerns\ResolvesSubdomain;
use CodebarAg\Zendesk\Exceptions\ZendeskConfigurationException;
use Saloon\Contracts\Authenticator;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Connector;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Request;
use Saloon\Http\Response;

class ZendeskConnector extends Connector
{
    use ResolvesSubdomain;

    protected ?AccessTokenRepository $tokens = null;

    /**
     * Without arguments, the subdomain and credentials are read from config('zendesk').
     * Pass them explicitly to talk to several Zendesk accounts at runtime.
     */
    public function __construct(
        ?string $subdomain = null,
        protected readonly ?OAuthCredentials $credentials = null,
    ) {
        $this->subdomain = $subdomain;
    }

    public function resolveBaseUrl(): string
    {
        return 'https://'.$this->resolveSubdomain().'.zendesk.com/api/v2';
    }

    protected function defaultHeaders(): array
    {
        return [
            'Accept' => 'application/json',
        ];
    }

    protected function defaultAuth(): Authenticator
    {
        return match ($this->authMethod()) {
            AuthMethod::OAUTH => new TokenAuthenticator($this->tokens()->get()),
            AuthMethod::ACCESS_TOKEN => new TokenAuthenticator($this->configuredAccessToken()),
        };
    }

    /**
     * Retries a request once with a fresh token when Zendesk rejects a cached OAuth token,
     * e.g. because the token was revoked before it expired.
     */
    public function send(Request $request, ?MockClient $mockClient = null, ?callable $handleRetry = null): Response
    {
        $response = parent::send($request, $mockClient, $handleRetry);

        if ($response->status() !== 401 || ! $this->usesManagedTokens()) {
            return $response;
        }

        $this->tokens()->forget();

        return parent::send($request, $mockClient, $handleRetry);
    }

    public function authMethod(): AuthMethod
    {
        if ($this->credentials !== null) {
            return AuthMethod::OAUTH;
        }

        $method = config('zendesk.auth.method');

        if (! is_string($method) || $method === '') {
            throw new ZendeskConfigurationException('No authentication method provided.');
        }

        return AuthMethod::tryFrom($method) ?? throw new ZendeskConfigurationException(sprintf(
            'Invalid authentication method "%s" provided. Supported methods: %s.',
            $method,
            implode(', ', array_column(AuthMethod::cases(), 'value')),
        ));
    }

    public function tokens(): AccessTokenRepository
    {
        return $this->tokens ??= new AccessTokenRepository(
            subdomain: $this->resolveSubdomain(),
            credentials: $this->credentials ?? OAuthCredentials::fromConfig(),
        );
    }

    protected function usesManagedTokens(): bool
    {
        // An authenticator set via authenticate() takes precedence over defaultAuth().
        return $this->authenticator === null && $this->authMethod() === AuthMethod::OAUTH;
    }

    protected function configuredAccessToken(): string
    {
        $token = config('zendesk.auth.access_token');

        if (! is_string($token) || $token === '') {
            throw new ZendeskConfigurationException('No access token provided for access_token authentication.');
        }

        return $token;
    }
}
