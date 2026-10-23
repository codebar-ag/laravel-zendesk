<?php

use CodebarAg\Zendesk\Dto\OAuth\AccessTokenDTO;
use CodebarAg\Zendesk\Dto\Tickets\AllTicketsDTO;
use CodebarAg\Zendesk\Dto\Tickets\Attachments\AttachmentDTO;
use CodebarAg\Zendesk\Dto\Tickets\Attachments\ThumbnailDTO;
use CodebarAg\Zendesk\Dto\Tickets\Attachments\UploadDTO;
use CodebarAg\Zendesk\Dto\Tickets\Comments\CommentDTO;
use CodebarAg\Zendesk\Dto\Tickets\CountTicketsDTO;
use CodebarAg\Zendesk\Enums\MalwareScanResult;

it('maps comments', function () {
    $comment = CommentDTO::fromArray([
        'id' => 1,
        'body' => 'Hello',
        'plain_body' => 'Hello',
        'html_body' => '<p>Hello</p>',
        'public' => true,
        'created_at' => '2026-01-02T03:04:05Z',
    ]);

    expect($comment->plain_body)->toBe('Hello')
        ->and($comment->html_body)->toBe('<p>Hello</p>')
        ->and($comment->created_at->toIso8601ZuluString())->toBe('2026-01-02T03:04:05Z')
        ->and(CommentDTO::fromArray([])->created_at)->toBeNull();
});

it('maps attachments without malware scan results or thumbnails', function () {
    $attachment = AttachmentDTO::fromArray(['id' => 1, 'file_name' => 'a.txt']);

    expect($attachment->malware_scan_result)->toBeNull()
        ->and($attachment->thumbnails)->toBeNull();
});

it('maps attachment thumbnails from arrays and dtos', function () {
    $thumbnail = ThumbnailDTO::fromArray(['id' => 2, 'malware_scan_result' => 'malware_found']);

    $attachment = AttachmentDTO::fromArray([
        'malware_scan_result' => MalwareScanResult::MALWARE_NOT_FOUND,
        'thumbnails' => [['id' => 1, 'width' => '80'], $thumbnail],
    ]);

    expect($attachment->malware_scan_result)->toBe(MalwareScanResult::MALWARE_NOT_FOUND)
        ->and($attachment->thumbnails[0]->width)->toBe(80)
        ->and($attachment->thumbnails[1])->toBe($thumbnail)
        ->and($thumbnail->malware_scan_result)->toBe(MalwareScanResult::MALWARE_FOUND);
});

it('maps uploads without attachments', function () {
    $upload = UploadDTO::fromArray(['token' => 'abc']);

    expect($upload->token)->toBe('abc')
        ->and($upload->attachments)->toBe([])
        ->and($upload->attachment)->toBeNull()
        ->and($upload->expires_at)->toBeNull();
});

it('maps an empty ticket list', function () {
    $tickets = AllTicketsDTO::fromArray([]);

    expect($tickets->tickets)->toBe([])
        ->and($tickets->count)->toBe(0)
        ->and($tickets->next_page_url)->toBeNull()
        ->and($tickets->previous_page_url)->toBeNull();
});

it('maps pagination urls', function () {
    $tickets = AllTicketsDTO::fromArray([
        'tickets' => [['id' => 1]],
        'count' => 101,
        'next_page' => 'https://acme.zendesk.com/api/v2/tickets.json?page=2',
        'previous_page' => null,
    ]);

    expect($tickets->tickets[0]->id)->toBe(1)
        ->and($tickets->next_page_url)->toBe('https://acme.zendesk.com/api/v2/tickets.json?page=2');
});

it('maps ticket counts', function () {
    $count = CountTicketsDTO::fromArray(['value' => 10, 'refreshed_at' => null]);

    expect($count->value)->toBe(10)
        ->and($count->refreshed_at)->toBeNull();
});

it('maps access tokens', function () {
    $token = AccessTokenDTO::fromArray([
        'access_token' => 'abc',
        'token_type' => 'bearer',
        'scope' => 'read',
        'expires_in' => 1800,
        'refresh_token' => 'def',
        'refresh_token_expires_in' => 2592000,
    ]);

    expect($token->access_token)->toBe('abc')
        ->and($token->token_type)->toBe('bearer')
        ->and($token->scope)->toBe('read')
        ->and($token->expires_in)->toBe(1800)
        ->and($token->refresh_token)->toBe('def')
        ->and($token->refresh_token_expires_in)->toBe(2592000);
});
