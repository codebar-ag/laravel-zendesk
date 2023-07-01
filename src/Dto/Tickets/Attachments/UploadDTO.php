<?php

namespace CodebarAg\Zendesk\Dto\Tickets\Attachments;

use CodebarAg\Zendesk\Enums\MalwareScanResult;
use Illuminate\Support\Carbon;
use Saloon\Http\Response;
use Spatie\LaravelData\Data;

final class UploadDTO extends Data
{
    public function __construct(
        public string $token,
        public Carbon $expires_at,
        public array $attachments,
        public AttachmentDTO $attachment,
    ) {
    }

    public static function fromResponse(Response $response): self
    {
        $data = $response->json()['upload'];

        return self::fromArray($data);
    }

    public static function fromArray(array $data): self
    {
        $attachments = $data['attachments'] ?? null;

        if ($attachments) {
            foreach ($attachments as $key => $attachment) {
                $attachments[$key] = self::getAttachment($attachment);
            }
        }

        return new static(
            token: $data['token'] ?? null,
            expires_at: Carbon::parse($data['expires_at'] ?? null),
            attachments: $attachments,
            attachment: self::getAttachment($data['attachment'] ?? null),
        );
    }

    private static function getAttachment(null|array|AttachmentDTO $data): AttachmentDTO
    {
        $attachment = $data ?? null;

        if (! $attachment instanceof AttachmentDTO) {
            $attachment = AttachmentDTO::fromArray($attachment);
        }

        return $attachment;
    }
}
