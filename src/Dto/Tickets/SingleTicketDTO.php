<?php

namespace CodebarAg\Zendesk\Dto\Tickets;

use CodebarAg\Zendesk\Dto\Tickets\Comments\CommentDTO;
use CodebarAg\Zendesk\Enums\TicketPriority;
use CodebarAg\Zendesk\Enums\TicketType;
use Illuminate\Support\Carbon;
use Saloon\Http\Response;
use Spatie\LaravelData\Data;
use function Pest\Laravel\instance;

final class SingleTicketDTO extends Data
{
    public function __construct(
        public ?bool $allow_attachments,
        public ?bool $allow_channelback,
        public ?string $assignee_email,
        public ?int $assignee_id,
        public ?array $attribute_value_ids,
        public ?int $brand_id,
        public ?array $collaborator_ids,
        public ?array $collaborators,
        public ?CommentDTO $comment,
        public ?Carbon $created_at,
        public ?array $custom_fields,
        public ?string $description,
        public ?Carbon $due_at,
        public ?array $email_cc_ids,
        public ?array $email_ccs,
        public ?string $external_id,
        public ?array $follower_ids,
        public ?array $followers,
        public ?array $followup_ids,
        public ?int $forum_topic_id,
        public ?bool $from_messaging_channel,
        public ?int $group_id,
        public ?bool $has_incidents,
        public ?int $id,
        public ?bool $is_public,
        public ?int $macro_id,
        public ?array $macro_ids,
        public ?array $metadata,
        public ?int $organization_id,
        public ?TicketPriority $priority,
        public ?int $problem_id,
        public ?string $raw_subject,
        public ?string $recipient,
        public ?array $requester,
        public ?int $requester_id,
        public ?bool $self_update,
        public ?array $satisfaction_rating,
        public ?array $sharing_agreement_ids,
        public ?string $status,
        public ?string $subject,
        public ?int $submitter_id,
        public ?array $tags,
        public ?int $ticket_form_id,
        public ?TicketType $type,
        public ?Carbon $updated_at,
        public ?Carbon $updated_stamp,
        public ?string $url,
        public ?array $via,
        public ?int $via_followup_source_id,
        public ?int $via_id,
        public ?array $voice_comment,
    ) {
    }

    public static function fromResponse(Response $response): self
    {
        $data = $response->json()['ticket'];

        return self::fromArray($data);
    }

    public static function fromArray(array $data): self
    {
        $comment = array_key_exists('comment', $data) ? $data['comment'] : null;

        if ($comment && ! $comment instanceof CommentDTO) {
            $comment = CommentDTO::fromArray($comment);
        }

        $priority =  array_key_exists('priority', $data) ? $data['priority'] : null;

        if ($priority && ! $priority instanceof TicketPriority) {
            $priority = TicketPriority::tryFrom($priority);
        }

        $type =  array_key_exists('type', $data) ? $data['type'] : null;

        if ($type && ! $type instanceof TicketType) {
            $type = TicketType::tryFrom($type);
        }

        return new static(
            allow_attachments: $data['allow_attachments'] ?? null,
            allow_channelback: $data['allow_channelback'] ?? null,
            assignee_email: $data['assignee_email'] ?? null,
            assignee_id: $data['assignee_id'] ?? null,
            attribute_value_ids: $data['attribute_value_ids'] ?? null,
            brand_id: $data['brand_id'] ?? null,
            collaborator_ids: $data['collaborator_ids'] ?? null,
            collaborators: $data['collaborators'] ?? null,
            comment: $comment,
            created_at: Carbon::parse($data['created_at'] ?? null),
            custom_fields: $data['custom_fields'] ?? null,
            description: $data['description'] ?? null,
            due_at: Carbon::parse($data['due_at'] ?? null),
            email_cc_ids: $data['email_cc_ids'] ?? null,
            email_ccs: $data['email_ccs'] ?? null,
            external_id: $data['external_id'] ?? null,
            follower_ids: $data['follower_ids'] ?? null,
            followers: $data['followers'] ?? null,
            followup_ids: $data['followup_ids'] ?? null,
            forum_topic_id: $data['forum_topic_id'] ?? null,
            from_messaging_channel: $data['from_messaging_channel'] ?? null,
            group_id: $data['group_id'] ?? null,
            has_incidents: $data['has_incidents'] ?? null,
            id: $data['id'] ?? null,
            is_public: $data['is_public'] ?? null,
            macro_id: $data['macro_id'] ?? null,
            macro_ids: $data['macro_ids'] ?? null,
            metadata: $data['metadata'] ?? null,
            organization_id: $data['organization_id'] ?? null,
            priority: $priority,
            problem_id: $data['problem_id'] ?? null,
            raw_subject: $data['raw_subject'] ?? null,
            recipient: $data['recipient'] ?? null,
            requester: $data['requester'] ?? null,
            requester_id: $data['requester_id'] ?? null,
            self_update: $data['self_update'] ?? null,
            satisfaction_rating: $data['satisfaction_rating'] ?? null,
            sharing_agreement_ids: $data['sharing_agreement_ids'] ?? null,
            status: $data['status'] ?? null,
            subject: $data['subject'] ?? null,
            submitter_id: $data['submitter_id'] ?? null,
            tags: $data['tags'] ?? null,
            ticket_form_id: $data['ticket_form_id'] ?? null,
            type: $type,
            updated_at: Carbon::parse($data['updated_at'] ?? null),
            updated_stamp: $data['updated_stamp'] ?? null,
            url: $data['url'] ?? null,
            via: $data['via'] ?? null,
            via_followup_source_id: $data['via_followup_source_id'] ?? null,
            via_id: $data['via_id'] ?? null,
            voice_comment: $data['voice_comment'] ?? null,
        );
    }
}
