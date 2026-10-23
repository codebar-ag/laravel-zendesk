<?php

namespace CodebarAg\Zendesk\Auth;

use CodebarAg\Zendesk\Exceptions\ZendeskConfigurationException;

final readonly class OAuthCredentials
{
    public const int MIN_EXPIRES_IN = 300;

    public const int MAX_EXPIRES_IN = 172800;

    public string $scopes;

    /**
     * @param  string|array<int, string>  $scopes
     */
    public function __construct(
        public string $clientId,
        public string $clientSecret,
        string|array $scopes = 'read write',
        public ?int $expiresIn = null,
    ) {
        if (trim($clientId) === '') {
            throw new ZendeskConfigurationException('No OAuth client ID provided.');
        }

        if (trim($clientSecret) === '') {
            throw new ZendeskConfigurationException('No OAuth client secret provided.');
        }

        // Zendesk expects a space separated string. An array creates a token that is rejected with 403.
        $this->scopes = implode(' ', array_filter(
            is_array($scopes) ? $scopes : (preg_split('/[\s,]+/', $scopes) ?: []),
            fn (string $scope): bool => $scope !== '',
        ));

        if ($this->scopes === '') {
            throw new ZendeskConfigurationException('No OAuth scopes provided.');
        }

        if ($expiresIn !== null && ($expiresIn < self::MIN_EXPIRES_IN || $expiresIn > self::MAX_EXPIRES_IN)) {
            throw new ZendeskConfigurationException(sprintf(
                'The OAuth token lifetime must be between %d and %d seconds.',
                self::MIN_EXPIRES_IN,
                self::MAX_EXPIRES_IN,
            ));
        }
    }

    public static function fromConfig(): self
    {
        $expiresIn = config('zendesk.auth.oauth.expires_in');

        return new self(
            clientId: (string) config('zendesk.auth.oauth.client_id'),
            clientSecret: (string) config('zendesk.auth.oauth.client_secret'),
            scopes: config('zendesk.auth.oauth.scopes') ?? 'read write',
            expiresIn: filled($expiresIn) ? (int) $expiresIn : null,
        );
    }
}
