<?php

namespace CodebarAg\Zendesk\Dto\Tickets\Comments;

use CodebarAg\Zendesk\Dto\Concerns\ParsesDates;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Spatie\LaravelData\Data;

class CommentDTO extends Data
{
    use ParsesDates;

    /**
     * @param  array<mixed>|null  $attachments
     * @param  array<mixed>|null  $metadata
     * @param  array<mixed>|null  $uploads
     * @param  array<mixed>|null  $via
     */
    public function __construct(
        public ?array $attachments,
        public ?int $audit_id,
        public ?int $author_id,
        public ?string $body,
        public ?Carbon $created_at,
        public ?string $html_body,
        public ?int $id,
        public ?array $metadata,
        public ?string $plain_body,
        public ?bool $public,
        public ?string $type,
        public ?array $uploads,
        public ?array $via,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            attachments: Arr::get($data, 'attachments'),
            audit_id: Arr::get($data, 'audit_id'),
            author_id: Arr::get($data, 'author_id'),
            body: Arr::get($data, 'body'),
            created_at: self::parseDate(Arr::get($data, 'created_at')),
            html_body: Arr::get($data, 'html_body'),
            id: Arr::get($data, 'id'),
            metadata: Arr::get($data, 'metadata'),
            plain_body: Arr::get($data, 'plain_body'),
            public: Arr::get($data, 'public'),
            type: Arr::get($data, 'type'),
            uploads: Arr::get($data, 'uploads'),
            via: Arr::get($data, 'via'),
        );
    }
}
