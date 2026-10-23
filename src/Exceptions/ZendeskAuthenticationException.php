<?php

namespace CodebarAg\Zendesk\Exceptions;

use JsonException;
use Saloon\Http\Response;

class ZendeskAuthenticationException extends ZendeskException
{
    public static function fromResponse(Response $response): self
    {
        try {
            $body = $response->json();
        } catch (JsonException) {
            $body = [];
        }

        $error = is_string($body['error'] ?? null) ? $body['error'] : 'unknown_error';
        $description = is_string($body['error_description'] ?? null) ? $body['error_description'] : '';

        $message = $description !== ''
            ? sprintf('Unable to obtain a Zendesk OAuth access token (%s): %s', $error, $description)
            : sprintf('Unable to obtain a Zendesk OAuth access token (%s).', $error);

        return new self($message, $response->status());
    }
}
