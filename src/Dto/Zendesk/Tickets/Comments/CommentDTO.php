<?php

namespace CodebarAg\Zendesk\Dto\Zendesk\Tickets\Comments;

use Illuminate\Support\Carbon;
use Spatie\LaravelData\Data;

class CommentDTO extends Data
{
    public function __construct(
        public ?array $attachments,
        public ?int $audit_id,
        public ?int $author_id,
        public ?string $body,
        public ?Carbon $created_at,
        public ?string $html_body,
        public ?int $id,
        public ?array $metadata,
        public ?int $plain_body,
        public ?bool $public,
        public ?string $type,
        public ?array $uploads,
        public ?array $via,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new static(
            attachments: $data['attachments'] ?? null,
            audit_id: $data['audit_id'] ?? null,
            author_id: $data['author_id'] ?? null,
            body: $data['body'] ?? null,
            created_at: $data['created_at'] ?? null,
            html_body: $data['html_body'] ?? null,
            id: $data['id'] ?? null,
            metadata: $data['metadata'] ?? null,
            plain_body: $data['plain_body'] ?? null,
            public: $data['public'] ?? null,
            type: $data['type'] ?? null,
            uploads: $data['uploads'] ?? null,
            via: $data['via'] ?? null,
        );
    }
}
