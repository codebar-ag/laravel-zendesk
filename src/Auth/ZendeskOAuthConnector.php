<?php

namespace CodebarAg\Zendesk\Auth;

use CodebarAg\Zendesk\Concerns\ResolvesSubdomain;
use Saloon\Http\Connector;

class ZendeskOAuthConnector extends Connector
{
    use ResolvesSubdomain;

    public function __construct(?string $subdomain = null)
    {
        $this->subdomain = $subdomain;
    }

    public function resolveBaseUrl(): string
    {
        return 'https://'.$this->resolveSubdomain().'.zendesk.com';
    }

    protected function defaultHeaders(): array
    {
        return [
            'Accept' => 'application/json',
        ];
    }
}
