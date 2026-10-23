<?php

namespace CodebarAg\Zendesk\Concerns;

use CodebarAg\Zendesk\Exceptions\ZendeskConfigurationException;

trait ResolvesSubdomain
{
    protected ?string $subdomain = null;

    public function resolveSubdomain(): string
    {
        $subdomain = $this->subdomain ?? config('zendesk.subdomain');

        if (! is_string($subdomain) || $subdomain === '') {
            throw new ZendeskConfigurationException('No subdomain provided.');
        }

        if (preg_match('/^[a-z0-9]([a-z0-9-]*[a-z0-9])?$/i', $subdomain) !== 1) {
            throw new ZendeskConfigurationException(sprintf('Invalid subdomain provided: "%s".', $subdomain));
        }

        return $subdomain;
    }
}
