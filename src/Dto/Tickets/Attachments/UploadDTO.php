<?php

namespace CodebarAg\Zendesk\Dto\Tickets\Attachments;

use CodebarAg\Zendesk\Dto\Concerns\ParsesDates;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Saloon\Http\Response;
use Spatie\LaravelData\Data;

class UploadDTO extends Data
{
    use ParsesDates;

    /**
     * @param  array<int, AttachmentDTO>  $attachments
     */
    public function __construct(
        public string $token,
        public ?Carbon $expires_at,
        public array $attachments,
        public ?AttachmentDTO $attachment,
    ) {}

    public static function fromResponse(Response $response): self
    {
        return self::fromArray((array) $response->json('upload'));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $attachment = Arr::get($data, 'attachment');

        return new self(
            token: (string) Arr::get($data, 'token'),
            expires_at: self::parseDate(Arr::get($data, 'expires_at')),
            attachments: array_map(
                fn (array|AttachmentDTO $attachment): AttachmentDTO => self::attachment($attachment),
                array_values((array) Arr::get($data, 'attachments', [])),
            ),
            attachment: $attachment === null ? null : self::attachment($attachment),
        );
    }

    /**
     * @param  array<string, mixed>|AttachmentDTO  $attachment
     */
    private static function attachment(array|AttachmentDTO $attachment): AttachmentDTO
    {
        return $attachment instanceof AttachmentDTO ? $attachment : AttachmentDTO::fromArray($attachment);
    }
}
