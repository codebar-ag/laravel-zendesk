<?php

namespace CodebarAg\Zendesk\Dto\Tickets\Attachments;

use CodebarAg\Zendesk\Enums\MalwareScanResult;
use Illuminate\Support\Carbon;
use Saloon\Http\Response;
use Spatie\LaravelData\Data;

class AttachmentDTO extends Data
{
    public function __construct(
        public ?string $content_type,
        public ?string $content_url,
        public ?bool $deleted,
        public ?string $file_name,
        public ?string $height,
        public ?int $id,
        public ?bool $inline,
        public ?bool $malware_access_override,
        public ?MalwareScanResult $malware_scan_result,
        public ?string $mapped_content_url,
        public ?int $size,
        public ?array $thumbnails,
        public ?string $url,
        public ?string $width,
    ) {
    }

    public static function fromResponse(Response $response): self
    {
        $data = $response->json();

        return self::fromArray($data);
    }

    public static function fromArray(array $data): self
    {
        return new static(
            content_type: $data['content_type'] ?? null,
            content_url: $data['content_url'] ?? null,
            deleted: $data['deleted'] ?? null,
            file_name: $data['file_name'] ?? null,
            height: $data['height'] ?? null,
            id: $data['id'] ?? null,
            inline: $data['inline'] ?? null,
            malware_access_override: $data['malware_access_override'] ?? null,
            malware_scan_result: MalwareScanResult::tryFrom($data['malware_scan_result'] ?? null),
            mapped_content_url: $data['mapped_content_url'] ?? null,
            size: $data['size'] ?? null,
            thumbnails: $data['thumbnails'] ?? null,
            url: $data['url'] ?? null,
            width: $data['width'] ?? null,
        );
    }
}
