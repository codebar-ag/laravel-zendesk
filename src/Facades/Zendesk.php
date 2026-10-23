<?php

namespace CodebarAg\Zendesk\Facades;

use CodebarAg\Zendesk\Auth\OAuthCredentials;
use CodebarAg\Zendesk\ZendeskConnector;
use Illuminate\Support\Facades\Facade;

/**
 * @method static ZendeskConnector connector(?string $subdomain = null, ?OAuthCredentials $credentials = null)
 *
 * @see \CodebarAg\Zendesk\Zendesk
 */
class Zendesk extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \CodebarAg\Zendesk\Zendesk::class;
    }
}
