<?php

namespace CodebarAg\Zendesk\Dto\Zendesk\Tickets;

use CodebarAg\Zendesk\Dto\Zendesk\Tickets\Comments\CommentDTO;
use Spatie\LaravelData\Data;

class CreateTicketDTO extends Data
{
    public function __construct(
        public CommentDTO $comment,
        public ?string $priority,
        public ?string $subject,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new static(
            comment: $data['comment'],
            priority: $data['priority'] ?? null,
            subject: $data['subject'] ?? null,
        );
    }
}
