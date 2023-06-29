<?php

namespace CodebarAg\Zendesk\Dto\Tickets;

use CodebarAg\Zendesk\Dto\Tickets\Comments\CommentDTO;
use CodebarAg\Zendesk\Enums\TicketPriority;
use Spatie\LaravelData\Data;

class CreateTicketDTO extends Data
{
    public function __construct(
        public CommentDTO $comment,
        public ?TicketPriority $priority,
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
