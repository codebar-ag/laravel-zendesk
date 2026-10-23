<?php

namespace CodebarAg\Zendesk;

use CodebarAg\Zendesk\Auth\OAuthCredentials;

class Zendesk
{
    public function connector(?string $subdomain = null, ?OAuthCredentials $credentials = null): ZendeskConnector
    {
        return new ZendeskConnector($subdomain, $credentials);
    }
}
