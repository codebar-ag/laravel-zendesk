<?php

use CodebarAg\Zendesk\Enums\TicketPriority;
use CodebarAg\Zendesk\Requests\AllTicketsRequest;
use CodebarAg\Zendesk\ZendeskConnector;
use Saloon\Http\Faking\MockResponse;
use Saloon\Laravel\Http\Faking\MockClient;

it('can get all tickets', closure: function () {
    $mockClient = new MockClient([
        AllTicketsRequest::class => MockResponse::fixture('all-tickets-request'),
    ]);

    $connector = new ZendeskConnector;
    $connector->withMockClient($mockClient);

    $response = $connector->send(new AllTicketsRequest());

    $mockClient->assertSent(AllTicketsRequest::class);

    expect($response->dto()->count)->toBe(2)
        ->and($response->dto()->tickets[1]['id'])->toBe(81)
        ->and($response->dto()->tickets[1]['subject'])->toBe('My printer is on fire!')
        ->and($response->dto()->tickets[1]['raw_subject'])->toBe('My printer is on fire!')
        ->and($response->dto()->tickets[1]['description'])->toBe('The smoke is very colorful.')
        ->and($response->dto()->tickets[1]['priority'])->toBe(TicketPriority::URGENT)
        ->and($response->dto()->tickets[1]['custom_fields'][1]['id'])->toBe(17195718961677)
        ->and($response->dto()->tickets[1]['custom_fields'][1]['value'])->toBe('Check field works')
        ->and($response->dto()->tickets[1]['custom_fields'][2]['id'])->toBe(17195752153741)
        ->and($response->dto()->tickets[1]['custom_fields'][2]['value'])->toBe('Check field works number 2')
        ->and($response->dto()->tickets[0]['id'])->toBe(79)
        ->and($response->dto()->tickets[0]['subject'])->toBe('Eine neue Anfrage der Bärtschi ist eingetroffen: Projekt #1.')
        ->and($response->dto()->tickets[0]['raw_subject'])->toBe('Eine neue Anfrage der Bärtschi ist eingetroffen: Projekt #1.')
        ->and($response->dto()->tickets[0]['description'])
        ->toBe("Eine neue Anfrage der Bärtschi ist eingetroffen.\n\nAnfrage\nIdentifikation 1\nBemerkung asdasd\nURL https://app.pv.test/nova/resources/inquiries/1\nErstellt am 01.07.2023 14:21 Uhr\n**Kunde**\nKunden Bärtschi\nKunden ID 437046\nBenutzer ID 437046_ralph\nBenutzername Ralph Senn\n**Konfiguration**\nName Projekt #1\nDachform Sattel-/Pultdach\nMontage Aufdach\nHerkunft Asiatisch\nWechselrichter Typ String\n**Adresse**\nStrasse Mühlematten 12\nPLZ 4455\nOrt Zunzgen\nLand CH")
        ->and($response->dto()->tickets[0]['priority'])->toBe(TicketPriority::URGENT)
        ->and($response->dto()->tickets[0]['custom_fields'][1]['id'])->toBe(17195718961677)
        ->and($response->dto()->tickets[0]['custom_fields'][1]['value'])->toBe(null)
        ->and($response->dto()->tickets[0]['custom_fields'][2]['id'])->toBe(17195752153741)
        ->and($response->dto()->tickets[0]['custom_fields'][2]['value'])->toBe(null);

});
