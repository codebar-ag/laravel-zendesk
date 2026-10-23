<?php

namespace CodebarAg\Zendesk\Auth;

use CodebarAg\Zendesk\Dto\OAuth\AccessTokenDTO;
use CodebarAg\Zendesk\Exceptions\ZendeskAuthenticationException;
use CodebarAg\Zendesk\Requests\OAuth\CreateAccessTokenRequest;
use Illuminate\Contracts\Cache\LockProvider;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use JsonException;

class AccessTokenRepository
{
    // Renew tokens a bit before Zendesk expires them to avoid races with in-flight requests.
    public const int EXPIRY_BUFFER = 60;

    protected Repository $cache;

    public function __construct(
        protected readonly string $subdomain,
        protected readonly OAuthCredentials $credentials,
        ?Repository $cache = null,
    ) {
        $this->cache = $cache ?? Cache::store(config('zendesk.cache.store'));
    }

    public function get(): string
    {
        $token = $this->cached();

        if ($token !== null) {
            return $token;
        }

        $store = $this->cache->getStore();

        if (! $store instanceof LockProvider) {
            return $this->refresh();
        }

        // Only one process fetches a token, the others wait and read it from the cache.
        return $store->lock($this->key().':lock', 10)
            ->block(10, fn (): string => $this->cached() ?? $this->refresh());
    }

    public function refresh(): string
    {
        $response = new ZendeskOAuthConnector($this->subdomain)
            ->send(new CreateAccessTokenRequest($this->credentials));

        if ($response->failed()) {
            throw ZendeskAuthenticationException::fromResponse($response);
        }

        try {
            $token = AccessTokenDTO::fromResponse($response);
        } catch (JsonException) {
            throw new ZendeskAuthenticationException('Zendesk returned an invalid OAuth token response.');
        }

        if ($token->access_token === '') {
            throw new ZendeskAuthenticationException('Zendesk returned an empty OAuth access token.');
        }

        $this->cache->put($this->key(), $token->access_token, $this->ttl($token));

        return $token->access_token;
    }

    public function forget(): void
    {
        $this->cache->forget($this->key());
    }

    public function key(): string
    {
        return config('zendesk.cache.prefix', 'zendesk:oauth:').sha1(implode('|', [
            $this->subdomain,
            $this->credentials->clientId,
            $this->credentials->scopes,
        ]));
    }

    protected function cached(): ?string
    {
        $token = $this->cache->get($this->key());

        return is_string($token) && $token !== '' ? $token : null;
    }

    protected function ttl(AccessTokenDTO $token): int
    {
        if ($token->expires_in === null) {
            return (int) config('zendesk.cache.fallback_ttl', 86400);
        }

        return max($token->expires_in - self::EXPIRY_BUFFER, 1);
    }
}
