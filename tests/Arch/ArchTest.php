<?php

use CodebarAg\Zendesk\Exceptions\ZendeskException;
use Saloon\Http\Connector;
use Saloon\Http\Request;
use Spatie\LaravelData\Data;

arch('it will not use any debug function')
    ->expect(['dd', 'ray', 'dump', 'var_dump', 'die'])
    ->not->toBeUsed();

arch('requests extend the Saloon request')
    ->expect('CodebarAg\Zendesk\Requests')
    ->classes()
    ->toExtend(Request::class);

arch('dtos extend laravel-data')
    ->expect('CodebarAg\Zendesk\Dto')
    ->classes()
    ->toExtend(Data::class);

arch('exceptions extend the package base exception')
    ->expect('CodebarAg\Zendesk\Exceptions')
    ->classes()
    ->toExtend(ZendeskException::class);

arch('connectors extend the Saloon connector')
    ->expect(['CodebarAg\Zendesk\ZendeskConnector', 'CodebarAg\Zendesk\Auth\ZendeskOAuthConnector'])
    ->toExtend(Connector::class);

arch('enums are backed by strings')
    ->expect('CodebarAg\Zendesk\Enums')
    ->toBeStringBackedEnums();

arch('the package does not throw generic exceptions')
    ->expect('CodebarAg\Zendesk')
    ->not->toUse(Exception::class);
