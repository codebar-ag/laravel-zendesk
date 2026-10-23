<?php

use CodebarAg\Zendesk\Dto\Tickets\Attachments\AttachmentDTO;
use CodebarAg\Zendesk\Dto\Tickets\Attachments\ThumbnailDTO;
use CodebarAg\Zendesk\Enums\MalwareScanResult;
use CodebarAg\Zendesk\Requests\CreateAttachmentRequest;
use CodebarAg\Zendesk\ZendeskConnector;
use Saloon\Http\Faking\MockResponse;

function uploadResponse(): MockResponse
{
    return MockResponse::make([
        'upload' => [
            'token' => 'upload-token',
            'expires_at' => '2026-10-26T12:00:00Z',
            'attachments' => [[
                'id' => 1,
                'file_name' => 'test.png',
                'content_type' => 'image/png',
                'content_url' => 'https://codebar-zendesk.zendesk.com/attachments/token/abc/?name=test.png',
                'size' => 26271,
                'width' => 640,
                'height' => 360,
                'malware_scan_result' => 'not_scanned',
                'thumbnails' => [['id' => 2, 'file_name' => 'test_thumb.png', 'width' => 80, 'height' => 45]],
            ]],
            'attachment' => [
                'id' => 1,
                'file_name' => 'test.png',
                'content_type' => 'image/png',
                'content_url' => 'https://codebar-zendesk.zendesk.com/attachments/token/abc/?name=test.png',
                'size' => 26271,
                'width' => 640,
                'height' => 360,
                'malware_scan_result' => 'not_scanned',
                'thumbnails' => [['id' => 2, 'file_name' => 'test_thumb.png', 'width' => 80, 'height' => 45]],
            ],
        ],
    ], 201);
}

$file = __DIR__.'/../Fixtures/Files/test.png';

it('can create an attachment', function () use ($file) {
    $mockClient = fakeZendesk([
        CreateAttachmentRequest::class => uploadResponse(),
    ]);

    $response = new ZendeskConnector()->send(new CreateAttachmentRequest(
        fileName: 'test.png',
        mimeType: 'image/png',
        stream: fopen($file, 'r'),
    ));

    $pendingRequest = $mockClient->getLastPendingRequest();

    expect($pendingRequest->getMethod()->value)->toBe('POST')
        ->and($pendingRequest->getUrl())->toBe('https://codebar-zendesk.zendesk.com/api/v2/uploads.json')
        ->and($pendingRequest->query()->all())->toBe(['filename' => 'test.png'])
        ->and($pendingRequest->headers()->get('Content-Type'))->toBe('image/png')
        ->and((string) $pendingRequest->createPsrRequest()->getBody())->toBe(file_get_contents($file));

    $dto = $response->dto();

    expect($dto->token)->toBe('upload-token')
        ->and($dto->expires_at->toIso8601ZuluString())->toBe('2026-10-26T12:00:00Z')
        ->and($dto->attachments)->toHaveCount(1)
        ->each->toBeInstanceOf(AttachmentDTO::class)
        ->and($dto->attachment->content_type)->toBe('image/png')
        ->and($dto->attachment->size)->toBe(26271)
        ->and($dto->attachment->file_name)->toBe('test.png')
        ->and($dto->attachment->width)->toBe(640)
        ->and($dto->attachment->height)->toBe(360)
        ->and($dto->attachment->malware_scan_result)->toBe(MalwareScanResult::NOT_SCANNED)
        ->and($dto->attachment->thumbnails)->each->toBeInstanceOf(ThumbnailDTO::class)
        ->and($dto->attachment->content_url)->toBe('https://codebar-zendesk.zendesk.com/attachments/token/abc/?name=test.png');
});

it('encodes the file name and can add files to an existing upload', function () use ($file) {
    $mockClient = fakeZendesk([
        CreateAttachmentRequest::class => uploadResponse(),
    ]);

    new ZendeskConnector()->send(new CreateAttachmentRequest(
        fileName: 'my file&name.png',
        mimeType: 'image/png',
        stream: fopen($file, 'r'),
        token: 'cs0vflvMytl7PHs8XueOo5mty',
    ));

    $psrRequest = $mockClient->getLastPendingRequest()->createPsrRequest();

    expect($psrRequest->getUri()->getQuery())->toBe('filename=my+file%26name.png&token=cs0vflvMytl7PHs8XueOo5mty');
});
