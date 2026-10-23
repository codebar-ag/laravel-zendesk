<?php

namespace CodebarAg\Zendesk\Auth;

enum AuthMethod: string
{
    case OAUTH = 'oauth';
    case ACCESS_TOKEN = 'access_token';
}
